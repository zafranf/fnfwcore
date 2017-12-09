<?php
/**
 * [getMemcached description]
 * @return [type] [description]
 */
if (!function_exists('getConnectMC')) {
    function getConnectMC()
    {
        $config = config();

        $mc = new Memcached();
        $mc->addServer($config['memcached']['host'], $config['memcached']['port']);

        return $mc;
    }
}

/**
 * [setMemcached description]
 * @param [type] $name [description]
 * @param [type] $data [description]
 * @param [type] $time [description]
 */
if (!function_exists('setMemcached')) {
    function setMemcached($name, $data, $time = 10)
    {
        $mc = getConnectMC();

        $time = $time * 60;
        $name = md5($name);
        $cache = $mc->set($name, $data, $time);

        return $cache;
    }
}

/**
 * [getMemcached description]
 * @param  [type] $name [description]
 * @return [type]       [description]
 */
if (!function_exists('getMemcached')) {
    function getMemcached($name)
    {
        $mc = getConnectMC();

        $name = md5($name);
        $cache = $mc->get($name);

        return $cache;
    }
}

/**
 * [cacheFile description]
 * @param  [type] $name   [description]
 * @param  [type] $buffer [description]
 * @return [type]         [description]
 */
/*function cacheFile($name, $buffer) {
global $config;

$cachename = md5($name);
$target = STORAGE_PATH."cache/".$cachename;

if (file_exists($target)) {
$cache = trim(file_get_contents($target));
$content = explode('[fnfw]', $cache);
// if ((time()-100)<$content[0] && strlen(trim($buffer))==strlen(trim($content[1]))) {
if (strlen(trim($buffer))==strlen(trim($content[1]))) {
$buffer = trim($content[1]);
} else {
$cache = trim(time().'[fnfw]'.$buffer);
file_put_contents($target, $cache);
chmod($target, 0777);
}
} else {
$cache = trim(time().'[fnfw]'.$buffer);
file_put_contents($target, $cache);
chmod($target, 0777);
}

return $buffer;
}*/

if (!function_exists('cacheMemcached')) {
    function cacheMemcached($name, $buffer, $time = 10)
    {
        $cache = getMemcached($name);
        if ($cache && (strlen(trim($buffer)) == strlen(trim($cache)))) {
            $buffer = $cache;
        } else {
            setMemcached($name, $buffer, $time);
        }

        return $buffer;
    }
}

/**
 * [cache description]
 * @param  [type] $buffer [description]
 * @return [type]         [description]
 */
if (!function_exists('cache')) {
    function cache($key, $data)
    {
        $config = config();

        // if ($config['cache']['active']) {
        // $url = getUrl();

        /* $minify = $config['minify'];
        if ($minify) {
        $data = sanitize_output($data);
        } */

        $driver = $config['cache']['driver'];
        if ($driver == "file") {
            // $data = cacheFile($url, $data);
        } else if ($driver == "memcached") {
            $data = cacheMemcached($key, $data);
        }
        // }

        return $data;
    }
}

/**
 * [getUrl description]
 * @return [type] [description]
 */
if (!function_exists('getUrl')) {
    function getUrl()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $url = $_SERVER['REQUEST_URI'];
        } else {
            $url = $_SERVER['SCRIPT_NAME'];
            $url .= (!empty($_SERVER['QUERY_STRING'])) ? '?' . $_SERVER['QUERY_STRING'] : '';
        }

        return $url;
    }
}

/**
 * [sanitize_output description]
 * @param  [type] $buffer [description]
 * @return [type]         [description]
 */
if (!function_exists('sanitize_output')) {
    function sanitize_output($buffer)
    {
        $search = array(
            // '/\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<> ?\/])\s*/s',
            '/\s*([~!@*\(\)+=\{\}\[\]])\s*/s', // remove white–space(s) around punctuation(s)
            '/\s+\/\>/s', // remove space in single tag close
            '/\>\s+\</s', // remove space between tags
            '/\/\*(?!\[if)([\s\S]+?)\*\//s', // remove comment except IE
            '/<\!-- (?!\[if|\<\/body)([\s\S]+?)-->/s', // remove comment except IE
            '/\>[^\S ]+/s', // strip whitespaces after tags, except space
            '/[^\S ]+\</s', // strip whitespaces before tags, except space
            '/(\s)+/s', // shorten multiple whitespace sequences
        );
        $replace = array(
            '$1',
            '/>',
            '><',
            '',
            '',
            '>',
            '<',
            '\\1',
        );

        $buffer = preg_replace($search, $replace, $buffer);

        return $buffer;
    }
}
