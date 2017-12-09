<?php
$routes = require_once APP_PATH.'routes.php';

/* set default controller */
$config = config();
$file = $config['default_controller'];

/* handle  request uri */
if ($_SERVER['REQUEST_URI']!='/') {
    /* separate from params */
    $uri = explode('?', $_SERVER['REQUEST_URI']);
    $file = trim($uri[0], '/'); 
}

/* check existing file */
$file = checkRouteFile($file, $routes, $config); 

/**
 * [checkRouteFile description]
 * @param  [type] $file [description]
 * @return [type]       [description]
 */
function checkRouteFile($file, $routes, $config) {
    $target = $file;
    $_SESSION['params'] = [];

    // checking file from full uri (w/o params)
    if (!file_exists($config['app_controllers'].$target.'.php')) {
        // explode uri by /
        $files = explode("/", $file);

        // count uri
        for ($i=(count($files)-1); $i>=0;$i--) {
            // reimplode the uri
            $target = implode("/", $files);

            // check file exist with new target
            if (file_exists($config['app_controllers'].$target.'.php')) {
                setRouteSession($target);
                return $target;
            } else if (file_exists($config['app_controllers'].$target.'/index.php')) {
                $target = $target.'/index';
                setRouteSession($target);
                return $target;
            } else {
                /* check routes */
                $check = checkRoutes($target, $routes);
                if ($check!=404) {
                    setRouteSession($target);
                    return $check;
                }
            }

            // store param to session
            $_SESSION['params'][] = $files[$i];

            // take out param from uri
            unset($files[$i]);
        }
    }

    /* for dynamic root */
    if (($target==404 || (isset($check) && $check==404)) && in_array('*', array_keys($routes))) {
        $file = $routes['*'];
    }

    return $file;
}

/**
 * [checkRoutes description]
 * @param  [type] $file [description]
 * @return [type]       [description]
 */
function checkRoutes($file, $routes) {
    $target = 404;
    
    /* check route in array routes */
    if (in_array($file, array_keys($routes))) {
        $target = $routes[$file];
    }

    return $target;
}

/**
 * [setRouteSession description]
 * @param [type] $route [description]
 */
function setRouteSession($route) {
    /* reverse session params */
    $_SESSION['params'] = array_reverse($_SESSION['params']);
    
    /* take out routes in params */
    $xroute = explode("/", $route);
    foreach ($xroute as $key => $val) {
        if (isset($_SESSION['params'][$key]) && $_SESSION['params'][$key]==$val) {
            unset($_SESSION['params'][$key]);
        }
    }
}

/**
 * [controller description]
 * @param  [type] $file [description]
 * @return [type]       [description]
 */
if (!function_exists('controller')) {
    function controller($file) {
        $config = config();

        if ($config['cache']['active']) {
            ob_start("cache");
        }

        $params     = getParameters();
        $controller = $file;
        $route      = getRoute();
        $target     = $config['app_controllers'].$file.'.php';

        /* Rearrange Input Files */
        if (isset($_FILES)) {
            $_FILES = reArrangeFiles();
        }

        if (file_exists($target)) {
            return require $target;
        }

        return view_error(404);
    }
}

/**
 * [view description]
 * @param  [type] $file [description]
 * @param  array $data [description]
 * @return [type]       [description]
 */
if (!function_exists('view')) {
    function view() {
        $code = 200; $file = ''; $data = [];
        foreach (func_get_args() as $arg) {
            if (is_int($arg)) {
                $code = $arg;
            } else if (is_string($arg)) {
                $file = $arg;
            } else if (is_array($arg)) {
                $data = $arg;
            }
        }

        http_response_code($code);

        $config = config();
        if ($config['cache']['active']) {
            ob_start("cache");
        }

        $params = getParameters();
        $target = $config['app_views'].$file.'.php';

        if (file_exists($target)) {
            extract($data);
            return require $target;
        }

        return view_error(404);
    }
}

/**
 * [view_error description]
 * @param  integer $code [description]
 * @param  string  $file [description]
 * @return [type]        [description]
 */
if (!function_exists('view_error')) {
    function view_error() {
        $code = 0; $file = '';
        foreach (func_get_args() as $arg) {
            if (is_int($arg)) {
                $code = $arg;
            } else if (is_string($arg)) {
                $file = $arg;
            }
        }

        http_response_code($code);

        $config = config();
        $file = !empty($file)?$file:'errors/'.$code;

        return require $config['app_views'].$file.'.php';
    }
}

return controller($file);
