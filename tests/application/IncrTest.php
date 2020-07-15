<?php

declare(strict_types=1);

namespace winwin\metric\application;

use winwin\metric\DatabaseTestCaseTrait;
use winwin\metric\servant\MetricServant;
use winwin\metric\TestCase;

class IncrTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function testIncr()
    {
        $metricServant = $this->getContainer()->get(MetricServant::class);
        $metricServant->incr([
            series(metric('a1', 'count'), [
                '2020-06-01' => 1.3,
                '2020-06-02' => 3,
            ]),
        ]);
        $seriesList = $metricServant->query(criteria([metric('a1', 'count')], '2020-06-01'));
        $this->assertEquals(2.6, $seriesList[0]->values['2020-06-01']);
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
