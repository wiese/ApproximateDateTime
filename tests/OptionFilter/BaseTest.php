<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\OptionFilter;

use ReflectionMethod;

class BaseTest extends ParentTest
{

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

        $this->mockClues(null, null, [], []);

        $this->assertEquals(range(1, 30), $this->getAllowableOptions(30));
    }

    public function testGetAllowableOptionsForDaysWithBefore() : void
    {
        $this->sut->setUnit('d');

        $this->mockClues(null, 3, [], []);

        $this->assertEquals(range(1, 3), $this->getAllowableOptions(31));
    }

    public function testGetAllowableOptionsForDaysWithBeforeAndAfter() : void
    {
        $this->sut->setUnit('d');

        $this->mockClues(27, 3, [], []);

        $this->assertEquals([1, 2, 3, 27, 28, 29, 30], $this->getAllowableOptions(30));
    }

    public function testGetAllowableOptionsForDaysWithBeforeAfterAndWhitelist() : void
    {
        $this->sut->setUnit('d');

        $this->mockClues(27, 3, [2, 3, 4, 5, 9, 31], []);

        $this->assertEquals([2, 3, 31], $this->getAllowableOptions(31));
    }

    public function testGetAllowableOptionsForDaysWithAllFilters() : void
    {
        $this->sut->setUnit('d');

        $this->mockClues(27, 3, [2, 3, 4, 5, 9, 31], [2]);

        $this->assertEquals([3, 31], $this->getAllowableOptions(31));
    }

    public function testGetAllowableOptionsGenerousWhitelist() : void
    {
        $this->sut->setUnit('d');

        $this->mockClues(null, null, range(1, 31), []);

        $this->assertEquals(range(1, 30), $this->getAllowableOptions(30));
    }

    public function testGetAllowableOptionsZeroValues() : void
    {
        $this->sut->setUnit('h');

        $this->mockClues(null, null, [], []);

        $this->assertEquals(range(0, 23), $this->getAllowableOptions());
    }

    public function testGetAllowableOptionsZeroValuesAndWhitelist() : void
    {
        $this->sut->setUnit('h');

        $this->mockClues(null, null, range(0, 3), []);

        $this->assertEquals(range(0, 3), $this->getAllowableOptions());
    }

    public function testBeforeAndAfter() : void
    {
        $this->sut->setUnit('h');

        $this->mockClues(8, 18, [], []);

        $this->assertEquals(range(8, 18), $this->getAllowableOptions());
    }

    protected function getAllowableOptions(int $overrideMax = null) : array
    {
        $method = new ReflectionMethod($this->sut, 'getAllowableOptions');
        $method->setAccessible(true);
        return $method->invoke($this->sut, $overrideMax);
    }
}
