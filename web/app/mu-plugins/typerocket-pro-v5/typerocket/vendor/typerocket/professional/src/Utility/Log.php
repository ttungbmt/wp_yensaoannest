<?php
namespace TypeRocketPro\Utility;

use TypeRocket\Core\Config;
use TypeRocketPro\Utility\Loggers\Logger;

/**
 * Class Log
 *
 * @method static bool emergency(string $message)
 * @method static bool alert(string $message)
 * @method static bool critical(string $message)
 * @method static bool error(string $message)
 * @method static bool warning(string $message)
 * @method static bool notice(string $message)
 * @method static bool info(string $message)
 * @method static bool debug(string $message)
 *
 * @package TypeRocket\Utility
 */
class Log
{
    /**
     * @param array $stack
     * @param string $type
     * @param string $message
     *
     * @return array
     */
    public static function stack(array $stack, $type, $message) : array
    {
        $response = [];

        foreach ($stack as $channel) {
            $response[$channel] = static::driver($channel)->{$type}($message);
        }

        return $response;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return bool|array
     */
    public static function __callStatic($name, $arguments)
    {
        $channel = Config::get('logging.default');

        if($channel === 'stack') {
            $stack = Config::get("logging.drivers.{$channel}");
            return static::stack($stack, $name, ...$arguments);
        } else {
            $logger = static::driver($channel);
        }

        return $logger->{$name}(...$arguments);
    }

    /**
     * @param string $channel
     *
     * @return Logger
     */
    public static function driver($channel) : Logger
    {
        $logger = Config::get("logging.channels.{$channel}");
        return new $logger['driver'];
    }
}