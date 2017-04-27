<!--hello-->

<?php 
if ( !users::$user_auth ) pageLoader::redirectTo('login');

$items_info = array();
$sql = 'SELECT * FROM `items` ORDER BY `type`';
$item_types_res = $db->query($sql);
if ($item_types_res && $item_types_res->num_rows > 0) {
    while ($row = $item_types_res->fetch_assoc()) {
        $items_info[$row['type']] = array(
            'title' => $row['title'],
            'price_min' => $row['price_min'],
            'price_max' => $row['price_max'],
            'text' => $row['text'],
            'reward_percent' => $row['reward_percent'] / 100
        );
    }
}

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
        'email' => '',
        'comment' => ''
    )
);

$o_data = array(
    ORDER_STEP_1 => array(
        'client' => array(
            'org' => array('name' => '', 'type' => 1),
            'man' => array('name' => '')
        )
    )
);

$order_id = $users->getUserOrderId(true);

$steps_show = array();

$cookie_data = array();
if ( isset($_COOKIE['order']) ) {
    $json_data = json_decode($_COOKIE['order'], true);
    if ( json_last_error() === JSON_ERROR_NONE ) $cookie_data = $json_data;
}

if ( isset($cookie_data[$order_id]) ) {
    if ( isset($_GET['check_order_data']) ) {
        echo '<div class="check_order_data"></div>';
    }
    
    foreach ($cookie_data[$order_id] as $step => $step_data) {
        switch ($step) {
            case ORDER_STEP_1:
                if ( isset($step_data['client']) && is_array($step_data['client']) ) {
                    foreach ($step_data['client'] as $client_type => $client_fields) {
                        if ($client_type == 'org' || $client_type == 'man') {
                            $client_values[$client_type]['name'] = $o_data[$step]['client'][$client_type]['name'] = isset($client_fields['name']) && strlen($client_fields['name']) > 0 ? $client_fields['name'] : '';
                            if ( !in_array($step+1, $steps_show) ) $steps_show[] = $step+1;
                        }
                        if ( $client_type == 'org' && isset($client_fields['type']) && is_numeric($client_fields['type']) && in_array($client_fields['type'], array(1,2,3,4)) ) {
                            $client_values[$client_type]['type'] = $o_data[$step]['client'][$client_type]['type'] = (int) $client_fields['type'];
                        }
                    }
                }
                break;
            case ORDER_STEP_2:
                if ( isset($step_data['item_types']) && is_array($step_data['item_types']) ) {
                    foreach ($step_data['item_types'] as $item_type => $item_options) {
                        if ( in_array($item_type, array(ITEM_TYPE_1, ITEM_TYPE_2, ITEM_TYPE_3, ITEM_TYPE_4)) ) {
                            foreach ($item_options as $option_name => $option_value) {
                                if ( in_array($option_name, array('price','reward')) && is_numeric($option_value) ) {
                                    $o_data[$step]['item_types'][$item_type][$option_name] = (int) $option_value;
                                    if ( !in_array($step+1, $steps_show) ) $steps_show[] = $step+1;
                                }
                            }
                        }
                    }
                }
                break;
            case ORDER_STEP_3:
                if ( isset($step_data['cars_count']) && is_array($step_data['cars_count']) ) {
                    foreach ($step_data['cars_count'] as $item_type => $cars_count) {
                        if ( in_array($item_type, array(ITEM_TYPE_1, ITEM_TYPE_2, ITEM_TYPE_3, ITEM_TYPE_4)) && is_numeric($cars_count) ) {
                            $o_data[$step]['cars_count'][$item_type] = (int) $cars_count;

                            if ( !in_array($step+1, $steps_show) ) $steps_show[] = $step+1;
                        }
                    }
                }
                break;
            case ORDER_STEP_4:
                if ( isset($step_data['client']) && is_array($step_data['client']) ) {
                    foreach ($step_data['client'] as $client_type => $client_fields) {
                        if ($client_type == 'org' || $client_type == 'man') {
                            foreach ($client_fields as $field_name => $field_value) {
                                if ( array_key_exists($field_name, $client_values[$client_type]) && strlen($field_value) > 0 ) {
                                    $o_data[$step]['client'][$client_type][$field_name] = $field_value;
                                    $client_values[$client_type][$field_name] = $field_value;
                                }
                            }
                        }
                    }
                }

                if ( isset($step_data['cars']) && is_array($step_data['cars']) ) {
                    foreach ($step_data['cars'] as $item_type => $cars_options) {
                        if ( in_array($item_type, array(ITEM_TYPE_1, ITEM_TYPE_2, ITEM_TYPE_3, ITEM_TYPE_4)) ) {
                            foreach ($cars_options as $car_id => $car_options) {
                                if ( is_numeric($car_id) ) {
                                    $o_data[$step]['cars'][$item_type][$car_id]['title'] = isset($car_options['title']) ? $car_options['title'] : '';
                                    $o_data[$step]['cars'][$item_type][$car_id]['vin'] = isset($car_options['vin']) ? $car_options['vin'] : '';
                                }
                            }
                        }
                    }
                }
                break;
        }
    }
}
$org_form_selected = strlen($o_data[ORDER_STEP_1]['client']['org']['name']) > 0;

