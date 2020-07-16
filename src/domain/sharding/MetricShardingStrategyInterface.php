<?php

declare(strict_types=1);

namespace winwin\metric\domain\sharding;

use winwin\metric\domain\Metric;

interface MetricShardingStrategyInterface
{
    public function getSharding(Metric $metric): int;
}
