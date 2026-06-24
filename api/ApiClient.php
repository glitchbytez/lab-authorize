<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/contracts/ApiContract.php';
require_once __DIR__ . '/drivers/DatabaseDriver.php';
require_once __DIR__ . '/drivers/ExternalDriver.php';

class ApiClient
{
    public static function make(): ApiContract
    {
        return match (env('API_DRIVER', 'database')) {
            'external' => new ExternalDriver(),
            default    => new DatabaseDriver(),
        };
    }
}
