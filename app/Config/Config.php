<?php
namespace App\Config;

class Config
{
    private static $config = [];

    public static function get($key)
    {
        return self::$config[$key] ?? $_ENV[$key] ?? null;
    }

    public static function set($key, $value)
    {
        self::$config[$key] = $value;
    }
} 