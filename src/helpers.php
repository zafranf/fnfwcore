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
    function url($url = "", $full = false)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        if ($full) {
            $urls = explode("?", $_SERVER['REQUEST_URI']);
            $segment = $urls[0];
        }
        return sprintf(
            "%s://%s%s%s",
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
            $_SERVER['SERVER_NAME'] . '/',
            isset($segment) ? ltrim($segment, '/') : '',
            $url != "/" ? ltrim($url, '/') : ''
        );
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
        if (is_string($str) || is_int($str)) {
            $str = strtolower(trim($str));
            if ($str == 'true' || $str == 't' || $str == 'yes' || $str == 'y' || $str == '1') {
                return true;
            }
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
 * [auth description]
 * @return boolean [description]
 */
if (!function_exists('auth')) {
    function auth($key = '')
    {
        $auth = [];
        if (isLogin()) {
            $auth = _session('user');
            if ($key != '') {
                if (isset($auth[$key])) {
                    $auth = $auth[$key];
                } else {
                    return null;
                }
            }
        }

        return $auth;
    }
}
