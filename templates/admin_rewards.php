<?php
if (!users::isAdmin()) pageLoader::redirectTo('login');

echo '
<h1 class="page_title">' . pageLoader::$current_template['title'] . '</h1>
<br class="clear">
<div class="white_with_border">';

$reward_requests = array();

$reward_statuses = array(
    USER_REWARD_AWAITS => 'ожидает',
    USER_REWARD_ACCEPTED => 'обработано',
    USER_REWARD_PAYED => 'исполнено'
);

if ( commonClass::checkArrayVar(@$_POST['reward']) ) {
    foreach ($_POST['reward'] as $reward_id => $reward_params) {
        if ( isset($reward_params['status']) && array_key_exists($reward_params['status'], $reward_statuses) ) {
            $sql = 'UPDATE `users_rewards` SET `status` = ' . $reward_params['status'] . ' WHERE `id` = ' . $reward_id;
            $db->query($sql);
        }
    }
    echo '<div class="success">Статусы обновлены</div>';
}

$sql = 'SELECT COUNT(*) FROM `users_rewards` AS `r`
    LEFT JOIN `users` AS `u`
        ON `u`.`id` = `r`.`user_id`';

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
    `u`.`last_name`,
    `u`.`first_name`,
    `u`.`email`,
    `u`.`phone_prefix`,
    `u`.`phone`,
    `r`.`amount`,
    `r`.`id`,
    `r`.`status`,
    `r`.`date`
    FROM `users_rewards` AS `r`
    LEFT JOIN `users` AS `u`
        ON `u`.`id` = `r`.`user_id`
    ORDER BY `r`.`date` DESC ' . pageLoader::getSQLLimit();

    $res = $db->query($sql);

    if ($res && $res->num_rows > 0) {
        echo '
<div class="order history">
    <form method="post">
        <table class="entities">
            <tbody>
                <tr>
                    <th>Дата</th>
                    <th>Менеджер</th>
                    <th>E-mail</th>
                    <th>Телефон</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                </tr>';

        while ($row = $res->fetch_assoc()) {

            $r_s_output = '
        <select name="reward[' . $row['id'] . '][status]">';
            foreach ($reward_statuses as $status_id => $status_caption) {
                $selected = $row['status'] == $status_id ? ' selected' : '';
                $r_s_output .= '<option value="' . $status_id . '"' . $selected . '>' . $status_caption . '</option>';
            }
            $r_s_output .= '
        </select>';

            echo '
                <tr>
                    <td>' . date('Y-m-d H:i:s', $row['date']) . '</td>
                    <td>' . $row['first_name'] . ' ' . $row['last_name'] . '</td>
                    <td>' . $row['email'] . '</td>
                    <td>' . $row['phone'] . '</td>
                    <td>' . number_format($row['amount'], 0, '', ' ') . '</td>
                    <td>' . $r_s_output . '</td>
                </tr>
                <tr class="details_row">
                    <td colspan="6"></td>
                </tr>';
        }

        echo '
            </tbody>
        </table>
        <table style="width: 100%;">
            <tbody>
                <tr>
                    <td>' . $pagination . '</td>
                    <td><div style="width: 100%; text-align: right;"><input type="submit" class="btn green" value="Сохранить"></div></td>
                </tr>
            </tbody>
        </table>
    </form>
</div>';
    }
} else {
    echo '<p><i>- Запросов на вывод средств нет -</i></p>';
}

echo '
</div>';