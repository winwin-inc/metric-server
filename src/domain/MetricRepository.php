<?php

declare(strict_types=1);

namespace winwin\metric\domain;

use kuiper\db\AbstractCrudRepository;
use kuiper\db\annotation\Entity;
use kuiper\db\Criteria;
use kuiper\di\annotation\Repository;
use kuiper\helper\Arrays;

/**
 * @Entity(Metric::class)
 * @Repository()
 *
 * @method Metric[] findAllBy($criteria = null) : array
 */
class MetricRepository extends AbstractCrudRepository
{
    public function findAllByMetricId(array $metricIds): array
    {
        return $this->findAllBy(Criteria::create()
            ->in('metricId', $metricIds)
            ->select('id', 'metricId.scopeId', 'metricId.name', 'metricId.tags'));
    }

    /**
     * @param MetricId[] $metricIds
     *
     * @return Metric[]
     */
    public function findAllAndCreateMissing(array $metricIds): array
    {
        $metrics = $this->findAllBy(Criteria::create()->in('metricId', $metricIds));
        /** @var Metric[] $metrics */
        $metrics = Arrays::assoc($metrics, 'naturalId');
        foreach ($metricIds as $naturalId => $metricId) {
            if (!isset($metrics[$naturalId])) {
                $metric = new Metric();
                $metric->setMetricId($metricId);
                $this->insert($metric);
                $metrics[$naturalId] = $metric;
            }
        }

        return $metrics;
    }
}
