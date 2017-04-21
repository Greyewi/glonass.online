<?php

class commonClass
{
    static $start_time = 0;
    static $cities_list = array();
    public static $admin_mail = 'moi-testing@ya.ru, dain1982plus@mail.ru, info@2zp.online';

    function __construct()
    {
        $this->setDebug();
        self::$start_time = $this->getMicroTime();
    }

    function setDebug()
    {
        if ($_SERVER['SERVER_NAME'] === "3t_new") {
            error_reporting(E_ALL);
            ini_set("display_errors", true);
        } else {
            ini_set("display_errors", false);
        }
    }

    function transliterate($str)
    {
        $ret = "";
        $str = iconv('UTF-8', 'windows-1251', $str);
        $target_chars_str = "ёйцукенгшщзхъфывапролджэячсмитьбюЁЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ!\"№;%:?*()_+/@#$%^&|-=\\";
        $target_chars_str = iconv('UTF-8', 'windows-1251', $target_chars_str);
        $target_chars_arr = preg_split('//', $target_chars_str, -1, PREG_SPLIT_NO_EMPTY);
        $replace_chars = array('yo', 'j', 'c', 'u', 'k', 'e', 'n', 'g', 'sh', 'sch', 'z', 'h', '', 'f', 'yi', 'v', 'a', 'p', 'r', 'o', 'l', 'd', 'zh', 'e', 'ya', 'ch', 's', 'm', 'i', 't', '', 'b', 'yu', 'YO', 'J', 'C', 'U', 'K', 'E', 'N', 'G', 'SH', 'SCH', 'Z', 'H', '', 'F', 'YI', 'V', 'A', 'P', 'R', 'O', 'L', 'D', 'ZH', 'E', 'YA', 'CH', 'S', 'M', 'I', 'T', '', 'B', 'YU', '', '', 'no_', '_', '', '_', '', '', '_', '_', '_', '', '', '', 'no_', '', '', '', '_and_', '', '_', '_', '');

        $str_arr = preg_split('//', $str, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($str_arr as $char) {
            $replace_pos = array_keys($target_chars_arr, $char);
            if (is_array($replace_pos) && sizeof($replace_pos) === 1)
                $char = $replace_chars[$replace_pos[0]];
            else if ($char == " ")
                $char = "-";

            $ret .= $char;
        }
        return $ret;
    }

    function getMicroTime($format = false)
    {
        list($usec, $sec) = explode(" ", microtime());
        $ret = (float)$usec + (float)$sec - self::$start_time;
        if ($format) {
            $ret = 'executed in ~' . round($ret, 4) . ' sec';
        }
        return $ret;
    }

    static function getCitiesList()
    {
        $ret = array();
        $sql = 'SELECT `id`, `title` FROM `cities`';
        $res = dbConn::$mysqli->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $ret[$row['id']] = $row['title'];
            }
        } else {
            die("MySQL error: " . dbConn::$mysqli->error);
        }
        self::$cities_list = $ret;
    }

    // removes all non-digits from the string
    // i.e. convert from +7 (999) 111-22-33 to 79991112233
    static function stringToInt($str)
    {
        return preg_replace('/\D/','',$str);
    }

    // convert from 9991112233 to +7 (999) 111-22-33
    static function stringToPhone($str)
    {
        $ret = null;
        if ( strlen($str) == 10 ) {
            $ret = preg_replace('/(\d{3})(\d{3})(\d{2})(\d{2})/', '+7 ($1) $2-$3-$4', $str);
        }
        return $ret;
    }

    static function mailCheck($str)
    {
        return (bool) preg_match('/.+@.+\..+/', $str);
    }

    static function sendMail($subject, $body, $recipients = null)
    {
        $mail_headers =
            "From: no-reply@" . $_SERVER['SERVER_NAME'] . "\r\n".
            "Content-Type: text/html; charset=utf-8" . "\r\n".
            "Content-Transfer-Encoding: 8bit" . "\r\n".
            "MIME-Version: 1.0" . "\r\n" .
            "X-Mailer: PHP/" . phpversion() . "\r\n";

        // отправим админу весточку
        $mailto = is_null($recipients) ? self::$admin_mail : $recipients;
        $subj = "=?UTF-8?B?" . base64_encode($subject) . "?=";

        mail($mailto, $subj, $body, $mail_headers);
    }

    static function btn_chkbx_output($caption, $name, $checked = false, $icon = '') {
        return '
    <div class="btn_chkbx' . ($checked ? ' checked' : '') . '">
        <input type="hidden" name="' . $name . '" value="' . (int) $checked . '">
        <table>
            <tr>
                <td class="icon">' . (strlen($icon) > 0 ? '<img src="/img/'.$icon.'" alt="">' : '') . '</td>
                <td class="text">' . $caption . '</td>
            </tr>
        </table>
        <div class="circle"></div>
    </div>';
    }
    
    static function checkArrayVar($var) {
        return isset($var) && is_array($var) && sizeof($var) > 0;
    }
    
    static function checkNumericVar($var, $in_array = array()) {
        return isset($var) && is_numeric($var) && ( sizeof($in_array) > 0 ? in_array($var, $in_array) : $var > 0 );
    }
    
    static function checkStringVar($var) {
        return isset($var) && strlen(trim(htmlspecialchars($var))) > 0;
    }
}