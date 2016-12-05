<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\OptionFilter;

use PHPUnit_Framework_TestCase;

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

    protected function mockClues($after, $before, $whitelist, $blacklist): void
    {
        $this->clues->method('getAfter')->willReturn($after);
        $this->clues->method('getBefore')->willReturn($before);
        $this->clues->method('getWhitelist')->willReturn($whitelist);
        $this->clues->method('getBlacklist')->willReturn($blacklist);
    }
}
