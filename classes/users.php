<?php
/**
 * Created by PhpStorm.
 * User: Dain
 * Date: 25.11.2016
 * Time: 18:17
 */
define('ORDER_STATUS_CREATED',          0); // новый заказ; (auto)
define('ORDER_STATUS_SAVED',            1); // заказ сохранен (временный заказ) (auto)
define('ORDER_STATUS_FORMED',           2); // заказ сформирован (auto)
define('ORDER_STATUS_RECEIVED',         3); // админ просмотрел заказ; только для сформированных заказов (auto)
define('ORDER_STATUS_AWAITS_PAYMENT',   4); // админ согласовал заказ с клиентом, ожидается оплата от клиента (manual)
define('ORDER_STATUS_WORKER_SET',       5); // админ назначил заказу исполнителя (manual)
define('ORDER_STATUS_PAYED',            6); // заказ полностью исполнен (manual)
define('ORDER_STATUS_CANCELED',         7); // заказ отменен (manual)
define('ORDER_STATUS_DONE',             8); // "выполнено" (manual)

define('USER_REWARD_AWAITS',    0); // пользователь запросил вывод средств (auto)
define('USER_REWARD_ACCEPTED',  1); // админ одобряе (manual)
define('USER_REWARD_PAYED',     2); // админ отправил бабки (manual)

define('ORDER_STEP_1', 1);
define('ORDER_STEP_2', 2);
define('ORDER_STEP_3', 3);
define('ORDER_STEP_4', 4);

define('ITEM_TYPE_1', 1);
define('ITEM_TYPE_2', 2);
define('ITEM_TYPE_3', 3);
define('ITEM_TYPE_4', 4);

define('USER_STATUS_ADMIN', 1);

class users
{
    public static $user_auth = false;
    public static $user_data = array();
    public static $order_id = 0;
    public static $items = array();
    public $reg_u = array();
    public $restore_mail = '';
    public $login_u = array();
    public $form_errors = array();
    static $success_result = '';

    static $edit_mode = false;

    function __construct(){}

    function registerFormParams()
    {
        commonClass::getCitiesList();

        $this->reg_u = array(
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'phone_text' => '',
            'phone_prefix' => 0,
            'phone' => 0,
            'city' => 0,
            'date' => 0,
            'passw' => '',
            'passw_conf' => ''
        );

        $required = array(
            'email',
            'first_name',
            'last_name',
            'phone_text',
            'city',
            'passw',
            'passw_conf'
        );

        $fields = array(
            'email' => 'E-mail',
            'first_name' => 'Фамилия',
            'last_name' => 'Имя',
            'phone_text' => 'Телефон',
            'city' => 'Город',
            'passw' => 'Пароль',
            'passw_conf' => 'Подтверждение пароля'
        );

        // попытка зарегистрироваться
        if (isset($_POST['reg_u']) && sizeof($_POST['reg_u']) > 0) {
            $p = $_POST['reg_u'];
            foreach ($this->reg_u as $f => &$v) {
                if (array_key_exists($f, $p)) {
                    if ($f == 'phone_text') {
                        $v = htmlspecialchars(trim($p[$f]));
                        $tmp = commonClass::stringToInt($v);
                        if (strlen($tmp) == 11 && in_array($tmp[0], array(7, 8))) {
                            $this->reg_u['phone_prefix'] = (int) substr($tmp, 1, 3);
                            $this->reg_u['phone'] = commonClass::stringToPhone(substr($tmp, 1));
                        } else {
                            $this->form_errors[] = 'Вы неправильно указали <b>Телефон</b>.<br><i>Пожалуйста, укажите телефон в формате +7 (ххх) ххх-хх-хх</i>';
                        }
                    } else if ($f == 'email') {
                        $v = htmlspecialchars(trim($p[$f]));
                        if (!commonClass::mailCheck($v)) {
                            $this->form_errors[] = 'Указан не правильный <b>E-mail</b>';
                        }
                    } else if ($f == 'city') {
                        $v = (int) $p[$f];
                        if (!array_key_exists($v, commonClass::$cities_list)) {
                            $this->form_errors[] = 'Попробуйте еще раз выбрать <b>Город</b>';
                        }
                    } else if (is_numeric($v)) {
                        if (is_numeric($p[$f])) $v = (int)$p[$f];
                        else $this->form_errors[] = 'Вы неправильно указали <b>' . $fields[$f] . '</b>';
                    } else {
                        if (strlen(trim($p[$f])) > 0) {
                            $v = htmlspecialchars(trim($p[$f]));
                        } else if (in_array($f, $required)) $this->form_errors[] = 'Вы не заполнили поле <b>' . $fields[$f] . '</b>';
                    }
                } else if (in_array($f, $required)) {
                    $this->form_errors[] = 'Вы не заполнили поле <b>' . $fields[$f] . '</b>';
                }
            }

            if (strlen($this->reg_u['passw']) > 0 && $this->reg_u['passw'] != $this->reg_u['passw_conf']) {
                $this->form_errors[] = 'Пароли не совпадают';
            }

            if (sizeof($this->form_errors) == 0) {
                $this->registerUser();
            }
        }
    }

