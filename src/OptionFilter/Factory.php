<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

use wiese\ApproximateDateTime\Manager;
use UnexpectedValueException;

class Factory
{

    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

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

        return new $className($this->manager->config, $this->manager->log);
    }
}
