<?php
ob_start();

$debug = array();

$steps = array(1,2,3,4);
$item_types = array(1,2,3,4);
$client_values = array(
    'org' => array(
        'type' => '',
        'name' => '',
        'inn' => '',
        'kpp' => '',
        'bik' => '',
        'ks' => '',
        'rs' => '',
        'bank' => '',
        'address' => '',
        'leader' => '',
        'rank' => '',
        'act_upon' => '',
        'contact' => '',
        'phone' => '',
        'email' => '',
        'comment' => ''
    ),
    'man' => array(
        'name' => '',
        'address' => '',
        'phone' => '',
        'passport_s' => '',
        'passport_n' => '',
        'passport_d' => '',
        'email' => '',
        'comment' => ''
    )
);

// todo: values html check

if (
    isset($_COOKIE['order_id']) && is_numeric($_COOKIE['order_id']) &&
    isset($_POST['step']) && is_numeric($_POST['step']) && in_array($_POST['step'], $steps)
) {
    $order_id = $_COOKIE['order_id'];
    $step_data = array();
    $cur_step = (int) $_POST['step'];

    switch ($cur_step) {
        case 1:
            if ( isset($_POST['client']) && is_array($_POST['client']) ) {
                foreach ($_POST['client'] as $client_type => $client_fields) {
                    if ($client_type == 'org' || $client_type == 'man') {
                        $step_data['client'][$client_type]['name'] = isset($client_fields['name']) && strlen($client_fields['name']) > 0 ? $client_fields['name'] : '';
                    }
                    if ( $client_type == 'org' && isset($client_fields['type']) && is_numeric($client_fields['type']) && in_array($client_fields['type'], array(1,2,3,4)) ) {
                        $step_data['client'][$client_type]['type'] = (int) $client_fields['type'];
                    }
                }
            }
            break;
        case 2:
            if ( isset($_POST['item_types']) && is_array($_POST['item_types']) ) {
                foreach ($_POST['item_types'] as $item_type => $item_options) {
                    if ( in_array($item_type, $item_types) ) {
                        foreach ($item_options as $option_name => $option_value) {
                            if ( in_array($option_name, array('price','reward')) && is_numeric($option_value) ) {
                                $step_data['item_types'][$item_type][$option_name] = (int) $option_value;
                            }
                        }
                    }
                }
            }
            break;
        case 3:
            if ( isset($_POST['cars_count']) && is_array($_POST['cars_count']) ) {
                foreach ($_POST['cars_count'] as $item_type => $cars_count) {
                    if ( in_array($item_type, $item_types) && is_numeric($cars_count) ) {
                        $step_data['cars_count'][$item_type] = (int) $cars_count;
                    }
                }
            }
            break;
        case 4:
            if ( isset($_POST['client']) && is_array($_POST['client']) ) {
                foreach ($_POST['client'] as $client_type => $client_fields) {
                    if ($client_type == 'org' || $client_type == 'man') {
                        foreach ($client_fields as $field_name => $field_value) {
                            if ( array_key_exists($field_name, $client_values[$client_type]) && strlen($field_value) > 0 ) {
                                $step_data['client'][$client_type][$field_name] = $field_value;
                            }
                        }
                    }
                }
            }

            if ( isset($_POST['cars']) && is_array($_POST['cars']) ) {
                foreach ($_POST['cars'] as $item_type => $cars_options) {
                    if ( in_array($item_type, $item_types) ) {
                        foreach ($cars_options as $car_id => $car_options) {
                            if ( is_numeric($car_id) ) {
                                $step_data['cars'][$item_type][$car_id]['title'] = isset($car_options['title']) ? $car_options['title'] : '';
                                $step_data['cars'][$item_type][$car_id]['vin'] = isset($car_options['vin']) ? $car_options['vin'] : '';
                            }
                        }
                    }
                }
            }
            break;
    }

    if ( sizeof($step_data) > 0 ) {

        $to_cookie = array();
        
        if ( isset($_COOKIE['order']) ) {
            $json_data = json_decode($_COOKIE['order'], true);
            if ( json_last_error() === JSON_ERROR_NONE && isset($json_data[$order_id]) ) {

                if ( array_key_exists($cur_step, $json_data[$order_id]) ) {
                    $to_cookie[$order_id][$cur_step] = $step_data;
                }

                foreach ($json_data[$order_id] as $cookie_step => $cookie_step_data) {
                    // если в куки уже есть данные по текущему шагу - перезапишем их актуальными данными
                    if ( $cookie_step == $cur_step ) {
                        $to_cookie[$order_id][$cur_step] = $step_data;
                    } else {
                        $to_cookie[$order_id][$cookie_step] = $cookie_step_data;
                    }
                }
            }
        }

        if (sizeof($to_cookie)==0) {
            $to_cookie = array(
                $order_id => array($cur_step => $step_data)
            );
        // если в куки уже есть данные, но нет текущего шага - перезапишем
        } else if ( !array_key_exists($cur_step, $to_cookie[$order_id]) ) {
            $to_cookie[$order_id][$cur_step] = $step_data;
        }

        if ( setcookie('order', json_encode($to_cookie), strtotime('+30 days'), '/') ) {
            $debug[] = '[step ' . $cur_step . ']: cookie saved';
        } else {
            $debug[] = '[step ' . $cur_step . ']: no cookie saved';
        }
        
        $result = ob_get_clean();
//        file_put_contents('save_step.log', $result);
        echo json_encode(implode(' | ', $debug));
//        echo json_encode($result);
    }
}
