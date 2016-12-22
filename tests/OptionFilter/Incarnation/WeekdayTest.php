<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\OptionFilter\Incarnation;

use wiese\ApproximateDateTime\Tests\OptionFilter\ParentTest;
use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\OptionFilter\Incarnation\Weekday;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;
use DateTimeZone;

class WeekdayTest extends ParentTest
{

    /**
     * @var \DateTimeZone
     */
    protected $tz;

    /**
     * @var Weekday
     */
    protected $sut;

    public function setUp() : void
    {
        $this->tz = new DateTimeZone('Pacific/Guam');

        $this->sut = new Weekday();
        $this->sut->setUnit('n'); // @todo mv into filter if only one purpose?
        $this->sut->setTimezone($this->tz);
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
        $start = new DateTimeData($this->tz);
        $start->y = 2001;
        $start->m = 9;
        $start->d = 1;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 2001;
        $end->m = 9;
        $end->d = 30;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(4, $ranges);

        $this->assertEquals(2001, $ranges[0]->getStart()->y);
        $this->assertEquals(9, $ranges[0]->getStart()->m);

        $this->assertEquals(4, $ranges[0]->getStart()->d);
        $this->assertEquals(11, $ranges[1]->getStart()->d);
        $this->assertEquals(18, $ranges[2]->getStart()->d);
        $this->assertEquals(25, $ranges[3]->getStart()->d);
    }

    public function testApplyNoDaysAllowed() : void
    {
        $this->mockClues(null, null, [], range(1, 7));

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 2002;
        $start->m = 1;
        $start->d = 1;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 2002;
        $end->m = 12;
        $end->d = 31;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(0, $ranges);
    }
}
