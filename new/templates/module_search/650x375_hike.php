<?php 
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */
$parts = 'templates/module_search/parts/';
?>
<form action="#" class="itt_form-p-tours">
  <div class="itt_row itt_mtv_bottom_12">
    <div class="itt_col-lg-4 itt_col-md-4 itt_col-sm-4 itt_col-xs-4 itt_multiple-sel">
      <ul>
        <li class="itt_font_weight_bd"><?php echo isset($client->lang['countries']) ? $client->lang['countries'] : $client->lang['countries'];?></li>
        <li class="itt_form_list_mtv_down">
          <?php $this->includeTemplate($parts.'country_scroll_stretch.php'); ?>
        </li>
        <li class="itt_form_list_mtv_down itt_search_module_grey_color itt_search_module_font-size_10 itt_search_modul_select_text_position"><?php echo $client->lang['you_can_select_only_one_country'];?></li>
      </ul>
    </div>
    
    <div class="itt_col-lg-4 itt_col-md-4 itt_col-sm-4 itt_col-xs-4">
      <ul>
        <li class="itt_font_weight_bd"><?php echo $client->lang['cities_in_tour'];?></li>
        <li class="itt_form_list_mtv_down">
          <div class="tour_city">
            <?php $this->includeTemplate($parts.'cities_scroll_stretch.php'); ?>
          </div>
        </li>
        <li class="itt_form_list_mtv_down itt_search_module_grey_color itt_search_module_font-size_10 itt_search_modul_select_text_position"><?php echo $client->lang['you_can_select_multiple_cities'];?></li>
      </ul>
    </div>
    
    <div class="itt_col-lg-4 itt_col-md-4 itt_col-sm-4 itt_col-xs-4">
      <ul>
        <li class="itt_font_weight_bd"><?php echo $client->lang['transport'];?></li>
        <li class="itt_custom_select_position itt_form_list_mtv_down">
          <?php echo $this->renderTemplate($parts.'transport_select_stretch.php', array('list'    => $hike_tour_form_field['transport'],
                                                                                        'default' => $hike_tour_default_form_value['transport'],
                                                                                        'client'  => $client,
                                                                                       )); ?>
        </li>
      </ul>
      
      <ul class="itt_mtv_bottom">
        <li class="itt_font_weight_bd"><?php echo $client->lang['fly_departure'];?></li>
        <li class="itt_custom_select_position itt_form_list_mtv_down">
          <?php echo $this->renderTemplate($parts.'fly_from_select_stretch.php', array('list'    => $hike_tour_form_field['city'],
                                                                                       'default' => key($hike_tour_default_form_value['city']),
                                                                                       'client'  => $client,
                                                                                      )); ?>
        </li>
      </ul>
      
      <div class="itt_row itt_mtv_bottom">
        <div class="itt_col-lg-4 itt_col-md-4 itt_col-sm-4 itt_col-xs-4">
          <div class="itt_font_weight_bd itt_age_of_children"><?php echo $client->lang['price_per_adult'];?></div>
          <ul class="itt_mtv_bootom_16">
            <li class="itt_custom_select_position">
              <select class="itt_currency_selector_hike itt_mod_search_custom_select itt_currency" name="itt_currency_selector_hike">
                <?php
                // Check def currency // '110' валюта источник
                if (($hike_tour_default_form_value['currency_default'] == '') || ($hike_tour_default_form_value['currency_default'] == '110')) {
                  $hike_tour_default_form_value['currency_default'] = 10;// EUR
                } else {
                  $hike_tour_default_form_value['currency_default'] = 2;// Грн.
                }
                ?>
                <?php echo $this->renderTemplate($parts.'currency_option_select_stretch.php', array('client'  => $client,
                                                                                                    'default' => safe($hike_tour_default_form_value, 'currency_default'))
                                                ); ?>
              </select>
            </li>
          </ul>
        </div>
        <div class="itt_col-lg-8 itt_col-md-8 itt_col-sm-8 itt_col-xs-7 itt_col-xs-push-1 itt_col-lg-push-0 itt_col-md-push-0 itt_col-sm-push-0">
          <ul class="itt_mtv_bootom_16">  
            <li class="itt_search_module_text_position_three"><?php echo $client->lang['price_to'];?></li>
            <li class="itt_custom_select_position">
              <input type="hidden" name="itt_price_from" class="itt_price_from" value="0"/>
              <input type="hidden" name="itt_amount_list_relation_currency_from_hike" value="0">
              <select class="itt_border_rd4 itt_form-control itt_amount_list_relation_currency_to_hike itt_price_till itt_mod_search_custom_select" name="itt_amount_list_relation_currency_to_hike">
                <!-- Set data list in module_search.js" -->
              </select>
            </li>
          </ul> 
        </div>
      </div>
      
    </div>
  </div>
  
  <div class="itt_row itt_mtv_bottom_12">
    <div class="itt_col-lg-6 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
      <div class="itt_font_weight_bd itt_age_of_children itt_margin_position15"><?php echo $client->lang['departure_date_v1'];?></div>
      <div class="itt_row itt_form_input_position">
        <div class="itt_col-lg-6 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
          <ul>
            <li class="itt_search_module_text_position_thee"><?php echo $client->lang['date_from'];?></li>
            <li class="itt_margin_position_left_top">
              <div class="itt itt_search_module_calendar_icons"></div>
              <input type="text" id="datepicker_hike" class="itt_form-control itt_search_module_form_background itt_search_module_form_background_thee itt_search_module_color_placeholder_black itt_font_weight_bd itt_date_from" placeholder="<?php echo date("d.m.Y");?>">
            </li>
          </ul>
        </div>
        <div class="itt_col-lg-6 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
          <ul>
            <li class="itt_search_module_text_position">+</li>
            <li class="itt_custom_select_position">
              <select class="itt_mod_search_custom_select itt_date_period">
                <?php echo $this->renderTemplate($parts.'days_period_option_select_stretch.php', array('client' => $client)); ?>
              </select>
            </li>
          </ul>
        </div>  
      </div>
    </div>
    <div class="itt_col-lg-4 itt_col-lg-push-2 itt_col-md-4 itt_col-md-push-2 itt_col-sm-4 itt_col-sm-push-2 itt_col-xs-4 itt_col-xs-push-2">
      <button name="send" class="itt_search_tour_btn itt_bg_color_search_module itt_color_white itt_border_rd8 itt_search_tour_btn_two itt itt_tour_search_button itt_tour_search_button_with_color" type="button"><?php echo $client->lang['find_tours'];?></button>
    </div>
  </div>
  
  <?php
  // Set default 25 row in result
  ?>
  <input type="hidden" name="items_per_page" class="itt_tour_count_per_page" value="25"/>
  
  <?php
  // Set def currency from config // '110' валюта источник => '10' => EUR // use in open tour view
  ?>
  <input type="hidden" name="currency_default_for_hike_tour" value="<?php echo $hike_tour_default_form_value['currency_default']; ?>"/>
</form>