<?php

namespace Pckg\Concept;

trait Multiton
{
    protected static $inst = [];

    public static function getInstance()
    {
        if (!static::$inst) {
            static::createInstance();
        }

        return end(static::$inst);
    }

    public static function getInstances()
    {
        return static::$inst;
    }

    public static function createInstance()
    {
        $instance = new static();

        static::$inst[] = $instance;

        return $instance;
    }

    public function addInstance($instance)
    {
        static::$inst[] = $instance;

        return count(self::$inst) - 1;
    }
}
