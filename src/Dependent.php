<?php
namespace Helvetica\Standard;

use Closure;
use Reflector;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
use Psr\Container\ContainerInterface;
use Helvetica\Standard\Exception\UnknownIdentifierException;

/**
 * Dependents container.
 * 
 * @property
 */
class Dependent implements ContainerInterface
{
    /**
     * Singletons storage.
     * 
     * @var object[]
     */
    private $singletons;

    /**
     * Dependent provider definitions
     * 
     * @var Closure[]
     */
    private $providers;

    /**
     * Get ReflectionParameters of params by Reflection
     * 
     * @param Reflector $reflection
     * 
     * @return ReflectionParameter[]
     */
    public static function getReflectionParameters(Reflector $reflection)
    {
        return \array_filter($reflection->getParameters(), function($r) {
            return (bool)$r->getClass();
        });
    }

    /**
     * Initialization class.
     * 
     * @param array $singletons
     * @param array $providers
     */
    public function __construct($singletons = [], $providers = [])
    {
        $this->singletons = $singletons;
        $this->providers = $providers;
    }

    /**
     * Set a dependent provider.
     * 
     * @param string $className
     * @param Closure $provider
     */
    public function setProvider($className, Closure $provider)
    {
        $this->providers[$className] = $provider;
    }

    /**
     * Provider exsists?
     * 
     * @param string $className
     * 
     * @return bool
     */
    public function hasProvider($className)
    {
        return \array_key_exists($className, $this->providers);
    }

    /**
     * Get a dependent provider.
     * 
     * @param string $className
     * 
     * @return Closure|null
     */
    public function getProvider($className)
    {
        if ($this->hasProvider($className)) {
            return $this->providers[$className];
        }
        throw new UnknownIdentifierException();
    }

    /**
     * Get the singletons by class name from providers list.
     * 
     * @param string $className
     */
    public function get($className)
    {
        if ($this->has($className)) {
            return $this->singletons[$className];
        }

        if ($this->hasProvider($className)) {
            $closure = $this->getProvider($className);
            $singleton = \call_user_func($closure, $this);
        } else {
            $singleton = $this->withFullInjectProvider($className);
        }

        $this->set($className, $singleton);

        return $singleton;
    }

    /**
     * Is the singletons exsists?
     * 
     * @param string $className
     * 
     * @return bool
     */
    public function has($className)
    {
        return \array_key_exists($className, $this->singletons);
    }

    /**
     * Set a singleton.
     * 
     * @param string $className
     * @param object $singleton
     */
    public function set($className, $singleton)
    {
        $this->singletons[$className] = $singleton;
    }

    /**
     * Injection method by class name.
     * 
     * @param string $className
     * @param string $method
     * @param array $inherentParams
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function methodCall($className, $method, $params=[])
    {
        $instance = $this->get($className);
        return $this->methodCallWithInstance($instance, $method, $params);
    }

    /**
     * Injection method by class instance.
     * 
     * @param object $instance
     * @param string $method
     * @param array $inherentParams
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function methodCallWithInstance($instance, $method, $params=[])
    {
        $reflection = new ReflectionClass($instance);
        $instances = $this->getParams($reflection, $method);
        $newParams = array_merge($instances, $params);
        return call_user_func_array([$instance, $method], $newParams);
    }

    /**
     * Get the closure of method.
     * 
     * @param object $instance
     * @param string $method
     * 
     * @return Closure
     * @throws \BadMethodCallException
     */
    public function methodToClosure($instance, $method)
    {
        $reflectionMethod = new ReflectionMethod($instance, $method);
        if ($reflectionMethod->isPublic()) {
            return $reflectionMethod->getClosure($instance);
        }
        throw new \BadMethodCallException('Call to non-public method '. __CLASS__ . '::' . $method);
    }

    /**
     * reverse a function and save singleton
     * 
     * @param callback $func
     * @param array $params
     * 
     * @return mixed
     */
    public function subCall($func, $params = [], $bind = null)
    {
        $reflectionFunction = new ReflectionFunction($func);
        $dependentInstances = $this->getDependentInstances($reflectionFunction);
        $newParams = array_merge($dependentInstances, $params);
        $closure = $reflectionFunction->getClosure();
        if (! \is_null($bind)) {
            $closure = $closure->bindTo($bind);
        }
        return call_user_func_array($closure, $newParams);
    }

    // private methods

    /**
     * The defailt provider for non-register dependents.
     * this provider will inject construct and relations
     * 
     * @return object
     */
    private function withFullInjectProvider($className)
    {
        $reflection = new ReflectionClass($className);
        $params = $this->getParams($reflection, '__construct');
        return $reflection->newInstanceArgs($params);
    }

    /**
     * instantiation param list of method and save
     * 
     * @param ReflectionClass $refClass
     * @param string $method
     * 
     * @return array
     */
    private function getParams($reflectionClass, $method)
    {
        $instances = [];
        if ($reflectionClass->hasMethod($method)) {
            /** @var ReflectionMethod $reflector */
            $reflector = $reflectionClass->getMethod($method);
            $instances = $this->getDependentInstances($reflector);
        }
        return $instances;
    }

    /**
     * Build dependent instances array.
     * 
     * @param Reflector $reflection
     * @return array
     */
    private function getDependentInstances(Reflector $reflector)
    {
        /** @var ReflectionParameter $reflectionParameters */
        $reflectionParameters = static::getReflectionParameters($reflector);
        $singletons = \array_map(function($reflectionParameter) {
            return $this->getSingletonByParameter($reflectionParameter);
        }, $reflectionParameters);
        return $singletons;
    }

    /**
     * Get singletons by ReflectionParameter.
     * 
     * @param ReflectionParameter $paramType
     * 
     * @return object
     * 
     * @throws \BadMethodCallException
     */
    private function getSingletonByParameter(ReflectionParameter $reflectionParameter)
    {
        $reflectionClass = $reflectionParameter->getClass();
        $className = $reflectionClass->getName();
        return $this->get($className);
    }
}
