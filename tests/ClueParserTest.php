<?php
declare(strict_types=1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\ApproximateDateTime;
use wiese\ApproximateDateTime\ClueParser;
use wiese\ApproximateDateTime\Clue;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use DateTime;
use DateTimeZone;

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
        $clue->first = new DateTime("2014-01-01T00:00:00+0000");
        $clue->last = new DateTime("2014-12-31T23:59:59+0000");

        $this->assertEquals($clue, $method->invoke($sut, '2014'));
    }

    public function testComplicatedClueCast()
    {
        $sut = new ClueParser();

        $class = new ReflectionClass($sut);
        $method = $class->getMethod('processClue');
        $method->setAccessible(true);

        $clue = new Clue;
        $clue->type = 'YY';
        $clue->rawValue = 2016;
        $clue->first = new DateTime("2016-01-01T00:00:00+0000");
        $clue->last = new DateTime("2016-12-31T23:59:59+0000");

        $this->assertEquals($clue, $method->invoke($sut, '2016'));

        $clue = new Clue;
        $clue->type = 'weekend';
        $clue->rawValue = 'Weekend';

        $this->assertEquals($clue, $method->invoke($sut, 'Weekend'));

        $clue = new Clue;
        $clue->type = 'meridian';
        $clue->rawValue = 'afternoon';

        $this->assertEquals($clue, $method->invoke($sut, 'afternoon'));

        $clue = new Clue;
        $clue->type = 'MM';
        $clue->before = 4;
        $clue->rawValue = '<April';

        $this->assertEquals($clue, $method->invoke($sut, '<April'));

        $clue = new Clue;
        $clue->type = 'MM';
        $clue->after = '???-02-04';
        $clue->rawValue = '>February-04';

        $this->assertEquals($clue, $method->invoke($sut, '>February-04'));
    }

    public function testAddClue()
    {
        $sut = new ClueParser();
        $this->assertEquals($sut, $sut->addClue('1985'));
        $this->assertEquals(['1985'], $sut->getClues());
    }
}
