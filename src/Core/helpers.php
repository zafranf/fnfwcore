<?php
// include helpers
if (file_exists(APP_PATH . 'helpers.php')) {
    include APP_PATH . 'helpers.php';
}

/**
 * [generatePaginate description]
 * @param  [type] $data [description]
 * @param  string $url  [description]
 * @return [type]       [description]
 */
if (!function_exists('generatePaginate')) {
    function generatePaginate($data, $url = '/')
    {
        $jumPage = ceil($data['total'] / $data['perpage']);
        if ($data['rows'] > 0) {
            echo '<div style="text-align: center;"><a class="button button-outline button-small" ' . ($data['current_page'] == 1) ? 'disabled' : 'href="' . $data['prev_page_link'] . '"' . '>&lt;</a>';
            $showPage = 0;
            for ($i = 1; $i <= $jumPage; $i++) {
                if ((($i >= $data['current_page'] - 3) && ($i <= $data['current_page'] + 3)) || ($i == 1) || ($i == $jumPage)) {
                    if (($showPage == 1) && ($i != 2)) {
                        echo '<a class="button button-outline button-small" disabled>.</a>';
                    }

                    if (($showPage != ($jumPage - 1)) && ($i == $jumPage)) {
                        echo '<a class="button button-outline button-small" disabled>.</a>';
                    }

                    echo '<a class="button button-outline button-small" ' . ($i == $data['current_page']) ? 'disabled' : 'href="' . $url . '?page=' . $i . '"' . '>' . $i . '</a>';
                    $showPage = $i;
                }
            }
            echo '<a class="button button-outline button-small" ' . ($data['current_page'] == $data['last_page']) ? 'disabled' : 'href="' . $data['next_page_link'] . '"' . '>&gt;</a></div>';
        }
    }
}

/**
 * [generateFlashMessages description]
 * @return [type] [description]
 */
if (!function_exists('generateFlashMessages')) {
    function generateFlashMessages()
    {
        $res = '';
        if (!empty($_SESSION['flash_messages'])) {
            $fm = $_SESSION['flash_messages'];

            if ($fm['type_message'] == "failed") {
                echo '<div class="error-messages">Ups, anda harus memperbaiki kesalahan berikut: <ul>';
                foreach ($fm['message'] as $msg) {
                    if ($msg != "") {
                        echo '<li>' . $msg . '</li>';
                    }
                }
                echo '</ul></div>';
            } else {
                echo '<div class="success-messages">' . $_SESSION['flash_messages']['message'] . '</div>';
            }

            $_SESSION['flash_messages'] = [];
        }
    }
}

/**
 * [config description]
 * @return [type] [description]
 */
if (!function_exists('config')) {
    function config($key = null)
    {
        $conf = require ROOT_PATH . 'vendor/zafranf/fnfwcore/src/Config/config.php';

        if (!is_null($key) && isset($conf[$key])) {
            $conf = $conf[$key];
        }

        return $conf;
    }
}

/**
 * [debug description]
 * @param  [type]  $data [description]
 * @param  boolean $die  [description]
 * @return [type]        [description]
 */
if (!function_exists('debug')) {
    function debug()
    {
        array_map(function ($data) {
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        }, func_get_args());

        die();
    }
}

/**
 * [nf description]
 * @param  [type] $num [description]
 * @return [type]      [description]
 */
if (!function_exists('nf')) {
    function nf($num, $digit = 0, $coms = ",", $dots = ".")
    {
        return number_format($num, $digit, $coms, $dots);
    }
}

/**
 * [slug description]
 * @param  [type] $text [description]
 * @param  string $rep  [description]
 * @return [type]       [description]
 */
if (!function_exists('slug')) {
    function slug($text, $rep = "-")
    {
        $text = strtolower($text);
        $text = preg_replace('([\s\W\_]+)', $rep, $text);

        return $text;
    }
}

/**
 * [url description]
 * @param  string  $url  [description]
 * @param  boolean $full [description]
 * @return [type]        [description]
 */
if (!function_exists('url')) {
    function url($url = "", $secure = false)
    {
        /* validas8 */
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        /* set variable */
        $http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
        $s1 = $secure ? 'https' : $http;
        $s2 = _server('SERVER_NAME') . '/';
        if (!filter_var($s2, FILTER_VALIDATE_URL)) {
            $s2 = _server('HTTP_HOST') . '/';
        }
        $s3 = ($url != "/") ? ltrim($url, '/') : '';

        return sprintf("%s://%s%s", $s1, $s2, $s3);
    }
}

/**
 * [bool description]
 * @param  [type]  $str [description]
 * @return [type]        [description]
 */
if (!function_exists('bool')) {
    function bool($str = false)
    {
        $true = ['true', 't', 'yes', 'y', '1', 'on'];

        if (is_string($str) || is_int($str) || is_bool($str)) {
            $str = strtolower(trim($str));

            return in_array($str, $true);
        }

        return false;
    }
}

/**
 * [is_json description]
 * @param  [type]  $data [description]
 * @return [type]        [description]
 */
if (!function_exists('is_json')) {
    function is_json($data = null)
    {
        if (!is_null($data)) {
            @json_decode($data);
            return (json_last_error() === JSON_ERROR_NONE);
        }

        return false;
    }
}

/**
 * Generate link stylesheet tag
 *
 * @param string $file
 * @param array $attributes
 * @return string
 */
if (!function_exists('load_css')) {
    function load_css($file = "", $attributes = [])
    {
        if (file_exists(public_path($file))) {
            $mtime = filemtime(public_path($file));

            return '<link href="' . url($file) . '?' . $mtime . '" rel="stylesheet">';
        }
    }
}

/**
 * Generate script tag
 *
 * @param string $file
 * @param boolean $async
 * @param array $attributes
 * @return string
 */
if (!function_exists('load_js')) {
    function load_js($file = "", $async = false, $attributes = [])
    {
        if (file_exists(public_path($file))) {
            $mtime = filemtime(public_path($file));
            $async = ($async) ? 'async' : '';

            return '<script src="' . url($file) . '?' . $mtime . '" ' . $async . '></script>';
        }
    }
}

/**
 * Get public folder path
 *
 * @param string $file
 * @return string
 */
if (!function_exists('public_path')) {
    function public_path($file = "")
    {
        return PUBLIC_PATH . $file;
    }
}

/**
 * Get storage folder path
 *
 * @param string $file
 * @return string
 */
if (!function_exists('storage_path')) {
    function storage_path($file = "")
    {
        return STORAGE_PATH . $file;
    }
}
