<?php
$isCLI = strpos(php_sapi_name(), 'cli') !== false;
if ($isCLI) {
    return true;
}
$routes = require_once APP_PATH . 'routes.php';

/* set default controller */
$file = config('default_controller');

/* handle  request uri */
if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/') {
    /* separate from params */
    $uri = explode('?', $_SERVER['REQUEST_URI']);
    $file = trim($uri[0], '/');
}

/* cek root tanpa parameter */
if (!isset($file) || empty($file)) {
    $file = config('default_controller');
}

/* check existing file */
$file = checkRouteFile($file, $routes);
if (is_callable($file)) {
    return call_user_func($file);
}

/**
 * [checkRouteFile description]
 * @param  [type] $file [description]
 * @return [type]       [description]
 */
function checkRouteFile($file, $routes)
{
    $config = config();
    $target = $file;
    $_SESSION['params'] = [];

    // checking file from full uri (w/o params)
    $check = checkRoutes($target, $routes);
    if (is_callable($check)) {
        setRouteSession($target);
        return $check;
    } else if ($check != 404) {
        setRouteSession($target);
        return $check;
    }

    /* for dynamic root */
    if (($target == 404 || (isset($check) && $check == 404)) && in_array('*', array_keys($routes))) {
        $file = $routes['*'];
    }

    return $file;
}

/**
 * [checkRoutes description]
 * @param  [type] $file [description]
 * @return [type]       [description]
 */
function checkRoutes($file, $routes)
{
    $target = 404;

    /* check route in array routes */
    if (in_array($file, array_keys($routes))) {
        $target = $routes[$file];
        if (is_callable($routes[$file])) {
            return $routes[$file];
        }
    } else {
        foreach ($routes as $route => $fileTarget) {
            if (strpos($route, '*') !== false) {
                preg_match('/(\*)/', $route, $matchRoute);

                $reg = str_replace(['*', '/'], ['(.*)', '\/'], $route);
                $check = preg_match('/' . $reg . '/', $file, $match);
                if ($check) {
                    unset($match[0]);
                    $file = str_replace($match, $matchRoute, $file);

                    if (in_array($file, array_keys($routes))) {
                        $target = $routes[$file];
                        $_SESSION['params'] = array_reverse($match);

                        return $target;
                        // debug($target);
                    }
                }
            }
        }
    }

    return $target;
}

/**
 * [setRouteSession description]
 * @param [type] $route [description]
 */
function setRouteSession($route)
{
    /* reverse session params */
    $_SESSION['params'] = array_reverse($_SESSION['params']);

    /* take out routes in params */
    $xroute = explode("/", $route);
    foreach ($xroute as $key => $val) {
        if (isset($_SESSION['params'][$key]) && $_SESSION['params'][$key] == $val) {
            unset($_SESSION['params'][$key]);
        }
    }
}

return controller($file);
