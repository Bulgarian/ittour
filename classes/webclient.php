<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

set_time_limit(0);
require_once(dirname(__FILE__).'/webclient_util.php');
require_once(dirname(__FILE__).'/api_client.php');
if(!class_exists('FastJSON'))
    require_once(dirname(__FILE__).'/fastjson.php');
require_once(dirname(__FILE__).'/../new/templates/results/package_tour_search_result_table.php');
require_once(dirname(__FILE__).'/../new/templates/results/package_tour_order_form_mod2.php');
require_once(dirname(__FILE__).'/../new/templates/results/html_form_buy_all.php');
require_once(dirname(dirname(__FILE__)).'/templates/language.php');
require_once(dirname(__FILE__).'/captcha/itt_captcha.php');
if(!class_exists('phpmailer'))
  require_once(dirname(__FILE__).'/mail/class.phpmailer.php');
if(!class_exists('smtp'))
  require_once(dirname(__FILE__).'/mail/class.smtp.php');
require_once(dirname(__FILE__).'/custom_cached_manager.php');

// Set module version
$_GET['ver'] = 2;

class webclient {
  
  var $proxy = null; // soap-client
  var $is_connected = true;
  var $wsdl;
  var $wsdl_login;
  var $wsdl_password;
  var $client_version = '1.1';
  var $use_cache = false;
  var $cache_type = 'file'; // session, file, memcached, db
  var $auth_token = 'd0f84f1f30ba54853f4b790be4ec5a8b'; 
  var $session_lifetime = 3600; // 1 час
  var $validat_lifetime = 3600; // 1 час
  var $permanent_lifetime = 86400; // 1 сутки
  var $is_error = false; 
  var $error_message = ''; 
  var $modules_url = '';
  var $error_handler_function = null;
  var $module_type = 'php';
  var $debug_info = array();
  var $webservice = null;
  var $package_search_form_data = array();
  var $hike_search_form_data = array();
  var $config = array();
  var $db;
  var $itt_captcha;
  var $agency_url;
  var $save_statistic = false;
  var $show_cart = false;
  var $show_search_banner = false;
  
  var $choosed_currency_id = 1; // default choosed currency id is USD if we can not determine it

