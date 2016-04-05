<?php namespace Pckg\Concept\Reflect\Resolver;

use Pckg\Concept\Reflect;
use Pckg\Concept\Reflect\Resolver;

class BasicResolver implements Resolver
{
    public function resolve($class)
    {
        return Reflect::create($class);
    }
}