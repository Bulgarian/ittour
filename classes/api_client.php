<?php

/**
 * Класс для получения данных с Айтитура
 */
class api_client {

  private $_api_base_url               = 'http://api.ittour.com.ua/';

  private $_api_params_url             = 'module/params';
  private $_api_search_url             = 'module/search';
  private $_api_search_by_keys_url     = 'module/search-list';
  private $_api_tour_info_url          = 'tour/info/';
  private $_api_tour_validate_url      = 'tour/validate/';
  private $_api_tour_flights_url       = 'tour/flights/';

  public $authorization_token = 'd0f84f1f30ba54853f4b790be4ec5a8b'; // Взять из Личного кабинета
  public $response_language   = 'ru';

  function __construct() {

  }

  /**
   * Справочники
   * http://api.ittour.com.ua/module/params
   * http://api.ittour.com.ua/module/params/318?entity=hotel:meal_type:from_city
   * http://api.ittour.com.ua/module/params/338?hotel=23&entity=meal_type:from_city
   * Сейчас на АПИ если задан $country_id, то $entities - обязательные параметры!
   */
  public function get_dictionary($country_id = null, array $entities = array(), array $params = array()) {
    $result = new stdClass;

    $url = $this->_api_base_url . $this->_api_params_url;
    if(isset($country_id)) $url .= '/' . $country_id;

    if(is_array($entities) && $entities) {
      $params['entity'] = implode(':', $entities);
    }

    $result = $this->get($url, $params);
    return $result;
  }

  /**
   * Поиск
   * http://api.ittour.com.ua/module/search?type=1&country=318&adult_amount=2&child_amount=0&hotel_rating=3:4:78&night_from=6&night_till=8&date_from=07.10.16&date_till=15.10.16&page=1
   */
  public function search(array $params) {
    $result = new stdClass;
    $url = $this->_api_base_url . $this->_api_search_url;
    $result = $this->get($url, $params);
    return $result;
  }

  /**
   * Поиск списком
   * http://api.ittour.com.ua/module/search-list?type=1&country=318&adult_amount=2&child_amount=0&hotel_rating=3:4:78&night_from=6&night_till=8&date_from=07.10.16&date_till=15.10.16&page=1
   */
  public function search_ungrouped(array $params) {
    $result = new stdClass;
    $url = $this->_api_base_url . $this->_api_search_by_keys_url;
    $result = $this->get($url, $params);
    return $result;
  }

  /**
   * Поиск туров по ключу
   * http://api.ittour.com.ua/module/search-list?key=01-01-86962de9bd39b08c74cb0407cbadd464:01-03-e4a9a7570feaf4314398954533e0cf82
   */
  public function search_by_keys(array $params) {
    $result = new stdClass;
    $url = $this->_api_base_url . $this->_api_search_by_keys_url;
    $result = $this->get($url, $params);
    return $result;
  }

  /**
   * Поиск перелётов к туру
   * http://api.ittour.com.ua/tour/flights/10-02-1ff82899834e9d542777c16556f564f2
   */
  public function tour_flights($tour_id) {
    $result = new stdClass;
    $url = $this->_api_base_url . $this->_api_tour_flights_url . $tour_id;
    $result = $this->get($url);
    return $result;
  }

  /**
   * Информация о туре
   * http://api.ittour.com.ua/tour/info/10-02-1ff82899834e9d542777c16556f564f2
   */
  public function tour_info($tour_id) {
    $result = new stdClass;
    $url = $this->_api_base_url . $this->_api_tour_info_url . $tour_id;
    $result = $this->get($url);
    return $result;
  }

  /**
   * Валидация тура
   * http://api.ittour.com.ua/tour/validate/10-02-1ff82899834e9d542777c16556f564f2
   */
  public function validate($tour_id) {
    $result = new stdClass;
    $url = $this->_api_base_url . $this->_api_tour_validate_url . $tour_id;
    $result = $this->get($url);
    return $result;
  }

  /**
   * Отправка запроса и обработка результата
   */
  private function get($url, array $params = array()) {
    // Установка GET параметров
    if($params) {
      $query_string = http_build_query($params);
      if($query_string) $url .= '?' . $query_string;
    }

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $this->get_http_headers());
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);

    $curl_error_number = curl_errno($curl);
    if($curl_error_number) {
      $curl_error_text = curl_error($curl);
      curl_close($curl);
      throw new api_client_exception('cURL error: ' . $curl_error_text, $curl_error_number);
    } else {
      $info = curl_getinfo($curl);
      if($info['http_code'] != 200) {
        curl_close($curl);
        throw new api_client_exception('HTTP request failed: ' . $response, $info['http_code']);
      }
    }

    curl_close($curl);

    $result = json_decode($response);

    $error_code = json_last_error();
    switch($error_code) {
      case JSON_ERROR_NONE:
      break;
      case JSON_ERROR_SYNTAX: // Невалидный json - api может возвращать строки!
        $result = $response;
      break;
      default:
        throw new api_client_exception('Json_decode failed. Error_code:' . $error_code . '<br>' . $response, $info['http_code']);
      break;
    }

    return $result;
  }

  /**
   * Формирование заголовков для запросов
   */
  private function get_http_headers() {
    $result = array();
    if($this->authorization_token) {
      $result[] = 'Authorization: ' . $this->authorization_token;
    } else {
      throw new api_client_exception('Authorization token not set');
    }
    if($this->response_language) $result[] = 'Accept-Language: ' . $this->response_language;

    return $result;
  }
}

class api_client_exception extends Exception {
  
}
