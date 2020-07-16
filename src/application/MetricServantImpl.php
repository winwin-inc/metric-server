<?php

declare(strict_types=1);

namespace winwin\metric\application;

use Carbon\Carbon;
use kuiper\db\Criteria;
use kuiper\di\annotation\Service;
use kuiper\helper\Arrays;
use winwin\metric\domain\Metric;
use winwin\metric\domain\MetricId;
use winwin\metric\domain\MetricRepositoryInterface;
use winwin\metric\domain\MetricValue;
use winwin\metric\domain\MetricValueRepositoryInterface;
use winwin\metric\servant\MetricSeries;
use winwin\metric\servant\MetricServant;
use winwin\metric\servant\TimeAggregation;

/**
 * @Service()
 */
class MetricServantImpl implements MetricServant
{
    /**
     * @var MetricRepositoryInterface
     */
    private $metricRepository;

    /**
     * @var MetricValueRepositoryInterface
     */
    private $metricValueRepository;

    /**
     * @var TagSerializer
     */
    private $tagSerializer;

    public function __construct(
        MetricRepositoryInterface $metricRepository,
        MetricValueRepositoryInterface $metricValueRepository,
        TagSerializer $tagSerializer)
    {
        $this->metricRepository = $metricRepository;
        $this->metricValueRepository = $metricValueRepository;
        $this->tagSerializer = $tagSerializer;
    }

    /**
     * {@inheritdoc}
     *
     * @param MetricSeries[] $seriesList
     */
    public function save($seriesList): void
    {
        $this->metricValueRepository->saveAll($this->createMetricValues($seriesList));
    }

    /**
     * {@inheritdoc}
     */
    public function incr($seriesList): void
    {
        $this->metricValueRepository->increaseAll($this->createMetricValues($seriesList));
    }

