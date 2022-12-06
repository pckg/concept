<?php

namespace Pckg\Concept\Reflect\Resolver;

use Pckg\Concept\Reflect;
use Pckg\Concept\Reflect\Resolver;

class Context implements Resolver
{

    public function canResolve($class)
    {
        foreach (context()->getData() as $object) {
            if (is_object($object)) {
                // @phpstan-ignore-next-line
                if (get_class($object) === $class || is_subclass_of($object, $class)) {
                    return true;
                } else if (in_array($class, class_implements($object))) {
                    return true;
                }
            }
        }

        return in_array($class, array_keys(context()->getWhenRequested()));
    }

    public function resolve($class, $data = [])
    {
        foreach (context()->getData() as $object) {
            if (is_object($object)) {
                // @phpstan-ignore-next-line
                if (get_class($object) === $class || is_subclass_of($object, $class)) {
                    return $object;
                } else if (in_array($class, class_implements($object))) {
                    return $object;
                }
            }
        }

        foreach (context()->getWhenRequested() as $service => $callable) {
            if ($service == $class) {
                return Reflect::call($callable, $data);
            }
        }
    }
}
