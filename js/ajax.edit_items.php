<?php
ob_start();
session_start();

$errors_output = array();

require_once '../classes/commonClass.php';
require_once '../classes/dbConn.php';
require_once '../classes/users.php';

$allowed_fields = array(
    'type',
    'title',
    'price_min',
    'price_max',
    'text',
    'reward_percent'
);

$db = new dbConn();
$users = new users();

if (!users::checkUserLoggedIn()) {
    $errors_output[] = 'Вы не авторизованы';
} else if (!commonClass::checkArrayVar(@$_POST['edit_item'])) {
    $errors_output[] = 'Нет данных оборудования';
} else {
    foreach ($_POST['edit_item'] as $item_type => $item_options) {

        $sql = array();

        foreach ($item_options as $f => $v) {
            if ( in_array($f, $allowed_fields) ) {
                if ( in_array($f, array('price_min','price_max')) ) {
                    $sql[] = '`'.$f.'` = ' . (int) $v;
                } else {
                    $sql[] = '`'.$f.'` = "' . dbConn::$mysqli->escape_string($v) . '"';
                }
            }
        }

        if (sizeof($sql) > 0) {
            $sql = 'UPDATE `items` SET ' . implode(', ', $sql) . ' WHERE `type` = ' . $item_type;
            $db->query($sql);
        }
    }
}

echo sizeof($errors_output) > 0 ? '<ul><li>'.implode('</li><li>', $errors_output).'</li></ul>' : '';

$result = ob_get_clean();
echo json_encode($result);