<?php

declare(strict_types=1);

/**
 * NOTE: This class is auto generated by Tars Generator (https://github.com/wenbinye/tars-generator).
 *
 * Do not edit the class manually.
 * Tars Generator version: 1.0-SNAPSHOT
 */

namespace winwin\metric\servant;

use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\annotation\TarsServant;

/**
 * @TarsServant(name="MetricObj")
 */
interface MetricServant
{
    /**
     * @TarsParameter(name = "seriesList", type = "vector<MetricSeries>")
     * @TarsReturnType(type = "void")
     *
     * @param array $seriesList
     *
     * @return void
     */
    public function save($seriesList);

    /**
     * @TarsParameter(name = "seriesList", type = "vector<MetricSeries>")
     * @TarsReturnType(type = "void")
     *
     * @param array $seriesList
     *
     * @return void
     */
    public function incr($seriesList);

    /**
     * @TarsParameter(name = "criteria", type = "MetricCriteria")
     * @TarsReturnType(type = "vector<MetricSeries>")
     *
     * @param \winwin\metric\servant\MetricCriteria $criteria
     *
     * @return array
     */
    public function query($criteria);

    /**
     * @TarsParameter(name = "criteria", type = "MetricCriteria")
     * @TarsReturnType(type = "void")
     *
     * @param \winwin\metric\servant\MetricCriteria $criteria
     *
     * @return void
     */
    public function delete($criteria);

    /**
     * @TarsParameter(name = "criteria", type = "MetricTagCriteria")
     * @TarsReturnType(type = "vector<MetricSeries>")
     *
     * @param \winwin\metric\servant\MetricTagCriteria $criteria
     *
     * @return array
     */
    public function queryByTag($criteria);

    /**
     * @TarsParameter(name = "criteria", type = "MetricAggCriteria")
     * @TarsReturnType(type = "MetricSeries")
     *
     * @param \winwin\metric\servant\MetricAggCriteria $criteria
     *
     * @return \winwin\metric\servant\MetricSeries
     */
    public function aggregateQuery($criteria);
}
