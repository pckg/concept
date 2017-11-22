<?php

namespace Pckg\Concept\Event;

use Pckg\Concept\ChainOfResponsibility\Next;

abstract class AbstractEvent
{

    use Next;

    protected $handlers = [];

    protected $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getEventHandlers()
    {
        return $this->handlers;
    }

    public function addEventHandler($handler)
    {
        $this->handlers[] = $handler;

        return $this;
    }

    public function getEventData()
    {
        return [];
    }

    public function handle()
    {
        chain($this->handlers, 'handle', $this->getEventData());
    }

}