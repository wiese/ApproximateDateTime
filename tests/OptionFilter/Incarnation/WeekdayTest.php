<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\OptionFilter\Incarnation;

use wiese\ApproximateDateTime\Tests\OptionFilter\ParentTest;
use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\OptionFilter\Incarnation\Weekday;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;

class WeekdayTest extends ParentTest
{

    /**
     * @var Weekday
     */
    protected $sut;

    public function setUp() : void
    {
        $this->sut = new Weekday();
        $this->sut->setUnit('n'); // @todo mv into filter if only one purpose?
        $this->sut->setCalendar(CAL_GREGORIAN);
        // keep a reference for modification during individual tests
        $this->clues = $this->getMockBuilder('wiese\ApproximateDateTime\Clues')
            // methods that are mocked; results can be manipulated later
            ->setMethods(['getWhitelist', 'getBlacklist', 'getAfter', 'getBefore'])
            ->getMock();
        $this->sut->setClues($this->clues);
    }

    public function testApplyAllTuesdaysInMonth() : void
    {
        $this->mockClues(null, null, [2], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData();
        $start->setY(2001);
        $start->setM(9);
        $start->setD(1);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(2001);
        $end->setM(9);
        $end->setD(30);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(4, $ranges);

        $this->assertEquals(2001, $ranges[0]->getStart()->getY());
        $this->assertEquals(9, $ranges[0]->getStart()->getM());

        $this->assertEquals(4, $ranges[0]->getStart()->getD());
        $this->assertEquals(11, $ranges[1]->getStart()->getD());
        $this->assertEquals(18, $ranges[2]->getStart()->getD());
        $this->assertEquals(25, $ranges[3]->getStart()->getD());
    }

    public function testApplyNoDaysAllowed() : void
    {
        $this->mockClues(null, null, [], range(1, 7));

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData();
        $start->setY(2002);
        $start->setM(1);
        $start->setD(1);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(2002);
        $end->setM(12);
        $end->setD(31);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(0, $ranges);
    }

    public function testBeforeAndAfter() : void
    {
        $this->markTestIncomplete('no before and after tests, yet!');
    }
}
