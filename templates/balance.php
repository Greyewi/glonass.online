<?php
if (!users::$user_auth) pageLoader::redirectTo('login');

$order_id = $users->getUserOrderId();

//todo: логика отображения данных карты и суммы после сабмита

echo '
<h1 class="page_title">' . pageLoader::$current_template['title'] . '</h1>
<br class="clear">
<div class="white_with_border">';

$errors = array();
$success = '';

$current_available = users::getUserBalance();
$current_unconfirmed = users::getUserBalanceFuture();

// карты пользователя
$user_cards = array();
$sql = 'SELECT * FROM `users_cards` WHERE `user_id` = ' . users::$user_data['id'];
$res = $db->query($sql);
if ( $res && $res->num_rows > 0 ) {
    while ($row = $res->fetch_assoc()) {
        $user_cards[$row['id']] = $row['card'];
    }
}

if ( isset($_POST['reward']) && is_array($_POST['reward']) ) {
    $reward = $_POST['reward'];

    if ( !isset($reward['amount']) || !is_numeric(commonClass::stringToInt($reward['amount'])) ) {
        $errors[] = 'Вы не указали (ли указали неправильно) сумму';
    } else {
        $reward['amount'] = commonClass::stringToInt($reward['amount']);
        if ( $reward['amount'] > $current_available ) {
            $errors[] = 'Вы не можете запросить сумму, превышающую баланс.';
        }
    }

    $card_id = 0;

    if ( !isset($reward['card_id']) || $reward['card_id'] == 'new' ) {
        if ( isset($reward['card']) && is_numeric(commonClass::stringToInt($reward['card'])) ) {
            $card_from_post = commonClass::stringToInt($reward['card']);
            // если юзер выбрал "на новую карту" и указал её номер, то проверим, не указывал ли он её ранее
            if ( in_array($card_from_post, $user_cards) ) {
                list($card_id) = array_keys($user_cards, $card_from_post);
            } else {
                // если нет, то сохраним её в БД
                $sql = 'INSERT INTO `users_cards` (`user_id`, `card`) VALUES (' . users::$user_data['id'] . ', "' . $card_from_post . '")';
                if ($db->query($sql)) {
                    $card_id = dbConn::$mysqli->insert_id;
                    $user_cards[$card_id] = commonClass::stringToInt($reward['card']);
                } else {
                    $errors[] = 'Ошибка при сохранении новой карты';
                }
            }
        } else {
            $errors[] = 'Вы не указали номер карты';
        }
    } else if ( is_numeric($reward['card_id']) && array_key_exists($reward['card_id'], $user_cards)) {
        $card_id = (int) $reward['card_id'];
    } else {
        $errors[] = 'Ошибка при обработке данных карты - карта не найдена';
    }

    if ( sizeof($errors) == 0 ) {
        $time_formed = time();
        $sql = '
            INSERT INTO `users_rewards` (
                `user_id`, 
                `card_id`, 
                `amount`, 
                `status`, 
                `date`
            ) VALUES (
                ' . users::$user_data['id'] . ', 
                ' . $card_id . ', 
                ' . $reward['amount'] . ',
                ' . USER_REWARD_AWAITS . ',
                ' . $time_formed . '
            )';

        if ( $db->query($sql) ) {
            $mail_headers =
                "From: no-reply@" . $_SERVER['SERVER_NAME'] . "\r\n".
                "Content-Type: text/html; charset=utf-8" . "\r\n".
                "Content-Transfer-Encoding: 8bit" . "\r\n".
                "MIME-Version: 1.0" . "\r\n" .
                "X-Mailer: PHP/" . phpversion() . "\r\n";

            // отправим админу весточку
            $mailto = commonClass::$admin_mail;
            $subj = "=?UTF-8?B?" . base64_encode("Запрос на вывод стредств") . "?=";
            $msg =
                date('Y-m-d H:i:s', $time_formed) . '<br><br>
                    Пользователь: ' . users::$user_data['first_name'] . ' ' . users::$user_data['last_name']. '<br>
                    Телефон: ' . users::$user_data['phone'] . '<br>
                    E-mail: <a href="mailto:'.users::$user_data['email'].'">' . users::$user_data['email'] . '</a><br><br>
                    --------<br><br>
                    Сумма: ' . $reward['amount'] . '<br>
                    Карта: ' . $user_cards[$card_id] . '<br>';

            mail($mailto, $subj, $msg, $mail_headers);

            $success = '<div class="success">Запрос сохранен и отправлен администратору.</div>';

            // пересчитаем баланс еще раз
            $current_available = users::getUserBalance();
        } else {
            $errors[] = 'Не удалось сохранить запрос на выплату';
        }
    }
}

echo '
<div class="balance_summary available">Баланс: ' . $current_available . ' р<span class="fa fa-question"></span><span class="hint_text">
Сумма Ваших вознаграждений.<br>
Получить вознаграждение можно отправив заявку на вывод средств.
</span></div>
<div class="balance_summary unconfirmed">Возможный баланс: ' . $current_unconfirmed . ' р<span class="fa fa-question"></span><span class="hint_text">Возможный баланс - сумма всех ваших вознаграждений.<br>Перемещается в баланс после оплаты счета заказчиком.</span></div>';

