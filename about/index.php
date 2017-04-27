<?php
ob_start();
session_start();
require_once '../classes/commonClass.php';
require_once '../classes/dbConn.php';
require_once '../classes/users.php';
require_once '../classes/pageLoader.php';

$commonClass = new commonClass();
$db = new dbConn();
$pageLoader = new pageLoader();
$users = new users();
//$template_file = $pageLoader->setTemplate();

users::checkUserLoggedIn();
?>
    <!DOCTYPE html>
    <html lang="ru">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, user-scalable=no">
        <meta name="description" CONTENT="Создание сайтов | Мыльников Дмитрий">
        <meta name="keywords" CONTENT="Мыльников Дмитрий, Мыльников, создание сайтов, landing, landing page">
        <!-- Page title
        –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <title>Мыльников Дмитрий</title>

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
        <link rel="icon" type="image/png" sizes="192x192" href="/img/ico/android-icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/img/ico/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="/img/ico/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/img/ico/favicon-16x16.png">
        <link rel="manifest" href="/img/ico/manifest.json">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="/img/ico/ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <!--/favicon-->

        <!-- Fonts
        –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <link href="https://fonts.googleapis.com/css?family=PT+Sans:400,700&amp;subset=cyrillic" rel="stylesheet">

        <!-- CSS
        –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <link rel="stylesheet" href="css/bootstrap.css">
        <link rel="stylesheet" href="css/animate.css">
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/style-media.css">

        <!-- Icons
        –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <link rel="stylesheet" href="css/font-awesome.min.css">

        <script src="js/modernizr.js"></script>
        <script src="js/jquery-2.1.3.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="http://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.2/jquery.ui.touch-punch.min.js"></script>


    </head>

    <!-- Body Start
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->

    <body data-spy="scroll" data-target=".navbar-collapse" data-offset="50">

        <!-- header bar
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->

        <header>
            <div class="container_not_fucking_bootstrap">
                <div class="top_menu">
                    <ul>
                        <li><a href="/">О проекте</a></li>
                        <?php if ( users::$user_auth ) { ?>
                        <li><a href="/main?check_order_data">Новый заказ</a></li>
                        <li><a href="/orders">Мои заказы</a></li>
                        <?php } ?>
                        <li><a href="/presentation">Презентационные материалы</a></li>
                    </ul>
                </div>
                <?php if (!users::$user_auth ) { ?>
                <div class="top_menu">
                    <ul style="float: right;">
                        <li><a href="/login">Вход</a></li>
                    </ul>
                </div>
                <?php } ?>
                <?php if ( users::$user_auth ) { ?>
                <div class="balance">
                    <div class="b_cont">
                        <a href="/balance">Баланс</a>:
                        <?=users::getUserBalance()?> р<br>
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


        <!-- section p2
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <section class="intro p2">
            <div class="container">
                <div class="col-xs-12 col-md-6 hidden-xs hidden-sm">
                    <img src="images/cash.png" alt="" style="width: 100%;">
                </div>
                <div class="col-xs-12 col-md-6 pd-l-50">
                    <div class="title">
                        Одна работа-<br>две зарплаты!
                    </div>
                    <div class="txt pd-b-40 pd-t-10">
                        Простой и надежный сервис дополнительного дохода для менеджеров автосалонов.
                    </div>
                    <a href="#start" class="btn_start">
                    Подробнее <i class="fa fa-angle-down" aria-hidden="true"></i>
                </a>
                </div>
            </div>
        </section>

        <!-- section p3
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <section class="p3" id="start">
            <div class="container flex">
                <div class="col-xs-12 col-md-6 col-md-5 hidden-xs hidden-sm">
                    <img src="images/lamp.png" alt="">
                </div>
                <div class="col-xs-12 col-md-6 col-md-7">
                    <div class="title">
                        Уникальность
                    </div>
                    <div class="txt2">
                        2zp.online это закрытое сообщество менеджеров по продаже автомобилей.<br>Опыт работы в автомобильной сфере позволяет предложить вам уникальную систему дополнительного заработка.
                    </div>
                </div>
            </div>
        </section>

        <!-- section p4
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <section class="p4">
            <div class="container flex">
                <div class="col-xs-12 col-md-6 col-md-5 hidden-xs hidden-sm">
                    <img src="images/mask.png" alt="">
                </div>
                <div class="col-xs-12 col-md-6 col-md-7">
                    <div class="title">
                        Честность
                    </div>
                    <div class="txt2">
                        Принцип работы проекта основан на доверии - вы продаете наши услуги своим клиентам и получаете за это честное вознаграждение.<br>Участие в проекте абсолютно бесплатное, мы никогда не будем просить Вас что-то оплатить, напротив мы готовы заплатить Вам за каждую сделку.
                    </div>
                </div>
            </div>
        </section>

        <!-- section p3
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <section class="p3">
            <div class="container flex">
                <div class="col-xs-12 col-md-6 col-md-5 hidden-xs hidden-sm">
                    <img src="images/key.png" alt="">
                </div>
                <div class="col-xs-12 col-md-6 col-md-7">
                    <div class="title">
                        Безопасность
                    </div>
                    <div class="txt2">
                        Мы высоко ценим оказанное нам доверие и надежно храним все персональные данные, статистику ваших продаж, информацию о выведенных вознаграждениях.
                    </div>
                </div>
            </div>
        </section>

        <!-- section p4
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <section class="p4">
            <div class="container flex">
                <div class="col-xs-12 col-md-6 col-md-5 hidden-xs hidden-sm">
                    <img src="images/money.png" alt="">
                </div>
                <div class="col-xs-12 col-md-6 col-md-7">
                    <div class="title">
                        Продавай дороже-<br>получай больше
                    </div>
                    <div class="col-xs-12 col-md-5">
                        <div class="txt2">
                            Чем дороже вы продаете тем больше получаете <br> В личном кабинете есть удобный инструмент для максимизации вашего вознаграждения<br> Просто измени положение бегунка и увеличь свое вознаграждение
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-7" style="padding-left:20px;">
                        <div class="txt3 pd-t-40">
                            Стоимость оборудования
                        </div>
                        <div class="txt4">
                            <input type="text" id="amount" class="dig" readonly>
                        </div>
                        <div id="slider-range-min"></div>
                        <div class="txt5 pd-t-10">
                            Ваше вознагрождение <span id="reward1">1000</span> рублей
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- section p3
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <section class="p3">
            <div class="container flex">
                <div class="col-xs-12 col-md-6 col-md-5 hidden-xs hidden-sm">
                    <img src="images/phone.png" alt="">
                </div>
                <div class="col-xs-12 col-md-6 col-md-7">
                    <div class="title">
                        Поддержка
                    </div>
                    <div class="txt2">
                        Вам не нужно быть экспертом что продавать наше оборудование, мы разработали готовые комплекты, учитывающие особенности автомобильных марок которые вы продаете. <br> Если возникнет сложность по работе с личным кабинетом, всегда придет на помощь Ваш персональный менеджер.
                    </div>
                </div>
            </div>
        </section>

        <!-- section sell
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <section class="sell pd">
            <div class="container">
                <div class="title gray text-center">
                    Что мы продаём
                </div>
                <div class="col-xs-12 pd-t-40">
                    <div class="col-md-3 col-xs-6 flex mn active" st="1">
                        <img src="images/put.svg" alt="">
                        <div class="txt_sell">
                            Маяк
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-6 flex mn" st="2">
                        <img src="images/mon.svg" alt="">
                        <div class="txt_sell">
                            Мониторинг<br>транспорта
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-6 flex mn" st="3">
                        <img src="images/zap.svg" alt="">
                        <div class="txt_sell">
                            Контроль<br>топлива
                        </div>
                    </div>
                    <div class="col-md-3 col-xs-6 flex mn" st="4">
                        <img src="images/dv.svg" alt="">
                        <div class="txt_sell">
                            Блокировка<br>двигателя
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- section monitor
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <div class="slide_cont_block">
            <div class="tabs__content active" st="1">
                <section class="monitor pd">
                    <div class="container flex">
                        <img src="images/put_b.svg" style="margin-right:10px;" alt="">
                        <div class="title bold gray text-center">
                            Маяк
                        </div>
                    </div>
                </section>
                <section class="content_monitor flex">
                    <div class="col-md-6 col-xs-12 text-right pd-r-40">
                        <div class="txt3 gray pn33">
                            Увеличь свое вознаграждение
                        </div>
                        <div class="txt3 bold gray pd-t-10">
                            Стоимость оборудования: <input type="text" id="amount2" class="dig2" readonly>
                        </div>
                        <div class="pd-t-10 pd-b-10 mon">
                            <div id="slider-range-min2"></div>
                        </div>
                        <div class="txt green">
                            Ваше вознагрождение <span id="reward2">1000</span> рублей
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12 p3 text-left pd pd-l-40">
                        <div class="txt3">Для кого?</div>
                        <ul class="pd-t-10">
                            <li>Для юридических и физических лиц</li>
                        </ul>
                        <div class="txt3">Для чего?</div>
                        <ul class="pd-t-10">
                            <li>Поиск угнанного авто</li>
                            <li>2 года автономной работы</li>
                            <li>Режимы: «угон!» «спящий»</li>
                            <li>Работает по всему миру</li>
                        </ul>
                        <div class="txt3">Что входит?</div>
                        <ul class="pd-t-10">
                            <li>Доставка курьером</li>
                            <li>Оплата при получении</li>
                        </ul>
                    </div>
                </section>
            </div>
            <div class="tabs__content" st="2">
                <section class="monitor pd">
                    <div class="container flex">
                        <img src="images/mon_b.svg" style="margin-right:10px;" alt="">
                        <div class="title bold gray text-center">
                            Мониторинг транспорта
                        </div>
                    </div>
                </section>
                <section class="content_monitor flex">
                    <div class="col-md-6 col-xs-12 text-right pd-r-40">
                        <div class="txt3 gray pn33">
                            Увеличь свое вознаграждение
                        </div>
                        <div class="txt3 bold gray pd-t-10">
                            Стоимость оборудования: <input type="text" id="amount3" class="dig2" readonly>
                        </div>
                        <div class="pd-t-10 pd-b-10 mon">
                            <div id="slider-range-min3"></div>
                        </div>
                        <div class="txt  green">
                            Ваше вознагрождение <span id="reward3">1300</span> рублей
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12 p3 text-left pd pd-l-40">
                        <div class="txt3">Для кого?</div>
                        <ul class="pd-t-10">
                            <li>Для юридических лиц</li>
                        </ul>
                        <div class="txt3">Для чего?</div>
                        <ul class="pd-t-10">
                            <li>Online мониторинг</li>
                            <li>Информация о стоянках</li>
                            <li>Контроль скорости</li>
                            <li>Питание от сети авто</li>
                        </ul>
                        <div class="txt3">Что входит?</div>
                        <ul class="pd-t-10">
                            <li>Выезд специалиста</li>
                            <li>Скрытая установка</li>
                            <li>Абон. плата 500 р\мес.</li>
                        </ul>
                    </div>
                </section>
            </div>
            <div class="tabs__content" st="3">
                <section class="monitor pd">
                    <div class="container flex">
                        <img src="images/zap_b.svg" style="margin-right:10px;" alt="">
                        <div class="title bold gray text-center">
                            Контроль топлива
                        </div>
                    </div>
                </section>
                <section class="content_monitor flex">
                    <div class="col-md-6 col-xs-12 text-right pd-r-40">
                        <div class="txt3 gray pn33">
                            Увеличь свое вознаграждение
                        </div>
                        <div class="txt3 bold gray pd-t-10">
                            Стоимость оборудования: <input type="text" id="amount5" class="dig2" readonly>
                        </div>
                        <div class="pd-t-10 pd-b-10 mon">
                            <div id="slider-range-min5"></div>
                        </div>
                        <div class="txt  green">
                            Ваше вознагрождение <span id="reward5">3000</span> рублей
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12 p3 text-left pd pd-l-40">
                        <div class="txt3">Для кого?</div>
                        <ul class="pd-t-10">
                            <li>Для юридических лиц</li>
                        </ul>
                        <div class="txt3">Для чего?</div>
                        <ul class="pd-t-10">
                            <li>Online мониторинг</li>
                            <li>Информация о заправках</li>
                            <li>Контроль расхода топлива</li>
                            <li>Предотвращение сливов</li>
                        </ul>
                        <div class="txt3">Что входит?</div>
                        <ul class="pd-t-10">
                            <li>Выезд специалиста</li>
                            <li>Монтаж оборудования</li>
                            <li>Абон. плата 500 р\мес.</li>
                        </ul>
                    </div>
                </section>
            </div>
            <div class="tabs__content" st="4">
                <section class="monitor pd">
                    <div class="container flex">
                        <img src="images/dv_b.svg" style="margin-right:10px;" alt="">
                        <div class="title bold gray text-center">
                            Блокировка двигателя
                        </div>
                    </div>
                </section>
                <section class="content_monitor flex">
                    <div class="col-md-6 col-xs-12 text-right pd-r-40">
                        <div class="txt3 gray pn33">
                            Увеличь свое вознаграждение
                        </div>
                        <div class="txt3 bold gray pd-t-10">
                            Стоимость оборудования: <input type="text" id="amount4" class="dig2" readonly>
                        </div>
                        <div class="pd-t-10 pd-b-10 mon">
                            <div id="slider-range-min4"></div>
                        </div>
                        <div class="txt  green">
                            Ваше вознагрождение <span id="reward4">500</span> рублей
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12 p3 text-left pd pd-l-40">
                        <div class="txt3">Для кого?</div>
                        <ul class="pd-t-10">
                            <li>Для юридических лиц</li>
                        </ul>
                        <div class="txt3">Для чего?</div>
                        <ul class="pd-t-10">
                            <li>Удаленная блокировка авто</li>
                            <li>Удаленная разблокировка авто</li>
                            <li>Контроль состояния зажигания</li>
                        </ul>
                        <div class="txt3">Что входит?</div>
                        <ul class="pd-t-10">
                            <li>Выезд специалиста</li>
                            <li>Скрытая установка</li>
                        </ul>
                    </div>
                </section>
            </div>
        </div>

        <!-- section steps
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <section class="steps pd">
            <div class="container">
                <div class="title gray text-center">
                    Этапы работы
                </div>
                <div class="flex pd-t-40">
                    <img src="images/step1.png" alt="">
                    <img src="images/step2.png" alt="">
                    <img src="images/step3.png" alt="">
                </div>
            </div>
        </section>

        <!-- section managers
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <section class="managers pd p3">
            <div class="container">
                <div class="col-md-5 top hidden-xs hidden-sm">
                    <img class="girl" src="images/top.png" alt="">
                </div>
                <div class="col-md-7 col-xs-12">
                    <div class="title pd-b-40 ">
                        С нами уже<br>310 менеджеров
                    </div>

                    <div class="scroll_tb">
                        <div class="head_tb">
                            <div class="col-xs-4">
                                Логин
                            </div>
                            <div class="col-xs-4">
                                Стоимость оборудования
                            </div>
                            <div class="col-xs-4">
                                Выведенное вознаграждение
                            </div>
                        </div>
                        <div class="body_tb">
                            <?php
							$handle = fopen("csv/import.csv", "r");
							
							if ($handle) {
								while (($buffer = fgets($handle)) !== false) {
									//разбиваем строку на значения и помещаем в массив
									$buffer = str_replace("'", "", $buffer);
									$buffer = str_replace('"', '', $buffer);
									$data = explode(',', $buffer);
									//помещаем массив данных в переменные
									list($number, $login, $price, $cash) = $data;
									//не выводим первую строку
									if ($number == "№") continue;
									else {
										?>
                                <div class="body_link">
                                    <div class="col-xs-4">
                                        <?=$number;?>.
                                            <?=$login;?>
                                    </div>
                                    <div class="col-xs-4">
                                        <?=number_format($price, 0, '.', ' ');?>
                                    </div>
                                    <div class="col-xs-4">
                                        <?=number_format($cash, 0, '.', ' ');?>
                                    </div>
                                </div>
                                <?php
									}
								}
							 
								fclose($handle);
							}	
						?>
                        </div>
                    </div>


                </div>
            </div>
        </section>

        <!-- Footer
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <footer class="footer">
            <div class="container">

                <div class="col-md-4 pd-r-40">
                    <div class="small">
                        адрес
                    </div>
                    <div class="txt">
                        Москва, Орджоникидзе 11 стр 11 офис 205
                    </div>
                </div>
                <div class="col-md-3 pd-r-40">
                    <div class="small">
                        телефон
                    </div>
                    <div class="txt">
                        8-800-250-55-27
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="small">
                        почта
                    </div>
                    <div class="txt">
                        2zp@gmail.com
                    </div>
                </div>
                <div class="col-md-3 text-right">
                    <?php if (!users::$user_auth ) { ?>
                    <div class="top_menu">
                        <a class="footer-login-link" href="/login">Вход</a>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </footer>

        <!-- post_footer
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->
        <div class="post_footer">
            <div class="container text-right">
                <img src="images/flogo.png" alt="">
            </div>
        </div>

        <!--  Javascripts
    –––––––––––––––––––––––––––––––––––––––––––––––––– -->

        <script src="js/bootstrap.min.js"></script>
        <script src="js/jquery.parallax.js"></script>
        <script src="js/particles.min.js"></script>
        <script src="js/wow.min.js"></script>
        <script src="js/function.js"></script>
        <script>
            function addThousandSeparator(nStr) {
                nStr += '';
                var x = nStr.split('.');
                var x1 = x[0];
                var x2 = x.length > 1 ? '.' + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                    x1 = x1.replace(rgx, '$1' + ' ' + '$2');
                }
                return x1 + x2;
            }

            $("#slider-range-min").slider({
                range: "min",
                value: 5000,
                min: 4000,
                max: 15000,
                step: 100,
                slide: function(event, ui) {
                    $("#amount").val(ui.value);
                    if (price_result <= 5000) {
                        price_result = (slider_percentage / 100 * price_delta + price_min).toFixed();
                        price_result = price_result - price_result % 100;
                        user_reward = price_result - 4000;
                    } else if (price_result > 5000) {
                        price_result = (slider_percentage / 100 * price_delta + price_min).toFixed();
                        price_result = price_result - price_result % 100;
                        user_reward = ((price_result - 5000) / 2) + 1000;
                    }
                    $("#reward1").val(user_reward);
                }
            });
            // $('#slider-range-min').each(function(){
            //     var slider_percentage = 0,
            //         price_result = 0,
            //         user_reward = 0,
            //         cur = $(this),
            //         slider_width = 230,
            //         price_min = 4000,
            //         price_max = 10000,
            //         price_delta = price_max - price_min;
            //         parent = cur.parentsUntil('.container').last();

            //     cur.draggable({
            //         axis: 'x',
            //         containment: 'parent',
            //         drag: function(e,ui){
            //             slider_percentage = ui.position.left * 100 / slider_width;
            //             //Маяк


            //             cur.find('.wrapper .fill').width(slider_percentage+'%');
            //             cur.parent().find('input.item_price_result').val(price_result);
            //             cur.parent().find('span.item_price_result').text(addThousandSeparator(price_result));
            //             cur.parent().find('input.user_reward').val(user_reward);
            //             $('#reward1').text(addThousandSeparator(user_reward));

            //         }
            //     });
            // });

        </script>
        <!-- BEGIN JIVOSITE CODE {literal} -->
        <script type='text/javascript'>
            (function() {
                var widget_id = 'sLKpxfBeF0';
                var d = document;
                var w = window;

                function l() {
                    var s = document.createElement('script');
                    s.type = 'text/javascript';
                    s.async = true;
                    s.src = '//code.jivosite.com/script/widget/' + widget_id;
                    var ss = document.getElementsByTagName('script')[0];
                    ss.parentNode.insertBefore(s, ss);
                }
                if (d.readyState == 'complete') {
                    l();
                } else {
                    if (w.attachEvent) {
                        w.attachEvent('onload', l);
                    } else {
                        w.addEventListener('load', l, false);
                    }
                }
            })();

        </script>
        <!-- {/literal} END JIVOSITE CODE -->
    </body>

    </html>
