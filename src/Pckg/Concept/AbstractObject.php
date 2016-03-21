<?php

namespace Pckg\Concept;

/**
 * Class AbstractObject
 * @package Pckg\Concept
 */
class AbstractObject
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

    /**
     * @param $method
     * @param $args
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
     * @param $return
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
     * @param $args
     * @return $this
     */
    public function setArgs($args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * @param      $key
     * @param null $default
     * @return null
     */
    public function getArg($key, $default = null)
    {
        return isset($this->args[$key])
            ? $this->args[$key]
            : $default;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setArg($key, $value)
    {
        $this->args[$key] = $value;

        return $this;
    }

    /**
     * @param $method
     * @param $args
     * @return bool|null
     */
    protected function handleReturnFirstObject($method, $args)
    {
        foreach ($args AS $arg) {
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
     * @param $method
     * @param $args
     * @return bool|null
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