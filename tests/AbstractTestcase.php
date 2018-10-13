<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

abstract class AbstractTestcase extends TestCase
{
    public function methodToClosure($instance, $methodName)
    {
        $reflection = new \ReflectionClass($instance);
        $methodReflection = $reflection->getMethod($methodName);
        $closure = $methodReflection->getClosure($instance);
        return $closure;
    }

    public function getPropertyValue($instance, $propertyName)
    {
        $reflection = new \ReflectionClass($instance);
        $propertyReflection = $reflection->getProperty($propertyName);
        $propertyReflection->setAccessible(true);
        $value = $propertyReflection->getValue($instance);
        $propertyReflection->setAccessible(false);
        return $value;
    }
}
