<?php
/* include helpers */
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
 * [generateToken description]
 * @return [type] [description]
 */
if (!function_exists('generateToken')) {
    function generateToken($string)
    {
        return md5($string . config('app')['key']);
    }
}
