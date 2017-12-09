<?php
/* default configuration */
$config = [
    /* application detail */
    'app_name'  => 'Functional Framework',
    'app_url'   => 'http://localhost',
    'app_key'   => 'fnfw',
    'app_lang'  => 'en',
    'app_debug' => false,

    /* app config */
    'app_views'  => 'views/',
    'app_controllers'  => 'controllers/',
    'default_controller' => 'welcome',

    // database setting
    'database'  => [
        'host'      => 'localhost',
        'username'  => 'root',
        'password'  => '',
        'dbname'    => '',
        'table_prefix' => ''
    ],

    // email setting
    'email'    => [
        'host'      => 'mail.domain.com',
        'port'      => 465,
        'username'  => 'name@mail.com',
        'password'  => 'password',
        'is_smtp'   => true,
        'smtp'      => [
            'auth'      => true,
            'secure'    => 'ssl',
            'debug'     => 0
        ]
    ],

    // cache setting
    'cache'     => [
        'active'    => true,
        'driver'    => 'file'
    ],
    'memcached' => [
        'host'      => '127.0.0.1',
        'port'      => 11211
    ],
    'minify'    => false
];

// include app config
if (file_exists(ROOT_PATH.'configuration.php')) {
    include ROOT_PATH.'configuration.php';
}

return $config;