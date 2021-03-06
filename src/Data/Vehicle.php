<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Data;

/**
 * @todo use a trait instead? inheritance between unrelated objects seems off.
 *
 * @package wiese\ApproximateDateTime\Data
 */
abstract class Vehicle
{

    /**
     * All-digit representation of a year
     *
     * @example 44 | 2016
     *
     * @var int|null
     */
    protected $y;

    /**
     * Representation of a month
     *
     * @var int|null
     */
    protected $m;

    /**
     * Representation of a day
     *
     * @example 1 | 31
     *
     * @var int|null
     */
    protected $d;

    /**
     * Representation of an hour
     *
     * @example 0 | 23
     *
     * @var int|null
     */
    protected $h;

    /**
     * Representation of a minute
     *
     * @example 0 | 59
     *
     * @var int|null
     */
    protected $i;

    /**
     * Representation of a second
     *
     * @example 0 | 59
     *
     * @var int|null
     */
    protected $s;

    protected static $options = ['y', 'm', 'd', 'h', 'i', 's'];

    /**
     * @return int|null
     */
    public function getY() : ? int
    {
        return $this->y;
    }

    /**
     * Set the all-digit representation of a year
     *
     * @param int|null $y The year, e.g. 2016
     * @return self
     */
    public function setY(int $y = null) : self
    {
        $this->y = $y;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getM() : ? int
    {
        return $this->m;
    }

    /**
     * Set the representation of a month
     *
     * @param int|null $m The month, e.g. 47
     * @return self
     */
    public function setM(int $m = null) : self
    {
        $this->m = $m;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getD() : ? int
    {
        return $this->d;
    }

    /**
     * Set the representation of a day
     *
     * @param int|null $d The day, e.g. 29
     * @return self
     */
    public function setD(int $d = null) : self
    {
        $this->d = $d;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getH() : ? int
    {
        return $this->h;
    }

    /**
     * Set the representation of an hour
     *
     * @param int|null $h The hour, e.g. 23
     * @return self
     */
    public function setH(int $h = null) : self
    {
        $this->h = $h;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getI() : ? int
    {
        return $this->i;
    }

    /**
     * Set the representation of a minute
     *
     * @param int|null $i The minute, e.g. 59
     * @return self
     */
    public function setI(int $i = null) : self
    {
        $this->i = $i;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getS() : ? int
    {
        return $this->s;
    }

    /**
     * Set the representation of a second
     *
     * @param int|null $s The second, e.g. 59
     * @return self
     */
    public function setS(int $s = null) : self
    {
        $this->s = $s;
        return $this;
    }

    /**
     * Set value for a dynamic unit. Delegates to the native setter
     *
     * @param string $unit
     * @param int|null $value
     * @return self
     */
    public function set(string $unit, int $value = null) : self
    {
        $this->assertUnit($unit);

        call_user_func([$this, 'set' . strtoupper($unit)], $value);

        return $this;
    }

    /**
     * @param string
     * @return int|null
     */
    public function get($unit) : ? int
    {
        $this->assertUnit($unit);

        return $this->{$unit};
    }

    /**
     * Populate the member values from an array
     *
     * @param array $array
     * @return self
     */
    public function fromArray(array $array) : self
    {
        $this->resetValues();

        foreach ($array as $unit => $value) {
            $this->set($unit, $value);
        }

        return $this;
    }

    /**
     * Check how another instance compares to this one
     *
     * @tutorial Implements Comparable interface
     *
     * @param self $other
     * @return int -1|0|1
     * @throws \Exception
     */
    public function compareTo(self $other) : int
    {
        foreach (static::$options as $unit) {
            $here = $this->get($unit);
            $there = $other->get($unit);

            if (is_int($here) && is_null($there) || is_int($there) && is_null($here)) {
                throw new \Exception('Can not compare objects with different units set.');
            } elseif (is_int($here) && is_int($there) && $here !== $there) {  // equality leads to continued check
                return ($here < $there) ? -1 : 1;
            }
        }

        return 0;
    }

    /**
     * Check if another instance is considered bigger
     *
     * @param self $other
     * @return bool
     */
    public function isBigger(self $other) : bool
    {
        return $this->compareTo($other) === 1;
    }

    /**
     * Check if another instance is considered smaller
     *
     * @param self $other
     * @return bool
     */
    public function isSmaller(self $other) : bool
    {
        return $this->compareTo($other) === -1;
    }

    /**
     * Check if another instance is considered equal
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other) : bool
    {
        return $this->compareTo($other) === 0;
    }

    /**
     * Get the units with non-null values
     *
     * @return array
     */
    public function getSetUnits() : array
    {
        $set = [];
        foreach (static::$options as $unit) {
            if (!is_null($this->get($unit))) {
                $set[] = $unit;
            }
        }

        return $set;
    }

    /**
     * Set all member values to null
     *
     * @return self
     */
    protected function resetValues() : self
    {
        foreach (static::$options as $unit) {
            $this->set($unit, null);
        }

        return $this;
    }

    /**
     * @param string $unit
     * @throws \InvalidArgumentException
     */
    protected function assertUnit(string $unit) : void
    {
        if (!in_array($unit, static::$options)) {
            throw new \InvalidArgumentException('Unknow date unit ' . $unit);
        }
    }
}
