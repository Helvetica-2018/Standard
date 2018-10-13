<?php
namespace Tests;

require_once(__DIR__ . '/AbstractTestcase.php');

use PHPUnit\Framework\TestCase;
use Helvetica\Standard\Dependent;
use Helvetica\Standard\Exception\UnknownIdentifierException;

class DepParam1
{
    public function method()
    {
        return 1;
    }

    private function privateMethod()
    {
        return 2;
    }
}

class DepParam2
{
    public function __construct(DepParam1 $d)
    {
        $this->d1 = $d;
    }

    public function method()
    {
        return $this->d1->method();
    }
}

class DepMain
{
    public function __construct(DepParam2 $d)
    {
        $this->d2 = $d;
    }

    public function method(DepParam2 $d2)
    {
        return $d2->method();
    }
}

class DepTestMethodCall
{
    public function method(DepParam2 $d2)
    {
        return $d2->method();
    }
}

class DependentTest extends AbstractTestcase
{
    public function testGetForSingleton()
    {
        $dependent = new Dependent();
        $dm = $dependent->get(DepMain::class);
        $this->assertInstanceOf(DepMain::class, $dm);
        $dm->test1 = 'TEST_DATA';

        $reDm = $dependent->get(DepMain::class);
        $this->assertInstanceOf(DepMain::class, $reDm);
        $this->assertEquals($reDm->test1, 'TEST_DATA');
        $this->assertEquals($reDm, $dm);

        $depParam2 = $dependent->get(DepParam2::class);
        $this->assertInstanceOf(DepParam2::class, $dm->d2);
        $this->assertEquals($reDm->d2, $depParam2);
        $this->assertEquals($reDm->d2, $dm->d2);

        $depParam1 = $dependent->get(DepParam1::class);
        $this->assertInstanceOf(DepParam1::class, $dm->d2->d1);
        $this->assertEquals($reDm->d2->d1, $depParam1);
        $this->assertEquals($reDm->d2->d1, $dm->d2->d1);
    }

    public function testGetForContructInjection()
    {
        $dependent = new Dependent();
        $dm = $dependent->get(DepMain::class);

        $depParam2 = $dependent->get(DepParam2::class);
        $this->assertInstanceOf(DepParam2::class, $depParam2);
        $this->assertEquals($depParam2, $dm->d2);
    }

    public function testSet()
    {
        $dependent = new Dependent();
        $depParam1 = new DepParam1();
        $dependent->set(DepParam1::class, $depParam1);

        $d1 = $dependent->get(DepParam1::class);
        $this->assertInstanceOf(DepParam1::class, $d1);
        $this->assertEquals($depParam1, $d1);
    }

    public function testHas()
    {
        $dependent = new Dependent();
        $depParam1 = new DepParam1();
        $dependent->set(DepParam1::class, $depParam1);

        $has = $dependent->has(DepParam1::class);
        $this->assertTrue($has);

        $has = $dependent->has(DepParam2::class);
        $this->assertFalse($has);
    }

    /**
     * @expectedException \TypeError
     */
    public function testSetProviderClosureParam()
    {
        $dependent = new Dependent();
        $dependent->setProvider('string', 'string');
    }

    public function testSetProvider()
    {
        $dependent = new Dependent([], []);
        $closure = function() {
            return 'testSetProvider';
        };
        $dependent->setProvider('test', $closure);
        
        $providers = $this->getPropertyValue($dependent, 'providers');
        $this->assertCount(1, $providers);
        $this->assertInstanceOf(\Closure::class, $providers['test']);
        $this->assertEquals($closure, $providers['test']);
        $this->assertEquals(\call_user_func($providers['test']), 'testSetProvider');
    }

    public function testHasProvider()
    {
        $dependent = new Dependent();
        $dependent->setProvider('className', function() {
            return 'closure';
        });
        $result = $dependent->hasProvider('className');
        $this->assertTrue($result);
    }

