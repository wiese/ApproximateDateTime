<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\OptionFilter;

use wiese\ApproximateDateTime\Manager;
use wiese\ApproximateDateTime\OptionFilter\Factory;
use wiese\ApproximateDateTime\OptionFilter\Incarnation\Numeric;
use wiese\ApproximateDateTime\OptionFilter\Incarnation\Day;
use wiese\ApproximateDateTime\OptionFilter\Incarnation\Weekday;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testProduce() : void
    {
        $sut = new Factory(new Manager());
        $this->assertInstanceOf(Numeric::class, $sut->produce('Numeric'));
        $this->assertInstanceOf(Day::class, $sut->produce('Day'));
        $this->assertInstanceOf(Weekday::class, $sut->produce('Weekday'));
    }

    public function testProduceException() : void
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Filter type bogus not implemented.');

        $sut = new Factory(new Manager());
        $sut->produce('bogus');
    }
}
