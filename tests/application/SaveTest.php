<?php

declare(strict_types=1);

namespace winwin\metric\application;

use winwin\metric\DatabaseTestCaseTrait;
use winwin\metric\servant\MetricServant;
use winwin\metric\TestCase;

class SaveTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function testSave()
    {
        $metricServant = $this->getContainer()->get(MetricServant::class);
        $metricServant->save([
            series(metric('a1', 'count'), [
                '2020-06-01' => 1.3,
                '2020-06-02' => 3,
            ]),
        ]);
        $this->assertTableRowCount('metric', 1);
        $this->assertTableRowCount('metric_value_01', 2);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataSet()
    {
        return $this->createArrayDataSet([
            'metric' => [],
            'metric_value' => [],
        ]);
    }
}
