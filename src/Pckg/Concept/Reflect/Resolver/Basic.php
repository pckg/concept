<?php

namespace Pckg\Concept\Reflect\Resolver;

use Pckg\Concept\Reflect;
use Pckg\Concept\Reflect\Resolver;

class Basic implements Resolver
{
    public function resolve($class, $data = [])
    {
        return Reflect::create($class, $data);
    }

    public function canResolve($class)
    {
        return false;
    }
}
