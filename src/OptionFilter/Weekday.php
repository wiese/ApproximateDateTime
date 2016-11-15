<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

use DateTime;
use DateInterval;
use DatePeriod;

class Weekday extends Base
{
    public function apply(array & $starts, array & $ends) : void
    {
        if (count($this->whitelist) === 7 && count($this->blacklist) === 0) { // all days allowed
            return;
        }

        $options = $this->getAllowableOptions();

        switch (count($options)) {
            case 7: // all days allowed
                return;
            case 0: // no days allowed
                $starts = $ends = [];
                return;
        }

        $newStarts = $newEnds = [];
        $oneDayInterval = new DateInterval('P1D');
        for ($i = 0; $i < count($starts); $i++) {
            $start = new DateTime(implode('-', $starts[$i]), $this->timezone);
            $end = new DateTime(implode('-', $ends[$i]), $this->timezone);
            $end->add($oneDayInterval); // work with end day, too
            $period = new DatePeriod($start, $oneDayInterval, $end);
            $gap = true;
            $previous = null;
            foreach ($period as $moment) {
                if (in_array($moment->format('N'), $options)) {
                    if ($gap) {
                        $newStarts[] = $this->dateTimeToMoment($moment);
                    }
                    $gap = false;
                    $previous = $moment;
                } else {
                    $gap = true;
                    if ($previous) {
                        $newEnds[] = $this->dateTimeToMoment($previous);
                        $previous = null;
                    }
                }
            }
            if (count($newStarts) !== count($newEnds)) {
                $newEnds[] = $this->dateTimeToMoment($previous);
            }
        }

        $starts = $newStarts;
        $ends = $newEnds;
    }

    /**
     * @todo static member method of our data object
     *
     * @param DateTime $dateTime
     * @return array
     */
    protected function dateTimeToMoment(DateTime $dateTime) : array
    {
        return [
            'y' => (int) $dateTime->format('Y'),
            'm' => (int) $dateTime->format('m'),
            'd' => (int) $dateTime->format('d')
        ];
    }
}
