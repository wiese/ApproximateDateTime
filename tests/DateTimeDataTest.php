<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\DateTimeData;
use DateTimeZone;
use Exception;
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
}
