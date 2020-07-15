<?php

declare(strict_types=1);

namespace winwin\metric\application;

use winwin\metric\DatabaseTestCaseTrait;
use winwin\metric\servant\MetricCriteria;
use winwin\metric\servant\MetricServant;
use winwin\metric\TestCase;

class QueryTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function testQuery()
    {
        $metricServant = $this->getContainer()->get(MetricServant::class);
        $criteria = new MetricCriteria();
        $criteria->metrics = [
            metric('a1', 'count'),
        ];
        $criteria->startDate = '2020-06-01';
        $criteria->endDate = '2020-06-02';
        $ret = $metricServant->query($criteria);
        $data = json_decode(json_encode($ret), true);
        $this->assertEquals([
            [
                'metric' => [
                    'scopeId' => 'a1',
                    'name' => 'count',
                    'tags' => [],
                ],
                'values' => [
                    '2020-06-01' => '1.3',
                    '2020-06-02' => '3',
                ],
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
            'metric_value' => [
                [
                    'id' => '1',
                    'create_time' => '2020-07-15 15:05:05',
                    'update_time' => '2020-07-15 15:05:05',
                    'metric_id' => '1',
                    'biz_date' => '2020-06-01',
                    'value' => '1.3',
                ],
                [
                    'id' => '2',
                    'create_time' => '2020-07-15 15:05:05',
                    'update_time' => '2020-07-15 15:05:05',
                    'metric_id' => '1',
                    'biz_date' => '2020-06-02',
                    'value' => '3',
                ],
            ],
        ]);
    }
}
