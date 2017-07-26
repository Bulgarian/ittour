<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */
require_once dirname(__FILE__).'/items/hike_search_form.php';
require_once dirname(__FILE__).'/items/package_search_form.php';


class module_search extends module{
    
    CONST version = 2;
    public $sheme, $settings, $config_agency;
    
    protected $css_template_path    = 'css/module_search/module_search.css';
    protected $html_template_path   = 'templates/module_search/module_search_slim.php';
    protected $js_template_path     = 'js/module_search/module_loader.js';
        
    public function __construct($ittour_module){
        $this->ittour_module = $ittour_module;
        
        $this->id_encrypt = '';
        $this->agency_url = urldecode(get('module_location_url'));
        $this->agency_id = $this->ittour_module->get_config('webservice_agency_id');
        
        if(!$this->agency_id) {    
            die("Error! Agency ID not exists.");
        }
        
        // Set biling parameter // 'true' => show new mod search, 'false' => show message 'Service not active'        
        $this->module_new_is_service_active = true;

        $this->config_agency = array(
            'module2_hide_pay_form' => $this->ittour_module->get_config('search_module_module2_hide_pay_form') ? $this->ittour_module->get_config('search_module_module2_hide_pay_form') : "0"// "0"=>НЕТ, "1"=>ДА
          , 'module2_type_select' => $this->ittour_module->get_config('search_module_module2_type_select') ? $this->ittour_module->get_config('search_module_module2_type_select') : "1"// "1"=>Мелкий, "2"=>Крупный
          , 'module2_currency_default' => $this->ittour_module->get_config('search_module_module2_currency_default') ? $this->ittour_module->get_config('search_module_module2_currency_default') : "1" // 1=>"USD", 10=>"EUR", 2=>"UAH"
          , 'scheme' => $this->ittour_module->get_config('search_module_scheme') ? $this->ittour_module->get_config('search_module_scheme') : "7020" // "7020"=>Cине-серый - основной
          , 'background' => $this->ittour_module->get_config('search_module_background') ? $this->ittour_module->get_config('search_module_background') : "F7F5F5" // "F7F5F5"
          , 'border_color' => $this->ittour_module->get_config('search_module_border_color') ? $this->ittour_module->get_config('search_module_border_color') : "3399FF" // "3399FF"
          , 'button_color' => $this->ittour_module->get_config('search_module_button_color') ? $this->ittour_module->get_config('search_module_button_color') : "3399FF" // "3399FF"
          , 'module2_title_color' => $this->ittour_module->get_config('search_module_module2_title_color') ? $this->ittour_module->get_config('search_module_module2_title_color') : "1E1E1E" // "1E1E1E"
          , 'use_white_text_color' => $this->ittour_module->get_config('search_module_use_white_text_color') ? $this->ittour_module->get_config('search_module_use_white_text_color') : "FAFAFA" // "FAFAFA"
          , 'select_color' => $this->ittour_module->get_config('search_module_select_color') ? $this->ittour_module->get_config('search_module_select_color') : "818181" // "818181"
          , 'price_color' => $this->ittour_module->get_config('search_module_price_color') ? $this->ittour_module->get_config('search_module_price_color') : "2272B7" // "2272B7"
          , 'charset' => $this->ittour_module->get_config('webservice_encoding') ? $this->ittour_module->get_config('webservice_encoding') : "utf-8" // "utf-8"
        );
        
        $this->module_code = uniqid(rand(), true);
        $this->is_hike_block_active = false;
        $this->is_package_block_active = true;
        
        $this->getSettings();
        
        // Set defaul data
        $is_ittour = false;
        $is_account = false;
        $custom_logo_url = '';
        $custom_mail_logo = '';
        $advertise_script = '';
        
        require_once($this->ittour_module->get_config('modules_path').'classes/webclient.php');
        
        $params = array('module_type' => 'php'
                  , 'error_handler_function' => 'onerror_func'
                  , 'advertise_script'  => $advertise_script
                  , 'agency_url'        => $this->agency_url
                  , 'agency_id'         => $this->agency_id
                  , 'request_info'      => $_SERVER
                  , 'config_replace'    => array(
                                              'webservice_login'    => $this->ittour_module->get_config('webservice_login')
                                            , 'webservice_password' => $this->ittour_module->get_config('webservice_password')
                                            , 'webservice_encoding' => $this->ittour_module->get_config('webservice_encoding')
                                            , 'webservice_language' => $this->ittour_module->get_config('webservice_language')
                                            , 'search_module_type'  => $this->ittour_module->get_config('search_module_type')
                                            , 'is_ittour'           => $is_ittour
                                            , 'custom_logo_url'     => $custom_logo_url
                                            , 'custom_mail_logo'    => $custom_mail_logo                                            
                                            , 'webservice_cache_type' => $this->ittour_module->get_config('webservice_cache_type')
                                            , 'default_setting'     => $this->ittour_module->get_config('default_setting')
                                            , 'use_hotel_result'    => $this->ittour_module->get_config('use_hotel_result')
                                            , 'show_basket'         => 0
                                            , 'module_page'         => get('module_page')?true:false
                                            , 'preview'             => get('preview')?true:false
                                             )
                  );
        $this->client = new webclient($params);
        $this->client->set_config('search_module_action_with_params', $this->client->get_config('modules_action') . "?module_type=tour_search&id={$this->id_encrypt}&ver=". self::version ."&type={$this->settings->type}&theme={$this->settings->theme}");
        
        $package_search_form_data = $this->client->get_package_search_form_fields();        

        $departure_cities = $this->client->get_optimize_package_departure_city_list(0);
        $first_city_id = safe(array_shift($departure_cities),'id');
        
        $this->package_tour_default_form_value = $this->client->fill_default_package_form_value(null, $package_search_form_data);        
        $this->package_tour_form_field = $this->client->prepare_optimize_package_search_filtered_field('php', 1, array_keys($this->package_tour_default_form_value['country']), array(), array_keys($this->package_tour_default_form_value['hotel_rating']));

        // Add new params 'currency_default' => package
        $this->package_tour_default_form_value = self::checkCurrencyDefault($this->package_tour_default_form_value, $this->config_agency);
        // Add new params 'module2_type_select' => package
        $this->package_tour_default_form_value = self::checkTypeSelectHtml($this->package_tour_default_form_value, $this->config_agency);
        
        // Создание новой картинки для звезд.
        // Get settings and create star image
        $getSheme = $this->getSheme();
        if (safe($getSheme, 'button_color')) {
            // Example array( 'какой цвет красить красить' => 'в какой цвет красить' )
            $color_replace = array(
                                   'r255g217b12' => $getSheme['button_color'],
                                   'r255g216b0' => $getSheme['button_color']
                                  );
            $img_patch = $this->client->get_bg_showcase_image_url('start_active_new_mod', $color_replace, '');
        }
    }
    
