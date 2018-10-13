<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Helvetica\Standard\Dependent;

class DepParam1
{
    public function method()
    {
        return 1;
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

class DependentTest extends TestCase
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

    public function testSetProvider()
    {
        
    }
}
