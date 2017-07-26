<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

Class new_module_storage {

    private $container = array();

    public function get($param) {
        if (!$this->exists($param)) {
            throw new Exception(sprintf("'%s' does not exists in storage"));
        }

        return $this->container[$param];
    }

    public function set($param, $value) {
        if ($this->exists($param)) {
            throw new Exception(sprintf("'%s' is already exists in storage", $param));
        }

        $this->container[$param] = $value;
    }

    public function exists($param) {
        return array_key_exists($param, $this->container);
    }

}