  function webclient($params = array()) {
    
    $config_file_name = dirname(dirname(__FILE__)) . '/config/'.get('custom_config',safe($params, 'custom_config')).'config.ini';
    if(file_exists($config_file_name)) {
      $this->config = parse_ini_file($config_file_name);
    } else {
      $this->config = parse_ini_file(dirname(dirname(__FILE__)) . '/config/config.ini');
    }
    if(isset($params['config_replace']))
      $this->config = array_merge($this->config, $params['config_replace']);    

    $this->wsdl = $this->config['webservice_url'];
    $this->wsdl_login = $this->config['webservice_login'];
    $this->wsdl_password = $this->config['webservice_password'];
    $this->use_cache = $this->config['webservice_use_cache'];
    if(!$this->use_cache)
      $this->cache_type = 'file';
    else
      $this->cache_type = $this->config['webservice_cache_type'];
    $this->session_lifetime = $this->config['webservice_cache_lifetime'];
    $this->modules_url = $this->config['modules_url'];  

    $this->show_search_banner = $this->get_config('show_search_banner');
    
    $this->lang = get_translate($this->get_config('webservice_language'));
    array_walk_recursive($this->lang, 'change_charset_array', $this);

    $this->error_handler_function = safe($params, 'error_handler_function');

    if($this->use_cache) {
      if($this->config['webservice_cache_type'] == 'db') {
        $this->db = mysql_connect($this->config['webservice_cache_db_host'], $this->config['webservice_cache_db_user'], $this->config['webservice_cache_db_password']);
        if(mysql_errno()) $this->error('10', mysql_error($this->db));
        mysql_select_db($this->config['webservice_cache_db_database_name'], $this->db);
        if(mysql_errno()) $this->error('10', mysql_error($this->db));
        mysql_query('SET NAMES '.$this->get_config('webservice_cache_db_charset'), $this->db);
      } elseif($this->config['webservice_cache_type'] == 'file') {
        if(!file_exists(dirname(dirname(__FILE__)) . "/_tmp"))
          $this->error('11', 'Для работы модулей необходимо создать каталог "_tmp" для кеширования данных');
      }
    }

    //get and set constants
    $this->request_info = safe($params, 'request_info');
  
    $this->get_default_constant_optimize();

    $this->agency_url   = safe($params, 'agency_url');
    $this->agency_id    = $this->config['webservice_agency_id'];
 
    $this->is_show_cart();    
    header('Content-Type: text/html; charset=' . $this->get_config('webservice_encoding'));

    $json = new FastJSON;
    
    if(get('_ittmd') == 'itt_captcha')
      $this->prepare_captcha();

    //ajax
    if(get('action') == 'package_tour_search') {
      $this->save_statistic = true;
      $search_array = $this->parse_package_search_url($_SERVER['QUERY_STRING']);
      $search_array = $this->prepare_package_search_array($search_array);
      $search_array['truncate'] = true;
      
      $p1 = '';
      
      // Add params ver for use new iterator
      $search_array['module_version'] = 2;
      $search_array['get_info_from_otpusk_table'] = true;
      $search_result = $this->package_tour_search($search_array);

      $html = '';
      if(!safe($search_array, 'instantsearch')) {
        $filter = safe($search_result, 'filter');
        if(isset($search_array['requested_tours'][0])) $filter['requested_tours'] = $search_array['requested_tours'][0];
        if(safe($_GET, 'ver') && ($_GET['ver'] == 2)) {
          $html = get_package_search_result_hotel_table_new_mod($this, $filter, $search_result);
        }
      }
      
      $p2 = '';
      
      if(get('callback')) {
        echo get('callback') . '(' . $json->encode(array('mem' => $p2-$p1, 'text' => $html)) . ');';
      } else {
        echo $json->encode(array('mem' => $p2-$p1, 'text' => $html));
      }
      exit;
    }

    if(get('action') == 'package_tour_cart') {
      $search_array = array();
      $search_array['truncate'] = true;
      $requested_tours = explode(' ', get('ids'));
      foreach($requested_tours as $requested_tour){
        if($tour = explode('-', $requested_tour)){
          $search_array['requested_tours'][$tour[1]][] =  $tour[0];
        }
      }
      $p1 = '';
      $search_result = $this->package_tour_search($search_array);

      $html = '';
      
      if(!safe($search_array, 'instantsearch')) {
        $filter = safe($search_result, 'filter');

        if($this->get_config('use_hotel_result')){  
          $html = get_package_search_result_hotel_table($this, $filter, $search_result, true);
        }else{
          $html = get_package_search_result_table($this, $filter, $search_result, true);
        }
      }
      $p2 = '';
  
      echo get('callback') . '(' . $json->encode(array('mem' => $p2-$p1, 'text' => $html)) . ');';
      exit;
    }
    if(get('action') == 'debug_message_send') {
      $this->debug_message_send(get('message_content'));
      exit;
    }
    if(get('action') == 'hike_tour_search') {
      $this->save_statistic = true;
      $search_array = $this->parse_hike_search_url($_SERVER['QUERY_STRING']);
      $search_array = $this->prepare_hike_search_array($search_array);
      $search_result = $this->hike_tour_search($search_array);
      $filter = $search_result['filter'];
      
      // Check mod version
      if (safe($_GET, 'ver') && ($_GET['ver'] == 2)) {
        // New mod search (mod 2)
        $html = get_hike_search_result_table_mod2($this, $filter, $search_result);
      } else {
        // Old mod search
        $html = get_hike_search_result_table($this, $filter, $search_result);
      }
      
      echo get('callback') . '(' . $json->encode(array('text' => $html)) . ');';
      exit;
    }
    
    if(get('action') == 'get_package_tour_order_form') {
      $this->prepare_captcha();
      $this->itt_captcha->reset();
      $info_message = null;
      $tour_id = get('tour_id');
      $sharding_rule_id = get('sharding_rule_id');
      $is_showcase = get('is_showcase');
      if(!is_numeric($tour_id)) {
        $info_message = $this->lang['sorry_tour_not_found'];
      } else {
        $search_array['requested_tours'][$sharding_rule_id] = array($tour_id);
        if(get('type') < 1000) {
          $search_array['get_config_from_showcase'] = get('type');
        }
        $search_array['dont_show_hotel_external_links'] = $this->get_config('dont_show_hotel_external_links');
        $search_result = $this->package_tour_search($search_array);
        if(!isset($search_result['offers']) || count($search_result['offers']) == 0) {
          $info_message = $this->lang['sorry_tour_not_found'];
        }
      }
      $html = get_package_tour_order_form($this, $info_message, $search_result,$is_showcase);
      if(strpos($_SERVER['HTTP_REFERER'], 'www.ittour.com.ua') !== false){
        $html = iconv($this->get_config('webservice_encoding'), 'utf-8', $html);
        $charset = 'utf-8';
      } else if(get('chrs')){
        $html = iconv($this->get_config('webservice_encoding'), get('chrs'), $html);
        $charset = get('chrs');
      } else {
        $charset = $this->get_config('webservice_encoding');
      }
      header("Content-Type: application/json");
      header("Accept-Charset: ".$charset);
      echo get('callback') . '(' . $json->encode(array('text' => $html)) . ');';
      exit;
    }
    
    // Action for 'new module search'
    if(get('action') == 'get_package_tour_order_form_mod2'){
      $this->prepare_captcha();
      $this->itt_captcha->reset();
      $info_message = null;
      $tour_id = get('tour_id');
      $sharding_rule_id = get('sharding_rule_id');
      if(!is_numeric($tour_id)){
        $info_message = $this->lang['sorry_tour_not_found'];
      } else {
        $search_array['requested_tours'][$sharding_rule_id] = array($tour_id);
        if(get('type') < 1000) {
          $search_array['get_config_from_showcase'] = get('type');
        }
        $search_array['get_info_from_otpusk_table'] = true;// Get info from otpusk
        $search_array['tour_view'] = 1; // Параметр для сбора статистики
        $search_result = $this->package_tour_search($search_array);

        if(!isset($search_result['offers']) || count($search_result['offers']) == 0) {
          $info_message = $this->lang['sorry_tour_not_found'];
        }
      }
      $html = get_package_tour_order_form_mod2($this, $info_message, $search_result);

      if(get('callback')) {
        echo get('callback') . '(' . $json->encode(array('text' => $html)) . ');';
      } else {
        echo $json->encode(array('text' => $html));
      }
      exit;
    }
    
    if(get('action') == 'get_package_validate_tour') {
      $tour_id = get('tour_id');
      $sharding_rule_id = get('sharding_rule_id');
      $search_result = array();
      $validate_result = $this->package_validate_tour($tour_id, $sharding_rule_id);
      if(get('callback')) {
        echo get('callback') . '(' . $json->encode(array('validate' => $validate_result)) . ');';
      } else {
        echo $json->encode(array('validate' => $validate_result));
      }
      exit;
    }
    
    if(get('action') == 'captcha_refresh') {
      $this->prepare_captcha();
      $this->itt_captcha->reset();
      $ittour_url = 'http://www.ittour.com.ua/';
      $url = $ittour_url . $this->itt_captcha->url();
      
      header("Content-Type: application/json");
      header("Accept-Charset: ".$this->get_config('webservice_encoding'));
      if(get('callback')) {
        echo get('callback') . '(' . $json->encode(array('url' => $url)) . ');';
      } else {
        echo $json->encode(array('url' => $url));
      }
      exit;
    }
    
    if(get('action') == 'package_tour_order_submit') {
      
      $search_array['requested_tours'][get('sharding_rule_id')] = array(get('tour_id'));
      $search_result = $this->package_tour_search($search_array);
      array_walk_recursive($_GET, 'change_charset_array', $this);
      $second_name = get('second_name');
      $first_name = get('first_name');
      $middle_name = get('middle_name');
      $get_price = get('input_price');
      $get_currency_id = get('input_currency_id');
      $city = get('city');
      $email = '';
      if(preg_match('/^[a-z0-9\_\.\-]+\@[a-z0-9\-]+\.[a-z0-9\.\-]+$/Usi', get('email'))) {
        $email = get('email');
      }
      $comment = get('comment');
      
      // Use in new mod search
      if (get('phone_code')) {
        // New mod search
        $phone_code = get('phone_code');
        $phone = $phone_code . get('phone');
      } else {
        // Old mod search
        $phone = get('phone');
      }
      
      $err = array();
      if(!count($search_result['offers'])) {
        $err[] = $this->lang['tour_not_valid'];
      } else {
        if(empty($first_name) || $first_name == $this->lang['first_name'])
          $err[] = $this->lang['input_first_name'];
        if(empty($city) || $city == $this->lang['city'])
          $err[] = $this->lang['input_city'];
        if(empty($phone) || $phone == $this->lang['phone'])
          $err[] = $this->lang['input_phone'];
        if(empty($email) || $email == $this->lang['email'])
          $err[] = $this->lang['input_email'];
        if(isset($_COOKIE['package_tour_order_submit']))
          $err[] = $this->lang['get_order_time'];
        
        // Check captcha
        $captcha_text_md5 = trim(get('value_code'));
        $user_input_captcha_md5 = md5(md5(strtolower(trim(get('captcha')))));
        if ($captcha_text_md5 != $user_input_captcha_md5)
          $err[] = $this->lang['input_captcha'];
      }
      if(count($err) == 0) {
        //send mail
        setcookie("package_tour_order_submit", 1, time()+60);
        $offer = $search_result['offers'][0];
        $mail_agency_order_message = file_get_contents(dirname(dirname(__FILE__)) . '/templates/mail_agency_package_tour_order_message.html');
        $mail_agency_order_message = @iconv('UTF-8', $this->get_config('webservice_encoding') . '//IGNORE', $mail_agency_order_message);

        $custom_logo_url = 'https://www.ittour.com.ua/';
        $custom_mail_logo = 'mail-logo.jpg';
        if($this->get_config('custom_logo_url')) {
          $custom_logo_url = $this->get_config('custom_logo_url');
        }
        if($this->get_config('custom_mail_logo')) {
          $custom_mail_logo = $this->get_config('custom_mail_logo');
        }
        if($get_price  && $get_currency_id){
          switch($get_currency_id){
            case 1:$currency_symbol = '$';
              break;
            case 2:$currency_symbol = 'грн';
              break;
            case 10:$currency_symbol = '€';
              break;
            default :$currency_symbol = '';
              break;
          }
          $currency_id = $get_currency_id;
          $price = $get_price;
        }else{
          $currency_symbol = $offer['currency']['html_symbol'];
          $currency_id = $offer['currency']['id'];
          $price = $offer['price'];
        }        
        
        $flights_user_choosed = $this->get_flights_user_choosed_in_order();
        
        $array_search = array('{$tour_id}', '{$country_name}', '{$user_first_name}', '{$user_last_name}', '{$user_middle_name}', '{$user_city}', '{$user_phone}', '{$user_email}', '{$user_comment}', '{$region_name}', '{$from_city_name}', '{$date_from}'
                            , '{$hotel_name}', '{$hotel_rating_name}', '{$room_type_name}', '{$meal_type_name}' , '{$duration}', '{$adult_amount}', '{$child_amount}', '{$price}', '{$currency}', '{$charset}', '{$site_path}', '{$operator_name}', '{$custom_logo_url}', '{$custom_mail_logo}', '{$user_flight_to}', '{$user_flight_back}');
        $array_replace = array($offer['id'] . '/' . $offer['sharding_rule_id'], $offer['locations'][0]['country_name'], $first_name, $second_name, $middle_name, $city, $phone, $email, nl2br($comment), $offer['locations'][0]['region_name'], $offer['transports'][0]['from_city_name'], $offer['date_from']
                             , $offer['locations'][0]['hotel_name'], $offer['locations'][0]['hotel_rating_name'], $offer['locations'][0]['room_type_name'], $offer['locations'][0]['meal_type_name'], $offer['duration'], $offer['adult_amount'], $offer['child_amount'], $offer['price'], $offer['currency']['html_symbol'], $this->get_config('webservice_encoding'), dirname(dirname(__FILE__)) . '/', $offer['operator']['code'],$custom_logo_url, $custom_mail_logo, $flights_user_choosed['flight_to_email'], $flights_user_choosed['flight_back_email']);
        $mail_agency_order_message = str_replace($array_search, $array_replace, $mail_agency_order_message);

        $mail_user_order_message = file_get_contents(dirname(dirname(__FILE__)) . '/templates/mail_user_package_tour_order_message.html');
        $mail_user_order_message = @iconv('UTF-8', $this->get_config('webservice_encoding') . '//IGNORE', $mail_user_order_message);
        $array_search = array('{$country_name}', '{$agency_name}', '{$charset}', '{$agency_phone}');
        $array_replace = array($offer['locations'][0]['country_name'], $this->get_config('commercial_name'), $this->get_config('webservice_encoding'), $this->get_config('phone1'));
        $mail_user_order_message = str_replace($array_search, $array_replace, $mail_user_order_message);
        
        $agency_emails = explode(',', $this->get_config('agency_email'));
        $agency_emails = array_diff($agency_emails, array(''));
        $mailer = new PHPMailer();
        // письмо агенству 
        prepare_mailer_custom($mailer);
        $mailer->CharSet = $this->get_config('webservice_encoding');    
        if($email)
          $mailer->From = $email;
        else
          $mailer->From = $this->get_config('mail_agency_from');
        $mailer->FromName = $second_name . ' ' . $first_name;
        foreach($agency_emails as $agency_email)
          $mailer->AddAddress(trim($agency_email));
        
        $sub_array_search = array('{$tour_id}', '{$country_name}', '{$region_name}', '{$hotel_name}', '{$date_from}');
        $sub_array_replace = array($offer['id'] . '-' . $offer['sharding_rule_id'], $offer['locations'][0]['country_name'], $offer['locations'][0]['region_name'], $offer['locations'][0]['hotel_name'], $offer['date_from']);
        $mail_subject = str_replace($sub_array_search, $sub_array_replace, $this->get_config('mail_agency_subject_package_tour', 'Новая заявка на тур'));
        $mail_subject = @iconv('UTF-8', $this->get_config('webservice_encoding') . '//IGNORE', $mail_subject);
        $mailer->Subject = $mail_subject;
        $mailer->ContentType = 'text/html';
        
        $mailer->Body = $mail_agency_order_message;
        send_mail_custom($this, $mailer);
        // письмо туристу
        if($email) {
          prepare_mailer_custom($mailer);
          $mailer->CharSet = $this->get_config('webservice_encoding');
          $mailer->From = trim($agency_emails[0]);
          $mailer->FromName = $this->get_config('commercial_name');
          $mailer->AddAddress($email);
          $mailer->Subject = $this->get_config('mail_user_subject', 'Новая заявка на тур');
          $mailer->ContentType = 'text/html';
          $mailer->Body = $mail_user_order_message;             
          send_mail_custom($this, $mailer);
        } 
        
        // сохраняем заявку в БД        
        $this->package_order_save(get('tour_id'), get('sharding_rule_id'),$first_name, $second_name, $middle_name, $city, $email, $phone, $comment, $currency_id, $price, $flights_user_choosed);        
      }
            
      header("Content-Type: application/json");
      header("Accept-Charset: ".$this->get_config('webservice_encoding'));

      if(get('callback')) {
        echo get('callback') . '(' . $json->encode(array('success' => (count($err)?false:true), 'error' => implode("\n", $err) )) . ');';
      } else {
        echo $json->encode(array('success' => (count($err)?false:true), 'error' => implode("\n", $err) ));
      }
      exit;
    }
    
    // New action for new mod search, package tour, 'buy online form'
    if ((get('action') == 'package_tour_order_submit_buy_all') || (get('action') == 'hike_tour_order_submit_buy_all')) {
      // Check 'Заявка' или 'Запрос'
      $order_flag = false;
      $request_flag = false;
      if(trim(get('clientname_1')) == '') {
        $order_flag = false;
        $request_flag = true;
      } else {
        $order_flag = true;
        $request_flag = false;
      }
      
      // Begin 'Запрос' ***
      $search_array['requested_tours'][get('sharding_rule_id')] = array(get('tour_id'));
      $search_result = $this->package_tour_search($search_array);
      array_walk_recursive($_GET, 'change_charset_array', $this);
      $second_name = '';
      $first_name = get('first_name');
      $middle_name = '';
      $get_price = get('input_price');
      $get_currency_id = get('input_currency_id');
      $city = get('city');
      $email = '';
      if(preg_match('/^[a-z0-9\_\.\-]+\@[a-z0-9\-]+\.[a-z0-9\.\-]+$/Usi', get('email'))) {
        $email = get('email');
      }
      $comment = get('comment');
      
      if (get('phone_code')) {
        // New mod search
        $phone_code = get('phone_code');
        $phone = $phone_code . get('phone');
      }
      
      $err = array();
      if(!count($search_result['offers'])) {
        $err[] = $this->lang['tour_not_valid'];
      } else {
        if(empty($first_name) || $first_name == $this->lang['first_name'])
          $err[] = $this->lang['input_first_name'] . '<br>';
        if(empty($city) || $city == $this->lang['city'])
          $err[] = $this->lang['input_city'] . '<br>';
        if(empty($phone) || $phone == $this->lang['phone'])
          $err[] = $this->lang['input_phone'] . '<br>';
        if(empty($email) || $email == $this->lang['email'])
          $err[] = $this->lang['input_email'] . '<br>';
        if(isset($_COOKIE['package_tour_order_submit']))
          $err[] = $this->lang['get_order_time'] . '<br>';
        
        // Check captcha
        $captcha_text_md5 = trim(get('value_code'));
        $user_input_captcha_md5 = md5(md5(strtolower(trim(get('captcha')))));
        if ($captcha_text_md5 != $user_input_captcha_md5)
          $err[] = $this->lang['input_captcha'];
      }
      if(count($err) == 0) {
        //send mail
        setcookie("package_tour_order_submit", 1, time()+60);
        $offer = $search_result['offers'][0];
        $mail_agency_order_message = file_get_contents(dirname(dirname(__FILE__)) . '/templates/mail_agency_package_tour_order_message.html');
        $mail_agency_order_message = @iconv('UTF-8', $this->get_config('webservice_encoding') . '//IGNORE', $mail_agency_order_message);

        $custom_logo_url = 'https://www.ittour.com.ua/';
        $custom_mail_logo = 'mail-logo.jpg';
        if($this->get_config('custom_logo_url')) {
          $custom_logo_url = $this->get_config('custom_logo_url');
        }
        if($this->get_config('custom_mail_logo')) {
          $custom_mail_logo = $this->get_config('custom_mail_logo');
        }
        if($get_price  && $get_currency_id){
          switch($get_currency_id){
            case 1:$currency_symbol = '$';
              break;
            case 2:$currency_symbol = 'грн';
              break;
            case 10:$currency_symbol = '€';
              break;
            default :$currency_symbol = '';
              break;
          }
          $currency_id = $get_currency_id;
          $price = $get_price;
        }else{
          $currency_symbol = $offer['currency']['html_symbol'];
          $currency_id = $offer['currency']['id'];
          $price = $offer['price'];
        }
        $flights_user_choosed = $this->get_flights_user_choosed_in_order();
        
        $array_search = array('{$tour_id}', '{$country_name}', '{$user_first_name}', '{$user_last_name}', '{$user_middle_name}', '{$user_city}', '{$user_phone}', '{$user_email}', '{$user_comment}', '{$region_name}', '{$from_city_name}', '{$date_from}'
                            , '{$hotel_name}', '{$hotel_rating_name}', '{$room_type_name}', '{$meal_type_name}' , '{$duration}', '{$adult_amount}', '{$child_amount}', '{$price}', '{$currency}', '{$charset}', '{$site_path}', '{$operator_name}', '{$custom_logo_url}', '{$custom_mail_logo}', '{$user_flight_to}', '{$user_flight_back}');
        $array_replace = array($offer['id'] . '/' . $offer['sharding_rule_id'], $offer['locations'][0]['country_name'], $first_name, $second_name, $middle_name, $city, $phone, $email, nl2br($comment), $offer['locations'][0]['region_name'], $offer['transports'][0]['from_city_name'], $offer['date_from']
                             , $offer['locations'][0]['hotel_name'], $offer['locations'][0]['hotel_rating_name'], $offer['locations'][0]['room_type_name'], $offer['locations'][0]['meal_type_name'], $offer['duration'], $offer['adult_amount'], $offer['child_amount'], $offer['price'], $offer['currency']['html_symbol'], $this->get_config('webservice_encoding'), dirname(dirname(__FILE__)) . '/', $offer['operator']['code'],$custom_logo_url, $custom_mail_logo, $flights_user_choosed['flight_to_email'], $flights_user_choosed['flight_back_email']);
        $mail_agency_order_message = str_replace($array_search, $array_replace, $mail_agency_order_message);

        $mail_user_order_message = file_get_contents(dirname(dirname(__FILE__)) . '/templates/mail_user_package_tour_order_message.html');
        $mail_user_order_message = @iconv('UTF-8', $this->get_config('webservice_encoding') . '//IGNORE', $mail_user_order_message);
        $array_search = array('{$country_name}', '{$agency_name}', '{$charset}', '{$agency_phone}');
        $array_replace = array($offer['locations'][0]['country_name'], $this->get_config('commercial_name'), $this->get_config('webservice_encoding'), $this->get_config('phone1'));
        $mail_user_order_message = str_replace($array_search, $array_replace, $mail_user_order_message);

        $agency_emails = explode(',', $this->get_config('agency_email'));
        $agency_emails = array_diff($agency_emails, array(''));
        $mailer = new PHPMailer();
        // письмо агенству 
        prepare_mailer_custom($mailer);
        $mailer->CharSet = $this->get_config('webservice_encoding');    
        if($email)
          $mailer->From = $email;
        else
          $mailer->From = $this->get_config('mail_agency_from');
        $mailer->FromName = $second_name . ' ' . $first_name;
        foreach($agency_emails as $agency_email)
          $mailer->AddAddress(trim($agency_email));

        $sub_array_search = array('{$tour_id}', '{$country_name}', '{$region_name}', '{$hotel_name}', '{$date_from}');
        $sub_array_replace = array($offer['id'] . '-' . $offer['sharding_rule_id'], $offer['locations'][0]['country_name'], $offer['locations'][0]['region_name'], $offer['locations'][0]['hotel_name'], $offer['date_from']);
        $mail_subject = str_replace($sub_array_search, $sub_array_replace, $this->get_config('mail_agency_subject_package_tour', 'Новая заявка на тур'));
        $mail_subject = @iconv('UTF-8', $this->get_config('webservice_encoding') . '//IGNORE', $mail_subject);
        $mailer->Subject = $mail_subject;
        $mailer->ContentType = 'text/html';

        $mailer->Body = $mail_agency_order_message;
        send_mail_custom($this, $mailer);
        // письмо туристу
        if($email) {
          prepare_mailer_custom($mailer);
          $mailer->CharSet = $this->get_config('webservice_encoding');
          $mailer->From = trim($agency_emails[0]);
          $mailer->FromName = $this->get_config('commercial_name');
          $mailer->AddAddress($email);
          $mailer->Subject = $this->get_config('mail_user_subject', 'Новая заявка на тур');
          $mailer->ContentType = 'text/html';
          $mailer->Body = $mail_user_order_message;
          send_mail_custom($this, $mailer);
        }
        
        // сохраняем заявку в БД
        $this->package_order_save(get('tour_id'), get('sharding_rule_id'),$first_name, $second_name, $middle_name, $city, $email, $phone, $comment, $currency_id, $price, $flights_user_choosed);
      }
      // End 'Запрос' ***
      
      // Begin 'Заявка' ***
      // Get data from request ($_GET)
      $errors = array();
      if (get('input_price')) $input_price = trim(get('input_price'));
      if (get('input_currency_id')) $input_currency_id = trim(get('input_currency_id'));
      if (get('tour_id')) $tour_id = trim(get('tour_id'));
      if (get('sharding_rule_id')) $sharding_rule_id = trim(get('sharding_rule_id'));
      if (get('is_card_payment')) $is_card_payment = trim(get('is_card_payment'));
      // Check payment
      if (!get('is_card_payment')) $errors[] = $this->lang['select_payment_system'] . '.';

      // 2 adults + 3 children
      $adult_amount = 0;
      $child_amount = 0;
      for ($i=1; $i<6; $i++) {
        $key = 'clientname_' . $i;
        if (get($key)) {
          ${$key} = trim(get($key));
          if ($i < 3) $adult_amount++;
          if ($i > 2) $child_amount++;
        }
      }

      // Create order => insert data to DB
      $order_id = 0;
      $payment_html_form = '';
      if ((count($errors)== 0) && ($order_flag === true)) {
        // Get data for params
        $offer = array();
        $offer['type_id'] = get('offer_type_id', 1);
        $offer['sharding_rule_id'] = get('sharding_rule_id', -1);
        $offer['kind_id'] = get('offer_kind_id', 1);
        $offer['price'] = get('input_price', 1);
        $offer['country_id'] = get('offer_country_id', 1);
        $offer['region_id'] = get('offer_region_id', 1);
        $offer['accomodation'] = get('offer_accomodation', 1);
        $offer['hotel_id'] = get('offer_hotel_id', 1);
        $offer['room_type_id'] = get('offer_room_type_id', 1);
        $offer['room_type_kn_id'] = get('offer_room_type_kn_id', 1);
        $offer['meal_type_id'] = get('offer_meal_type_id', 1);
        $offer['meal_type_kn_id'] = get('offer_meal_type_kn_id', 1);
        $offer['operator_id'] = get('offer_operator_id', 1);
        $offer['spo_code'] = get('offer_spo_code', 1);
        $offer['duration'] = get('offer_duration', 1);
        $offer['date_from'] = get('offer_date_from', 1);
        $offer['adult_amount'] = get('offers_adult_amount', 1);
        $offer['child_amount'] = get('offers_child_amount', 0);
        $offer['from_city_id'] = get('offer_from_city_id', 1);
        $offer['currency_id'] = get('offer_currency_id', 1);
        $offer['price_grn'] = get('offer_price_grn', 1);
        $offer['from_plane_id'] = get('offer_from_plane_id', 1);
        $offer['to_plane_id'] = get('offer_to_plane_id', 1);
        $offer['spo_comment'] = get('offer_spo_comment', '');
        $offer['rating_kn_id'] = '';
        $offer['online_booking'] = '';
        $offer['tour_price_id'] = get('offer_tour_price_id', 1);

        $post = array();
        $post['transfer'] = 22;// 22 'Груповий' : 'Ідивідуальний'
        $post['is_direct_booking'] = 0;
        $post['comment'] = '';
        $post['resp_agency'] = '';
        $post['add_services_price'] = 0;
        $post['add_visa_price'] = 0;
        $post['commission'] = '';
        $post['add_percent_price'] = 0;
        $post['order_responsibility'] = 4;
        $post['payment_method'] = $is_card_payment;
        $post['is_card_payment'] = 1; // Всегда, т.к. только картой оплата.
        $post['flight_from_id'] = get('offer_flight_from_id', 1);
        $post['from_active'] = 0;
        $post['flight_to_id'] = get('offer_flight_to_id', 1);
        $post['to_active'] = 0;

        $agency_id = $this->agency_id;

        $new_tourists = array();

        // Set tourist data 5(max adult) + 3(max child) + 3(max child < 2year) + 1(для выполненния условия for)
        $tourist_count = 5 + 3 + 3 + 1;
        for ($i=1; $i<$tourist_count; $i++) {
           // first_name
           $key = 'clientname_' . $i;
           $first_name = trim(get($key));
           
           // Check empty user data
           if ($first_name == '') continue;

           // last_name
           $key = 'clientsurname_' . $i;
           $last_name = trim(get($key));

           // gender
           $key = 'gender_' . $i;
           $gender = trim(get($key));

           // birthday
           $key = 'clientDobDay_' . $i;
           $birthday_dd = trim(get($key));
           $key = 'clientDobMonth_' . $i;
           $birthday_mm = trim(get($key));
           $key = 'clientDobYear_' . $i;
           $birthday_yyyy = trim(get($key));
           $birthday = $birthday_yyyy . '-' . $birthday_mm . '-' . $birthday_dd;

           // passport
           $key = 'pass_series_' . $i;
           $passport_series = trim(get($key));
           $key = 'pass_numb_' . $i;
           $passport_number = trim(get($key));
           $passport = $passport_series . '-' . $passport_number;

           // passport_end_date
           $key = 'clientPassValidDay_' . $i;
           $passport_end_date_dd = trim(get($key));
           $key = 'clientPassValidMonth_' . $i;
           $passport_end_date_mm = trim(get($key));
           $key = 'clientPassValidYear_' . $i;
           $passport_end_date_yyyy = trim(get($key));
           $passport_end_date = $passport_end_date_yyyy . '-' . $passport_end_date_mm . '-' . $passport_end_date_dd;

           // nationality
           $key = 'citizenship_' . $i;
           $nationality = trim(get($key));

           // tourist_type
           $yyyy_now = date("Y");
           $age = $yyyy_now - $birthday_yyyy;
           $tourist_type = 0;// default
           if ($age <= 2) $tourist_type = 30; // '30' => 'ребенка до 2-х лет'
           if (($age > 2) && ($age <= 15)) $tourist_type = 29; // '29' => 'ребенка',
           if ($age >= 16) $tourist_type = 28; // '28' => 'взрослого',

           // issued
           $key = 'clientAuthor_' . $i;
           $issued = trim(get($key));

           // passport_create_date
           $key = 'clientPassDay_' . $i;
           $passport_create_date_dd = trim(get($key));
           $key = 'clientPassMonth_' . $i;
           $passport_create_date_mm = trim(get($key));
           $key = 'clientPassYear_' . $i;
           $passport_create_date_yyyy = trim(get($key));
           $passport_create_date = $passport_create_date_yyyy . '-' . $passport_create_date_mm . '-' . $passport_create_date_dd;

           // visa
           $visa_type_id = 0;
           $visa_price = 0;

           $new_tourists[] = array(
                                   'order_id' => 0,
                                   'first_name' => $first_name,
                                   'last_name' => $last_name,
                                   'gender' => $gender,
                                   'birthday' => $birthday,
                                   'passport' => $passport,
                                   'passport_end_date' => $passport_end_date,
                                   'nationality' => $nationality,
                                   'tourist_type' => $tourist_type,
                                   'issued' => $issued,
                                   'visa_type_id' => $visa_type_id,
                                   'visa_price' => $visa_price,
                                   'passport_create_date' => $passport_create_date,
                                   'resp_tourist' => 0
                                  );
        }
        
        // Check 'hike' or 'package'
        if (get('action') == 'hike_tour_order_submit_buy_all') {
          $offer['tour_type_is_hike'] = 1;
          $offer['tour_id'] = $tour_id;
        }
          
        // Добавляем параметр в order, чтобы заявки уходилив в админку ittour с пометкой что order сделан с сайте а не манагером агенства.
        $offer['is_pay_from_site_not_ittour'] = 1;

        // Set data
        $params = array(
                        'offer'        => $offer,
                        'post'         => $post,
                        'agency_id'    => $agency_id,
                        'tourists'     => $new_tourists
                        );

        // Insert data to DB => get html form for view
        $payment_html_form = $this->create_order_buy_online($params);
        
        if (($order_flag === true) && (get('checkbox1') == 1) && (get('checkbox2') == 1)) {
          // 'Заявка' // Set json respons // проверка перед отправкой респонса что вя параметры выбраны и блок с оплатой показан.
          header("Content-Type: application/json");
          header("Accept-Charset: ".$this->get_config('webservice_encoding'));
          if(get('callback')) {
            echo get('callback') . '(' . $json->encode(array('success' => (count($errors)?false:true), 'error' => implode("<br/>", $errors), 'payment_html_form' => $payment_html_form)) . ');';
          } else {
            echo $json->encode(array('success' => (count($errors)?false:true), 'error' => implode("<br/>", $errors), 'payment_html_form' => $payment_html_form));
          }
          exit;
        }
      }
      // End 'Заявка' ***
      
      // Проверка добавлена для удаления платежной формы.
      // Eсли юзер не выбирал платежку, а только заполнил инфу о себе.      
      // для php сборки делать так всегда!!!
      $payment_html_form = '';      
      
      // 'Запрос' // Set json respons // ответ как после  запроса.
      header("Content-Type: application/json");
      header("Accept-Charset: ".$this->get_config('webservice_encoding'));
      if(get('callback')) {
        echo get('callback') . '(' . $json->encode(array('success' => (count($err)?false:true), 'error' => implode("\n", $err), 'payment_html_form' => $payment_html_form )) . ');';
      } else {
        echo $json->encode(array('success' => (count($err)?false:true), 'error' => implode("\n", $err), 'payment_html_form' => $payment_html_form ));
      }
      exit;
    }
    
    if(get('action') == 'get_hike_tour_order_form') {
      $this->prepare_captcha();
      $this->itt_captcha->reset();
      $info_message = null;
      $tour_id = get('tour_id');
      $is_showcase = get('is_showcase');
      if(!is_numeric($tour_id))
        $info_message = $this->lang['sorry_tour_not_found'];
      else {
        $search_array = array( 'tours' => array($tour_id)
                             , 'new_order' => true );// Set valid accomodations in tout
        $search_result = $this->hike_tour_price_search($search_array);
        if(count($search_result) == 0)
          $info_message = $this->lang['sorry_tour_not_found'];
        else {
          $hike_arrays = $this->create_hike_additional_array($search_result);
          $offer_dates = safe($hike_arrays, 'offer_dates');
          $offer_accomodations = safe($hike_arrays, 'offer_accomodations');
          $offer_prices = safe($hike_arrays, 'offer_prices');
          $offer_prices_json = safe($hike_arrays, 'offer_multi_prices_json');
          $offer_early_prices = safe($hike_arrays, 'offer_early_prices');
          $offer_early_prices_json = safe($hike_arrays, 'offer_multi_early_prices_json');
          $countries_str = safe($hike_arrays, 'countries_str');
          $cities_str = safe($hike_arrays, 'cities_str');
          $hotels = safe($hike_arrays, 'hotels'); 
          $countries_code_iso = safe($hike_arrays, 'countries_code_iso');
        }        
      }

      $html = get_hike_tour_order_form($this, $info_message, safe($search_result, 0), $offer_prices_json, $offer_early_prices_json, $hotels, $countries_str, $cities_str, $offer_dates, $offer_accomodations,$is_showcase,$countries_code_iso);
      header("Content-Type: application/json");
      header("Accept-Charset: ".$this->get_config('webservice_encoding'));

      echo get('callback') . '(' . $json->encode(array('text' => $html)) . ');';
      exit;
    }
    
    if(get('action') == 'get_hike_tour_order_form_mod2') {
      $this->prepare_captcha();
      $this->itt_captcha->reset();
      $info_message = null;
      $tour_id = get('tour_id');
      $is_showcase = get('is_showcase');
      if(!is_numeric($tour_id))
        $info_message = $this->lang['sorry_tour_not_found'];
      else {
        $search_array = array( 'tours' => array($tour_id)
                             , 'new_order' => true );// Set valid accomodations in tout
        $search_result = $this->hike_tour_price_search($search_array);
        if(count($search_result) == 0)
          $info_message = $this->lang['sorry_tour_not_found'];
        else {
          $hike_arrays = $this->create_hike_additional_array($search_result);
          $offer_dates = safe($hike_arrays, 'offer_dates');
          $offer_accomodations = safe($hike_arrays, 'offer_accomodations');
          $offer_prices = safe($hike_arrays, 'offer_prices');
          $offer_prices_json = safe($hike_arrays, 'offer_multi_prices_json');
          $offer_early_prices = safe($hike_arrays, 'offer_early_prices');
          $offer_early_prices_json = safe($hike_arrays, 'offer_multi_early_prices_json');
          $countries_str = safe($hike_arrays, 'countries_str');
          $cities_str = safe($hike_arrays, 'cities_str');
          $hotels = safe($hike_arrays, 'hotels'); 
          $countries_code_iso = safe($hike_arrays, 'countries_code_iso');
        }
      }
      
      $html = get_hike_tour_order_form_mod2($this, $info_message, safe($search_result, 0), $offer_prices_json, $offer_early_prices_json, $hotels, $countries_str, $cities_str, $offer_dates, $offer_accomodations,$is_showcase,$countries_code_iso);
      
      // Set charset and encode html
      if(strpos($_SERVER['HTTP_REFERER'], 'www.ittour.com.ua') !== false){
        //$html = iconv($this->get_config('webservice_encoding'), 'utf-8', $html);// From old
        $charset = 'utf-8';
      } else if(get('chrs')){
        //$html = iconv($this->get_config('webservice_encoding'), get('chrs'), $html);// From old
        $charset = get('chrs');
        $html = iconv($charset, 'utf-8', $html);
      } else {
        $charset = $this->get_config('webservice_encoding');
        $html = iconv($charset, 'utf-8', $html);
      }
      
      header("Content-Type: application/json");
      header("Accept-Charset: ".$charset);
      echo get('callback') . '(' . $json->encode(array('text' => $html)) . ');';
      exit;
    }
    
    if(get('action') == 'get_hike_tour_hikes') {
      $tour_id = get('tour_id');
      $is_showcase = get('is_showcase');
      if(is_numeric($tour_id)) {
        $search_array = array( 'tours' => array($tour_id) );
        $search_result = $this->hike_tour_price_search($search_array);
      }

      $html = get_hike_tour_hikes($this, safe($search_result, 0), $is_showcase);
      header("Content-Type: application/json");
      header("Accept-Charset: ".$this->get_config('webservice_encoding'));
      echo get('callback') . '(' . $json->encode(array('text' => $html)) . ');';
      exit;
    }
    
    if(get('action') == 'get_hike_tour_view') {
      $tour_id = get('tour_id');
      if(!is_numeric($tour_id))
        $info_message = $this->lang['sorry_tour_not_found'];
      else {        
        $search_array = array( 'tours' => array($tour_id) );
        $search_result = $this->hike_tour_price_search($search_array);
        if(count($search_result) == 0)
          $info_message = $this->lang['sorry_tour_not_found'];
        else {
          $hike_arrays = $this->create_hike_additional_array($search_result);
          $offer_dates = safe($hike_arrays, 'offer_dates');
          $offer_accomodations = safe($hike_arrays, 'offer_accomodations');
          $offer_prices = safe($hike_arrays, 'offer_prices');
          $offer_prices_json = safe($hike_arrays, 'offer_prices_json');
          $offer_early_prices = safe($hike_arrays, 'offer_early_prices');
          $offer_early_prices_json = safe($hike_arrays, 'offer_early_prices_json');
          $countries_str = safe($hike_arrays, 'countries_str');
          $cities_str = safe($hike_arrays, 'cities_str');
          $hotels = safe($hike_arrays, 'hotels');
        }
      }
           
      $html = get_hike_tour_view($this, $info_message, safe($search_result, 0), $hotels, $countries_str, $cities_str, $offer_dates, $offer_accomodations); 
      header("Content-Type: application/json");
      header("Accept-Charset: ".$this->get_config('webservice_encoding'));      
      echo get('callback') . '(' . $json->encode(array('text' => $html)) . ');';
      exit;      
    }

    if(get('action') == 'hike_tour_order_submit') {
      
      $search_array = array( 'tours' => array(get('tour_id')) );
      $search_result = $this->hike_tour_price_search($search_array);
      
      $price_and_priceid = explode(':', get('price'));
      $price = safe($price_and_priceid, 0);
      $price_id = safe($price_and_priceid, 1);
      $currency = get('currency');
      $currency_id = get('currency_id');

      $offer = $search_result[0];
      $hike_arrays = $this->create_hike_additional_array($search_result);
      $offer_dates = safe($hike_arrays, 'offer_dates');
      $offer_accomodations = safe($hike_arrays, 'offer_accomodations');
      $offer_prices = safe($hike_arrays, 'offer_prices');
      $offer_prices_json = safe($hike_arrays, 'offer_prices_json');
      $offer_early_prices = safe($hike_arrays, 'offer_early_prices');
      $offer_early_prices_json = safe($hike_arrays, 'offer_early_prices_json');
      $countries_str = safe($hike_arrays, 'countries_str');
      $cities_str = safe($hike_arrays, 'cities_str');
      $hotels = safe($hike_arrays, 'hotels');
      
      array_walk_recursive($_GET, 'change_charset_array', $this);
      $second_name = get('second_name');
      $first_name = get('first_name');
      $middle_name = get('middle_name');
      $city = get('city');
      $email = get('email');
      $comment = get('comment');
      
      // Use in new mod search
      if (get('phone_code')) {
        // New mod search
        $phone_code = get('phone_code');
        $phone = $phone_code . get('phone');
      } else {
        // Old mod search
        $phone = get('phone');
      }
      
      $err = array();      
      if(empty($first_name) || $first_name == $this->lang['first_name'])
        $err[] = $this->lang['input_first_name'];
      if(empty($city) || $city == $this->lang['city'])
        $err[] = $this->lang['input_city'];
      if(empty($phone) || $phone == $this->lang['phone'])
        $err[] = $this->lang['input_phone'];
      if(empty($email) || $email == $this->lang['email'])
        $err[] = $this->lang['input_email'];      
      if(isset($_COOKIE['hike_tour_order_submit']))
        $err[] = $this->lang['get_order_time'];
      
      // Check captcha
      $captcha_text_md5 = trim(get('value_code'));
      $user_input_captcha_md5 = md5(md5(strtolower(trim(get('captcha')))));
      if ($captcha_text_md5 != $user_input_captcha_md5)
        $err[] = $this->lang['input_captcha'];
      
      if(count($err) == 0) {
        //send mail
        setcookie("hike_tour_order_submit", 1, time()+60);
        $mail_agency_order_message = file_get_contents(dirname(dirname(__FILE__)) . '/templates/mail_agency_hike_tour_order_message.html');
        $mail_agency_order_message = @iconv('UTF-8', $this->get_config('webservice_encoding') . '//IGNORE', $mail_agency_order_message);
        $array_search = array('{$tour_id}', '{$country_name}', '{$user_first_name}', '{$user_last_name}', '{$user_middle_name}', '{$user_city}', '{$user_phone}', '{$user_email}', '{$user_comment}', '{$tour_name}', '{$city_name}', '{$from_city_name}' 
                            , '{$transport}', '{$meal_type_name}', '{$duration}', '{$accomodation}', '{$price}', '{$date}', '{$charset}', '{$site_path}', '{$operator_name}'
                            , '{$accomodation_id}', '{$departure_id}', '{$currency}');
        $array_replace = array($offer['tour_id'], $countries_str, $first_name, $second_name, $middle_name, $city, $phone, $email, nl2br($comment), $offer['tour']['name'], $cities_str, $offer['tour']['from_city']
                             , $offer['tour']['transport_type'], $offer['tour']['meal_type_code'], $offer['tour']['duration'], $offer_accomodations[get('accomodation')], $price, get('departure_date'), $this->get_config('webservice_encoding'), dirname(dirname(__FILE__)) . '/', $offer['operator']['code']
                             , get('accomodation'), $offer['id'], $currency);
        $mail_agency_order_message = str_replace($array_search, $array_replace, $mail_agency_order_message);

        $mail_user_order_message = file_get_contents(dirname(dirname(__FILE__)) . '/templates/mail_user_hike_tour_order_message.html');
        $mail_user_order_message = @iconv('UTF-8', $this->get_config('webservice_encoding') . '//IGNORE', $mail_user_order_message);
        $array_search = array('{$country_name}', '{$agency_name}', '{$tour_name}', '{$charset}', '{$agency_phone}');
        $array_replace = array($countries_str, $this->get_config('commercial_name'), $offer['tour']['name'], $this->get_config('webservice_encoding'), $this->get_config('phone1'));
        $mail_user_order_message = str_replace($array_search, $array_replace, $mail_user_order_message);
        
        $mailer = new PHPMailer();
        $agency_emails = explode(',', $this->get_config('agency_email'));
        $agency_emails = array_diff($agency_emails, array(''));
        // письмо агенству
        prepare_mailer_custom($mailer);
        $mailer->CharSet = $this->get_config('webservice_encoding');
        if($email)
          $mailer->From = $email;
        else
          $mailer->From = $this->get_config('mail_agency_from');
          
        $mailer->FromName = $second_name . ' ' . $first_name;
        foreach($agency_emails as $agency_email)
          $mailer->AddAddress(trim($agency_email));
          
        $sub_array_search = array('{$tour_id}', '{$tour_name}', '{$date_from}');
        $sub_array_replace = array($offer['id'], $offer['tour']['name'], $offer['date_from']);
        $mail_subject = str_replace($sub_array_search, $sub_array_replace, $this->get_config('mail_agency_subject_hike_tour', 'Новая заявка на тур'));
        
        $mailer->Subject = $mail_subject;
        $mailer->ContentType = 'text/html';
        $mailer->Body = $mail_agency_order_message;       
        send_mail_custom($this, $mailer);

        // письмо туристу
        if($email) {
          prepare_mailer_custom($mailer);
          $mailer->CharSet = $this->get_config('webservice_encoding');    
          $mailer->From = trim($agency_emails[0]);
          $mailer->FromName = $this->get_config('commercial_name');
          $mailer->AddAddress($email);
          $mailer->Subject = $this->get_config('mail_user_subject', 'Новая заявка на тур');
          $mailer->ContentType = 'text/html';            
          $mailer->Body = $mail_user_order_message;             
          send_mail_custom($this, $mailer);
        }                      
        
        // сохраняем заявку в БД
        $this->hike_order_save(get('tour_id'), $first_name, $second_name, $middle_name, $city, $email, $phone, $comment, get('accomodation'), $price, get('departure_date'), $price_id, $currency_id);
      }      
      header("Content-Type: application/json");
      header("Accept-Charset: ".$this->get_config('webservice_encoding'));
      echo get('callback') . '(' . $json->encode(array('success' => (count($err)?false:true), 'error' => implode("\n", $err) )) . ');';
      exit;
    }  
    
    if(get('action') == 'get_hike_search_filtered_field') {
      $field = $this->get_hike_search_filtered_field(get('object_result', 'ajax'), get('event_owner_level'), explode(' ', get('country_id')), explode(' ', get('transport_id')), explode(' ', get('city_id')), explode(' ', get('tour_city_id')), get('width_class') );
      echo get('callback') . '(' . $field . ');'; // field уже json
      exit;
    }
    
    if(get('action') == 'get_package_search_filtered_field') {
      $field = $this->get_package_search_filtered_field(get('object_result', 'ajax'), get('event_owner_level'), explode(' ', get('country_id')), explode(' ', get('region_id')), explode(' ', get('hotel_rating_id')), get('tour_type', 0), get('tour_kind', 0), get('date_till', ''), get('departure_city', 0));
      if(get('callback')) {
        echo get('callback') . '(' . $field . ');'; // field уже json
      } else {
        echo $field;
      }
      exit;
    }
    
    if(get('action') == 'add_tour_to_basket') {
      $tours = trim(safe($_COOKIE, 'basket_tours'));
      $offer_id = get('offer_id');
      $tours = explode(' ', $tours);
      if(!in_array($offer_id, $tours)) {
        $tours[] = $offer_id;
        $res = 'add';
      } else {
        $tours = array_diff($tours, array(0 => $offer_id));
        $res = 'del';
      }
      setcookie('basket_tours', implode(' ', $tours), time()+365*24*60*60);
      echo get('callback') . '(' . $json->encode(array('text' => $res)) . ');';
      exit;
    }
    if(get('action') == 'get_showcase_package_city_to_country') {
      $param = array('country' =>get('country'));
      if(get('any_city')){
        $param['any_city'] = get('any_city');
      }
      if(get('hotel_ratings')){
        $param['rating'] = explode(',', get('hotel_ratings'));
      }
      if(get('showcase')){
        $param['showcase'] = get('showcase');
      }
      if(get('result')){
        $param['result'] = get('result');
      }
      if(get('return_empry_result')){
        $param['return_empry_result'] = get('return_empry_result');
      }
      $res = $this->get_showcase_package_city_to_country($param);
      header("Content-Type: application/json");
      header("Accept-Charset: ".$this->get_config('webservice_encoding'));
      echo get('callback') . '(' . $json->encode($res) . ');';
      exit;
    }

    //ajax
    if(get('action') == 'get_showcase_filters') {
      $result = array("country", "ratind", "city");
      $filters = array();
      if(get('country')) {
        $filters['country'] = get('country');
        unset($result['country']);
      }
      if(get('city')) {
        $filters['city'] = get('city');
        unset($result['city']);
      }
      if(get('ratind')) {
        $filters['ratind'] = get('ratind');
        unset($result['ratind']);
      }
      if($result) {
        $param = array('result' => $result, 'showcase' => 48, 'use_all' => 1);
        $param = array_merge($param, $filters);
        $filter_list = $this->get_showcase_filter($param);
      } else {
        $filter_list = array();
      }
      echo get('callback') . '(' . $json->encode($filter_list) . ');';
      exit;
    }
    
    if(get('action') == 'get_showcase_tour') {
      $this->save_statistic = true;
      $search_array = $this->parse_showcase_search_url($_SERVER['QUERY_STRING']);
      $search_array['truncate'] = true;
      $search_array['group_by_hotel'] = true;
      $search_array['showcase_type'] = $this->get_config('showcase_type');
      $search_array['page'] = (int)get('page');
      $search_array['custom_query'] = get('custom_query');
      $html = '';
      if(get('type') == 66) {
        $search_array = $this->prepare_showcase_hike_search_array($search_array);
        $search_result = $this->package_tour_search($search_array,true);
        $filter = safe($search_result, 'filter');
        switch($this->get_config('width_class')) {
          case '54':  $html = get_showcase_hike_table($this, $filter, $search_result);
            break;
          case '55':  $html = get_showcase_hike_table_light($this, $filter, $search_result);
            break;
          default:    $html = get_showcase_hike_table_max($this, $filter, $search_result);
            
        }
      } else {
        $search_array = $this->prepare_showcase_search_array($search_array);
        if(get('type') == 48 && !safe($search_array,'countries') && !safe($search_array,'cities') && count(safe($search_array,'hotel_ratings')) > 3) {
          $search_array['get_best'] = 1;
        }
        if($this->get_config('use_tour_currency')) {
          $search_array['use_tour_currency'] = true;
        }
        $search_result = $this->package_tour_search($search_array,true);
        $filter = safe($search_result, 'filter');
        if($this->get_config('results_type') == 65) { 
          if($this->get_config('width_class') == 53) {
            $html = get_showcase_result_table_big($this, $filter, $search_result);
          } elseif($this->get_config('width_class') == 54) {
            $html = get_showcase_result_table_medium($this, $filter, $search_result);
          } else {
            $html = get_showcase_result_table_small($this, $filter, $search_result);
      }
        } else {
          if($this->get_config('results_type') == -1) {
            if(get('site_page') == 1) {
              $html = get_showcase_result_table_site_page_day($this, $filter, $search_result);
            } else {
              $html = get_showcase_result_table_big_day($this, $filter, $search_result);
            }
          } else {
          $html = get_showcase_result_hotel($this, $filter, $search_result);
        }
      }
      }
      
      header("Content-Type: application/json");
      header("Accept-Charset: ".$this->get_config('webservice_encoding'));
      $count = $this->get_config('stop_search')?0:count($search_result['offers']);
      $results = array( 'text'                          => $html
                      , 'results_count'                 => $count
                      , 'stop_search in config'         => $this->get_config('stop_search')?'true':'false'
                      , 'stop_search in search results' => isset($search_result['stop_search'])?$search_result['stop_search']:'not set'
                      );
      echo get('callback') . '(' . $json->encode($results) . ');';
      exit;
    }
    
    if(get('action') == 'help'){
       if(get('func_name')) {
         $result = $this->help(get('func_name'), array());
         print($result);
       }
      exit;
    }
    
    if(get('action') == 'get_min_prices_content' && get('itt_tour_type')){
      $results = array('text' => $this->get_min_prices_content(get('itt_tour_type'), get('width_class')));
      
      header("Content-Type: application/json");
      header("Accept-Charset: ".$this->get_config('webservice_encoding'));
      echo get('callback') . '(' . $json->encode($results) . ');';
      exit;
    }

    // **** BEGIN **** фото витрина экскурсионных туров (ajax фильтры) *********
    if (get('action') == 'showcase_hike_tour_search') {
        $search_array          = $this->parse_hike_search_url($_SERVER['QUERY_STRING']);
        $prepared_search_array = $this->prepare_hike_search_array($search_array);
        $search_result         = $this->hike_tour_search($prepared_search_array);
        $html                  = get_showcase_hike_photo_content($search_result, $this, get('currency'));
        
        header("Content-Type: application/json");
        header("Accept-Charset: ".$this->get_config('webservice_encoding'));
        echo get('callback') . '(' . $json->encode($html) . ');';
        exit;
    }
    // **** END ****** фото витрина экскурсионных туров (ajax фильтры) *********
    
    // Get 'transport IDs' for current country if filter uptate.
    if (get('action') == 'showcase_hike_filter_update') {
      $filter_array  = get('country');
      if($filter_array) {
        $filter_result = $this->showcase_hike_filter_update($filter_array);
      } else {
        $filter_result = '';
      }
      
      header("Content-Type: application/json");
      header("Accept-Charset: ".$this->get_config('webservice_encoding'));
      echo get('callback') . '(' . $json->encode($filter_result) . ');';
      exit;
    }
}
  
