<?php

namespace wiese\ApproximateDateTime\Tests\OptionFilter\Incarnation;

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
        // keep a reference for modification during individual tests
        $this->clues = $this->getMockBuilder('wiese\ApproximateDateTime\Clues')
            // methods that are mocked; results can be manipulated later
            ->setMethods(['getWhitelist', 'getBlacklist', 'getAfter', 'getBefore'])
            ->getMock();
        $this->sut->setClues($this->clues);
    }

    public function testYearMonth() : void
    {
        $this->markTestIncomplete();

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

        $clue = new Clue();
        $clue->setY(1994);
        $clue->setM(6);
        $clue->filter = Clue::FILTER_AFTEREQUALS;
        $this->mockClues($clue, null, [], []);

        $ranges = $this->sut->apply($ranges);

        $this->assertEquals(1994, $ranges[0]->getStart()->getY());
        $this->assertEquals(6, $ranges[0]->getStart()->getM());
        $this->assertEquals(1, $ranges[0]->getStart()->getD());
        $this->assertEquals(1995, $ranges[0]->getEnd()->getY());
        $this->assertEquals(11, $ranges[0]->getEnd()->getM());
        $this->assertEquals(30, $ranges[0]->getEnd()->getD());
    }
}
