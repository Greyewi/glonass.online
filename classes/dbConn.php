<?php

class dbConn
{
    /*
    static $settings = array(
        'host' => 'localhost',
        'user' => 'watest_ttriru',
        'pass' => 'jdcpgzbkd',
        'base' => 'watest_ttriru'
    );
    */
    static $settings = array(
        'host' => 'localhost',
        'user' => 'ttrilk_test',
        'pass' => 'v6QQNavV',
        'base' => 'ttrilk_test'
    );

    static $settings_local = array(
        'host' => '127.0.0.1',
        'user' => 'root',
        'pass' => '',
        'base' => 'ttrilk_test'
    );

    static $mysqli;

    function __construct()
    {
        $this->connect();
    }

    function connect()
    {
        $settings = self::$settings_local;
        self::$mysqli = new mysqli($settings['host'], $settings['user'], $settings['pass'], $settings['base']);
        if (self::$mysqli->connect_errno) {
            die("MySQL connect error: " . self::$mysqli->connect_error);
        } else {
            self::$mysqli->query('SET NAMES utf8');
        }
    }

    function query($q)
    {
        $ret = false;
        if (strlen($q) > 0) {
            if (!$ret = self::$mysqli->query($q)) {
                die("MySQL query error: " . self::$mysqli->error);
            }
        }
        return $ret;
    }
}