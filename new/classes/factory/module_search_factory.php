<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

require_once dirname(__FILE__).'/module_factory.php';


Class module_search_factory implements module_factory{
    
    private $client;
    
    
    public function __construct(webclient $client){  
        $this->client = $client;
    }
    
    /*
     * Getting module creator by name
     * param String $name
     */
    public function getModuleByName(String $name){
        $method_name = 'create'.str_replace('x', '_', $name);
        
        if(method_exists ($this, $method_name)){
            return $this->{$method_name}();
        }else{
            throw new Exception(sprintf('Module, with name "%s", was not found', $method_name));
        }
    }
    
    /*
     * Getting module creator by id
     * param String $name
     */
    public function getModuleById($type_id){ 
        switch ($type_id) {
            case '2970':
                return $this->create650_375();
            break;
            default:
                return $this->create650_375();                
            break;
        }
    }
    
    private function create650_375(){
        return $this->createModule('module_search_type1');
    }
    
    /*
     * Getting module creator by id
     * param String $name
     */
    private function createModule($name){
        
        $path = 'new/classes/module_search/'.$name.'.php';
        
        if(file_exists($path)){
            include $path;
        }else{
            throw new Exception(sprintf("File '%s', was not found", $name));
        }
        
        return new $name($this->client);
    }
}
