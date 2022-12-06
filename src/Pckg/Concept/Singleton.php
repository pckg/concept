<?php

namespace Pckg\Concept;

trait Singleton
{
    protected static $inst = null;

    public static function getInstance()
    {
        if (!static::$inst) {
            static::$inst = static::createInstance();
        }

        return static::$inst;
    }

    public function createInstance()
    {
        return new static();
    }
}
