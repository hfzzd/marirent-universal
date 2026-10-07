<?php

use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;

return [

    'default' => env('LOG_CHANNEL', 'stack'),

    'deprecations' => [
        'driver' => 'null',
    ],

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        /*
        |--------------------------------------------------------------------------
        | Sentry Channel
        |--------------------------------------------------------------------------
        |
        | This channel will send log records to Sentry based on the DSN set in
        | your environment variables.
        |
        */
        'sentry' => [
            'driver' => 'monolog',
            'handler' => Sentry\SentryLaravel\SentryHandler::class,
            'handler_with' => [
                'dsn' => env('SENTRY_LARAVEL_DSN'),
                'level' => Level::Warning,
            ],
            'formatter' => env('LOG_SENTRY_FORMATTER')
                ? new LineFormatter(env('LOG_SENTRY_FORMATTER'))
                : null,
            'formatter_with' => env('LOG_SENTRY_FORMATTER_WITH', []),
            'level' => env('APP_ENV') === 'production' ? 'warning' : 'debug',
        ],

    ],

];