    /**
     * {@inheritdoc}
     */
    public function query($criteria): array
    {
        $metrics = $this->metricRepository->findAllByMetricId($this->toMetricIdList($criteria->metrics));

        return $this->queryMetricValues($metrics, $criteria->startDate, $criteria->endDate);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($criteria): void
    {
        $metrics = $this->metricRepository->findAllByMetricId($this->toMetricIdList($criteria->metrics));
        $this->metricValueRepository->deleteAllBetween(
            $metrics,
            Carbon::parse($criteria->startDate),
            $criteria->endDate ? Carbon::parse($criteria->endDate) : null
        );
    }

    /**
     * {@inheritdoc}
     */
    public function queryByTag($criteria): array
    {
        $metricCriteria = Criteria::create([
            'metricId.scopeId' => $criteria->scopeId,
            'metricId.name' => $criteria->name,
        ]);
        $regexp = $this->buildTagRegexp($criteria->tagPatterns);
        if ($criteria->matchAll) {
            $metricCriteria->where('metricId.tags', '^'.$regexp.'$', 'RLIKE');
        } else {
            $metricCriteria->where('metricId.tags', '[[:<:]]'.$regexp, 'RLIKE');
        }
        $metrics = $this->metricRepository->findAllBy($metricCriteria);

        return $this->queryMetricValues($metrics, $criteria->startDate, $criteria->endDate);
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateQuery($criteria): MetricSeries
    {
        $metricCriteria = Criteria::create([
            'metricId.name' => $criteria->name,
            'metricId.tags' => $this->tagSerializer->serialize($criteria->tags),
        ])->where('metricId.scopeId', $criteria->scopePattern, 'RLIKE');

        $series = new MetricSeries();
        $metricDto = new \winwin\metric\servant\Metric();
        $metricDto->name = $criteria->name;
        $metricDto->scopeId = $criteria->scopePattern;
        $metricDto->tags = $criteria->tags;
        $series->metric = $metricDto;
        $series->values = [];

        $metrics = $this->metricRepository->findAllBy($metricCriteria);
        $values = $this->metricValueRepository->findAllBetween(
            $metrics,
            Carbon::parse($criteria->startDate),
            $criteria->endDate ? Carbon::parse($criteria->endDate) : null
        );
        foreach ($values as $value) {
            $date = $this->getAggDate($value->getBizDate(), $criteria->timeAggregation)
                ->format('Y-m-d');
            if (isset($series->values[$date])) {
                $series->values[$date] += $value->getValue();
            } else {
                $series->values[$date] = $value->getValue();
            }
        }

        return $series;
    }

    /**
     * @param Metric[] $metrics
     *
     * @return MetricSeries[]
     */
    private function queryMetricValues(array $metrics, string $startDate, ?string $endDate): array
    {
        $values = $this->metricValueRepository->findAllBetween(
            $metrics, Carbon::parse($startDate), $endDate ? Carbon::parse($endDate) : null);
        $metricValuesById = Arrays::groupBy($values, 'metricId');
        $seriesList = [];
        foreach ($metrics as $metric) {
            $series = new MetricSeries();
            $series->metric = $this->toMetricDto($metric);
            $metricValues = $metricValuesById[$metric->getId()] ?? [];
            $series->values = array_combine(
                Arrays::pull($metricValues, 'dateString'),
                Arrays::pull($metricValues, 'value')
            );
            $seriesList[] = $series;
        }

        return $seriesList;
    }

    /**
     * @param MetricSeries[] $seriesList
     *
     * @return MetricValue[]
     */
    private function createMetricValues(array $seriesList): array
    {
        $metricDtoList = Arrays::pullField($seriesList, 'metric');
        $metricIds = $this->toMetricIdList($metricDtoList);
        $metrics = $this->metricRepository->findAllAndCreateMissing($metricIds);
        $values = [];
        foreach ($seriesList as $series) {
            $metric = $metrics[$this->toMetricId($series->metric)->getNaturalId()];
            foreach ($series->values as $date => $value) {
                $values[] = MetricValue::create($metric, Carbon::parse($date), $value);
            }
        }

        return $values;
    }

    private function toMetricId(\winwin\metric\servant\Metric $metricDto): MetricId
    {
        return new MetricId($metricDto->scopeId, $metricDto->name,
            $this->tagSerializer->serialize($metricDto->tags ?? []));
    }

    /**
     * @param \winwin\metric\servant\Metric[] $metricDtoList
     *
     * @return MetricId[]
     */
    private function toMetricIdList(array $metricDtoList): array
    {
        $metricIds = [];
        foreach ($metricDtoList as $metricDto) {
            /** @var \winwin\metric\servant\Metric $metricDto */
            $metricId = $this->toMetricId($metricDto);
            $metricIds[$metricId->getNaturalId()] = $metricId;
        }

        return $metricIds;
    }

    private function toMetricDto(Metric $metric): \winwin\metric\servant\Metric
    {
        $metricDto = new \winwin\metric\servant\Metric();
        $metricDto->scopeId = $metric->getScopeId();
        $metricDto->name = $metric->getName();
        $metricDto->tags = $this->tagSerializer->deserialize($metric->getTags());

        return $metricDto;
    }

    private function buildTagRegexp(array $tagPatterns): string
    {
        ksort($tagPatterns);
        $ret = [];
        foreach ($tagPatterns as $tag => $pattern) {
            $ret[] = $tag.'='.$pattern;
        }

        return implode('&', $ret);
    }

    private function getAggDate(\DateTime $bizDate, ?TimeAggregation $timeAggregation): \DateTime
    {
        if (!$timeAggregation) {
            return $bizDate;
        }
        switch ($timeAggregation->value) {
            case TimeAggregation::NONE:
                return $bizDate;
            case TimeAggregation::WEEKLY:
                return Carbon::instance($bizDate)->startOfWeek();
            case TimeAggregation::MONTHLY:
                return Carbon::instance($bizDate)->startOfMonth();
            case TimeAggregation::YEARLY:
                return Carbon::instance($bizDate)->startOfYear();
            default:
                throw new \InvalidArgumentException('Unknown TimeAggregation '.$timeAggregation->name);
        }
    }
}
