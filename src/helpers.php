<?php
// include helpers
if (file_exists(APP_PATH.'helpers.php')) {
    include APP_PATH.'helpers.php';
}

/**
 * [generatePaginate description]
 * @param  [type] $data [description]
 * @param  string $url  [description]
 * @return [type]       [description]
 */
if (!function_exists('generatePaginate')) {
    function generatePaginate($data, $url='/') {
        $jumPage = ceil($data['total']/$data['perpage']);
        if ($data['rows']>0) {
        ?>
        <div style="text-align: center;">
            <a class="button button-outline button-small" <?=($data['current_page']==1)?'disabled':'href="'.$data['prev_page_link'].'"'?>>&lt;</a>
            <?php /*for($i=1;$i<=$data['last_page'];$i++) { ?>
                <a class="button button-outline button-small" <?=($i==$data['current_page'])?'disabled':'href="'.$url.'?page='.$i.'"'?>><?=$i;?></a>
            <?php }*/ ?>
            <?php
            $showPage = 0;
            for($i = 1; $i <= $jumPage; $i++) {
                if ((($i >= $data['current_page'] - 3) && ($i <= $data['current_page'] + 3)) || ($i == 1) || ($i == $jumPage)) {
                    if (($showPage == 1) && ($i != 2))  echo '<a class="button button-outline button-small" disabled>.</a>';
                    if (($showPage != ($jumPage - 1)) && ($i == $jumPage))  echo '<a class="button button-outline button-small" disabled>.</a>';
                    // if ($i == $data['current_page']) echo " <b>".$i."</b> ";
                    ?>
                    <a class="button button-outline button-small" <?=($i==$data['current_page'])?'disabled':'href="'.$url.'?page='.$i.'"'?>><?=$i;?></a>
                    <?php
                    $showPage = $i;
                }
            }
            ?>
            <a class="button button-outline button-small" <?=($data['current_page']==$data['last_page'])?'disabled':'href="'.$data['next_page_link'].'"'?>>&gt;</a>
        </div>
        <?php
        }
    }
}

/**
 * [generateFlashMessages description]
 * @return [type] [description]
 */
if (!function_exists('generateFlashMessages')) {
    function generateFlashMessages() {
        $res = '';
        if (!empty($_SESSION['flash_messages'])) {
            $fm = $_SESSION['flash_messages'];
            if ($fm['type_message']=="failed") {
                ?>
                <div class="error-messages">
                    Ups, anda harus memperbaiki kesalahan berikut:
                    <ul>
                    <?php
                    foreach ($fm['message'] as $msg) {
                        if ($msg!="") {
                            echo '<li>'.$msg.'</li>';
                        }
                    }
                    ?>
                    </ul>
                </div>
                <?php
            } else {
                ?>
                <div class="success-messages">
                    <?=$_SESSION['flash_messages']['message']?>
                </div>
                <?php
            }
            $_SESSION['flash_messages'] = [];
        }
    }
}

/**
 * [nf description]
 * @param  [type] $num [description]
 * @return [type]      [description]
 */
if (!function_exists('nf')) {
    function nf($num, $digit=0, $coms=",", $dots=".") {
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
    function slug($text, $rep="-") {
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
    function url($url="", $full=false){
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
            $_SERVER['SERVER_NAME'].'/',
            isset($segment)?ltrim($segment, '/'):'',
            $url!="/"?ltrim($url, '/'):''
        );
    }
}

/**
 * [config description]
 * @return [type] [description]
 */
if (!function_exists('config')) {
    function config($key=null) {
        $conf = require 'Config/config.php';
        
        if (!is_null($key) && isset($conf[$key])) {
            $conf = $conf[$key];
        }

        return $conf;
    }
}