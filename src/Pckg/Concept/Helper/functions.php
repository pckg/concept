<?php

use DebugBar\DebugBar;
use Pckg\Concept\Context;
use Pckg\Concept\Event\AbstractEvent;
use Pckg\Concept\Event\Dispatcher;

if (!function_exists('context')) {
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
}

if (!function_exists('measure')) {
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
}

if (!function_exists('startMeasure')) {
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
}

if (!function_exists('stopMeasure')) {
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
}

if (!function_exists('debugBar')) {
    /**
     * @return DebugBar
     */
    function debugBar()
    {
        return context()->exists(DebugBar::class)
            ? context()->get(DebugBar::class)
            : null;
    }
}

if (!function_exists('is_only_callable')) {
    function is_only_callable($input)
    {
        if (is_string($input)) {
            return false;
        }

        return is_callable($input);
    }
}

if (!function_exists('dispatcher')) {
    /**
     *
     * @return Dispatcher
     * */
    function dispatcher()
    {
        return context()->getOrCreate(Dispatcher::class);
    }
}

if (!function_exists('trigger')) {
    /**
     * @param       $event
     * @param null  $method
     * @param array $args
     *
     * @return mixed|null|object
     */
    function trigger($event, $args = [], $method = null)
    {
        return dispatcher()->trigger($event, $args, $method);
    }
}

if (!function_exists('listen')) {
    /**
     *
     * @return Pckg\Concept\Event\Dispatcher
     * */
    function listen($event, $eventHandler)
    {
        return dispatcher()->listen($event, $eventHandler);
    }
}

if (!function_exists('listenOnce')) {
    function listenOnce($event, $eventHandler)
    {
        if (dispatcher()->hasListener($event, $eventHandler)) {
            return;
        }

        return dispatcher()->listen($event, $eventHandler);
    }
}

if (!function_exists('registerEvent')) {
    function registerEvent(AbstractEvent $event)
    {
        return dispatcher()->registerEvent($event);
    }
}

if (!function_exists('triggerEvent')) {
    function triggerEvent($event, $args = [])
    {
        return dispatcher()->trigger($event, $args, 'handle');
    }
}

if (!function_exists('object_implements')) {
    function object_implements($object, $interface)
    {
        return (is_object($object) || is_string($object))
            && class_exists(is_string($object) ? $object : get_class($object))
            && in_array($interface, class_implements($object));
    }
}

if (!function_exists('report')) {
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
