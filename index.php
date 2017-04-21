<?php
ob_start();
session_start();
require_once 'classes/commonClass.php';
require_once 'classes/dbConn.php';
require_once 'classes/users.php';
require_once 'classes/pageLoader.php';

$commonClass = new commonClass();
$db = new dbConn();
$pageLoader = new pageLoader();
$users = new users();
$template_file = $pageLoader->setTemplate();

users::checkUserLoggedIn();
users::setUserOrderId();

require_once pageLoader::$tmp_path.'/_header.php';
require_once $template_file;
require_once pageLoader::$tmp_path.'/_footer.php';

ob_end_flush();