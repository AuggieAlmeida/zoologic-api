<?php
namespace App\Config;

class Config
{
    private static $config = [];

    /**
     * Resolve a configuration value.
     *
     * Values set at runtime win. Otherwise the value is read from the
     * environment: phpdotenv populates $_ENV when a .env file exists, while
     * managed platforms (Northflank, Render, Vercel) inject real environment
     * variables, which may only be visible through $_SERVER or getenv()
     * depending on the variables_order ini setting.
     *
     * An empty string is a legitimate value (an empty database password, for
     * example), so only a missing key falls back to $default.
     */
    public static function get($key, $default = null)
    {
        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return ($value === false || $value === null) ? $default : $value;
    }

    public static function set($key, $value)
    {
        self::$config[$key] = $value;
    }

    /**
     * Resolve a configuration value as a boolean.
     *
     * Accepts the usual textual forms ("true", "1", "yes", "on") because
     * environment variables are always strings.
     */
    public static function bool($key, $default = false)
    {
        $value = self::get($key);

        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
