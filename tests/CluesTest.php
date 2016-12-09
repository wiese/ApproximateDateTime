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
        $this->assertEquals([date('Y')], $this->sut->getWhitelist('y'));
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
        $clue1->type = 'y';
        $clue1->value = 1954;
        $this->sut->append($clue1);

        $clue2 = new Clue;
        $clue2->type = 'm';
        $clue2->value = 7;
        $this->sut->append($clue2);

        $clue3 = new Clue;
        $clue3->type = 'd';
        $clue3->value = 24;
        $this->sut->append($clue3);

        $clue4 = new Clue;
        $clue4->type = 'd';
        $clue4->value = 26;
        $this->sut->append($clue4);

        $this->assertEquals([1954], $this->sut->getWhitelist('y'));
        $this->assertEquals([], $this->sut->getBlacklist('y'));
        $this->assertEquals([7], $this->sut->getWhitelist('m'));
        $this->assertEquals([], $this->sut->getBlacklist('m'));
        $this->assertEquals([24, 26], $this->sut->getWhitelist('d'));
        $this->assertEquals([], $this->sut->getBlacklist('d'));
        $this->assertEquals([], $this->sut->getWhitelist('h'));
        $this->assertEquals([], $this->sut->getBlacklist('h'));
        $this->assertEquals([], $this->sut->getWhitelist('i'));
        $this->assertEquals([], $this->sut->getBlacklist('i'));
        $this->assertEquals([], $this->sut->getWhitelist('s'));
        $this->assertEquals([], $this->sut->getBlacklist('s'));

        $clue5 = new Clue;
        $clue5->type = 'h';
        $clue5->value = 9;
        $this->sut->append($clue5);

        $clue6 = new Clue;
        $clue6->type = 'i';
        $clue6->value = 2;
        $this->sut->append($clue6);

        $clue7 = new Clue;
        $clue7->type = 'i';
        $clue7->value = 1; // i values in "wrong" order
        $this->sut->append($clue7);

        $clue8 = new Clue;
        $clue8->type = 's';
        $clue8->value = 33;
        $this->sut->append($clue8);

        $this->assertEquals([1954], $this->sut->getWhitelist('y'));
        $this->assertEquals([], $this->sut->getBlacklist('y'));
        $this->assertEquals([7], $this->sut->getWhitelist('m'));
        $this->assertEquals([], $this->sut->getBlacklist('m'));
        $this->assertEquals([24, 26], $this->sut->getWhitelist('d'));
        $this->assertEquals([], $this->sut->getBlacklist('d'));
        $this->assertEquals([9], $this->sut->getWhitelist('h'));
        $this->assertEquals([], $this->sut->getBlacklist('h'));
        $this->assertEquals([1, 2], $this->sut->getWhitelist('i')); // ordered
        $this->assertEquals([], $this->sut->getBlacklist('i'));
        $this->assertEquals([33], $this->sut->getWhitelist('s'));
        $this->assertEquals([], $this->sut->getBlacklist('s'));

        $clue9 = new Clue;
        $clue9->type = 'i';
        $clue9->value = 1;
        $clue9->filter = Clue::FILTER_BLACKLIST;
        $this->sut->append($clue9);

        $this->assertEquals([1954], $this->sut->getWhitelist('y'));
        $this->assertEquals([], $this->sut->getBlacklist('y'));
        $this->assertEquals([7], $this->sut->getWhitelist('m'));
        $this->assertEquals([], $this->sut->getBlacklist('m'));
        $this->assertEquals([24, 26], $this->sut->getWhitelist('d'));
        $this->assertEquals([], $this->sut->getBlacklist('d'));
        $this->assertEquals([9], $this->sut->getWhitelist('h'));
        $this->assertEquals([], $this->sut->getBlacklist('h'));
        $this->assertEquals([1, 2], $this->sut->getWhitelist('i'));
        $this->assertEquals([1], $this->sut->getBlacklist('i'));
        $this->assertEquals([33], $this->sut->getWhitelist('s'));
        $this->assertEquals([], $this->sut->getBlacklist('s'));
    }

    public function testBeforeAndAfter() : void
    {
        $clue1 = new Clue;
        $clue1->type = 'm-d';
        $clue1->value = ['m' => 5, 'd' => 10];
        $clue1->filter = Clue::FILTER_BEFOREEQUALS;
        $this->sut->append($clue1);

        $this->assertNull($this->sut->getBefore('y'));
        $this->assertNull($this->sut->getAfter('y'));
        $this->assertEquals(5, $this->sut->getBefore('m'));
        $this->assertNull($this->sut->getAfter('m'));
        $this->assertEquals(10, $this->sut->getBefore('d'));
        $this->assertNull($this->sut->getAfter('d'));
        $this->assertNull($this->sut->getBefore('h'));
        $this->assertNull($this->sut->getAfter('h'));
        $this->assertNull($this->sut->getBefore('i'));
        $this->assertNull($this->sut->getAfter('i'));
        $this->assertNull($this->sut->getBefore('s'));
        $this->assertNull($this->sut->getAfter('s'));

        $clue2 = new Clue;
        $clue2->type = 'm-d';
        $clue2->value = ['m' => 3, 'd' => 12];
        $clue2->filter = Clue::FILTER_BEFOREEQUALS;
        $this->sut->append($clue2);

        $this->assertEquals(3, $this->sut->getBefore('m'));
        $this->assertEquals(10, $this->sut->getBefore('d'));

        $clue3 = new Clue;
        $clue3->type = 'm-d';
        $clue3->value = ['m' => 11, 'd' => 4];
        $clue3->filter = Clue::FILTER_BEFOREEQUALS;
        $this->sut->append($clue3);

        $this->assertEquals(3, $this->sut->getBefore('m'));
        $this->assertEquals(4, $this->sut->getBefore('d'));

        $clue4 = new Clue;
        $clue4->type = 'y';
        $clue4->value = ['y' => 2001];
        $clue4->filter = Clue::FILTER_AFTEREQUALS;
        $this->sut->append($clue4);

        $this->assertNull($this->sut->getBefore('y'));
        $this->assertEquals(2001, $this->sut->getAfter('y'));

        $clue5 = new Clue;
        $clue5->type = 'y';
        $clue5->value = ['y' => 2011];
        $clue5->filter = Clue::FILTER_AFTEREQUALS;
        $this->sut->append($clue5);

        $this->assertNull($this->sut->getBefore('y'));
        $this->assertEquals(2011, $this->sut->getAfter('y'));
    }
}
