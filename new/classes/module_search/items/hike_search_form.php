<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

class hike_search_form implements new_concrete_module {

    private $client, $hike_tour_form_field, $hike_tour_default_form_value, $display;

    function __construct($client, $hike_tour_form_field, $hike_tour_default_form_value, $display = false) {
        $this->client = $client;
        $this->hike_tour_form_field = $hike_tour_form_field;
        $this->hike_tour_default_form_value = $hike_tour_default_form_value;
        $this->display = $display;
    }

    public function getVariablesArray() {
        return array(
            'client' => $this->client,
            'country_list' => $this->client->get_optimize_hike_country_list(false),
            'custom_logo_url' => $this->getCustomLogoUrl(),
            'operators' => $this->getOperators(),
            'hike_tour_form_field' => $this->hike_tour_form_field,
            'hike_tour_default_form_value' => $this->hike_tour_default_form_value,
            'search_module_id' => filter_input(INPUT_GET, 'type') === '200x775' ? 4 : '4 itt_search-module5',
            'search_form_id' => filter_input(INPUT_GET, 'type') === '200x775' ? 2 : '2 itt_form-tours4',
            'diplay' => $this->display,
            
            'food_list' => $this->client->get_optimize_package_food_list(),
            'hotel_rating_list' => $this->client->get_optimize_package_hotel_rating_list(),
            'adult_list' => $this->client->get_optimize_package_adult_list(),
            'children_list' => $this->client->get_optimize_package_children_list(),
            'night_from_list' => $this->client->get_optimize_package_night_from_list(),
            'night_to_list' => $this->client->get_optimize_package_night_to_list(),
            
            'package_tour_form_field' => $this->formField,
            'package_tour_default_form_value' => $this->defaultValues
        );
    }

    private function getCustomLogoUrl() {
        $custom_logo_url = $this->client->get_config('custom_logo_url');

        return $custom_logo_url ? $custom_logo_url : 'https://www.ittour.com.ua/';
    }

    private function getOperators() {
        $operators = array();
        foreach($this->client->hike_search_form_data['operator']['list'] as $operator) {
            $operators[$operator['operator_id']] = array('code' => $operator['operator_name'], 'id' => $operator['operator_id']);
        }
        return $operators;
    }

    public function getTemplateFilename() {
        return 'templates/module_search/'.(filter_input(INPUT_GET, 'preview_size') ? filter_input(INPUT_GET, 'preview_size') : $this->client->get_config('search_module_type')) . '_hike.php';
    }
    
}
