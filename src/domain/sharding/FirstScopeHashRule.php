<?php

declare(strict_types=1);

namespace winwin\metric\domain\sharding;

use kuiper\db\sharding\rule\StringHashRule;

/**
 * 对 scope_id 切分，取第一部分计算分表数
 * 如果以数字结束，则按数字取模，其他情况使用 crc32 函数计算 hash 后取模
 * 例如当 bucket 为32时：
 *  scope_id 为 client1234_branch4321 计算结果为 1234 % 32 = 18
 *  scope_id 为 global  计算结果为 crc32('global') % 32 = 3.
 */
class FirstScopeHashRule extends StringHashRule
{
    /**
     * @var string
     */
    private $delimiter;

    public function __construct(int $bucket, string $delimiter = '_')
    {
        parent::__construct('scope_id', $bucket);
        $this->delimiter = $delimiter;
    }

    protected function getPartitionFor($value)
    {
        $first = explode($this->delimiter, $value, 2)[0];
        if (preg_match('/(\d+)$/', $first, $matches)) {
            return $matches[1] % $this->bucket;
        }

        return parent::getPartitionFor($first);
    }
}
