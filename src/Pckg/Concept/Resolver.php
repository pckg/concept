<?php

namespace Pckg\Concept;

use Exception;

class Resolver
{
    protected $caller;

    protected $method;

    protected $name;

    protected $resolver;

    public function when($caller)
    {
        $this->caller = $caller;

        return $this;
    }

    public function hasCaller($caller)
    {
        if (!$this->caller) {
            return true;
        }

        return $this->caller == $caller;
    }

    public function calls($method)
    {
        $this->method = $method;

        return $this;
    }

    public function hasMethod($method)
    {
        if (!$this->method) {
            return true;
        }

        return $this->method == $method;
    }

    public function requests($name)
    {
        $this->name = $name;

        return $this;
    }

    public function hasName($name)
    {
        if (!$this->name) {
            return true;
        }

        return $this->name == $name;
    }

    public function provide($resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }

    public function canResolve($for, $method, $name)
    {
        return $this->hasCaller($for) && $this->hasMethod($method) && $this->hasName($name) && $this->resolver;
    }

    public function resolve($args = [])
    {
        if (is_string($this->resolver)) {
            return Reflect::create($this->resolver, is_array($args) ? $args : (is_only_callable($args) ? $args() : $args));
        } else if (is_only_callable($this->resolver)) {
            return Reflect::call($this->resolver, $args);
        }

        throw new Exception("Resolver is not set!");
    }
}
