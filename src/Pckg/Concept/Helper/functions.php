<?php

namespace Pckg\Concept\Helper {

    use DebugBar\DebugBar;
    use Exception;
    use Pckg\Concept\Context;
    use Pckg\Concept\Event\AbstractEvent;
    use Pckg\Concept\Event\Dispatcher;
    use Throwable;

    /**
     * @return Context
     * @throws Exception
     */
    function context($key = null, $val = null)
    {
        $context = Context::getInstance();

        if ($val) {
            return $context->bind($key, $val);
        } else if ($key) {
            return $context->get($key);
        }

        return $context;
    }

    function measure($message, callable $callback = null, $limit = null)
    {
        if ($message) {
            startMeasure($message);
        }
        $result = $callback ? $callback() : null;
        if ($message) {
            stopMeasure($message);
        }

        return $result;
    }

    function startMeasure($name)
    {
        if ($debugBar = debugBar()) {
            try {
                $debugBar['time']->startMeasure($name);
            } catch (Throwable $e) {
                // fail silently
            }
        }
    }

    function stopMeasure($name)
    {
        if ($debugBar = debugBar()) {
            try {
                $debugBar['time']->stopMeasure($name);
            } catch (Throwable $e) {
                // fail silently
            }
        }
    }

    /**
     * @return DebugBar
     */
    function debugBar()
    {
        return context()->exists(DebugBar::class)
            ? context()->get(DebugBar::class)
            : null;
    }

    function is_only_callable($input)
    {
        if (is_string($input)) {
            return false;
        }

        return is_callable($input);
    }

    /**
     *
     * @return Dispatcher
     * */
    function dispatcher()
    {
        return context()->getOrCreate(Dispatcher::class);
    }

    /**
     * @param       $event
     * @param null $method
     * @param array $args
     *
     * @return mixed|null|object
     */
    function trigger($event, $args = [], $method = null)
    {
        return dispatcher()->trigger($event, $args, $method);
    }

    /**
     *
     * @return Dispatcher
     * */
    function listen($event, $eventHandler)
    {
        return dispatcher()->listen($event, $eventHandler);
    }

    function listenOnce($event, $eventHandler)
    {
        if (dispatcher()->hasListener($event, $eventHandler)) {
            return;
        }

        return dispatcher()->listen($event, $eventHandler);
    }

    function registerEvent(AbstractEvent $event)
    {
        return dispatcher()->registerEvent($event);
    }

    function triggerEvent($event, $args = [])
    {
        return dispatcher()->trigger($event, $args, 'handle');
    }

    function object_implements($object, $interface)
    {
        return (is_object($object) || is_string($object))
            && class_exists(is_string($object) ? $object : get_class($object))
            && in_array($interface, class_implements($object));
    }

    function report(...$data)
    {
        foreach ($data as $val) {
            if (is_string($val)) {
                error_log(date('Y-m-d H:i:s') . ' ' . $val);
                continue;
            }

            if ($val instanceof Throwable) {
                error_log(date('Y-m-d H:i:s') . ' ' . 'EXCEPTION: ' . exception($val));
                continue;
            }
        }
    }
}
