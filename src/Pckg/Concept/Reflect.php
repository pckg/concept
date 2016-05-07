<?php

namespace Pckg\Concept;

use Exception;
use Pckg\Concept\Reflect\Resolver;
use Pckg\Concept\Reflect\Resolver\BasicResolver;
use Pckg\Framework\Reflect\FrameworkResolver;
use Pckg\Htmlbuilder\Resolver\FormResolver;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Class Reflect
 * @package Pckg
 */
class Reflect
{

    protected static $resolvers = [];

    /**
     * Create $class with parameters provided in $params variable and getData() method.
     *
     * @param       $class
     * @param array $params
     *
     * @throws Exception
     */
    public static function create($class, $params = [])
    {
        if (!class_exists($class)) {
            // debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            throw new Exception('Class ' . $class . ' not found');
        }

        if (!method_exists($class, '__construct')) {
            return new $class;
        }

        startMeasure('Creating ' . $class);
        $reflectionMethod = new ReflectionMethod($class, '__construct');

        $reflectionParams = static::paramsToArray($reflectionMethod->getParameters(),
            is_array($params) ? $params : [$params]);

        $reflection = new ReflectionClass($class);

        $newInstance = $reflection->newInstanceArgs($reflectionParams);
        stopMeasure('Creating ' . $class);

        return $newInstance;
    }

    /**
     * Calls $method on $object with parameters provided in $params variable and getData method().
     *
     * @param        $object
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     */
    public static function method($object, $method = '__construct', $params = [])
    {
        try {
            $reflectionMethod = new ReflectionMethod($object, $method);

        } catch (ReflectionException $e) {
            try {
                $result = call_user_func_array([$object, $method], $params); // @T00D00

                return $result;
            } catch (\Exception $e) {
                d($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
                throw $e;
            }
        }

        $params = static::paramsToArray($reflectionMethod->getParameters(), is_array($params) ? $params : [$params]);

        if ($reflectionMethod->isStatic()) {
            $reflectionClass = new ReflectionClass(is_object($object) ? get_class($object) : $object);
            $result = $reflectionMethod->invokeArgs($reflectionClass, $params);
        } else {
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
     * @param       $params
     * @param array $data
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
     * @param ReflectionParameter $param
     * @param array               $data
     * @param null                $key
     *
     * @return mixed|null|object
     * @throws Exception
     */
    protected static function getParamValue(ReflectionParameter $param, $data = [], $key = null)
    {
        if (array_key_exists($param->name, $data)) {
            return $data[$param->name];

        } else if ($key && !is_numeric($key) && array_key_exists($key, $data)) {
            return $data[$key];

        } else if (
            !$param->allowsNull() &&
            $param->getClass() &&
            ($class = $param->getClass()->getName()) && ($object = static::getHintedParameter($class, $data))
        ) {
            return $object;

        } else if ($param->isOptional()) {
            return $param->getDefaultValue();

        } else if ($param->allowsNull()) {
            return null;

        }

        throw new Exception("Cannot find value for parameter " . $param->name . (isset($class) ? " as " . $class : "") . ".");
    }

    /**
     * Searches for instance of $class in $data and getData().
     *
     * @param $class
     * @param $data
     *
     * @return object
     * @throws Exception
     */
    protected static function getHintedParameter($class, $data)
    {
        if (!class_exists($class) && !interface_exists($class)) {
            throw new Exception('Class and/or interface ' . $class . ' does not exist.');
        }

        foreach ($data as $object) {
            if (is_object($object)) {
                if (get_class($object) === $class || is_subclass_of($object, $class)) {
                    return $object;

                } else if (in_array($class, class_implements($object))) {
                    return $object;

                }
            }
        }

        return static::createHintedParameter($class, $data);
    }

    /**
     * @param $class
     * @param $data
     *
     * @return object
     * @throws Exception
     */
    protected static function createHintedParameter($class, $data = [])
    {
        if (!static::$resolvers) {
            static::$resolvers[] = new FrameworkResolver();
            static::$resolvers[] = new FormResolver();
            static::$resolvers[] = new BasicResolver();
        }

        return static::resolve($class, $data);
    }

    public static function resolve($class, $data = [], $resolvers = [])
    {
        foreach ($resolvers ?: static::$resolvers as $resolver) {
            if ($resolved = $resolver->resolve($class)) {
                return $resolved;
            }
        }
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