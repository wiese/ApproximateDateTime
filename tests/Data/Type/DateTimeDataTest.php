<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests\Data\Type;

use wiese\ApproximateDateTime\Data\Type\DateTimeData;
use PHPUnit\Framework\TestCase;

class DateTimeDataTest extends TestCase
{
    public function testProperties() : void
    {
        $sut = new DateTimeData;
        $this->assertObjectHasAttribute('y', $sut);
        $this->assertObjectHasAttribute('m', $sut);
        $this->assertObjectHasAttribute('d', $sut);
        $this->assertObjectHasAttribute('h', $sut);
        $this->assertObjectHasAttribute('i', $sut);
        $this->assertObjectHasAttribute('s', $sut);
    }
}
