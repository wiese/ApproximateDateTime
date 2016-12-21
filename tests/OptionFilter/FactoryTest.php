<?php

namespace wiese\ApproximateDateTime\Tests\OptionFilter;

use wiese\ApproximateDateTime\OptionFilter\Factory;
use PHPUnit_Framework_TestCase;

class FactoryTest extends PHPUnit_Framework_TestCase
{
    public function testProduce() : void
    {
        $sut = new Factory;
        $this->assertInstanceOf('wiese\ApproximateDateTime\OptionFilter\Incarnation\Numeric', $sut->produce('Numeric'));
        $this->assertInstanceOf('wiese\ApproximateDateTime\OptionFilter\Incarnation\Day', $sut->produce('Day'));
        $this->assertInstanceOf('wiese\ApproximateDateTime\OptionFilter\Incarnation\Weekday', $sut->produce('Weekday'));
    }

    public function testProduceException() : void
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Filter type bogus not implemented.');

        $sut = new Factory;
        $sut->produce('bogus');
    }
}
