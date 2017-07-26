<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

Interface module_factory {
    public function __construct(webclient $client);
    public function getModuleByName(String $name);
    public function getModuleById($id);
}

