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
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'auth'          => AuthFilter::class,
        'api_auth'      => AuthFilter::class,
    ];

    public array $required = [
        'before' => [],
        'after'  => ['toolbar'],
    ];

    public array $globals = [
        'before' => [
            'csrf' => ['except' => ['login','forgot-password','reset-password/*','api/*','menu/table*','menu/table/*','menu/table/*/order','menu/table/*/poll/*']],
        ],
        'after' => [],
    ];

    public array $methods  = [];
    public array $filters  = [];
}
