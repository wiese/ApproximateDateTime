<?php

namespace wiese\ApproximateDateTime\Tests;

use PHPUnit_Framework_TestCase;
use wiese\ApproximateDateTime\DateTimeFormats;

class DateTimeFormatsTest extends PHPUnit_Framework_TestCase
{
    public function testConstants() : void
    {
        $this->assertEquals('Y', DateTimeFormats::YEAR);
        $this->assertEquals('n', DateTimeFormats::MONTH);
        $this->assertEquals('j', DateTimeFormats::DAY);
        $this->assertEquals('H', DateTimeFormats::HOUR);
        $this->assertEquals('i', DateTimeFormats::MINUTE);
        $this->assertEquals('s', DateTimeFormats::SECOND);
        $this->assertEquals('N', DateTimeFormats::WEEKDAY);
    }
}
