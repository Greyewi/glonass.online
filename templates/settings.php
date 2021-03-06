<?php
if (!users::$user_auth) pageLoader::redirectTo('login');

commonClass::getCitiesList();


// todo: check errors

if ( commonClass::checkArrayVar(@$_POST['user']) ) {
    // posted user data
    $p_u_d = $_POST['user'];
    $update_array = array();
    
    if ( commonClass::checkStringVar(@$p_u_d['last_name']) && $p_u_d['last_name'] != users::$user_data['last_name'] ) {
        $update_array[] = '`last_name` = "' . dbConn::$mysqli->escape_string(htmlspecialchars(trim($p_u_d['last_name']))) . '"';
    }
    if ( commonClass::checkStringVar(@$p_u_d['first_name']) && $p_u_d['first_name'] != users::$user_data['first_name'] ) {
        $update_array[] = '`first_name` = "' . dbConn::$mysqli->escape_string(htmlspecialchars(trim($p_u_d['first_name']))) . '"';
    }
    if ( commonClass::checkStringVar(@$p_u_d['phone_text']) ) {
        $phone_int = commonClass::stringToInt($p_u_d['phone_text']);
        if (strlen($phone_int) == 11 && in_array($phone_int[0], array(7, 8))) {
            if ( $p_u_d['phone_text'] != users::$user_data['phone'] ) {
                $update_array[] = '`phone` = "' . commonClass::stringToPhone(substr($phone_int, 1)) . '"';
                $update_array[] = '`phone_prefix` = ' . (int) substr($phone_int, 1, 3);
            }
        }
    }
    if ( commonClass::checkStringVar(@$p_u_d['email']) && $p_u_d['email'] != users::$user_data['email'] && commonClass::mailCheck($p_u_d['email']) ) {
        $update_array[] = '`email` = "' . dbConn::$mysqli->escape_string(htmlspecialchars(trim($p_u_d['email']))) . '"';
    }
    if ( commonClass::checkStringVar(@$p_u_d['passw']) && $users->createHash($p_u_d['passw']) != users::$user_data['passw'] ) {
        $update_array[] = '`passw` = "' . dbConn::$mysqli->escape_string($users->createHash($p_u_d['passw'])) . '"';
    }
    if ( commonClass::checkNumericVar(@$p_u_d['city'], array_keys(commonClass::$cities_list)) && $p_u_d['city'] != users::$user_data['city'] ) {
        $update_array[] = '`city` = ' . (int) $p_u_d['city'];
    }

    if ( sizeof($update_array) > 0 ) {
        $sql = 'UPDATE `users` SET ' . implode(', ', $update_array) . ' WHERE `id` = ' . (int) users::$user_data['id'];
        if ( $db->query($sql) ) {
            pageLoader::redirectTo('settings?success');
        }
    }
}

echo '
<h1 class="page_title">' . pageLoader::$current_template['title'] . '</h1>
<br class="clear">
<div class="white_with_border">
    ' . ( isset($_GET['success']) ? '<div class="success">Данные обновлены</div>' : '' ) . '
    <form class="account_settings" method="POST">
        <table>
            <tbody>
                <tr>
                    <td><label for="user_last_name">Имя</label></td>
                    <td><input type="text" name="user[last_name]" id="user_last_name" value="' . users::$user_data['last_name'] . '" required></td>
                </tr>
                <tr>
                    <td><label for="user_first_name">Фамилия</label></td>
                    <td><input type="text" name="user[first_name]" id="user_first_name" value="' . users::$user_data['first_name'] . '" required></td>
                </tr>
                <tr>
                    <td><label for="user_phone_text">Телефон</label></td>
                    <td><input type="text" name="user[phone_text]" placeholder="+7 (xxx) xxx-xx-xx" id="user_phone_text" value="' . users::$user_data['phone'] . '" required pattern="\+7(?:\s|-)?\(?\d{3}\)?(?:\s|-)?\d{3}(?:\s|-)?\d{2}(?:\s|-)?\d{2}"></td>
                </tr>
                <tr>
                    <td><label for="user_email">E-mail</label></td>
                    <td><input type="text" name="user[email]" id="user_email" value="' . users::$user_data['email'] . '" required pattern=".*@.*\..{2,}"></td>
                </tr>';

if ( sizeof(commonClass::$cities_list) > 0 ) {
    echo '
                <tr>
                    <td><label for="user_city">Город</label></td>
                    <td>
                        <select name="user[city]" id="user_city" required>';
    foreach (commonClass::$cities_list as $id => $title) {
        echo '<option value="' . $id . '"' . ($id == users::$user_data['city'] ? ' selected' : '') . '>' . $title . '</option>';
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
            </tbody>
        </table>
        <input type="submit" value="сохранить" class="btn green">
    </form>
</div>';