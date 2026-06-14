<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath   = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public string $defaultGroup = 'default';

    public array $default = [
        'DSN'      => 'mysql:host=localhost;dbname=restoCRM;charset=utf8mb4',
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',          // ← set your MySQL password here
        'database' => 'restoCRM',
        'DBDriver' => 'PDO',
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
}
