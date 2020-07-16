<?php

declare(strict_types=1);

namespace winwin\metric\application;

use winwin\metric\DatabaseTestCaseTrait;
use winwin\metric\servant\MetricServant;
use winwin\metric\servant\MetricTagCriteria;
use winwin\metric\TestCase;

class QueryByTagTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function testQueryByTag()
    {
        $metricServant = $this->getContainer()->get(MetricServant::class);
        $criteria = new MetricTagCriteria();
        $criteria->scopeId = 'a1';
        $criteria->name = 'count';
        $criteria->tagPatterns = [
            'cashier' => '.*',
        ];
        $criteria->startDate = '2020-06-01';
        $criteria->endDate = '2020-06-02';
        $ret = $metricServant->queryByTag($criteria);
        $data = json_decode(json_encode($ret), true);
        $this->assertEquals([
            [
                'metric' => [
                    'scopeId' => 'a1',
                    'name' => 'count',
                    'tags' => ['cashier' => 'john', 'pay_by' => 'alipay'],
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
                    'tags' => 'cashier=john&pay_by=alipay',
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
