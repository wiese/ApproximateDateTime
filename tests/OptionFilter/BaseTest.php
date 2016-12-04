<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\OptionFilter;

use PHPUnit_Framework_TestCase;

class BaseTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var BaseIncarnation
     */
    protected $sut;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject Of Clues
     */
    protected $clues;

    public function setUp() : void
    {
        $this->sut = $this->getMockForAbstractClass('wiese\ApproximateDateTime\OptionFilter\Base');
        $this->clues = $this->getMockBuilder('wiese\ApproximateDateTime\Clues')
            // methods that are mocked; results can be manipulated later
            ->setMethods(['getWhitelist', 'getBlacklist', 'getBefore', 'getAfter'])
            ->getMock();
        $this->sut->setClues($this->clues);
    }

    public function testGetAllowableOptionsForDays() : void
    {
        $this->sut->setUnit('d');

        $this->assertEquals(range(1, 30), $this->getAllowableOptions(30));

        $this->clues->method('getBefore')->willReturn(3);

        $this->assertEquals(range(1, 3), $this->getAllowableOptions(31));

        $this->clues->method('getAfter')->willReturn(27);

        $this->assertEquals([1, 2, 3, 27, 28, 29, 30], $this->getAllowableOptions(30));

        $this->clues->method('getWhitelist')->willReturn([2, 3, 4, 5, 9, 31]);
        $this->assertEquals([2, 3, 31], $this->getAllowableOptions(31));

        $this->clues->method('getBlacklist')->willReturn([2]);
        $this->assertEquals([3, 31], $this->getAllowableOptions(31));
    }

    public function testGetAllowableOptionsGenerousWhitelist() : void
    {
        $this->sut->setUnit('d');
        $this->clues->method('getWhitelist')->willReturn(range(1, 31));
        $this->assertEquals(range(1, 30), $this->getAllowableOptions(30));
    }

    public function testGetAllowableOptionsZeroValues() : void
    {
        $this->sut->setUnit('h');

        $this->assertEquals(range(0, 23), $this->getAllowableOptions());

        $this->clues->method('getWhitelist')->willReturn(range(0, 3));
        $this->assertEquals(range(0, 3), $this->getAllowableOptions());
    }

    protected function getAllowableOptions(int $overrideMax = null) : array
    {
        $method = new \ReflectionMethod($this->sut, 'getAllowableOptions');
        $method->setAccessible(true);
        return $method->invoke($this->sut, $overrideMax);
    }
}
