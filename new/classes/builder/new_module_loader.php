<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

class new_module_loader {

    private $template, $module;

    function __construct($module, Template $template) {
        $this->module = $module;
        $this->template = $template;
    }

    public function render() {
        return $this->template->render($this->module->getTemplateFilename(), $this->module->getVariablesArray());
    }
}