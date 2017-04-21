<?php
session_start();

require_once '../classes/dbConn.php';
require_once '../classes/users.php';

$db = new dbConn();
$users = new users();

$ret = array(
    'error' => true,
    'message' => ''
);


$order_id = isset($_POST['order_id']) && is_numeric($_POST['order_id']) && $_POST['order_id'] > 0 ? (int) $_POST['order_id'] : null;

if ( !users::checkUserLoggedIn() ) {
    $ret['message'] = 'Вы не авторизованны';
} else if ( is_null($order_id) ) {
    $ret['message'] = 'Не передан ID заказа';
} else if ( !users::checkOrderId($order_id) ) {
    $ret['message'] = 'Похоже, Вы пытаетесь оформить чужой заказ. Зачем Вы это делаете? Перестаньте';
} else {
    $sql = '
        SELECT
            `oi`.`item_id` AS `item_id`,
            `i`.`type` AS `item_type`,
            `oi`.`count`,
            `oi`.`price` AS `item_price`,
            `oi`.`reward` AS `item_reward`,
            (`oi`.`price` * `oi`.`count`) AS `summary_price`,
            (`oi`.`reward` * `oi`.`count`) AS `summary_reward`,
            `o`.`client_type`,
            `o`.`client_info`
        FROM `orders` AS `o`
        LEFT JOIN `orders_items` AS `oi`
            ON `oi`.`order_id` = `o`.`id`
        LEFT JOIN `items` AS `i`
            ON `i`.`id` = `oi`.`item_id`
        WHERE `o`.`id` = ' . $order_id;

    $res = $db->query($sql);
    if ( $res && $res->num_rows > 0 ) {
        users::saveUserOrderId($order_id);
        $ret['error'] = false;

        $steps_data = array();

        while ( $row = $res->fetch_assoc() ) {
            $steps_data[ORDER_STEP_1]['client'][$row['client_type']] = json_decode($row['client_info'], true);
            $steps_data[ORDER_STEP_2]['item_types'][$row['item_type']] = array(
                'price' => (int) $row['item_price'],
                'reward' => (int) $row['item_reward']
            );
            $steps_data[ORDER_STEP_3]['cars_count'][$row['item_type']] = (int) $row['count'];

            $steps_data[ORDER_STEP_4]['client'][$row['client_type']] = json_decode($row['client_info'], true);

            $sql = 'SELECT `id`,`title`, `vin` FROM `orders_cars` WHERE `order_id` = ' . $order_id . ' AND `item_id` = ' . $row['item_id'];;
            $sub_res = $db->query($sql);
            if ( $sub_res && $sub_res->num_rows > 0 ) {
                while ( $r = $sub_res->fetch_assoc() ) {
                    $steps_data[ORDER_STEP_4]['cars'][$row['item_type']][$r['id']] = array(
                        'title' => $r['title'],
                        'vin' => $r['vin']
                    );
                }
            }
        }

        $to_cookie = array(
            $order_id => $steps_data
        );

        setcookie('order', json_encode($to_cookie), strtotime('+30 days'), '/');
    } else {
        $ret['message'] = 'Не удалось получить данные заказа';
    }
}

echo json_encode($ret);