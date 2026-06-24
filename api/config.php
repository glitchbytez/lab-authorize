<?php
/**
 * Reads a value from .env (project root) with a fallback to system environment variables.
 * Call env() anywhere after this file is loaded — no global state needed.
 */
function env(string $key, string $default = ''): string
{
    static $config = null;

    if ($config === null) {
        $envFile = dirname(__DIR__) . '/.env';
        $config  = file_exists($envFile) ? (parse_ini_file($envFile) ?: []) : [];
    }

    if (isset($config[$key])) {
        return $config[$key];
    }

    $sys = getenv($key);
    return $sys !== false ? $sys : $default;
}
