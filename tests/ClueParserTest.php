<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\ApproximateDateTime;
use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\ClueParser;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

class ClueParserTest extends PHPUnit_Framework_TestCase
{
    public function testCastClue()
    {
        $sut = new ClueParser();

        $class = new ReflectionClass($sut);
        $method = $class->getMethod('processClue');
        $method->setAccessible(true);

        $clue = new Clue;
        $clue->type = 'y';
        $clue->rawValue = 2014;

        $this->assertEquals($clue, $method->invoke($sut, '2014'));
    }

    public function testComplicatedClueCast()
    {
        $sut = new ClueParser();

        $class = new ReflectionClass($sut);
        $method = $class->getMethod('processClue');
        $method->setAccessible(true);

        $clue = new Clue;
        $clue->type = 'y';
        $clue->rawValue = '2016';

        $this->assertEquals($clue, $method->invoke($sut, '2016'));

        $this->markTestIncomplete();

        $clue = new Clue;
        $clue->type = 'weekend';
        $clue->value = true;
        $clue->rawValue = 'Weekend';

        $this->assertEquals($clue, $method->invoke($sut, 'Weekend'));

        $clue = new Clue;
        $clue->type = 'meridian';
        $clue->value = 'pm';
        $clue->rawValue = 'afternoon';

        $this->assertEquals($clue, $method->invoke($sut, 'afternoon'));

        $clue = new Clue;
        $clue->type = 'MM';
        $clue->before = true;
        $clue->value = 4;
        $clue->rawValue = '<April';

        $this->assertEquals($clue, $method->invoke($sut, '<April'));

        $clue = new Clue;
        $clue->type = 'MM-DD';
        $clue->after = true;
        $clue->value = '???-02-04';
        $clue->rawValue = '>February-04';

        $this->assertEquals($clue, $method->invoke($sut, '>February-04'));
    }

    public function testAddClue()
    {
        $sut = new ClueParser();
        $this->assertEquals($sut, $sut->addClue('1985'));
        $this->assertEquals(['1985'], $sut->getClues());
    }

    public function allClueTypes()
    {
        $this->markTestIncomplete();

        $sut = new ClueParser();

        // clear cut values
        $sut->addClue('2016');
        // '????-06-??T??-??-??'
        $sut->addClue('June');
        // '????-05-17T??-??-??'
        $sut->addClue('May-17');


        // negation possible
        $sut->addClue('!2008-03-14');




        // before/after indication
        // expressable by range or point in time and comparison operator
        // '????-(01,02,03)-??T??-??-??'
        // '<????-04-??T??-??-??'
        $sut->addClue('<April');
        // expressable only by point in time and comparison operator
        // '>????-02-04T??-??-??'
        $sut->addClue('>February-04');




        // named clues (needs to be implemented)

        // mapping possible: keyword -> valid ranges
        // '????-??-??T(12,13,14,15,16,17,18)-??-??'
        $sut->addClue('afternoon');

        // mapping impossible -> needs checking for every value (weekday)
        // '????-??-??T??-??-??'
        $sut->addClue('Weekend');
        $sut->addClue('Thursday');
    }

    public function simpleProgramming()
    {
        $this->markTestIncomplete();

        $sut->addClue('2016');
        $sut->addClue('June');
        $sut->addClue('May-17');
        $sut->addClue('!2008-03-14');
    }
}
