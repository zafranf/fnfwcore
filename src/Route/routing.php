<?php
$routes = require_once APP_PATH . 'routes.php';

/* set default controller */
$file = config('default_controller');

/* handle  request uri */
if ($_SERVER['REQUEST_URI'] != '/') {
    /* separate from params */
    $uri = explode('?', $_SERVER['REQUEST_URI']);
    $file = trim($uri[0], '/');
}

/* check existing file */
$file = checkRouteFile($file, $routes);

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
    if (!file_exists($config['app']['controller_folder'] . $target . '.php')) {
        // explode uri by /
        $files = explode("/", $file);

        // count uri
        for ($i = (count($files) - 1); $i >= 0; $i--) {
            // reimplode the uri
            $target = implode("/", $files);

            // check file exist with new target
            if (file_exists($config['app']['controller_folder'] . $target . '.php')) {
                setRouteSession($target);
                return $target;
            } else if (file_exists($config['app']['controller_folder'] . $target . '/index.php')) {
                $target = $target . '/index';
                setRouteSession($target);
                return $target;
            } else {
                /* check routes */
                $check = checkRoutes($target, $routes);
                if ($check != 404) {
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

/**
 * [controller description]
 * @param  [type] $file [description]
 * @return [type]       [description]
 */
if (!function_exists('controller')) {
    function controller($file)
    {
        $config = config();

        $params = getParameters();
        $controller = $file;
        $route = getRoute();
        $target = $config['app']['controller_folder'] . $file . '.php';

        /* check file */
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
    function view()
    {
        $config = config();
        $code = 200;
        $file = '';
        $data = [];

        /* get arguments */
        foreach (func_get_args() as $arg) {
            if (is_int($arg)) {
                $code = $arg;
            } else if (is_string($arg)) {
                $file = $arg;
            } else if (is_array($arg)) {
                $data = $arg;
            }
        }

        /* set http header */
        http_response_code($code);

        /* set params */
        $params = getParameters();

        /* get file */
        $file = $config['app']['view_folder'] . $file . '.php';

        /* check file */
        if (file_exists($file)) {
            extract($data);
            return require $file;
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
    function view_error()
    {
        $config = config();
        $code = 0;
        $file = '';

        /* get arguments */
        foreach (func_get_args() as $arg) {
            if (is_int($arg)) {
                $code = $arg;
            } else if (is_string($arg)) {
                $file = $arg;
            }
        }

        /* set http header */
        http_response_code($code);

        /* get file */
        $file = !empty($file) ? $file : 'errors/' . $code;

        return require $config['app']['view_folder'] . $file . '.php';
    }
}

return controller($file);
