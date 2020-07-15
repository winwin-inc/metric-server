<?php

declare(strict_types=1);

namespace winwin\metric\domain;

use DateTime;
use kuiper\db\AbstractCrudRepository;
use kuiper\db\annotation\Entity;
use kuiper\db\Criteria;
use kuiper\di\annotation\Repository;
use kuiper\helper\Arrays;

/**
 * @Entity(MetricValue::class)
 * @Repository()
 *
 * @method MetricValue[] findAllBy($criteria = null) : array
 */
class MetricValueRepository extends AbstractCrudRepository
{
    public function findAllBetween(array $metrics, DateTime $startDate, ?DateTime $endDate = null): array
    {
        return $this->findAllBy($this->createCriteria($metrics, $startDate, $endDate));
    }

    public function deleteAllBetween(array $metrics, DateTime $startDate, ?DateTime $endDate = null): void
    {
        $this->deleteAllBy($this->createCriteria($metrics, $startDate, $endDate));
    }

    /**
     * @param MetricValue[] $values
     */
    public function saveAll(array $values): void
    {
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
        $exists = $this->findExistValues($values);
        $this->batchInsert(array_filter($values, static function (MetricValue $value) {
            return is_null($value->getId());
        }));
        $stmt = $this->queryBuilder->update($this->getTableName());

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
        $this->doExecute($stmt);
    }

    /**
     * @param MetricValue[] $values
     *
     * @return MetricValue[]
     */
    private function findExistValues(array $values): array
    {
        $naturalIds = [];
        foreach ($values as $value) {
            $naturalIds[] = [
                'bizDate' => $value->getBizDate()->format('Y-m-d'),
                'metricId' => $value->getMetricId(),
            ];
        }
        /** @var MetricValue[][] $exists */
        $exists = [];
        foreach ($this->findAllBy(Criteria::create()
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
        if (empty($exists)) {
            return [];
        }

        return array_merge(...array_values(array_map('array_values', $exists)));
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
}
