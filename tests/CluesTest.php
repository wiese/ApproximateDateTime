<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\Clues;
use PHPUnit\Framework\TestCase;

class CluesTest extends TestCase
{

    /**
     * @var Clues
     */
    private $sut;

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
        $clue1 = new Clue();
        $clue1->setY(1954);
        $this->sut->append($clue1);

        $clue2 = new Clue();
        $clue2->setM(7);
        $this->sut->append($clue2);

        $clue3 = new Clue();
        $clue3->setD(24);
        $this->sut->append($clue3);

        $clue4 = new Clue();
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

        $clue5 = new Clue();
        $clue5->setH(9);
        $this->sut->append($clue5);

        $clue6 = new Clue();
        $clue6->setI(2);
        $this->sut->append($clue6);

        $clue7 = new Clue();
        $clue7->setI(1); // i values in "wrong" order
        $this->sut->append($clue7);

        $clue8 = new Clue();
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

        $clue9 = new Clue(Clue::IS_BLACKLIST);
        $clue9->setI(1);
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
}
