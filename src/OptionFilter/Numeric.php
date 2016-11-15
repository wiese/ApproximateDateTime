<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

class Numeric extends Base
{
    public function apply(array & $starts, array & $ends) : void
    {
        $options = $this->getAllowableOptions();

        $newStarts = $newEnds = [];
        foreach ($options as $key => $value) {
            if (!isset($options[$key - 1]) // first overall
                || $options[$key - 1] != $value - 1 // first of a block
            ) {
                $newStarts[] = [$this->unit => $value];
            }
            if (!isset($options[$key + 1]) // last
                || $options[$key + 1] != $value + 1 // last of a block
            ) {
                $newEnds[] = [$this->unit => $value];
            }
        }

        $starts = $this->enrichMomentInformation($starts, $newStarts);
        $ends = $this->enrichMomentInformation($ends, $newEnds);
    }

    /**
     * Add precision and diversity to moment information
     *
     * @tutorial $lowerLevelInfo is added to every piece of $higherLevelInfo;
     * amount of combinations increasing to $lowerLevelInfo * $higherLevelInfo
     *
     * @example [m => 5] & [d => [17, 19]] -> [[m => 5, d => 17], [m => 5, d => 19]]
     *
     * @param array $higherLevelInfo
     * @param array $lowerLevelInfo
     * @return array
     */
    protected function enrichMomentInformation(array $higherLevelInfo, array $lowerLevelInfo) : array
    {
        $combined = [];
        foreach ($higherLevelInfo as $value1) {
            foreach ($lowerLevelInfo as $value2) {
                $combined[] = $value1 + $value2;
            }
        }

        if (empty($higherLevelInfo)) { // on "highest level"/first run
            $combined = $lowerLevelInfo;
        }

        return $combined;
    }
}
