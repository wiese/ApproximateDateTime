<?php

namespace wiese\ApproximateDateTime\OptionFilter;

use wiese\ApproximateDateTime\OptionFilter\OptionFilterInterface;
use UnexpectedValueException;

class Factory
{

    /**
     * Create an instance of OptionFilter by name
     *
     * @param string $name
     * @return OptionFilterInterface
     */
    public function produce(string $name) : OptionFilterInterface
    {
        $className = __NAMESPACE__ . '\\Incarnation\\' . $name;

        if (!class_exists($className)) {
            throw new UnexpectedValueException("Filter type $name not implemented.");
        }

        return new $className();
    }
}
