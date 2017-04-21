<?php
session_start();

require_once '../classes/dbConn.php';
require_once '../classes/users.php';

$db = new dbConn();
$users = new users();

if ( users::checkUserLoggedIn() && isset($_POST['order_id']) && is_numeric($_POST['order_id']) && users::checkOrderId($_POST['order_id'])) {
    users::deleteOrder($_POST['order_id']);
}