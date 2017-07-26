<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

Class generator implements new_concrete_module {
        
    public $module, $params;
    
    public function __construct(module $module, $template_filename, $params = array()) {
        $this->module = $module;
        $this->params = $params;
        $this->template_filename = $template_filename;
        
        $this->addParams(array('module' => $this->module));
    }
    
    public function handler(){

        if(file_exists($this->module->getPath().$this->getTemplateFilename())){
            $template = new template();
            $template->setDir($this->module->getPath());

            $moduleLoader = new new_module_loader($this, $template);
            return $moduleLoader->render();
        }
        return null;
    }    
    
    public function getTemplateFilename(){
        return $this->template_filename;
    }    
    
    public function getVariablesArray() {
        return $this->params;
    }
    
    public function addParams($additional_params = array()){
        $this->params = array_merge($this->params, (array)$additional_params);
    }
    
    public function removeParams($removing_params = array()){
        $removing_params = array_flip((array)$removing_params);
        $this->params = array_diff_key($this->params, $removing_params) + array_diff_key($removing_params, $this->params);
    }
}