<?php

declare(strict_types=1);

namespace winwin\metric\application;

use winwin\metric\DatabaseTestCaseTrait;
use winwin\metric\servant\MetricAggCriteria;
use winwin\metric\servant\MetricServant;
use winwin\metric\servant\TimeAggregation;
use winwin\metric\TestCase;

class AggregateQueryTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function testAggQuery()
    {
        $metricServant = $this->getContainer()->get(MetricServant::class);
        $criteria = new MetricAggCriteria();
        $criteria->scopePattern = 'a1';
        $criteria->name = 'count';
        $criteria->startDate = '2020-06-01';
        $criteria->endDate = '2020-06-02';
        $criteria->timeAggregation = TimeAggregation::fromValue(TimeAggregation::MONTHLY);
        $ret = $metricServant->aggregateQuery($criteria);
        $data = json_decode(json_encode($ret), true);
        // var_export($data);
        $this->assertEquals([
            'metric' => [
                'scopeId' => 'a1',
                'name' => 'count',
                'tags' => null,
            ],
            'values' => [
                '2020-06-01' => '4.3',
            ],
        ], $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataSet()
    {
        return $this->createArrayDataSet([
            'metric' => [
                [
                    'id' => '1',
                    'create_time' => '2020-07-15 15:05:05',
                    'update_time' => '2020-07-15 15:05:05',
                    'scope_id' => 'a1',
                    'name' => 'count',
                    'tags' => '',
                ],
            ],
            'metric_value_01' => [
                [
                    'id' => '1',
                    'create_time' => '2020-07-15 15:05:05',
                    'update_time' => '2020-07-15 15:05:05',
                    'metric_id' => '1',
                    'biz_date' => '2020-06-01',
                    'sharding' => 1,
                    'value' => '1.3',
                ],
                [
                    'id' => '2',
                    'create_time' => '2020-07-15 15:05:05',
                    'update_time' => '2020-07-15 15:05:05',
                    'metric_id' => '1',
                    'biz_date' => '2020-06-02',
                    'sharding' => 1,
                    'value' => '3',
                ],
            ],
        ]);
    }
}
