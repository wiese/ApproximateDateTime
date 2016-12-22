<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\OptionFilter\Incarnation;

use wiese\ApproximateDateTime\Tests\OptionFilter\ParentTest;
use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\OptionFilter\Incarnation\Day;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;
use DateTimeZone;

class DayTest extends ParentTest
{

    /**
     * @var \DateTimeZone
     */
    protected $tz;

    /**
     * @var Day
     */
    protected $sut;

    public function setUp() : void
    {
        $this->tz = new DateTimeZone('Asia/Ulaanbaatar');

        $this->sut = new Day();
        $this->sut->setUnit('d');
        $this->sut->setTimezone($this->tz);
        $this->sut->setCalendar(CAL_GREGORIAN);
        // keep a reference for modification during individual tests
        $this->clues = $this->getMockBuilder('wiese\ApproximateDateTime\Clues')
            // methods that are mocked; results can be manipulated later
            ->setMethods(['getWhitelist', 'getBlacklist', 'getAfter', 'getBefore'])
            ->getMock();
        $this->sut->setClues($this->clues);
    }

    public function testApplyAllDaysInMonth() : void
    {
        $this->mockClues(null, null, [], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1993;
        $start->m = 5;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1993;
        $end->m = 5;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertEquals(1993, $ranges[0]->getStart()->y);
        $this->assertEquals(5, $ranges[0]->getStart()->m);
        $this->assertEquals(1, $ranges[0]->getStart()->d);
        $this->assertEquals(1993, $ranges[0]->getEnd()->y);
        $this->assertEquals(5, $ranges[0]->getEnd()->m);
        $this->assertEquals(31, $ranges[0]->getEnd()->d);
    }

    public function testApplyAllDaysInConsecutiveMonths() : void
    {
        $this->mockClues(null, null, [], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1998;
        $start->m = 1;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1998;
        $end->m = 2;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(1, $ranges);

        $this->assertEquals(1998, $ranges[0]->getStart()->y);
        $this->assertEquals(1, $ranges[0]->getStart()->m);
        $this->assertEquals(1, $ranges[0]->getStart()->d);
        $this->assertEquals(1998, $ranges[0]->getEnd()->y);
        $this->assertEquals(2, $ranges[0]->getEnd()->m);
        $this->assertEquals(28, $ranges[0]->getEnd()->d);
    }

    public function testApplyAllDaysThroughBigWhitelistInConsecutiveMonths() : void
    {
        $this->mockClues(null, null, range(1, 31), []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1998;
        $start->m = 1;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1998;
        $end->m = 2;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(1, $ranges);

        $this->assertEquals(1998, $ranges[0]->getStart()->y);
        $this->assertEquals(1, $ranges[0]->getStart()->m);
        $this->assertEquals(1, $ranges[0]->getStart()->d);
        $this->assertEquals(1998, $ranges[0]->getEnd()->y);
        $this->assertEquals(2, $ranges[0]->getEnd()->m);
        $this->assertEquals(28, $ranges[0]->getEnd()->d);
    }

    public function testApplyAllDaysInSeparateMonths() : void
    {
        $this->mockClues(null, null, [], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1953;
        $start->m = 10;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1953;
        $end->m = 10;
        $range->setEnd($end);
        $ranges->append($range);
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1954;
        $start->m = 5;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1954;
        $end->m = 5;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(2, $ranges);

        $this->assertEquals(1953, $ranges[0]->getStart()->y);
        $this->assertEquals(10, $ranges[0]->getStart()->m);
        $this->assertEquals(1, $ranges[0]->getStart()->d);
        $this->assertEquals(1953, $ranges[0]->getEnd()->y);
        $this->assertEquals(10, $ranges[0]->getEnd()->m);
        $this->assertEquals(31, $ranges[0]->getEnd()->d);

        $this->assertEquals(1954, $ranges[1]->getStart()->y);
        $this->assertEquals(5, $ranges[1]->getStart()->m);
        $this->assertEquals(1, $ranges[1]->getStart()->d);
        $this->assertEquals(1954, $ranges[1]->getEnd()->y);
        $this->assertEquals(5, $ranges[1]->getEnd()->m);
        $this->assertEquals(31, $ranges[1]->getEnd()->d);
    }

    public function testApplyConsecutiveDaysInMonth() : void
    {
        $this->mockClues(null, null, [3, 4, 5], [5]);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1958;
        $start->m = 11;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1958;
        $end->m = 11;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(1, $ranges);

        $this->assertEquals(1958, $ranges[0]->getStart()->y);
        $this->assertEquals(11, $ranges[0]->getStart()->m);
        $this->assertEquals(3, $ranges[0]->getStart()->d);
        $this->assertEquals(1958, $ranges[0]->getEnd()->y);
        $this->assertEquals(11, $ranges[0]->getEnd()->m);
        $this->assertEquals(4, $ranges[0]->getEnd()->d);
    }

    public function testApplyConsecutiveDaysInConsecutiveMonths() : void
    {
        $this->mockClues(null, null, [7, 8, 9], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1997;
        $start->m = 11;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1997;
        $end->m = 12;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(2, $ranges);

        $this->assertEquals(1997, $ranges[0]->getStart()->y);
        $this->assertEquals(11, $ranges[0]->getStart()->m);
        $this->assertEquals(7, $ranges[0]->getStart()->d);
        $this->assertEquals(1997, $ranges[0]->getEnd()->y);
        $this->assertEquals(11, $ranges[0]->getEnd()->m);
        $this->assertEquals(9, $ranges[0]->getEnd()->d);

        $this->assertEquals(1997, $ranges[1]->getStart()->y);
        $this->assertEquals(12, $ranges[1]->getStart()->m);
        $this->assertEquals(7, $ranges[1]->getStart()->d);
        $this->assertEquals(1997, $ranges[1]->getEnd()->y);
        $this->assertEquals(12, $ranges[1]->getEnd()->m);
        $this->assertEquals(9, $ranges[1]->getEnd()->d);
    }

    public function testApplyConsecutiveDaysInSeparateMonths() : void
    {
        $this->mockClues(null, null, [27, 28], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1962;
        $start->m = 12;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1962;
        $end->m = 12;
        $range->setEnd($end);
        $ranges->append($range);

        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1963;
        $start->m = 9;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1963;
        $end->m = 9;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(2, $ranges);

        $this->assertEquals(1962, $ranges[0]->getStart()->y);
        $this->assertEquals(12, $ranges[0]->getStart()->m);
        $this->assertEquals(27, $ranges[0]->getStart()->d);
        $this->assertEquals(1962, $ranges[0]->getEnd()->y);
        $this->assertEquals(12, $ranges[0]->getEnd()->m);
        $this->assertEquals(28, $ranges[0]->getEnd()->d);

        $this->assertEquals(1963, $ranges[1]->getStart()->y);
        $this->assertEquals(9, $ranges[1]->getStart()->m);
        $this->assertEquals(27, $ranges[1]->getStart()->d);
        $this->assertEquals(1963, $ranges[1]->getEnd()->y);
        $this->assertEquals(9, $ranges[1]->getEnd()->m);
        $this->assertEquals(28, $ranges[1]->getEnd()->d);
    }

    public function testApplySeparateDaysInMonth() : void
    {
        $this->mockClues(null, null, [3, 4, 5], [4]);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1958;
        $start->m = 11;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1958;
        $end->m = 11;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(2, $ranges);

        $this->assertEquals(1958, $ranges[0]->getStart()->y);
        $this->assertEquals(11, $ranges[0]->getStart()->m);
        $this->assertEquals(3, $ranges[0]->getStart()->d);
        $this->assertEquals(1958, $ranges[0]->getEnd()->y);
        $this->assertEquals(11, $ranges[0]->getEnd()->m);
        $this->assertEquals(3, $ranges[0]->getEnd()->d);

        $this->assertEquals(1958, $ranges[1]->getStart()->y);
        $this->assertEquals(11, $ranges[1]->getStart()->m);
        $this->assertEquals(5, $ranges[1]->getStart()->d);
        $this->assertEquals(1958, $ranges[1]->getEnd()->y);
        $this->assertEquals(11, $ranges[1]->getEnd()->m);
        $this->assertEquals(5, $ranges[1]->getEnd()->d);
    }

    public function testApplySeparateDaysInConsecutiveMonths() : void
    {
        $this->mockClues(null, null, [10, 12], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1930;
        $start->m = 6;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1930;
        $end->m = 8;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(6, $ranges);

        $this->assertEquals(1930, $ranges[0]->getStart()->y);
        $this->assertEquals(6, $ranges[0]->getStart()->m);
        $this->assertEquals(10, $ranges[0]->getStart()->d);
        $this->assertEquals(1930, $ranges[0]->getEnd()->y);
        $this->assertEquals(6, $ranges[0]->getEnd()->m);
        $this->assertEquals(10, $ranges[0]->getEnd()->d);

        $this->assertEquals(1930, $ranges[1]->getStart()->y);
        $this->assertEquals(6, $ranges[1]->getStart()->m);
        $this->assertEquals(12, $ranges[1]->getStart()->d);
        $this->assertEquals(1930, $ranges[1]->getEnd()->y);
        $this->assertEquals(6, $ranges[1]->getEnd()->m);
        $this->assertEquals(12, $ranges[1]->getEnd()->d);

        $this->assertEquals(1930, $ranges[2]->getStart()->y);
        $this->assertEquals(7, $ranges[2]->getStart()->m);
        $this->assertEquals(10, $ranges[2]->getStart()->d);
        $this->assertEquals(1930, $ranges[2]->getEnd()->y);
        $this->assertEquals(7, $ranges[2]->getEnd()->m);
        $this->assertEquals(10, $ranges[2]->getEnd()->d);

        $this->assertEquals(1930, $ranges[3]->getStart()->y);
        $this->assertEquals(7, $ranges[3]->getStart()->m);
        $this->assertEquals(12, $ranges[3]->getStart()->d);
        $this->assertEquals(1930, $ranges[3]->getEnd()->y);
        $this->assertEquals(7, $ranges[3]->getEnd()->m);
        $this->assertEquals(12, $ranges[3]->getEnd()->d);

        $this->assertEquals(1930, $ranges[4]->getStart()->y);
        $this->assertEquals(8, $ranges[4]->getStart()->m);
        $this->assertEquals(10, $ranges[4]->getStart()->d);
        $this->assertEquals(1930, $ranges[4]->getEnd()->y);
        $this->assertEquals(8, $ranges[4]->getEnd()->m);
        $this->assertEquals(10, $ranges[4]->getEnd()->d);

        $this->assertEquals(1930, $ranges[5]->getStart()->y);
        $this->assertEquals(8, $ranges[5]->getStart()->m);
        $this->assertEquals(12, $ranges[5]->getStart()->d);
        $this->assertEquals(1930, $ranges[5]->getEnd()->y);
        $this->assertEquals(8, $ranges[5]->getEnd()->m);
        $this->assertEquals(12, $ranges[5]->getEnd()->d);
    }

    public function testApplySeparateDaysInSeparateMonths() : void
    {
        $this->mockClues(null, null, [27, 29], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1969;
        $start->m = 5;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1969;
        $end->m = 5;
        $range->setEnd($end);
        $ranges->append($range);

        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1969;
        $start->m = 8;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1969;
        $end->m = 8;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(4, $ranges);

        $this->assertEquals(1969, $ranges[0]->getStart()->y);
        $this->assertEquals(5, $ranges[0]->getStart()->m);
        $this->assertEquals(27, $ranges[0]->getStart()->d);
        $this->assertEquals(1969, $ranges[0]->getEnd()->y);
        $this->assertEquals(5, $ranges[0]->getEnd()->m);
        $this->assertEquals(27, $ranges[0]->getEnd()->d);

        $this->assertEquals(1969, $ranges[1]->getStart()->y);
        $this->assertEquals(5, $ranges[1]->getStart()->m);
        $this->assertEquals(29, $ranges[1]->getStart()->d);
        $this->assertEquals(1969, $ranges[1]->getEnd()->y);
        $this->assertEquals(5, $ranges[1]->getEnd()->m);
        $this->assertEquals(29, $ranges[1]->getEnd()->d);

        $this->assertEquals(1969, $ranges[2]->getStart()->y);
        $this->assertEquals(8, $ranges[2]->getStart()->m);
        $this->assertEquals(27, $ranges[2]->getStart()->d);
        $this->assertEquals(1969, $ranges[2]->getEnd()->y);
        $this->assertEquals(8, $ranges[2]->getEnd()->m);
        $this->assertEquals(27, $ranges[2]->getEnd()->d);

        $this->assertEquals(1969, $ranges[3]->getStart()->y);
        $this->assertEquals(8, $ranges[3]->getStart()->m);
        $this->assertEquals(29, $ranges[3]->getStart()->d);
        $this->assertEquals(1969, $ranges[3]->getEnd()->y);
        $this->assertEquals(8, $ranges[3]->getEnd()->m);
        $this->assertEquals(29, $ranges[3]->getEnd()->d);
    }

    public function testApplyBeforeAndAfterOverMonthBorder() : void
    {
        $this->mockClues(28, 2, [], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1960;
        $start->m = 2;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1960;
        $end->m = 3;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(3, $ranges);

        $this->assertEquals(1960, $ranges[0]->getStart()->y);
        $this->assertEquals(2, $ranges[0]->getStart()->m);
        $this->assertEquals(1, $ranges[0]->getStart()->d);
        $this->assertEquals(1960, $ranges[0]->getEnd()->y);
        $this->assertEquals(2, $ranges[0]->getEnd()->m);
        $this->assertEquals(2, $ranges[0]->getEnd()->d);

        $this->assertEquals(1960, $ranges[1]->getStart()->y);
        $this->assertEquals(2, $ranges[1]->getStart()->m);
        $this->assertEquals(28, $ranges[1]->getStart()->d);
        $this->assertEquals(1960, $ranges[1]->getEnd()->y);
        $this->assertEquals(3, $ranges[1]->getEnd()->m);
        $this->assertEquals(2, $ranges[1]->getEnd()->d);

        $this->assertEquals(1960, $ranges[2]->getStart()->y);
        $this->assertEquals(3, $ranges[2]->getStart()->m);
        $this->assertEquals(28, $ranges[2]->getStart()->d);
        $this->assertEquals(1960, $ranges[2]->getEnd()->y);
        $this->assertEquals(3, $ranges[2]->getEnd()->m);
        $this->assertEquals(31, $ranges[2]->getEnd()->d);
    }

    public function testDaysInMonthException() : void
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Can not calculate days in month on DateTimeData without y or m.');

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1968;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1969;
        $range->setEnd($end);
        $ranges->append($range);

        $this->sut->apply($ranges);
    }
}
