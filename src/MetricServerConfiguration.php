<?php

declare(strict_types=1);

namespace winwin\metric;

use Aura\SqlQuery\QueryFactory;
use DI\Annotation\Inject;
use kuiper\db\ConnectionPoolInterface;
use kuiper\db\event\listener\AutoCreateShardTable;
use kuiper\db\event\ShardTableNotExistEvent;
use kuiper\db\sharding\Cluster;
use kuiper\db\sharding\ClusterInterface;
use kuiper\db\sharding\rule\EqualToRule;
use kuiper\db\sharding\rule\IdentityRule;
use kuiper\db\sharding\rule\RuleInterface;
use kuiper\db\sharding\Strategy;
use kuiper\di\annotation\Bean;
use kuiper\di\annotation\Configuration;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use winwin\metric\domain\sharding\FirstScopeHashRule;

/**
 * @Configuration()
 */
class MetricServerConfiguration
{
    /**
     * @Bean()
     * @Inject({"dbOptions" = "application.database"})
     */
    public function databaseCluster(
        ConnectionPoolInterface $connectionPool,
        QueryFactory $queryFactory,
        EventDispatcherInterface $eventDispatcher,
        array $dbOptions): ClusterInterface
    {
        $eventDispatcher->addListener(ShardTableNotExistEvent::class, new AutoCreateShardTable());
        $cluster = new Cluster([$connectionPool], $queryFactory, $eventDispatcher);

        $metricValueTableStrategy = new Strategy();
        $metricValueTableStrategy->setDbRule(new IdentityRule(0));
        $metricValueTableStrategy->setTableRule(new EqualToRule('sharding'));
        $cluster->setTableStrategy(($dbOptions['table-prefix'] ?? '').'metric_value', $metricValueTableStrategy);

        return $cluster;
    }

    /**
     * @Bean("MetricShardingRule")
     * @Inject({"buckets": "application.sharding.buckets"})
     */
    public function metricShardingRule(?int $buckets): RuleInterface
    {
        return new FirstScopeHashRule($buckets ?? 64);
    }
}
