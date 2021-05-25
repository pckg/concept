<?php

namespace Test\Concept;

use Pckg\Concept\Context;
use Pckg\Framework\Test\Codeception\Cest;

class ContextCest extends Cest
{

    public function testContext()
    {
        $this->tester->assertEquals(context(), context());
    }
}
