<?php

declare(strict_types=1);

use winwin\metric\servant\Metric;
use winwin\metric\servant\MetricCriteria;
use winwin\metric\servant\MetricSeries;

function metric(string $scopeId, string $name, array $tags = []): Metric
{
    $metric = new Metric();
    $metric->scopeId = $scopeId;
    $metric->name = $name;
    $metric->tags = $tags;

    return $metric;
}

function series(Metric $metric, array $values): MetricSeries
{
    $series = new MetricSeries();
    $series->metric = $metric;
    $series->values = $values;

    return $series;
}

function criteria(array $metrics, string $startDate, ?string $endDate = null): MetricCriteria
{
    $criteria = new MetricCriteria();
    $criteria->metrics = $metrics;
    $criteria->startDate = $startDate;
    $criteria->endDate = $endDate;

    return $criteria;
}
