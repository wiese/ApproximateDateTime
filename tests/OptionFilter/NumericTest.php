<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\OptionFilter;

use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\OptionFilter\Numeric;
use wiese\ApproximateDateTime\Ranges;
use wiese\ApproximateDateTime\Range;
use DateTimeZone;
use PHPUnit_Framework_TestCase;

class NumericTest extends PHPUnit_Framework_TestCase
{
    public function testApplyConsecutive() : void
    {
        $sut = new Numeric;
        $sut->setUnit('y');
        $sut->setWhitelist(array(2011, 2012));
        $sut->setTimezone(new DateTimeZone('Asia/Kathmandu'));

        // empty so far
        $ranges = new Ranges();

        $ranges = $sut->apply($ranges);

        $this->assertCount(1, $ranges);
        $this->assertEquals(2011, $ranges[0]->getStart()->y);
        $this->assertEquals(2012, $ranges[0]->getEnd()->y);
    }

    public function testApplySeparateRange() : void
    {
        $sut = new Numeric;
        $sut->setUnit('y');
        $sut->setWhitelist(array(2008, 2010, 2039, 2040));
        $sut->setTimezone(new DateTimeZone('Australia/Darwin'));

        // empty so far
        $ranges = new Ranges();

        $ranges = $sut->apply($ranges);

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
        $sut = new Numeric;
        $sut->setUnit('m');
        $sut->setWhitelist(array(3, 4));
        $tz = new DateTimeZone('Europe/Berlin');
        $sut->setTimezone($tz);

        $ranges = new Ranges();
        $range = new Range();
        $start = new DateTimeData($tz);
        $start->y = 1995;
        $range->setStart($start);
        $end = new DateTimeData($tz);
        $end->y = 1995;
        $range->setEnd($end);
        $ranges->append($range);

        $ranges = $sut->apply($ranges);

        $this->assertCount(1, $ranges);
        $this->assertEquals(1995, $ranges[0]->getStart()->y);
        $this->assertEquals(3, $ranges[0]->getStart()->m);
        $this->assertEquals(1995, $ranges[0]->getEnd()->y);
        $this->assertEquals(4, $ranges[0]->getEnd()->m);
    }
}
