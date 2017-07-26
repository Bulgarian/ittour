<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

class custom_cached_manager {
    public function __construct() {
    }
    
    public function get($key) {
        $result = false;
        if (!empty($_SESSION[$key])) $result = $_SESSION[$key];
        return $result;
    }
    
    public function set($key, $value, $lifetime = null) {
        $result = true;        
        $_SESSION[$key] = $value;
        return $result;
    }
}