    function restoreFormParams()
    {
        if ( commonClass::checkArrayVar(@$_POST['restore_u']) ) {
            $post = $_POST['restore_u'];
            
            if (!isset($post['email'])) {
                $this->form_errors[] = 'ошибка чтения email аккаунта';
            } else if ( strlen(trim(htmlspecialchars($post['email']))) == 0 ) {
                $this->form_errors[] = 'вы не указали email аккаунта';
            } else if ( !commonClass::mailCheck($post['email']) ) {
                $this->form_errors[] = 'email указан не верно';
            } else if ( !$this->checkUserMail($post['email']) ) {
                $this->form_errors[] = 'аккаунт с таким email\'ом не найден';
            }

            $this->restore_mail = $post['email'];
        }
    }

    function checkUserMail($mail)
    {
        $ret = false;
        $sql = 'SELECT `id` FROM `users` WHERE `email` LIKE "'.dbConn::$mysqli->escape_string(htmlspecialchars($mail)).'"';
        $res = dbConn::$mysqli->query($sql);
        if ($res && $res->num_rows === 1) $ret = true;
        return $ret;
    }

    function registerUser()
    {
        $d = $this->reg_u;
        $d['email'] = dbConn::$mysqli->escape_string($d['email']);
        $sql = 'INSERT INTO `users` (
            `email`, 
            `first_name`, 
            `last_name`, 
            `phone_prefix`, 
            `phone`, 
            `city`, 
            `passw`, 
            `date`
        ) VALUES (
            "' . dbConn::$mysqli->escape_string($d['email']) . '",
            "' . dbConn::$mysqli->escape_string($d['first_name']) . '",
            "' . dbConn::$mysqli->escape_string($d['last_name']) . '",
            ' . $d['phone_prefix'] . ',
            "' . dbConn::$mysqli->escape_string($d['phone']) . '",
            ' . $d['city'] . ',
            "' . $this->createHash($d['passw']) . '",
            ' . time() . '
        )';

