<?php

/**
 * Created by PhpStorm.
 * User: Dain
 * Date: 25.11.2016
 * Time: 18:51
 */
class pageLoader
{
    static $GET_query_string = '';
    static $path_parts = array();
    static $current_template = array(
        'url' => 'main'
    );
    static $pages = array(
        'main' => array(
            'title' => 'Стоимость'
        ),
        '404' => array(
            'title' => 'Страница не найдена'
        ),
        '401' => array(
            'title' => 'Доступ запрещен'
        ),
        'register' => array(
            'title' => 'Регистрация'
        ),
        'login' => array(
            'title' => 'Авторизация'
        ),
        'logout' => array(
            'title' => 'Выход'
        ),
        'restore' => array(
            'title' => 'Восстановление пароля'
        ),
        'settings' => array(
            'title' => 'Настройки аккаунта'
        ),
        'balance' => array(
            'title' => 'Баланс'
        ),
        'orders' => array(
            'title' => 'История заказов'
        ),
        'presentation' => array(
            'title' => 'Презентационные материалы'
        ),

        //admin pages
        'admin_orders' => array(
            'title' => 'Заявки'
        ),
        'admin_users' => array(
            'title' => 'Менеджеры'
        ),
        'admin_user' => array(
            'title' => 'Данные менеджера'
        ),
        'admin_register' => array(
            'title' => 'Новый менеджер'
        ),
        'admin_rewards' => array(
            'title' => 'Выплаты'
        ),
    );
    public static $tmp_path = 'templates';

    // pagination setup
    public static $current_page = 1;
    public static $entities_per_page = 10;
    public static $per_page_array = array(1,2,5,10,15,20,30,100);
    public static $pages_range = 2;


    function __construct()
    {
        $this->__parseURL();
    }

    function __parseURL()
    {
        $url_parts = parse_url($_SERVER['REQUEST_URI']);
        self::$GET_query_string = isset($url_parts['query']) && strlen($url_parts['query']) > 0 ? $url_parts['query'] : '';
        self::$path_parts = explode('/', trim($url_parts['path'], '/'));
        if (sizeof(self::$path_parts) > 1) {
            self::$path_parts = array('404');
        }
    }

    function setTemplate()
    {
        $url = self::$path_parts[0];
        if (strlen($url) == 0) {
            self::$current_template['url'] = 'main';
            self::$current_template = array_merge(self::$current_template, self::$pages['main']);
        } else if (array_key_exists($url, self::$pages)) {
            self::$current_template['url'] = $url;
            self::$current_template = array_merge(self::$current_template, self::$pages[$url]);
        } else {
            //$this->redirectTo('404', 404);
            self::$current_template['url'] = '404';
            self::$current_template = array_merge(self::$current_template, self::$pages['404']);
        }

        $ret = self::$tmp_path.'/'.self::$current_template['url'].'.php';
        if (!file_exists($ret)) die('No tmp file found (' . self::$current_template['url'] . ')');
        return $ret;
    }

    static function redirectTo($page = '', $code = 301, $clear_get_query = false)
    {
        $url_parts = array('http://' . $_SERVER['SERVER_NAME']);

        if (strlen($page) > 0) {
            $url_parts[] = trim($page, '/');
        }

        /*
        switch ($code) {
            case 200:
                $code_text = 'OK';
                break;
            case 301:
                $code_text = 'Moved Permanently';
                break;
            case 401:
                $code_text = 'Unauthorized';
                break;
            case 404:
                $code_text = 'Not Found';
                break;
            default:
                $code_text = 'Found';
        }
        */

        $page = implode('/', $url_parts) . (strlen(self::$GET_query_string) > 0 && !$clear_get_query ? '?' . self::$GET_query_string : '');
        session_write_close();
        header('Location: ' . $page, true, $code);
        exit();
    }

    static function setEntitiesPerPage($entities_count)
    {
        $entities_per_page = 10;

        if ( $entities_count > 0 ) {
            if (commonClass::checkNumericVar(@$_COOKIE['per_page'], self::$per_page_array)) {
                $entities_per_page = (int)$_COOKIE['per_page'];
            }
            if (commonClass::checkNumericVar(@$_GET['per_page'], self::$per_page_array)) {
                $entities_per_page = (int)$_GET['per_page'];
                setcookie('per_page', $entities_per_page, strtotime('+30 days'));
            }
        }
        self::$entities_per_page = $entities_per_page;
    }

    static function setCurrentPage($entities_count)
    {
        $page = 1;

        if ( $entities_count > 0 ) {

            $entities_per_page = self::$entities_per_page;
            $pages_count = ceil($entities_count / $entities_per_page);

            if (isset($_COOKIE['page']) && is_numeric($_COOKIE['page']) && $_COOKIE['page'] > 0 && $_COOKIE['page'] <= $pages_count) {
                $page = (int)$_COOKIE['page'];
            }
            if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $pages_count) {
                $page = (int)$_GET['page'];
                setcookie('page', $page, strtotime('+30 days'));
            }
        }
        self::$current_page = $page;
    }

    static function paginationOutput($entities_count)
    {
        $ret = '';
        $current_page = self::$current_page;
        $entities_per_page = self::$entities_per_page;
        $pages_count = (int) ceil($entities_count / $entities_per_page);
        $pages_range = self::$pages_range;

        if ( $pages_count > 1 ) {

            $ret .= '<div class="pagination">';
            
            $arrow_left = $current_page > 1 ? '<a href="?page=' . ($current_page - 1) . '">«</a>' : '';
            $arrow_right = $current_page < $pages_count ? '<a href="?page=' . ($current_page + 1) . '">»</a>' : '';

            $range_start = max(1, $current_page - $pages_range);
            $range_end = min($pages_count, $current_page + $pages_range);

            $ret .= $arrow_left;

            if ($range_start > 1) {
                $ret .= '<a href="?page=1">1</a>';
                if ($range_start > 2) {
                    $ret .= '<span class="dots">...</span>';
                }
            }
            
            for ( $page = $range_start; $page <= $range_end; $page++ ) {
                $ret .= $page == $current_page ? '<span class="active">' . $page . '</span>' : '<a href="?page=' . $page . '">' . $page . '</a>';
            }
            
            if ( $range_end < $pages_count ) {
                if ( $range_end < ($pages_count - 1) ) {
                    $ret .= '<span class="dots">...</span>';
                }
                $ret .= '<a href="?page=' . $pages_count . '">' . $pages_count . '</a>';
            }

            $ret .= $arrow_right;
            
            $ret .= '</div>';
        }
        return $ret;
    }
    
    static function perPageOutput()
    {
        $ret = '<form class="entities_per_page"><label for="entities_per_page">Записей на странице: </label><select id="entities_per_page" name="per_page">';
        foreach (self::$per_page_array as $per_page) {
            $ret .= '<option value="' . $per_page . '"' . ( self::$entities_per_page == $per_page ? ' selected' : '' ) . '>' . $per_page . '</option>';
        }
        $ret .= '</select></form>';
        
        return $ret;
    }

    static function getSQLLimit()
    {
        return 'LIMIT ' . ( pageLoader::$entities_per_page * (pageLoader::$current_page - 1) ) . ', ' . pageLoader::$entities_per_page;
    }
}