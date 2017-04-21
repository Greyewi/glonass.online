<?php
if ( users::$user_auth ) pageLoader::redirectTo('main');
//todo: check post data

$users->restoreFormParams();
?>
<form action="" method="post" class="register white_with_border">
    <?=$users->formErrorsOutput();?>
    <div class="title">Восстановление пароля</div>
    <label for="restore_u_email">E-mail аккаунта</label><input class="text" id="restore_u_email" type="text" name="restore_u[email]" value="<?=$users->restore_mail;?>" autofocus><br>
<!--    <a href="/register">зарегистрироваться</a><br>-->
    <a href="/login">авторизоваться</a><br>
    <input type="submit" value="Восстановить" class="btn green">
</form>