<?php

namespace Pckg\Concept\Event;

class Dispatcher
{

    protected $listeners = [];

    protected $events = [];

    protected $triggered = [];

    public function listen($event, $eventHandler)
    {
        $this->listeners[$this->getEventName($event)][] = $eventHandler;

        return $this;
    }

    public function registerEvent(AbstractEvent $event)
    {
        $this->events[$event->getName()] = $event;

        return $this;
    }

    public function hasListeners($event)
    {
        return isset($this->listeners[$this->getEventName($event)]);
    }

    public function getListeners($event)
    {
        return $this->listeners[$this->getEventName($event)];
    }

    protected function getEventName($event)
    {
        if (is_string($event)) {
            return $event;

        } else if ($event instanceof AbstractEvent) {
            return $event->getName();

        }

        $finalEvent = [];
        foreach ($event AS $e) {
            if (is_object($e)) {
                $finalEvent[] = get_class($e);
                $finalEvent[] = spl_object_hash($e);
            } else {
                $finalEvent[] = $e;
            }
        }

        $finalEvent = implode('.', $finalEvent);

        return $finalEvent;
    }

    public function isTriggered($event, $num = 0)
    {
        $eventName = $this->getEventName($event);

        return isset($this->triggers[$eventName]) && $this->triggers[$eventName] >= $num;
    }

    public function trigger($event, $method = null, array $args = [])
    {
        $eventName = $this->getEventName($event);

        $handlers = array_merge(
            isset($this->events[$eventName])
                ? $this->events[$eventName]->getEventHandlers()
                : [],
            isset($this->listeners[$eventName])
                ? $this->listeners[$eventName]
                : []
        );

        $this->triggers[$eventName] = isset($this->triggers[$eventName])
            ? $this->triggers[$eventName] + 1
            : 1;

        if (!$handlers) {
            return null;
        }

        $result = chain($handlers, 'handle', $args);

        return $result;
    }

    public function destroy($event)
    {
        if (isset($this->listeners[$event])) {
            unset($this->listeners[$event]);
        }

        if (isset($this->events[$event])) {
            unset($this->events[$event]);
        }

        if (isset($this->triggers[$event])) {
            unset($this->triggers[$event]);
        }

        return null;
    }

}