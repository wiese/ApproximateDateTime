<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use UnexpectedValueException;
use wiese\ApproximateDateTime\Clue;
use PHPUnit\Framework\TestCase;

class ClueTest extends TestCase
{

    public function testConstruct() : void
    {
        $sut = new Clue(Clue::IS_WHITELIST);
        $this->assertEquals(Clue::IS_WHITELIST, $sut->getType());

        $sut = new Clue(Clue::IS_BLACKLIST);
        $this->assertEquals(Clue::IS_BLACKLIST, $sut->getType());

        $sut = new Clue(Clue::IS_BEFOREEQUALS);
        $this->assertEquals(Clue::IS_BEFOREEQUALS, $sut->getType());

        $sut = new Clue(Clue::IS_AFTEREQUALS);
        $this->assertEquals(Clue::IS_AFTEREQUALS, $sut->getType());
    }

    public function testBadConstruct() : void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Bad Clue type given: 11');

        new Clue(11);
    }

    public function testProperties() : void
    {
        $this->assertInternalType('int', Clue::IS_WHITELIST);
        $this->assertInternalType('int', Clue::IS_BLACKLIST);
        $this->assertInternalType('int', Clue::IS_BEFOREEQUALS);
        $this->assertInternalType('int', Clue::IS_AFTEREQUALS);

        $sut = new Clue();

        $this->assertNull($sut->getY());
        $this->assertNull($sut->getM());
        $this->assertNull($sut->getD());
        $this->assertNull($sut->getH());
        $this->assertNull($sut->getI());
        $this->assertNull($sut->getS());
        $this->assertNull($sut->getN());

        $sut->setY(2007);
        $sut->setM(8);
        $sut->setD(30);
        $sut->setH(8);
        $sut->setI(37);
        $sut->setS(20);
        $sut->setN(6);

        $this->assertEquals(2007, $sut->getY());
        $this->assertEquals(8, $sut->getM());
        $this->assertEquals(30, $sut->getD());
        $this->assertEquals(8, $sut->getH());
        $this->assertEquals(37, $sut->getI());
        $this->assertEquals(20, $sut->getS());
        $this->assertEquals(6, $sut->getN());
    }

    public function testGetSetUnits() : void
    {
        $sut = new Clue();
        $this->assertEmpty($sut->getSetUnits());

        $sut->setY(333);
        $sut->setM(5);
        $this->assertEquals(['y', 'm'], $sut->getSetUnits());
    }
}
