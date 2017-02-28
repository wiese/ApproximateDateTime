<?php

namespace wiese\ApproximateDateTime;

use UnexpectedValueException;

class ClueValidator
{
    /**
     * @throws UnexpectedValueException
     * @param Clue $clue
     */
    public function validate(Clue $clue) : void
    {
        $setUnits = $clue->getSetUnits();
        $nSetUnits = count($setUnits);

        if ($nSetUnits === 0) {
            throw new UnexpectedValueException(
                'Clues must each have exactly one piece of information set. None given.'
            );
        } elseif ($nSetUnits > 1) {
            throw new UnexpectedValueException(
                'Clues can only carry one piece of information. Given: ' . implode(', ', $setUnits)
            );
        }
    }
}
