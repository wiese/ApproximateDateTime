<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\OptionFilter;

use wiese\ApproximateDateTime\Manager;
use wiese\ApproximateDateTime\OptionFilter\Factory;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testProduce() : void
    {
        $sut = new Factory(new Manager());
        $this->assertInstanceOf('wiese\ApproximateDateTime\OptionFilter\Incarnation\Numeric', $sut->produce('Numeric'));
        $this->assertInstanceOf('wiese\ApproximateDateTime\OptionFilter\Incarnation\Day', $sut->produce('Day'));
        $this->assertInstanceOf('wiese\ApproximateDateTime\OptionFilter\Incarnation\Weekday', $sut->produce('Weekday'));
    }

    public function testProduceException() : void
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Filter type bogus not implemented.');

        $sut = new Factory(new Manager());
        $sut->produce('bogus');
    }
}
