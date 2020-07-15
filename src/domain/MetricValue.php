<?php

declare(strict_types=1);

namespace winwin\metric\domain;

use kuiper\db\annotation\CreationTimestamp;
use kuiper\db\annotation\GeneratedValue;
use kuiper\db\annotation\Id;
use kuiper\db\annotation\NaturalId;
use kuiper\db\annotation\UpdateTimestamp;

class MetricValue
{
    /**
     * @Id
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @CreationTimestamp
     *
     * @var \DateTime
     */
    private $createTime;

    /**
     * @UpdateTimestamp
     *
     * @var \DateTime
     */
    private $updateTime;

    /**
     * @var int
     * @NaturalId()
     */
    private $metricId;

    /**
     * @var \DateTime
     * @NaturalId()
     */
    private $bizDate;

    /**
     * @var float
     */
    private $value;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return static
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * @param \DateTime $createTime
     *
     * @return static
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

    /**
     * @param \DateTime $updateTime
     *
     * @return static
     */
    public function setUpdateTime($updateTime)
    {
        $this->updateTime = $updateTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getMetricId()
    {
        return $this->metricId;
    }

    /**
     * @param int $metricId
     *
     * @return static
     */
    public function setMetricId($metricId)
    {
        $this->metricId = $metricId;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBizDate()
    {
        return $this->bizDate;
    }

    /**
     * @param \DateTime $bizDate
     *
     * @return static
     */
    public function setBizDate($bizDate)
    {
        $this->bizDate = $bizDate;

        return $this;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     *
     * @return static
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getDateString(): string
    {
        return $this->bizDate->format('Y-m-d');
    }
}
