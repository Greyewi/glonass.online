<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?= pageLoader::$current_template['title']; ?></title>
<!--    <meta name="viewport" content="width=980, initial-scale=1, maximum-scale=1, user-scalable=yes">-->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    
    <!--favicon-->
    <link rel="apple-touch-icon" sizes="57x57" href="/img/ico/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/img/ico/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/img/ico/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/img/ico/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/img/ico/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/img/ico/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/img/ico/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/img/ico/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/ico/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/img/ico/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/ico/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/img/ico/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/ico/favicon-16x16.png">
    <link rel="manifest" href="/img/ico/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/img/ico/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <!--/favicon-->
    
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/font-awesome.min.css">
</head>
<body>
<header>
    <div class="container common">
        <div class="top_menu">
            <ul>
                <li><a href="/about/">О проекте</a></li>
                <?php if ( users::$user_auth ) { ?>
                <li><a href="/main/?check_order_data">Новый заказ</a></li>
                <li><a href="/orders/">Мои заказы</a></li>
                <?php } ?>
                <li><a href="/presentation/">Презентационные материалы</a></li>
            </ul>
        </div>
        <?php if ( users::$user_auth ) { ?>
        <div class="balance">
            <div class="b_cont">
                <a href="/balance/">Баланс</a>: <?=users::getUserBalance()?> р<br>
                <span>Возможный баланс: <?=users::getUserBalanceFuture()?> р</span>
            </div>
        </div>
        <div class="user_name">
            <div class="un_cont">
                <a class="un_a" href="#">
                    Здравствуйте,<br>
                    <?=users::$user_data['first_name'] . ' ' . users::$user_data['last_name']?>
                </a>
                <div class="submenu">
                    <a href="/settings">Профиль аккаунта</a>
                    <a href="/logout">Выйти</a>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</header>
<?php if ( users::isAdmin() ) {?>
<section class="admin_menu">
    <div class="container">
        <ul>
            <li><a href="/admin_orders/">Заявки</a></li>
            <li><a href="/admin_users/">Менеджеры</a></li>
            <li><a href="/admin_rewards/">Выплаты</a></li>
        </ul>
        <div id="editor_mode<?=users::$edit_mode?'" class="active':''?>">режим редактирования<div><span></span></div></div>
    </div>
</section>
<?php } ?>
<section class="main">
    <div class="container">