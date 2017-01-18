<?php

namespace wiese\ApproximateDateTime\Tests\OptionFilter\Incarnation;

use wiese\ApproximateDateTime\Clues;
use wiese\ApproximateDateTime\Tests\OptionFilter\ParentTest;
use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\OptionFilter\Incarnation\Compound;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;

class CompoundTest extends ParentTest
{

    /**
     * @var Compound
     */
    protected $sut;

    public function init(string $unit) : void
    {
        $this->sut = new Compound();
        $this->sut->setUnit($unit);
        $this->sut->setCalendar(CAL_GREGORIAN);
    }

    public function testEmptyClues() : void
    {
        $this->init('y-m');

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData();
        $start->setY(1985);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(1985);
        $range->setEnd($end);
        $ranges->append($range);

        $clues = new Clues();

        $this->sut->setClues($clues);

        $this->assertEquals($ranges, $this->sut->__invoke($ranges));
    }

    public function testEmptyCluesEmptyRanges() : void
    {
        $this->init('y-m');

        $ranges = new Ranges();

        $clues = new Clues();

        $this->sut->setClues($clues);

        $this->assertEquals($ranges, $this->sut->__invoke($ranges));
    }

    public function testAfter() : void
    {
        $this->init('y-m');

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData();
        $start->setY(1994);
        $start->setM(1);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(1995);
        $end->setM(11);
        $range->setEnd($end);
        $ranges->append($range);

        $clues = new Clues();

        $clue = new Clue();
        $clue->setY(1994);
        $clue->setM(6);
        $clue->type = Clue::IS_AFTEREQUALS;
        $clues->append($clue);

        $this->sut->setClues($clues);

        $ranges = $this->sut->__invoke($ranges);

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
        $start = new DateTimeData();
        $start->setY(1994);
        $start->setM(1);
        $start->setD(3);
        $range->setStart($start);
        $end = new DateTimeData();
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
        $clue->type = Clue::IS_BEFOREEQUALS;
        $clues->append($clue);

        $this->sut->setClues($clues);

        $ranges = $this->sut->__invoke($ranges);

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
        $start = new DateTimeData();
        $start->setY(1975);
        $start->setM(9);
        $start->setD(1);
        $range->setStart($start);
        $end = new DateTimeData();
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
        $clue->type = Clue::IS_AFTEREQUALS;
        $clues->append($clue);

        $clue = new Clue();
        $clue->setY(1975);
        $clue->setM(9);
        $clue->setD(26);
        $clue->type = Clue::IS_BEFOREEQUALS;
        $clues->append($clue);

        $this->sut->setClues($clues);

        $ranges = $this->sut->__invoke($ranges);

        $this->assertEquals(1975, $ranges[0]->getStart()->getY());
        $this->assertEquals(9, $ranges[0]->getStart()->getM());
        $this->assertEquals(24, $ranges[0]->getStart()->getD());
        $this->assertEquals(1975, $ranges[0]->getEnd()->getY());
        $this->assertEquals(9, $ranges[0]->getEnd()->getM());
        $this->assertEquals(26, $ranges[0]->getEnd()->getD());
    }

    public function testWhitelistMerge() : void
    {
        $this->init('y-m');

        // @todo also start with empty ranges once

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData();
        $start->setY(1975);
        $start->setM(1);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(1975);
        $end->setM(12);
        $range->setEnd($end);
        $ranges->append($range);

        $clues = new Clues();

        $clue = new Clue();
        $clue->setY(1975);
        $clue->setM(9);
        $clue->type = Clue::IS_WHITELIST;
        $clues->append($clue);

        $this->sut->setClues($clues);

        $newRanges = $this->sut->__invoke($ranges);

        $this->assertEquals($ranges, $newRanges);
    }

    public function testWhitelistMergeTouching() : void
    {
        $this->init('y-m');

        // @todo also start with empty ranges once

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData();
        $start->setY(1969);
        $start->setM(5);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(1969);
        $end->setM(8);
        $range->setEnd($end);
        $ranges->append($range);

        $clues = new Clues();

        $clue = new Clue();
        $clue->setY(1969);
        $clue->setM(5);
        $clue->type = Clue::IS_WHITELIST;
        $clues->append($clue);

        $clue = new Clue();
        $clue->setY(1969);
        $clue->setM(8);
        $clue->type = Clue::IS_WHITELIST;
        $clues->append($clue);

        $this->sut->setClues($clues);

        $newRanges = $this->sut->__invoke($ranges);

        $this->assertEquals($ranges, $newRanges);
    }

