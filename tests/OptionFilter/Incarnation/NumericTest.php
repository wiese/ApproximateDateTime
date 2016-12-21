<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\OptionFilter\Incarnation;

use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\OptionFilter\Incarnation\Numeric;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;
use DateTimeZone;
use PHPUnit_Framework_TestCase;

class NumericTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var DateTimeZone
     */
    protected $tz;

    /**
     * @var Numeric
     */
    protected $sut;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject Of Clues
     */
    protected $clues;

    public function setUp(): void
    {
        $this->sut = new Numeric;
        $this->tz = new DateTimeZone('Asia/Kathmandu');
        $this->sut->setTimezone($this->tz);
        // keep a reference for modification during individual tests
        $this->clues = $this->getMockBuilder('wiese\ApproximateDateTime\Clues')
            // methods that are mocked; results can be manipulated later
            ->setMethods(['getWhitelist', 'getBlacklist'])
            ->getMock();
        $this->sut->setClues($this->clues);
    }

    public function testApplyConsecutive() : void
    {
        $this->sut->setUnit('y');
        $this->clues->method('getWhitelist')->willReturn([2011, 2012]);

        // empty so far
        $ranges = new Ranges();

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(1, $ranges);
        $this->assertEquals(2011, $ranges[0]->getStart()->y);
        $this->assertEquals(2012, $ranges[0]->getEnd()->y);
    }

    public function testApplySeparateRange() : void
    {
        $this->sut->setUnit('y');
        $this->clues->method('getWhitelist')->willReturn([2008, 2010, 2039, 2040]);

        // empty so far
        $ranges = new Ranges();

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(3, $ranges);
        $this->assertEquals(2008, $ranges[0]->getStart()->y);
        $this->assertEquals(2008, $ranges[0]->getEnd()->y);
        $this->assertEquals(2010, $ranges[1]->getStart()->y);
        $this->assertEquals(2010, $ranges[1]->getEnd()->y);
        $this->assertEquals(2039, $ranges[2]->getStart()->y);
        $this->assertEquals(2040, $ranges[2]->getEnd()->y);
    }

    public function testWithMergePreexisting() : void
    {
        $this->sut->setUnit('m');
        $this->clues->method('getWhitelist')->willReturn([3, 4]);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($this->tz);
        $start->y = 1995;
        $range->setStart($start);
        $end = new DateTimeData($this->tz);
        $end->y = 1995;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->apply($ranges);

        $this->assertCount(1, $ranges);
        $this->assertEquals(1995, $ranges[0]->getStart()->y);
        $this->assertEquals(3, $ranges[0]->getStart()->m);
        $this->assertEquals(1995, $ranges[0]->getEnd()->y);
        $this->assertEquals(4, $ranges[0]->getEnd()->m);
    }
}
