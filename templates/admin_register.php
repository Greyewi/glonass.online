<?php
if (!users::isAdmin()) pageLoader::redirectTo('login');

$users->registerFormParams();
?>
<form action="" method="post" class="register white_with_border">
    <?=$users->formErrorsOutput();?>
    <div class="title"><?=$pageLoader::$current_template['title']?></div>
    <label for="reg_u_email">E-mail</label><input class="text" id="reg_u_email" type="text" name="reg_u[email]" value="<?=$users->reg_u['email'];?>" placeholder="название_ящика@домен" autofocus required pattern=".*@.*\..{2,}"><br>
    <label for="reg_u_fname">Фамилия</label><input class="text" id="reg_u_fname" type="text" name="reg_u[first_name]" value="<?=$users->reg_u['first_name'];?>" required><br>
    <label for="reg_u_lname">Имя</label><input class="text" id="reg_u_lname" type="text" name="reg_u[last_name]" value="<?=$users->reg_u['last_name'];?>" required><br>
    <label for="reg_u_phone">Телефон</label><input class="text" id="reg_u_phone" type="text" name="reg_u[phone_text]" value="<?=$users->reg_u['phone_text'];?>" placeholder="+7 (xxx) xxx-xx-xx" required pattern="\+7(?:\s|-)?\(?\d{3}\)?(?:\s|-)?\d{3}(?:\s|-)?\d{2}(?:\s|-)?\d{2}"><br>
    <?php
    if ( sizeof(commonClass::$cities_list > 0) ) {
        echo '<label for="reg_u_city">Город</label><select id="reg_u_city" name="reg_u[city]">';
        foreach (commonClass::$cities_list as $id => $title) {
            echo '<option value="' . $id . '"' . ($id == $users->reg_u['city'] ? ' selected' : '') . '>' . $title . '</option>';
        }
        echo '</select><br>';
    }
    ?>
    <div class="passw"><label for="reg_u_passw">Пароль</label><input class="text<?=(array_key_exists('passw', $users->form_errors) ? ' error' : '');?>" id="reg_u_passw" type="password" name="reg_u[passw]" value="" required><span class="fa fa-eye"></span></div>
    <div><label for="reg_u_passw_conf">Подтверждение пароля</label><input class="text<?=(array_key_exists('passw_conf', $users->form_errors) ? ' error' : '');?>" id="reg_u_passw_conf" type="password" name="reg_u[passw_conf]" value="" required></div><br>
    <input type="submit" value="Зарегистрировать" class="btn green">
</form>