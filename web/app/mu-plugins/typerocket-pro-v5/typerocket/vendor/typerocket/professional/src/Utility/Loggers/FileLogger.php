<?php
namespace TypeRocketPro\Utility\Loggers;

use TypeRocket\Core\Config;

class FileLogger extends Logger
{
    /**
     * @param string $type
     * @param string $message
     */
    protected function log($type, $message) : bool
    {
        $time = time();
        $file = Config::get('paths.storage') . '/logs/typerocket-' . date('Y-m-d', $time ). '.log';
        $message = $this->message($type, $message, $time);

        return (bool) file_put_contents($file, $message . PHP_EOL, FILE_APPEND);
    }
}