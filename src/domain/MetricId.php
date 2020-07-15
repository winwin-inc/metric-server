<?php

declare(strict_types=1);

namespace winwin\metric\domain;

use kuiper\db\annotation\Embeddable;

/**
 * @Embeddable()
 */
class MetricId
{
    /**
     * @var string
     */
    private $scopeId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $tags;

    /**
     * MetricId constructor.
     */
    public function __construct(string $scopeId, string $name, string $tags)
    {
        $this->scopeId = $scopeId;
        $this->name = $name;
        $this->tags = $tags;
    }

    public function getScopeId(): string
    {
        return $this->scopeId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTags(): string
    {
        return $this->tags;
    }

    public function getNaturalId(): string
    {
        return implode(',', [$this->scopeId, $this->name, $this->tags]);
    }
}
