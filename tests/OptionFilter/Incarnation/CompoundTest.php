<?php

namespace wiese\ApproximateDateTime\Tests\OptionFilter\Incarnation;

use wiese\ApproximateDateTime\Clues;
use wiese\ApproximateDateTime\Tests\OptionFilter\ParentTest;
use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\OptionFilter\Incarnation\Compound;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;
use DateTimeZone;

class CompoundTest extends ParentTest
{

    /**
     * @var \DateTimeZone
     */
    protected $tz;

    /**
     * @var Compound
     */
    protected $sut;

    public function init(string $unit) : void
    {
        $this->tz = new DateTimeZone('Arctic/Longyearbyen');

        $this->sut = new Compound();
        $this->sut->setUnit($unit);
        $this->sut->setTimezone($this->tz);
        $this->sut->setCalendar(CAL_GREGORIAN);
    }

    public function testAfter() : void
    {
        $this->init('y-m');

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1994);
        $start->setM(1);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1995);
        $end->setM(11);
        $range->setEnd($end);
        $ranges->append($range);

        $clues = new Clues();

        $clue = new Clue();
        $clue->setY(1994);
        $clue->setM(6);
        $clue->filter = Clue::FILTER_AFTEREQUALS;
        $clues->append($clue);

        $this->sut->setClues($clues);

        $ranges = $this->sut->apply($ranges);

        $this->assertEquals(1994, $ranges[0]->getStart()->getY());
        $this->assertEquals(6, $ranges[0]->getStart()->getM());
        $this->assertEquals(1995, $ranges[0]->getEnd()->getY());
        $this->assertEquals(11, $ranges[0]->getEnd()->getM());
    }

    public function testBefore() : void
    {
        $this->init('y-m-d');

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1994);
        $start->setM(1);
        $start->setD(3);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1995);
        $end->setM(11);
        $end->setM(19);
        $range->setEnd($end);
        $ranges->append($range);

        $clues = new Clues();

        $clue = new Clue();
        $clue->setY(1995);
        $clue->setM(10);
        $clue->setD(3);
        $clue->filter = Clue::FILTER_BEFOREEQUALS;
        $clues->append($clue);

        $this->sut->setClues($clues);

        $ranges = $this->sut->apply($ranges);

        $this->assertEquals(1994, $ranges[0]->getStart()->getY());
        $this->assertEquals(1, $ranges[0]->getStart()->getM());
        $this->assertEquals(3, $ranges[0]->getStart()->getD());
        $this->assertEquals(1995, $ranges[0]->getEnd()->getY());
        $this->assertEquals(10, $ranges[0]->getEnd()->getM());
        $this->assertEquals(3, $ranges[0]->getEnd()->getD());
    }

    public function testBeforeAndAfter() : void
    {
        $this->init('y-m-d');

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1975);
        $start->setM(9);
        $start->setD(1);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1975);
        $end->setM(9);
        $end->setM(30);
        $range->setEnd($end);
        $ranges->append($range);

        $clues = new Clues();

        $clue = new Clue();
        $clue->setY(1975);
        $clue->setM(9);
        $clue->setD(24);
        $clue->filter = Clue::FILTER_AFTEREQUALS;
        $clues->append($clue);

        $clue = new Clue();
        $clue->setY(1975);
        $clue->setM(9);
        $clue->setD(26);
        $clue->filter = Clue::FILTER_BEFOREEQUALS;
        $clues->append($clue);

        $this->sut->setClues($clues);

        $ranges = $this->sut->apply($ranges);

        $this->assertEquals(1975, $ranges[0]->getStart()->getY());
        $this->assertEquals(9, $ranges[0]->getStart()->getM());
        $this->assertEquals(24, $ranges[0]->getStart()->getD());
        $this->assertEquals(1975, $ranges[0]->getEnd()->getY());
        $this->assertEquals(9, $ranges[0]->getEnd()->getM());
        $this->assertEquals(26, $ranges[0]->getEnd()->getD());
    }
}
