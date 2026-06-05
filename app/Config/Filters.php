<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;
use App\Filters\AuthFilter;

class Filters extends BaseConfig
{
    /**
     * Aliases for filter classes.
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'auth'          => AuthFilter::class,   // our custom auth filter
        'api_auth'      => AuthFilter::class,
    ];

    /**
     * List of always-active filters.
     */
    public array $required = [
        'before' => [
            // 'honeypot',
            'csrf',
            // 'invalidchars',
        ],
        'after' => [
            'toolbar',
            // 'honeypot',
            // 'secureheaders',
        ],
    ];

    /**
     * Filters to run on every request.
     */
    public array $globals = [
        'before' => [
            // 'csrf', // already in required
        ],
        'after' => [],
    ];

    /**
     * Filters by HTTP method.
     */
    public array $methods = [];

    /**
     * Filters for specific URI patterns.
     */
    public array $filters = [];
}
