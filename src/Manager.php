<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Manager
{

    /**
     * @var Config
     */
    public $config;

    /**
     * @var LoggerInterface
     */
    public $log;

    public function __construct()
    {
        $this->config = new Config();

        $this->log = new Logger($this->config->logChannel);
        $handler = $this->config->logHandler;
        if (is_string($handler)) {
            $this->log->pushHandler(new $handler);
        }
    }
}
