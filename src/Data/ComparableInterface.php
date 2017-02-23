<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Data;

/**
 * Interface Comparable
 *
 * @see https://wiki.php.net/rfc/comparable
 *
 * @package wiese\ApproximateDateTime\Data
 */
interface ComparableInterface
{
    /**
     * Check how another instance compares to this one
     *
     * @tutorial Returns -1 if $this < $other, +1 if $this > $other, 0 otherwise (if objects are considered equal)
     *
     * @param self $other
     * @return int -1|0|1
     */
    public function compareTo(self $other) : int;

    /**
     * Check if another instance is considered bigger
     *
     * @param self $other
     * @return bool
     */
    public function isBigger(self $other) : bool;

    /**
     * Check if another instance is considered smaller
     *
     * @param self $other
     * @return bool
     */
    public function isSmaller(self $other) : bool;

    /**
     * Check if another instance is considered equal
     *
     * @param self $other
     * @return bool
     */
    public function equals(self $other) : bool;
}