  /** 
   *    function return info about flights that user sents in order form 
   *    gets data from $_GET
  */  
  function get_flights_user_choosed_in_order() {
      $return = array();
      $return['user_flight_to_id'] = get('flight_to_id', 0);
      $return['user_flight_back_id'] = get('flight_back_id', 0);
        
      $return['user_flight_to_info'] = get('flight_to_info', '');
      $return['user_flight_back_info'] = get('flight_back_info', '');
      
      $return['flight_to_email'] = '';
      $return['flight_back_email'] = '';
      
      $tostr = explode('-sep-', $return['user_flight_to_info']);
      $tostr_result = '';
      foreach($tostr as $k=>$v) {
          $v = trim($v);
          if(trim($v)) {
              $tostr_result .= $v.' ';
          }
      }
      
      $backstr = explode('-sep-', $return['user_flight_back_info']);
      $back_result = '';
      foreach($backstr as $k=>$v) {
          $v = trim($v);
          if($v) {
              $back_result .= $v.' ';
          }
      }
      if($return['user_flight_to_id'] && $tostr_result) {
          $return['flight_to_email'] = 'ID: '.$return['user_flight_to_id'].' INFO: '.$tostr_result;
      }
      if($return['user_flight_back_id'] && $back_result) {
          $return['flight_back_email'] = 'ID: '.$return['user_flight_back_id'].' INFO: '.$back_result;;
      }
      
     
      unset($tostr,$tostr_result,$backstr,$back_result);
      return $return;
  }

