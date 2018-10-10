<?php
namespace Helvetica\Standard;

use Closure;
use Reflector;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
use Helvetica\Standard\Container;

class Di extends Container
{
    /**
     * Store instances
     * 
     * @var Container
     */
    private $dependent;

    /**
     * get instances of params from Reflection
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
     * @param string $className
     */
    public function __construct(Container $dependent)
    {
        $this->dependent = $dependent;
    }

    /**
     * Get and save singleton of className from registed providers.
     * 
     * @param string $className
     * @return object
     */
    public function newClass($className)
    {
        if ($this->has($className)) {
            return $this->get($className);
        }

        if ($this->dependent->has($className)) {
            $singleton = $this->dependent->get($className);
        } else {
            $singleton = $this->defaultProvider($className);
        }

        $this->set($className, $singleton);

        return $singleton;
    }

    /**
     * Default provider.
     * 
     * @param string $className
     * @return mixed
     */
    private function defaultProvider($className)
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
    public function getParams($reflectionClass, $method)
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
     * Injection by class name.
     * 
     * @param string $className
     * @param string $method
     * @param array $inherentParams
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function injection($className, $method, $inherentParams=[])
    {
        $reflection = new ReflectionClass($className);
        $instances = $this->getParams($reflection, $method);
        $params = array_merge($instances, $inherentParams);
        return $reflection->newInstanceArgs($params);
    }

    /**
     * Injection by class instance.
     * 
     * @param object $instance
     * @param string $method
     * @param array $inherentParams
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function injectionByObject($instance, $method, $inherentParams=[])
    {
        $reflection = new ReflectionClass($instance);
        $instances = $this->getParams($reflection, $method);
        $params = array_merge($instances, $inherentParams);
        return call_user_func_array([$instance, $method], $params);
    }

    /**
     * Get the Closure of method.
     * 
     * @param object $instance
     * @param string $method
     * 
     * @return Closure
     */
    public function getClosure($instance, $method)
    {
        $reflectionMethod = ReflectionMethod($instance, $method);
        return $reflectionMethod->getClosure($instance);
    }

    /**
     * reverse a function and save singleton
     * 
     * @param callback $subject
     * @param array $inherentParams
     * 
     * @return mixed
     */
    public function call($subject, $inherentParams = [], $bind = null)
    {
        $reflectionFunction = new ReflectionFunction($subject);
        $dependentInstances = $this->getDependentInstances($reflectionFunction);
        $params = array_merge($dependentInstances, $inherentParams);
        $closure = $reflectionFunction->getClosure()->bindTo($bind);
        return call_user_func_array($closure, $params);
    }

    /**
     * Build dependent instances array.
     * 
     * @param Reflector $reflection
     * @return array
     */
    private function getDependentInstances(Reflector $reflector)
    {
        $reflectionParameters = static::getReflectionParameters($reflector);
        $instances = \array_map(function($reflectionParameter) {
            return $this->getDependentByParameter($reflectionParameter);
        }, $reflectionParameters);
        return $instances;
    }

    /**
     * instantiation params
     * 
     * @param ReflectionParameter $paramType
     * 
     * @return object
     * 
     * @throw \BadMethodCallException
     */
    protected function getDependentByParameter(ReflectionParameter $reflectionParameter)
    {
        $reflectionClass = $reflectionParameter->getClass();
        if (is_null($reflectionClass)) {
            throw new \BadMethodCallException('The method parameters not exists.');
        }
        $className = $reflectionClass->getName();
        return $this->newClass($className);
    }

    /**
     * Set a dependent provider.
     * 
     * @param string $className
     * @param Closure|object $provider
     * 
     * @return void
     */
    public function setProvider($className, $provider)
    {
        $this->dependent->set($className, $provider);
    }
}