// при активном режиме редактирования показывать только второй шаг
if ( users::$edit_mode ) $steps_show = array(ORDER_STEP_2);

?>
<h1 class="page_title">Оформление заказа № <?=$order_id?></h1>
<div class="content_block" id="step1">
    <?=users::$edit_mode?'<div class="editor_curtain"></div>':''?>
    <form>
        <div class="block_title arrow">шаг 1. добавьте покупателя</div>
        <div class="cntnt">
            <div class="pc pc_50 left">
                <?=commonClass::btn_chkbx_output('Физическое лицо', 'man_form', !$org_form_selected)?>
            </div>
            <div class="pc pc_50">
                <?=commonClass::btn_chkbx_output('Юридическое лицо', 'org_form', $org_form_selected)?>
            </div>
            <div class="clear"></div>
            <div class="pc pc_50 left org_form_child">
                <select name="client[org][type]" class="form_element">
                    <option value="1"<?=($o_data[ORDER_STEP_1]['client']['org']['type'] == 1?' selected':'')?>>ИП</option>
                    <option value="2"<?=($o_data[ORDER_STEP_1]['client']['org']['type'] == 2?' selected':'')?>>ООО</option>
                    <option value="3"<?=($o_data[ORDER_STEP_1]['client']['org']['type'] == 3?' selected':'')?>>ОАО</option>
                    <option value="4"<?=($o_data[ORDER_STEP_1]['client']['org']['type'] == 4?' selected':'')?>>ЗАО</option>
                </select>
            </div>
            <div class="pc pc_50 org_form_child">
                <input type="text" name="client[org][name]" placeholder="Название компании" value="<?=$o_data[ORDER_STEP_1]['client']['org']['name']?>" class="form_element">
            </div>
            <div class="pc center man_form_child">
                <input type="text" name="client[man][name]" placeholder="ФИО" value="<?=$o_data[ORDER_STEP_1]['client']['man']['name']?>" class="form_element">
            </div>
            <div class="pc center"><div class="form_btn green ok" style="display: none;">ok</div></div>
            <!--
            <div class="pc pc_50 left"><div class="form_btn green ok">ok</div></div>
            <div class="pc pc_50"><div class="form_btn cancel">отмена</div></div>
            -->
        </div>
    </form>