    public static function checkCurrencyDefault($params, $config) {
        if (!array_key_exists('currency_default', $params)) {
            
            // Set default
            $params['currency_default'] = ''; // '2' => 'Грн', '1' => 'USD', '10' => 'EUR'
            if (array_key_exists('module2_currency_default', $_GET)) {
                // User change settings in account and click button pre-view
                $params['currency_default'] = $_GET['module2_currency_default'];
            } else {
                // Set currency as config
                if (array_key_exists('module2_currency_default', $config))
                $params['currency_default'] = $config['module2_currency_default'];
            }
        }
        
        return $params;
    }
    
    public static function checkTypeSelectHtml($params, $config) {
        if (!array_key_exists('module2_type_select', $params)) {
            // Set default
            $params['module2_type_select'] = '1'; // '1' => 'мелкий', '2' => 'крупный'
            if (array_key_exists('module2_type_select', $_GET)) {
                // User change settings in account and click button pre-view
                $params['module2_type_select'] = $_GET['module2_type_select'];
            } else {
                // Set currency as config
                if (array_key_exists('module2_type_select', $config))
                $params['module2_type_select'] = $config['module2_type_select'];
            }
        }
        
        return $params;
    }
    
    public function getSettings(){
        
        $result_code = '';
                
        $this->settings->language = 'russian';
        $this->settings->charset  = 'utf-8';    
        $this->settings->type     = 2970;
        $this->settings->scheme   = 7020;
        $this->settings->extended_search_url = '';
        $this->settings->theme = 40;
        
        $dictionary = array();


        $preview = false;
        $agency_url = urldecode(get('module_location_url'));
         
        if(get('module_page')) {
            $module_page = true;
        }        
        
        if($this->config_agency) {
            
            if(isset($this->config_agency['charset'])){
                $this->settings->charset = '';
            }
            if(isset($config_agency['language'])){
                $this->settings->language = $dictionary['language'][$this->config_agency['language']];
            }
            if(isset($config_agency['type'])){
                $this->settings->type = $this->config_agency['type'];
            }
            if(get('type') && is_numeric(get('type'))){
                $this->settings->type = get('type');
            }
            if(isset($config_agency['scheme'])){
                $this->settings->scheme = $this->config_agency['scheme'];
            }
            if(isset($this->config_agency['extended_search_url'])){
                $this->settings->extended_search_url = $this->config_agency['extended_search_url'];
            }   
            if(isset($this->config_agency['theme'])){
                $this->settings->theme = $this->config_agency['theme'];
            }
            if(get('theme') && is_numeric(get('theme'))){
                $this->settings->theme = get('theme');
            }
            if(isset($config_agency['hide_title'])){
                $this->settings->hide_title = $this->config_agency['hide_title'];
            }
        }
        if(get('language')){
          $this->settings->language = $dictionary['language'][get('language')];
        }
        if(isset($this->config_agency['scheme'])){
          $this->settings->scheme = $this->config_agency['scheme'];
        }
        
        if(get('preview') || get('charset')){
            $this->config_agency['use_hotel_result'] = get("use_hotel_result");
            $this->config_agency['show_basket'] = get('dont_show_basket');
        }
                
        $is_account = false;
    }
    
