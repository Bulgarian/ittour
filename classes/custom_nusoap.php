<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

require_once(dirname(__FILE__).'/nusoap.php');
class custom_nusphere_soapclient extends nusphere_soapclient {
  function custom_nusphere_soapclient($endpoint, $wsdl = false) {  
    parent::nusphere_soapclient($endpoint, $wsdl);    
  }    
  
  function setError($str){
    die($str);
  }
}

?>