<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\DateTimeData;
use DateTime;
use DateTimeZone;
use PHPUnit_Framework_TestCase;

class DateTimeDataTest extends PHPUnit_Framework_TestCase
{
    public function testMerge() : void
    {
        $tz = new DateTimeZone('Europe/Berlin');
        $sut = new DateTimeData($tz);
        $sut->setY(2014);

        $this->assertEquals(2014, $sut->getY());
        $this->assertNull($sut->getM());
        $this->assertNull($sut->getD());
        $this->assertNull($sut->getH());
        $this->assertNull($sut->getI());
        $this->assertNull($sut->getS());

        $sut2 = new DateTimeData($tz);
        $sut2->setM(3);

        $sut->merge($sut2);

        $this->assertEquals(2014, $sut->getY());
        $this->assertEquals(3, $sut->getM());
        $this->assertNull($sut->getD());
        $this->assertNull($sut->getH());
        $this->assertNull($sut->getI());
        $this->assertNull($sut->getS());

        $sut3 = new DateTimeData($tz);
        $sut3->setH(7);
        $sut3->setI(59);

        $sut->merge($sut3);

        $this->assertEquals(2014, $sut->getY());
        $this->assertEquals(3, $sut->getM());
        $this->assertNull($sut->getD());
        $this->assertEquals(7, $sut->getH());
        $this->assertEquals(59, $sut->getI());
        $this->assertNull($sut->getS());
    }

    public function testMergeDifferentTimezones() : void
    {
        $tz = new DateTimeZone('Europe/Berlin');
        $sut = new DateTimeData($tz);

        $tz = new DateTimeZone('Atlantic/Faroe');
        $sut2 = new DateTimeData($tz);

        $this->expectException('Exception');

        $sut->merge($sut2);
    }

    public function testToString() : void
    {
        $tz = new DateTimeZone('Europe/Berlin');
        $sut = new DateTimeData($tz);

        $sut->setY(2007);
        $sut->setM(8);

        $this->assertEquals('2007-08-00T00:00:00', $sut->toString());

        $sut->setD(30);
        $sut->setH(9);
        $sut->setI(27);
        $sut->setS(5);

        $this->assertEquals('2007-08-30T09:27:05', $sut->toString());
    }

    public function testToDateTime() : void
    {
        $tz = new DateTimeZone('Europe/Berlin');
        $sut = new DateTimeData($tz);

        $sut->setY(2009);
        $sut->setM(11);
        $sut->setD(3);

        $res = $sut->toDateTime();
        $this->assertInstanceOf('DateTimeInterface', $res);
        $this->assertEquals(2009, $res->format('Y'));
        $this->assertEquals(11, $res->format('m'));
        $this->assertEquals(3, $res->format('d'));
        $this->assertEquals(0, $res->format('H'));
        $this->assertEquals(0, $res->format('i'));
        $this->assertEquals(0, $res->format('s'));
    }

    public function testToDateTimeException() : void
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('DateTime can not be created from incompletely populated DateTimeData.');

        $tz = new DateTimeZone('Europe/Berlin');
        $sut = new DateTimeData($tz);

        $sut->setY(2009);
        $sut->setM(12);

        $sut->toDateTime();
    }

    public function testFromDateTime() : void
    {
        $tz = new DateTimeZone('Europe/Berlin');
        $dateTime = new DateTime('1999-05-10 15:30:21', $tz);

        $sut = DateTimeData::fromDateTime($dateTime);

        $this->assertEquals(1999, $sut->getY());
        $this->assertEquals(5, $sut->getM());
        $this->assertEquals(10, $sut->getD());
        $this->assertEquals(15, $sut->getH());
        $this->assertEquals(30, $sut->getI());
        $this->assertEquals(21, $sut->getS());
    }

    public function testSetTime() : void
    {
        $tz = new DateTimeZone('Atlantic/Faroe');
        $sut = new DateTimeData($tz);

        $this->assertNull($sut->getY());
        $this->assertNull($sut->getM());
        $this->assertNull($sut->getD());
        $this->assertNull($sut->getH());
        $this->assertNull($sut->getI());
        $this->assertNull($sut->getS());

        $this->assertEquals($sut, $sut->setTime(5, 4, 3));

        $this->assertNull($sut->getY());
        $this->assertNull($sut->getM());
        $this->assertNull($sut->getD());
        $this->assertEquals(5, $sut->getH());
        $this->assertEquals(4, $sut->getI());
        $this->assertEquals(3, $sut->getS());
    }
}