    protected function getSheme(){        
        $module_scheme = array();
        
        foreach ($module_scheme as $name => $value){
            $module_scheme[$name] = $this->config_agency[$name];
        }
        
        $settings_key_name = array('module2_currency_default', 'module2_type_select', 'select_color', 'module2_title_color', 'price_color', 'use_white_text_color', 'main_domain');
        foreach ($settings_key_name as $key_name) {
            if (isset($this->config_agency[$key_name]) && !array_key_exists($key_name, $module_scheme)) {
                $module_scheme[$key_name] = $this->config_agency[$key_name];
            }
        }
        
        // Check new params from set => set new color
        if (get('select_color')) $module_scheme['select_color'] = get('select_color');
        
        // Check new params from set => set new color
        if (get('module2_title_color')) $module_scheme['module2_title_color'] = get('module2_title_color');
                
        // Check new params from set => set new color
        if (get('price_color')) $module_scheme['price_color'] = get('price_color');
        
        // Check new params from set => set new color
        if (get('use_white_text_color')) $module_scheme['use_white_text_color'] = get('use_white_text_color');
        
        // Check new params from set => set new color
        if (get('main_domain')) $module_scheme['main_domain'] = get('main_domain');
        
        $config_agency_scheme_id = $this->config_agency['scheme'];
        $module_scheme = self::setNewDefaultColorSchemes($module_scheme, $config_agency_scheme_id, $this->config_agency);
        
        return $module_scheme; 
    }
    
