<?php
namespace TypeRocketPro\Utility\Loggers;

class NullLogger extends Logger
{
    protected function log($type, $message): bool
    {
        return true;
    }
}