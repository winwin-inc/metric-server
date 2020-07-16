<?php

declare(strict_types=1);

namespace winwin\metric\domain;

interface MetricRepositoryInterface
{
    /**
     * @param MetricId[] $metricIds
     *
     * @return Metric[]
     */
    public function findAllByMetricId(array $metricIds): array;

    /**
     * @param MetricId[] $metricIds
     *
     * @return Metric[]
     */
    public function findAllAndCreateMissing(array $metricIds): array;
}
