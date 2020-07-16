<?php

declare(strict_types=1);

namespace winwin\metric\domain\sharding;

use kuiper\db\sharding\rule\RuleInterface;
use winwin\metric\domain\Metric;

class MetricShardingStrategy implements MetricShardingStrategyInterface
{
    /**
     * @var RuleInterface
     */
    private $rule;

    public function __construct(RuleInterface $rule)
    {
        $this->rule = $rule;
    }

    public function getSharding(Metric $metric): int
    {
        return $this->rule->getPartition([
            'scope_id' => $metric->getScopeId(),
            'name' => $metric->getName(),
            'tags' => $metric->getTags(),
        ]);
    }
}
