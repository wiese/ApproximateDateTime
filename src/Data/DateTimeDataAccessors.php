<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Data;

trait DateTimeDataAccessors
{

    protected $data;

    /**
     * @return int|null
     */
    public function getY() : ? int
    {
        return $this->data->y;
    }

    /**
     * Set the all-digit representation of a year
     *
     * @param int|null $y The year, e.g. 2016
     */
    public function setY(int $y = null) : void
    {
        $this->data->y = $y;
    }

    /**
     * @return int|null
     */
    public function getM() : ? int
    {
        return $this->data->m;
    }

    /**
     * Set the representation of a month
     *
     * @param int|null $m The month, e.g. 4
     */
    public function setM(int $m = null) : void
    {
        $this->data->m = $m;
    }

    /**
     * @return int|null
     */
    public function getD() : ? int
    {
        return $this->data->d;
    }

    /**
     * Set the representation of a day
     *
     * @param int|null $d The day, e.g. 29
     */
    public function setD(int $d = null) : void
    {
        $this->data->d = $d;
    }

    /**
     * @return int|null
     */
    public function getH() : ? int
    {
        return $this->data->h;
    }

    /**
     * Set the representation of an hour
     *
     * @param int|null $h The hour, e.g. 23
     */
    public function setH(int $h = null) : void
    {
        $this->data->h = $h;
    }

    /**
     * @return int|null
     */
    public function getI() : ? int
    {
        return $this->data->i;
    }

    /**
     * Set the representation of a minute
     *
     * @param int|null $i The minute, e.g. 59
     */
    public function setI(int $i = null) : void
    {
        $this->data->i = $i;
    }

    /**
     * @return int|null
     */
    public function getS() : ? int
    {
        return $this->data->s;
    }

    /**
     * Set the representation of a second
     *
     * @param int|null $s The second, e.g. 59
     */
    public function setS(int $s = null) : void
    {
        $this->data->s = $s;
    }
}