</div>
<div class="content_block" id="step2">
    <form>
        <div class="block_title arrow">шаг 2. выберите Оборудование</div>
        <div class="cntnt<?=(in_array(ORDER_STEP_2, $steps_show)?' active':'')?>">
            <?php
            foreach ($items_info as $item_type => $item_info) {
                $reward_percent = $item_info['reward_percent'];
                $defined = isset($o_data[ORDER_STEP_2]['item_types'][$item_type]) ? $o_data[ORDER_STEP_2]['item_types'][$item_type] : null;
                $item_price = 
                    isset($defined['price']) && $defined['price'] >= $item_info['price_min'] && $defined['price'] <= $item_info['price_max']
                        ? $defined['price']
                        : ceil(($item_info['price_max'] + $item_info['price_min'])/2);

                //Осторожно костыль; задает изначальные значения ползунка - общая стоимость

                if($item_type == 1){
                    $item_price = 8500;
                }
                if($item_type == 2){
                    $item_price = 18500;
                }
                if($item_type == 3){
                    $item_price = 1800;
                }
                if($item_type == 4){
                    $item_price = 5000;
                }


                $item_reward = 
                    isset($defined['reward']) && $defined['reward'] >= $item_info['price_min']*$reward_percent && $defined['reward'] <= $item_info['price_max']*$reward_percent
                        ? $defined['reward']
                        : ceil($item_price * $reward_percent);


                //Осторожно костыль; задает изначальные значения ползунка - вознаграждение

                if($item_type == 1){
                    $item_reward = 1300;
                }
                if($item_type == 2){
                    $item_reward = 3000;
                }
                if($item_type == 3){
                    $item_reward = 500;
                }
                if($item_type == 4){
                    $item_reward = 1000;
                }



                $price_delta = $item_info['price_max'] - $item_info['price_min'];
                $slider_filled = ($item_price-$item_info['price_min']) / $price_delta * 100;
                
                if ( users::$edit_mode ) {
                    $item_info['title'] = '<input type="text" name="edit_item[' . $item_type . '][title]" value="' . $item_info['title'] . '">';
                    $item_info['text'] = '<textarea class="editor" name="edit_item[' . $item_type . '][text]">' . $item_info['text'] . '</textarea>
                    от: <input type="text" name="edit_item[' . $item_type . '][price_min]" value="' . $item_info['price_min'] . '"> руб.<br>
                    до: <input type="text" name="edit_item[' . $item_type . '][price_max]" value="' . $item_info['price_max'] . '"> руб.<br>
                    %: <input type="text" name="edit_item[' . $item_type . '][reward_percent]" value="' . $item_info['reward_percent'] * 100 . '">';
                }
                
                echo '
            <div class="pc pc_25" item-type="'.$item_type.'">
                ' . commonClass::btn_chkbx_output(
                        $item_info['title'],
                        'item_type_'.$item_type,
                        users::$edit_mode || !is_null($defined),
                        'icon_item_type'.$item_type.'.png'
                ) . '
                <div class="item_details item_type_'.$item_type.'_child">
                    <div class="item_text">' . $item_info['text'] . '</div>
                    <b>Увеличь свое вознаграждение:</b>
                    <div class="reward">
                        ' . ( users::$edit_mode ? '<div class="editor_curtain"></div>': '' ) . '
                        <input type="hidden" class="cart_price_min" value="'.$item_info['price_min'].'">
                        <input type="hidden" class="cart_price_max" value="'.$item_info['price_max'].'">
                        <input type="hidden" class="reward_percent" value="'.$reward_percent.'">
                        <input type="hidden" name="item_types['.$item_type.'][price]" class="item_price_result" value="'.$item_price.'"><br>
                        <input type="hidden" name="item_types['.$item_type.'][reward]" class="user_reward" value="'.$item_reward.'"><br>
                        Стоимость оборудования: <span class="item_price_result">'.number_format($item_price, 0, '', ' ').'</span> р.
                        <div class="slider">
                            <span class="handle" style="left: '.number_format(($slider_filled-8),4).'%"></span>
                            <div class="wrapper"><div class="fill" style="width: '.number_format($slider_filled,4).'%"></div></div>
                        </div>
                        <div class="result">Ваше вознаграждение: <span class="user_reward">'.number_format($item_reward, 0, '', ' ').'</span> р.</div>
                    </div>
                </div>
            </div>';
            }
            ?>
            <div class="clear"></div>
            <div class="pc center">
                <?php
                if ( users::$edit_mode ) {
                    echo '<div class="form_btn green ok edit">редактировать</div>';
                } else {
                    echo '<div class="form_btn green ok"' . ( commonClass::checkArrayVar(@$o_data[ORDER_STEP_2]) ? '': ' style="display: none;"' ) . '>ok</div>';
                }
                ?>
            </div>
        </div>
    </form>
