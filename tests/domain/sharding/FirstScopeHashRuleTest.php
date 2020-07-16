<?php

declare(strict_types=1);

namespace winwin\metric\domain\sharding;

use PHPUnit\Framework\TestCase;

class FirstScopeHashRuleTest extends TestCase
{
    public function testNumericScope()
    {
        $rule = new FirstScopeHashRule(32);
        $this->assertEquals(18, $rule->getPartition(['scope_id' => 'client1234_branch4321']));
        $this->assertEquals(3, $rule->getPartition(['scope_id' => 'global']));
    }
}
