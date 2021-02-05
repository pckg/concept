<?php

namespace Pckg\Concept;

trait Mapper
{

    protected $mapper = [];

    public function getMapped($key)
    {
        return isset($this->mapper[$key])
            ? $this->mapper[$key]
            : null;
    }
}
