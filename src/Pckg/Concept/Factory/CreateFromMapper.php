<?php

namespace Pckg\Concept\Factory;

use LFW\Reflect;

trait CreateFromMapper
{

    protected $mapper;

    public function canCreate($key)
    {
        return isset($this->mapper[$key]);
    }

    public function create($key, $params = [])
    {
        if (is_array($key)) {
            return $this->createArray($key, $params);
        }

        if (!$this->canCreate($key)) {
            if (class_exists($key)) {
                return Reflect::create($key, $params);
            }

            throw new \Exception($key . " isn't mapped in " . static::CLASS);
        }

        return Reflect::create($this->mapper[$key], $params);
    }

    public function createArray($arrClasses, $params = [])
    {
        foreach ($arrClasses AS &$class) {
            $class = $this->create($class, $params);
        }

        return $arrClasses;
    }

}