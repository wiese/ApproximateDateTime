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
        $sut->y = 2014;

        $this->assertEquals(2014, $sut->y);
        $this->assertNull($sut->m);
        $this->assertNull($sut->d);
        $this->assertNull($sut->h);
        $this->assertNull($sut->i);
        $this->assertNull($sut->s);

        $sut2 = new DateTimeData($tz);
        $sut2->m = 3;

        $sut->merge($sut2);

        $this->assertEquals(2014, $sut->y);
        $this->assertEquals(3, $sut->m);
        $this->assertNull($sut->d);
        $this->assertNull($sut->h);
        $this->assertNull($sut->i);
        $this->assertNull($sut->s);

        $sut3 = new DateTimeData($tz);
        $sut3->h = 7;
        $sut3->i = 59;

        $sut->merge($sut3);

        $this->assertEquals(2014, $sut->y);
        $this->assertEquals(3, $sut->m);
        $this->assertNull($sut->d);
        $this->assertEquals(7, $sut->h);
        $this->assertEquals(59, $sut->i);
        $this->assertNull($sut->s);
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

        $sut->y = 2007;
        $sut->m = 8;

        $this->assertEquals('2007-08-00T00:00:00', $sut->toString());

        $sut->d = 30;
        $sut->h = 9;
        $sut->i = 27;
        $sut->s = 5;

        $this->assertEquals('2007-08-30T09:27:05', $sut->toString());
    }

    public function testToDateTime() : void
    {
        $tz = new DateTimeZone('Europe/Berlin');
        $sut = new DateTimeData($tz);

        $sut->y = 2009;
        $sut->m = 11;
        $sut->d = 3;

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

        $sut->y = 2009;
        $sut->m = 12;

        $sut->toDateTime();
    }

    public function testFromDateTime() : void
    {
        $tz = new DateTimeZone('Europe/Berlin');
        $dateTime = new DateTime('1999-05-10 15:30:21', $tz);

        $sut = DateTimeData::fromDateTime($dateTime);

        $this->assertEquals(1999, $sut->y);
        $this->assertEquals(5, $sut->m);
        $this->assertEquals(10, $sut->d);
        $this->assertEquals(15, $sut->h);
        $this->assertEquals(30, $sut->i);
        $this->assertEquals(21, $sut->s);
    }
}
