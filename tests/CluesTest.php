<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\Clues;
use PHPUnit_Framework_TestCase;

class CluesTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Clues
     */
    protected $sut;

    public function setUp() : void
    {
        $this->sut = new Clues;
    }

    public function testDefault() : void
    {
        $defaultYear = new Clue();
        $defaultYear->setY((int) date('Y'));
        $this->assertEquals([$defaultYear], $this->sut->getWhitelist('y'));
        $this->assertEquals([], $this->sut->getWhitelist('m'));
        $this->assertEquals([], $this->sut->getWhitelist('d'));
        $this->assertEquals([], $this->sut->getWhitelist('h'));
        $this->assertEquals([], $this->sut->getWhitelist('i'));
        $this->assertEquals([], $this->sut->getWhitelist('s'));

        $properties = ['y', 'm', 'd', 'h', 'i', 's'];

        foreach ($properties as $property) {
            $this->assertEquals([], $this->sut->getBlacklist($property));
        }

        $methods = ['getBefore', 'getAfter'];
        foreach ($methods as $method) {
            foreach ($properties as $property) {
                $this->assertNull($this->sut->$method($property));
            }
        }
    }

    public function testBlackAndWhitelist() : void
    {
        $clue1 = new Clue;
        $clue1->setY(1954);
        $this->sut->append($clue1);

        $clue2 = new Clue;
        $clue2->setM(7);
        $this->sut->append($clue2);

        $clue3 = new Clue;
        $clue3->setD(24);
        $this->sut->append($clue3);

        $clue4 = new Clue;
        $clue4->setD(26);
        $this->sut->append($clue4);

        $this->assertEquals([$clue1], $this->sut->getWhitelist('y'));
        $this->assertEquals([], $this->sut->getBlacklist('y'));
        $this->assertEquals([$clue2], $this->sut->getWhitelist('m'));
        $this->assertEquals([], $this->sut->getBlacklist('m'));
        $this->assertEquals([$clue3, $clue4], $this->sut->getWhitelist('d'));
        $this->assertEquals([], $this->sut->getBlacklist('d'));
        $this->assertEquals([], $this->sut->getWhitelist('h'));
        $this->assertEquals([], $this->sut->getBlacklist('h'));
        $this->assertEquals([], $this->sut->getWhitelist('i'));
        $this->assertEquals([], $this->sut->getBlacklist('i'));
        $this->assertEquals([], $this->sut->getWhitelist('s'));
        $this->assertEquals([], $this->sut->getBlacklist('s'));

        $clue5 = new Clue;
        $clue5->setH(9);
        $this->sut->append($clue5);

        $clue6 = new Clue;
        $clue6->setI(2);
        $this->sut->append($clue6);

        $clue7 = new Clue;
        $clue7->setI(1); // i values in "wrong" order
        $this->sut->append($clue7);

        $clue8 = new Clue;
        $clue8->setS(33);
        $this->sut->append($clue8);

        $this->assertEquals([$clue1], $this->sut->getWhitelist('y'));
        $this->assertEquals([], $this->sut->getBlacklist('y'));
        $this->assertEquals([$clue2], $this->sut->getWhitelist('m'));
        $this->assertEquals([], $this->sut->getBlacklist('m'));
        $this->assertEquals([$clue3, $clue4], $this->sut->getWhitelist('d'));
        $this->assertEquals([], $this->sut->getBlacklist('d'));
        $this->assertEquals([$clue5], $this->sut->getWhitelist('h'));
        $this->assertEquals([], $this->sut->getBlacklist('h'));
        $this->assertEquals([$clue7, $clue6], $this->sut->getWhitelist('i')); // ordered
        $this->assertEquals([], $this->sut->getBlacklist('i'));
        $this->assertEquals([$clue8], $this->sut->getWhitelist('s'));
        $this->assertEquals([], $this->sut->getBlacklist('s'));

        $clue9 = new Clue;
        $clue9->setI(1);
        $clue9->type = Clue::IS_BLACKLIST;
        $this->sut->append($clue9);

        $this->assertEquals([$clue1], $this->sut->getWhitelist('y'));
        $this->assertEquals([], $this->sut->getBlacklist('y'));
        $this->assertEquals([$clue2], $this->sut->getWhitelist('m'));
        $this->assertEquals([], $this->sut->getBlacklist('m'));
        $this->assertEquals([$clue3, $clue4], $this->sut->getWhitelist('d'));
        $this->assertEquals([], $this->sut->getBlacklist('d'));
        $this->assertEquals([$clue5], $this->sut->getWhitelist('h'));
        $this->assertEquals([], $this->sut->getBlacklist('h'));
        $this->assertEquals([$clue7, $clue6], $this->sut->getWhitelist('i'));
        $this->assertEquals([$clue9], $this->sut->getBlacklist('i'));
        $this->assertEquals([$clue8], $this->sut->getWhitelist('s'));
        $this->assertEquals([], $this->sut->getBlacklist('s'));
    }

    public function testBeforeAndAfter() : void
    {
        $this->markTestIncomplete();
        // test case was incomplete before - clue properties were not used in conjunction, but separately

        $clue1 = new Clue;
        $clue1->setM(5);
        $clue1->setD(10);
        $clue1->type = Clue::IS_BEFOREEQUALS;
        $this->sut->append($clue1);

        $this->assertNull($this->sut->getBefore('y'));
        $this->assertNull($this->sut->getAfter('y'));
        $this->assertNull($this->sut->getBefore('m'));
        $this->assertNull($this->sut->getAfter('m'));

        // what? how?
        //$this->assertEquals(5, $this->sut->getBefore('m-d'));
        //$this->assertNull($this->sut->getAfter('m-d'));

        $this->assertNull($this->sut->getBefore('d'));
        $this->assertNull($this->sut->getAfter('d'));
        $this->assertNull($this->sut->getBefore('h'));
        $this->assertNull($this->sut->getAfter('h'));
        $this->assertNull($this->sut->getBefore('i'));
        $this->assertNull($this->sut->getAfter('i'));
        $this->assertNull($this->sut->getBefore('s'));
        $this->assertNull($this->sut->getAfter('s'));

        $clue2 = new Clue;
        $clue2->setM(3);
        $clue2->setD(12);
        $clue2->type = Clue::IS_BEFOREEQUALS;
        $this->sut->append($clue2);

        $this->assertEquals(3, $this->sut->getBefore('m'));
        $this->assertEquals(10, $this->sut->getBefore('d'));

        $clue3 = new Clue;
        $clue3->setM(11);
        $clue3->setD(4);
        $clue3->type = Clue::IS_BEFOREEQUALS;
        $this->sut->append($clue3);

        $this->assertEquals(3, $this->sut->getBefore('m'));
        $this->assertEquals(4, $this->sut->getBefore('d'));

        $clue4 = new Clue;
        $clue4->setY(2001);
        $clue4->type = Clue::IS_AFTEREQUALS;
        $this->sut->append($clue4);

        $this->assertNull($this->sut->getBefore('y'));
        $this->assertEquals(2001, $this->sut->getAfter('y'));

        $clue5 = new Clue;
        $clue5->setY(2011);
        $clue5->type = Clue::IS_AFTEREQUALS;
        $this->sut->append($clue5);

        $this->assertNull($this->sut->getBefore('y'));
        $this->assertEquals(2011, $this->sut->getAfter('y'));
    }
}
