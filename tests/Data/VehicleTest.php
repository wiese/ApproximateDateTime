<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\Data;

use wiese\ApproximateDateTime\Data\Vehicle;
use PHPUnit\Framework\TestCase;

class VehicleTest extends TestCase
{

    /**
     * @var Vehicle
     */
    protected $sut;

    public function setUp() : void
    {
        $this->sut = $this->getMockForAbstractClass('wiese\ApproximateDateTime\Data\Vehicle');
    }
    public function testFromArray() : void
    {
        $sut = $this->sut;
        $sut->fromArray(['y' => 2011]);
        $this->assertInstanceOf('wiese\ApproximateDateTime\Data\Vehicle', $sut);
        $this->assertEquals(2011, $sut->getY());
        $this->assertEquals(2011, $sut->get('y'));
        $this->assertNull($sut->getM());
        $this->assertNull($sut->getD());
        $this->assertNull($sut->getH());
        $this->assertNull($sut->getI());
        $this->assertNull($sut->getS());

        $sut = $this->sut;
        $sut->fromArray(['y' => 2001, 'm' => 11, 'd' => 27, 'h' => 3, 'i' => 38, 's' => 59]);
        $this->assertInstanceOf('wiese\ApproximateDateTime\Data\Vehicle', $sut);
        $this->assertEquals(2001, $sut->getY());
        $this->assertEquals(11, $sut->getM());
        $this->assertEquals(27, $sut->getD());
        $this->assertEquals(3, $sut->getH());
        $this->assertEquals(38, $sut->getI());
        $this->assertEquals(59, $sut->getS());
    }

    public function testFromArrayBadUnit() : void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unknow date unit k');

        $this->sut->fromArray(['y' => 2001, 'k' => 2]);
    }

    public function testSetBadUnit() : void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unknow date unit f');

        $this->sut->set('f', 13);
    }

    public function testGetBadUnit() : void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unknow date unit b');

        $this->sut->get('b');
    }

    public function testCompareTo() : void
    {
        $one = clone $this->sut;
        $one->fromArray(['y' => 2007]);
        $two = clone $this->sut;
        $two->fromArray(['y' => 2007]);

        $this->assertEquals(0, $one->compareTo($two));
        $this->assertEquals(0, $two->compareTo($one));
        $this->assertTrue($one->equals($two));
        $this->assertFalse($one->isBigger($two));
        $this->assertFalse($one->isSmaller($two));

        $one = clone $this->sut;
        $one->fromArray(['y' => 2006]);
        $two = clone $this->sut;
        $two->fromArray(['y' => 2007]);

        $this->assertEquals(-1, $one->compareTo($two));
        $this->assertEquals(1, $two->compareTo($one));
        $this->assertFalse($one->equals($two));
        $this->assertFalse($one->isBigger($two));
        $this->assertTrue($one->isSmaller($two));

        $one = clone $this->sut;
        $one->fromArray(['y' => 2007]);
        $two = clone $this->sut;
        $two->fromArray(['y' => 2006]);

        $this->assertEquals(1, $one->compareTo($two));
        $this->assertEquals(-1, $two->compareTo($one));
        $this->assertFalse($one->equals($two));
        $this->assertTrue($one->isBigger($two));
        $this->assertFalse($one->isSmaller($two));

        $one = clone $this->sut;
        $one->fromArray(['y' => 2003, 'm' => 3, 'd' => 14]);
        $two = clone $this->sut;
        $two->fromArray(['y' => 2003, 'm' => 3, 'd' => 15]);

        $this->assertEquals(-1, $one->compareTo($two));
        $this->assertFalse($one->equals($two));
        $this->assertFalse($one->isBigger($two));
        $this->assertTrue($one->isSmaller($two));

        $one = clone $this->sut;
        $one->fromArray(['y' => 2006, 'm' => 3, 'd' => 14]);
        $two = clone $this->sut;
        $two->fromArray(['y' => 2003, 'm' => 3, 'd' => 15]);

        $this->assertEquals(1, $one->compareTo($two));
        $this->assertFalse($one->equals($two));
        $this->assertTrue($one->isBigger($two));
        $this->assertFalse($one->isSmaller($two));

        $one = clone $this->sut;
        $one->fromArray(['y' => 2007, 'm' => 10, 'd' => 30, 'h' => 9]);
        $two = clone $this->sut;
        $two->fromArray(['y' => 2007, 'm' => 10, 'd' => 30, 'h' => 9]);
        $this->assertEquals(0, $one->compareTo($two));
        $this->assertTrue($one->equals($two));
        $this->assertFalse($one->isBigger($two));
        $this->assertFalse($one->isSmaller($two));
    }

    public function testCompareToUncomparable() : void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Can not compare objects with different units set.');

        $one = clone $this->sut;
        $one->fromArray(['y' => 2007]);
        $two = clone $this->sut;
        $two->fromArray(['m' => 3]);

        $one->compareTo($two);
    }
}
