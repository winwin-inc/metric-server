<?php

declare(strict_types=1);

namespace winwin\metric\domain;

use DateTime;
use DI\Annotation\Inject;
use kuiper\db\annotation\Entity;
use kuiper\db\Criteria;
use kuiper\db\sharding\AbstractShardingCrudRepository;
use kuiper\db\sharding\rule\RuleInterface;
use kuiper\di\annotation\Repository;
use kuiper\helper\Arrays;

/**
 * @Entity(MetricValue::class)
 * @Repository()
 *
 * @method MetricValue[] findAllBy($criteria = null) : array
 */
class MetricValueRepository extends AbstractShardingCrudRepository implements MetricValueRepositoryInterface
{
    /**
     * @Inject("MetricShardingRule")
     *
     * @var RuleInterface
     */
    private $metricShardingRule;

    public function findAllBetween(array $metrics, DateTime $startDate, ?DateTime $endDate = null): array
    {
        $values = [];
        foreach (Arrays::groupBy($metrics, function ($metric) { return $this->getMetricSharding($metric); })
                 as $partition => $partMetrics) {
            $values[] = $this->findAllBy($this->createCriteria($partMetrics, $startDate, $endDate)
                ->where('sharding', $partition)
                ->select('id', 'bizDate', 'metricId', 'value'));
        }

        return array_merge(...$values);
    }

    public function deleteAllBetween(array $metrics, DateTime $startDate, ?DateTime $endDate = null): void
    {
        foreach (Arrays::groupBy($metrics, function ($metric) { return $this->getMetricSharding($metric); })
                 as $partition => $partMetrics) {
            $this->deleteAllBy($this->createCriteria($partMetrics, $startDate, $endDate)
                ->where('sharding', $partition));
        }
    }

    /**
     * @param MetricValue[] $values
     */
    public function saveAll(array $values): void
    {
        foreach ($values as $value) {
            $value->setSharding($this->getMetricSharding($value->getMetric()));
        }
        $exists = $this->findExistValues($values);
        $this->batchInsert(array_filter($values, static function (MetricValue $value) {
            return is_null($value->getId());
        }));
        if (!empty($exists)) {
            $this->batchUpdate($exists);
        }
    }

    /**
     * @param MetricValue[] $values
     */
    public function increaseAll(array $values): void
    {
        foreach ($values as $value) {
            $value->setSharding($this->getMetricSharding($value->getMetric()));
        }
        $exists = $this->findExistValues($values);
        $this->batchInsert(array_filter($values, static function (MetricValue $value) {
            return is_null($value->getId());
        }));
        $stmt = $this->queryBuilder->update($this->getTableName());

        foreach (Arrays::groupBy($values, 'sharding') as $sharding => $partValues) {
            $i = 1;
            $caseExp = ['case `id`'];
            $bindValues = [];
            foreach ($exists as $exist) {
                $v1 = 'i'.($i++);
                $v2 = 'i'.($i++);
                $caseExp[] = sprintf('when :%s then value+:%s', $v1, $v2);
                $bindValues[$v1] = $exist->getId();
                $bindValues[$v2] = $exist->getValue();
            }
            $stmt->set('value', implode(' ', $caseExp).' end');
            $stmt->bindValues($bindValues);
            $stmt->in('id', Arrays::pull($exists, 'id'));
            $stmt->shardBy(['sharding' => $sharding]);
            $this->doExecute($stmt);
        }
    }

    /**
     * @param MetricValue[] $values
     *
     * @return MetricValue[]
     */
    private function findExistValues(array $values): array
    {
        $all = [];
        foreach (Arrays::groupBy($values, 'sharding') as $sharding => $partValues) {
            $naturalIds = [];
            foreach ($values as $value) {
                $naturalIds[] = [
                    'bizDate' => $value->getBizDate()->format('Y-m-d'),
                    'metricId' => $value->getMetricId(),
                ];
            }
            /** @var MetricValue[][] $exists */
            $exists = [];
            foreach ($this->findAllBy(Criteria::create(['sharding' => $sharding])
                ->matches($naturalIds, ['bizDate', 'metricId'])) as $metricValue) {
                $exists[$metricValue->getDateString()][$metricValue->getMetricId()] = $metricValue;
            }
            foreach ($values as $value) {
                if (isset($exists[$value->getDateString()][$value->getMetricId()])) {
                    $exist = $exists[$value->getDateString()][$value->getMetricId()];
                    $exist->setValue($value->getValue());
                    $value->setId($exist->getId());
                }
            }
            if (!empty($exists)) {
                $all[] = Arrays::flatten($exists);
            }
        }

        return Arrays::flatten($all);
    }

    private function createCriteria(array $metrics, DateTime $startDate, ?DateTime $endDate): Criteria
    {
        $criteria = Criteria::create()
            ->in('metricId', Arrays::pull($metrics, 'id'));
        if ($endDate) {
            $criteria
                ->where('bizDate', $startDate, '>=')
                ->where('bizDate', $endDate, '<=');
        } else {
            $criteria->where('bizDate', $startDate);
        }

        return $criteria;
    }

    private function getMetricSharding(Metric $metric): int
    {
        return $this->metricShardingRule->getPartition([
            'scope_id' => $metric->getScopeId(),
            'name' => $metric->getName(),
            'tags' => $metric->getTags(),
        ]);
    }

    public function setMetricShardingRule(RuleInterface $metricShardingRule): void
    {
        $this->metricShardingRule = $metricShardingRule;
    }
}
