<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;
use DateTimeZone;
use PHPUnit_Framework_TestCase;

class RangesTest extends PHPUnit_Framework_TestCase
{
    protected $timezone;

    public function setUp() : void
    {
        $this->timezone = new DateTimeZone('Europe/Berlin');
    }

    public function testMergeEmptyAndExisting() : void
    {
        $sut = new Ranges();

        $sut2 = new Ranges();

        $range = new Range();
        $start = new DateTimeData($this->timezone);
        $start->y = 2014;
        $range->setStart($start);
        $end = new DateTimeData($this->timezone);
        $end->y = 2017;
        $range->setEnd($end);

        $sut2->append($range);

        $this->assertEquals($sut2, $sut->merge($sut2));
    }

    public function testMergeTwoNonEmpty() : void
    {
        $sut = new Ranges();

        $range = new Range();
        $start = new DateTimeData($this->timezone);
        $start->y = 2014;
        $range->setStart($start);
        $end = new DateTimeData($this->timezone);
        $end->y = 2017;
        $range->setEnd($end);

        $sut->append($range);

        $sut2 = new Ranges();

        $range = new Range();
        $start = new DateTimeData($this->timezone);
        $start->m = 3;
        $range->setStart($start);
        $end = new DateTimeData($this->timezone);
        $end->m = 4;
        $range->setEnd($end);

        $sut2->append($range);

        $sut = $sut->merge($sut2);

        $this->assertEquals(1, count($sut));
        $this->assertEquals(2014, $sut[0]->getStart()->y);
        $this->assertEquals(3, $sut[0]->getStart()->m);
        $this->assertEquals(2017, $sut[0]->getEnd()->y);
        $this->assertEquals(4, $sut[0]->getEnd()->m);
    }

    public function testMergeComplex() : void
    {
        $sut = new Ranges();

        $range = new Range();
        $start = new DateTimeData($this->timezone);
        $start->y = 2015;
        $range->setStart($start);
        $end = new DateTimeData($this->timezone);
        $end->y = 2015;
        $range->setEnd($end);

        $sut->append($range);

        $range = new Range();
        $start = new DateTimeData($this->timezone);
        $start->y = 2016;
        $range->setStart($start);
        $end = new DateTimeData($this->timezone);
        $end->y = 2018;
        $range->setEnd($end);

        $sut->append($range);


        $sut2 = new Ranges();

        $range = new Range();
        $start = new DateTimeData($this->timezone);
        $start->m = 1;
        $range->setStart($start);
        $end = new DateTimeData($this->timezone);
        $end->m = 2;
        $range->setEnd($end);

        $sut2->append($range);

        $range = new Range();
        $start = new DateTimeData($this->timezone);
        $start->m = 7;
        $range->setStart($start);
        $end = new DateTimeData($this->timezone);
        $end->m = 9;
        $range->setEnd($end);

        $sut2->append($range);

        $sut = $sut->merge($sut2);

        $this->assertEquals(4, count($sut));

        $this->assertEquals(2015, $sut[0]->getStart()->y);
        $this->assertEquals(1, $sut[0]->getStart()->m);
        $this->assertEquals(2015, $sut[0]->getEnd()->y);
        $this->assertEquals(2, $sut[0]->getEnd()->m);

        $this->assertEquals(2015, $sut[1]->getStart()->y);
        $this->assertEquals(7, $sut[1]->getStart()->m);
        $this->assertEquals(2015, $sut[1]->getEnd()->y);
        $this->assertEquals(9, $sut[1]->getEnd()->m);

        $this->assertEquals(2016, $sut[2]->getStart()->y);
        $this->assertEquals(1, $sut[2]->getStart()->m);
        $this->assertEquals(2018, $sut[2]->getEnd()->y);
        $this->assertEquals(2, $sut[2]->getEnd()->m);

        $this->assertEquals(2016, $sut[3]->getStart()->y);
        $this->assertEquals(7, $sut[3]->getStart()->m);
        $this->assertEquals(2018, $sut[3]->getEnd()->y);
        $this->assertEquals(9, $sut[3]->getEnd()->m);
    }
}
