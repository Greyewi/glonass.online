<?php
if ( users::$user_auth ) pageLoader::redirectTo('main');
$users->loginFormParams();
?>
<form action="" method="post" class="register white_with_border">
    <?=$users->formErrorsOutput();?>
    <div class="title">Авторизация</div>
    <label for="login_u_email">E-mail</label><input class="text" id="login_u_email" type="text" name="login_u[email]" value="<?=$users->login_u['email'];?>" autofocus required pattern=".*@.*\..{2,}"><br>
    <label for="login_u_passw">Пароль</label><input class="text" id="login_u_passw" type="password" name="login_u[passw]" value="" required><br>
<!--    <a href="/register">зарегистрироваться</a><br>-->
    <a href="/restore">забыли пароль?</a><br>
    <input type="submit" value="Войти" class="btn green">
</form>