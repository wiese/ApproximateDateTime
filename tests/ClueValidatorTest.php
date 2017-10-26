<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use UnexpectedValueException;
use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\ClueValidator;
use PHPUnit\Framework\TestCase;

class ClueValidatorTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testValidateSuccess() : void
    {
        $sut = new ClueValidator();

        $clue = new Clue();
        $clue->setY(2000);
        $sut->validate($clue);

        $clue = new Clue();
        $clue->setM(1);
        $sut->validate($clue);

        $clue = new Clue();
        $clue->setM(13);
        $sut->validate($clue); // no validation of overflow
        $clue->setM(-1);
        $sut->validate($clue); // no validation of plausibility
    }

    public function testValidateFail0() : void
    {
        $sut = new ClueValidator();
        $clue = new Clue();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Clues must each have exactly one piece of information set. None given.');

        $sut->validate($clue);
    }

    public function testValidateFail2() : void
    {
        $sut = new ClueValidator();
        $clue = new Clue();
        $clue->setY(333);
        $clue->setM(10);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Clues can only carry one piece of information. Given: y, m');

        $sut->validate($clue);
    }

    public function testValidateFail6() : void
    {
        $sut = new ClueValidator();
        $clue = new Clue();
        $clue->setY(1997);
        $clue->setM(3);
        $clue->setD(14);
        $clue->setH(15);
        $clue->setI(30);
        $clue->setS(0);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Clues can only carry one piece of information. Given: y, m, d, h, i, s');

        $sut->validate($clue);
    }
}
