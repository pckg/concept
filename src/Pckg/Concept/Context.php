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
     * @var array
     */
    protected $whenRequested = [];

    /**
     * @return $this
     */
    public function whenRequested($service, callable $callback)
    {
        $this->whenRequested[$service] = $callback;

        return $this;
    }

    /**
     * @return array
     */
    public function getWhenRequested()
    {
        return $this->whenRequested;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return $this
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
     * @return mixed
     * @throws Exception
     */
    public function getOrCreate($class, $args = [], callable $callback = null, callable $create = null)
    {
        if (!$this->exists($class)) {
            $object = $create ? $create($class, $args) : Reflect::create($class, $args);
            $this->bind($class, $object);
            /**
             * Used for calling other methods like boot(), register(), ...
             */
            if ($callback) {
                $callback($object);
            }
        }

        return $this->data[$class];
    }

    /**
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
     * @return $this
     */
    public function bind($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function unbind($key)
    {
        unset($this->data[$key]);

        return $this;
    }

    /**
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
     * @return bool
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
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

    public function mock($object, callable $task, string $bind = null)
    {
        if (!$bind) {
            $bind = get_class($object);
        }

        $original = context()->get($bind);
        context()->bind($bind, $object);

        try {
            $response = $task($object, $original);
        } catch (\Throwable $e) {
            context()->bind($bind, $original);
            ddd(exception($e));
            return;
        }

        context()->bind($bind, $original);

        return $response;
    }
}