        if (dbConn::$mysqli->query($sql)) {
            $user_id = dbConn::$mysqli->insert_id;
//            $this->setUserHash($user_id, $d['email']);
            pageLoader::redirectTo('admin_users?user_created');
            // todo: set user hash only after successful email confirmation (follow by email link with user id hash)
//            pageLoader::redirectTo('usercheck');
        } else if ( dbConn::$mysqli->errno == 1062 ) { // ибо "Duplicate entry" может быть только по email'у
            $this->form_errors[] = 'Пользователь с таким E-mail\'ом уже зарегистрирован. <a href="/restore">Забыли пароль?</a>';
        } else {
            $this->form_errors[] = 'DB error: ' . dbConn::$mysqli->error . '; err_code - ' . dbConn::$mysqli->errno;
        }
    }

    function createHash($str)
    {
        return md5($str);
    }

    function loginFormParams()
    {
        $this->login_u = array(
            'email' => '',
            'passw' => ''
        );

        $fields = array(
            'email' => 'E-mail',
            'passw' => 'Пароль'
        );
        
        if (isset($_POST['login_u']) && sizeof($_POST['login_u']) > 0) {
            $p = $_POST['login_u'];
            foreach ($this->login_u as $f => &$v) {
                if (array_key_exists($f, $p) && strlen(trim($p[$f])) > 0) {
                    $v = htmlspecialchars(trim($p[$f]));
                    if ( $f == 'email' && !commonClass::mailCheck($v) ) $this->form_errors[] = 'Указан неправильный <b>E-mail</b>';
                } else {
                    $this->form_errors[] = 'Вы не заполнили поле <b>' . $fields[$f] . '</b>';
                }
            }

            if (sizeof($this->form_errors) == 0) {
                $sql = 'SELECT `id` 
                FROM `users` 
                WHERE 
                    `email` LIKE "' . dbConn::$mysqli->escape_string($this->login_u['email']) . '" AND 
                    `passw` LIKE "' . $this->createHash($this->login_u['passw']) . '"';
                $res = dbConn::$mysqli->query($sql);
                if ($res && $res->num_rows == 1) {
                    list($user_id) = $res->fetch_row();
                    dbConn::$mysqli->query('UPDATE `users` SET `last_visit` = ' . time() . ' WHERE `id` = ' . $user_id);
                    $this->setUserHash($user_id, $this->login_u['email']);
                    pageLoader::redirectTo('main');
                } else {
                    $this->form_errors[] = 'Неправильный логин или пароль';
                }
            }
        }
    }

    function formErrorsOutput()
    {
        $ret = '';
        if ( sizeof($this->form_errors) > 0 ) $ret = '<div class="errors"><p>' . implode('</p><p>', $this->form_errors) . '</p></div>';
        return $ret;
    }

    static function getUserHash()
    {
        $user_id_hash = null;
        if (isset(self::$user_data['uid']) && strlen(self::$user_data['uid']) > 0) {
            $user_id_hash = self::$user_data['uid'];
        } else if (isset($_SESSION['uid']) && strlen($_SESSION['uid']) > 0) {
            $user_id_hash = $_SESSION['uid'];
        } else if (isset($_COOKIE['uid']) && strlen($_COOKIE['uid']) > 0) {
            $user_id_hash = $_COOKIE['uid'];
        }
        return $user_id_hash;
    }

    static function checkUserHash()
    {
        $ret = false;
        $user_hash = self::getUserHash();
        if (!is_null($user_hash) && strlen($user_hash) == 32) {
            $sql = 'SELECT `id` FROM `users` WHERE MD5(CONCAT(`id`, `email`)) LIKE "' . dbConn::$mysqli->escape_string($user_hash) . '"';
            $res = dbConn::$mysqli->query($sql);
            if ($res && $res->num_rows == 1) {
                list(self::$user_data['id']) = $res->fetch_row();
                $ret = true;
                self::$user_auth = true;
                self::getUserDataById(self::$user_data['id']);
            }
        }

        return $ret;
    }

    function setUserHash($user_id, $user_mail)
    {
        $hash = $this->createHash($user_id . $user_mail);
        self::$user_data['uid'] = $hash;
        $_SESSION['uid'] = $hash;
        if (isset($_COOKIE['uid'])) {
            $_COOKIE['uid'] = $hash;
        } else {
            setcookie('uid', $hash, strtotime('+30 days'), '/');
        }
    }

    static function dropUserHash()
    {
        if (isset($_COOKIE['uid'])) setcookie('uid',null,1);
        if (isset($_SESSION['uid'])) unset($_SESSION['uid']);
    }

    static function getUserDataById($user_id, $output = false)
    {
        $ret = array();
        $sql = 'SELECT * FROM `users` WHERE `id` = ' . $user_id;
        $res = dbConn::$mysqli->query($sql);
        if ($res && $res->num_rows == 1) {
            while ($row = $res->fetch_assoc()) {
                if ( $output ) $ret = $row;
                else self::$user_data = $row;
            }
        }
        return $ret;
    }

    static function checkUserLoggedIn()
    {
        return self::checkUserHash();
    }

    static function getUserBalance($user_id = null)
    {
        $ret = 0;

        $user_id = (int) (is_null($user_id) ? self::$user_data['id'] : $user_id);

        // вся сумма вознаграждений пользователя за все оплаченные заказы
        $sql = '
        SELECT SUM(`reward` * `count`) AS `total_reward` 
        FROM `orders_items` 
        WHERE `order_id` IN (
            SELECT `id` 
            FROM `orders` 
            WHERE 
                `user_id` = ' . $user_id . ' AND 
                `status` = ' . ORDER_STATUS_DONE /*ORDER_STATUS_PAYED*/ . '
            )';
        $res = dbConn::$mysqli->query($sql);
        if ( $res && $res->num_rows > 0 ) {
            list($ret) = $res->fetch_row();
            if ( is_null($ret) ) $ret = 0;
        }

        // вычислим, сколько пользователь может получить на данный момент:
        // узнаем, сколько уже запрашивал пользователь, и отнимем эту сумму от общей
        if ($ret > 0) {
            $payed = 0;
            $sql = 'SELECT SUM(`amount`) as `payed` FROM `users_rewards` WHERE `user_id` = ' . $user_id;
            $res = dbConn::$mysqli->query($sql);
            if ( $res && $res->num_rows > 0 ) {
                list($payed) = $res->fetch_row();
            }
            $ret = $ret - $payed;
        }
        
        return $ret;
    }

    static function getUserBalanceFuture($user_id = null)
    {
        $ret = 0;

        $user_id = (int) (is_null($user_id) ? self::$user_data['id'] : $user_id);

        $order_statuses = array(
            ORDER_STATUS_CREATED,
            ORDER_STATUS_SAVED,
            ORDER_STATUS_DONE,
            ORDER_STATUS_CANCELED
        );

        // общая сумма вознаграждений пользователя за заказы с любыми статусами, кроме перечисленных выше
        $sql = '
        SELECT SUM(`reward` * `count`) AS `total_reward` 
        FROM `orders_items` 
        WHERE `order_id` IN (
            SELECT `id` 
            FROM `orders` 
            WHERE 
                `user_id` = ' . $user_id . ' AND 
                `status` NOT IN (' . implode(', ', $order_statuses) . ')
            )';
        $res = dbConn::$mysqli->query($sql);
        if ( $res && $res->num_rows > 0 ) {
            list($ret) = $res->fetch_row();
            if ( is_null($ret) ) $ret = 0;
        }
        
        return $ret;
    }

    static function getOrderStatus($order_id)
    {
        $ret = null;
        $sql = 'SELECT `status` FROM `orders` WHERE `id` = ' . $order_id;
        $res = dbConn::$mysqli->query($sql);
        if ( $res && $res->num_rows == 1 ) list($ret) = $res->fetch_row();
        if ( !is_numeric($ret) ) $ret = null;
        return $ret;
    }

    static function updateOrderStatus($order_id, $new_status)
    {
        $sql = 'UPDATE `orders` SET `status` = ' . $new_status . ' WHERE `id` = ' . $order_id;
        dbConn::$mysqli->query($sql);
        if ( dbConn::$mysqli->affected_rows > 0 ) {
            $sql = 'INSERT INTO `orders_statuses_history` (`order_id`, `status`, `date`) VALUES (' . $order_id . ', ' . $new_status . ', ' . time() . ')';
            
            dbConn::$mysqli->query($sql);


            $subject = '';

            switch ($new_status) {
                case ORDER_STATUS_FORMED:
                    $subject = 'Заявка №'.$order_id.' оформлена';
                    break;
                case ORDER_STATUS_AWAITS_PAYMENT:
                    $subject = 'Статус заявки №'.$order_id.' - ждем оплаты';
                    break;
                case ORDER_STATUS_WORKER_SET:
                    $subject = 'Заявке №'.$order_id.' назначен исполнитель';
                    break;
                case ORDER_STATUS_PAYED:
                    $subject = 'Получена оплата по завке №'.$order_id;
                    break;
                case ORDER_STATUS_CANCELED:
                    $subject = 'Заявка №'.$order_id.' отменена';
                    break;
                case ORDER_STATUS_DONE:
                    $subject = 'Заявка №'.$order_id.' выполнена';
                    break;
            }

            if ( strlen($subject) > 0 ) {
                $msg =
                    date('Y-m-d H:i:s') . '<br><br>
                ' . $subject . '<br><br>
                <a href="http://'.$_SERVER['SERVER_NAME'].'/orders" target="_blank">В личный кабинет</a>';

                $order_data = self::getOrderDataForMail($order_id);

                if ( strlen($order_data) > 0 ) {
                    $msg .= '<hr>
                    <strong>Данные заявки:</strong><br>' . $order_data;
                }

                $order_owner = self::getUserDataByOrderId($order_id);

                if ( sizeof($order_owner) > 0 && isset($order_owner['email']) ) {
                    if ( sizeof(commonClass::$cities_list) == 0 ) commonClass::getCitiesList();

                    $msg .= '<hr>
                        <strong>Данные менеджера:</strong><br>
                        Имя: ' . $order_owner['first_name'] . ' ' . $order_owner['last_name'] . '<br>
                        Email: ' . $order_owner['email'] . '<br>
                        Телефон: ' . $order_owner['phone'] . '<br>
                        Город: ' . commonClass::$cities_list[$order_owner['city']];

                    $recipients = $order_owner['email'] . ', ' . commonClass::$admin_mail;
                } else {
                    $recipients = null;
                }
                commonClass::sendMail($subject, $msg, $recipients);
            }

            if ( self::$order_id == $order_id && in_array($new_status, array(ORDER_STATUS_FORMED)) ) {
                self::saveUserOrderId(0);
            }
        }
    }

    static function getUserDataByOrderId($order_id)
    {
        $ret = array();
        $sql = 'SELECT * FROM `users` WHERE `id` = (SELECT `user_id` FROM `orders` WHERE `id` = '. $order_id .')';
        $res = dbConn::$mysqli->query($sql);
        if ( $res && $res->num_rows == 1 ) {
            $ret = $res->fetch_assoc();
        }
        return $ret;
    }

    static function setUserOrderId()
    {
        $order_id = 0;
        if (isset($_SESSION['order_id']) && is_numeric($_SESSION['order_id'])) {
            $order_id = (int) $_SESSION['order_id'];
        } else if (isset($_COOKIE['order_id']) && is_numeric($_COOKIE['order_id'])) {
            $order_id = (int) $_COOKIE['order_id'];
        }

        if ( $order_id > 0 && !self::checkOrderId($order_id) ) $order_id = 0;
        
        self::$order_id = $order_id;
    }
    
    static function checkOrderId($order_id)
    {
        $ret = false;
        $user_id = isset(self::$user_data['id']) && is_numeric(self::$user_data['id']) ? (int) self::$user_data['id'] : null;

        if ( !is_null($user_id) ) {
            $sql = 'SELECT `id` FROM `orders` WHERE `id` = ' . (int) $order_id . ' AND `user_id` = ' . $user_id;
            $res = dbConn::$mysqli->query($sql);
            $ret = $res && $res->num_rows == 1;
        }
        return $ret;
    }

    function getUserOrderId($create_new = false, $status = ORDER_STATUS_CREATED)
    {
        $order_id = self::$order_id;
        if ( !in_array($status, array(ORDER_STATUS_CREATED, ORDER_STATUS_SAVED)) ) $status = ORDER_STATUS_CREATED;
        if ( $order_id == 0 ) {
            // получим данные активного заказа.
            $sql = 'SELECT `id` FROM `orders` WHERE `status` = ' . $status . ' AND `user_id` = ' . self::$user_data['id'];
            $res = dbConn::$mysqli->query($sql);
            if ($res && $res->num_rows > 0) {
                $d = $res->fetch_assoc();
                $order_id = (int) $d['id'];
                $this->saveUserOrderId($order_id);
            } else if ( $create_new ) {
                //Если такого нет, создадим новый, пустой (если просили)
                $sql = 'INSERT INTO `orders` (`user_id`,`date`,`status`) VALUES (' . self::$user_data['id'] . ', ' . time() . ', ' . ORDER_STATUS_CREATED . ')';
                dbConn::$mysqli->query($sql);
                $order_id = dbConn::$mysqli->insert_id;
                $this->saveUserOrderId($order_id);
            } else {
                // либо редиректим на главную
                pageLoader::redirectTo('');
            }
        }
        return $order_id;
    }

    static function saveUserOrderId($order_id)
    {
        if (!isset($_SESSION['order_id']) || $_SESSION['order_id'] != $order_id) $_SESSION['order_id'] = $order_id;

        setcookie('order_id', $order_id, strtotime('+30 days'), '/');
        /*
        if (!isset($_COOKIE['order_id'])) {
            setcookie('order_id', $order_id, strtotime('+30 days'), '/');
        } else if ($_COOKIE['order_id'] != $order_id) {
            $_COOKIE['order_id'] = $order_id;
        }
        */
    }

    static function dropUserOrderId()
    {
        if (isset($_COOKIE['order_id'])) setcookie('order_id',null,1,'/');
        if (isset($_SESSION['order_id'])) unset($_SESSION['order_id']);
        if (isset($_COOKIE['order'])) setcookie('order',null,1,'/');
        if (isset($_SESSION['order'])) unset($_SESSION['order']);
    }

    static function deleteOrder($order_id)
    {
        dbConn::$mysqli->query('DELETE FROM `orders_statuses_history` WHERE `order_id` = ' . $order_id);
        dbConn::$mysqli->query('DELETE FROM `orders_items` WHERE `order_id` = ' . $order_id);
        dbConn::$mysqli->query('DELETE FROM `orders_cars` WHERE `order_id` = ' . $order_id);
        dbConn::$mysqli->query('DELETE FROM `orders` WHERE `id` = ' . $order_id);
    }

    // вытаскивает и сохраняет пары "тип пакета" => "id пакета"
    static function getItemsIDs()
    {
        if ( sizeof(self::$items) == 0 ) {
            $sql = 'SELECT `id`, `type` FROM `items`';
            $res = dbConn::$mysqli->query($sql);
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    self::$items[$row['type']] = $row['id'];
                }
            }
        }
    }

    // возвращает id пакета по его типу
    static function getItemIDbyType($type)
    {
        $ret = null;
        if ( sizeof(self::$items) == 0 ) self::getItemsIDs();
        if ( array_key_exists($type, self::$items) ) $ret = self::$items[$type];
        return $ret;
    }

    // возвращает массив соответствия id пакетов, имеющихся в указанном заказе, и id строк.
    // используется для обновления данных пакета (стоимость, вознаграждение, кол-во авто) при обновлении заказа
    static function getOrderItems($order_id)
    {
        $ret = array();
        $sql = 'SELECT `id`, `item_id` FROM `orders_items` WHERE `order_id` = ' . $order_id;
        $res = dbConn::$mysqli->query($sql);
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $ret[$row['item_id']] = $row['id'];
            }
        }
        return $ret;
    }

    static function getUserOrdersCount($user_id)
    {
        $ret = 0;
        $sql = 'SELECT COUNT(*) FROM `orders` WHERE `status` NOT IN ('.ORDER_STATUS_CREATED.', '.ORDER_STATUS_SAVED.') AND `user_id` = ' . (int) $user_id;
        $res = dbConn::$mysqli->query($sql);
        if ( $res && $res->num_rows == 1 ) {
            list($ret) = $res->fetch_row();
        }
        return (int) $ret;
    }
    
    static function isAdmin() 
    {
        $ret = isset(self::$user_data['status']) && self::$user_data['status'] == USER_STATUS_ADMIN;
        if ( $ret ) {
            self::$edit_mode = (bool) self::$user_data['edit_mode'];
        }
        return $ret;
    }

    static function getUserData($user_id)
    {
        $ret = array();
        $sql = 'SELECT * FROM `users` WHERE `id` = ' . (int) $user_id;

        $res = dbConn::$mysqli->query($sql);

        if ( $res && $res->num_rows > 0 ) {
            while ( $row = $res->fetch_assoc() ) {
                $ret = array(
                    'id'                => $row['id'],
                    'email'             => $row['email'],
                    'first_name'        => $row['first_name'],
                    'last_name'         => $row['last_name'],
                    'phone'             => $row['phone'],
                    'city'              => $row['city'],
                    'rewards'           => number_format(self::getUserBalance($row['id']), 0, '', ' '),
                    'rewards_future'    => number_format(self::getUserBalanceFuture($row['id']), 0, '', ' '),
                    'orders_count'      => self::getUserOrdersCount($row['id']),
                    'status'            => $row['status']
                );
            }
        }
        return $ret;
    }

    // это, надеюсь, последний метод, написанный перед сдачей проекта. Я уже чертовски утомился и приуныл...
    static function getOrderDataForMail($order_id)
    {
        $ret = '';


        $order_statuses = array(
            ORDER_STATUS_FORMED => 'оформлен',
            ORDER_STATUS_AWAITS_PAYMENT => 'ждем оплаты',
            ORDER_STATUS_PAYED => 'оплачено',
            ORDER_STATUS_WORKER_SET => 'установка',
            ORDER_STATUS_DONE => 'выполнено',
            ORDER_STATUS_CANCELED => 'отменен',
        );


        $client_fields = array(
            'org' => array(
                'type' => 'Форма организации',
                'name' => 'Название',
                'inn' => 'ИНН',
                'kpp' => 'КПП',
                'bik' => 'БИК',
                'ks' => 'К/С',
                'rs' => 'Р/с',
                'bank' => 'Банк',
                'address' => 'Адрес',
                'leader' => 'Руководитель',
                'rank' => 'Должность',
                'act_upon' => 'Действует на основании',
                'contact' => 'Контактное лицо (ФИО)',
                'phone' => 'Тел контактного лица',
                'email' => 'E-mail для отправки договора и счета',
                'comment' => 'Комментарий к заказу'
            ),
            'man' => array(
                'name' => 'ФИО',
                'address' => 'Адрес',
                'phone' => 'Телефон',
                'email' => 'E-mail для отправки договора и счета',
                'comment' => 'Комментарий к заказу'
            )
        );

        $org_types = array(
            1 => 'ИП',
            2 => 'ООО',
            3 => 'ОАО',
            4 => 'ЗАО'
        );

        $client_fields_captions = array(
            'org_type' => 'Форма организации',
            'org_name' => 'Название',
            'org_inn' => 'ИНН',
            'org_kpp' => 'КПП',
            'org_bik' => 'БИК',
            'org_ks' => 'К/С',
            'org_rs' => 'Р/с',
            'org_bank' => 'Банк',
            'org_address' => 'Адрес',
            'org_leader' => 'Руководитель',
            'org_rank' => 'Должность',
            'org_act_upon' => 'Действует на основании',
            'org_contact' => 'Контактное лицо (ФИО)',
            'org_phone' => 'Тел контактного лица',
            'org_email' => 'E-mail для отправки договора и счета',
            'org_comment' => 'Комментарий к заказу',
            'man_name' => 'ФИО',
            'man_address' => 'Адрес',
            'man_phone' => 'Телефон',
            'man_email' => 'E-mail для отправки договора и счета',
            'man_comment' => 'Комментарий к заказу'
        );

        $sql = 'SELECT
            `o`.`id`,
            `o`.`status`,
            `o`.`date`,
            `osh`.`date` AS `status_date`,
            `o`.`client_type`,
            `o`.`client_info`,
            `o`.`worker_info`,
            `o`.`invoice_info`
        FROM `orders` AS `o`
        LEFT JOIN `orders_statuses_history` AS `osh`
            ON `osh`.`order_id` = `o`.`id` AND `osh`.`status` = `o`.`status`
        WHERE `o`.`id` = ' . $order_id;

        $res = dbConn::$mysqli->query($sql);
        if ($res && $res->num_rows > 0) {
            $ret .=  '
<table border="1">
    <tbody>
        <tr>
            <th>№ заказа</th>
            <th>Клиент</th>
            <th>Счет</th>
            <th>Оборудование</th>
            <th>Установка</th>
            <th>Ваше вознаграждение</th>
            <th>Статус</th>
        </tr>';

            $orders = array();

            while ($row = $res->fetch_assoc()) {
                $id = (int)$row['id'];
                $orders[$id]['date'] = $row['date'];
                $orders[$id]['status'] = $row['status'];
                $orders[$id]['client_type'] = $row['client_type'];
                $orders[$id]['client_info'] = $row['client_info'];
                $orders[$id]['worker_info'] = $row['worker_info'];
                $orders[$id]['status_date'] = $row['status_date'];
                $orders[$id]['invoice_info'] = $row['invoice_info'];

                // order items details
                $sql = 'SELECT
                    `oi`.`item_id` AS `item_id`,
                    `i`.`title`,
                    `oi`.`count`,
                    `oi`.`price` AS `item_price`,
                    `oi`.`reward` AS `item_reward`,
                    (`oi`.`price` * `oi`.`count`) AS `summary_price`,
                    (`oi`.`reward` * `oi`.`count`) AS `summary_reward`
                FROM `orders_items` AS `oi`
                LEFT JOIN `items` AS `i`
                    ON `i`.`id` = `oi`.`item_id`
                WHERE `oi`.`order_id` = ' . $id;

                $res2 = dbConn::$mysqli->query($sql);
                if ($res2 && $res2->num_rows > 0) {
                    while ($row2 = $res2->fetch_assoc()) {
                        $orders[$id]['equipment'][] = $row2['title'] . ' - ' . $row2['count'] . ' шт';

                        if (!isset($orders[$id]['summary_price'])) $orders[$id]['summary_price'] = $row2['summary_price'];
                        else $orders[$id]['summary_price'] += $row2['summary_price'];

                        if (!isset($orders[$id]['summary_reward'])) $orders[$id]['summary_reward'] = $row2['summary_reward'];
                        else $orders[$id]['summary_reward'] += $row2['summary_reward'];

                        $orders[$id]['items'][$row2['item_id']] = array(
                            'title' => $row2['title'],
                            'count' => $row2['count'],
                            'summary_price' => $row2['summary_price'],
                            'summary_reward' => $row2['summary_reward']
                        );
                    }
                }
            }


            foreach ($orders as $o_id => $o_params) {

                // первыый столбик
                $o_id_output = $o_id . '<div>' . date('d.m.Y', $o_params['date']) . '<br>' . date('h:i', $o_params['date']) . '</div>';

                // данные клиента
                $o_c_data = json_decode($o_params['client_info'], true);
                $o_c_output = '';
                if ($o_params['client_type'] == 'org') {
                    $o_c_output .= '
                <b>' . $org_types[$o_c_data['type']] . ' "' . $o_c_data['name'] . '"</b>' .
                        (isset($o_c_data['contact']) ? $o_c_data['contact'] . '<br>' : '') .
                        (isset($o_c_data['phone']) ? $o_c_data['phone'] . '<br>' : '');
                } else {
                    $o_c_output .= '
                <b>' . $o_c_data['name'] . '</b>' .
                        (isset($o_c_data['phone']) ? $o_c_data['phone'] . '<br>' : '') .
                        (isset($o_c_data['email']) ? $o_c_data['email'] : '');
                }

                // данные счета
                $o_i_data = json_decode($o_params['invoice_info'], true);
                $o_i_output =
                    (isset($o_i_data['id']) ? $o_i_data['id'] . '<br>' : '') .
                    (isset($o_i_data['date']) ? $o_i_data['date'] . '<br>' : '');

                // данные исполнителя
                $o_w_data = json_decode($o_params['worker_info'], true);
                $o_w_output =
                    (isset($o_w_data['date']) ? $o_w_data['date'] . '<br>' : '') .
                    (isset($o_w_data['name']) ? $o_w_data['name'] . '<br>' : '') .
                    (isset($o_w_data['phone']) ? $o_w_data['phone'] . '<br>' : '');

                // оборудование
                $o_e_output =
                    implode('; ', $o_params['equipment']) . '
            <hr noshade size="1" color="bfd7e0"><b>' . number_format($o_params['summary_price'], 0, '', ' ') . '  руб.</b>';

                // вознаграждение
                $o_r_output = '<b>' . number_format($o_params['summary_reward'], 0, '', ' ') . ' руб.</b>';

                // последний столбик (статус, удалить)
                $o_s_output =
                    $order_statuses[$o_params['status']] . '
            <div>' . date('d.m.y h:i', $o_params['status_date']) . '</div>';

                $ret .= '
        <tr>
            <td>' . $o_id . '<div>' . date('d.m.Y', $o_params['date']) . '<br>' . date('h:i', $o_params['date']) . '</div></td>
            <td>' . $o_c_output . '</td>
            <td>' . $o_i_output . '</td>
            <td>' . $o_e_output . '</td>
            <td>' . $o_w_output . '</td>
            <td>' . $o_r_output . '</td>
            <td>' . $o_s_output . '</td>
        </tr>
        <tr>
            <td colspan="7">
                <div>
                    <b>Клиент</b>
                    <div></div>
                    <table border="1">
                        <tbody>';

                $table1_rows_count = ceil(sizeof($o_c_data) / 2);

                $cnt = 0;
                foreach ($client_fields[$o_params['client_type']] as $f => $v) {
//        foreach ($o_c_data as $f => $v) {
                    $ret .= '
                            <tr>
                                <td>' . $client_fields_captions[$o_params['client_type'] . '_' . $f] . '</td><td>';
                    if ($o_params['client_type'] == 'org' && $f == 'type') {
                        $ret .= $org_types[$o_c_data[$f]];
                    } else {
                        $ret .=(isset($o_c_data[$f]) ? $o_c_data[$f] : '-');
                    }
                    $ret .= '</td>
                            </tr>';

                    if ($cnt == ($table1_rows_count - 1)) {
                        $ret .= '
                        </tbody>
                    </table>
                    <table border="1">
                        <tbody>';
                    }
                    $cnt++;
                }

                $ret .= '
                        </tbody>
                    </table>
                </div>
                <div>
                    <b>Оборудование</b>
                    <table border="1">
                        <tbody>';
                $cnt = 1;
                foreach ($o_params['items'] as $item_id => $item_data) {
                    $cars = array();
                    $sql = 'SELECT `vin`, `title` FROM `orders_cars` WHERE `order_id` = ' . $o_id . ' AND `item_id` = ' . $item_id;

                    $res = dbConn::$mysqli->query($sql);
                    if ($res && $res->num_rows > 0) {
                        while ($row = $res->fetch_assoc()) {
                            $cars[] = $row['title'] . ' - ' . $row['vin'];
                        }
                    }

                    $ret .= '
                            <tr>
                                <td>' . $cnt . '</td>
                                <td>' .
                        $item_data['title'] . ', ' .
                        $item_data['count'] . ' шт, ' .
                        $item_data['summary_price'] . ' р.<br>' .
                        'Ваше вознаграждение: ' . $item_data['summary_reward'] . 'р.
                                </td>
                                <td>' . implode('; ', $cars) . '</td>
                            </tr>';

                    $cnt++;
                }
                $ret .= '
                        </tbody>
                    </table>
                    <b>Итого:</b>
                    <b>Оборудование: ' . number_format($o_params['summary_price'], 0, '', ' ') . ' р.; Ваше вознаграждение: ' . number_format($o_params['summary_reward'], 0, '', ' ') . ' р.</b>
                </div>
            </td>
        </tr>';

            }
            $ret .= '</tbody></table></div>';
        }

        return $ret;
    }
}