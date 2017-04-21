<?php
if (!users::isAdmin()) pageLoader::redirectTo('login');

if ( sizeof(commonClass::$cities_list) == 0 ) commonClass::getCitiesList();

echo '
<h1 class="page_title">' . pageLoader::$current_template['title'] . '</h1>
<br class="clear">
<div class="white_with_border">';

if ( commonClass::checkNumericVar(@$_GET['id']) && commonClass::checkArrayVar($user = users::getUserData($_GET['id'])) ) {

    if ( isset($_GET['del']) ) {
        $user_orders_ids = array();
        $sql = 'SELECT `id` FROM `orders` WHERE `user_id` = ' . $user['id'];
        $res = $db->query($sql);
        if ( $res && $res->num_rows > 0 ) {
            while ( $row = $res->fetch_assoc() ) {
                $user_orders_ids[] = (int) $row['id'];
            }
        }
        
        if ( sizeof($user_orders_ids) > 0 ) {
            $db->query('DELETE FROM `orders_status_history` WHERE `order_id` IN (' . implode(',', $user_orders_ids) . ')');
            $db->query('DELETE FROM `orders_cars` WHERE `order_id` IN (' . implode(',', $user_orders_ids) . ')');
            $db->query('DELETE FROM `orders_items` WHERE `order_id` IN (' . implode(',', $user_orders_ids) . ')');
            $db->query('DELETE FROM `orders` WHERE `id` IN (' . implode(',', $user_orders_ids) . ')');
        }

        $db->query('DELETE FROM `users_rewards` WHERE `id` = ' . $user['id']);
        $db->query('DELETE FROM `users_cards` WHERE `id` = ' . $user['id']);
        $db->query('DELETE FROM `users` WHERE `id` = ' . $user['id']);

        pageLoader::redirectTo('admin_users/?user_deleted', 301, true);
    }

    if ( commonClass::checkArrayVar(@$_POST['user']) ) {
        $update_array = array();
        // posted user data
        $p_u_d = $_POST['user'];
        if ( commonClass::checkStringVar(@$p_u_d['last_name']) && $p_u_d['last_name'] != $user['last_name'] ) {
            $update_array[] = '`last_name` = "' . dbConn::$mysqli->escape_string(htmlspecialchars(trim($p_u_d['last_name']))) . '"';
        }
        if ( commonClass::checkStringVar(@$p_u_d['first_name']) && $p_u_d['first_name'] != $user['first_name'] ) {
            $update_array[] = '`first_name` = "' . dbConn::$mysqli->escape_string(htmlspecialchars(trim($p_u_d['first_name']))) . '"';
        }
        if ( commonClass::checkStringVar(@$p_u_d['phone_text']) ) {
            $phone_int = commonClass::stringToInt($p_u_d['phone_text']);
            if (strlen($phone_int) == 11 && in_array($phone_int[0], array(7, 8))) {
                if ( $p_u_d['phone_text'] != $user['phone'] ) {
                    $update_array[] = '`phone` = "' . commonClass::stringToPhone(substr($phone_int, 1)) . '"';
                    $update_array[] = '`phone_prefix` = ' . (int) substr($phone_int, 1, 3);
                }
            }
        }
        if ( commonClass::checkStringVar(@$p_u_d['email']) && $p_u_d['email'] != users::$user_data['email'] && commonClass::mailCheck($p_u_d['email']) ) {
            $update_array[] = '`email` = "' . dbConn::$mysqli->escape_string(htmlspecialchars(trim($p_u_d['email']))) . '"';
        }
        if ( commonClass::checkStringVar(@$p_u_d['passw']) && $users->createHash($p_u_d['passw']) != $user['passw'] ) {
            $update_array[] = '`passw` = "' . dbConn::$mysqli->escape_string($users->createHash($p_u_d['passw'])) . '"';
        }
        if ( commonClass::checkNumericVar(@$p_u_d['city'], array_keys(commonClass::$cities_list)) && $p_u_d['city'] != $user['city'] ) {
            $update_array[] = '`city` = ' . (int) $p_u_d['city'];
        }
        if ( isset($p_u_d['status']) && $p_u_d['status'] == USER_STATUS_ADMIN) {
            if ( $user['status'] != USER_STATUS_ADMIN ) $update_array[] = '`status` = ' . USER_STATUS_ADMIN;
        } else {
            if ( $user['status'] != 0 ) $update_array[] = '`status` = 0';
        }

        if ( sizeof($update_array) > 0 ) {
            $sql = 'UPDATE `users` SET ' . implode(', ', $update_array) . ' WHERE `id` = ' . (int) $user['id'];
            if ( $db->query($sql) ) {
                pageLoader::redirectTo('admin_users/?user_edited', 301, true);
            }
        }
    }

    echo '
<form class="account_settings" method="POST">
        <table>
            <tbody>
                <tr>
                    <td><label for="user_last_name">Имя</label></td>
                    <td><input type="text" name="user[last_name]" id="user_last_name" value="' . $user['last_name'] . '" required></td>
                </tr>
                <tr>
                    <td><label for="user_first_name">Фамилия</label></td>
                    <td><input type="text" name="user[first_name]" id="user_first_name" value="' . $user['first_name'] . '" required></td>
                </tr>
                <tr>
                    <td><label for="user_phone_text">Телефон</label></td>
                    <td><input type="text" name="user[phone_text]" placeholder="+7 (xxx) xxx-xx-xx" id="user_phone_text" value="' . $user['phone'] . '" required pattern="\+7(?:\s|-)?\(?\d{3}\)?(?:\s|-)?\d{3}(?:\s|-)?\d{2}(?:\s|-)?\d{2}"></td>
                </tr>
                <tr>
                    <td><label for="user_email">E-mail</label></td>
                    <td><input type="text" name="user[email]" id="user_email" value="' . $user['email'] . '" required pattern=".*@.*\..{2,}"></td>
                </tr>';

    if (sizeof(commonClass::$cities_list) > 0) {
        echo '
                <tr>
                    <td><label for="user_city">Город</label></td>
                    <td>
                        <select name="user[city]" id="user_city" required>';
        foreach (commonClass::$cities_list as $id => $title) {
            echo '<option value="' . $id . '"' . ($id == $user['city'] ? ' selected' : '') . '>' . $title . '</option>';
        }
        echo '
                        </select>
                    </td>
                </tr>';
    }

    echo '
                <tr>
                    <td><label for="user_passw">Новый пароль</label></td>
                    <td><input type="password" name="user[passw]" id="user_passw" value=""></td>
                </tr>
                <tr>
                    <td><label for="user_admin">Админ?</label></td>
                    <td><input type="checkbox" name="user[status]" id="user_admin" value="1"' . ( $user['status'] == USER_STATUS_ADMIN ? ' checked' : '' ) . '></td>
                </tr>
            </tbody>
        </table>
        <input type="submit" value="сохранить" class="btn green">
    </form>';
}
echo '</div>';