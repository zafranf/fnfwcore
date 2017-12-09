<?php
/* default configuration */
$config = [
    /* app config */
    'app' => [
        'name' => 'Functional Framework',
        'url' => 'http://localhost',
        'key' => 'fnfw',
        'lang' => 'en',
        'debug' => false,
        'view_folder' => 'views/',
        'controller_folder' => 'controllers/',
    ],

    'default_controller' => 'welcome',

    /* database setting */
    'database' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'dbname' => '',
        'table_prefix' => '',
    ],

    /* email setting */
    'email' => [
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
        'active' => true,
        'driver' => 'file',
    ],
    'memcached' => [
        'host' => '127.0.0.1',
        'port' => 11211,
    ],

    /* minify output */
    'minify' => false,
];

// include app config
if (file_exists(ROOT_PATH . 'configuration.php')) {
    include ROOT_PATH . 'configuration.php';
}

return $config;