  function prepare_captcha() {
    $this->itt_captcha = new itt_captcha();
    $this->itt_captcha->chars = 5;
    $this->itt_captcha->lx = 180;
    $this->itt_captcha->ly = 50;
    $this->itt_captcha->max_rotation = 10;
    $this->itt_captcha->noise = false;
    $this->itt_captcha->background_color = '#fff';
    $this->itt_captcha->handler();
  }
  
  function db_query($query) {
    $args = func_get_args();
    $query = array_shift($args);
    $query = sql_placeholder_ex($query, $args, $error);
    $res = mysql_query($query, $this->db);
    if(mysql_errno()) $this->error('10', mysql_error($this->db) . " SQL: {$query}");
    return $res;
  }

  function db_row($query) {
    $args = func_get_args();
    $query = array_shift($args);
    $query = sql_placeholder_ex($query, $args, $error);
    $res = mysql_query($query, $this->db);
    if(mysql_errno()) $this->error('10', mysql_error($this->db) . " SQL: {$query}");
    if(mysql_num_rows($res)) {
      $row = mysql_fetch_assoc($res);
      return $row;
    }
    return false;
  }

  function db_rows($query) {
    $args = func_get_args();
    $query = array_shift($args);
    $query = sql_placeholder_ex($query, $args, $error);
    $res = mysql_query($query, $this->db);
    if(mysql_errno()) $this->error('10', mysql_error($this->db) . " SQL: {$query}");
    $result = array();
    if(mysql_num_rows($res)) {
      while($row = mysql_fetch_assoc($res)) {
        $result[] = $row;
      }
    }
    return $result;
  }

  function connect() {
    //$soap = new custom_nusphere_soapclient($this->wsdl, 'wsdl');
    //$this->proxy = $soap->getProxy();
    //$this->proxy->decode_utf8 = false;
    //$this->proxy->soap_defencoding = 'UTF-8';
    //$this->is_connected = true;    
    
    // Я. всегда возращаем токен
    //$result = $this->exec('authentificate', array('login' => $this->wsdl_login, 'password' => $this->wsdl_password), true);
    $this->auth_token = 'd0f84f1f30ba54853f4b790be4ec5a8b';
  }
  
  function get_request($htmlspecialchars = false) {
    echo '<h2>Request</h2>';
    if(!$htmlspecialchars)
      echo '<pre>' . $this->proxy->request . '</pre>';    
    else
      echo '<pre>' . htmlspecialchars($this->proxy->request, ENT_QUOTES) . '</pre>';
  }
  
  function get_response($htmlspecialchars = false) {
    echo '<h2>Response</h2>';
    if(!$htmlspecialchars)
      echo '<pre>' . $this->proxy->response . '</pre>';    
    else
      echo '<pre>' . htmlspecialchars($this->proxy->response, ENT_QUOTES) . '</pre>';
  }

  function error($error_code, $error_text) {
    $this->is_error = true;
    $this->error_message = 'Error #' . $error_code . ': ' . $error_text;
    if(!is_null($this->error_handler_function) && function_exists($this->error_handler_function)) {
      call_user_func($this->error_handler_function, $error_code, $error_text);
    }
  }

  function exec($func_name, $param, $cache_enabled=false) {

    // Я. найти все методі где візіваетсья exec, и добавить в switch case
	$api_client = new api_client;
	try {
	  switch($func_name) {
		  case 'get_search_form_fields':
			$result = $api_client->get_dictionary();
			break;
		  case 'package_validate_tour':
			$result = $api_client->validate($param['tour_id']);
			break;
			/*case 'package_tour_search':
			$result = $api_client->search($param['type_id']);
			break;
			case 'hike_tour_search':
			$result = $api_client->search($param['type_id']);
			break;*/
			case 'get_package_tour_price':
			$result = $api_client->search($param['price_id']);
			break;
			case 'get_package_order_info':
			$result = $api_client->search($param['price_id']);
			break;
			case 'get_flight':
			$result = $api_client->tour_flights($param['$tour_id']);
			break;
			case 'get_references':
			$result = $api_client->tour_info($param['$tour_id']);
			break;
			//'get_country_list' = countries();  скорее всего одно и то же, и там и там массив со странами
		  case 'get_default_constant_optimize':
			$result = $api_client->get_dictionary(); // не нашел что єтот метод возвращать должен
			break;
		  default:
		    $result = $api_client->search($param);
		    break;
	  }
	  
	  return json_decode(json_encode($result), true);
	} catch(api_client_exception $e) {
	  echo $e->getMessage();
	  echo '<br>';
	  echo $e->getCode();
	}
	
	
	
	
	  
    $umc = new custom_cached_manager();
    // кеш
    $cache_time = $this->session_lifetime;
    if($func_name== 'package_validate_tour'){
      $cache_time = $this->validat_lifetime;
    }
    $param_key = md5(serialize($param));
    if(($this->get_config('default_setting') && ( $func_name != 'get_default_constant_optimize' && $func_name != 'get_default_constant')) || $func_name== 'package_validate_tour'){
      $config_id = 'default_' . $this->get_config('webservice_encoding');
    }else{
      $config_id = $this->wsdl_login;
    }
    $db_key = "{$config_id}_{$func_name}_{$param_key}";
    if($this->get_config('add_cache_name_prefix')){
      $memcache_key = "{$config_id}_{$func_name}_{$param_key}_date_prev";
      $cache_filename = dirname(dirname(__FILE__)) . "/_tmp/{$config_id}_{$func_name}_{$param_key}_prev.txt";
    }else{
      $cache_filename = dirname(dirname(__FILE__)) . "/_tmp/{$config_id}_{$func_name}_{$param_key}.txt";
      $memcache_key = "{$config_id}_{$func_name}_{$param_key}_date";
    }
    if( !safe($_GET, 'no_cache') && !$this->get_config('no_cache') && $cache_enabled && $this->use_cache) {
      if($this->cache_type == 'file' && file_exists($cache_filename) && (time() - filemtime($cache_filename) < $cache_time) ) {
        $fp = fopen($cache_filename, 'r');
        $result = fread($fp, filesize($cache_filename));
        fclose($fp);
        return unserialize($result);
        exit;
      } elseif ($this->cache_type == 'session' && isset($_SESSION['webclient'][$func_name][$param_key]) && (time()-$_SESSION['webclient'][$func_name][$param_key.'_date'] < $cache_time)) {
        return $result = $_SESSION['webclient'][$func_name][$param_key];
        exit;
      } elseif ($this->cache_type == 'memcached') {
          if($cache_result = $umc->get($memcache_key)){
              return $cache_result;
              exit;
          }      
      } elseif($this->cache_type == 'db' && $res = $this->db_query('SELECT UNIX_TIMESTAMP(last_update_at) as last_update_at FROM main_cache WHERE code = ?', $db_key)) {
        if(mysql_num_rows($res) > 0) {
          $row = mysql_fetch_assoc($res);
          if($row['last_update_at'] && (time()-$row['last_update_at'] < $cache_time)) {
            if(strpos($func_name, 'get_search_form_fields') !== false || strpos($func_name, 'get_hike_search_form_fields') !== false) {
              return true;
              exit;
            } else {
              $res = $this->db_query('SELECT value FROM main_cache WHERE code = ?', $db_key);
              $row = mysql_fetch_assoc($res);
              return unserialize($row['value']);
              exit;
            }
          }
        }
      }
      
    }

    if(!$this->is_connected)
      $this->connect();
    $param['exec_function']  = $func_name;
    $param['client_version'] = $this->client_version;
    $param['module_type']    = $this->module_type;
    $param['auth_token']     = $this->auth_token;
    $param['request_info']   = $this->request_info; 
    
    $php_url = 'http://'.$_SERVER['SERVER_NAME'].preg_replace('|\?.*|', '', $_SERVER['REQUEST_URI']);
    $param['agency_url']     = ($this->module_type == 'js')?($this->agency_url):$php_url;
    $param['save_statistic'] = $this->save_statistic;

    if(strtolower($this->get_config('webservice_encoding')) != 'utf-8') {
      array_walk_recursive($param, 'change_charset_array_revert', $this);
    }
    
    $param_serialize = serialize($param);
    $this->proxy->setHTTPEncoding('gzip');
    // Похоже, что настройки свойств в class nusphere_soapclient где-то затираются, 
    // в итоге клиент ждёт ответа 30 секунд. Костыльное решение.
    $this->proxy->response_timeout = 120;
    $result = $this->proxy->_exec($param_serialize);
    
    $result = unserialize(base64_decode($result));    
    
    if(strtolower($this->get_config('webservice_encoding')) != 'utf-8')
      array_walk_recursive($result, 'change_charset_array', $this);
    if(safe($result, 'result_code', 0) > 0) {
      $this->error($result['result_code'], $result['result_value']);
    }
    if($result && $cache_enabled && $this->use_cache && !$this->is_error) {
      if($this->cache_type == 'file') {
        $fp = fopen($cache_filename, 'w');
        fwrite($fp, serialize($result));
        fclose($fp);
      } elseif($this->cache_type == 'session') {
        $_SESSION['webclient'][$func_name][$param_key] = $result;
        $_SESSION['webclient'][$func_name][$param_key.'_date'] = time();
      } elseif($this->cache_type == 'memcached') {
          $umc->set($memcache_key, $result, $cache_time); 
              $umc = new custom_cached_manager();
              $old_keys = $umc->get($this->wsdl_login . 'memcache_keys');
              if(!$old_keys)$old_keys = array();
              $old_keys_ = array_merge($old_keys, array($memcache_key));
              $new_keys = array_unique($old_keys_);
              $umc->set($this->wsdl_login . 'memcache_keys', $new_keys, $cache_time * 2);
      } elseif($this->cache_type == 'db') {
        if((strpos($func_name, 'get_search_form_fields') !== false && $prefix = 'package_') || (strpos($func_name, 'get_hike_search_form_fields') !== false && $prefix = 'hike_')) {
          foreach($result as $table_name => $result_item) {
            $this->db_query("TRUNCATE TABLE {$prefix}{$table_name}");
            foreach($result_item['list'] as $item) {
              $str = '';
              foreach($item as $item_one_name => $item_one) {
                if($item_one_name == 'type_id' || $item_one_name == 'kind_id')
                  $item_one = ',' . $item_one . ',';
                $str .= ( ($str == ''?'':',') . "'" . addslashes($item_one) . "'" );
              }
              $this->db_query("INSERT INTO {$prefix}{$table_name} VALUES ($str)");
            }
          }
        }

        $this->db_query('DELETE FROM main_cache WHERE code = ?', $db_key);
        $this->db_query('INSERT INTO main_cache (code, value, last_update_at) VALUES (?, ?, now())', $db_key, serialize($result));
      }
      

    
    }
    return $result;
  }
  
