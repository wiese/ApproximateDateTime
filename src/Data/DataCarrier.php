<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Data;

use InvalidArgumentException;
use LogicException;

trait DataCarrier
{

    protected $data;

    /**
     * Make sure children are not manipulated via references in copies. ImmutableDateTimeData containers as alternative?
     */
    public function __clone()
    {
        $this->data = clone $this->data;
    }

    /**
     * Set value for a dynamic unit. Delegates to the native setter
     *
     * @param string $unit
     * @param int|null $value
     */
    public function set(string $unit, int $value = null) : void
    {
        $this->assertUnit($unit);

        $this->data->{$unit} = $value;
    }

    /**
     * @param string
     * @return int|null
     */
    public function get($unit) : ? int
    {
        $this->assertUnit($unit);

        return $this->data->{$unit};
    }

    /**
     * {@inheritDoc}
     * @see ComparableInterface::compareTo()
     *
     * @throws LogicException
     */
    public function compareTo(self $other) : int
    {
        foreach ($this->getUnits() as $unit) {
            $here = $this->data->{$unit};
            $there = $other->data->{$unit};

            if (is_int($here) && is_null($there) || is_int($there) && is_null($here)) {
                throw new LogicException('Can not compare objects with different units set.');
            } elseif (is_int($here) && is_int($there) && $here !== $there) {  // equality leads to continued check
                return ($here < $there) ? -1 : 1;
            }
        }

        return 0;
    }

    /**
     * {@inheritDoc}
     * @see ComparableInterface::isBigger()
     */
    public function isBigger(self $other) : bool
    {
        return $this->compareTo($other) === 1;
    }

    /**
     * {@inheritDoc}
     * @see ComparableInterface::isSmaller()
     */
    public function isSmaller(self $other) : bool
    {
        return $this->compareTo($other) === -1;
    }

    /**
     * {@inheritDoc}
     * @see ComparableInterface::equals()
     */
    public function equals(self $other) : bool
    {
        return $this->compareTo($other) === 0;
    }

    /**
     * @param string $unit
     * @throws InvalidArgumentException
     */
    protected function assertUnit(string $unit) : void
    {
        if (!in_array($unit, $this->getUnits())) {
            throw new InvalidArgumentException('Unknow date unit ' . $unit);
        }
    }

    /**
     * Get the units (properties) present in the data object
     *
     * @tutorial Has a smell, as order of properties in code determines functionality, e.g. sorting by values
     *
     * @return array
     */
    protected function getUnits() : array
    {
        return array_keys(get_object_vars($this->data));
    }
}
