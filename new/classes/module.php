<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

require_once dirname(__FILE__).'/builder/new_concrete_module.php';
require_once dirname(__FILE__).'/builder/new_module_loader.php';
require_once dirname(__FILE__).'/builder/new_module_storage.php';
require_once dirname(__FILE__).'/builder/template.php';

require_once dirname(__FILE__).'/generator/css_generator.php';
require_once dirname(__FILE__).'/generator/html_generator.php';
require_once dirname(__FILE__).'/generator/js_generator.php';

Class Module {    
    public function getSettings(){}
    public function getCssCode(){}
    public function getJsCode(){}
    public function getHtmlCode(){}
}