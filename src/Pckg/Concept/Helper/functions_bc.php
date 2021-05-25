<?php

use DebugBar\DebugBar;
use Pckg\Concept\Context;
use Pckg\Concept\Event\AbstractEvent;
use Pckg\Concept\Event\Dispatcher;
use Pckg\Concept\Helper;

if (!function_exists('context')) {
    /**
     * @return Context
     * @throws Exception
     */
    function context($key = null, $val = null)
    {
        return Helper\context($key, $val);
    }
}

if (!function_exists('measure')) {
    function measure($message, callable $callback = null, $limit = null)
    {
        return Helper\measure($message, $callback, $limit);
    }
}

if (!function_exists('startMeasure')) {
    function startMeasure($name)
    {
        Helper\startMeasure($name);
    }
}

if (!function_exists('stopMeasure')) {
    function stopMeasure($name)
    {
        Helper\stopMeasure($name);
    }
}

if (!function_exists('debugBar')) {
    /**
     * @return DebugBar
     */
    function debugBar()
    {
        return Helper\debugBar();
    }
}

if (!function_exists('is_only_callable')) {
    function is_only_callable($input)
    {
        return Helper\is_only_callable($input);
    }
}

if (!function_exists('dispatcher')) {
    /**
     *
     * @return Dispatcher
     * */
    function dispatcher()
    {
        return Helper\dispatcher();
    }
}

if (!function_exists('trigger')) {
    /**
     * @param       $event
     * @param null $method
     * @param array $args
     *
     * @return mixed|null|object
     */
    function trigger($event, $args = [], $method = null)
    {
        return Helper\trigger($event, $args, $method);
    }
}

if (!function_exists('listen')) {
    /**
     *
     * @return Pckg\Concept\Event\Dispatcher
     * */
    function listen($event, $eventHandler)
    {
        return Helper\listen($event, $eventHandler);
    }
}

if (!function_exists('listenOnce')) {
    function listenOnce($event, $eventHandler)
    {
        return Helper\listenOnce($event, $eventHandler);
    }
}

if (!function_exists('registerEvent')) {
    function registerEvent(AbstractEvent $event)
    {
        return Helper\registerEvent($event);
    }
}

if (!function_exists('triggerEvent')) {
    function triggerEvent($event, $args = [])
    {
        return Helper\triggerEvent($event, $args);
    }
}

if (!function_exists('object_implements')) {
    function object_implements($object, $interface)
    {
        return Helper\object_implements($object, $interface);
    }
}

if (!function_exists('report')) {
    function report(...$data)
    {
        Helper\report(...$data);
    }
}
