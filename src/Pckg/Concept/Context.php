<?php

namespace Pckg\Concept;

use Exception;
use Pckg\Concept\Event\Dispatcher;

/**
 * Simple data collector.
 * Class Context
 *
 * @package Pckg
 */
class Context
{

    use Multiton, Mapper {
        Multiton::createInstance as parentCreateInstance;
        Multiton::getInstance as parentGetInstance;
    }

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return static
     */
    public static function createInstance()
    {
        $instance = static::parentCreateInstance();

        $instance->bind(Context::class, $instance);

        $instance->bind(Dispatcher::class, new Dispatcher());

        return $instance;
    }

    /**
     * @return Context
     * @throws Exception
     */
    public static function getInstance()
    {
        return static::parentGetInstance();
    }

    /**
     * @param       $key
     * @param       $class
     * @param array $args
     *
     * @return mixed
     * @throws Exception
     */
    public function getOrCreate($class, $args = [])
    {
        if (!$this->exists($class)) {
            $this->bind($class, Reflect::create($class, $args));
        }

        return $this->data[$class];
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!isset($this->data[$key])) {
            throw new Exception($key . " isn't set in Context!");
        }

        return $this->data[$key];
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getOrDefault($key, $default = null)
    {
        if (!array_key_exists($key, $this->data)) {
            return $default;
        }

        return $this->data[$key];
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function bind($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function bindIfNot($key, $value)
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @param $key
     *
     * @return mixed
     * @throws Exception
     */
    public function find($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        throw new Exception("Key $key not found in context");
    }

}