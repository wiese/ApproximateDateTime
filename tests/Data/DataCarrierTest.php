<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\Data;

use wiese\ApproximateDateTime\Data\DataCarrier;
use PHPUnit\Framework\TestCase;
use wiese\ApproximateDateTime\Data\Type\DateTimeData;

class DataCarrierTest extends TestCase
{

    protected $sut;

    public function setUp() : void
    {
        $this->sut = $this->getMockForTrait(DataCarrier::class);
        $dataProperty = new \ReflectionProperty($this->sut, 'data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($this->sut, new DateTimeData());
    }

    public function testSetGet() : void
    {
        $this->assertNull($this->sut->get('y'));
        $this->assertNull($this->sut->get('m'));
        $this->assertNull($this->sut->get('d'));
        $this->assertNull($this->sut->get('h'));
        $this->assertNull($this->sut->get('i'));
        $this->assertNull($this->sut->get('s'));

        $this->sut->set('y', 2000);
        $this->sut->set('m', 6);
        $this->sut->set('d', 12);
        $this->sut->set('h', 17);
        $this->sut->set('i', 0);
        $this->sut->set('s', 21);

        $this->assertEquals(2000, $this->sut->get('y'));
        $this->assertEquals(6, $this->sut->get('m'));
        $this->assertEquals(12, $this->sut->get('d'));
        $this->assertEquals(17, $this->sut->get('h'));
        $this->assertEquals(0, $this->sut->get('i'));
        $this->assertEquals(21, $this->sut->get('s'));
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
        $one->set('y', 2007);
        $two = clone $this->sut;
        $two->set('y', 2007);

        $this->assertEquals(0, $one->compareTo($two));
        $this->assertEquals(0, $two->compareTo($one));
        $this->assertTrue($one->equals($two));
        $this->assertFalse($one->isBigger($two));
        $this->assertFalse($one->isSmaller($two));

        $one = clone $this->sut;
        $one->set('y', 2006);
        $two = clone $this->sut;
        $two->set('y', 2007);

        $this->assertEquals(-1, $one->compareTo($two));
        $this->assertEquals(1, $two->compareTo($one));
        $this->assertFalse($one->equals($two));
        $this->assertFalse($one->isBigger($two));
        $this->assertTrue($one->isSmaller($two));

        $one = clone $this->sut;
        $one->set('y', 2007);
        $two = clone $this->sut;
        $two->set('y', 2006);

        $this->assertEquals(1, $one->compareTo($two));
        $this->assertEquals(-1, $two->compareTo($one));
        $this->assertFalse($one->equals($two));
        $this->assertTrue($one->isBigger($two));
        $this->assertFalse($one->isSmaller($two));

        $one = clone $this->sut;
        $one->set('y', 2003);
        $one->set('m', 3);
        $one->set('d', 14);
        $two = clone $this->sut;
        $two->set('y', 2003);
        $two->set('m', 3);
        $two->set('d', 15);

        $this->assertEquals(-1, $one->compareTo($two));
        $this->assertFalse($one->equals($two));
        $this->assertFalse($one->isBigger($two));
        $this->assertTrue($one->isSmaller($two));

        $one = clone $this->sut;
        $one->set('y', 2006);
        $one->set('m', 3);
        $one->set('d', 14);
        $two = clone $this->sut;
        $two->set('y', 2003);
        $two->set('m', 3);
        $two->set('d', 15);

        $this->assertEquals(1, $one->compareTo($two));
        $this->assertFalse($one->equals($two));
        $this->assertTrue($one->isBigger($two));
        $this->assertFalse($one->isSmaller($two));

        $one = clone $this->sut;
        $one->set('y', 2007);
        $one->set('m', 10);
        $one->set('d', 30);
        $one->set('h', 9);
        $two = clone $this->sut;
        $two->set('y', 2007);
        $two->set('m', 10);
        $two->set('d', 30);
        $two->set('h', 9);
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
        $one->set('y', 2007);
        $two = clone $this->sut;
        $two->set('m', 3);

        $one->compareTo($two);
    }
}
