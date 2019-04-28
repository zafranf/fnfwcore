<?php
/* default configuration */
$config = [
    /* default controller */
    'default_controller' => 'welcome',

    /* app config */
    'app' => [
        'name' => '(fn) FW - functional Framework',
        'url' => 'http://localhost',
        'key' => 'fnfw',
        'lang' => 'en',
        'debug' => false,
        'view_folder' => APP_PATH . 'views/',
        'controller_folder' => APP_PATH . 'controllers/',
    ],

    /* database setting */
    'database' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'dbname' => '',
        'table_prefix' => '',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
    ],

    /* email setting */
    'mail' => [
        'host' => 'mail.domain.com',
        'port' => 465,
        'username' => 'name@mail.com',
        'password' => 'password',
        'is_smtp' => true,
        'smtp' => [
            'auth' => true,
            'secure' => 'ssl',
            'debug' => 0,
        ],
    ],

    /* cache setting */
    'cache' => [
        'driver' => 'file',
        'lifetime' => 10,
    ],

    /* memcached setting */
    'memcached' => [
        'host' => '127.0.0.1',
        'port' => 11211,
    ],

    /* redis setting */
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
    ],

    /* minify output */
    'minify' => false,
];

// include app config
if (file_exists(ROOT_PATH . 'configuration.php')) {
    include ROOT_PATH . 'configuration.php';
}

return $config;
