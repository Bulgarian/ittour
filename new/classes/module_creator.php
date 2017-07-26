<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

require_once dirname(__FILE__).'/builder/template.php';
require_once dirname(__FILE__).'/builder/new_module_loader.php';
require_once dirname(__FILE__).'/builder/new_concrete_module.php';

Class module_creator implements new_concrete_module {
    
    protected $module;
    protected $template = 'js/main.js';
    
    public function __construct(module $module){
        $this->module = $module; 
    }    
    
    public function show(){
        $template = new template();
        $template->setDir($this->module->getPath());

        $moduleLoader = new new_module_loader($this, $template);
        return $moduleLoader->render();
    }
    
    public function getTemplateFilename(){
        return $this->template;
    }    
    
    public function getVariablesArray() {
        
        return array(
            'html' => $this->getHtmlCode(), 
            'css'  => $this->getCssCode(),
            'js'   => $this->getJsCode(),
        );
    }    
    
    public function getHtml(){
        
        return $this->module->getHtmlCode();
    }
    
    private function getHtmlCode(){
        
        return get_js_from_html($this->module->getHtmlCode(), 'tour_search_module_mod2');
    }    
    
    private function getCssCode(){
        
        return str_replace(array("\n", "\r"), "", $this->module->getCssCode());
        
    }
    
    private function getJsCode(){
        
        return $this->module->getJsCode();
        
    }
}