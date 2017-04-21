<?php
/**
 * Created by PhpStorm.
 * User: Dain
 * Date: 05.12.2016
 * Time: 0:38
 */
if ( !users::$user_auth ) pageLoader::redirectTo('login');

users::dropUserHash();

pageLoader::redirectTo('login');