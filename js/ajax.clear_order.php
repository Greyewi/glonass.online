<?php
session_start();

require_once '../classes/dbConn.php';
require_once '../classes/users.php';

$db = new dbConn();
$users = new users();

if ( users::checkUserLoggedIn() ) {
    users::dropUserOrderId();
}