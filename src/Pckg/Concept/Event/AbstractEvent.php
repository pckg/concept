<?php

namespace Pckg\Concept\Event;

use Pckg\Concept\ChainOfResponsibility\Next;

abstract class AbstractEvent
{
    use Next;

    protected array $handlers = [];

    protected string $name;

    protected array $eventData = [];

    public function getName()
    {
        return $this->name ?? null;
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

    public function setEventData(array $data)
    {
        $this->eventData = $data;

        return $this;
    }

    public function getEventData()
    {
        return $this->eventData;
    }

    public function handle()
    {
        chain($this->handlers, 'handle', $this->getEventData());
    }
}
