<?php

namespace Test\Concept\Helper;

use Pckg\Framework\Test\Codeception\Cest;

class FunctionsCest
{
    use Cest;

    public function testContext()
    {
        $this->tester->assertEquals(context(), \Pckg\Concept\Helper\context());
    }

    public function testIsOnlyCallable()
    {
        $this->tester->assertTrue(is_only_callable(fn() => null));
        $this->tester->assertFalse(is_only_callable('strtolower'));
    }
}
