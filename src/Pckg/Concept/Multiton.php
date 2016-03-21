<?php

namespace Pckg\Concept;

trait Multiton
{

    protected static $inst = [];

    public static function getInstance($index = 0)
    {
        if (!isset(static::$inst[$index])) {
            throw new \Exception('Instance #' . $index . ' ' . __CLASS__ . ' does not exist.');
        }

        return static::$inst[$index];
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