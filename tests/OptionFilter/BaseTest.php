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

    public function testForDays() : void
    {
        $this->sut->setUnit('d');

        $this->mockClues(null, null, [], []);

        $this->assertEquals(range(1, 30), $this->getAllowableOptions(30));
    }

    public function testForDaysWithBefore() : void
    {
        $this->sut->setUnit('d');

        $this->mockClues(null, 3, [], []);

        $this->assertEquals(range(1, 3), $this->getAllowableOptions(31));
    }

    public function testForDaysWithBeforeAndAfter() : void
    {
        $this->sut->setUnit('d');

        $this->mockClues(27, 3, [], []);

        $this->assertEquals([1, 2, 3, 27, 28, 29, 30], $this->getAllowableOptions(30));
    }

    public function testForDaysWithBeforeAfterAndWhitelist() : void
    {
        $this->sut->setUnit('d');

        $this->mockClues(27, 3, [2, 3, 4, 5, 9, 31], []);

        $this->assertEquals([2, 3, 31], $this->getAllowableOptions(31));
    }

    public function testForDaysWithAllFilters() : void
    {
        $this->sut->setUnit('d');

        $this->mockClues(27, 3, [2, 3, 4, 5, 9, 31], [2]);

        $this->assertEquals([3, 31], $this->getAllowableOptions(31));
    }

    public function testGenerousWhitelist() : void
    {
        $this->sut->setUnit('d');

        $this->mockClues(null, null, range(1, 31), []);

        $this->assertEquals(range(1, 30), $this->getAllowableOptions(30));
    }

    public function testZeroValues() : void
    {
        $this->sut->setUnit('h');

        $this->mockClues(null, null, [], []);

        $this->assertEquals(range(0, 23), $this->getAllowableOptions());
    }

    public function testZeroValuesAndWhitelist() : void
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

    public function testBeforeAndAfterInversed() : void
    {
        $this->sut->setUnit('i');

        $this->mockClues(18, 8, [], []);

        $this->assertEquals(array_merge(range(0, 8), range(18, 59)), $this->getAllowableOptions());
    }

    protected function getAllowableOptions(int $overrideMax = null) : array
    {
        $method = new ReflectionMethod($this->sut, 'getAllowableOptions');
        $method->setAccessible(true);
        return $method->invoke($this->sut, $overrideMax);
    }
}
