<?php
namespace TypeRocketPro\Utility;

use TypeRocket\Core\Config;
use TypeRocketPro\Http\Download;
use TypeRocketPro\Utility\Drives\Drive;

/**
 * Class Log
 *
 * @method static bool create(string $file, string $content)
 * @method static bool append(string $file, string $content)
 * @method static bool replace(string $file, string $content)
 * @method static bool get(string $file)
 * @method static bool delete(string $file)
 * @method static bool exists(string $file)
 * @method static bool path(string $file)
 * @method static int|false size(string $file)
 * @method static int|false lastModified(string $file)
 * @method static Download download(string $file, string $name = null, array $headers = null, string $type = null)
 *
 * @package TypeRocket\Utility
 */
class Storage
{
    /**
     * @param array $stack
     * @param string $action
     * @param mixed ...$arguments
     *
     * @return array
     */
    public static function stack(array $stack, $action, ...$arguments) : array
    {
        $response = [];

        foreach ($stack as $location) {
            $response[$location] = static::driver($location)->{$action}(...$arguments);
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
        $channel = Config::get('storage.default');

        if($channel === 'stack') {
            $stack = Config::get("storage.drivers.{$channel}");
            return static::stack($stack, $name, ...$arguments);
        } else {
            $logger = static::driver($channel);
        }

        return $logger->{$name}(...$arguments);
    }

    /**
     * @param string $location
     *
     * @return Drive
     */
    public static function driver($location) : Drive
    {
        $drive = Config::get("storage.drivers.{$location}");
        return new $drive['driver'];
    }
}