    public function testWhitelistSeparate() : void
    {
        $this->init('y-m');

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData();
        $start->setY(1975);
        $start->setM(1);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(1975);
        $end->setM(12);
        $range->setEnd($end);
        $ranges->append($range);

        $clues = new Clues();

        $clue = new Clue();
        $clue->setY(1976);
        $clue->setM(9);
        $clue->type = Clue::IS_WHITELIST;
        $clues->append($clue);

        $this->sut->setClues($clues);

        $newRanges = $this->sut->__invoke($ranges);

        $this->assertCount(2, $newRanges);
        $this->assertEquals(1975, $newRanges[0]->getStart()->getY());
        $this->assertEquals(1, $newRanges[0]->getStart()->getM());
        $this->assertEquals(1975, $newRanges[0]->getEnd()->getY());
        $this->assertEquals(12, $newRanges[0]->getEnd()->getM());
        $this->assertEquals(1976, $newRanges[1]->getStart()->getY());
        $this->assertEquals(9, $newRanges[1]->getStart()->getM());
        $this->assertEquals(1976, $newRanges[1]->getEnd()->getY());
        $this->assertEquals(9, $newRanges[1]->getEnd()->getM());
    }

    public function testBlacklist() : void
    {
        $this->init('y-m');

        // @todo also start with empty ranges once

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData();
        $start->setY(1975);
        $start->setM(1);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(1976);
        $end->setM(12);
        $range->setEnd($end);
        $ranges->append($range);

        $clues = new Clues();
        $clue = new Clue();
        $clue->setY(1975);
        $clue->setM(9);
        $clue->type = Clue::IS_BLACKLIST;
        $clues->append($clue);
        $this->sut->setClues($clues);

        $newRanges = $this->sut->__invoke($ranges);

        $this->assertCount(2, $newRanges);
        $this->assertEquals(1975, $newRanges[0]->getStart()->getY());
        $this->assertEquals(1, $newRanges[0]->getStart()->getM());
        $this->assertEquals(1975, $newRanges[0]->getEnd()->getY());
        $this->assertEquals(8, $newRanges[0]->getEnd()->getM());
        $this->assertEquals(1975, $newRanges[1]->getStart()->getY());
        $this->assertEquals(10, $newRanges[1]->getStart()->getM());
        $this->assertEquals(1976, $newRanges[1]->getEnd()->getY());
        $this->assertEquals(12, $newRanges[1]->getEnd()->getM());
    }

    public function testMix() : void
    {
        $this->init('y-m-d');

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData();
        $start->setY(1971);
        $start->setM(1);
        $start->setD(1);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(1971);
        $end->setM(1);
        $end->setD(31);
        $range->setEnd($end);
        $ranges->append($range);
        $range = new Range();
        $start = new DateTimeData();
        $start->setY(1971);
        $start->setM(3);
        $start->setD(1);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(1971);
        $end->setM(10);
        $end->setD(31);
        $range->setEnd($end);
        $ranges->append($range);

        $clues = new Clues();

        $clue = new Clue();
        $clue->setY(1971);
        $clue->setM(2);
        $clue->setD(14);
        $clue->type = Clue::IS_WHITELIST;
        $clues->append($clue);

        $this->sut->setClues($clues);

        $ranges = $this->sut->__invoke($ranges);

        $this->assertCount(3, $ranges);

        $this->assertEquals(1971, $ranges[0]->getStart()->getY());
        $this->assertEquals(1, $ranges[0]->getStart()->getM());
        $this->assertEquals(1971, $ranges[0]->getEnd()->getY());
        $this->assertEquals(1, $ranges[0]->getEnd()->getM());
        $this->assertEquals(1971, $ranges[1]->getStart()->getY());
        $this->assertEquals(2, $ranges[1]->getStart()->getM());
        $this->assertEquals(14, $ranges[1]->getStart()->getD());
        $this->assertEquals(1971, $ranges[1]->getEnd()->getY());
        $this->assertEquals(2, $ranges[1]->getEnd()->getM());
        $this->assertEquals(14, $ranges[1]->getEnd()->getD());
        $this->assertEquals(1971, $ranges[2]->getStart()->getY());
        $this->assertEquals(3, $ranges[2]->getStart()->getM());
        $this->assertEquals(1971, $ranges[2]->getEnd()->getY());
        $this->assertEquals(10, $ranges[2]->getEnd()->getM());
    }
}