</div>
<div class="content_block" id="step3">
    <?=users::$edit_mode?'<div class="editor_curtain"></div>':''?>
    <form>
        <div class="block_title arrow">шаг 3. укажите Количество оборудования</div>
        <div class="cntnt<?=(in_array(ORDER_STEP_3, $steps_show)?' active':'')?>">
            <?php
            foreach ($items_info as $item_type => $item_info) {
                    echo '
            <div class="cars_counters item_type_'.$item_type.'_child">
                <div class="caption"><span>'.$item_info['title'].'</span></div>
                <div class="cars_count">
                    <span title="уменьшить на 1">-</span>
                    <input type="text" name="cars_count['.$item_type.']" value="'.(isset($o_data[ORDER_STEP_3]['cars_count'][$item_type])? $o_data[ORDER_STEP_3]['cars_count'][$item_type] : 1).'">
                    <span class="increase" title="увеличить на 1">+</span>
                </div>
                <a href="#" class="delete">Удалить</a>
            </div>';
            }
            ?>
            <div class="order">
                <table class="entities">
                    <tbody>
                        <tr>
                            <th>Оборудование</th>
                            <th>Стоимость оборудования, р.</th>
                            <th>Вознаграждение, р.</th>
                            <th>Кол-во оборудования</th>
                            <th>Стоимость оборудования итого, р.</th>
                            <th>Вознаграждение итого, р.</th>
                        </tr>
                        <tr class="item_type_1_child">
                            <td class="text">Мониторинг</td>
                            <td class="item_price_result"></td>
                            <td class="user_reward"></td>
                            <td class="cars_count"></td>
                            <td class="summary_price"></td>
                            <td class="summary_reward"></td>
                        </tr>
                        <tr class="item_type_2_child">
                            <td class="text">Контроль топлива</td>
                            <td class="item_price_result"></td>
                            <td class="user_reward"></td>
                            <td class="cars_count"></td>
                            <td class="summary_price"></td>
                            <td class="summary_reward"></td>
                        </tr>
                        <tr class="item_type_3_child">
                            <td class="text">Блокировка двигателя</td>
                            <td class="item_price_result"></td>
                            <td class="user_reward"></td>
                            <td class="cars_count"></td>
                            <td class="summary_price"></td>
                            <td class="summary_reward"></td>
                        </tr>
                        <tr class="item_type_4_child">
                            <td class="text">Маяк</td>
                            <td class="item_price_result"></td>
                            <td class="user_reward"></td>
                            <td class="cars_count"></td>
                            <td class="summary_price"></td>
                            <td class="summary_reward"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="pc left" style="width:90%;font-size: 120%;">
                <input type="hidden" class="summary_price_total" value="">
                <input type="hidden" class="summary_reward_total" value="">
                <b>Стоимость оборудования</b>: <span class="summary_price_total"></span> р.<br>
                <b style="color:#39b3e6">Ваше вознаграждение: <span class="summary_reward_total"></span> р.</b>
            </div>
            <div class="clear"></div>
            <div class="pc pc_50 left"><div class="form_btn green">оформить заказ</div></div>
            <div class="pc pc_50"><div class="form_btn save_tmp">сохранить</div></div>
        </div>
    </form>
