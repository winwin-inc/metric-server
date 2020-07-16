<?php

declare(strict_types=1);

namespace winwin\metric\domain;

use DateTime;

interface MetricValueRepositoryInterface
{
    /**
     * @param Metric[] $metrics
     *
     * @return MetricValue[]
     */
    public function findAllBetween(array $metrics, DateTime $startDate, ?DateTime $endDate = null): array;

    /**
     * @param Metric[] $metrics
     */
    public function deleteAllBetween(array $metrics, DateTime $startDate, ?DateTime $endDate = null): void;

    /**
     * @param MetricValue[] $values
     */
    public function saveAll(array $values): void;

    /**
     * @param MetricValue[] $values
     */
    public function increaseAll(array $values): void;
}
