<?php
return [
    // New config goes here
   'database'                => [
        'host'     => env('MYSQL_HOST', (env('CI', false) ? 'mariadb' : 'localhost')),
        'database' => env('MYSQL_DATABASE', 'engelsystem'),
        'username' => env('MYSQL_USER', 'root'),
        'password' => env('MYSQL_PASSWORD', ''),
    ],

    // For accessing /metrics (and /stats)
    'api_key'                 => env('API_KEY', ''),
    
    // Application name (not the event name)
    'app_name'                => env('APP_NAME', 'Engelsystem-FC'),
    
    // Set to development to enable debugging messages
    'environment'             => env('ENVIRONMENT', 'development'),
    
    'timezone'                => env('TIMEZONE', 'America/Los_Angeles'),
    
        // Available locales in /locale/
    'locales'                 => [
        'en_US.UTF-8' => 'English',
    ],
    
    // The default locale to use
    'default_locale'          => env('DEFAULT_LOCALE', 'en_US.UTF-8'),
    ];