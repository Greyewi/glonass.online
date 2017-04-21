<?php
/**
 * Created by PhpStorm.
 * User: Dain
 * Date: 08.03.2017
 * Time: 0:15
 */

ob_start();
session_start();

$errors_output = array();
$result = array();

require_once '../classes/commonClass.php';
require_once '../classes/dbConn.php';
require_once '../classes/users.php';

$db = new dbConn();
$users = new users();

if (!users::checkUserLoggedIn()) {
    $errors_output[] = 'Вы не авторизованы';
} else if ( !users::isAdmin() ) {
    $errors_output[] = 'У вас недостаточно прав';
} else {
    $user_id = users::$user_data['id'];
    $new_edit_mode = (int) !users::$edit_mode;
    
    $sql = 'UPDATE `users` SET `edit_mode` = ' . $new_edit_mode . ' WHERE `id` = ' . $user_id;
    if ( !$db->query($sql) ) {
        $errors_output[] = 'Ошибкаа БД. Обратитесь к администратору';
    }
}

$result['output'] = ob_get_clean();
$result['errors'] = sizeof($errors_output) > 0 ? '<ul><li>' . implode('</li><li>', $errors_output) . '</li></ul>' : '';

echo json_encode($result);