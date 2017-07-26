<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

class package_search_form implements new_concrete_module {

    private $client, $formField, $defaultValues, $display;

    function __construct($client, $formField, $defaultValues, $display) {
        $this->client = $client;
        $this->formField = $formField;
        $this->defaultValues = $defaultValues;
        $this->display = $display;
    }

    public function getVariablesArray() {
        return array(
            'client' => $this->client,
            'kind_list' => $this->getKindList(),
            'food_list' => $this->client->get_optimize_package_food_list(),
            'hotel_rating_list' => $this->client->get_optimize_package_hotel_rating_list(),
            'adult_list' => $this->client->get_optimize_package_adult_list(),
            'children_list' => $this->client->get_optimize_package_children_list(),
            'night_from_list' => $this->client->get_optimize_package_night_from_list(),
            'night_to_list' => $this->client->get_optimize_package_night_to_list(),
            'custom_logo_url' => $this->getCustomLogoUrl(),
            'package_tour_form_field' => $this->formField,
            'package_tour_default_form_value' => $this->defaultValues,
            'search_module_id' => filter_input(INPUT_GET, 'type') === '200x775' ? 4 : '4 itt_search-module5',
            'search_form_id' => filter_input(INPUT_GET, 'type') === '200x775' ? 2 : '2 itt_form-tours4',
            'display' => $this->display,
        );
    }

    private function getCustomLogoUrl() {
        $custom_logo_url = $this->client->get_config('custom_logo_url');

        return $custom_logo_url ? $custom_logo_url : 'https://www.ittour.com.ua/';
    }

    private function getKindList() {
        $kind_list = $this->client->get_optimize_package_tour_kind_list();
        $allowed_kinds = array(7, 92); // На расширенном выносном модуле поиска туров 650x375 оставить только 3 значения поискового параметра "Вид тура": "Все", "Горнолыжные" и "Автобусные".
        $tmp_kind_list = array();
        foreach ($kind_list as $key => $value) {
            if (in_array($value['id'], $allowed_kinds)) {
                $tmp_kind_list[] = $value;
            }
        }
        return $tmp_kind_list;
    }

    public function getTemplateFilename() {
        return 'templates/module_search/'.(filter_input(INPUT_GET, 'preview_size') ? filter_input(INPUT_GET, 'preview_size') : $this->client->get_config('search_module_type')) . '_package.php';
    }

}