</div>
<div class="content_block" id="step4">
    <?=users::$edit_mode?'<div class="editor_curtain"></div>':''?>
    <form>
        <div class="block_title arrow">шаг 4. Заполните реквизиты покупателя</div>
        <div class="cntnt<?=(in_array(ORDER_STEP_4, $steps_show)?' active':'')?>">
            <h2>Заполните реквизиты:</h2>
            <?php
            $client_type = $org_form_selected ? 'org' : 'man';
            $client_fields = array(
                'org' => array(
                    'type' => 'Форма организации',
                    'inn' => 'ИНН',
                    'name' => 'Название',
                    'bank' => 'Банк',
                    'address' => 'Адрес',
                    'kpp' => 'КПП',
                    'leader' => 'Руководитель',
                    'bik' => 'БИК',
                    'rank' => 'Должность',
                    'ks' => 'К/С',
                    'act_upon' => 'Действует на основании',
                    'rs' => 'Р/с',
                    'contact' => 'Контактное лицо (ФИО)',
                    'email' => 'E-mail для отправки договора и счета',
                    'phone' => 'Тел контактного лица',
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

            foreach ($client_fields as $type => $fields) {
                echo '
            <table class="form_table client_info '.$type.'_form_child' . ($client_type == $type ? ' active' : '') . '">
                <tbody>
                    <tr>';

                $cnt = 0;
                foreach ($fields as $field_name => $field_caption) {
                    if ( $cnt > 0 && $cnt % 2 == 0 ) {
                        echo '</tr><tr>';
                    }
                    echo '
                        <td><label for="'.$type.'_'.$field_name.'">'.$field_caption.'</label></td>
                        <td>';

                    $html_attributes = 'id="' . $type . '_' . $field_name . '" name="client[' . $type . '][' . $field_name . ']" class="form_element"';
                    $html_value = isset($client_values[$type][$field_name]) ? $client_values[$type][$field_name] : '';
                    
                    if ( $type == 'org' && $field_name == 'type' ) {
                        echo '
                    <select ' . $html_attributes . '>
                        <option value="1"' . ($html_value=='1' ? ' selected' : '') . '>ИП</option>
                        <option value="2"' . ($html_value=='2' ? ' selected' : '') . '>ООО</option>
                        <option value="3"' . ($html_value=='3' ? ' selected' : '') . '>ОАО</option>
                        <option value="4"' . ($html_value=='4' ? ' selected' : '') . '>ЗАО</option>
                    </select>';
                    } else if ( $field_name == 'comment' ) {
                        echo '<textarea ' . $html_attributes . ' maxlength="500">' . $html_value . '</textarea>';
                    } else {
                        echo '<input ' . $html_attributes . ' type="text" value="' . $html_value . '">';
                    }

                    echo '
                        </td>';
                    $cnt++;
                }

                if ( sizeof($fields) % 2 != 0 ) echo '<td colspan="2"></td>';

                echo '
                    </tr>
                </tbody>
            </table>';
            }
            ?>
            <h2>Укажите марки и VIN авто:</h2>
            <?php
            foreach ($items_info as $item_type => $item_info) {
                $cars_count = commonClass::checkArrayVar(@$o_data[ORDER_STEP_4]['cars'][$item_type]) ? sizeof($o_data[ORDER_STEP_4]['cars'][$item_type]) : 1;
                echo '
            <div class="item_type_caption item_type_'.$item_type.'_child">
                <div class="caption">'.$item_info['title'].'</div>
                <table class="form_table">
                    <tbody>';

                if ( commonClass::checkArrayVar(@$o_data[ORDER_STEP_4]['cars'][$item_type]) ) {
                    $cars_info = $o_data[ORDER_STEP_4]['cars'][$item_type];
                    $cnt = 1;
                    foreach ($cars_info as $car_id => $car_options) {
                        $value_title = isset($car_options['title']) && strlen($car_options['title']) > 0 ? $car_options['title'] : '';
                        $value_vin = isset($car_options['vin']) && strlen($car_options['vin']) > 0 ? $car_options['vin'] : '';
                        echo '
                        <tr>
                            <td><label for="item_type_' . $item_type . '_car' . $car_id . '">' . $cnt . '. Марка авто</label></td>
                            <td><input id="item_type_' . $item_type . '_car' . $car_id . '" type="text" class="form_element car_name" value="' . $value_title . '" name="cars[' . $item_type . '][' . $car_id . '][title]"></td>
                            <td><label for="item_type_' . $item_type . '_vin' . $car_id . '">VIN</label></td>
                            <td><input id="item_type_' . $item_type . '_vin' . $car_id . '" type="text" class="form_element car_vin" value="' . $value_vin . '" name="cars[' . $item_type . '][' . $car_id . '][vin]"></td>
                        </tr>';
                        $cnt++;
                    }
                } else {
                    for ($i = 1; $i <= $cars_count; $i++) {
                        $value_title = isset($o_data[ORDER_STEP_4]['cars'][$item_type][$i]['title']) && strlen($o_data[ORDER_STEP_4]['cars'][$item_type][$i]['title']) > 0 ? $o_data[ORDER_STEP_4]['cars'][$item_type][$i]['title'] : '';
                        $value_vin = isset($o_data[ORDER_STEP_4]['cars'][$item_type][$i]['vin']) && strlen($o_data[ORDER_STEP_4]['cars'][$item_type][$i]['vin']) > 0 ? $o_data[ORDER_STEP_4]['cars'][$item_type][$i]['vin'] : '';
                        echo '
                        <tr>
                            <td><label for="item_type_' . $item_type . '_car' . $i . '">' . $i . '. Марка авто</label></td>
                            <td><input id="item_type_' . $item_type . '_car' . $i . '" type="text" class="form_element car_name" value="' . $value_title . '" name="cars[' . $item_type . '][' . $i . '][title]"></td>
                            <td><label for="item_type_' . $item_type . '_vin' . $i . '">VIN</label></td>
                            <td><input id="item_type_' . $item_type . '_vin' . $i . '" type="text" class="form_element car_vin" value="' . $value_vin . '" name="cars[' . $item_type . '][' . $i . '][vin]"></td>
                        </tr>';
                    }
                }

                echo '
                    </tbody>
                </table>
            </div>';
            }
            ?>
            <div class="pc pc_50 left"><div class="form_btn green">отправить</div></div>
            <div class="pc pc_50"><div class="form_btn save_tmp">сохранить</div></div>
            <!--
            <div class="pc pc_50 left"><div class="form_btn green">отправить</div></div>
            <div class="pc pc_50"><div class="form_btn cancel">отмена</div></div>
            -->
        </div>
    </form>
</div>