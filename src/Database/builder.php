<?php
/**
 * db function
 *
 * @param array $config
 * @return void
 */
function db($config = [])
{
    /* merge config */
    $config = array_merge(config('database'), $config);

    /* connect */
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
