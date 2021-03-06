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
        $start->setY(1914);
        $sut->setStart($start);
        $end = new DateTimeData($timezone);
        $end->setY(1918);
        $sut->setEnd($end);

        $ranges = $sut->filet();

        $this->assertCount(5, $ranges);

        $this->assertEquals(1914, $ranges[0]->getStart()->getY());
        $this->assertNull($ranges[0]->getEnd()->getM());
        $this->assertEquals(1914, $ranges[0]->getEnd()->getY());
        $this->assertNull($ranges[0]->getEnd()->getM());
        $this->assertEquals(1918, $ranges[4]->getStart()->getY());
        $this->assertNull($ranges[4]->getEnd()->getM());
        $this->assertEquals(1918, $ranges[4]->getEnd()->getY());
        $this->assertNull($ranges[4]->getEnd()->getM());
    }

    public function testFiletMonths() : void
    {
        $timezone = new \DateTimeZone('Asia/Vladivostok');

        $sut = new Range();
        $start = new DateTimeData($timezone);
        $start->setY(1914);
        $start->setM(2);
        $sut->setStart($start);
        $end = new DateTimeData($timezone);
        $end->setY(1914);
        $end->setM(4);
        $sut->setEnd($end);

        $ranges = $sut->filet();

        $this->assertCount(3, $ranges);

        $this->assertEquals(1914, $ranges[0]->getStart()->getY());
        $this->assertEquals(2, $ranges[0]->getStart()->getM());
        $this->assertEquals(1914, $ranges[1]->getStart()->getY());
        $this->assertEquals(3, $ranges[1]->getStart()->getM());
        $this->assertEquals(1914, $ranges[2]->getStart()->getY());
        $this->assertEquals(4, $ranges[2]->getStart()->getM());
    }

    public function testFiletYearsAndMonths() : void
    {
        $timezone = new \DateTimeZone('Asia/Vladivostok');

        $sut = new Range();
        $start = new DateTimeData($timezone);
        $start->setY(1914);
        $start->setM(2);
        $sut->setStart($start);
        $end = new DateTimeData($timezone);
        $end->setY(1918);
        $end->setM(4);
        $sut->setEnd($end);

        $ranges = $sut->filet();

        $this->assertCount(3, $ranges);

        $this->assertEquals(1914, $ranges[0]->getStart()->getY());
        $this->assertEquals(2, $ranges[0]->getStart()->getM());
        $this->assertEquals(1914, $ranges[1]->getStart()->getY());
        $this->assertEquals(3, $ranges[1]->getStart()->getM());
        $this->assertEquals(1914, $ranges[2]->getStart()->getY());
        $this->assertEquals(4, $ranges[2]->getStart()->getM());
    }
}
