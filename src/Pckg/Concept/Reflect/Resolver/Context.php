<?php namespace Pckg\Concept\Reflect\Resolver;

use Pckg\Concept\Reflect\Resolver;

class Context implements Resolver
{

    public function __construct(\Pckg\Concept\Context $context)
    {
        $this->context = $context;
    }

    public function resolve($class)
    {
        foreach ($this->context->getData() as $object) {
            if (is_object($object)) {
                if (get_class($object) === $class || is_subclass_of($object, $class)) {
                    return $object;

                } else if (in_array($class, class_implements($object))) {
                    return $object;

                }
            }
        }
    }

}