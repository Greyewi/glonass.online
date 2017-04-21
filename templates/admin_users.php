<?php
if (!users::isAdmin()) pageLoader::redirectTo('login');

if ( sizeof(commonClass::$cities_list) == 0 ) commonClass::getCitiesList();


$reward_statuses = array(
    USER_REWARD_AWAITS => 'ожидает',
    USER_REWARD_ACCEPTED => 'обработано',
    USER_REWARD_PAYED => 'выплачено'
);

echo '
<h1 class="page_title">' . pageLoader::$current_template['title'] . '</h1>
<br class="clear">
<div class="white_with_border">';

if ( isset($_GET['user_created']) ) echo '<div class="success">Пользователь создан</div>';
if ( isset($_GET['user_edited']) ) echo '<div class="success">Данные пользователя обновлены</div>';
if ( isset($_GET['user_deleted']) ) echo '<div class="success">Пользователь удален</div>';

$sql = 'SELECT COUNT(*) FROM `users`';

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

    $sql = 'SELECT * FROM `users`' . pageLoader::getSQLLimit();

    $res = $db->query($sql);
    $users_arr = array();

    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $users_arr[] = array(
                'id' => $row['id'],
                'email' => $row['email'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'phone' => $row['phone'],
                'city' => commonClass::$cities_list[$row['city']],
                'rewards' => number_format(users::getUserBalance($row['id']), 0, '', ' '),
                'rewards_future' => number_format(users::getUserBalanceFuture($row['id']), 0, '', ' '),
                'orders_count' => users::getUserOrdersCount($row['id']),
                'status' => $row['status']
            );
        }
    }

    if (sizeof($users_arr) > 0) {
        echo '
<div class="order history">
    <form method="post">
        <table class="entities">
            <tbody>
                <tr>
                    <th>Менеджер</th>
                    <th>E-mail</th>
                    <th>Телефон</th>
                    <th>Город</th>
                    <th>Баланс</th>
                    <th>Возможный баланс</th>
                    <th>Кол-во заявок</th>
                    <th>Выплаты</th>
                    <th></th>
                </tr>';
        for ($i = 0, $cnt = sizeof($users_arr); $i < $cnt; $i++) {
            $admin_label = $users_arr[$i]['status'] == USER_STATUS_ADMIN ? '<br><span class="user_admin">администратор</span>' : '';
            echo '
                <tr>
                    <td>' . $users_arr[$i]['first_name'] . ' ' . $users_arr[$i]['last_name'] . $admin_label . '</td>
                    <td>' . $users_arr[$i]['email'] . '</td>
                    <td>' . $users_arr[$i]['phone'] . '</td>
                    <td>' . $users_arr[$i]['city'] . '</td>
                    <td>' . $users_arr[$i]['rewards'] . '</td>
                    <td>' . $users_arr[$i]['rewards_future'] . '</td>
                    <td>' . $users_arr[$i]['orders_count'] . '</td>
                    <td><b class="details_link show" id="rewards' . $users_arr[$i]['id'] . '" title="подробнее">Выплаты</b></td>
                    <td><a href="/admin_user?id=' . $users_arr[$i]['id'] . '">Редактировать</a><br><a class="user delete" href="/admin_user?id=' . $users_arr[$i]['id'] . '&del">Удалить</a></td>
                </tr>';

            echo '
                <tr class="details_row">
                    <td colspan="9">
                        <div class="details" id="details_rewards' . $users_arr[$i]['id'] . '">
                            <b>Выплаты</b>';

            $user_cards = array();
            $sql = 'SELECT * FROM `users_cards` WHERE `user_id` = ' . $users_arr[$i]['id'];
            $res = $db->query($sql);
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $user_cards[$row['id']] = $row['card'];
                }
            }

            $sql = 'SELECT * FROM `users_rewards` WHERE `user_id` = ' . $users_arr[$i]['id'];
            $res = $db->query($sql);
            if ($res && $res->num_rows > 0) {
                echo '
                            <table class="rewards_history entities">
                                <tbody>
                                <tr>
                                    <th>Дата</th>
                                    <th>Сумма, р</th>
                                    <th>Карта</th>
                                    <th>Статус</th>
                                </tr>';

                while ($row = $res->fetch_assoc()) {
                    $card_mask = substr($user_cards[$row['card_id']], 0, 4) . str_repeat('*', strlen($user_cards[$row['card_id']]) - 8) . substr($user_cards[$row['card_id']], -4);
                    echo '
                                <tr>
                                    <td>' . date('d-m-Y', $row['date']) . '</td>
                                    <td>' . $row['amount'] . '</td>
                                    <td>' . $card_mask . '</td>
                                    <td>' . $reward_statuses[$row['status']] . '</td>
                                </tr>';
                }

                echo '
                                </tbody>
                            </table>';
            } else {
                echo '
                            <p><i>- выплат нет -</i></p>';
            }

            echo '
                        </div>
                    </td>
                </tr>';
        }
        echo '
            </tbody>
        </table>
    </form>
    <table style="width: 100%;">
        <tbody>
            <tr>
                <td>' . $pagination . '</td>
                <td><div style="width: 100%; text-align: right; height: 70px;"><a href="/admin_register" class="btn green" style="float: right; margin-top: 20px;">Зарегистрировать</a></div></td>
            </tr>
        </tbody>
    </table>
</div>';
    }
}
echo '</div>';