<?php

namespace Test\Concept\Event;

use Pckg\Concept\Event\Event;
use Pckg\Framework\Test\Codeception\Cest;

class EventCest
{
    use Cest;

    public function testDefaultEvent()
    {
        $event = new Event();
        $this->tester->assertNull($event->getName());
        $this->tester->assertEquals([], $event->getEventData());

        $event->setName('defaultName');
        $event->setEventData(['default' => 'data']);

        $this->tester->assertEquals('defaultName', $event->getName());
        $this->tester->assertEquals([], $event->getEventHandlers());
        $this->tester->assertEquals(['default' => 'data'], $event->getEventData());
    }
}
