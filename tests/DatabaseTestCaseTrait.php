<?php

declare(strict_types=1);

namespace winwin\metric;

use PHPUnit\DbUnit\TestCaseTrait;

trait DatabaseTestCaseTrait
{
    use TestCaseTrait {
        setUp as dbSetUp;
        tearDown as dbTearDown;
    }

    protected function setUp(): void
    {
        $this->dbSetUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->dbTearDown();
    }
}
