<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    /**
     * The default database group.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public string $defaultGroup = 'default';

    /**
     * Default connection — update credentials here
     */
    public array $default = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'root',       // ← change this
        'password' => '',           // ← change this
        'database' => '',   // ← your DB name
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
        'numberNative' => false,
    ];

    /**
     * Test database (for running tests).
     */
    public array $tests = [
        'DSN'      => '',
        'hostname' => '127.0.0.1',
        'username' => 'root',
        'password' => 'Sourav@15',
        'database' => 'restocrm_test',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'pConnect' => false,
        'DBDebug'  => true,
        'charset'  => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => false,
        'failover' => [],
        'port'     => 3306,
    ];
}
