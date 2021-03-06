<?php
/**
 * [exception_handler description]
 * @param  [type] $severity [description]
 * @param  [type] $message  [description]
 * @param  [type] $file     [description]
 * @param  [type] $line     [description]
 * @return [type]           [description]
 */
/* set error handler */
exception_handler();
function exception_handler()
{
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->pushHandler(function ($e) {
        /* save log */
        _log($e->__toString() . ' throw in ' . $e->getFile() . ' on line ' . $e->getLine());

        /* hide error */
        if (!config('app')['debug']) {
            $string = '<p><h2>Something wrong..</h2><hr></p>';
            debug($string);
        }
    });
    $whoops->register();
}

/**
 * [_log description]
 * @param  [type] $data [description]
 * @return [type]       [description]
 */
if (!function_exists('_log')) {
    function _log($data)
    {
        $target = STORAGE_PATH . "logs/" . date("Ymd") . ".log";
        $log = "[" . date("Y-m-d H:i:s") . "] " . (is_array($data) ? print_r($data, true) : $data) . "\r\n";

        file_put_contents($target, $log, FILE_APPEND);
    }
}

/**
 * [_goto description]
 * @param  string $url [description]
 * @return [type]      [description]
 */
if (!function_exists('_goto')) {
    function _goto($url = "/")
    {
        header("location: " . $url);
        die();
    }
}

/**
 * [_session description]
 * @param  [type]  $str [description]
 * @param  boolean $int [description]
 * @return [type]       [description]
 */
if (!function_exists('_server')) {
    function _server($key = null)
    {
        /* Check $key */
        if (is_null($key)) {
            return $_SERVER;
        }

        /* Check requested string */
        $key = strtoupper($key);
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        return null;
    }
}

/**
 * [_session description]
 * @param  [type]  $str [description]
 * @param  boolean $int [description]
 * @return [type]       [description]
 */
if (!function_exists('_session')) {
    function _session($key = null)
    {
        /* Check $key */
        if (is_null($key)) {
            return $_SESSION;
        }

        /* Check requested string */
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return null;
    }
}

/**
 * [_cookie description]
 * @param  [type]  $str [description]
 * @param  boolean $int [description]
 * @return [type]       [description]
 */
if (!function_exists('_cookie')) {
    function _cookie($key = null)
    {
        /* Check $key */
        if (is_null($key)) {
            return $_COOKIE;
        }

        /* Check requested string */
        if (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        }

        return null;
    }
}

/**
 * [_input description]
 * @param  [type]  $str [description]
 * @param  boolean $int [description]
 * @return [type]       [description]
 */
if (!function_exists('_input')) {
    function _input($key = null, $default = null)
    {
        /* Check $key */
        if (is_null($key)) {
            return $_REQUEST;
        }

        /* Check requested string */
        if (isset($_REQUEST[$key])) {
            return $_REQUEST[$key];
        }

        return $default;
    }
}

/**
 * [_get description]
 * @param  [type]  $key [description]
 * @param  boolean $int [description]
 * @return [type]       [description]
 */
if (!function_exists('_get')) {
    function _get($key = null, $default = null)
    {
        /* Check $key */
        if (is_null($key)) {
            return $_GET;
        }

        /* Check requested string */
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        return $default;
    }
}

/**
 * [_post description]
 * @param  [type]  $key [description]
 * @param  boolean $int [description]
 * @return [type]       [description]
 */
if (!function_exists('_post')) {
    function _post($key = null, $default = null)
    {
        /* Check $key */
        if (is_null($key)) {
            return $_POST;
        }

        /* Check requested string */
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        return $default;
    }
}

/**
 * [_file description]
 * @param  [type]  $file [description]
 * @return [type]       [description]
 */
