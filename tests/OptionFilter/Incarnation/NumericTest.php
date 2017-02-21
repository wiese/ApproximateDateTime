<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\OptionFilter\Incarnation;

use Psr\Log\NullLogger;
use wiese\ApproximateDateTime\Config;
use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\Manager;
use wiese\ApproximateDateTime\OptionFilter\Incarnation\Numeric;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;
use wiese\ApproximateDateTime\Tests\OptionFilter\ParentTest;

class NumericTest extends ParentTest
{

    public function setUp(): void
    {
        $this->sut = new Numeric(new Config(), new NullLogger());
        // keep a reference for modification during individual tests
        $this->clues = $this->getMockBuilder('wiese\ApproximateDateTime\Clues')
            // methods that are mocked; results can be manipulated later
            ->setMethods(['getWhitelist', 'getBlacklist', 'getBefore', 'getAfter'])
            ->getMock();
        $this->sut->setClues($this->clues);
    }

    public function testApplyConsecutive() : void
    {
        $this->sut->setUnit('y');

        $this->mockClues(null, null, [2011, 2012], []);

        // empty so far
        $ranges = new Ranges();

        $ranges = $this->sut->__invoke($ranges);

        $this->assertCount(1, $ranges);
        $this->assertEquals(2011, $ranges[0]->getStart()->getY());
        $this->assertEquals(2012, $ranges[0]->getEnd()->getY());
    }

    public function testApplySeparateRange() : void
    {
        $this->sut->setUnit('y');

        $this->mockClues(null, null, [2008, 2010, 2039, 2040], []);

        // empty so far
        $ranges = new Ranges();

        $ranges = $this->sut->__invoke($ranges);

        $this->assertCount(3, $ranges);
        $this->assertEquals(2008, $ranges[0]->getStart()->getY());
        $this->assertEquals(2008, $ranges[0]->getEnd()->getY());
        $this->assertEquals(2010, $ranges[1]->getStart()->getY());
        $this->assertEquals(2010, $ranges[1]->getEnd()->getY());
        $this->assertEquals(2039, $ranges[2]->getStart()->getY());
        $this->assertEquals(2040, $ranges[2]->getEnd()->getY());
    }

    public function testWithMergePreexisting() : void
    {
        $this->sut->setUnit('m');

        $this->mockClues(null, null, [3, 4], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData();
        $start->setY(1995);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(1995);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->__invoke($ranges);

        $this->assertCount(1, $ranges);
        $this->assertEquals(1995, $ranges[0]->getStart()->getY());
        $this->assertEquals(3, $ranges[0]->getStart()->getM());
        $this->assertEquals(1995, $ranges[0]->getEnd()->getY());
        $this->assertEquals(4, $ranges[0]->getEnd()->getM());
    }

    public function testBeforeAndAfter() : void
    {
        $this->sut->setUnit('m');

        $this->mockClues(2, 7, [], []);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData();
        $start->setY(1995);
        $range->setStart($start);
        $end = new DateTimeData();
        $end->setY(1995);
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $this->sut->__invoke($ranges);

        $this->assertCount(1, $ranges);
        $this->assertEquals(1995, $ranges[0]->getStart()->getY());
        $this->assertEquals(2, $ranges[0]->getStart()->getM());
        $this->assertEquals(1995, $ranges[0]->getEnd()->getY());
        $this->assertEquals(7, $ranges[0]->getEnd()->getM());
    }
}