if ( sizeof($errors) > 0 ) echo '<div class="errors"><p>' . implode('</p><p>', $errors) . '</p></div>';
echo $success;

if ( $current_available > 0 ) {
    echo '<form action="" method="post">';

    // если у пользователя в системе уже есть карта(ы), он может запросить вывод на неё (одну из них)
    if ( sizeof($user_cards) > 0 ) {
        echo '
        <div class="user_card_type">Произвести выплату на: 
            <input type="radio" name="reward[card_id]" value="new" id="user_card_new"' . (!isset($_POST['reward']['card_id']) || $_POST['reward']['card_id'] == 'new' ? ' checked' : '') . '>
            <label for="user_card_new">Новую карту</label>';

        foreach ($user_cards as $card_id => $card_number) {
            $card_mask = substr($card_number,0,4) . str_repeat('*', strlen($card_number)-8) . substr($card_number,-4);
            echo '
            <input type="radio" name="reward[card_id]" value="' . $card_id . '" id="user_card_' . $card_id . '"' . (isset($_POST['reward']['card_id']) && $_POST['reward']['card_id'] == $card_id ? ' checked' : '') . '>
            <label for="user_card_' . $card_id . '">Карту ' . $card_mask . '</label>';
        }

        echo '
        </div>';
    }

    // в любом случае, пользователь может указать новую карту

    echo '<table class="form_table third">
            <tbody>
                <tr>
                    <td><label for="reward_amount">Сумма</label></td>
                    <td><input id="reward_amount" name="reward[amount]" class="text" value="' . ( isset($_POST['reward']['amount']) ? htmlspecialchars(trim($_POST['reward']['amount'])) : '' ) . '" autofocus></td>
                </tr>
                <tr>
                    <td><label for="reward_card">Номер карты</label></td>
                    <td><input id="reward_card" name="reward[card]" class="text" value="' . ( isset($_POST['reward']['card']) ? htmlspecialchars(trim($_POST['reward']['card'])) : '' ) . '"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" value="Получить" class="btn green balance"></td>
                </tr>
            </tbody>
        </table>';

    echo '</form>';
}

// история запросов выплат
$sql = 'SELECT `id` FROM `users_rewards` WHERE `user_id` = ' . users::$user_data['id'];
$res = $db->query($sql);
if ( $res && $res->num_rows > 0 ) {
?>
    <h3>История выплат</h3>
    <form action="" method="post" class="test">
        <select class="rewards_history_period" name="rewards_history_period" title="период истории">
    <?php
    $periods = array(
        'За все время',
        'За сегодня',
        'За неделю',
        'За месяц',
        'За 3 месяца'
    );

    $reward_statuses = array(
        USER_REWARD_AWAITS => 'ожидает',
        USER_REWARD_ACCEPTED => 'обработано',
        USER_REWARD_PAYED => 'исполнено'
    );

    $current_period = isset($_POST['rewards_history_period']) && array_key_exists($_POST['rewards_history_period'], $periods) ? $_POST['rewards_history_period'] : 0;
    foreach ($periods as $period_value => $period_caption) {
        $selected = $period_value == $current_period ? ' selected' : '';
        echo '<option value="' . $period_value . '"' . $selected . '>' . $period_caption . '</option>';
    }
    ?>
        </select>
    </form>

    <table class="rewards_history entities">
        <tbody>
        <tr>
            <th>Дата</th>
            <th>Сумма, р</th>
            <th>Карта</th>
            <th>Статус</th>
        </tr>
    <?php
    switch ($current_period) {
        case 0: // за все время
            $period_clause = '';
            break;
        case 1: // сегодня
            $period_clause = mktime(0,0,0,date('m'),date('d'),date('Y'));
            break;
        case 2: // неделя
            $period_clause = strtotime('-1 week');
            break;
        case 3: // месяц
            $period_clause = strtotime('-1 month');
            break;
        case 4: // 3 месяца
            $period_clause = strtotime('-3 month');
            break;
        default:
            $period_clause = '';
    }
    $sql = 'SELECT * FROM `users_rewards` WHERE `user_id` = ' . users::$user_data['id'] . ( strlen($period_clause) > 0 ? ' AND `date` > ' . $period_clause : '' );
    $res = $db->query($sql);
    if ( $res && $res->num_rows > 0 ) {
        while ($row = $res->fetch_assoc()) {
            $card_mask = substr($user_cards[$row['card_id']],0,4) . str_repeat('*', strlen($user_cards[$row['card_id']])-8) . substr($user_cards[$row['card_id']],-4);
            echo '
        <tr>
            <td>' . date('d-m-Y', $row['date']) . '</td>
            <td>' . $row['amount'] . '</td>
            <td>' . $card_mask . '</td>
            <td>' . $reward_statuses[$row['status']] . '</td>
        </tr>';
            }
    } else {
        echo '
        <tr>
            <td colspan="4">за выбранный период выплат нет</td>
        </tr>';
    }
    echo '</tbody></table>';
}


echo '</div>';