  function get_package_search_form_fields($param = array()) {
    //$param['hotel_by_country'] = true;
    $param['language'] = $this->get_config('webservice_language');
    $this->package_search_form_data = $this->exec('get_search_form_fields', $param, true);
    $this->package_search_form_data['default_period'] = safe($this->package_search_form_data['country']['list']['0'],'period',21);
    $this->config['date_till_days_def'] = $this->package_search_form_data['default_period'];
  }
  
  function get_hike_search_form_fields($param = array()) {
    $this->hike_search_form_data = $this->exec('get_hike_search_form_fields', $param, true);
  }      
  
  function package_tour_search($param = array(), $cache = false, $current_url = '') {
    if(!$current_url) {
      $param['current_url'] = $_SERVER['REQUEST_URI'];
    } else {
      $param['current_url'] = $current_url;
    }
    if($cache) {
      $param['current_url'] = preg_replace('/callback=jQuery[0-9_]+\&(.*)\&id=[a-zA-Z0-9]+\&(.*)\&_\=[0-9]+/', 'callback=jQuery_deleted_&\1&id=000000&\2&_=0', $param['current_url']);
    }
    $param['language'] = $this->get_config('webservice_language');
    $result = $this->exec('package_tour_search', $param,$cache);
    return $result;
  }
  
  function hike_tour_search($param = array()) {
    $param['language'] = $this->get_config('webservice_language');
    $param['current_url'] = $_SERVER['REQUEST_URI'];
    $result = $this->exec('hike_tour_search', $param);
    return $result;
  }
  
  function hike_tour_price_search($param = array()) {
    $param['language'] = $this->get_config('webservice_language');
    $param['current_url'] = $_SERVER['REQUEST_URI'];
    $result = $this->exec('hike_tour_price_search', $param);
    return $result;
  }  
  
  function get_agency_list($param) {    
    $result = $this->exec('get_agency_list', $param, true);
    return $result;    
  }
  
  function get_agency_cities($param = array()) {
    $result = $this->exec('get_agency_cities', $param, true);
    return $result;
  }
  
  function get_showcase_package_city_to_country($param = array()) {
    $param['language'] = $this->get_config('webservice_language');
    if ($cities = $this->exec('get_showcase_filter', $param, true)) {    
      array_unshift($cities['city'], array("id" => 0, "code" => $this->lang['any_city']));    
      $result = '';
      foreach ($cities['city'] as $city) {
        $result_array[$city['id']] = $city['code'];
        $result .= ' <li><a id="cit_' . $city['id'] . '" class="ittour_showcase_city_name_link" country_id="' . $param['country'] . '" href="javascript:;">' .
                '<span class="ittour_showcase_city_name" rel="' . $city['id'] . '">' . $city['code'] . '</span>' .
                '</a>' .
                '</li>';
      }
      return array('html' => $result, 'cities' => $result_array);
    } else {
      return array('html' => '', 'cities' => array(), 'no_result' => true);
    }
  }

  function get_counttry ($param = array()){
   $param['language'] = $this->get_config('webservice_language');
   $result = $this->exec('get_showcase_filter', $param, true);
   return $resilt['country'];
  }
  
  function get_showcase_filter($param = array()){
   if(!isset($param['use_rus'])) {
     $param['language'] = $this->get_config('webservice_language');
   }
   return $this->exec('get_showcase_filter', $param, false);
  }
  
  function get_showcase_list() {
    $param = array();
    $result = $this->exec('get_showcase_list', $param, true);
    return $result;    
  }

  function get_showcase($code, $include_header = false, $limit = 0, $order = 0, $order_by = 'asc') {
    $param['code'] = $code;
    $param['include_header'] = $include_header;
    $param['limit'] = $limit;
    $param['order'] = $order;
    $param['order_by'] = $order_by;
    $result = $this->exec('get_showcase', $param, true);
    return $result;
  }
  function get_country_showcase($is_hike = false) {
//    $search_array['truncate'] = true;
    $search_array['showcase_type'] = '44';
//    $search_array['items_per_page'] = get('row_count');
    if ($is_hike) {
        $search_array['is_hike'] = $is_hike;
        $search_array['use_uah_price'] = (bool)$this->get_config('is_uah_price_used');
        $search_result = $this->hike_tour_search($search_array,true);
    } else {
        $search_result = $this->package_tour_search($search_array,true);
    }
      
    return $search_result;
  }
  function get_last_min_showcase() {
    $search_array['showcase_type'] = '90';
    $search_result = $this->package_tour_search($search_array,true);
    return $search_result;
  }
  
  function get_country_list() {
    $param = array();
    $result = $this->exec('get_country_list', $param, true);
    return $result;    
  }
  
  function get_default_constant_optimize() {
    $param = array();
    $constants = $this->exec('get_default_constant_optimize', $param, true);    

    foreach($constants as $name => $value)
      if (!array_key_exists($name, $this->config))
        $this->config[$name] = $value;

  }
  
  function get_config($name, $default = null) {
    $result = safe($this->config, $name);
	if($default && !$result) return $default; 
    return $result;
  }
  
  function set_config($name, $value) {
    $this->config[$name] = $value;
  }
  
  function get_hike_search_filtered_field($request_type, $event_owner_level, $country_ids, $transport_ids, $city_ids, $tour_city_ids, $width_class = null) {
    $country_ids = array_diff($country_ids, array(''));
    $transport_ids = array_diff($transport_ids, array(''));
    $city_ids = array_diff($city_ids, array(''));
    $tour_city_ids = array_diff($tour_city_ids, array(''));
    
    $search_form_field = $this->get_hike_search_form_fields();
    $field = $this->prepare_optimize_hike_search_filtered_field($request_type, $event_owner_level, $country_ids, $transport_ids, $city_ids, $tour_city_ids);

    if($request_type == 'ajax') {
      if($event_owner_level == 4){
        unset($field['transport']);
        unset($field['city']);
        unset($field['tour_city']);
      } elseif($event_owner_level == 3){
        unset($field['transport']);
        unset($field['city']);
      } elseif($event_owner_level == 2){
        unset($field['transport']);
      }
      $json = new FastJSON; 
      return $json->encode($field);
    }elseif($request_type == 'data'){
      $field['city']['data'] = $field['city'];
      $field['tour_city']['data'] = $field['tour_city'];
      $width_class = empty($width_class) ? '' : " width_class=\"$width_class\"";
      $field['city']['html'] = '';
      foreach($field['city']['data'] as $city){
        $field['city']['html'] .= '<li>
                                  <a id="cit_' .$city['id']. '" class="ittour_showcase_city_name_link" country_id="' .$city['id']. '" href="javascript:;">
                                  <span class="ittour_showcase_city_name" rel="' .$city['id']. '"' . $width_class . '>' .$city['name']. '</span>
                                  </a>
                                  </li>';
      }
      $field['transport']['data'] = $field['transport'];
      $field['transport']['html'] = '';
      foreach($field['transport']['data'] as $transport){
        $field['transport']['html'] .= '<li>
                                  <a id="cit_' .$transport['id']. '" class="ittour_showcase_transport_name_link" country_id="' .$transport['id']. '" href="javascript:;">
                                  <span class="ittour_showcase_transport_name" rel="' .$transport['id']. '">' .$transport['name']. '</span>
                                  </a>
                                  </li>';
      }
      
      $json = new FastJSON; 
      return $json->encode($field);
    } else {
      return $field;
    }
  }
  
  function get_package_search_filtered_field($request_type, $event_owner_level, $country_ids, $region_ids, $hotel_rating_ids, $tour_type, $tour_kind, $date_till, $departure_city, $region_list_ids = array()) {
    // add new params if user change in search form
    $date_till_user_set = trim($date_till);//string => d.m.y
    $departure_city_user_set = trim($departure_city);//integer => 1764
    
    $country_ids = array_diff($country_ids, array(''));
    $region_ids = array_diff($region_ids, array(''));
    $hotel_rating_ids = array_diff($hotel_rating_ids, array(''));

    $search_form_field = $this->get_package_search_form_fields();
    $field = $this->prepare_optimize_package_search_filtered_field($request_type, $event_owner_level, $country_ids, $region_ids, $hotel_rating_ids, $tour_type, $tour_kind, $date_till_user_set, $departure_city_user_set, $region_list_ids);
    if($request_type == 'ajax') {
      if($event_owner_level == 1)
        unset($field['country']);
      if($event_owner_level == 2) {
        unset($field['country']);
        unset($field['region']);
      }
      $json = new FastJSON; 
      return $json->encode($field);
    }elseif($request_type == 'data'){
        
        $field['departure_city'] = array('data' => $field['departure_city']);
        $field['hotel'] = array('data' => $field['hotel']);
        $field['region'] = array('data' => $field['region']);
        $field['period']['data'] = $field['period'];
        
        switch ($event_owner_level) {
            case 1:
                unset($field['country']);
            break;
            case 2:
                unset($field['country']);
                unset($field['region']);
            break;
            case 4:
                unset($field['country']);
                unset($field['region']);
                unset($field['departure_city']);
            break;
        }
        
        $json = new FastJSON; 
        return $json->encode($field);
      
    } else {
      return $field;
    }
  }
  
  function set_debug($str_number, $comment = ''){  
    $mtime = microtime();
    $mtime = explode(" ",$mtime);
    $mtime = $mtime[1] + $mtime[0];
    
    $this->debug_info[] = array('str_num' => $str_number . $comment, 'time' => $mtime);
    $curunt_index = count($this->debug_info) - 1;
    $this->debug_info[$curunt_index]['start_dif'] = sprintf("%f", (round($this->debug_info[$curunt_index]['time'] - $this->debug_info[0]['time'], 6)));
    if($curunt_index != 0)
      $this->debug_info[$curunt_index]['prev_dif'] = sprintf("%f", (round($this->debug_info[$curunt_index]['time'] - $this->debug_info[$curunt_index-1]['time'], 6)));
  }
  
  function save_debug(){
    $fp = fopen(dirname(dirname(__FILE__)) . '/_logs/log.txt', 'a');
    fwrite($fp, "========================================================================\n========================================================================\n\n");
    fwrite($fp, print_r($this->debug_info, 1));
    fclose($fp);
  }
  
  function package_order_save($tour_id, $sharding_rule_id, $first_name, $second_name, $middle_name, $city, $email, $phone, $comment, $currency_id, $price, $flights_user_choosed = array()) { 
    $param = array( 'tour_id' => $tour_id
                  , 'sharding_rule_id' => $sharding_rule_id
                  , 'first_name' => $first_name
                  , 'second_name' => $second_name
                  , 'middle_name' => $middle_name
                  , 'city' => $city
                  , 'email' => $email
                  , 'phone' => $phone
                  , 'comment' => $comment
                  , 'currency_id' => $currency_id
                  , 'price' => $price
                  , 'flights_user_choosed' => $flights_user_choosed
    );
    $this->exec('package_order_save', $param);  
  }
  
  function hike_order_save($tour_id, $first_name, $second_name, $middle_name, $city, $email, $phone, $comment, $accomodation, $price, $departure_date, $price_id, $currency_id = null) {
    $param = array( 'tour_id' => $tour_id
                  , 'first_name' => null
                  , 'second_name' => $first_name
                  , 'middle_name' => $middle_name
                  , 'city' => $city
                  , 'email' => $email
                  , 'phone' => $phone
                  , 'comment' => $comment
                  , 'accomodation' => $accomodation
                  , 'price' => $price
                  , 'price_id' => $price_id
                  , 'departure_date' => $departure_date
                  , 'currency_id' => $currency_id
    );
    $this->exec('hike_order_save', $param);
  }

  function get_optimize_package_country_list($add_line_with_all_item, $tour_type = 0, $tour_kind = 0) {
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      $country_list = array();
      
      foreach($this->package_search_form_data['country']['list'] as $country) {
        if( ($tour_type == 0 || strpos($country['type_id'], $tour_type) !== false) && ($tour_kind == 0 || in_array($tour_kind, explode(',', $country['kind_id']))) )
          $country_list[] = array('id' => $country['id'], 'name' => $country['name'], 'flag_small' => $country['flag_small'], 'period' => $country['period']);
      }
    } elseif($this->cache_type == 'db') {
      $sql_where = '';
      if($tour_type)
        $sql_where .= placeholder(" AND type_id LIKE CONCAT('%,', ?, ',%')", $tour_type);
      if($tour_kind)
        $sql_where .= placeholder(" AND kind_id LIKE CONCAT('%,', ?, ',%')", $tour_kind);
      $country_list = $this->db_rows("SELECT * FROM package_country WHERE 1 = 1" . $sql_where);
    }
    
