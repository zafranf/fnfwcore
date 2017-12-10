<?php
/**
 * [configPool description]
 * @return [type] [description]
 */
if (!function_exists('configPool')) {
    function configPool()
    {
        $driver = config('cache')['driver'];
        if ($driver == "file") {
            $driver = setupFilesystem();
        } else if ($driver == "sqlite") {
            $driver = setupSQLite();
        } else if ($driver == "apc") {
            $driver = setupAPC();
        } else if ($driver == "memcached") {
            $driver = setupMemcached();
        } else if ($driver == "redis") {
            $driver = setupRedis();
        } else {
            $driver = setupEphemeral();
        }

        return new \Stash\Pool($driver);
    }
}

/**
 * [setupFilesystem description]
 * @return [type] [description]
 */
if (!function_exists('setupFilesystem')) {
    function setupFilesystem()
    {
        $options = [
            'path' => STORAGE_PATH . 'cache/',
            'filePermissions' => 777,
            'dirPermissions' => 777,
        ];

        return new \Stash\Driver\FileSystem($options);
    }
}

/**
 * [setupSQLite description]
 * @return [type] [description]
 */
if (!function_exists('setupSQLite')) {
    function setupSQLite()
    {
        $options = [
            'extension' => 'pdo',
            'path' => STORAGE_PATH . 'cache/',
            'filePermissions' => 777,
            'dirPermissions' => 777,
        ];

        return new \Stash\Driver\Sqlite($options);
    }
}

/**
 * [setupAPC description]
 * @return [type] [description]
 */
if (!function_exists('setupAPC')) {
    function setupAPC()
    {
        $options = [
            'ttl' => (config('cache')['lifetime'] * 60),
            'namespace' => md5(__file__),
        ];

        return new \Stash\Driver\Apc($options);
    }
}

/**
 * [setupMemcached description]
 * @return [type] [description]
 */
if (!function_exists('setupMemcached')) {
    function setupMemcached()
    {
        $options = [
            'servers' => [
                config('memcached')['host'],
                config('memcached')['port'],
            ],
            'serializer' => 'json',
        ];

        return new \Stash\Driver\Memcache($options);
    }
}

/**
 * [setupRedis description]
 * @return [type] [description]
 */
if (!function_exists('setupRedis')) {
    function setupRedis()
    {
        $options = [
            'servers' => [
                config('redis')['host'],
                config('redis')['port'],
            ],
        ];

        return new \Stash\Driver\Redis($options);
    }
}

/**
 * [setupEphemeral description]
 * @return [type] [description]
 */
if (!function_exists('setupEphemeral')) {
    function setupEphemeral()
    {
        $options = [];

        return new \Stash\Driver\Ephemeral($options);
    }
}

/**
 * [cache description]
 * @return [type] [description]
 */
if (!function_exists('cache')) {
    function cache($key, $data = null, $ttl = null)
    {
        $pool = configPool();

        $item = $pool->getItem(md5($key));

        if (!is_null($data)) {
            if (is_null($ttl)) {
                $ttl = config('cache')['lifetime'];
            }
            $ttl *= 60;

            $item->expiresAfter($ttl);
            $item->set($data);

            $pool->save($item);
        }

        return $item->get();
    }
}
