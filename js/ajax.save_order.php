<?php
ob_start();
session_start();

$debug = array();
$bad_lines = array();
$errors_output = array();

require_once '../classes/commonClass.php';
require_once '../classes/dbConn.php';
require_once '../classes/users.php';

$steps = array(ORDER_STEP_1, ORDER_STEP_2, ORDER_STEP_3, ORDER_STEP_4);
$item_types = array(ITEM_TYPE_1, ITEM_TYPE_2, ITEM_TYPE_3, ITEM_TYPE_4);

$db = new dbConn();
$users = new users();

if (!users::checkUserLoggedIn()) {
    $bad_lines[] = __LINE__;
    $errors_output[] = 'Вы не авторизованы';
} else if (!commonClass::checkNumericVar(@$_COOKIE['order_id'])) {
    $bad_lines[] = __LINE__;
    $errors_output[] = 'ошибка номера заявки';
} else if (!users::checkOrderId($_COOKIE['order_id'])) {
    $bad_lines[] = __LINE__;
    $errors_output[] = 'указанная заявка не существует, удалена, либо не связана с Вашей учетной записью';
} else if (!commonClass::checkNumericVar(@$_POST['status'], array(ORDER_STATUS_SAVED, ORDER_STATUS_FORMED))) {
    $bad_lines[] = __LINE__;
    $errors_output[] = 'ошибка статуса заявки';
} else {

    $user_id = users::$user_data['id'];
    $order_id = (int) $_COOKIE['order_id'];
    $status_new = (int) $_POST['status'];
    $status_current = users::getOrderStatus($order_id);

    if ( $status_current > $status_new ) {
        $bad_lines[] = __LINE__;
        $errors_output[] = 'ошибка смены статуса заявки. Обратитесь к администратору';
    } else {

        $cookie_data = array();
        if ( isset($_COOKIE['order']) ) {
            $json_data = json_decode($_COOKIE['order'], true);
            if ( json_last_error() === JSON_ERROR_NONE ) $cookie_data = $json_data;
        } else {
            $errors_output[] = 'отсутствуют данные COOKIE. Попробуйте обновить страницу или обратитесь к администратору';
        }

        if ( commonClass::checkArrayVar(@$cookie_data[$order_id]) ) {
            $o_data = $cookie_data[$order_id];
            
            $client_valid_fields = array(
                'org' => array('type', 'name', 'inn', 'kpp', 'bik', 'ks', 'rs', 'bank', 'address', 'leader', 'rank', 'act_upon', 'contact', 'phone', 'email', 'comment'),
                'man' => array('name', 'address', 'phone', 'email', 'comment')
            );
            
            $client_type = isset($o_data[ORDER_STEP_1]['client']['org']['name']) && strlen($o_data[ORDER_STEP_1]['client']['org']['name']) > 0 ? 'org' : 'man';
            $client_info = array();
            foreach ($o_data[ORDER_STEP_1]['client'][$client_type] as $f => $v) {
                if ( in_array($f, $client_valid_fields[$client_type]) ) $client_info[$f] = $v;
            }

            if ( sizeof($client_info) > 0 ) {
                $client_info = json_encode($client_info);
            } else {
                $bad_lines[] = __LINE__;
                $errors_output[] = 'отсутствуют данные клиента (шаг 1)';
            }


            $client_type_step4 = null;
            $step4_errors = array();
            if ( commonClass::checkArrayVar(@$o_data[ORDER_STEP_4]['client']) ) {
                foreach ($o_data[ORDER_STEP_4]['client'] as $f => $v) {
                    if ( in_array($f, array('org', 'man')) ) {
                        $client_type_step4 = $f;
                        break;
                    }
                }

                if ( $client_type_step4 !== $client_type ) {
                    $bad_lines[] = __LINE__;
                    $step4_errors[] = 'тип клиента шага 4 не совпадает с шагом 1';
                } else {
                    $client_info = array();
                    foreach ($o_data[ORDER_STEP_4]['client'][$client_type] as $f => $v) {
                        if ( in_array($f, $client_valid_fields[$client_type]) ) $client_info[$f] = $v;
                    }

                    if ( sizeof($client_info) > 0 ) {
                        $client_info = json_encode($client_info);
                    } else {
                        $bad_lines[] = __LINE__;
                        $step4_errors[] = 'ошибка данных клиента (шаг 4)';
                    }
                }

            } else {
                $bad_lines[] = __LINE__;
                $step4_errors[] = 'отсутствуют данные клиента (шаг 4)';
            }



            // now let's (prepare for) save/update items data
            $sql_cars_delete = array();
            $sql_cars_insert = array();
            $sql_items = array();

            $items_data = commonClass::checkArrayVar(@$o_data[ORDER_STEP_2]['item_types']) ? $o_data[ORDER_STEP_2]['item_types'] : array();
            if ( sizeof($items_data) > 0 ) {
                $items_saved_ids = users::getOrderItems($order_id);
                foreach ($items_data as $item_type => $item_data) {

                    if (!in_array($item_type, $item_types)) {
                        $bad_lines[] = __LINE__;
                        $errors_output[] = 'ошибка чтения типа оборудования (шаг 2)';
                    } else if (!commonClass::checkNumericVar(@$item_data['price'])) {
                        $bad_lines[] = __LINE__;
                        $errors_output[] = 'отсутствует цена выбранного пакета оборудования (шаг 2)';
                    } else if (!commonClass::checkNumericVar(@$item_data['reward'])) {
                        $bad_lines[] = __LINE__;
                        $errors_output[] = 'отсутствует вознаграждение по выбранному пакету оборудования (шаг 2)';
                    } else {

                        $item_id = users::getItemIDbyType($item_type);
                        if (commonClass::checkNumericVar(@$o_data[ORDER_STEP_3]['cars_count'][$item_type])) {
                            $item_count = (int) $o_data[ORDER_STEP_3]['cars_count'][$item_type];

                            // if there is saved data for this item type then update it or insert new otherwise
                            if ( array_key_exists($item_type, $items_saved_ids) ) {
                                $sql_items[$item_type] = 'UPDATE `orders_items` SET 
                                    `price` = ' . $item_data['price'] . ', 
                                    `reward` = ' . $item_data['reward'] . ', 
                                    `count` = ' . $item_count . ' 
                                WHERE `id` = ' . $items_saved_ids[$item_type];
                            } else {
                                $sql_items[$item_type] = 'INSERT INTO `orders_items` (`order_id`,`item_id`,`price`,`reward`,`count`) VALUES ( 
                                    ' . $order_id . ', 
                                    ' . $item_id . ', 
                                    ' . $item_data['price'] . ', 
                                    ' . $item_data['reward'] . ', 
                                    ' . $item_count . '
                                )';
                            }

                            // each item type' cars data (step 4)
                            if ( commonClass::checkArrayVar(@$o_data[ORDER_STEP_4]['cars'][$item_type]) ) {
                                $cars_data = $o_data[ORDER_STEP_4]['cars'][$item_type];
                                $cars_data_to_db = array();
                                if ( sizeof($cars_data) == $item_count ) {
                                    foreach ($cars_data as $car_data) {

                                        if (!isset($car_data['title'])) {
                                            $bad_lines[] = __LINE__;
                                            $step4_errors[] = 'ошибка чтения марки авто (шаг 4, тип №' . $item_type . ')';
                                        } else if (strlen(trim($car_data['title'])) == 0) {
                                            $bad_lines[] = __LINE__;
                                            $step4_errors[] = 'не указана марка авто (шаг 4, тип №' . $item_type . ')';
                                        } else if (strlen(trim($car_data['title'])) > 150) {
                                            $bad_lines[] = __LINE__;
                                            $step4_errors[] = 'название марки авто не должно быть больше 150 символов (шаг 4, тип №' . $item_type . ')';
                                        } else if (!isset($car_data['vin'])) {
                                            $bad_lines[] = __LINE__;
                                            $step4_errors[] = 'ошибка чтения VIN (шаг 4, тип №' . $item_type . ')';
                                        } else if (strlen(trim($car_data['vin'])) == 0) {
                                            $bad_lines[] = __LINE__;
                                            $step4_errors[] = 'не указан VIN (шаг 4, тип №' . $item_type . ')';
                                        } else if (strlen(trim($car_data['vin'])) > 17) {
                                            $bad_lines[] = __LINE__;
                                            $step4_errors[] = 'VIN должен быть не больше 17 символов (шаг 4, тип №' . $item_type . ')';
                                        } else {

                                            $cars_data_to_db[] =
                                                $order_id . ',' .
                                                $item_type . ',' .
                                                '"' . dbConn::$mysqli->escape_string(htmlspecialchars(trim($car_data['vin']))) . '",' .
                                                '"' . dbConn::$mysqli->escape_string(htmlspecialchars(trim($car_data['title']))) . '"';
                                        }
                                    }
                                } else {
                                    $bad_lines[] = __LINE__;
                                    $step4_errors[] = 'количество авто шаг 3 и шага 4 не совпадают (тип №' . $item_type . ')';
                                }

                                if (sizeof($cars_data_to_db) > 0) {
                                    $sql_cars_delete[$item_type] = 'DELETE FROM `orders_cars` WHERE `order_id` = ' . $order_id . ' AND `item_id` = ' . $item_id;
                                    $sql_cars_insert[$item_type] = 'INSERT INTO `orders_cars` (`order_id`, `item_id`, `vin`, `title`) VALUES (' . implode('),(', $cars_data_to_db) . ')';
                                }
                            } else {
                                $bad_lines[] = __LINE__;
                                $step4_errors[] = 'не указанны данные авто (шаг 4, тип №' . $item_type . ')';
                            }
                            
                        } else {
                            $bad_lines[] = __LINE__;
                            $errors_output[] = 'отсутствуют данные количества авто для выбранного пакетов оборудования (шаг 3, тип №' . $item_type . ')';
                        }
                    }
                }
            } else {
                $bad_lines[] = __LINE__;
                $errors_output[] = 'отсутствуют данные выбранных пакетов оборудования (шаг 2)';
            }



            // if there were errors about step 4 and current order status is FORMED (2)
            if ( sizeof($step4_errors) > 0 && $status_new == ORDER_STATUS_FORMED ) {
                $errors_output = array_merge($errors_output, $step4_errors);
            }



            // update order data (if no errors)
            if ( sizeof($errors_output) == 0 ) {

                foreach ($sql_cars_delete as $item_type => $sql) {
                    if (dbConn::$mysqli->query($sql)) {
                        $debug['result'][] = 'orders_cars data cleared (item type - ' . $item_type . ')';
                    } else {
                        $bad_lines[] = __LINE__;
                        $debug['result'][] = 'bad query: ' . htmlspecialchars($sql);
                        $errors_output[] = 'ошибка БД при обновлении (удалении) данных авто (тип №' . $item_type . '). Обратитесь к администратору';
                    }
                }

                foreach ($sql_cars_insert as $item_type => $sql) {
                    if (dbConn::$mysqli->query($sql)) {
                        $debug['result'][] = 'orders_cars data inserted (item type - ' . $item_type . ')';
                    } else {
                        $bad_lines[] = __LINE__;
                        $debug['result'][] = 'bad query: ' . htmlspecialchars($sql);
                        $errors_output[] = 'ошибка БД при обновлении (добавлении) данных авто (тип №' . $item_type . '). Обратитесь к администратору';
                    }
                }

                foreach ($sql_items as $item_type => $sql) {
                    if (dbConn::$mysqli->query($sql)) {
                        $debug['result'][] = 'orders_items data inserted (item type - '.$item_type.')';
                    } else {
                        $bad_lines[] = __LINE__;
                        $errors_output[] = 'ошибка БД при обновлении (добавлении) данных оборудования (тип №' . $item_type . '). Обратитесь к администратору';
                    }
                }

                $sql = 'UPDATE `orders` SET 
                `client_type` = "' . $client_type . '", 
                `client_info` = "' . dbConn::$mysqli->escape_string($client_info) . '" 
            WHERE `id` = ' . $order_id;

                if (dbConn::$mysqli->query($sql)) {
                    $debug['result'][] = 'order (id ' . $order_id . ') data updated';

                    // if order status has changed - insert entry to orders_statuses_history
                    if ($status_new != $status_current) {
                        users::updateOrderStatus($order_id, $status_new);
                    }

                    // if order has been formed - clear all order data cached in cookie and session
                    if ($status_new == ORDER_STATUS_FORMED) {
                        users::dropUserOrderId();
                        $debug['result'][] = 'order dropped';
                    }
                } else {
                    $bad_lines[] = __LINE__;
                    $debug['result'][] = 'bad query: ' . htmlspecialchars($sql);
                    $errors_output[] = 'ошибка БД при обновлении данных заявки. Обратитесь к администратору';
                }
            }
            
        } else {
            $bad_lines[] = __LINE__;
            $errors_output[] = 'ошибка данных COOKIE. Обратитесь к администратору';
        }
    }
}

$result = ob_get_clean();
$debug['errors'] = sizeof($errors_output) > 0 ? '<ul><li>' . implode('</li><li>', $errors_output) . '</li></ul>' : '';
$debug['output'] = $result;
$debug['bad_lines'] = implode(',', $bad_lines);
//file_put_contents('save_order.log', print_r($debug, true));
echo json_encode($debug);