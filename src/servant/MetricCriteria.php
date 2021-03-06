<?php

declare(strict_types=1);

/**
 * NOTE: This class is auto generated by Tars Generator (https://github.com/wenbinye/tars-generator).
 *
 * Do not edit the class manually.
 * Tars Generator version: 1.0-SNAPSHOT
 */

namespace winwin\metric\servant;

use wenbinye\tars\protocol\annotation\TarsProperty;

final class MetricCriteria
{
    /**
     * @TarsProperty(order = 0, required = true, type = "vector<Metric>")
     *
     * @var array
     */
    public $metrics;

    /**
     * @TarsProperty(order = 1, required = true, type = "string")
     *
     * @var string
     */
    public $startDate;

    /**
     * @TarsProperty(order = 2, required = false, type = "string")
     *
     * @var string
     */
    public $endDate;
}
