<?php namespace Pckg\Concept;

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
     * @param       $class
     * @param array $params
     *
     * @throws Exception
     */
    public static function create($class, $params = [])
    {
        if (!class_exists($class)) {
            throw new Exception('Class ' . $class . ' not found');
        }

        if (!method_exists($class, '__construct')) {
            return new $class;
        }

        $reflectionParams = measure('Preparing ' . $class . ' parameters', function() use ($class, $params) {
            $reflectionMethod = new ReflectionMethod($class, '__construct');

            return static::paramsToArray(
                $reflectionMethod->getParameters(),
                is_array($params) ? $params : [$params]
            );
        });

        $object = measure(
            'Creating ' . $class,
            function() use ($class, $reflectionParams) {
                $reflection = new ReflectionClass($class);

                return $reflection->newInstanceArgs($reflectionParams);
            }
        );

        return $object;
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
        if (!$object) {
            throw new Exception('Cannot create object of no class');
        }

        try {
            $reflectionMethod = new ReflectionMethod(is_object($object) ? get_class($object) : $object, $method);
        } catch (ReflectionException $e) {
            /**
             * @T00D00 - document when we catch ReflectionException
             *         - #1 - when $object is empty
             */

            try {
                $result = call_user_func_array([$object, $method], $params);

                return $result;
            } catch (Throwable $e2) {
                throw $e2;
            }
            throw $e;
        }

        $params = static::paramsToArray($reflectionMethod->getParameters(), is_array($params) ? $params : [$params]);

        $result = $reflectionMethod->isStatic()
            ? ((function() use ($reflectionMethod, $object, $params) {
                $reflectionClass = new ReflectionClass(is_object($object) ? get_class($object) : $object);

                return $reflectionMethod->invokeArgs($reflectionClass, $params);
            })())
            : ((function() use ($reflectionMethod, $object, $params, $method) {
                if (is_string($object) && $method != '__construct') {
                    $object = static::create($object, $params);
                }

                return $reflectionMethod->invokeArgs($object, $params);
            })());

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
            /**
             * Param was found by param name.
             */
            return $data[$param->name];
        } else if ($key && !is_numeric($key) && array_key_exists($key, $data)) {
            /**
             * Param was found by string key name.
             */
            return $data[$key];
        } else if (
            !$param->allowsNull() &&
            $param->getClass() &&
            ($class = $param->getClass()->getName()) && ($object = static::getHintedParameter($class, $data))
        ) {
            /**
             * Class, subclass of interface was found in $data or was automatically created by resolvers.
             */
            return $object;
        } elseif ($param->isCallable() && $object = static::getCallableParameter($data)) {
            /**
             * Callable parameter was found in $data.
             */
            return $object;
        } else if ($param->isOptional()) {
            /**
             * Parameter has default value, pass it.
             */
            return $param->getDefaultValue();
        } else if ($key >= 0 && array_key_exists($key, $data)) {
            /**
             * Numeric index was found.
             */
            return $data[$key];
        } else if ($param->allowsNull()) {
            /**
             * Default value is null.
             */
            return null;
        }

        /**
         * Throw exception on all other cases.
         */
        throw new Exception(
            "Cannot find value for parameter " . $param->name . (isset($class) ? " as " . $class : "") . "."
        );
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