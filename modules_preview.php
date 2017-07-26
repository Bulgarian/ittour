<?php
print"HERE!!";
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

//error_reporting(E_ALL);
error_reporting(E_ALL ^ E_DEPRECATED);
//error_reporting(0);

ini_set('memory_limit', '200M');
function onerror_func($error_code, $error_text) { die($error_text); }

session_start();
require_once('classes/webclient.php');
require_once('new/classes/module.php');
require_once('new/classes/factory/module_search_factory.php');
require_once('new/classes/module_creator.php'); 

// Create Webclient
$search_module['params'] = array('error_handler_function' => 'onerror_func');
$search_module['client'] = new webclient(array('error_handler_function' => 'onerror_func'));

// Create module search
$module_search_factory = new module_search_factory($search_module['client']);
$module = $module_search_factory->getModuleById(2970);
$module_creator = new module_creator($module);
$html = $module_creator->show();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $search_module['client']->get_config('webservice_encoding'); ?>" />
    <title><?php echo iconv('utf-8', $search_module['client']->get_config('webservice_encoding'), 'Система Поиска и Бронирования Туров - IT-tour');?></title>    
</head>
<body>
    <div id="tour_search_module_mod2">
      <?php echo $module_creator->getHtml(); ?>
    </div>
    <script type="text/javascript"><?php echo $html; ?></script>
    
    <script type="text/javascript">
        var script;
        
        function loadJqueryAndStartModSearch(filename) {
          script = document.createElement('script');
          script.type = 'text/javascript';
          script.src = filename;
          document.getElementsByTagName('head')[0].appendChild(script);
          script.onload = function(){
            load_js_and_html_for_jsx();
          };
        };
        
        loadJqueryAndStartModSearch('<?php //echo $search_module['client']->get_config('modules_url'); ?>new/js/jquery-1.7.2.min.js');
    </script>    
</body>
</html>
