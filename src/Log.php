<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use Psr\Log\LoggerInterface;
use Monolog\Logger;

class Log
{

    public static function get() : LoggerInterface
    {
        $logger = new Logger('ApproximateDateTime');

        $handler = Config::$logHandler;
        if (is_string($handler)) {
            $logger->pushHandler(new $handler);
        }

        return $logger;
    }
}