    /**
     * @expectedException \Helvetica\Standard\Exception\UnknownIdentifierException
     */
    public function testGetProviderWithException()
    {
        $dependent = new Dependent();
        $dependent->getProvider('_test_unknow_provider_key');
    }

    public function testGetProvider()
    {
        $dependent = new Dependent();
        $dependent->setProvider('className', function() {
            return 'closure';
        });
        $closure = $dependent->getProvider('className');
        $this->assertInstanceOf(\Closure::class, $closure);
        $this->assertEquals($closure(), 'closure');
    }

    public function testMethodCall()
    {
        $dependent = new Dependent();
        $result = $dependent->methodCall(DepTestMethodCall::class, 'method');
        $this->assertEquals(1, $result);
        $this->assertTrue($dependent->has(DepParam1::class));
        $this->assertTrue($dependent->has(DepParam2::class));
        $this->assertTrue($dependent->has(DepTestMethodCall::class));
    }

    public function testMethodCallWithInstance()
    {
        $dependent = new Dependent();
        $instance = new DepParam1();
        $result = $dependent->methodCallWithInstance($instance, 'method');
        $this->assertEquals(1, $result);
    }

    public function testMethodToClosure()
    {
        $dependent = new Dependent();
        $instance = new DepParam1();
        $closure = $dependent->methodToClosure($instance, 'method');
        $this->assertInstanceOf(\Closure::class, $closure);
        $this->assertEquals(1, \call_user_func($closure));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testMethodToClosureExtention()
    {
        $dependent = new Dependent();
        $instance = new DepParam1();
        $dependent->methodToClosure($instance, 'privateMethod');
    }

    public function testSubCall()
    {
        $func = function(DepParam1 $d1, $param) {
            return [$d1, $param];
        };
        $dependent = new Dependent();
        list($result1, $result2) = $dependent->subCall($func, [1]);
        $this->assertInstanceOf(DepParam1::class, $result1);
        $this->assertEquals(1, $result2);
    }

    public function testWithFullInjectProvider()
    {
        $dependent = new Dependent();
        $closure = $this->methodToClosure($dependent, 'withFullInjectProvider');
        $depParam2 = $closure(DepParam2::class);
        $this->assertInstanceOf(DepParam2::class, $depParam2);
    }

    public function testGetParams()
    {
        $dependent = new Dependent();
        $reflection = new \ReflectionClass(DepTestMethodCall::class);
        $closure = $this->methodToClosure($dependent, 'getParams');
        $result = $closure($reflection, 'method');
        $this->assertCount(1, $result);
        $this->assertInstanceOf(DepParam2::class, $result[0]);
    }

    public function testGetDependentInstances()
    {
        $dependent = new Dependent();
        $reflection = new \ReflectionClass(DepTestMethodCall::class);
        $methodReflection = $reflection->getMethod('method');
        $closure = $this->methodToClosure($dependent, 'getDependentInstances');
        $result = $closure($methodReflection);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(DepParam2::class, $result[0]);

        $singletons = $this->getPropertyValue($dependent, 'singletons');
        $this->assertArrayHasKey(DepParam2::class, $singletons);
    }

    public function testGetSingletonByParameter()
    {
        $dependent = new Dependent();
        $reflection = new \ReflectionClass(DepTestMethodCall::class);
        $methodReflection = $reflection->getMethod('method');
        $parameterReflections = $methodReflection->getParameters();
        $reflectionParameter = $parameterReflections[0];

        $closure = $this->methodToClosure($dependent, 'getSingletonByParameter');
        $result = $closure($reflectionParameter);
        $this->assertInstanceOf(DepParam2::class, $result);

        $singletons = $this->getPropertyValue($dependent, 'singletons');
        $this->assertArrayHasKey(DepParam2::class, $singletons);
    }

    /**
     * @expectedException \TypeError
     */
    public function testGetSingletonByParameterException()
    {
        $dependent = new Dependent();
        $closure = $this->methodToClosure($dependent, 'getSingletonByParameter');
        $closure('string');
    }
}