    protected static function setNewDefaultColorSchemes($module_scheme, $config_agency_scheme_id, $config_agency) {
        // Если у агенства нет еще схемы в настройках => ставим стандартную '7020' => 'Cине-серый - основной'
        if (($config_agency_scheme_id == '') || ($config_agency_scheme_id == null)) $config_agency_scheme_id = 7020;
        
        if ($config_agency_scheme_id == '7032') {
            $module_scheme['background'] = 'ECF3DD';// Example: 'EFFFF4'
            $module_scheme['border_color'] = '0B9D41';
            $module_scheme['button_color'] = '0B9D41';
            $module_scheme['module2_title_color'] = '1E1E1E';
            $module_scheme['use_white_text_color'] = 'FFFFFF';
            $module_scheme['select_color'] = '818181';
            $module_scheme['price_color'] = '2272B7';
        }
        
        if ($config_agency_scheme_id == '7038') {
            $module_scheme['background'] = 'F0E6DD';// Example: 'EFFFF4'
            $module_scheme['border_color'] = 'CC1519';
            $module_scheme['button_color'] = 'CC1519';
            $module_scheme['module2_title_color'] = '1E1E1E';
            $module_scheme['use_white_text_color'] = 'FFFFFF';
            $module_scheme['select_color'] = '818181';
            $module_scheme['price_color'] = '1B8902';
        }
        
        if ($config_agency_scheme_id == '7030') {
            $module_scheme['background'] = 'E8F1B4';// Example: 'EFFFF4'
            $module_scheme['border_color'] = '94B003';
            $module_scheme['button_color'] = '7E9603';
            $module_scheme['module2_title_color'] = '1E1E1E';
            $module_scheme['use_white_text_color'] = 'FFFFFF';
            $module_scheme['select_color'] = '818181';
            $module_scheme['price_color'] = '2272B7';
        }
        
        if ($config_agency_scheme_id == '7006') {
            $module_scheme['background'] = 'DFDBF5';// Example: 'EFFFF4'
            $module_scheme['border_color'] = '8475E1';
            $module_scheme['button_color'] = '7060D8';
            $module_scheme['module2_title_color'] = '1E1E1E';
            $module_scheme['use_white_text_color'] = 'FAFAFA';
            $module_scheme['select_color'] = '818181';
            $module_scheme['price_color'] = '2272B7';
        }
        
        if ($config_agency_scheme_id == '7000') {
            $module_scheme['background'] = 'F0F0F0';// Example: 'EFFFF4'
            $module_scheme['border_color'] = '787476';
            $module_scheme['button_color'] = '787476';
            $module_scheme['module2_title_color'] = '1E1E1E';
            $module_scheme['use_white_text_color'] = 'FFFFFF';
            $module_scheme['select_color'] = '818181';
            $module_scheme['price_color'] = '1B8902';
        }
        
        if ($config_agency_scheme_id == '7034') {
            $module_scheme['background'] = 'DCEBFA';// Example: 'EFFFF4'
            $module_scheme['border_color'] = '3399FF';
            $module_scheme['button_color'] = '3399FF';
            $module_scheme['module2_title_color'] = '1E1E1E';
            $module_scheme['use_white_text_color'] = 'FAFAFA';
            $module_scheme['select_color'] = '818181';
            $module_scheme['price_color'] = '2272B7';
        }
        
        if ($config_agency_scheme_id == '7020') {
            $module_scheme['background'] = 'F7F5F5';// Example: 'EFFFF4'
            $module_scheme['border_color'] = '3399FF';
            $module_scheme['button_color'] = '3399FF';
            $module_scheme['module2_title_color'] = '1E1E1E';
            $module_scheme['use_white_text_color'] = 'FAFAFA';
            $module_scheme['select_color'] = '818181';
            $module_scheme['price_color'] = '2272B7';
        }
        
        // Цвета из конфига .ini
        if ($config_agency['background'] != '') $module_scheme['background'] = $config_agency['background'];
        if ($config_agency['border_color'] != '') $module_scheme['border_color'] = $config_agency['border_color'];
        if ($config_agency['button_color'] != '') $module_scheme['button_color'] = $config_agency['button_color'];
        if ($config_agency['module2_title_color'] != '') $module_scheme['module2_title_color'] = $config_agency['module2_title_color'];
        if ($config_agency['use_white_text_color'] != '') $module_scheme['use_white_text_color'] = $config_agency['use_white_text_color'];
        if ($config_agency['select_color'] != '') $module_scheme['select_color'] = $config_agency['select_color'];
        if ($config_agency['price_color'] != '') $module_scheme['price_color'] = $config_agency['price_color'];
        
        return $module_scheme;
    }
    
    /*
     * Generate custom js for this module
     */
    public function getJsCode(){
        $js = new js_generator($this, $this->js_template_path);
        return $js->handler();
    }
    
    
    /*
     * Generate custom css for this module
     */ 
    public function getCssCode(){
        
        $module_scheme = $this->getSheme();

        $css = new css_generator($this, $this->css_template_path, $module_scheme);
        return $css->handler();
    }
    
    
    /*
     * Generate MAIN HTML template
     */ 
    public function getHtmlCode(){
        
        $template = new html_generator($this, $this->html_template_path);
        return $template->handler();
    }
    
    
    /*
     * Package Form. Use in MAIN HTML template
     */
    public function getPackageSearchForm(){
        
        $template = new template();
        $template->setDir($this->getPath());
        
        $package_search_form = new package_search_form($this->client, $this->package_tour_form_field, $this->package_tour_default_form_value,  $this->is_package_block_active);
        $moduleLoader = new new_module_loader($package_search_form, $template);
        
        return $moduleLoader->render();
    }
    
    
    /*
     * Hike Form. Use in MAIN HTML template
     */
    public function getHikeSearchForm(){
          
        $template = new template();
        $template->setDir($this->getPath());
        
        $hike_search_form = new hike_search_form($this->client, $this->hike_tour_form_field, $this->hike_tour_default_form_value, $this->is_hike_block_active);
        $moduleLoader = new new_module_loader($hike_search_form, $template);
        
        return $moduleLoader->render();
    }
    
    
     /*
     * Return path to module
     */
    public function getPath(){
        return $this->ittour_module->get_config('modules_path') . 'new/';
    }
}
