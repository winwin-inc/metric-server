<?php

declare(strict_types=1);

namespace winwin\metric\domain;

use kuiper\db\annotation\CreationTimestamp;
use kuiper\db\annotation\GeneratedValue;
use kuiper\db\annotation\Id;
use kuiper\db\annotation\NaturalId;
use kuiper\db\annotation\UpdateTimestamp;

class Metric
{
    /**
     * @Id
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @CreationTimestamp()
     *
     * @var \DateTime
     */
    private $createTime;

    /**
     * @UpdateTimestamp()
     *
     * @var \DateTime
     */
    private $updateTime;

    /**
     * @NaturalId()
     *
     * @var MetricId
     */
    private $metricId;

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

    public function getMetricId(): MetricId
    {
        return $this->metricId;
    }

    public function setMetricId(MetricId $metricId): void
    {
        $this->metricId = $metricId;
    }

    public function getScopeId(): string
    {
        return $this->metricId->getScopeId();
    }

    public function getName(): string
    {
        return $this->metricId->getName();
    }

    public function getTags(): string
    {
        return $this->metricId->getTags();
    }

    public function getNaturalId(): string
    {
        return $this->metricId->getNaturalId();
    }
}
