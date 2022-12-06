<?php

namespace Pckg\Concept;

use Exception;
use Pckg\Concept\Reflect\Resolver;
use Pckg\Concept\Reflect\Resolver\Basic;
use Pckg\Concept\Reflect\Resolver\Context;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

/**
 * Class Reflect
 *
 * @package Pckg
 */
class Reflect
{
    protected static $resolvers = [];

    /**
     * Create $class with parameters provided in $params variable and getData() method.
     *
     * @throws Exception
     * @return object|mixed
     */
    public static function create($class, $params = [])
    {
        if (!class_exists($class)) {
            throw new Exception('Class ' . $class . ' not found');
        }

        if (!method_exists($class, '__construct')) {
            return new $class();
        }

        $reflectionMethod = new ReflectionMethod($class, '__construct');

        $reflectionParams = static::paramsToArray(
            $reflectionMethod->getParameters(),
            is_array($params) ? $params : [$params]
        );

        $reflection = new ReflectionClass($class);

        $object = $reflection->newInstanceArgs($reflectionParams);

        return $object;
    }

    /**
     * Calls $method on $object with parameters provided in $params variable and getData method().
     *
     * @return mixed|null
     * @throws Exception
     * @throws Throwable
     */
    public static function method($object, $method = '__construct', $params = [])
    {
        if (!$object) {
            throw new Exception('Cannot create object of no class');
        }

        try {
            $reflectionMethod = new ReflectionMethod(is_object($object) ? get_class($object) : $object, $method);
        } catch (ReflectionException $e) {
            /**
             * @T00D00 - document when we catch ReflectionException
             *         - #1 - when $object is empty
             *         - #2 - when method doesn't exist
             */

            try {
                $result = call_user_func_array([$object, $method], $params);

                return $result;
            } catch (Throwable $e2) {
                throw $e2;
            }
        }

        $params = static::paramsToArray($reflectionMethod->getParameters(), is_array($params) ? $params : [$params]);

        $result = null;
        if ($reflectionMethod->isStatic()) {
            $reflectionClass = new ReflectionClass(is_object($object) ? get_class($object) : $object);

            $result = $reflectionMethod->invokeArgs($reflectionClass, $params);
        } else {
            if (is_string($object) && $method != '__construct') {
                $object = static::create($object, $params);
            }

            $result = $reflectionMethod->invokeArgs($object, $params);
        }

        return $result;
    }

    public static function call(callable $callable, $params = [])
    {
        $reflectionFunction = new ReflectionFunction($callable);

        $params = static::paramsToArray($reflectionFunction->getParameters(), is_array($params) ? $params : [$params]);

        return $reflectionFunction->invokeArgs($params);
    }

    /**
     * Transforms array of ReflectionParameters into array of arguments.
     *
     * @return array
     * @throws Exception
     */
    protected static function paramsToArray($params, $data = [])
    {
        $arrParams = [];

        foreach ($params as $key => $param) {
            $arrParams[] = static::getParamValue($param, $data, $key);
        }

        return $arrParams;
    }

    /**
     * Call correct strategy for finding $param value.
     *
     * @return mixed|null|object
     * @throws Exception
     */
    protected static function getParamValue(ReflectionParameter $param, $data = [], $key = null)
    {
        /**
         * Param was found by param name.
         */
        if (array_key_exists($param->name, $data)) {
            return $data[$param->name];
        }

        /**
         * Param was found by string key name.
         */
        if ($key && !is_numeric($key) && array_key_exists($key, $data)) {
            return $data[$key];
        }

        $paramType = $param->getType();
        // @phpstan-ignore-next-line
        $paramTypeName = $paramType?->getName();
        // @phpstan-ignore-next-line
        $paramTypeBuiltIn = $paramType?->isBuiltin();

        /**
         * Class, subclass of interface was found in $data or was automatically created by resolvers.
         */
        if (
            !$param->allowsNull() &&
            $paramType &&
            ($class = $paramTypeName) &&
            !$paramTypeBuiltIn && ($object = static::getHintedParameter($class, $data))
        ) {
            return $object;
        }

        /**
         * Callable parameter was found in $data.
         */
        if ($paramType && $paramTypeName === 'callable' && $object = static::getCallableParameter($data)) {
            return $object;
        }

        /**
         * Parameter has default value, pass it.
         */
        if ($param->isOptional()) {
            return $param->getDefaultValue();
        }

        if ($key >= 0 && array_key_exists($key, $data)) {
            $tempMatch = $data[$key];

            if ($paramType) {
                $class = $paramTypeName;
                if (!$paramTypeBuiltIn && $tempMatch instanceof $class) {
                    return $tempMatch;
                } else {
                    foreach ($data as $item) {
                        if (is_object($item) && $item instanceof $class) {
                            return $item;
                        }
                    }
                }
            } else {
                return $tempMatch;
            }
        }

        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        if ($param->allowsNull()) {
            /**
             * Default value is null.
             */
            return null;
        }

        /**
         * Throw exception on all other cases.
         */
        throw new Exception(
            "Cannot find value for parameter " . (isset($class) ? $class . ' ' : null) . '$' . $param->name . ' in ' .
            $param->getDeclaringClass()->getName() . '->' . $param->getDeclaringFunction()->getName() . '().'
        );
    }

    /**
     * Searches for instance of $class in $data and getData().
     *
     * @return object|null
     * @throws Exception
     */
    protected static function getHintedParameter($class, $data)
    {
        if (!class_exists($class) && !interface_exists($class)) {
            throw new Exception('Class and/or interface ' . $class . ' does not exist.');
        }

        foreach ($data as $object) {
            if (is_object($object)) {
                if (get_class($object) === $class) {
                    return $object;
                } else if (is_subclass_of($object, $class)) {
                    return $object;
                    // @phpstan-ignore-next-line
                } else if (in_array($class, class_implements($object))) {
                    return $object;
                }
            }
        }

        return static::createHintedParameter($class, $data);
    }

    protected static function getCallableParameter($data)
    {
        foreach ($data as $item) {
            if (is_only_callable($item)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return object
     * @throws Exception
     */
    protected static function createHintedParameter($class, $data = [])
    {
        $staticResolvers = [Basic::class];
        foreach ($staticResolvers as $resolver) {
            $found = false;
            foreach (static::$resolvers as $res) {
                if (get_class($res) === $resolver) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                static::$resolvers[] = new $resolver();
            }
        }

        return static::resolve($class, $data);
    }

    public static function canResolve($class)
    {
        foreach (static::$resolvers as $resolver) {
            if ($resolver->canResolve($class)) {
                return true;
            }
        }

        return false;
    }

    public static function getResolvers()
    {
        return static::$resolvers;
    }

    public static function resolve($class, $data = [], $resolvers = [])
    {
        foreach ($resolvers ?: static::$resolvers as $resolver) {
            if ($resolved = $resolver->resolve($class, $data)) {
                return $resolved;
            }
        }

        return Reflect::create($class, $data);
    }

    public static function addResolver(Resolver $resolver)
    {
        static::$resolvers[] = $resolver;
    }

    public static function prependResolver(Resolver $resolver)
    {
        array_unshift(static::$resolvers, $resolver);
    }
}