    if($add_line_with_all_item)
      array_unshift($country_list, array("id" => 0, "name" => $this->lang['all_countries']));
    return $country_list;
  }
  
  function get_optimize_package_region_list($add_line_with_all_item, $country_ids, $tour_type = 0, $tour_kind = 0) {
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      $region_list = array();

      foreach($this->package_search_form_data['region']['list'] as $region) {
        if(($tour_type == 0 || strpos($region['type_id'], $tour_type) !== false) && ($tour_kind == 0 || in_array($tour_kind, explode(',', $region['kind_id']))) && in_array($region['country_id'], $country_ids))
          $region_list[] = array('id' => $region['id'], 'name' => $region['name']);
      }
    } elseif($this->cache_type == 'db') {
      $sql_where = '';
      if($tour_type)
        $sql_where .= placeholder(" AND type_id LIKE CONCAT('%,', ?, ',%')", $tour_type);
      if($tour_kind)
        $sql_where .= placeholder(" AND kind_id LIKE CONCAT('%,', ?, ',%')", $tour_kind);
      $region_list = $this->db_rows("SELECT * FROM package_region WHERE country_id IN (?@)".$sql_where, $country_ids);
    }
    
    if($add_line_with_all_item)
      array_unshift($region_list, array("id" => 0, "name" => $this->lang['all_regions']));
    return $region_list;
  }

  function get_optimize_package_hotel_list($add_line_with_all_item, $country_ids, $region_ids, $hotel_rating_ids, $tour_type = 0, $tour_kind = 0) {
    if(!$hotel_rating_ids)
      $hotel_rating_ids = array(0);
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {      
      $hotel_list = array();
      
      foreach($this->package_search_form_data['hotel']['list'] as $hotel) {
        if(($tour_type == 0 || strpos($hotel['type_id'], $tour_type) !== false) && ($tour_kind == 0 || in_array($tour_kind, explode(',', $hotel['kind_id']))) && ( in_array($hotel['region_id'], $region_ids) OR ( (count($region_ids) == 0 || array_search(0, $region_ids) !== false) && in_array($hotel['country_id'], $country_ids)) ) && in_array($hotel['rating_id'], $hotel_rating_ids) )
          $hotel_list[] = array('id' => $hotel['id'], 'name' => $hotel['name']);
      }
    } elseif($this->cache_type == 'db') {
      $sql_where = '';
      if($tour_type)
        $sql_where .= placeholder(" AND type_id LIKE CONCAT('%,', ?, ',%')", $tour_type);
      if($tour_kind)
        $sql_where .= placeholder(" AND kind_id LIKE CONCAT('%,', ?, ',%')", $tour_kind);
      if($region_ids && !in_array(0, $region_ids))
        $sql_where .= placeholder(" AND region_id IN (?@)", $region_ids);
      if($country_ids)
        $sql_where .= placeholder(" AND country_id IN (?@)", $country_ids);
      if($hotel_rating_ids)
        $sql_where .= placeholder(" AND rating_id IN (?@)", $hotel_rating_ids);
      $hotel_list = $this->db_rows("SELECT * FROM package_hotel WHERE 1 = 1".$sql_where);
    }
    
    if($add_line_with_all_item)
      array_unshift($hotel_list, array("id" => 0, "name" => $this->lang['all_hotels']));
    return $hotel_list;
  }

  function get_optimize_package_departure_city_list($add_line_with_all_item, $country_ids = array(), $region_ids = array(), $tour_type = 0, $tour_kind = 0) {
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      foreach($this->package_search_form_data['departure_city']['list'] as $city) {
        $departure_all_city_list[$city['id']] = $city['name'];
      }
      
      $departure_city_list = array();

      if(count($region_ids) && array_search(0, $region_ids) === false) {
        foreach($this->package_search_form_data['departure_city_by_region']['list'] as $departure_city) {
          if($departure_city['from_city_id'] && ($tour_type == 0 || $departure_city['type_id'] = $tour_type) && ($tour_kind == 0 || $departure_city['kind_id'] == $tour_kind) && in_array($departure_city['to_region_id'], $region_ids))
            $departure_city_list[] = array('id' => $departure_city['from_city_id'], 'name' => $departure_all_city_list[$departure_city['from_city_id']]);
        }
      } elseif(count($country_ids)) {
        foreach($this->package_search_form_data['departure_city_by_country']['list'] as $departure_city) {
          if(safe($departure_all_city_list, $departure_city['from_city_id']) && ($tour_type == 0 || strpos($departure_city['type_id'], $tour_type) !== false) && ($tour_kind == 0 || in_array($tour_kind, explode(',', $departure_city['kind_id']))) && in_array($departure_city['to_country_id'], $country_ids))
            $departure_city_list[] = array('id' => $departure_city['from_city_id'], 'name' => $departure_all_city_list[$departure_city['from_city_id']]);
        }
      } else {
        $departure_city_list = $this->package_search_form_data['departure_city']['list'];
      }
    } elseif($this->cache_type == 'db') {
      $sql_where = '';
      if($tour_type)
        $sql_where .= placeholder(" AND d.type_id LIKE CONCAT('%,', ?, ',%')", $tour_type);
      if($tour_kind)
        $sql_where .= placeholder(" AND d.kind_id LIKE CONCAT('%,', ?, ',%')", $tour_kind);
      if(count($region_ids) && array_search(0, $region_ids) === false) {
        $sql_where .= placeholder(" AND d.to_region_id IN (?@)", $region_ids);
        $departure_city_list = $this->db_rows("SELECT c.* FROM package_departure_city_by_region as d INNER JOIN package_departure_city c ON c.id = d.from_city_id WHERE 1 = 1".$sql_where);
      } elseif(count($country_ids)) {
        $sql_where .= placeholder(" AND d.to_country_id IN (?@)", $country_ids);
        $departure_city_list = $this->db_rows("SELECT c.* FROM package_departure_city_by_country as d INNER JOIN package_departure_city c ON c.id = d.from_city_id WHERE 1 = 1".$sql_where);
      } else {
        $departure_city_list = $this->db_rows("SELECT c.* FROM package_departure_city c WHERE 1 = 1");
      }
    }
    
    if($add_line_with_all_item)
      array_unshift($departure_city_list, array("id" => 0, "name" => $this->lang['any_city']));
    return $departure_city_list;
    
  }

  function get_optimize_package_tour_kind_list() {
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      return $this->package_search_form_data['tour_kind']['list'];
    } elseif($this->cache_type == 'db') {
      return $this->db_rows('SELECT * FROM package_tour_kind');
    }
  }

  function get_optimize_package_food_list() {
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      return $this->package_search_form_data['food']['list'];
    } elseif($this->cache_type == 'db') {
      return $this->db_rows('SELECT * FROM package_food');
    }
  }

  function get_optimize_package_hotel_rating_list() {
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      return $this->package_search_form_data['hotel_rating']['list'];
    } elseif($this->cache_type == 'db') {
      return $this->db_rows('SELECT * FROM package_hotel_rating');
    }
  }

  function get_optimize_package_hotel_rating_full_list() {
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      return $this->package_search_form_data['hotel_rating_full']['list'];
    } elseif($this->cache_type == 'db') {
      return $this->db_rows('SELECT * FROM package_hotel_rating_full');
    }
  }

  function get_optimize_package_adult_list() {
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      return $this->package_search_form_data['adult']['list'];
    } elseif($this->cache_type == 'db') {
      return $this->db_rows('SELECT * FROM package_adult');
    }
  }

  function get_optimize_package_children_list() {
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      return $this->package_search_form_data['children']['list'];
    } elseif($this->cache_type == 'db') {
      return $this->db_rows('SELECT * FROM package_children');
    }
  }

  function get_optimize_package_night_from_list() {
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      return $this->package_search_form_data['night_from']['list'];
    } elseif($this->cache_type == 'db') {
      return $this->db_rows('SELECT * FROM package_night_from');
    }
  }

  function get_optimize_package_night_to_list() {
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      return $this->package_search_form_data['night_to']['list'];
    } elseif($this->cache_type == 'db') {
      return $this->db_rows('SELECT * FROM package_night_to');
    }
  }

  function get_package_item_by_name($item_name, $name) {
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      foreach($this->package_search_form_data[$item_name]['list'] as $arr_item)
        if($arr_item['name'] == $name)
          return $arr_item;
      return false;
    } if($this->cache_type == 'db') {
      return $this->db_row("SELECT * FROM package_{$item_name} WHERE name = ?", $name);
    }
  }
  
  function get_hike_item_by_name($item_name, $name) {
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      foreach($this->hike_search_form_data[$item_name]['list'] as $arr_item)
        if($arr_item['name'] == $name)
          return $arr_item;
      return false;
    } if($this->cache_type == 'db') {
      return $this->db_row("SELECT * FROM hike_{$item_name} WHERE name = ?", $name);
    }
  }

  function prepare_optimize_package_search_filtered_field($request_type, $event_owner_level, $country_ids, $region_ids, $hotel_rating_ids, $tour_type = 0, $tour_kind = 0, $date_till_user_set = '', $departure_city_user_set = 0, $region_list_ids = array()){
    global $dc;
    $country_list = $this->get_optimize_package_country_list(false, $tour_type, $tour_kind);
    if(count($country_ids) == 0) $country_ids[] = $country_list[0]['id']; // переключение типа тура
    $region_list = $this->get_optimize_package_region_list(true, $country_ids, $tour_type, $tour_kind);
    
    // set user param, if user change => transit On or transit OFF
    if (count($region_list_ids)>0) {
        $region_ids = $region_list_ids;
    }
    
    $hotel_list = $this->get_optimize_package_hotel_list(true, $country_ids, $region_ids, $hotel_rating_ids, $tour_type, $tour_kind);
    $departure_city_list = $this->get_optimize_package_departure_city_list(true, $country_ids, $region_ids, $tour_type, $tour_kind);
        
    if ($date_till_user_set != '') {
        // set new period
        $date_begin = date("d.m.Y");//current date
        $date_end = $date_till_user_set;//"28.12.13"; for test
        
        $date_end_tmp = explode(".",$date_end);
        $date_end_tmp[2] =  '20'. $date_end_tmp[2];
        $date_end = implode(".", $date_end_tmp);
        
        $period = (strtotime($date_end) - strtotime($date_begin)) / (60*60*24);
    } else {
        // default period
        $period = 21;        
        if($country_ids) {
            foreach($country_list as $country) {
                if(in_array($country['id'], $country_ids)) {
                    $period = $country['period'];
                    break;
                }
            }
        }
    }
    
    if($request_type == 'ajax'){
      if(!$region_ids) $region_ids = array(0);
      $country_list = $this->get_option_from_array($country_list, $country_ids);
      $region_list = $this->get_option_from_array($region_list, $region_ids);
      $hotel_list = $this->get_option_from_array($hotel_list, 0);
      
      if ($departure_city_user_set != 0) {
          // user set new departure_city
          $departure_city_set = $departure_city_user_set;
      } else {
          // default departure_city
          //$departure_city_set = $this->config['departure_city_def'];// work version set defauld city // Kiev
          $departure_city_set = 0;
      }
      
      $departure_city_list = $this->get_option_from_array($departure_city_list, $departure_city_set);
    }
    

    // Begin set relation counrty <=> tour_type as in page http://www.ittour.com.ua/tour-search-all-ajax.html
    // Set params for sql
    $sql_table_region = 'all_region_flight';
    
    // Set current country_id
    $current_country_id = 0;
    if (count($country_ids)>1) {
      $current_country_id = $country_ids[count($country_ids)-1];
    } elseif(isset($country_ids[0])) {
      $current_country_id = $country_ids[0];
    }
    
    // Get data for item country: tour_type, etc
    if ($current_country_id !=0) {
      $sql = $this->get_country_tour_type(array('sql_table_region' => $sql_table_region
                                              , 'current_country_id' => $current_country_id));
    }
    
    // Set dafault country_tour_type
    $country_tour_type = 0;// 0 => All, 1 => transport On, 2 => transport Off
    
    // Set not dafault country_tour_type
    if (count($sql)>0) {
      foreach ($sql as $item) {
        if ($item['id'] == $current_country_id) {
          switch ($item['type_id']) {
            case '1':
              $country_tour_type = 1;
              break;
            case '2':
              $country_tour_type = 2;
              break;
            case '1,2':
              $country_tour_type = 0;
              break;
          }
        }
      }
    }
    // End set relation counrty <=> tour_type as in page http://www.ittour.com.ua/tour-search-all-ajax.html
    
    // Check parameter country_tour_type (tour_type) if user change country or type
    if (isset($tour_type)) {
      $country_tour_type = $tour_type;
    }
    
    $field = array('country' => $country_list, 'region' => $region_list, 'hotel' => $hotel_list, 'departure_city' => $departure_city_list, 'period' => $period, 'country_tour_type' => $country_tour_type);
    
    return $field;
  }
  
  function get_optimize_hike_country_list($add_line_with_all_item) {
  
    $country_list = array();
  
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      $country_list = safe($this->hike_search_form_data['country'],'list',array());
    } elseif($this->cache_type == 'db') {
      $country_list = $this->db_rows("SELECT * FROM hike_country");
    }
    
    if($add_line_with_all_item)
      array_unshift($country_list, array("id" => 0, "name" => $this->lang['all_countries'], "flag_small" => 'ico_world.gif'));
    return $country_list;
  }
  
  function get_optimize_hike_transport_list($add_line_with_all_item, $country_ids) {
  
    $transport_list = array();  
  
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      $transport_id_added = array();      
      $all_city = (!count($country_ids) || in_array(0, $country_ids))?true:false;
      foreach($this->hike_search_form_data['city_transport']['list'] as $city_transport) {
        if(!in_array($city_transport['transport_type_id'], $transport_id_added) && ($all_city || in_array($city_transport['country_id'], $country_ids))){
          $transport_list[] = array('id' => $city_transport['transport_type_id'], 'name' => $this->hike_search_form_data['transport']['list'][$city_transport['transport_type_id']]['name']);          
          $transport_id_added[] = $city_transport['transport_type_id'];
        }
      }
    } elseif($this->cache_type == 'db') {
      $sql_where = '';
      if($country_ids)
        $sql_where .= placeholder(" AND ct.country_id IN (?@)", $country_ids);
      $transport_list = $this->db_rows("SELECT DISTINCT t.id, t.name FROM hike_transport t INNER JOIN hike_city_transport ct ON ct.transport_type_id = t.id WHERE 1 = 1" . $sql_where);
    }
    
    if($add_line_with_all_item)
      array_unshift($transport_list, array("id" => 0, "name" => $this->lang['all_type']));
    return $transport_list;
  }
  
  function get_optimize_hike_city_list($add_line_with_all_item, $country_ids, $transport_ids) {
  
    $city_list = array();
  
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      $city_id_added = array();
      $all_transport = (!count($transport_ids) || in_array(0, $transport_ids))?true:false;
      $all_city = (!count($country_ids) || in_array(0, $country_ids))?true:false;
      if(!is_array($this->hike_search_form_data['city_transport']['list'])) {
        $this->hike_search_form_data['city_transport']['list'] = array();
      }
      foreach($this->hike_search_form_data['city_transport']['list'] as $city_transport) {
        if(!in_array($city_transport['from_city_id'], $city_id_added) && ($all_city || in_array($city_transport['country_id'], $country_ids)) && ($all_transport || in_array($city_transport['transport_type_id'], $transport_ids)) && safe($this->hike_search_form_data['city']['list'], $city_transport['from_city_id'])){
          $city_list[] = array('id' => $city_transport['from_city_id'], 'name' => $this->hike_search_form_data['city']['list'][$city_transport['from_city_id']]['name']);          
          $city_id_added[] = $city_transport['from_city_id'];
        }
      }
    } elseif($this->cache_type == 'db') {
      $sql_where = '';
      if($country_ids)
        $sql_where .= placeholder(" AND ct.country_id IN (?@)", $country_ids);
      if($transport_ids)
        $sql_where .= placeholder(" AND ct.transport_type_id IN (?@)", $transport_ids);
      $city_list = $this->db_rows("SELECT DISTINCT c.id, c.name FROM hike_city c INNER JOIN hike_city_transport ct ON ct.from_city_id = c.id WHERE 1 = 1" . $sql_where);
    }

    if($add_line_with_all_item)
      array_unshift($city_list, array("id" => 0, "name" => $this->lang['all_city']));
    return $city_list;
  }
  
  function get_optimize_hike_tour_city_list($add_line_with_all_item, $country_ids, $transport_ids, $city_ids) {
  
    $tour_city_list = array();
  
    if($this->cache_type == 'file' || $this->cache_type == 'memcached') {
      $tour_city_id_added = array();
      $all_transport = (!count($transport_ids) || in_array(0, $transport_ids))?true:false;
      $all_city = (!count($city_ids) || in_array(0, $city_ids))?true:false;

      foreach($this->hike_search_form_data['tour_city']['list'] as $tour_city) {
        if(!in_array($tour_city['tour_city_id'], $tour_city_id_added) && in_array($tour_city['country_id'], $country_ids) && ($all_transport || in_array($tour_city['transport_type_id'], $transport_ids))  && ($all_city || in_array($tour_city['from_city_id'], $city_ids)) ){
          $tour_city_list[] = array('id' => $tour_city['tour_city_id'], 'name' => $tour_city['tour_city_name']);          
          $tour_city_id_added[] = $tour_city['tour_city_id'];
        }
      }
    } elseif($this->cache_type == 'db') {
      $sql_where = '';
      if($country_ids)
        $sql_where .= placeholder(" AND ct.country_id IN (?@)", $country_ids);
      if($transport_ids)
        $sql_where .= placeholder(" AND ct.transport_type_id IN (?@)", $transport_ids);
      if($city_ids)
        $sql_where .= placeholder(" AND ct.from_city_id IN (?@)", $city_ids);
      $tour_city_list = $this->db_rows("SELECT DISTINCT ct.tour_city_id as id, ct.tour_city_name as name FROM hike_tour_city ct WHERE 1 = 1" . $sql_where);
    }
    
    if($add_line_with_all_item)
      array_unshift($tour_city_list, array("id" => 0, "name" => $this->lang['not_matter']));
    return $tour_city_list;
  }
  
  function prepare_optimize_hike_search_filtered_field($request_type, $event_owner_level, $country_ids, $transport_ids, $city_ids, $tour_city_ids){      
  
    $transport_list = $this->get_optimize_hike_transport_list(true, $country_ids);
    $city_list = $this->get_optimize_hike_city_list(true, $country_ids, $transport_ids);
    $tour_city_list = $this->get_optimize_hike_tour_city_list(true, $country_ids, $transport_ids, $city_ids);
      
    if($request_type == 'ajax'){                                                      
      if(!$transport_ids) $transport_ids = array(0);
      if(!$city_ids) $city_ids = array(0);
      if(!$tour_city_ids) $tour_city_ids = array(0);
      
      $transport_list = $this->get_option_from_array($transport_list, $transport_ids);
      $city_list = $this->get_option_from_array($city_list, $city_ids);
      $tour_city_list = $this->get_option_from_array($tour_city_list, $tour_city_ids);
    }
    
    $field = array('transport' => $transport_list, 'city' => $city_list, 'tour_city' => $tour_city_list, 'operator' => array());
    
    return $field;
  }

  function get_option_from_array(&$list, $select_item_ids) {
    if(is_numeric($select_item_ids))
      $select_item_ids = array($select_item_ids);
    $str = '';
    foreach($list as $item) {
      $str .= "<option value='".$item['id']."' ".(in_array($item['id'], $select_item_ids)?"selected='selected'":"")." >".$item['name']."</option>\n";
    }
    return $str;
  }

  function get_defaul_values($array) {
    $result = array();
    foreach($array as $value)
      $result[$value] = true;
    return $result;
  }

  function fill_default_package_form_value(){

    // рейтинг отеля
    $hotel_rating_def = explode(',', $this->config['hotel_rating_def']);
    $hotel_rating_default = array();
    foreach($hotel_rating_def as $hotel_rating) {
      $item = $this->get_package_item_by_name('hotel_rating', $hotel_rating);
      if($item)
        $hotel_rating_default[$item['id']] = true;
    }
    // -----------------

    // питание
    $food_def = explode(',', $this->config['food_def']);
    $food_default = array();
    foreach($food_def as $food) {
      $item = $this->get_package_item_by_name('food', $food);
      if($item)
        $food_default[$item['id']] = true;
    }
    // -----------------

    // страна
    $country_default = array();
    $item = $this->get_package_item_by_name('country', $this->config['country_def']);
    if($item)
      $country_default[$item['id']] = true;

    $res = array('country' => $country_default
               , 'region' => array(0 => true)
               , 'hotel' => array(0 => true)
               , 'hotel_rating' => $hotel_rating_default
               , 'food' => $food_default
               , 'night_from' => $this->config['night_from_def']
               , 'night_till' => $this->config['night_till_def']
               , 'price_from' => 0
               , 'price_till' => $this->config['price_till_def']
               , 'date_from' => date('d.m.y', time()+$this->config['date_from_days_def']*86400)
               , 'date_till' => date('d.m.y', time()+$this->config['date_till_days_def']*86400)
               , 'departure_city' => $this->config['departure_city_def']
               , 'operator' => $this->config['operator_def'] ? $this->config['operator_def'] : 0
               , 'adult' => $this->config['adult_amount_def']
               , 'children' => $this->config['child_amount_def']
               , 'child1_age' => $this->config['child_age_def']
               , 'child2_age' => $this->config['child_age_def']
               , 'child3_age' => $this->config['child_age_def']
               , 'items_per_page' => $this->config['item_per_page_def']
               , 'requested_tour' => ''
               , 'tour_kind' => 0
              );
    return $res;
  }
  
  function fill_default_hike_form_value(){
   
    // страна
    $country_default = array();
    if($this->config['hike_country_def'])
      $item = $this->get_hike_item_by_name('country', $this->config['hike_country_def']);
    if(!isset($item) || !$item) {
      $temp = $this->get_optimize_hike_country_list(false);
      $item = array_shift($temp);
    }
    $country_default[$item['id']] = true;
      
    $res = array('country' => $country_default
               , 'transport' => array(0 => true)
               , 'city' => array(0 => true)
               , 'transport_city' => array(0 => true)
               , 'operator' => array(0 => true)
               , 'items_per_page' => $this->config['item_per_page_def']
               , 'hike_date_from' => date('d.m.y', time()+$this->config['hike_date_from_days_def']*86400)
               , 'hike_date_till' => date('d.m.y', time()+$this->config['hike_date_till_days_def']*86400)
               , 'hike_price_till' => $this->config['hike_price_till_def']
              );
    return $res;
  }
  
  function get_bg_image_url($image_prefix, $module_type, $background_color, $border_color, $tab_noactive_background, $opacity_name = '') {
    $path_to_image = $this->config['modules_path'] . "images/{$image_prefix}_{$module_type}.png";
    $source_full_path_to_image = $this->config['modules_path'] . "images/bg_cache/{$image_prefix}_{$module_type}_{$background_color}_{$border_color}_{$tab_noactive_background}.png";
    $source_relative_path_to_image = $this->config['modules_url'] . "images/bg_cache/{$image_prefix}_{$module_type}_{$background_color}_{$border_color}_{$tab_noactive_background}.png";
    
    if(file_exists($source_full_path_to_image))
      return $source_relative_path_to_image;

    $img = imagecreatefrompng($path_to_image);    
    
    imagetruecolortopalette($img, true, 256);
    for ($i = imagecolorstotal($img); $i--;){
      $d = imagecolorsforindex($img, $i);
      // change background
      if ($d['red']==0xF3 && $d['green']==0xF4 && $d['blue']==0xF7)
        imagecolorset($img, $i, hexdec(substr($background_color, 0, 2)), hexdec(substr($background_color, 2, 2)), hexdec(substr($background_color, 4, 2)));
      // change border
      if ($d['red']==0xC2 && $d['green']==0xC5 && $d['blue']==0xD2)
        imagecolorset($img, $i, hexdec(substr($border_color, 0, 2)), hexdec(substr($border_color, 2, 2)), hexdec(substr($border_color, 4, 2)));
      // change tab noactive background
      if ($d['red']==0xE0 && $d['green']==0xE2 && $d['blue']==0xE8)
        imagecolorset($img, $i, hexdec(substr($tab_noactive_background, 0, 2)), hexdec(substr($tab_noactive_background, 2, 2)), hexdec(substr($tab_noactive_background, 4, 2)));
    }
    
    if(file_exists( str_replace('.png', $opacity_name.'_opacity.png', $path_to_image) )) {
      $opacity_name = $opacity_name.'_opacity.png';
    } else {
      $opacity_name = '_opacity.png';
    }
    if(file_exists( str_replace('.png', $opacity_name, $path_to_image) )) {
      $imgdest = imagecreatetruecolor(imagesx($img), imagesy($img));
      imagealphablending($img, true);
      imagesavealpha($img, true);
      $trans_colour = imagecolorallocatealpha($imgdest, 0, 0, 0, 127);
      imagefill($imgdest, 0, 0, $trans_colour);
      imageAlphaBlending($imgdest, true);
      imagesavealpha($imgdest, true);
      imagecopy($imgdest, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));      

      $img_opacity = imagecreatefrompng(str_replace('.png', $opacity_name, $path_to_image));
      imagealphablending($img_opacity, false);
      imagesavealpha($img_opacity, true);
      imagecopy($imgdest, $img_opacity, 0, 0, 0, 0, imagesx($img), imagesy($img));
      imagedestroy($img_opacity);
      imagedestroy($img);
    } else
      $imgdest = &$img;

    imagepng($imgdest, $source_full_path_to_image);    
    imagedestroy($imgdest);    
    return $source_relative_path_to_image;

  }
 
  function get_bg_showcase_image_url($image_name, $color_replace, $image_name_suffix = '') {
    
    $path_to_image = $this->config['modules_path'] . "images/bg_replace/{$image_name}{$image_name_suffix}.png";
    $file_color_name = $image_name . $image_name_suffix . '_' . implode('_', $color_replace);
    $source_full_path_to_image = $this->config['modules_path'] . "images/bg_cache/{$file_color_name}.png";
    $source_relative_path_to_image = $this->config['modules_url'] . "images/bg_cache/{$file_color_name}.png";
    
    if(file_exists($source_full_path_to_image))
      return $source_relative_path_to_image;

    $img = imagecreatefrompng($path_to_image);    
    
    imagetruecolortopalette($img, true, 256);
    for ($i = imagecolorstotal($img); $i--;){
      $d = imagecolorsforindex($img, $i);
      
      $img_key = 'r'.$d['red'].'g'.$d['green'].'b'.$d['blue'];
      if($color = safe($color_replace,$img_key)) {
        if($color == 'transparent') {
          imagecolortransparent($img,$i);
        } else {
          imagecolorset($img, $i, hexdec(substr($color, 0, 2)), hexdec(substr($color, 2, 2)), hexdec(substr($color, 4, 2)));
        }
      }

    }

    if(file_exists( str_replace('.png', '_opacity.png', $path_to_image) )) {
      $imgdest = imagecreatetruecolor(imagesx($img), imagesy($img));
      imagealphablending($img, true);
      imagesavealpha($img, true);
      $trans_colour = imagecolorallocatealpha($imgdest, 0, 0, 0, 127);
      imagefill($imgdest, 0, 0, $trans_colour);
      imageAlphaBlending($imgdest, true);
      imagesavealpha($imgdest, true);
      imagecopy($imgdest, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));      

      $img_opacity = imagecreatefrompng(str_replace('.png', '_opacity.png', $path_to_image));
      imagealphablending($img_opacity, false);
      imagesavealpha($img_opacity, true);
      imagecopy($imgdest, $img_opacity, 0, 0, 0, 0, imagesx($img), imagesy($img));
      imagedestroy($img_opacity);
      imagedestroy($img);
    } else
      $imgdest = &$img;

    imagepng($imgdest, $source_full_path_to_image);    
    imagedestroy($imgdest);    
    return $source_relative_path_to_image;

  }
  
  
  function parse_showcase_search_url($url) {
    $search_array['price_from'] = 0;
    $url = urldecode($url);
    foreach(explode('&', $url) as $param) {
      $param_array = explode('=', $param);
      $value = $param_array[1];
      if(trim($value) == '')
        continue;
      switch ($param_array[0]) {
        case 'country':
          foreach (explode(' ', $value) as $hr) {
              $search_array['countries'][] = $hr;
          }
            
          break;
        case 'departure_city':
            $search_array['departure_cities'][] = $value;
          break;
        case 'hotel_rating':
          foreach (explode(' ', $value) as $hr)
            $search_array['hotel_ratings'][] = $hr;
          break;
        case 'default_form_select': 
              $search_array['default_form'] = 1;
          break;
        case 'food':
          foreach (explode(' ', $value) as $hr)
            if($hr != 0)
              $search_array['meal_types'][] = $hr;
          break;
        case 'night_from':
          $search_array['durations']['0']['from'] = $value;
          break;
        case 'night_till':
          $search_array['durations']['0']['till'] = $value;
          break;
         case 'items_per_page':
          $search_array['items_per_page'] = $value;
          break;
         case 'transport':
           if($value) {
             $search_array['transport_types'][] = $value;
           }
          break;
        case 'type':
          if($value == 42)
            $search_array['date_till'] = date('Y-m-d', time()+30*24*60*60); // 7 дней
          elseif($value == 44)
            $search_array['date_till'] = date('Y-m-d', time()+120*24*60*60); // 120 дней
          break;
      }
    }
    return $search_array;
  }
  
  function prepare_showcase_search_array($search_array) {
  
    if(!array_key_exists('date_from', $search_array))
      $search_array['date_from'] = date('Y-m-d', time()+$this->get_config('showcase_date_from_days_def')*86400);

    if(!array_key_exists('date_till', $search_array))
      $search_array['date_till'] = date('Y-m-d', time()+$this->get_config('showcase_date_till_days_def')*86400);

    if(!array_key_exists('adult_amount', $search_array))
      $search_array['adult_amount'] = $this->get_config('showcase_adult_amount_def');

    if(!array_key_exists('items_per_page', $search_array))
      $search_array['items_per_page'] = 12;

    if(!array_key_exists('types', $search_array)) {
      $search_array['types'] = array(1,2);
    }

    return $search_array;
  }

  function prepare_showcase_hike_search_array($search_array) {
  
    if(!array_key_exists('date_from', $search_array))
      $search_array['date_from'] = date('Y-m-d', time()+$this->get_config('showcase_hike_date_from_days_def')*86400);

    if(!array_key_exists('date_till', $search_array))
      $search_array['date_till'] = date('Y-m-d', time()+$this->get_config('showcase_hike_date_till_days_def')*86400);

    if(!array_key_exists('adult_amount', $search_array))
      $search_array['adult_amount'] = $this->get_config('showcase_hike_adult_amount_def');

    if(!array_key_exists('items_per_page', $search_array))
      $search_array['items_per_page'] = 12;

    if(!array_key_exists('types', $search_array)) {
      $search_array['types'] = array(1,2);
    }

    return $search_array;
  }
  
  function prepare_package_search_array($search_array) {
    if(!array_key_exists('date_from', $search_array))
      $search_array['date_from'] = date('Y-m-d', time()+$this->get_config('date_from_days_def')*86400);
    if(!array_key_exists('date_till', $search_array))
      if(!array_key_exists('days_from_today', $search_array))
        $search_array['date_till'] = date('Y-m-d', time()+$this->get_config('date_till_days_def')*86400);
      else
        $search_array['date_till'] = date('Y-m-d', time()+$search_array['days_from_today']*86400);

    if(!array_key_exists('durations', $search_array)) {
      $search_array['durations'][0]['from'] = $this->get_config('night_from_def');        
      $search_array['durations'][0]['till'] = $this->get_config('night_till_def');
    }

    if(!array_key_exists('adult_amount', $search_array))
      $search_array['adult_amount'] = $this->get_config('adult_amount_def');
    if(!array_key_exists('items_per_page', $search_array))
      $search_array['items_per_page'] = $this->get_config('item_per_page_def');

    if(!array_key_exists('types', $search_array)) {
      $search_array['types'] = array(1,2);
      //unset($search_array['departure_cities']);
    }

    return $search_array;
  }

  function prepare_hike_search_array($search_array) {

    if(!array_key_exists('is_ticket_price_included', $search_array))
      $search_array['is_ticket_price_included'] = -1;
    $search_array['city_search_type'] = 'and';
    return $search_array;
  }
  
  function parse_package_search_url($url) {
    preg_match('/switch_price=([A-Z]+)/', $url, $switched);
    $currency = $switched[1];
    switch ($currency) {
      case 'UAH':
        $this->choosed_currency_id = 2;
        $search_array['switch_price'] = 2;
        break;
      case 'EUR':
        $this->choosed_currency_id = 10;
        $search_array['switch_price'] = 10;
        break;
      case 'USD':
      default:
        $this->choosed_currency_id = 1;
        $search_array['switch_price'] = 1;
        break;
    }
    $search_array['price_from'] = 0;
    $url = urldecode($url);
    foreach(explode('&', $url) as $param) {
      $param_array = explode('=', $param);
      $value = $param_array[1];
      if(trim($value) == '')
        continue;
      switch ($param_array[0]) {
        case 'requested_tour':
          $search_array['requested_tours'][0] = $value;
          break;
        case 'country':
          foreach (explode(' ', $value) as $hr)
            $search_array['countries'][] = $hr;
          break;
        case 'region': 
          foreach (explode(' ', $value) as $hr)
            $search_array['regions'][] = $hr;
          break;
        case 'hotel':
          foreach (explode(' ', $value) as $hr)
            $search_array['hotels'][] = $hr;
          break;
        case 'departure_city':
          if($value != 0)
            $search_array['departure_cities'][] = $value;
          break;
        case 'operator':
          if($value != 0)
            $search_array['operators'][] = $value;
          break;
        case 'hotel_rating': 
          foreach (explode(' ', $value) as $hr)
            $search_array['hotel_ratings'][] = $hr;
          break;
        case 'food': 
          foreach (explode(' ', $value) as $hr)
            if($hr != 0)
              $search_array['meal_types'][] = $hr;
          break;
        case 'food_select': 
          if($value != 0)
            $search_array['meal_types'][] = $value;
          break;
        case 'night_from':
          $search_array['durations']['0']['from'] = $value;
          break;
        case 'night_till':
          $search_array['durations']['0']['till'] = $value;
          break;
        case 'price_from':
          $search_array['price_from'] = str_replace(' ', '', $value);
          break;
        case 'price_till':
          $search_array['price_till'] = str_replace(' ', '', $value);
          break;
        case 'adults':
          $search_array['adult_amount'] = $value;
          break;
        case 'children':
          $search_array['child_amount'] = $value;
          break;
        case 'child1_age':
        case 'child2_age':
        case 'child3_age':
          if($value)
            $search_array['child_ages'][] = $value;
          break;
        case 'date_from':
          $date_from = '0000-00-00'; 
          if (preg_match('/^([0-9]+)[.]([0-9]+)[.]([0-9]+).*/', $value, $matches)) {
            $date_from = '20'.$matches[3].'-'.((strlen($matches[2]) == 1)?'0':'').$matches[2].'-'.$matches[1];
          }
          $search_array['date_from'] = $date_from;
          break;
        case 'date_till':
          $date_till = '9999-99-99'; 
          if (preg_match('/^([0-9]+)[.]([0-9]+)[.]([0-9]+).*/', $value, $matches)) {
            $date_till = '20'.$matches[3].'-'.((strlen($matches[2]) == 1)?'0':'').$matches[2].'-'.$matches[1];
          }
          $search_array['date_till'] = $date_till;
          break;
        case 'days_from_today':
          $search_array['days_from_today'] = $value;
          break;
        case 'items_per_page': 
          $search_array['items_per_page'] = $value;
          break;
        case 'package_tour_type': 
          $search_array['types'] = $value?array($value):array(1,2);
          break;
        case 'only_tour_with_transport':
          $search_array['types'] = array(1);
          break;
        case 'instantsearch': 
          $search_array['instantsearch'] = 1;
          break;
        case 'default_form_select': 
          $search_array['default_form'] = 1;
          break;
        case 'tour_kind':
          if($value)
            $search_array['tour_kinds'] = array($value);
          break;
        case 'transport_type':
          $search_array['transport_type'] = explode(" ", $value);
          break;
      }
    }
    return $search_array;
  }

  function parse_hike_search_url($url) { 
    preg_match('/switch_price=([A-Z]+)/', $url, $switched);
    $currency = $switched[1];
    switch ($currency) {
      case 'UAH':
        $this->choosed_currency_id = 2;
        $search_array['switch_price'] = 2;
        break;
      case 'USD':
        $this->choosed_currency_id = 1;
        $search_array['switch_price'] = 1;
        break;
      case 'EUR':
      default:
        $this->choosed_currency_id = 10;
        $search_array['switch_price'] = 10;
        break;
    }
    $url = urldecode($url);
    foreach(explode('&', $url) as $param) {      
      $param_array = explode('=', $param);
      $value = $param_array[1];
      if(trim($value) == '')
        continue;
      switch ($param_array[0]) {
        case 'requested_tour':
            $search_array['tours'][0] = $value;
          break;
        case 'country': 
          foreach (explode(' ', $value) as $hr)
            $search_array['countries'][] = $hr;
          break;
        case 'transport': 
          foreach (explode(' ', $value) as $hr)            
            $search_array['transport_types'][] = $hr;
          break;
        case 'city': 
          foreach (explode(' ', $value) as $hr)
            $search_array['departure_cities'][] = $hr;
          break;
        case 'tour_city': 
          foreach (explode(' ', $value) as $hr)
            $search_array['cities'][] = $hr;
          break;
        case 'operator': 
          foreach (explode(' ', $value) as $hr)
            $search_array['operators'][] = $hr;
          break;
        case 'items_per_page': 
          $search_array['items_per_page'] = $value;
          break;
        case 'hike_price_till': 
          $search_array['price_till'] = $value;
          break;
        case 'page': 
          $search_array['page'] = $value;
          break;
        case 'hike_date_from':
          $date_from = '0000-00-00'; 
          if (preg_match('/^([0-9]+)[.]([0-9]+)[.]([0-9]+).*/', $value, $matches)) {
            $date_from = '20'.$matches[3].'-'.((strlen($matches[2]) == 1)?'0':'').$matches[2].'-'.$matches[1];
          }
          $search_array['date_from'] = $date_from;
          break;
        case 'hike_date_till':
          $date_till = '9999-99-99'; 
          if (preg_match('/^([0-9]+)[.]([0-9]+)[.]([0-9]+).*/', $value, $matches)) {
            $date_till = '20'.$matches[3].'-'.((strlen($matches[2]) == 1)?'0':'').$matches[2].'-'.$matches[1];
          }
          $search_array['date_till'] = $date_till;
          break;
        case 'hike_tour_type': 
          $search_array['is_ticket_price_included'] = $value;
          break;
      }
    }    
    return $search_array;
  }
  
  function create_hike_additional_array(&$search_result) {

    $lang = get_translate();

    $json = new FastJSON;  

    $offer_dates = array();
    foreach($search_result as $offer_) {
      $offer_dates[] = safe($offer_, 'departure_date');
    }
    
    $offer_accomodations = array();
    foreach($search_result[0]['accomodations'] as $accomodation) {
      $offer_accomodations[$accomodation['id']] = $accomodation['name'];
    }
    
    $offer_prices = array();
    foreach($search_result as $offer_) {
      foreach($offer_['prices'] as $price) {
        if(count($price)) {
          $offer_prices[safe($offer_, 'departure_date')][$price[0]['accomodation_id']]['price'] = round($price[0]['price']);
          $offer_prices[safe($offer_, 'departure_date')][$price[0]['accomodation_id']]['price_id'] = round($price[0]['id']);
        }
      }
    }
    $offer_prices_json = $json->encode($offer_prices);
    $offer_multi_prices = array();
    foreach($search_result as $offer_) {
      foreach($offer_['multi_currency'] as $currency=>$currencies) {
        foreach($currencies['prices'] as $price) {
          if(count($price)) {
            $offer_multi_prices[$currency][safe($offer_, 'departure_date')][$price[0]['accomodation_id']]['price'] = round($price[0]['price']);
            $offer_multi_prices[$currency][safe($offer_, 'departure_date')][$price[0]['accomodation_id']]['price_id'] = round($price[0]['id']);
          }
        }
      }
    }
    $offer_multi_prices_json = $json->encode($offer_multi_prices);
    
    $offer_early_prices = array();
    foreach($search_result as $offer_) {
      if(isset($offer_['early_prices']))
        foreach($offer_['early_prices'] as $days_num => $early_prices) {
          if(isset($early_prices))
            foreach($early_prices as $early_price) {
              $offer_early_prices[safe($offer_, 'departure_date')][$early_price[0]['accomodation_id']][] = array('price_id' => $early_price[0]['id'], 'week_num' => (int)ceil($days_num/7), 'price' => round($early_price[0]['price']), 'early_timestamp' => (strtotime(safe($offer_, 'departure_date')) - $days_num*24*60*60));
          }
        }
    } 
    $offer_early_prices_json = $json->encode($offer_early_prices);
    
    $offer_multi_early_prices = array();
    foreach($search_result as $offer_) {
      foreach($offer_['multi_currency'] as $currency=>$currencies) {
        if(isset($currencies['early_prices']))
          foreach($currencies['early_prices'] as $days_num => $early_prices) {
            if(isset($early_prices))
              foreach($early_prices as $early_price) {
                $offer_multi_early_prices[$currency][safe($offer_, 'departure_date')][$early_price[0]['accomodation_id']][] = array('price_id' => $early_price[0]['id'], 'week_num' => (int)ceil($days_num/7), 'price' => round($early_price[0]['price']), 'early_timestamp' => (strtotime(safe($offer_, 'departure_date')) - $days_num*24*60*60));
            }
          }
      }
    } 
    $offer_multi_early_prices_json = $json->encode($offer_multi_early_prices);

    $countries_str = '';
    $countries_code_iso = array();
    foreach($search_result[0]['countries'] as $country) {
      $countries_str[] = $country['name'];
      $countries_code_iso[] = $country['code_iso'];
    }
    $countries_str = implode(' - ', $countries_str);      
    
    $cities_str = '';
    foreach($search_result[0]['cities'] as $city)
      $cities_str[] = $city['name'];
    $cities_str = implode(' - ', $cities_str);                

    if(safe($search_result[0], 'hotels')) {              
      $hotel_rating_str = '';
      foreach($search_result[0]['hotel_ratings'] as $key_hotel => $hotel_rating) {
        if($key_hotel == 0)
          $hotel_rating_str = $hotel_rating['code'] . '*';
        if(($key_hotel == count($search_result[0]['hotel_ratings'])-1) && $key_hotel != 0)
          $hotel_rating_str .= ' - ' . $hotel_rating['code'] . '*';
      }
      
      $hotels_str = array();
      foreach($search_result[0]['hotels'] as $hotel)
        $hotels_str[] = $hotel['name'];
      $hotels_str = implode(', ', $hotels_str);
      
      $hotels =  $hotels_str;
//      $hotels = $this->lang['hotels'] . ' ' . $hotel_rating_str . ':&nbsp;' . $hotels_str;
    } else
      $hotels = $this->lang['hotels_not_specified'];
    $show_prices = array();
    foreach($search_result[0]['multi_currency'] as $key => $multi_currency) {
      $show_prices[$key] = $multi_currency['show_prices'];
    }
    $show_prices = $json->encode($show_prices);
    return array('offer_accomodations' => $offer_accomodations
               , 'offer_prices' => $offer_prices
               , 'offer_dates' => $offer_dates
               , 'offer_prices_json' => $offer_prices_json
               , 'offer_multi_prices_json' => $offer_multi_prices_json
               , 'offer_early_prices' => $offer_early_prices
               , 'offer_early_prices_json' => $offer_early_prices_json
               , 'offer_multi_early_prices_json' => $offer_multi_early_prices_json
               , 'countries_str' => $countries_str
               , 'cities_str' => $cities_str
               , 'hotels' => $hotels
               , 'countries_code_iso' => $countries_code_iso);
  }
  
  function get_package_order_info($price_id) {
    $param = array('price_id' => $price_id);
    $result = $this->exec('get_package_order_info', $param, false);
    return $result;    
  }
  
  function get_package_tour_price($price_id) {
    $param = array('price_id' => $price_id);
    $result = $this->exec('get_package_tour_price', $param, false);
    return $result;    
  }
  
  function get_flight($flight_id) {
    $param = array('flight_id' => $flight_id);
    $result = $this->exec('get_flight', $param, false);
    return $result;    
  }
  
  function get_references($get = 'all') {
    $param = array('get' => $get);
    $result = $this->exec('get_references', $param, false);
    return $result;    
  }
  
  function get_hike_tour_iterator($filters) {
    $param = $filters;
    $result = $this->exec('get_hike_tour_iterator', $param, false);
    return $result;    
  }
  
  function get_hike_tour_price_iterator($filters) {
    $param = $filters;
    $result = $this->exec('get_hike_tour_price_iterator', $param, false);
    return $result;    
  }
  
  function get_hike_tour_info($param) {
    $result = $this->exec('get_hike_tour_info', $param, false);
    return $result;    
  }
  
  function get_hike_departure($param) {
    $result = $this->exec('get_hike_departure', $param, false);
    return $result;    
  }
  function package_validate_tour($tour_id, $sharding_rule_id) {
    if($tour_id && $sharding_rule_id){
      $param['tour_id'] = $tour_id;
      $param['sharding_rule_id'] = $sharding_rule_id;
    }
    $result = $this->exec('package_validate_tour', $param, true);
    return $result;    
  }
  
  function get_hike_tour_order_iterator($param) {
    $result = $this->exec('get_hike_tour_order_iterator', $param, false);
    return $result;    
  }
  
  function get_hike($param) {
    $result = $this->exec('get_hike', $param, false);
    return $result;    
  }
  
  function is_show_cart() {
    $this->show_cart = $this->get_config('show_basket');
  }
  
  function get_include_contents($filename) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        return ob_get_clean();
    }
    return false;
}
  function debug_message_send($text) {
    $param['text'] = $text;
    $this->exec('debug_message_send', $param, false);
    return true;
  }
  
  function help($func_name, $param, $cache_enabled=false) {

    if(!$this->is_connected)
      $this->connect();
    $param['exec_function']  = $func_name;
    $param['client_version'] = $this->client_version;
    $param['module_type']    = $this->module_type;
    $param['auth_token']     = $this->auth_token;
    $param['request_info']   = $this->request_info; 
    
    $php_url = 'http://'.$_SERVER['SERVER_NAME'].preg_replace('|\?.*|', '', $_SERVER['REQUEST_URI']);
    $param['agency_url']     = ($this->module_type == 'js')?($this->agency_url):$php_url;
    $param['save_statistic'] = $this->save_statistic;

    if(strtolower($this->get_config('webservice_encoding')) != 'utf-8') {
      array_walk_recursive($param, 'change_charset_array_revert', $this);
    }


    $param_serialize = serialize($param);
    $this->proxy->setHTTPEncoding('gzip');
    $result=$this->proxy->_help($param_serialize);
    $result = unserialize(base64_decode($result));    
    if(strtolower($this->get_config('webservice_encoding')) != 'utf-8')
      array_walk_recursive($result, 'change_charset_array', $this);
    if(safe($result, 'result_code', 0) > 0) {
      $this->error($result['result_code'], $result['result_value']);
    }
    return $result;
  }
  
  function get_flag_name($old_file, $size = 24) {
    if(preg_match('/^([^\.]+)\.[a-z0-9]{2,4}$/', $old_file, $matches)) {
      $name = $matches[1];
      $new_name = str_replace('{name}', $name, $this->config['flag_reg']);
      if($size) {
        $new_name = str_replace('{size}', $size, $new_name);
      } else {
        $new_name = str_replace('{size}/', '', $new_name);
      }
      return $new_name;
    }
    return $old_file;
  }
  
  function check_user($login) {
    $result = $this->exec('check_user', array('login' => $login), false);
    return $result;    
  }
  
  function check_user_authentification($login, $password){
    $param = array( 'login'     => $login
                  , 'password' => $password);
    $result = $this->exec('check_user_authentification', $param, false);
    return $result;    
  }
  
  function get_user_info($user_id) {
    $result = $this->exec('get_user_info', array('user_id' => $user_id), false);
    return $result;    
  }
  
  function get_balance($agency_id) {
    $result = $this->exec('get_balance', array('agency_id' => $agency_id), false);
    return $result; 
  }   

  function get_services($agency_id) {
    $result = $this->exec('get_services', array('agency_id' => $agency_id), false);
    return $result; 
  }   

  function get_active_services($agency_id, $service_id) {
    $param = array( 'agency_id'  => $agency_id
                  , 'service_id' => $service_id);
    $result = $this->exec('get_active_services', $param, false);
    return $result; 
  }     
  
  function get_hotel_info($hotel_id) {
    $result = $this->exec('get_hotel_info', array('hotel_id' => $hotel_id), true);
    return $result; 
  }   
  
  function get_min_prices_content($itt_tour_type, $width_class) {
    if ($itt_tour_type == 1) {
      $is_hike = false;
    } else if ($itt_tour_type == 2) {
      $is_hike = true;
    }
    $offers = $this->get_country_showcase($is_hike);
    switch ($width_class) {
      case 53:
        $result = get_showcase_tours_by_country_content($offers, $this, $is_hike);
        break;
      case 55:
        $result = get_showcase_tours_by_country_light_content($offers, $this, $is_hike);
        break;
    }
    return $result;
  }

  function import_module_package_orders($orders) {
    return $this->exec('import_module_package_orders', array('orders' => $orders));
  }

  function export_module_package_orders($params) {
    return $this->exec('export_module_package_orders', $params);
  }

  function import_module_hike_orders($orders) {
    return $this->exec('import_module_hike_orders', array('orders' => $orders));
  }

  function export_module_hike_orders($params) {
    return $this->exec('export_module_hike_orders', $params);
  }

  function update_module_orders($orders) {
    return $this->exec('update_module_orders', array('orders' => $orders));
  }

  function import_package_orders($orders) {
    return $this->exec('import_package_orders', array('orders' => $orders));
  }

  function update_package_orders($orders) {
    return $this->exec('update_package_orders', array('orders' => $orders));
  }

  function export_package_orders($params) {
    return $this->exec('export_package_orders', $params);
  }

  function import_hike_orders($orders) {
    return $this->exec('import_hike_orders', array('orders' => $orders));
  }

  function update_hike_orders($orders) {
    return $this->exec('update_hike_orders', array('orders' => $orders));
  }

  function export_hike_orders($params) {
    return $this->exec('export_hike_orders', $params);
  }

  function declOfNum($number, $titles) {
    $cases = array (2, 0, 1, 1, 1, 2);
    return $titles[ ($number%100 > 4 && $number %100 < 20) ? 2 : $cases[min($number%10, 5)] ];
  }
  
  /**
   * Method get 'transport IDs' for current country.
   * 
   * @param integer $country_id
   * @return array
   */
  public function showcase_hike_filter_update($country_id) {
    // Get settings color, etc for item agency
    $it_obj = new itt_object();
    $config_showcase_photo = $it_obj->showcase_config($this->agency_id, 95);
    
    // Check param 'is_ticket_price_included'
    if ($config_showcase_photo['bgg_hike_photo_showcase_tour_transport'] == 1) {
      return $this->exec('showcase_hike_filter_update', array('country' => $country_id, 'is_ticket_price_included' => 1));
    } else {
      return $this->exec('showcase_hike_filter_update', array('country' => $country_id));
    }
  }
  
  /**
   * Method get country tour type
   * 
   * @param array $params
   * @return array
   */
  public function get_country_tour_type($params) {
    return $this->exec('get_country_tour_type', $params);
  }

  /**
   * Method send data to webservice
   * 
   * @param array $params
   * @return string
   */
  public function create_order_buy_online($params) {
    return $this->exec('create_order_buy_online', $params);
  }
  
  /**
   * Возвращает ссылку на следующую страницу из пейджинатора
   * 
   * @param array $pages
   * @return string
   */
  private function get_next_page_url($pages) {
    $result = '';
    $pages = array_reverse($pages);
    foreach($pages as $page) {
      if($page['is_next']) {
        $result = $page['href'];
        break;
      }
    }
    return $result;
  }
  
  /**
   * Возвращает ссылку на страницу по её номеру
   * 
   * @param string $base_url
   * @param integer $page_number
   * @return string
   */
  private function get_page_url($base_url, $page_number) {
    $request_uri = preg_replace('/&page=\d+/i', '', $base_url);
    $request_uri .= '&page=' . $page_number;
    return $request_uri;
  }

  /**
   * Возвращает поисковые результаты для страницы по её номеру
   * 
   * @param array $search_array
   * @param array $mod2_pager
   * @param integer $mod2_page
   * @return array
   */
  private function get_mod2_result_by_page_number($search_array, $mod2_pager, $mod2_page) {
    $search_result_filtered = array();
    $items_total_amount = 0;

    $ittour_start_page = min($mod2_pager[$mod2_page]['ittour_pages']);
    $ittour_end_page = max($mod2_pager[max(array_keys($mod2_pager))]['ittour_pages']); // Надо загружать все страницы
    $search_results = array();

    // Запрос всех нужных страниц
    for($ittour_page = $ittour_start_page; $ittour_page <= $ittour_end_page; $ittour_page++) {
      $request_uri = $this->get_page_url($_SERVER['REQUEST_URI'], $ittour_page);
      $search_result = $this->package_tour_search($search_array, true, $request_uri);
      $search_results[$ittour_page] = $search_result;
      if(!$this->get_next_page_url($search_result['pager']['pages'])) break;
    }

    foreach($search_results as $key => $search_result) {
      $items_total_amount = $search_result['pager']['items_total_amount'];
      foreach($search_result['offers'] as $offer) {
        if(!in_array($offer['hotel_id'], $mod2_pager[$mod2_page]['hotels'])) {
          continue;
        }
        $search_result_filtered[] = $offer;
      }
    }

    $result['search_result_filtered'] = $search_result_filtered;
    $result['items_total_amount'] = $items_total_amount;
    return $result;
  }
}

?>
