<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Data;

abstract class Vehicle
{

    /**
     * @var int|null
     */
    protected $y;

    /**
     * @var int|null
     */
    protected $m;

    /**
     * @var int|null
     */
    protected $d;

    /**
     * @var int|null
     */
    protected $n;

    /**
     * @var int|null
     */
    protected $h;

    /**
     * @var int|null
     */
    protected $i;

    /**
     * @var int|null
     */
    protected $s;

    protected static $options = ['y', 'm', 'd', 'n', 'h', 'i' , 's'];

    /**
     * @return int|null
     */
    public function getY() : ? int
    {
        return $this->y;
    }

    /**
     * @param int|null $y
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
     * @param int|null $m
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
     * @param int|null $d
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
    public function getN() : ? int
    {
        return $this->n;
    }

    /**
     * @param int|null $d
     * @return self
     */
    public function setN(int $n = null) : self
    {
        $this->n = $n;
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
     * @param int|null $h
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
     * @param int|null $i
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
     * @param int|null $s
     * @return self
     */
    public function setS(int $s = null) : self
    {
        $this->s = $s;
        return $this;
    }

    public function set(string $unit, int $value = null) : self
    {
        $this->assertUnit($unit);

        $this->{$unit} = $value;
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

    public function fromArray(array $array) : self
    {
        $this->resetValues();

        foreach ($array AS $unit => $value) {
            $this->set($unit, $value);
        }

        return $this;
    }

    public function resetValues() : self
    {
        foreach (self::$options as $unit) {
            $this->set($unit, null);
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
        foreach (self::$options as $unit) {
            $here = $this->get($unit);
            $there = $other->get($unit);

            if (is_int($here) && is_null($there) || is_int($there) && is_null($here)) {
                throw new \Exception('Can not compare objects with different units set.');
            }
            elseif (is_int($here) && is_int($there) && $here !== $there) {  // equality leads to continued check
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

    public function getSetUnits() : array
    {
        $set = [];
        foreach (self::$options AS $unit) {
            if (!is_null($this->get($unit))) {
                $set[] = $unit;
            }
        }

        return $set;
    }

    /**
     * @param string $unit
     * @throws \InvalidArgumentException
     */
    protected function assertUnit(string $unit) : void
    {
        if (!in_array($unit, self::$options)) {
            throw new \InvalidArgumentException('Unknow date unit ' . $unit);
        }
    }
}
