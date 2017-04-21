<?php
session_start();
ob_start();
echo date('Y-m-d H:i:s') . '<br>----session----<br>';
var_dump($_SESSION);
echo '<br>----cookie----<br>';
var_dump($_COOKIE);
$msg = ob_get_clean();

$mail_headers =
    "From: no-reply@" . $_SERVER['SERVER_NAME'] . "\r\n".
    "Content-Type: text/html; charset=utf-8" . "\r\n".
    "Content-Transfer-Encoding: 8bit" . "\r\n".
    "MIME-Version: 1.0" . "\r\n" .
    "X-Mailer: PHP/" . phpversion() . "\r\n";

$subj = "=?UTF-8?B?" . base64_encode("3t_test data") . "?=";

mail('dain1982plus@mail.ru', $subj, $msg, $mail_headers);

echo 'отправлено';