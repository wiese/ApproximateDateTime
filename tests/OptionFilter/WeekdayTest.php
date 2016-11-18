<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\OptionFilter;

use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\OptionFilter\Weekday;
use wiese\ApproximateDateTime\Ranges;
use wiese\ApproximateDateTime\Range;
use DateTimeZone;
use PHPUnit_Framework_TestCase;

class WeekdayTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \DateTimeZone
     */
    protected $tz;

    /**
     * @var \wiese\ApproximateDateTime\OptionFilter\Day
     */
    protected $sut;

    public function setUp() : void
    {
        $this->tz = new DateTimeZone('Pacific/Guam');

        $this->sut = new Weekday();
        $this->sut->setMin(1);
        $this->sut->setMin(7);
        $this->sut->setUnit('n'); // @todo mv into filter if only one purpose
        $this->sut->setTimezone($this->tz);
        $this->sut->setCalendar(CAL_GREGORIAN);
    }

    public function testApplyAllTuesdaysInMonth() : void
    {
        $this->sut->setWhitelist(array(2));

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

        $this->assertEquals(4, $ranges[0]->getStart()->d);
        $this->assertEquals(11, $ranges[1]->getStart()->d);
        $this->assertEquals(18, $ranges[2]->getStart()->d);
        $this->assertEquals(25, $ranges[3]->getStart()->d);
    }
}
