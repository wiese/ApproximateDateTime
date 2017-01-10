<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use PHPUnit_Framework_TestCase;
use wiese\ApproximateDateTime\DateTimeFormat;

class DateTimeFormatTest extends PHPUnit_Framework_TestCase
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
