<?php
if (!users::$user_auth) pageLoader::redirectTo('login');


echo '
<h1 class="page_title">' . pageLoader::$current_template['title'] . '</h1>
<br class="clear">
<div class="white_with_border">';

$orders = array();

$order_statuses = array(
    ORDER_STATUS_SAVED => 'сохранен',
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

$client_fields_highlight = array(
    'org_name',
    'org_inn',
    'org_contact',
    'org_phone',
    'org_email',
    'man_name',
    'man_phone',
    'main_email'
);

$exclude_statuses = array(ORDER_STATUS_CREATED);


// for pagination
$sql = 'SELECT COUNT(*) FROM `orders` 
WHERE `user_id` =' . users::$user_data['id'] . ' AND `status` NOT IN (' . implode(', ', $exclude_statuses) . ')';

$entities_count = 0;

$res = $db->query($sql);
if ( $res && $res->num_rows == 1 ) {
    $entities_count = $res->fetch_row();
    if ( is_array($entities_count) ) $entities_count = (int) $entities_count[0];
}

if ( $entities_count > 0 ) {

    $pageLoader->setEntitiesPerPage($entities_count);
    $pageLoader->setCurrentPage($entities_count);

    $pagination = $pageLoader->paginationOutput($entities_count);

    echo pageLoader::perPageOutput();

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
WHERE `o`.`user_id` = ' . users::$user_data['id'] . ' AND `o`.`status` NOT IN (' . implode(', ', $exclude_statuses) . ')' . pageLoader::getSQLLimit();

    $res = $db->query($sql);
    if ($res && $res->num_rows > 0) {
        echo '
<div class="order history"><table class="entities">
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

        // class="details_link show" id="client'.$o_id.'" title="подробнее"
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

            $res2 = $db->query($sql);
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
            $o_id_output = $o_id . '<div class="event_date">' . date('d.m.Y', $o_params['date']) . '<br>' . date('h:i', $o_params['date']) . '</div>';

            // данные клиента
            $o_c_data = json_decode($o_params['client_info'], true);
            $o_c_output = '';
            if ($o_params['client_type'] == 'org') {
                $o_c_output .= '
                <b class="details_link show" id="client' . $o_id . '" title="подробнее">' . $org_types[$o_c_data['type']] . ' "' . $o_c_data['name'] . '"</b>' .
                    (isset($o_c_data['contact']) ? $o_c_data['contact'] . '<br>' : '') .
                    (isset($o_c_data['phone']) ? $o_c_data['phone'] . '<br>' : '');
            } else {
                $o_c_output .= '
                <b class="details_link show" id="client' . $o_id . '" title="подробнее">' . $o_c_data['name'] . '</b>' .
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
            <hr noshade size="1" color="bfd7e0">
            <b class="details_link show" id="items' . $o_id . '" title="подробнее">' . number_format($o_params['summary_price'], 0, '', ' ') . '  руб.</b>';

            // вознаграждение
            $o_r_output = '<b>' . number_format($o_params['summary_reward'], 0, '', ' ') . ' руб.</b>';

            // последний столбик (статус, удалить)
            $o_s_output =
                $order_statuses[$o_params['status']] . '
            <div class="event_date">' . date('d.m.y h:i', $o_params['status_date']) . '</div>
            ' . ($o_params['status'] == ORDER_STATUS_SAVED ? '<input type="hidden" name="order_id" value="' . $o_id . '"><a href="#" class="change">Оформить</a><br><a href="#" class="delete">Удалить</a>' : '');

            echo '
        <tr>
            <td>' . $o_id . '<div class="event_date">' . date('d.m.Y', $o_params['date']) . '<br>' . date('h:i', $o_params['date']) . '</div></td>
            <td>' . $o_c_output . '</td>
            <td>' . $o_i_output . '</td>
            <td>' . $o_e_output . '</td>
            <td>' . $o_w_output . '</td>
            <td>' . $o_r_output . '</td>
            <td class="status">' . $o_s_output . '</td>
        </tr>
        ';


            echo '
        <tr class="details_row">
            <td colspan="7">
                <div class="details" id="details_client' . $o_id . '">
                    <b>Клиент</b>
                    <div class="clear"></div>
                    <table class="text_table">
                        <tbody>';

            $table1_rows_count = ceil(sizeof($o_c_data) / 2);

            $cnt = 0;
            foreach ($client_fields[$o_params['client_type']] as $f => $v) {
//        foreach ($o_c_data as $f => $v) {
                echo '
                            <tr>
                                <td>' . $client_fields_captions[$o_params['client_type'] . '_' . $f] . '</td><td>';
                if ($o_params['client_type'] == 'org' && $f == 'type') {
                    echo $org_types[$o_c_data[$f]];
                } else {
                    echo(isset($o_c_data[$f]) ? $o_c_data[$f] : '-');
                }
                echo '</td>
                            </tr>';

                if ($cnt == ($table1_rows_count - 1)) {
                    echo '
                        </tbody>
                    </table>
                    <table class="text_table">
                        <tbody>';
                }
                $cnt++;
            }

            echo '
                        </tbody>
                    </table>
                </div>
                <div class="details" id="details_items' . $o_id . '">
                    <b>Оборудование</b>
                    <table class="order_items">
                        <tbody>';
            $cnt = 1;
            foreach ($o_params['items'] as $item_id => $item_data) {
                $cars = array();
                $sql = 'SELECT `vin`, `title` FROM `orders_cars` WHERE `order_id` = ' . $o_id . ' AND `item_id` = ' . $item_id;

                $res = $db->query($sql);
                if ($res && $res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        $cars[] = $row['title'] . ' - ' . $row['vin'];
                    }
                }

                echo '
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
            echo '
                        </tbody>
                    </table>
                    <b>Итого:</b>
                    <b class="black">Оборудование: ' . number_format($o_params['summary_price'], 0, '', ' ') . ' р.; Ваше вознаграждение: ' . number_format($o_params['summary_reward'], 0, '', ' ') . ' р.</b>
                </div>
            </td>
        </tr>';

        }
        echo '</tbody></table></div>';


        echo $pagination;
    }
} else {
    echo 'У вас пока нет заказов<p><a href="/">Оформить заказ</a></p>';
}
echo '</div>';