if (!function_exists('_file')) {
    function _file($name)
    {
        $fl = null;

        /* Check requested file */
        if (!is_array($name) && isset($_FILES[$name])) {
            $file = $_FILES[$name];
        }

        /* Mapping file */
        if (isset($file['name']) && $file['name'] != "" && $file['error'] == 0) {
            $xname = explode(".", $file['name']);
            $fl = [];
            $fl['filename'] = $file['name'];
            $fl['name'] = str_replace('.' . end($xname), "", $file['name']);
            $fl['ext'] = '.' . end($xname);
            $fl['tmp'] = $file['tmp_name'];
            $fl['size'] = round($file['size'] / 1024, 2); //in KB
            $fl['mime'] = mime_content_type($fl['tmp']);

            /* Get image dimension */
            $mime = explode("/", $fl['mime'])[0];
            if ($mime == "image" || in_array($fl['ext'], ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'])) {
                $info = getimagesize($fl['tmp']);
                $fl['width'] = $info[0];
                $fl['height'] = $info[1];
            }
        }

        return $fl;
    }
}

/**
 * [_files description]
 * @param  [type]  $file [description]
 * @return [type]       [description]
 */
if (!function_exists('_files')) {
    function _files($key = null)
    {
        /* rearrange files */
        $_FILES = reArrangeFiles();

        /* Check $key */
        if (is_null($key)) {
            return $_FILES;
        }

        /* Check requested string */
        if (isset($_FILES[$key])) {
            return _file($key);
        }

        return null;
    }
}

/**
 * [reArrangeFiles description]
 * http://php.net/manual/en/features.file-upload.multiple.php#118180
 * @param  [type] $files [description]
 * @return [type]        [description]
 */
if (!function_exists('reArrangeFiles')) {
    function reArrangeFiles()
    {
        $walker = function ($files, $fileInfokey, callable $walker) {
            $ret = [];
            foreach ($files as $k => $v) {
                if (is_array($v)) {
                    $ret[$k] = $walker($v, $fileInfokey, $walker);
                } else {
                    $ret[$k][$fileInfokey] = $v;
                }
            }
            return $ret;
        };

        $files = [];
        foreach ($_FILES as $name => $values) {
            /* init for array_merge */
            if (!isset($files[$name])) {
                $files[$name] = [];
            }
            if (!is_array($values['error'])) {
                /* normal syntax */
                $files[$name] = $values;
            } else {
                /* html array feature */
                foreach ($values as $fileInfoKey => $subArray) {
                    $files[$name] = array_replace_recursive($files[$name], $walker($subArray, $fileInfoKey, $walker));
                }
            }
        }

        return $files;
    }
}

/**
 * [isLogin description]
 * @return boolean [description]
 */
if (!function_exists('isLogin')) {
    function isLogin()
    {
        if (_session('user') !== null && !empty(_session('user'))) {
            return true;
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

/**
 * Undocumented function
 *
 * @return void
 */
if (!function_exists('visitorLog')) {
    function visitorLog()
    {
        $device = '';
        $agent = new \Jenssegers\Agent\Agent();
        if ($agent->isPhone()) {
            $device = 'phone';
        } else if ($agent->isTablet()) {
            $device = 'tablet';
        } else if ($agent->isDesktop()) {
            $device = 'desktop';
        }
        $device_name = $agent->device();
        $ip = (_server('REMOTE_ADDR') != null) ? _server('REMOTE_ADDR') : '127.0.0.1';
        $browser_agent = $agent->getUserAgent();
        $browser = $agent->browser();
        $browser_version = $agent->version($browser);
        $os = $agent->platform();
        $os_version = $agent->version($os);
        $page = (_server('REQUEST_URI') != null) ? _server('REQUEST_URI') : '/';
        $referrer = (_server('HTTP_REFERER') != null) ? _server('HTTP_REFERER') : '';
        $referral = str_replace(url('/'), "/", $referrer);
        $is_robot = $agent->isRobot() ? 1 : 0;
        $robot_name = $is_robot ? $agent->robot() : '';

        $params = [
            'ip' => $ip,
            'page' => $page,
            'referral' => $referral,
            'agent' => $browser_agent,
            'browser' => $browser,
            'browser_version' => $browser_version,
            'device' => $device,
            'device_name' => $device_name,
            'os' => $os,
            'os_version' => $os_version,
            'is_robot' => $is_robot,
            'robot_name' => $robot_name,
        ];

        $table = db()->table('visitor_logs');
        $q = $table->where(function ($q) {
            $q->whereBetween('created_at', date("Y-m-d H:00:00"), date("Y-m-d H:59:59"));
        });
        foreach ($params as $key => $value) {
            $q->where($key, $value);
        }
        $find = $q->first();

        if (!$find) {
            $params['count'] = db()->raw('count+1');
            $params['created_at'] = date("Y-m-d H:i:s");
            $table->insert($params);
        } else {
            $q->update([
                'count' => db()->raw('count+1'),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
        }
    }
}

/**
 * Undocumented function
 *
 * @param [type] $description
 * @return void
 */
if (!function_exists('activityLog')) {
    function activityLog($description)
    {
        /* Filter password */
        $sensor = 'xxx';
        if (isset($_POST['password'])) {
            $_POST['password'] = $sensor;
        }
        if (isset($_POST['password_confirmation'])) {
            $_POST['password_confirmation'] = $sensor;
        }
        if (isset($_POST['user_password'])) {
            $_POST['user_password'] = $sensor;
        }

        $save = db()->table('activity_logs')->insert([
            'description' => $description,
            'method' => _server('REQUEST_METHOD'),
            'path' => _server('REQUEST_URI'),
            'ip' => _server('REMOTE_ADDR'),
            'get' => json_encode(_get()),
            'post' => json_encode(_post()),
            'files' => json_encode(_files()),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

/**
 * Undocumented function
 *
 * @param array $params
 * @return void
 */
if (!function_exists('validation')) {
    function validation(array $params)
    {
        $requests = $params['requests'];
        $rules = $params['rules'];
        $messages = $params['messages'] ?? [];
        $aliases = $params['aliases'] ?? [];
        $redirect = $params['redirect'] ?? _server('HTTP_REFERER');

        $validator = new \Rakit\Validation\Validator;
        $validation = $validator->make($requests, $rules);
        if (!empty($messages)) {
            $validation->setMessages($messages);
        }
        if (!empty($aliases)) {
            $validation->setAliases($aliases);
        }
        $validation->validate();

        if ($validation->fails()) {
            setFlashMessages($validation->errors->firstOfAll());
            return _goto(($redirect ?? '/'));
        }
    }
}

/**
 * [setFlashMessages description]
 * @param array $messages  [description]
 * @param string $type [description]
 * @return [type] [description]
 */
if (!function_exists('setFlashMessages')) {
    function setFlashMessages(array $messages, $type = 'failed')
    {
        /* Set the value */
        $_SESSION['flash_messages'] = $messages;

        /* Set type message */
        $_SESSION['flash_messages']['type_message'] = $type;
    }
}

/**
 * [setFlashMessage description]
 * @param string $key  [description]
 * @param string $val  [description]
 * @param string $type [description]
 * @return [type] [description]
 */
if (!function_exists('setFlashMessage')) {
    function setFlashMessage($key, $val = null, $type = 'failed')
    {
        /* Override val & type */
        if ($val == "success" || $val == "failed") {
            $type = $val;
            $val = null;
        }

        /* Check the value */
        if (is_null($val)) {
            $_SESSION['flash_messages']['message'] = $key;
        } else {
            $_SESSION['flash_messages'][$key] = $val;
        }

        /* Set type message */
        $_SESSION['flash_messages']['type_message'] = $type;
    }
}

/**
 * [getFlashMessages description]
 * @return [type] [description]
 */
if (!function_exists('getFlashMessages')) {
    function getFlashMessages()
    {
        $fm = [];
        if (checkFlashMessages()) {
            $fm = $_SESSION['flash_messages'];
            $_SESSION['flash_messages'] = [];
        }

        return $fm;
    }
}

/**
 * [checkFlashMessages description]
 * @return [type] [description]
 */
if (!function_exists('checkFlashMessages')) {
    function checkFlashMessages()
    {
        if (!empty($_SESSION['flash_messages'])) {
            return true;
        }

        return false;
    }
}

/**
 * Undocumented function
 *
 * @param [type] $data
 * @param integer $statusCode
 * @param boolean $json
 * @return void
 */
if (!function_exists('response')) {
    function response($data, $statusCode = 200, $json = true)
    {
        http_response_code($statusCode);
        if ($json) {
            header('Content-Type: application/json');
            $data = json_encode($data);
        }

        die($data);
    }
}

/**
 * [getRoute description]
 * @return [type] [description]
 */
if (!function_exists('getRoute')) {
    function getRoute()
    {
        $uri = isset($_SERVER['REQUEST_URI']) ? explode("?", $_SERVER['REQUEST_URI'])[0] : '';

        $exclude = ["index", "add", "edit", "delete", "detail", "save", "update"];
        foreach ($exclude as $excl) {
            $uri = preg_replace("/\/" . $excl . "(\S+)?/", "", $uri);
        }

        return $uri;
    }
}

/**
 * [getFlashMessages description]
 * @return [type] [description]
 */
if (!function_exists('getParameters')) {
    function getParameters()
    {
        $params = [];

        /* Check Session Params */
        if (isset($_SESSION['params'])) {
            $params = $_SESSION['params'];
        }

        /* Merge with $_GET */
        $params = array_merge($params, $_GET);

        return $params;
    }
}

/**
 * [sendMail description]
 * @param  array  $par [description]
 * @return [type]      [description]
 */
if (!function_exists('sendMail')) {
    function sendMail($par = [])
    {
        $cfg = config('mail');

        /* Start PHPMailer */
        $mail = new \PHPMailer\PHPMailer\PHPMailer;

        /* SMTP Config */
        if (isset($cfg['is_smtp'])) {
            $mail->isSMTP();
            $mail->SMTPAuth = $cfg['smtp']['auth'];
            $mail->SMTPSecure = $cfg['smtp']['secure'];
            $mail->SMTPDebug = $cfg['smtp']['debug'];
        } else {
            $mail->isMail();
        }

        /* Host & User Auth */
        $mail->Host = $cfg['host'];
        $mail->Port = $cfg['port'];
        $mail->Username = $cfg['username'];
        $mail->Password = $cfg['password'];

        /* Set Message ID */
        $mail->MessageID = '<' . md5(date('YmdHis') . uniqid()) . '@' . _server('HTTP_HOST') . '>';

        /* Set Sender */
        if (isset($par['from'])) {
            if (is_array($par['from'])) {
                if (isset($par['from']['email'])) {
                    $from_name = isset($par['from']['name']) ? $par['from']['name'] : '';
                    $mail->setFrom($par['from']['email'], $from_name);
                }
            } else {
                $mail->setFrom($par['from']);
            }
        } else {
            $mail->setFrom($cfg['username']);
        }

        /* Set Sender 'Reply To' */
        if (isset($par['reply_to'])) {
            if (is_array($par['reply_to'])) {
                if (isset($par['reply_to']['email'])) {
                    $replyto_name = isset($par['reply_to']['name']) ? $par['reply_to']['name'] : '';
                    $mail->addReplyTo($par['reply_to']['email'], $replyto_name);
                }
            } else {
                $mail->addReplyTo($par['reply_to']);
            }
        }

        /* Set 'To' Recipient */
        if (isset($par['to'])) {
            if (is_array($par['to'])) {
                /* Check if recipient more than 1 */
                if (isset($par['to'][0])) {
                    foreach ($par['to'] as $key => $val) {
                        if (isset($val['email'])) {
                            $to_name = isset($val['name']) ? $val['name'] : '';
                            $mail->addAddress($val['email'], $to_name);
                        } else {
                            $mail->addAddress($val);
                        }
                    }
                }
                /* Check if recipient only 1 and using name */
                else if (isset($par['to']['email'])) {
                    $to_name = isset($par['to']['name']) ? $par['to']['name'] : '';
                    $mail->addAddress($par['to']['email'], $to_name);
                }
            } else {
                /* Check if recipient only 1 and just email */
                $mail->addAddress($par['to']);
            }
        }

        /* Set 'Cc' Recipient */
        if (isset($par['cc'])) {
            if (is_array($par['cc'])) {
                /* Check if 'Cc' recipient more than 1 */
                if (isset($par['cc'][0])) {
                    foreach ($par['cc'] as $key => $val) {
                        if (isset($val['email'])) {
                            $cc_name = isset($val['name']) ? $val['name'] : '';
                            $mail->addCC($val['email'], $cc_name);
                        } else {
                            $mail->addCC($val);
                        }
                    }
                }
                /* Check if 'Cc' recipient only 1 and using name */
                else if (isset($par['cc']['email'])) {
                    $cc_name = isset($par['cc']['name']) ? $par['cc']['name'] : '';
                    $mail->addCC($par['cc']['email'], $cc_name);
                }
            } else {
                /* Check if 'Cc' recipient only 1 and just email */
                $mail->addCC($par['cc']);
            }
        }

        /* Set 'Bcc' Recipient */
        if (isset($par['bcc'])) {
            if (is_array($par['bcc'])) {
                /* Check if 'Bcc' recipient more than 1 */
                if (isset($par['bcc'][0])) {
                    foreach ($par['bcc'] as $key => $val) {
                        if (isset($val['email'])) {
                            $bcc_name = isset($val['name']) ? $val['name'] : '';
                            $mail->addBCC($val['email'], $bcc_name);
                        } else {
                            $mail->addBCC($val);
                        }
                    }
                }
                /* Check if 'Bcc' recipient only 1 and using name */
                else if (isset($par['bcc']['email'])) {
                    $bcc_name = isset($par['bcc']['name']) ? $par['bcc']['name'] : '';
                    $mail->addBCC($par['bcc']['email'], $bcc_name);
                }
            } else {
                /* Check if 'Bcc' recipient only 1 and just email */
                $mail->addBCC($par['bcc']);
            }
        }

        /* Set Attachments */
        if (isset($par['attachments'])) {
            if (is_array($par['attachments'])) {
                /* Check if attachment more than 1 */
                if (isset($par['attachments'][0])) {
                    foreach ($par['attachments'] as $key => $val) {
                        if (isset($val['file'])) {
                            $attachment_name = isset($val['name']) ? $val['name'] : '';
                            $mail->addAttachment($val['file'], $attachment_name);
                        } else {
                            $mail->addAttachment($val);
                        }
                    }
                }
                /* Check if attachment only 1 and using name */
                else if (isset($par['attachments']['file'])) {
                    $attachment_name = isset($par['attachments']['name']) ? $par['attachments']['name'] : '';
                    $mail->addAttachment($par['attachments']['file'], $attachment_name);
                }
            } else {
                /* Check if attachment only 1 and just filename */
                $mail->addAttachment($par['attachments']);
            }
        }

        /* Always HTML */
        $mail->isHTML(true);

        /* Set Mail Content */
        $mail->Subject = $par['subject'];
        $mail->Body = $par['message'];
        if (isset($par['message_alt']) && $par['message_alt'] != "") {
            $mail->AltBody = $par['message_alt'];
        }

        if ($mail->isError()) {
            throw new Exception('Message could not be sent. ' . $mail->ErrorInfo);
        }

        if (!$mail->send()) {
            throw new Exception('Message not sent. ' . $mail->ErrorInfo);
        }

        return $mail->getLastMessageID();
    }
}

/**
 * [uploadFile description]
 * @param  [type]  $file       [description]
 * @param  string  $folder     [description]
 * @param  [type]  $saveas     [description]
 * @param  boolean $imageoptim [description]
 * @return [type]              [description]
 */
if (!function_exists('uploadFile')) {
    function uploadFile($file, $folder = 'upload', $saveas, $imageoptim = false)
    {
        $return = false;

        /* Set folder location */
        if ($folder == "" || $folder == "upload") {
            $folder = PUBLIC_PATH . 'upload/';
        }

        /* Check directory and create if not exist */
        if (!is_dir($folder)) {
            mkdir($folder);
        }

        /* Proses upload file */
        $move = move_uploaded_file($file, $folder . $saveas);
        if ($move && $imageoptim) {
            $move = imageOptimation($folder . $saveas, $folder, $saveas);
        }

        if ($move) {
            $return = [
                'folder' => $folder,
                'filename' => $saveas,
            ];
        }

        return $return;
    }
}

/**
 * [imageOptimation description]
 * Original source: https://gist.github.com/ianmustafa/b8ab7dfd490ff2081ac6d29d828727db
 * @param  [type] $image  [description]
 * @param  [type] $folder [description]
 * @param  [type] $saveas [description]
 * @return [type]         [description]
 */
if (!function_exists('imageOptimation')) {
    function imageOptimation($image, $folder, $saveas)
    {
        $config = config();

        // image config
        $imageconfig = array(
            'sm' => array(
                'x' => 200,
                'y' => 150,
                'b' => 4,
            ),
            'md' => array(
                'x' => 400,
                'y' => 300,
                'b' => 8,
            ),
            'lg' => array(
                'x' => 800,
                'y' => 600,
                'b' => 16,
            ),
            'fb' => array(
                'x' => 1200,
                'y' => 630,
                'b' => 18,
            ),
        );

        if (isset($config['imageoptim'])) {
            $imageconfig = $config['imageoptim'];
        }

        // Optimalisasi gambar
        foreach ($imageconfig as $suffix => $config) {
            // Set nama file baru, pakai jenis gambar target sebagai suffix
            $name_ext = explode('.', $saveas);
            $savename_suffix = $name_ext[0] . ".{$suffix}." . end($name_ext);
            // Hitung aspek rasio gambar
            $or = $config['x'] / $config['y'];

            // Kita buat objek gambar dasar untuk diolah,
            // serta ambil informasi gambarnya
            $baseimage = new Imagick($image);
            $mime = $baseimage->getImageMimeType();
            $w = $baseimage->getImageWidth();
            $h = $baseimage->getImageHeight();
            $r = $w / $h;

            // Clone objek gambar dasar untuk dijadikan gambar utama,
            // lalu ubah ukurannya
            $mainimage = clone $baseimage; //$baseimage->clone();
            $mainimage->scaleImage($config['x'], $config['y'], true);
            // Jika rasio gambar tidak sesuai dengan dimensi target,
            // kita bisa membuat gambar latar blur untuk mengisi ruang kosong di sekitar gambar
            if ($r != $or) {
                // Buat kanvas baru
                $compimage = new Imagick();
                $compimage->newImage($config['x'], $config['y'], new ImagickPixel('#fff'));
                $compimage->setImageFormat($baseimage->getImageFormat());
                // Ambil dimensi baru dari gambar utama yang telah diubah ukurannya
                $nw = $mainimage->getImageWidth();
                $nh = $mainimage->getImageHeight();
                // Set ukuran gambar latar
                $bgw = $r < $or ? $config['x'] : ceil($config['y'] * $r);
                $bgh = $r < $or ? ceil($config['x'] * $h / $w) : $config['y'];
                // Set posisi gambar utama di kanvas
                $icx = $r < $or ? ceil(($config['x'] - $nw) / 2) : 0;
                $icy = $r < $or ? 0 : ceil(($config['y'] - $nh) / 2);
                // Set posisi gambar latar di kanvas
                $bgcx = $r < $or ? 0 : ceil(($config['x'] - $bgw) / 2);
                $bgcy = $r < $or ? ceil(($config['y'] - $bgh) / 2) : 0;
                // Lalu clone gambar dasar untuk dijadikan gambar latar,
                // ubah ukurannya, lalu blur dan set opacity-nya
                $bgimage = clone $baseimage; //$baseimage->clone();
                $bgimage->scaleImage($bgw, $bgh, true);
                $bgimage->gaussianBlurImage($config['b'], $config['b']);
                $bgimage->setImageOpacity(0.5);
                // Gabungkan semua gambar menjadi satu
                $compimage->compositeImage($bgimage, Imagick::COMPOSITE_DEFAULT, $bgcx, $bgcy);
                $compimage->compositeImage($mainimage, Imagick::COMPOSITE_DEFAULT, $icx, $icy);
                // Lalu cloen gambar hasil gabungan untuk dijadikan output
                $output = clone $compimage; //$compimage->clone();
                $compimage->destroy();
            }

            // Atau, jika dimensinya sesuai, langsung pakai gambar utama
            else {
                // Lalu cloen gambar utama untuk dijadikan output
                $output = clone $mainimage; //$mainimage->clone();
                $mainimage->destroy();
            }
            // Atur format gambar, kualitas kompresi dan opsi interlace
            $output->setImageFormat('jpg');
            $output->setImageCompression(Imagick::COMPRESSION_JPEG);
            $output->setImageCompressionQuality(60);
            $output->setImageUnits(Imagick::RESOLUTION_PIXELSPERINCH);
            $output->setImageResolution(72, 72);
            $output->setInterlaceScheme(Imagick::INTERLACE_PLANE);
            $output->stripImage();
            // Simpan gambar, namun jika gagal langsung return false
            if (!$output->writeImage($folder . "{$savename_suffix}")) {
                return false;
            }

            // Destroy :)
            $baseimage->destroy();
            $output->destroy();
        }

        return true;
    }
}

/**
 * [config description]
 * @return [type] [description]
 */
if (!function_exists('config')) {
    function config($key = null)
    {
        $config = include dirname(__DIR__) . "/Config/config.php";

        /* Check $key */
        if (is_null($key)) {
            return $config;
        }

        /* Check requested string */
        if (isset($config[$key])) {
            return $config[$key];
        }

        return null;
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
