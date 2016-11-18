<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\Range;
use PHPUnit_Framework_TestCase;

class RangeTest extends PHPUnit_Framework_TestCase
{
    public function testFiletYears() : void
    {
        $timezone = new \DateTimeZone('Asia/Vladivostok');

        $sut = new Range();
        $start = new DateTimeData($timezone);
        $start->y = 1914;
        $sut->setStart($start);
        $end = new DateTimeData($timezone);
        $end->y = 1918;
        $sut->setEnd($end);

        $ranges = $sut->filet();

        $this->assertCount(5, $ranges);

        $this->assertEquals(1914, $ranges[0]->getStart()->y);
        $this->assertNull($ranges[0]->getEnd()->m);
        $this->assertEquals(1914, $ranges[0]->getEnd()->y);
        $this->assertNull($ranges[0]->getEnd()->m);
        $this->assertEquals(1918, $ranges[4]->getStart()->y);
        $this->assertNull($ranges[4]->getEnd()->m);
        $this->assertEquals(1918, $ranges[4]->getEnd()->y);
        $this->assertNull($ranges[4]->getEnd()->m);
    }

    public function testFiletMonths() : void
    {
        $timezone = new \DateTimeZone('Asia/Vladivostok');

        $sut = new Range();
        $start = new DateTimeData($timezone);
        $start->y = 1914;
        $start->m = 2;
        $sut->setStart($start);
        $end = new DateTimeData($timezone);
        $end->y = 1914;
        $end->m = 4;
        $sut->setEnd($end);

        $ranges = $sut->filet();

        $this->assertCount(3, $ranges);

        $this->assertEquals(1914, $ranges[0]->getStart()->y);
        $this->assertEquals(2, $ranges[0]->getStart()->m);
        $this->assertEquals(1914, $ranges[1]->getStart()->y);
        $this->assertEquals(3, $ranges[1]->getStart()->m);
        $this->assertEquals(1914, $ranges[2]->getStart()->y);
        $this->assertEquals(4, $ranges[2]->getStart()->m);
    }

    public function testFiletYearsAndMonths() : void
    {
        $timezone = new \DateTimeZone('Asia/Vladivostok');

        $sut = new Range();
        $start = new DateTimeData($timezone);
        $start->y = 1914;
        $start->m = 2;
        $sut->setStart($start);
        $end = new DateTimeData($timezone);
        $end->y = 1918;
        $end->m = 4;
        $sut->setEnd($end);

        $ranges = $sut->filet();

        $this->assertCount(3, $ranges);

        $this->assertEquals(1914, $ranges[0]->getStart()->y);
        $this->assertEquals(2, $ranges[0]->getStart()->m);
        $this->assertEquals(1914, $ranges[1]->getStart()->y);
        $this->assertEquals(3, $ranges[1]->getStart()->m);
        $this->assertEquals(1914, $ranges[2]->getStart()->y);
        $this->assertEquals(4, $ranges[2]->getStart()->m);
    }
}
