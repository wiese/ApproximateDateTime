<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\Data\Type;

use wiese\ApproximateDateTime\Data\Type\ClueData;
use PHPUnit\Framework\TestCase;

class ClueDataTest extends TestCase
{
    public function testProperties() : void
    {
        $sut = new ClueData;
        $this->assertObjectHasAttribute('y', $sut);
        $this->assertObjectHasAttribute('m', $sut);
        $this->assertObjectHasAttribute('d', $sut);
        $this->assertObjectHasAttribute('h', $sut);
        $this->assertObjectHasAttribute('i', $sut);
        $this->assertObjectHasAttribute('s', $sut);
        $this->assertObjectHasAttribute('n', $sut);
    }
}
