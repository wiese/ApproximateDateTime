<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\DateTimeFormat;
use PHPUnit\Framework\TestCase;

class DateTimeFormatTest extends TestCase
{
    public function testConstants() : void
    {
        $this->assertEquals('Y', DateTimeFormat::YEAR);
        $this->assertEquals('n', DateTimeFormat::MONTH);
        $this->assertEquals('j', DateTimeFormat::DAY);
        $this->assertEquals('G', DateTimeFormat::HOUR);
        $this->assertEquals('i', DateTimeFormat::MINUTE);
        $this->assertEquals('s', DateTimeFormat::SECOND);
        $this->assertEquals('N', DateTimeFormat::WEEKDAY);
    }
}
