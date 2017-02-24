<?php

namespace wiese\ApproximateDateTime;

use UnexpectedValueException;

class ClueValidator
{
    public function validate(Clue $clue) : void
    {
        $setUnits = $clue->getSetUnits();

        if (count($setUnits) > 1) {
            throw new UnexpectedValueException(
                'Clues can only carry on piece of information. Given: ' . implode(', ', $setUnits)
            );
        }
    }
}
