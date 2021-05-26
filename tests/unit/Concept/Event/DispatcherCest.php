<?php

namespace Test\Concept\Event;

use Pckg\Concept\Event\Dispatcher;
use Pckg\Framework\Test\Codeception\Cest;

class DispatcherCest
{
    use Cest;

    public function testCanListenAsCallback()
    {
        $dispatcher = new Dispatcher();
        $this->tester->assertFalse($dispatcher->hasListeners('someEvent'));

        $hash = $dispatcher->listen('someEvent', fn() => null);
        $this->tester->assertTrue($dispatcher->hasListeners('someEvent'));

        $dispatcher->ignore('someEvent', $hash);
        $this->tester->assertFalse($dispatcher->hasListeners('someEvent'));
    }
}
