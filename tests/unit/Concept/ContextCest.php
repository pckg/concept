<?php

namespace Test\Concept;

use Pckg\Concept\Context;
use Pckg\Framework\Test\Codeception\Cest;

class ContextCest
{
    use Cest;

    public function testContext()
    {
        $this->tester->assertEquals(context(), context());
    }
}
