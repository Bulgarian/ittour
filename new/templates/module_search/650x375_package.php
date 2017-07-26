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
  <div class="itt_container-fluid">
    <div class="itt_row itt_mtv_bottom_15">
      
      <!-- Select county block START -->
      <div class="itt_col-xs-3 itt_col-sm-2 itt_col-md-3 itt_col-lg-1">
        <div class="itt_select_country_media_position">
          <ul class="itt_pull-left itt_group-place">
            <li class="itt_font_weight_bd itt_headt_title_color"><?php echo $client->lang['country'];?>, <?php echo $client->lang['region_small'];?></li>
            <li class="itt_input_search_media_width itt_form_list_mtv_down">
              <div class="itt itt_search_module_icons"></div>
              <input type="text" id="itt_module_search_filter" class="itt_search_module_search_form itt_form-control itt_search_module_form_background itt_search_module_form_background_two" placeholder="<?php echo $client->lang['enter_the_name_of_the_country'];?>">
            </li>
            <li class="itt_search_module_grey_color itt_form_list_mtv_down">
              <?php echo $client->lang['or_text'];?> <a href="javascript:void(0);" class="itt_link itt_bg-color_conten"><span class="itt_glyphicon itt_glyphicon-chevron-down"></span> <span class="itt_border_bottom_dashed" id="itt_module_search_filter_button"><?php echo $client->lang['select_from_the_list'];?></span></a>
                
                <!-- Begin po-pup with country / region -->
                <div class="itt_pop-up-place itt_border-color itt_country-region_popup itt_bg-color_conten">
                  <div class="itt_column">
                    <strong><?php echo $client->lang['countries']; ?></strong>
                    <div class="itt_multiple-sel">
                      <?php
                      $popular_list = array('318', '338');
                      $regular = $popular = '';
                      $is_first = true;
                      foreach ($package_tour_form_field['country'] as $country_item) {
                        $selected = safe($package_tour_default_form_value['country'], $country_item['id']) ? 'selected="selected"' : (count($package_tour_default_form_value['country']) == 0 && $is_first ? 'selected="selected"' : '');
                        $type = 'regular';
                        if (in_array($country_item['id'],$popular_list)){
                          $type = 'popular';
                        }
                        $$type .= "<option value='".$country_item['id']."' {$selected} >".$country_item['name']."</option>";

                        if ($is_first) {$is_first = false;}
                      }
                      ?>
                      <select class="itt_country" size="5" multiple="multiple">
                        <?php if(count($popular) > 0){?>
                          <optgroup label="<?php echo isset($client->lang['popular_destinations']) ? $client->lang['popular_destinations'] : $client->lang['popular_destinations']; ?>">
                            <?php echo $popular; ?>
                          </optgroup>
                        <?php } ?>
                        <?php if(count($regular) > 0){?>
                          <optgroup label="<?php echo isset($client->lang['other_destinations']) ? $client->lang['other_destinations'] : $client->lang['other_destinations']; ?>">
                            <?php echo $regular; ?>
                          </optgroup>
                        <?php }?>
                      </select>
                      <div class="scrollbarY">
                        <div class="scrollbar"><div class="track"><div class="thumb"></div></div></div>
                        <div class="viewport"><div class="overview"></div></div>
                      </div>
                    </div>
                    <em><?php echo $client->lang['you_can_select_only_one_country']; ?></em>
                  </div>
                    
                  <div class="itt_column itt_region">
                    <strong><?php echo $client->lang['regions']; ?></strong>
                    <div class="scrollbarY">
                      <div class="scrollbar"><div class="track"><div class="thumb"></div></div></div>
                      <div class="viewport">
                        <div class="overview">
                          <div class="itt_list-checkbox">
                            <?php foreach ($package_tour_form_field['region'] as $region_item) {
                              $selected = safe($package_tour_default_form_value['region'], $region_item['id']) ? 'checked="checked"' : '';?>
                              <div class="itt_row itt_row_in_list_region">
                                <input value="<?php echo $region_item['id'];?>" class="itt_region_package" type="checkbox" id="<?php echo $region_item['id'] ?>" />
                                <label for="<?php echo $region_item['id'] ?>"><?php echo $region_item['name'] ?></label>
                              </div>
                            <?php } ?>
                          </div>
                        </div>
                      </div>
                    </div>
                    <em><?php echo $client->lang['you_can_select_multiple_regions']; ?></em>
                  </div>

                  <a href="javascript:void(0);" class="itt_close itt_text-color"><?php echo $client->lang['close_small']; ?></a>
                  <span class="itt_arrow">&nbsp;</span>
                </div>
                <!-- End po-pup with country / region -->
              
            </li>
          </ul>
        </div>
      </div>
      <!-- Select county block END -->
      
      
      <!-- Select date block START -->
      <div class="itt_col-xs-7 itt_col-xs-offset-2 itt_col-sm-4 itt_col-sm-offset-3 itt_col-md-offset-2 itt_col-md-4 itt_col-lg-3 itt_col-lg-offset-2">
        <div class="itt_date_block_media_position">
          <ul class="itt_pull-left itt_position_rel">
            <li class="itt_mtv_top_20 itt_search_module_text_position"><?php echo $client->lang['date_from'];?></li>
            <li class="itt_font_weight_bd itt_headt_title_color"><?php echo $client->lang['departure'];?></li>
            <li class="itt_form_list_mtv_down itt_select_date_width">
              <div class="itt itt_search_module_calendar_icons"></div>
              <input type="text" id="datepicker_package" class="itt_search_module_form_background_calendar itt_search_module_form_background itt_search_module_color_placeholder_black itt_date_from" placeholder="<?php echo date("d.m.Y");?>" value="<?php echo date("d.m.Y");?>">
            </li>
          </ul>
          <ul class="itt_date_day_select itt_pull-left">
            <li class="itt_search_module_text_position">+</li>
            <li class="itt_custom_select_position">
              <select class="itt_mod_search_custom_select itt_date_period itt_date_period_package">
                <?php echo $this->renderTemplate($parts.'days_period_option_select_stretch.php', array('client' => $client)); ?>
              </select>
            </li>
          </ul>
          <div class="itt_departure_date itt_search_module_grey_color">
            <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/mod_search_650x350/decor-note.png" height="3" width="16" class="itt itt_decor-note_img" alt="decor-note" />
            <?php echo $client->lang['departure_date_v1'];?>
            <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/mod_search_650x350/decor-note.png" height="3" width="16" class="itt itt_decor-note_img_two" alt="decor-note" />
          </div> 
        </div>
      </div>
      <!-- Select date block END -->
      
      
      <!-- Night on tour START 1 -->
      <div class="itt_col-xs-3 itt_col-sm-3 itt_col-md-2 itt_col-md-offset-1 itt_col-lg-offset-1 itt_col-lg-2 itt_hidden-xs itt_visible-sm itt_visible-md itt_visible-lg"> 
        <div class="itt_night_select_mod_position">
          <div class="itt_font_weight_bd itt_headt_title_color itt_age_of_children"><?php echo $client->lang['nights_on_the_tour'];?></div>
          <ul class="itt_mtv_bottom_15 itt_pull-left">  
            <li class="itt_position_rel itt_form_list_mtv_down">
              <select class="itt_tour_nights_from itt_tour_nights_from_1 itt_mod_search_custom_select itt_night_from_package">
                <?php echo $this->renderTemplate($parts.'selected_6_stretch.php'); ?>
              </select>
            </li>
          </ul>
          <ul class="itt_pull-left itt_add_night_position">
            <li class="itt_search_module_text_position_minus">-</li>
            <li class="itt_position_rel itt_form_list_mtv_down">
              <select class="itt_tour_nights_to itt_tour_nights_to_1 itt_mod_search_custom_select itt_night_till_package">
                <?php echo $this->renderTemplate($parts.'selected_14_stretch.php'); ?>
              </select>
            </li>
          </ul>
          <div class="itt_departure_date_two itt_search_module_grey_color">
            <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/mod_search_650x350/decor-note.png" height="3" width="16" class="itt itt_decor-note_img" alt="decor-note" /><?php echo $client->lang['and_from'];?>
            <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/mod_search_650x350/decor-note.png" height="3" width="16" class="itt itt_decor-note_img_two" alt="decor-note" />
          </div>
        </div>
      </div>
      <!-- Night on tour 1 END -->
      
      
      <!-- Children 1 block START -->
      <div class="itt_col-lg-3 itt_hidden-xs itt_hidden-sm itt_hidden-md itt_visible-lg">
        <div class="itt_children_block_media_position">
          <ul class="itt_pull-left">
            <li class="itt_font_weight_bd itt_headt_title_color"><?php echo $client->lang['exp'];?></li>
            <li class="itt_form_list_mtv_down itt_position_rel itt_children_select_width">
              <select class="itt_border_rd4 itt_form-control itt_mod_search_custom_select itt_adult_package itt_adult">
                <?php echo $this->renderTemplate($parts.'selected_adult_stretch.php', array('client'  => $client)); ?>
              </select>
            </li>
          </ul>
          <ul class="itt_pull-left itt_child_margin">
            <li class="itt_font_weight_bd itt_headt_title_color"><?php echo $client->lang['children'];?></li>
            <li class="itt_form_list_mtv_down itt_position_rel itt_children_select_width">
              <select class="itt_border_rd4 itt_form-control itt_child_count_selector itt_mod_search_custom_select itt_children_package itt_children">
                <?php echo $this->renderTemplate($parts.'selected_0_stretch.php'); ?>
              </select>
            </li>
          </ul>
          <div class="itt_child_margin itt_pull-left itt_row_child_item_block_num_1 itt_row_child_item_block">
            <div class="itt_font_weight_bd itt_headt_title_color itt_child_age_text_position"><?php echo $client->lang['children_ages'];?></div>
            <div class="itt_children_select_bg itt_children_select_width">
              <input type="text" class="itt_child_age_value" name="itt_child_age_value" value="0" maxlength="2">
              <ul class="itt_child_age itt_pull-left">
                <?php echo $this->renderTemplate($parts.'children_age_stretch.php'); ?>
              </ul>
              <ul class="itt_switch_child_position itt_pull-right">
                <li><a href="javascript:void(0);" class="itt_cheld_age_switch_up"><span class="itt_glyphicon itt_glyphicon-play itt_top_glyphicon_select"></span></a></li>
                <li><a href="javascript:void(0);" class="itt_cheld_age_switch_down"><span class="itt_glyphicon itt_glyphicon-play itt_down_glyphicon_select"></span></a></li>
              </ul>
            </div>
          </div>
          <div class="itt_child_margin_age itt_pull-left itt_row_child_item_block_num_2 itt_row_child_item_block">
            <div class="itt_children_select_bg itt_children_select_width">
              <input type="text" class="itt_child_age_value" name="itt_child_age_value" value="0" maxlength="2">
              <ul class="itt_child_age itt_pull-left">
                <?php echo $this->renderTemplate($parts.'children_age_stretch.php'); ?>
              </ul>
              <ul class="itt_switch_child_position itt_pull-right">
                <li><a href="javascript:void(0);" class="itt_cheld_age_switch_up"><span class="itt_glyphicon itt_glyphicon-play itt_top_glyphicon_select"></span></a></li>
                <li><a href="javascript:void(0);" class="itt_cheld_age_switch_down"><span class="itt_glyphicon itt_glyphicon-play itt_down_glyphicon_select"></span></a></li>
              </ul>
            </div>
          </div>
          <div class="itt_child_margin_age itt_pull-left itt_row_child_item_block_num_3 itt_row_child_item_block">
            <div class="itt_children_select_bg itt_children_select_width">
              <input type="text" class="itt_child_age_value" name="itt_child_age_value" value="0" maxlength="2">
              <ul class="itt_child_age itt_pull-left">
                <?php echo $this->renderTemplate($parts.'children_age_stretch.php'); ?>
              </ul>
              <ul class="itt_switch_child_position itt_pull-right">
                <li><a href="javascript:void(0);" class="itt_cheld_age_switch_up"><span class="itt_glyphicon itt_glyphicon-play itt_top_glyphicon_select"></span></a></li>
                <li><a href="javascript:void(0);" class="itt_cheld_age_switch_down"><span class="itt_glyphicon itt_glyphicon-play itt_down_glyphicon_select"></span></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- Children block 1 END -->
      
    </div>
    
    <div class="itt_row itt_mtv_bottom_15">
      
      <!-- Stars block START -->
      <div class="itt_col-xs-2 itt_col-sm-2 itt_col-md-2 itt_col-lg-1">
        <div class="itt_star_block_media_position">
          <ul class="itt_pull-left">
            <li class="itt_font_weight_bd itt_headt_title_color itt_hotel_start_padding"><?php echo $client->lang['hotel'];?>: <span class="itt_mod_search_horel_ratin_set"><span>3*,</span> <span>4*,</span> <span>5*</span></span></li>
            <li class="itt_form_list_mtv_down">
              <?php echo $this->renderTemplate($parts.'stars_hotel_stretch.php', array('client'  => $client));?>
            </li>
          </ul>
        </div>
      </div>
      <!-- Stars block END -->
      
      
      <!-- Nutrition block START -->
      <div class="itt_col-xs-2 itt_col-sm-2 itt_col-xs-offset-1 itt_col-sm-offset-1 itt_col-md-2 itt_col-md-offset-1 itt_col-lg-1">
        <div class="itt_nutrition_block_media_position">
          <ul class="itt_pull-left itt_nutrition_media_margin">
            <li class="itt_font_weight_bd itt_headt_title_color"><?php echo $client->lang['meal_short'];?></li>
            <li class="itt_nutrition_select_media itt_form_list_mtv_down">
              <select class="itt_mod_search_custom_select itt_food itt_food_package" id="itt_diet">
                <option value="496 388 498 512 560 1956" selected="selected"><?php echo $client->lang['any_food']; ?></option>
                <option value="388 496 498 512 560"><?php echo $client->lang['breakfasts_and_better']; ?></option>
                <option value="496 498 512 560"><?php echo $client->lang['half_and_better']; ?></option>
                <option value="512 560"><?php echo $client->lang['all_inclusive']; ?></option>
                <option value="560"><?php echo $client->lang['ultra_all_inclusive']; ?></option>
              </select>
            </li>
          </ul>
        </div>
      </div>
      <!-- Nutrition block END -->
      
      
      <!-- Children 2 block START -->
      <div class="itt_col-xs-6 itt_col-xs-offset-1 itt_col-sm-6 itt_col-sm-offset-1 itt_col-md-6 itt_col-md-offset-1 itt_visible-xs itt_visible-sm itt_visible-md itt_hidden-lg">
        <div class="itt_children_block_media_position_two">
          <ul class="itt_pull-left">
            <li class="itt_font_weight_bd itt_headt_title_color"><?php echo $client->lang['exp'];?></li>
            <li class="itt_form_list_mtv_down itt_position_rel itt_children_select_width">
              <select class="itt_border_rd4 itt_form-control itt_mod_search_custom_select itt_adult_package itt_adult">
                <?php echo $this->renderTemplate($parts.'selected_adult_stretch.php', array('client'  => $client)); ?>
              </select>
            </li>
          </ul>
          <ul class="itt_pull-left itt_child_margin">
            <li class="itt_font_weight_bd itt_headt_title_color"><?php echo $client->lang['children'];?></li>
            <li class="itt_form_list_mtv_down itt_position_rel itt_children_select_width">
              <select class="itt_border_rd4 itt_form-control itt_child_count_selector itt_mod_search_custom_select itt_children_package itt_children">
                <?php echo $this->renderTemplate($parts.'selected_0_stretch.php'); ?>
              </select>
            </li>
          </ul>
          <div class="itt_child_margin itt_pull-left itt_row_child_item_block_num_1 itt_row_child_item_block">
            <div class="itt_font_weight_bd itt_headt_title_color itt_child_age_text_position"><?php echo $client->lang['children_ages'];?></div>
            <div class="itt_children_select_bg itt_children_select_width">
              <input type="text" class="itt_child_age_value" name="itt_child_age_value" value="0" maxlength="2">
              <ul class="itt_child_age itt_pull-left">
                <?php echo $this->renderTemplate($parts.'children_age_stretch.php'); ?>
              </ul>
              <ul class="itt_switch_child_position itt_pull-right">
                <li><a href="javascript:void(0);" class="itt_cheld_age_switch_up"><span class="itt_glyphicon itt_glyphicon-play itt_top_glyphicon_select"></span></a></li>
                <li><a href="javascript:void(0);" class="itt_cheld_age_switch_down"><span class="itt_glyphicon itt_glyphicon-play itt_down_glyphicon_select"></span></a></li>                    
              </ul>
            </div>
          </div>
          <div class="itt_child_margin_age itt_pull-left itt_row_child_item_block_num_2 itt_row_child_item_block">      
            <div class="itt_children_select_bg itt_children_select_width">
              <input type="text" class="itt_child_age_value" name="itt_child_age_value" value="0" maxlength="2">
              <ul class="itt_child_age itt_pull-left">
                <?php echo $this->renderTemplate($parts.'children_age_stretch.php'); ?>
              </ul>
              <ul class="itt_switch_child_position itt_pull-right">
                <li><a href="javascript:void(0);" class="itt_cheld_age_switch_up"><span class="itt_glyphicon itt_glyphicon-play itt_top_glyphicon_select"></span></a></li>
                <li><a href="javascript:void(0);" class="itt_cheld_age_switch_down"><span class="itt_glyphicon itt_glyphicon-play itt_down_glyphicon_select"></span></a></li>
              </ul>
            </div>
          </div>
          <div class="itt_child_margin_age itt_pull-left itt_row_child_item_block_num_3 itt_row_child_item_block">
            <div class="itt_children_select_bg itt_children_select_width">
              <input type="text" class="itt_child_age_value" name="itt_child_age_value" value="0" maxlength="2">
              <ul class="itt_child_age itt_pull-left">
                <?php echo $this->renderTemplate($parts.'children_age_stretch.php'); ?>
              </ul>
              <ul class="itt_switch_child_position itt_pull-right">
                <li><a href="javascript:void(0);" class="itt_cheld_age_switch_up"><span class="itt_glyphicon itt_glyphicon-play itt_top_glyphicon_select"></span></a></li>
                <li><a href="javascript:void(0);" class="itt_cheld_age_switch_down"><span class="itt_glyphicon itt_glyphicon-play itt_down_glyphicon_select"></span></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- Children block 2 END -->
      
      
      <!-- Hotel 1 START -->
      <div class="itt_col-lg-1 itt_col-lg-offset-1 itt_hidden-xs itt_hidden-sm itt_hidden-md itt_visible-lg">
        <div class="itt_hotel_media_block_position">
          <ul class="itt_pull-left">
            <li class="itt_font_weight_bd itt_headt_title_color"><?php echo $client->lang['hotel'];?></li>
            <li class="itt_position_rel itt_form_list_mtv_down itt_hotel_media_width">
              <select class="itt_hotel itt_hotel_1 itt_mod_search_custom_select itt_hotel_package" id="itt_d1_package">
                <option value="0" selected="selected"><?php echo $client->lang['all_hotels'];?></option>
              </select>
            </li>
          </ul>
        </div>
      </div>
      <!-- Hotel 1 END -->
      
      
      <!-- Price range 1 START -->
      <div class="itt_col-lg-3 itt_col-lg-offset-2 itt_hidden-xs itt_hidden-sm itt_hidden-md itt_visible-lg">
        <div class="itt_price_range_block">
          <div class="itt_font_weight_bd itt_headt_title_color itt_age_of_children"><?php echo $client->lang['price_for_all_tour_participants'];?></div>
          <ul class="itt_pull-left itt_mtv_bootom_15 itt_price_paddaing_mod_edition">
            <li class="itt_position_rel itt_form_list_mtv_down itt_children_select_width">
              <select class="itt_currency_selector itt_mod_search_custom_select itt_switch_price_package itt_currency" name="itt_currency_selector">
                <?php
                if ($package_tour_default_form_value['currency_default'] != '') {
                  $currency_default_for_tpl = $package_tour_default_form_value['currency_default'];
                } else {
                  $currency_default_for_tpl = 1;// USD
                }
                ?>
                <?php echo $this->renderTemplate($parts.'currency_option_select_stretch.php', array('client'  => $client, 
                                                                                                    'default' => $currency_default_for_tpl)
                                                ); ?>
              </select>
            </li>
          </ul>
          <ul class="itt_pull-left itt_mtv_bootom_15 itt_first_currency_select_position">  
            <li class="itt_position_rel itt_form_list_mtv_down itt_form_text_align_right">
              <select class="itt_amount_list_relation_currency_from itt_amount_list_relation_currency_from_1 itt_mod_search_custom_select itt_price_from_package itt_price_from">
                <!-- Set data list in search-module.js" -->
              </select>
            </li>
          </ul>
          <ul class="itt_pull-left itt_mtv_bootom_15 itt_position_rel itt_price_two_position">
            <li class="itt_search_module_text_position_minus">-</li>
            <li class="itt_position_rel itt_form_list_mtv_down itt_form_text_align_right">
              <select class="itt_border_rd4 itt_form-control itt_font_weight_bd itt_amount_list_relation_currency_to itt_amount_list_relation_currency_to_1 itt_mod_search_custom_select itt_price_till_package itt_price_till">
                <!-- Set data list in search-module.js" -->
              </select>
            </li>
          </ul>
          <div class="itt_departure_date_thee itt_search_module_grey_color">
            <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/mod_search_650x350/decor-note.png" height="3" width="16" class="itt itt_decor-note_img" alt="decor-note" /><?php echo $client->lang['and_from'];?>
            <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/mod_search_650x350/decor-note.png" height="3" width="16" class="itt itt_decor-note_img_two" alt="decor-note" />
          </div> 
        </div>
      </div>
      <!-- Price range 1 END -->
      
      
      <!-- Departure block 1 START -->
      <div class="itt_col-lg-1 itt_hidden-xs itt_hidden-sm itt_hidden-md itt_visible-lg">
        <div class="itt_departure_media_block">
          <ul class="itt_pull-left">
            <li class="itt_font_weight_bd itt_headt_title_color"><?php echo $client->lang['fly_departure'];?></li>
            <li class="itt_position_rel itt_form_list_mtv_down itt_departue_select_width">
              <select class="itt_mod_search_custom_select itt_departure_city itt_departure_city_1 itt_departure_city_package">
                <?php
                $list = $package_tour_form_field['departure_city'];
                $default = safe($package_tour_default_form_value, 'departure_city', null);
                foreach($list as $departure_city_item) {
                   $selected = $default == $departure_city_item['id'] ? "selected='selected'" : '';
                   echo "<option value='".$departure_city_item['id']."'". $selected." >".$departure_city_item['name']."</option>";
                } ?>
              </select>
            </li>
          </ul>
        </div>
      </div>
      <!-- Departure block 1 END -->
      
    </div>
    
    <div class="itt_row itt_mtv_bottom_15 itt_hidden-lg">
      
      <!-- Hotel 2 START -->
      <div class="itt_col-xs-1 itt_col-sm-2 itt_col-md-2 itt_visible-xs itt_visible-sm itt_visible-md itt_hidden-lg">
        <div class="itt_hotel_media_block_position_two">
          <ul class="itt_pull-left">
            <li class="itt_font_weight_bd itt_headt_title_color"><?php echo $client->lang['hotel'];?></li>
            <li class="itt_position_rel itt_form_list_mtv_down itt_hotel_media_width">
              <select class="itt_hotel itt_hotel_2 itt_mod_search_custom_select itt_hotel_package">
                <option value="0" selected="selected"><?php echo $client->lang['all_hotels'];?></option>
              </select>
            </li>
          </ul>
        </div>
      </div>
      <!-- Hotel 2 END -->
      
      
      <!-- Night on tour  2 START -->
      <div class="itt_col-xs-3 itt_col-xs-offset-3 itt_visible-xs itt_hidden-sm itt_hidden-md itt_hidden-lg"> 
        <div class="itt_night_select_mod_position_two">
          <div class="itt_font_weight_bd itt_headt_title_color itt_age_of_children"><?php echo $client->lang['nights_on_the_tour'];?></div>
          <ul class="itt_mtv_bottom_15 itt_pull-left">
            <li class="itt_position_rel itt_form_list_mtv_down">
              <select class="itt_tour_nights_from itt_tour_nights_from_2 itt_mod_search_custom_select itt_night_from_package">
                <?php echo $this->renderTemplate($parts.'selected_6_stretch.php'); ?>
              </select>
            </li>
          </ul>
          <ul class="itt_pull-left itt_add_night_position">
            <li class="itt_search_module_text_position_minus">-</li>
            <li class="itt_position_rel itt_form_list_mtv_down">
              <select class="itt_tour_nights_to itt_tour_nights_to_2 itt_mod_search_custom_select itt_night_till_package">
                <?php echo $this->renderTemplate($parts.'selected_14_stretch.php'); ?>
              </select>
            </li>
          </ul>
          <div class="itt_departure_date_two itt_search_module_grey_color">
            <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/mod_search_650x350/decor-note.png" height="3" width="16" class="itt itt_decor-note_img" alt="decor-note" /><?php echo $client->lang['and_from'];?>
            <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/mod_search_650x350/decor-note.png" height="3" width="16" class="itt itt_decor-note_img_two" alt="decor-note" />
          </div>
        </div>
      </div>
      <!-- Night on tour 2 END -->
      
      
      <!-- Price range 2 START -->
      <div class="itt_col-xs-5 itt_col-sm-3 itt_col-sm-offset-2 itt_col-md-2 itt_visible-xs itt_visible-sm itt_visible-md itt_hidden-lg">
        <div class="itt_price_range_block_two">
          <div class="itt_font_weight_bd itt_headt_title_color itt_age_of_children"><?php echo $client->lang['price_for_all_tour_participants'];?></div>
          <ul class="itt_pull-left itt_mtv_bootom_15">
            <li class="itt_position_rel itt_form_list_mtv_down itt_children_select_width">
              <select class="itt_food_select_media_position_two itt_currency_selector itt_currency itt_mod_search_custom_select itt_switch_price_package" name="itt_currency_selector">
                <?php
                if ($package_tour_default_form_value['currency_default'] != '') {
                  $currency_default_for_tpl = $package_tour_default_form_value['currency_default'];
                } else {
                  $currency_default_for_tpl = 1;// USD
                }
                ?>
                <?php echo $this->renderTemplate($parts.'currency_option_select_stretch.php', array('client'  => $client, 
                                                                                                    'default' => $currency_default_for_tpl)
                                                ); ?>
              </select>
            </li>
          </ul>
          <ul class="itt_pull-left itt_mtv_bootom_15 itt_first_currency_select_position">  
            <li class="itt_position_rel itt_form_list_mtv_down itt_form_text_align_right">
              <select class="itt_amount_list_relation_currency_from itt_amount_list_relation_currency_from_2 itt_mod_search_custom_select itt_price_from_package itt_price_from">
                <!-- Set data list in search-module.js" -->
              </select>
            </li>
          </ul>
          <ul class="itt_pull-left itt_mtv_bootom_15 itt_position_rel itt_price_two_position">  
            <li class="itt_search_module_text_position_minus">-</li>
            <li class="itt_position_rel itt_form_list_mtv_down itt_form_text_align_right">
              <select class="itt_border_rd4 itt_form-control itt_font_weight_bd itt_amount_list_relation_currency_to itt_amount_list_relation_currency_to_2 itt_mod_search_custom_select itt_price_till_package itt_price_till">
                <!-- Set data list in search-module.js" -->
              </select>
            </li>
          </ul>
          <div class="itt_departure_date_thee itt_search_module_grey_color">
            <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/mod_search_650x350/decor-note.png" height="3" width="16" class="itt itt_decor-note_img" alt="decor-note" /><?php echo $client->lang['and_from'];?>
            <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/mod_search_650x350/decor-note.png" height="3" width="16" class="itt itt_decor-note_img_two" alt="decor-note" />
          </div> 
        </div>
      </div>
      <!-- Price range 2 END -->
      
      
      <!-- Departure block 3 START -->
      <div class="itt_col-sm-1 itt_col-sm-offset-1 itt_col-md-2 itt_col-md-offset-2 itt_hidden-xs itt_visible-sm itt_visible-md itt_hidden-lg">
        <div class="itt_departure_media_block_three">
          <ul class="itt_pull-left">
            <li class="itt_font_weight_bd itt_headt_title_color"><?php echo $client->lang['fly_departure'];?></li>
            <li class="itt_position_rel itt_form_list_mtv_down itt_departue_select_width">
              <select class="itt_mod_search_custom_select itt_departure_city itt_departure_city_2 itt_departure_city_package">
                <?php
                $list = $package_tour_form_field['departure_city'];
                $default = safe($package_tour_default_form_value, 'departure_city', null);
                foreach($list as $departure_city_item) {
                   $selected = $default == $departure_city_item['id'] ? "selected='selected'" : '';
                   echo "<option value='".$departure_city_item['id']."'". $selected." >".$departure_city_item['name']."</option>";
                } ?>
              </select>
            </li>
          </ul>
        </div>
      </div>
      <!-- Departure block 3 END -->
      
    </div>
    
    <div class="itt_row itt_mtv_bottom_15 itt_hidden-sm itt_hidden-md itt_hidden-lg itt_position_absolute_departure">
      
      <!-- Departure block 2 START -->
      <div class="itt_col-xs-1 itt_visible-xs itt_hidden-sm itt_hidden-md itt_hidden-lg">
        <div class="itt_departure_media_block_two">
          <ul class="itt_pull-left">
            <li class="itt_font_weight_bd itt_headt_title_color"><?php echo $client->lang['fly_departure'];?></li>
            <li class="itt_position_rel itt_form_list_mtv_down itt_departue_select_width">
              <select class="itt_mod_search_custom_select itt_departure_city itt_departure_city_3 itt_departure_city_package">
                <?php
                $list = $package_tour_form_field['departure_city'];
                $default = safe($package_tour_default_form_value, 'departure_city', null);
                foreach($list as $departure_city_item) {
                   $selected = $default == $departure_city_item['id'] ? "selected='selected'" : '';
                   echo "<option value='".$departure_city_item['id']."'". $selected." >".$departure_city_item['name']."</option>";
                } ?>
              </select>
            </li>
          </ul>
        </div>
      </div>
      <!-- Departure block 2 END -->
      
    </div>
    
    <div class="itt_button_search_tour">
      <button type="button" name="send" class="itt_search_tour_btn itt_bg_color_search_module itt_font_weight_bd itt_color_white itt_border_rd8 itt package itt_tour_search_button itt_tour_search_button_with_color"><?php echo $client->lang['find_tours'];?></button>
    </div>
    
  </div>
  
  
  
  <!-- Begin hidden input use for search and apply settings -->
  <input type="hidden" class="itt_tour_per_page" name="items_per_page" value="75">
  
  <?php
  // Set def currency from config // '110' валюта источник => '1' => USD // use in open tour view
  ?>
  <input type="hidden" name="currency_default_for_package_tour" value="<?php echo $package_tour_default_form_value['currency_default']; ?>"/>
  
  <?php
  // Add hidden input for use setting 'module2_type_select' in js
  $custom_select_name = 'itt_custom_select_piptik_small';// Set default value
  if (array_key_exists('module2_type_select', $package_tour_default_form_value)) {
    if ($package_tour_default_form_value['module2_type_select'] == '2'){
        $custom_select_name = 'itt_custom_select_piptik_big';
    }
  }
  ?>
  <input type="hidden" name="module2_type_select" value="<?php echo $custom_select_name; ?>"/>

  <?php
  // Save agency setting to session for use in template
  $_SESSION['agency_config_package_tour_default_form_value']['module2_hide_pay_form'] = $client->get_config('search_module_module2_hide_pay_form') ? $client->get_config('search_module_module2_hide_pay_form') : 0;
  // Add hidden input for use setting 'module2_hide_pay_form' in js
  ?>
  <input type="hidden" name="module2_hide_pay_form" value="<?php echo $client->get_config('search_module_module2_hide_pay_form') ? $client->get_config('search_module_module2_hide_pay_form') : 0; ?>"/>

  <!-- Begin list data from all custom select -->
  <input type="hidden" name="itt_date_period_package" value="12">
  <input type="hidden" name="itt_food_package" value="496 388 498 512 560 1956">
  <input type="hidden" name="itt_adult_package" value="<?php echo ( (trim($client->get_config('adult_amount_def')) != '') && ($client->get_config('adult_amount_def') > 0) && ($client->get_config('adult_amount_def') <=5) ) ? $client->get_config('adult_amount_def') : 2; ?>">
  <input type="hidden" name="itt_children_package" value="0">
  <input type="hidden" name="itt_child1_age_package" value="0">
  <input type="hidden" name="itt_child2_age_package" value="0">
  <input type="hidden" name="itt_child3_age_package" value="0">
  <input type="hidden" name="itt_hotel_package" value="0">
  <input type="hidden" name="itt_night_from_package" value="6">
  <input type="hidden" name="itt_night_till_package" value="14">
  <?php
  if ($package_tour_default_form_value['currency_default'] == '1') {
      $itt_switch_price_package = 'USD';
      $itt_price_from_package = 100;
      $itt_price_till_package = 10000;
  } elseif ($package_tour_default_form_value['currency_default'] == '10') {
      $itt_switch_price_package = 'EUR';
      $itt_price_from_package = 100;
      $itt_price_till_package = 10000;    
  } elseif ($package_tour_default_form_value['currency_default'] == '2') {
      $itt_switch_price_package = 'UAH';
      $itt_price_from_package = 0;
      $itt_price_till_package = 100000;
  } else {
      $itt_switch_price_package = 'USD';
      $itt_price_from_package = 100;
      $itt_price_till_package = 10000;
  }
  ?>
  <input type="hidden" name="itt_switch_price_package" value="<?php echo $itt_switch_price_package;?>">
  <input type="hidden" name="itt_price_from_package" value="<?php echo $itt_price_from_package;?>">
  <input type="hidden" name="itt_price_till_package" value="<?php echo $itt_price_till_package;?>">
  <?php $departure_city_package_default = safe($package_tour_default_form_value, 'departure_city', 2014); /* 2014 => Kiev */ ?>
  <input type="hidden" name="itt_departure_city_package" value="<?php echo $departure_city_package_default; ?>">
  <!-- End list data from all custom select -->
  <!-- End hidden input use for search and apply settings -->
</form>