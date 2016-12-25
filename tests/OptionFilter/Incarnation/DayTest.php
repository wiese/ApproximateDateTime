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
        $start->setY(1993);
        $start->setM(5);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1993);
        $end->setM(5);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertEquals(1993, $ranges[0]->getStart()->getY());
        $this->assertEquals(5, $ranges[0]->getStart()->getM());
        $this->assertEquals(1, $ranges[0]->getStart()->getD());
        $this->assertEquals(1993, $ranges[0]->getEnd()->getY());
        $this->assertEquals(5, $ranges[0]->getEnd()->getM());
        $this->assertEquals(31, $ranges[0]->getEnd()->getD());
    }

    public function testApplyAllDaysInConsecutiveMonths() : void
    {
        $this->mockClues(null, null, [], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1998);
        $start->setM(1);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1998);
        $end->setM(2);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(1, $ranges);

        $this->assertEquals(1998, $ranges[0]->getStart()->getY());
        $this->assertEquals(1, $ranges[0]->getStart()->getM());
        $this->assertEquals(1, $ranges[0]->getStart()->getD());
        $this->assertEquals(1998, $ranges[0]->getEnd()->getY());
        $this->assertEquals(2, $ranges[0]->getEnd()->getM());
        $this->assertEquals(28, $ranges[0]->getEnd()->getD());
    }

    public function testApplyAllDaysThroughBigWhitelistInConsecutiveMonths() : void
    {
        $this->mockClues(null, null, range(1, 31), []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1998);
        $start->setM(1);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1998);
        $end->setM(2);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(1, $ranges);

        $this->assertEquals(1998, $ranges[0]->getStart()->getY());
        $this->assertEquals(1, $ranges[0]->getStart()->getM());
        $this->assertEquals(1, $ranges[0]->getStart()->getD());
        $this->assertEquals(1998, $ranges[0]->getEnd()->getY());
        $this->assertEquals(2, $ranges[0]->getEnd()->getM());
        $this->assertEquals(28, $ranges[0]->getEnd()->getD());
    }

    public function testApplyAllDaysInSeparateMonths() : void
    {
        $this->mockClues(null, null, [], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1953);
        $start->setM(10);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1953);
        $end->setM(10);
        $range->setEnd($end);
        $ranges->append($range);
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1954);
        $start->setM(5);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1954);
        $end->setM(5);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(2, $ranges);

        $this->assertEquals(1953, $ranges[0]->getStart()->getY());
        $this->assertEquals(10, $ranges[0]->getStart()->getM());
        $this->assertEquals(1, $ranges[0]->getStart()->getD());
        $this->assertEquals(1953, $ranges[0]->getEnd()->getY());
        $this->assertEquals(10, $ranges[0]->getEnd()->getM());
        $this->assertEquals(31, $ranges[0]->getEnd()->getD());

        $this->assertEquals(1954, $ranges[1]->getStart()->getY());
        $this->assertEquals(5, $ranges[1]->getStart()->getM());
        $this->assertEquals(1, $ranges[1]->getStart()->getD());
        $this->assertEquals(1954, $ranges[1]->getEnd()->getY());
        $this->assertEquals(5, $ranges[1]->getEnd()->getM());
        $this->assertEquals(31, $ranges[1]->getEnd()->getD());
    }

    public function testApplyConsecutiveDaysInMonth() : void
    {
        $this->mockClues(null, null, [3, 4, 5], [5]);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1958);
        $start->setM(11);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1958);
        $end->setM(11);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(1, $ranges);

        $this->assertEquals(1958, $ranges[0]->getStart()->getY());
        $this->assertEquals(11, $ranges[0]->getStart()->getM());
        $this->assertEquals(3, $ranges[0]->getStart()->getD());
        $this->assertEquals(1958, $ranges[0]->getEnd()->getY());
        $this->assertEquals(11, $ranges[0]->getEnd()->getM());
        $this->assertEquals(4, $ranges[0]->getEnd()->getD());
    }

    public function testApplyConsecutiveDaysInConsecutiveMonths() : void
    {
        $this->mockClues(null, null, [7, 8, 9], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1997);
        $start->setM(11);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1997);
        $end->setM(12);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(2, $ranges);

        $this->assertEquals(1997, $ranges[0]->getStart()->getY());
        $this->assertEquals(11, $ranges[0]->getStart()->getM());
        $this->assertEquals(7, $ranges[0]->getStart()->getD());
        $this->assertEquals(1997, $ranges[0]->getEnd()->getY());
        $this->assertEquals(11, $ranges[0]->getEnd()->getM());
        $this->assertEquals(9, $ranges[0]->getEnd()->getD());

        $this->assertEquals(1997, $ranges[1]->getStart()->getY());
        $this->assertEquals(12, $ranges[1]->getStart()->getM());
        $this->assertEquals(7, $ranges[1]->getStart()->getD());
        $this->assertEquals(1997, $ranges[1]->getEnd()->getY());
        $this->assertEquals(12, $ranges[1]->getEnd()->getM());
        $this->assertEquals(9, $ranges[1]->getEnd()->getD());
    }

    public function testApplyConsecutiveDaysInSeparateMonths() : void
    {
        $this->mockClues(null, null, [27, 28], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1962);
        $start->setM(12);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1962);
        $end->setM(12);
        $range->setEnd($end);
        $ranges->append($range);

        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1963);
        $start->setM(9);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1963);
        $end->setM(9);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(2, $ranges);

        $this->assertEquals(1962, $ranges[0]->getStart()->getY());
        $this->assertEquals(12, $ranges[0]->getStart()->getM());
        $this->assertEquals(27, $ranges[0]->getStart()->getD());
        $this->assertEquals(1962, $ranges[0]->getEnd()->getY());
        $this->assertEquals(12, $ranges[0]->getEnd()->getM());
        $this->assertEquals(28, $ranges[0]->getEnd()->getD());

        $this->assertEquals(1963, $ranges[1]->getStart()->getY());
        $this->assertEquals(9, $ranges[1]->getStart()->getM());
        $this->assertEquals(27, $ranges[1]->getStart()->getD());
        $this->assertEquals(1963, $ranges[1]->getEnd()->getY());
        $this->assertEquals(9, $ranges[1]->getEnd()->getM());
        $this->assertEquals(28, $ranges[1]->getEnd()->getD());
    }

    public function testApplySeparateDaysInMonth() : void
    {
        $this->mockClues(null, null, [3, 4, 5], [4]);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1958);
        $start->setM(11);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1958);
        $end->setM(11);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(2, $ranges);

        $this->assertEquals(1958, $ranges[0]->getStart()->getY());
        $this->assertEquals(11, $ranges[0]->getStart()->getM());
        $this->assertEquals(3, $ranges[0]->getStart()->getD());
        $this->assertEquals(1958, $ranges[0]->getEnd()->getY());
        $this->assertEquals(11, $ranges[0]->getEnd()->getM());
        $this->assertEquals(3, $ranges[0]->getEnd()->getD());

        $this->assertEquals(1958, $ranges[1]->getStart()->getY());
        $this->assertEquals(11, $ranges[1]->getStart()->getM());
        $this->assertEquals(5, $ranges[1]->getStart()->getD());
        $this->assertEquals(1958, $ranges[1]->getEnd()->getY());
        $this->assertEquals(11, $ranges[1]->getEnd()->getM());
        $this->assertEquals(5, $ranges[1]->getEnd()->getD());
    }

    public function testApplySeparateDaysInConsecutiveMonths() : void
    {
        $this->mockClues(null, null, [10, 12], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1930);
        $start->setM(6);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1930);
        $end->setM(8);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(6, $ranges);

        $this->assertEquals(1930, $ranges[0]->getStart()->getY());
        $this->assertEquals(6, $ranges[0]->getStart()->getM());
        $this->assertEquals(10, $ranges[0]->getStart()->getD());
        $this->assertEquals(1930, $ranges[0]->getEnd()->getY());
        $this->assertEquals(6, $ranges[0]->getEnd()->getM());
        $this->assertEquals(10, $ranges[0]->getEnd()->getD());

        $this->assertEquals(1930, $ranges[1]->getStart()->getY());
        $this->assertEquals(6, $ranges[1]->getStart()->getM());
        $this->assertEquals(12, $ranges[1]->getStart()->getD());
        $this->assertEquals(1930, $ranges[1]->getEnd()->getY());
        $this->assertEquals(6, $ranges[1]->getEnd()->getM());
        $this->assertEquals(12, $ranges[1]->getEnd()->getD());

        $this->assertEquals(1930, $ranges[2]->getStart()->getY());
        $this->assertEquals(7, $ranges[2]->getStart()->getM());
        $this->assertEquals(10, $ranges[2]->getStart()->getD());
        $this->assertEquals(1930, $ranges[2]->getEnd()->getY());
        $this->assertEquals(7, $ranges[2]->getEnd()->getM());
        $this->assertEquals(10, $ranges[2]->getEnd()->getD());

        $this->assertEquals(1930, $ranges[3]->getStart()->getY());
        $this->assertEquals(7, $ranges[3]->getStart()->getM());
        $this->assertEquals(12, $ranges[3]->getStart()->getD());
        $this->assertEquals(1930, $ranges[3]->getEnd()->getY());
        $this->assertEquals(7, $ranges[3]->getEnd()->getM());
        $this->assertEquals(12, $ranges[3]->getEnd()->getD());

        $this->assertEquals(1930, $ranges[4]->getStart()->getY());
        $this->assertEquals(8, $ranges[4]->getStart()->getM());
        $this->assertEquals(10, $ranges[4]->getStart()->getD());
        $this->assertEquals(1930, $ranges[4]->getEnd()->getY());
        $this->assertEquals(8, $ranges[4]->getEnd()->getM());
        $this->assertEquals(10, $ranges[4]->getEnd()->getD());

        $this->assertEquals(1930, $ranges[5]->getStart()->getY());
        $this->assertEquals(8, $ranges[5]->getStart()->getM());
        $this->assertEquals(12, $ranges[5]->getStart()->getD());
        $this->assertEquals(1930, $ranges[5]->getEnd()->getY());
        $this->assertEquals(8, $ranges[5]->getEnd()->getM());
        $this->assertEquals(12, $ranges[5]->getEnd()->getD());
    }

    public function testApplySeparateDaysInSeparateMonths() : void
    {
        $this->mockClues(null, null, [27, 29], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1969);
        $start->setM(5);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1969);
        $end->setM(5);
        $range->setEnd($end);
        $ranges->append($range);

        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1969);
        $start->setM(8);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1969);
        $end->setM(8);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(4, $ranges);

        $this->assertEquals(1969, $ranges[0]->getStart()->getY());
        $this->assertEquals(5, $ranges[0]->getStart()->getM());
        $this->assertEquals(27, $ranges[0]->getStart()->getD());
        $this->assertEquals(1969, $ranges[0]->getEnd()->getY());
        $this->assertEquals(5, $ranges[0]->getEnd()->getM());
        $this->assertEquals(27, $ranges[0]->getEnd()->getD());

        $this->assertEquals(1969, $ranges[1]->getStart()->getY());
        $this->assertEquals(5, $ranges[1]->getStart()->getM());
        $this->assertEquals(29, $ranges[1]->getStart()->getD());
        $this->assertEquals(1969, $ranges[1]->getEnd()->getY());
        $this->assertEquals(5, $ranges[1]->getEnd()->getM());
        $this->assertEquals(29, $ranges[1]->getEnd()->getD());

        $this->assertEquals(1969, $ranges[2]->getStart()->getY());
        $this->assertEquals(8, $ranges[2]->getStart()->getM());
        $this->assertEquals(27, $ranges[2]->getStart()->getD());
        $this->assertEquals(1969, $ranges[2]->getEnd()->getY());
        $this->assertEquals(8, $ranges[2]->getEnd()->getM());
        $this->assertEquals(27, $ranges[2]->getEnd()->getD());

        $this->assertEquals(1969, $ranges[3]->getStart()->getY());
        $this->assertEquals(8, $ranges[3]->getStart()->getM());
        $this->assertEquals(29, $ranges[3]->getStart()->getD());
        $this->assertEquals(1969, $ranges[3]->getEnd()->getY());
        $this->assertEquals(8, $ranges[3]->getEnd()->getM());
        $this->assertEquals(29, $ranges[3]->getEnd()->getD());
    }

    public function testApplyBeforeAndAfterOverMonthBorder() : void
    {
        $this->mockClues(28, 2, [], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1960);
        $start->setM(2);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1960);
        $end->setM(3);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(3, $ranges);

        $this->assertEquals(1960, $ranges[0]->getStart()->getY());
        $this->assertEquals(2, $ranges[0]->getStart()->getM());
        $this->assertEquals(1, $ranges[0]->getStart()->getD());
        $this->assertEquals(1960, $ranges[0]->getEnd()->getY());
        $this->assertEquals(2, $ranges[0]->getEnd()->getM());
        $this->assertEquals(2, $ranges[0]->getEnd()->getD());

        $this->assertEquals(1960, $ranges[1]->getStart()->getY());
        $this->assertEquals(2, $ranges[1]->getStart()->getM());
        $this->assertEquals(28, $ranges[1]->getStart()->getD());
        $this->assertEquals(1960, $ranges[1]->getEnd()->getY());
        $this->assertEquals(3, $ranges[1]->getEnd()->getM());
        $this->assertEquals(2, $ranges[1]->getEnd()->getD());

        $this->assertEquals(1960, $ranges[2]->getStart()->getY());
        $this->assertEquals(3, $ranges[2]->getStart()->getM());
        $this->assertEquals(28, $ranges[2]->getStart()->getD());
        $this->assertEquals(1960, $ranges[2]->getEnd()->getY());
        $this->assertEquals(3, $ranges[2]->getEnd()->getM());
        $this->assertEquals(31, $ranges[2]->getEnd()->getD());
    }

    public function testDaysInMonthException() : void
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Can not calculate days in month on DateTimeData without y or m.');

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->setY(1968);
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->setY(1969);
        $range->setEnd($end);
        $ranges->append($range);

        $this->sut->apply($ranges);
    }
}
