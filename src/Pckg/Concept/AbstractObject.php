<?php

namespace Pckg\Concept;

/**
 * Class AbstractObject
 *
 * @package Pckg\Concept
 */
abstract class AbstractObject
{
    /**
     * @var array
     */
    protected $args = [];

    /**
     * @var null
     */
    protected $value = null;

    /**
     * @var null
     */
    protected $validator = null;

    /**
     * @var string
     */
    protected $return = 'firstObject';

    abstract public function getElement();

    /**
     * @param string $method
     * @param array $args
     *
     * @return string
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        if (!is_string($this->return)) {
            return $this->return;
        } else if (method_exists($this, 'handleReturn' . ucfirst($this->return))) {
            return $this->{'handleReturn' . ucfirst($this->return)}($method, $args);
        }

        $up = new \Exception('No return method for final call on chaining');
        throw $up;
        // it's not funny it all ...
    }

    /**
     * @return $this
     */
    public function setReturn($return)
    {
        $this->return = $return;

        return $this;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param array $args
     *
     * @return $this
     */
    public function setArgs($args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * @return null
     */
    public function getArg(string $key, $default = null)
    {
        return isset($this->args[$key])
            ? $this->args[$key]
            : $default;
    }

    /**
     * @return $this
     */
    public function setArg(string $key, $value)
    {
        $this->args[$key] = $value;

        return $this;
    }

    /**
     * @return bool|mixed
     */
    protected function handleReturnFirstObject($method, $args)
    {
        foreach ($args as $arg) {
            if (is_object($arg)) {
                if ($arg instanceof AbstractObject) {
                    return $arg->getElement();
                }

                return $arg; // return first object so chaining is available =)
            }
        }

        return true;
    }

    /**
     * @return bool|mixed
     */
    protected function handleReturnResult($method, $args)
    {
        return $this->return;
    }

    /**
     * @return true
     */
    protected function handleReturnTrue()
    {
        return true;
    }

    /**
     * @return false
     */
    protected function handleReturnFalse()
    {
        return false;
    }

    /**
     * @return null
     */
    protected function handleReturnNull()
    {
        return null;
    }
}
