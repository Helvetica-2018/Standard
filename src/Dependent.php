<?php
namespace Helvetica\Standard;

use Closure;
use ReflectionClass;
use Helvetica\Standard\Container;

/**
 * @property array singleton
 * @property ReflectionClass reflection
 * @property Ioc $ioc
 */
class Dependent
{
    /** @var mixed */
    private $singleton;

    /** @var ReflectionClass */
    private $reflection;

    /** @var array */
    protected static $providers = [];

    /**
     * Set Dependent providers.
     * 
     * @param array $providers
     */
    public static function setProviders($providers)
    {
        if (\is_array($providers)) {
            foreach ($providers as $class => $callback) {
                if (!\method_exists($callback, '__invoke')) {
                    throw new \RuntimeException("Dependent provider `{$class}` must be callable.");
                }
            }
            static::$providers = \array_merge(static::$providers, $providers);
        }
    }

    /**
     * @param Container $container
     * @param string $className
     */
    public function __construct(Container $container, $className)
    {
        $this->container = $container;
        $this->ioc = $this->container->get(Ioc::class);

        $this->reflection = new ReflectionClass($className);
        if (!$this->reflection->isInstantiable()) {
            throw new \Exception('Can\'t instantiate ' . $className);
        }

        if (\array_key_exists($className, static::$providers)) {
            $callback = static::$providers[$className];
            if (\is_string($callback)) {
                $callback = new $callback;
            }
            $this->singleton = \call_user_func($callback, $className);
        } else {
            $params = $this->ioc->getParams($this->reflection, '__construct');
            $this->singleton = $this->reflection->newInstanceArgs($params);
            $this->singleton->container = $container;
        }
    }

    /**
     * Sets static property value.
     * 
     * @param string $name
     * @param mixed $value
     */
    public function setStaticProperty($name, $value)
    {
        $this->reflection->setStaticPropertyValue($name, $value);
    }

    /**
     * Get ReflectionMethod from ReflectionClass
     * 
     * @param string $name
     * 
     * @return ReflectionMethod
     */
    public function getMethod($name)
    {
        return $this->reflection->getMethod($name);
    }

    /**
     * Get ReflectionClass from ReflectionClass
     * 
     * @return ReflectionClass
     */
    public function getReflection()
    {
        return $this->reflection;
    }

    /**
     * Get the Closure of method.
     * 
     * @param string $method
     * 
     * @return Closure
     */
    public function getClosure($method)
    {
        return $this->getMethod($method)->getClosure($this->getInstance());
    }

    /**
     * Get the Instance of registed object.
     * 
     * @return mixed
     */
    public function getInstance()
    {
        return $this->singleton;
    }

    /**
     * @param string $method
     * @param array $inherentParams
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function injection($method, $inherentParams=[])
    {
        if (! $this->reflection->hasMethod($method)) {
            throw new \InvalidArgumentException('Method not exists.');
        }
        $instances = $this->ioc->getParams($this->reflection, $method, count($inherentParams));
        $params = array_merge($instances, $inherentParams);
        $closure = $this->getClosure($method);
        return call_user_func_array($closure, $params);
    }

    /**
     * Injection a Closure
     * 
     * @param Closure $closure
     * @param string $method
     * @param array $inherentParams
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function injectionByClosure(Closure $closure, $method, $inherentParams=[])
    {
        $instances = $this->ioc->getParams($this->reflection, $method, count($inherentParams));
        $params = array_merge($instances, $inherentParams);
        return call_user_func_array($closure, $params);
    }
}
