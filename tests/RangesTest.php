<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class RangesTest extends TestCase
{

    /**
     * @var DateTimeZone
     */
    private $timezone;

    public function setUp() : void
    {
        $this->timezone = new DateTimeZone('Europe/Berlin');
    }

    public function testMergeEmptyAndExisting() : void
    {
        $sut = new Ranges();

        $sut2 = new Ranges();

        $range = new Range();
        $start = new DateTimeData();
        $start->setY(2014);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(2017);
        $range->setEnd($end);

        $sut2->append($range);

        $this->assertEquals($sut2, $sut->merge($sut2));
    }

    public function testMergeTwoNonEmpty() : void
    {
        $sut = new Ranges();

        $range = new Range();
        $start = new DateTimeData();
        $start->setY(2014);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(2017);
        $range->setEnd($end);

        $sut->append($range);

        $sut2 = new Ranges();

        $range = new Range();
        $start = new DateTimeData();
        $start->setM(3);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setM(4);
        $range->setEnd($end);

        $sut2->append($range);

        $sut = $sut->merge($sut2);

        $this->assertEquals(1, count($sut));
        $this->assertEquals(2014, $sut[0]->getStart()->getY());
        $this->assertEquals(3, $sut[0]->getStart()->getM());
        $this->assertEquals(2017, $sut[0]->getEnd()->getY());
        $this->assertEquals(4, $sut[0]->getEnd()->getM());
    }

    public function testMergeComplex() : void
    {
        $sut = new Ranges();

        $range = new Range();
        $start = new DateTimeData();
        $start->setY(2015);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(2015);
        $range->setEnd($end);

        $sut->append($range);

        $range = new Range();
        $start = new DateTimeData();
        $start->setY(2016);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(2018);
        $range->setEnd($end);

        $sut->append($range);


        $sut2 = new Ranges();

        $range = new Range();
        $start = new DateTimeData();
        $start->setM(1);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setM(2);
        $range->setEnd($end);

        $sut2->append($range);

        $range = new Range();
        $start = new DateTimeData();
        $start->setM(7);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setM(9);
        $range->setEnd($end);

        $sut2->append($range);

        $sut = $sut->merge($sut2);

        $this->assertEquals(4, count($sut));

        $this->assertEquals(2015, $sut[0]->getStart()->getY());
        $this->assertEquals(1, $sut[0]->getStart()->getM());
        $this->assertEquals(2015, $sut[0]->getEnd()->getY());
        $this->assertEquals(2, $sut[0]->getEnd()->getM());

        $this->assertEquals(2015, $sut[1]->getStart()->getY());
        $this->assertEquals(7, $sut[1]->getStart()->getM());
        $this->assertEquals(2015, $sut[1]->getEnd()->getY());
        $this->assertEquals(9, $sut[1]->getEnd()->getM());

        $this->assertEquals(2016, $sut[2]->getStart()->getY());
        $this->assertEquals(1, $sut[2]->getStart()->getM());
        $this->assertEquals(2018, $sut[2]->getEnd()->getY());
        $this->assertEquals(2, $sut[2]->getEnd()->getM());

        $this->assertEquals(2016, $sut[3]->getStart()->getY());
        $this->assertEquals(7, $sut[3]->getStart()->getM());
        $this->assertEquals(2018, $sut[3]->getEnd()->getY());
        $this->assertEquals(9, $sut[3]->getEnd()->getM());
    }
}
