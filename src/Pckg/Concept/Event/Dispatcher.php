<?php

namespace Pckg\Concept\Event;

use Pckg\Concept\Reflect;

class Dispatcher
{

    protected $listeners = [];

    protected $events = [];

    protected $triggered = [];

    protected $triggers = [];

    public function listen($event, $eventHandler)
    {
        if (!$eventHandler) {
            return;
        }

        $hash = !is_string($eventHandler) ? spl_object_hash($eventHandler) : sha1($eventHandler);
        $this->listeners[$this->getEventName($event)][$hash] = $eventHandler;

        return $hash;
    }

    public function ignore($event, $eventHandlerKey)
    {
        $eventName = $this->getEventName($event);
        foreach ($this->listeners[$eventName] ?? [] as $key => $handler) {
            if ($key === $eventHandlerKey) {
                unset($this->listeners[$eventName][$key]);
                if (!$this->listeners[$eventName]) {
                    unset($this->listeners[$eventName]);
                }
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
        foreach ($event as $e) {
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

    public function trigger($event, $args = [], $method = null)
    {
        $eventName = $this->getEventName($event);

        $handlers = array_merge(
            isset($this->events[$eventName])
                ? $this->events[$eventName]->getEventHandlers()
                : [],
            $this->listeners[$eventName] ?? []
        );

        $this->triggers[$eventName] = ($this->triggers[$eventName] ?? 0) + 1;

        if (!$handlers) {
            return null;
        }

        /**
         * Make args array.
         */
        if (!is_array($args)) {
            $args = [$args];
        }

        /**
         * Create event object.
         */
        $eventObj = new Event();
        $eventObj->setName($eventName);
        $args[] = $eventObj;

        /**
         * Handlers are not chained anymore.
         * They are interrupted only if handler returns false.
         */
        foreach ($handlers as $handler) {
            if (is_string($handler)) {
                $handler = Reflect::create($handler, $args);
            }

            if (is_only_callable($handler)) {
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
