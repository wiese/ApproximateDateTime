<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\OptionFilter;

use PHPUnit_Framework_TestCase;
use wiese\ApproximateDateTime\Clue;

abstract class ParentTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PHPUnit_Framework_MockObject_MockObject Of the OptionFilter instance
     */
    protected $sut;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject Of Clues
     */
    protected $clues;

    /**
     * Set clue hints to be returned by mock methods on $this->clues during processing
     *
     * @param int|null $after
     * @param int|null $before
     * @param array $whitelist
     * @param array $blacklist
     */
    protected function mockClues(int $after = null, int $before = null, array $whitelist, array $blacklist) : void
    {
        $property = new \ReflectionProperty($this->sut, 'unit');
        $property->setAccessible(true);
        $unit = $property->getValue($this->sut);

        if ($after) {
            $clue = new Clue();
            $clue->set($unit, $after);
        } else {
            $clue = null;
        }
        $this->clues->method('getAfter')->willReturn($clue);

        if ($before) {
            $clue = new Clue();
            $clue->set($unit, $before);
        } else {
            $clue = null;
        }
        $this->clues->method('getBefore')->willReturn($clue);

        $clues = [];
        foreach ($whitelist as $value) {
            $clue = new Clue();
            $clue->set($unit, $value);
            $clues[] = $clue;
        }
        $this->clues->method('getWhitelist')->willReturn($clues);

        $clues = [];
        foreach ($blacklist as $value) {
            $clue = new Clue();
            $clue->set($unit, $value);
            $clues[] = $clue;
        }
        $this->clues->method('getBlacklist')->willReturn($clues);
    }
}
