<?php

namespace Pckg\Concept;

trait Mapper
{

    protected array $mapper = [];

    public function getMapped($key)
    {
        return isset($this->mapper[$key])
            ? $this->mapper[$key]
            : null;
    }
}
