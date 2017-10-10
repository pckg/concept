<?php

use DebugBar\DebugBar;
use Pckg\Concept\Context;

/**
 * @return Context
 * @throws Exception
 */
if (!function_exists('context')) {
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
    function measure($message, callable $callback)
    {
        startMeasure($message);
        $result = $callback();
        stopMeasure($message);

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