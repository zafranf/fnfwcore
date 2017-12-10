<?php
/**
 * db function
 *
 * @param array $config
 * @return void
 */
function db($config = [])
{
    if (empty($config)) {
        $config = config('database');
    }

    $connection = new \Pixie\Connection($config['driver'], [
        'host' => $config['host'],
        'database' => $config['dbname'],
        'username' => $config['username'],
        'password' => $config['password'],
        'charset' => $config['charset'],
        'collation' => $config['collation'],
        'prefix' => $config['table_prefix'],
    ]);

    return new \Pixie\QueryBuilder\QueryBuilderHandler($connection);
}
