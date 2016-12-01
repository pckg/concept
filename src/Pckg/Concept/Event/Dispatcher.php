<?php

namespace Pckg\Concept\Event;

use Pckg\Concept\Reflect;

class Dispatcher
{

    protected $listeners = [];

    protected $events = [];

    protected $triggered = [];

    public function listen($event, $eventHandler)
    {
        $hash = !is_string($eventHandler) ? spl_object_hash($eventHandler) : sha1($eventHandler);
        $this->listeners[$this->getEventName($event)][$hash] = $eventHandler;

        return $hash;
    }

    public function ignore($event, $eventHandlerKey)
    {
        foreach ($this->listeners[$this->getEventName($event)] ?? [] as $key => $handler) {
            if ($key == $eventHandlerKey) {
                unset($this->listeners[$this->getEventName($event)][$key]);
            }
        }

        return $this;
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

    public function isTriggered($event, $num = 0)
    {
        $eventName = $this->getEventName($event);

        return isset($this->triggers[$eventName]) && $this->triggers[$eventName] >= $num;
    }

    public function trigger($event, array $args = [], $method = null)
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

        /**
         * Handlers are not chained anymore.
         * They are interrupted only if handler returns false.
         */
        foreach ($handlers as $handler) {
            if (is_string($handler)) {
                $handler = Reflect::create($handler, $args);
            }

            if (is_callable($handler)) {
                Reflect::call($handler, $args);
            } else if (is_object($handler)) {
                Reflect::method($handler, 'handle', $args);
            }
        }

        return $this;
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