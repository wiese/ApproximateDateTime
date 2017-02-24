<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\Data;

use wiese\ApproximateDateTime\Data\DateTimeDataAccessors;
use wiese\ApproximateDateTime\Data\Type\DateTimeData;
use PHPUnit\Framework\TestCase;

class DateTimeDataAccessorsTest extends TestCase
{

    protected $sut;

    public function setUp() : void
    {
        $this->sut = $this->getMockForTrait(DateTimeDataAccessors::class);
        $dataProperty = new \ReflectionProperty($this->sut, 'data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($this->sut, new DateTimeData());
    }

    public function testAccessors() : void
    {
        $sut = $this->sut;

        $this->assertNull($sut->getY());
        $this->assertNull($sut->getM());
        $this->assertNull($sut->getD());
        $this->assertNull($sut->getH());
        $this->assertNull($sut->getI());
        $this->assertNull($sut->getS());

        $sut->setY(2007);
        $sut->setM(8);
        $sut->setD(30);
        $sut->setH(8);
        $sut->setI(37);
        $sut->setS(20);

        $this->assertEquals(2007, $sut->getY());
        $this->assertEquals(8, $sut->getM());
        $this->assertEquals(30, $sut->getD());
        $this->assertEquals(8, $sut->getH());
        $this->assertEquals(37, $sut->getI());
        $this->assertEquals(20, $sut->getS());
    }
}
