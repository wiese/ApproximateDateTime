<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\Clue;
use PHPUnit_Framework_TestCase;

class ClueTest extends PHPUnit_Framework_TestCase
{
    public function testFromArray() : void
    {
        $sut = new Clue();

        $sut = $sut->fromArray(['y' => 2011]);
        $this->assertInstanceOf('wiese\ApproximateDateTime\Clue', $sut);
        $this->assertEquals(2011, $sut->getY());
        $this->assertEquals(2011, $sut->get('y'));
        $this->assertNull($sut->getM());
        $this->assertNull($sut->getD());
        $this->assertNull($sut->getH());
        $this->assertNull($sut->getI());
        $this->assertNull($sut->getS());

        $sut = $sut->fromArray(['y' => 2001, 'm' => 11, 'd' => 27, 'h' => 3, 'i' => 38, 's' => 59]);
        $this->assertInstanceOf('wiese\ApproximateDateTime\Clue', $sut);
        $this->assertEquals(2001, $sut->getY());
        $this->assertEquals(11, $sut->getM());
        $this->assertEquals(27, $sut->getD());
        $this->assertEquals(3, $sut->getH());
        $this->assertEquals(38, $sut->getI());
        $this->assertEquals(59, $sut->getS());

        $sut = $sut->fromArray(['n' => 7]);
        $this->assertEquals(7, $sut->getN());
        $sut->setN(8);
        $this->assertEquals(8, $sut->getN());
    }

    public function testProperties() : void
    {
        $sut = new Clue();
        $sut->rawValue = 'not implemented';
        $sut->filter = $sut::FILTER_AFTEREQUALS;
    }
}
