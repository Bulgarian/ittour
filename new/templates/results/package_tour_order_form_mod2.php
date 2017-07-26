<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

function get_package_tour_order_form_mod2(&$client, &$info_message, &$search_result) {
  
  // Set url ittour
  $ittour_url = 'http://www.ittour.com.ua/';
  
  // Set price in base currency
  if (isset($search_result['offers'][0]['currency_id'])) {
    // Version base currency
    $price = ceil($search_result['offers'][0]['prices'][$search_result['offers'][0]['currency_id']]);
    // Set base currency_id
    $currency_id = $search_result['offers'][0]['currency_id'];
  } else {
    // Version old logic as old mod search
    $price = ceil($search_result['offers'][0]['prices'][$search_result['offers'][0]['currency']['id']]);
    // Set base currency_id
    $currency_id = $search_result['offers'][0]['currency']['id'];
  }
  
  $title = $client->lang['price_for_number'];
  if($search_result['offers'][0]['currency']['id'] == '2') {
    $currency = $client->lang['uah'];
    $title .= ' ( '.ceil($search_result['offers'][0]['prices']['1']).' $ | '.ceil($search_result['offers'][0]['prices']['10']).' €)';
    
  }
  
  // Set array for view
  $currencies = array( '1'  => array('symb' => $client->lang['dollar_symbol'],  'price' => ceil($search_result['offers'][0]['prices']['1']))
                     , '2'  => array('symb' => $client->lang['uah'],            'price' => ceil($search_result['offers'][0]['prices']['2']))
                     , '10' => array('symb' => $client->lang['euro_symbol'],    'price' => ceil($search_result['offers'][0]['prices']['10']))
                     );
  
  ob_start();


// Set relation 'country_id' => position x,y for flag in sprite
$relation_country_id_position_sprite_array = set_relation_country_id_position_sprite_array();

// Set tour date (view version)
$date_from_for_view = replace_in_date_month_en_to_month_ru($search_result['offers'][0]['locations'][0]['date_from'], $client);
$date_till_for_view = replace_in_date_month_en_to_month_ru($search_result['offers'][0]['locations'][0]['date_till'], $client);

// Set data 'city from case' and 'flight included' 
if (isset($search_result['offers'][0]['transports']) && isset($search_result['offers'][0]['transports'][0])) {
  // Check genetive case Example: 'из Киевa', 'из Киев'
  if ($search_result['offers'][0]['transports'][0]['from_city_name_genitive_case'] != '') {
    $from_city_name = $client->lang['text_is'] . ' ' . $search_result['offers'][0]['transports'][0]['from_city_name_genitive_case'];
  } else {
    $from_city_name = $client->lang['text_is'] . ' ' . $search_result['offers'][0]['transports'][0]['from_city_name'];
  }
  $flight_included = (($search_result['offers'][0]['kind_id'] == 92) ? $client->lang['bus'] : $client->lang['included']);
} else {
  $from_city_name = $client->lang['without_flight'];
  $flight_included = $client->lang['not_included'];
}

// Set data 'country to'
if ($search_result['offers'][0]['transports'][0]['to_country_name_accusative_case'] != '') {
    $to_country_name = $search_result['offers'][0]['transports'][0]['to_country_name_accusative_case'];
} else {
    $to_country_name = $search_result['offers'][0]['transports'][0]['to_country_name'];
}

// Set id for use in js and html google.maps
$id_for_html_google_map_small = rand(100500, 500100);
$id_for_html_google_map_big = rand(500101, 987654);
$id_for_html_div_begin_tour_content = rand(1, 100123);
?>

<!-- Begin add google.map -->
<script type="text/javascript">
<?php
// Check google map coordinats
$flag_not_google_map = true;
if (array_key_exists('otpusk_hotel_description', $search_result['offers'][0])) {
  if(isset($search_result['offers'][0]['otpusk_hotel_description']['lat']) && isset($search_result['offers'][0]['otpusk_hotel_description']['lng'])) {
    $flag_not_google_map = false;
    $google_map_lat = $search_result['offers'][0]['otpusk_hotel_description']['lat'];
    $google_map_lng = $search_result['offers'][0]['otpusk_hotel_description']['lng'];
  } else {
    $flag_not_google_map = true;
  }
}

// Set lat, lng for item tour map
if (!$flag_not_google_map) {
  echo "var lat = " . $google_map_lat . ";\n"
     . "var lng = " . $google_map_lng . ";\n";
}
?>

jQueryMod2(document).ready(function(){
    // Load mini google map
    <?php
    // Check isset google map coordinats
    if (!$flag_not_google_map) {
      ?>
      // Load mini google map
      setTimeout(function(){
          initialize_google_map_custom_view_tour(7, 'map_canvas_<?php echo $id_for_html_google_map_small; ?>', <?php echo $google_map_lat; ?>, <?php echo $google_map_lng; ?>);
      }, 1700);
      <?php
    }
    ?>
    
    // Get tab id for item tour
    var id_tab_for_current_tour = '#itt_view_item_tour_ramdom_id_<?php echo $id_for_html_div_begin_tour_content; ?> ';
    
    // Handler sub tabs (package tour)
    jQueryMod2(id_tab_for_current_tour + '.itt_accordion_tabs').children('li').first().children('a.title_sub_tab').addClass('is-active').next().addClass('is-open').show();
    var ip = jQueryMod2(id_tab_for_current_tour + '.itt_tab_map section');    
    jQueryMod2(id_tab_for_current_tour + '.itt_accordion_tabs').on('click', 'li > a.title_sub_tab', function(event) {
        if (jQueryMod2(this).parent('.itt_tab_map').length != 0 && !ip.hasClass('itt_map_loaded')) {
            setTimeout(function(){
                ip.find('div:first').css('display','none');// Hide text 'Load map...'
            }, 600);
        } else {
            // Если кликаю не на таб с картой, удалять карту для новой подгрузки.
            ip.find('div.itt_map_big_preview_tour_view').html('').removeAttr('style');
        }
        if (!jQueryMod2(this).hasClass('is-active')) {
            event.preventDefault();
            jQueryMod2(id_tab_for_current_tour + '.itt_accordion_tabs .is-open').removeClass('is-open').hide();
            jQueryMod2(this).next().toggleClass('is-open').toggle();
            jQueryMod2(id_tab_for_current_tour + '.itt_accordion_tabs').find('.is-active').removeClass('is-active');
            jQueryMod2(this).addClass('is-active');
        } else {
            event.preventDefault();
        }
    });
    
    // Ставим валюту по умолчанию такую как в фильтре ('Показывать цену в') результата поиска
    // Set default currency from filter
    var default_currency_from_config = jQueryMod2('#itt_issuing_search_package_tours select.itt_issuing_search_custom_select_swith_currency option:selected').val();    
    if (typeof default_currency_from_config != 'undefined' || default_currency_from_config == '1' || default_currency_from_config == '2' || default_currency_from_config == '10') {
      // Set new currency
      jQueryMod2(id_tab_for_current_tour + ' .itt_currency_tour_selector option[currency_id="' + default_currency_from_config + '"]').prop('selected', true);
      jQueryMod2(id_tab_for_current_tour + ' input[name="itt_current_currency_id"]').val(default_currency_from_config);
      
      // Get new price
      var new_price_for_new_currency = jQueryMod2(id_tab_for_current_tour + ' .itt_currency_tour_selector option:selected').val();
      
      // Set new price
      jQueryMod2(id_tab_for_current_tour + ' .itt_price_tour_view_user').html(new_price_for_new_currency);
    }
    
    // Set active sub tabs (package tour)
    setTimeout(function(){
      jQueryMod2(id_tab_for_current_tour + '.itt_accordion_tabs').children('li').first().children('a.title_sub_tab').trigger('click');// Set active sub tab
    },500);
});
</script>
<!-- End add google.map -->

<div class="itt_tour_view_item" id="itt_view_item_tour_ramdom_id_<?php echo $id_for_html_div_begin_tour_content; ?>">
  <!-- Begin new html 09.04.2015 -->
  <div class="itt_container-fluid">
    <div class="itt_row">
      <!-- Left block START -->
      <div class="itt_col-lg-9 itt_col-md-9 itt_col-sm-8 itt_col-xs-7"> 
        <div class="itt_color_blue itt_hotel_name" title="<?php echo $search_result['offers'][0]['locations'][0]['hotel_name']; ?>"><?php echo $search_result['offers'][0]['locations'][0]['hotel_name']; ?></div>
        <div class="itt_wifi_icons_position">
          <?php
          $set_hotel_stars = 4;
          if (isset($search_result['offers'][0]['locations'][0]['hotel_rating_name'])) {
            // Set stars horel (ittour => 'hotel_rating_name')
            $set_hotel_stars = $search_result['offers'][0]['locations'][0]['hotel_rating_name'];
          }
          ?>
          <span class="itt_hotel_star">&nbsp;<?php echo $set_hotel_stars; ?>*</span>
          <?php          
          // Check 'free wifi'
          if (array_key_exists('otpusk_hotelservice_info', $search_result['offers'][0])) {
            // Service status:
            // "1","yes","есть"
            // "2","no","нет"
            // "3","pay","платно"
            // "4","free","бесплатно"
            if (($search_result['offers'][0]['otpusk_hotelservice_info']['wifi'] == 1) || ($search_result['offers'][0]['otpusk_hotelservice_info']['wifi'] == 4)) {
            ?>
              <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/icon_wifi.gif" width="25" height="25" class="itt" alt="icon_wifi" title="<?php echo $client->lang['free_wi_fi']; ?>" />
              <span class="itt_color_green" title="<?php echo $client->lang['free_wi_fi']; ?>"><?php echo $client->lang['free_wi_fi_eng']; ?></span>
            <?php
            }
          }
          ?>
        </div>
        <div class="itt_left_colum_width">
        <ul class="itt_add_children itt_package_star_position">
          <li>
            <ul class="itt_hotel_rate">
              <?php
              if (isset($search_result['offers'][0]['locations'][0]['hotel_rating_name'])) {
                // Set stars horel (ittour => 'hotel_rating_name')
                $k=0;
                for ($i=1; $i<=$search_result['offers'][0]['locations'][0]['hotel_rating_name']; $i++) {
                  $k++;
                  ?>
                  <li><span class="itt_glyphicon itt_glyphicon-star itt_color_yellow"></li>
                  <?php
                }
                for ($j=$k; $j<5; $j++) {
                  ?>
                  <li><span class="itt_glyphicon itt_glyphicon-star-empty itt_color_yellow"></span></li>
                  <?php
                }
              ?>
              <?php
              } else {
                // Default 4* bolvanka
              ?>
                <li><span class="itt_glyphicon itt_glyphicon-star itt_color_yellow"></li>
                <li><span class="itt_glyphicon itt_glyphicon-star itt_color_yellow"></li>
                <li><span class="itt_glyphicon itt_glyphicon-star itt_color_yellow"></li>
                <li><span class="itt_glyphicon itt_glyphicon-star itt_color_yellow"></li>
                <li><span class="itt_glyphicon itt_glyphicon-star-empty itt_color_yellow"></span></li>
              <?php
              }
              ?>
            </ul>
          </li>
          <?php
          if (!$flag_not_google_map) {
          ?>
          <li><span><a href="javascript:void(0);" class="itt_open_map"><?php echo $client->lang['show_on_map']; ?></a></span></li>
          <?php
          }
          ?>
        </ul>
        <ul class="itt_hotel_rate">
          <li>
            <?php
            // Check isset flag => show flag
            if (array_key_exists($search_result['offers'][0]['locations'][0]['country_id'], $relation_country_id_position_sprite_array)) {
            ?>
            <span class="itt_flag_counter itt_border_rd4" style="background: url(<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/flags-web3.png) no-repeat <?php echo $relation_country_id_position_sprite_array[ $search_result['offers'][0]['locations'][0]['country_id'] ]['x']; ?>px <?php echo $relation_country_id_position_sprite_array[ $search_result['offers'][0]['locations'][0]['country_id'] ]['y']; ?>px;"></span>
            <?php
            }
            ?>
          </li>
          <li><span class="itt_location_tour_view"><?php echo $search_result['offers'][0]['locations'][0]['country_name']; ?>, <?php echo $search_result['offers'][0]['locations'][0]['region_name']; ?></span></li>
        </ul>
        <div class="itt_photo_left_block">
          <div class="itt_prew_photo itt_border_rd4 itt_hotel_big_photo">
            <?php
            // Main image hotel
            if (array_key_exists('otpusk_hotel_images', $search_result['offers'][0])) {
              // Otpusk photo
              if (isset($search_result['offers'][0]['otpusk_hotel_images'][0])) {
                // Show first image as main
                ?>
                <img src="<?php echo $search_result['offers'][0]['otpusk_hotel_images'][0]['href_ittour_big']; ?>" width="250" height="180" alt="hotel_prew" />
                <?php
              }
            } elseif ($search_result['offers'][0]['locations'][0]['hotel_image'] != '') {
              // Ittour photo
              ?>
              <img src="<?php echo $ittour_url . $search_result['offers'][0]['locations'][0]['hotel_image']; ?>" width="250" height="180" alt="hotel_prew" />
              <?php
            } elseif ($search_result['offers'][0]['locations'][0]['hotel_images_ittour'][0]) {
              // Ittour country photo
              $img_num_for_view = rand(0, (count($search_result['offers'][0]['locations'][0]['hotel_images_ittour']) - 1));
              ?>
              <img src="<?php echo $ittour_url . $search_result['offers'][0]['locations'][0]['hotel_images_ittour'][$img_num_for_view]; ?>" width="250" height="180" alt="hotel_prew" />
              <?php
            } else {
              // No photo
              ?>
              <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/<?php echo $client->lang['no_img_png']; ?>" width="250" height="180" alt="hotel_prew" />
              <?php
            }
            ?>
          </div>
        </div>
        </div>
        <div class="itt_hidden-xs itt_hidden-sm itt_visible-md itt_visible-lg itt_left_block_right_media_colum">
          <table class="itt_select_date_table_one_big_width">
            <tr>
              <td><span><?php echo $client->lang['tour_dates']; ?>:</span></td>
              <td><?php echo $client->lang['date_from'] . ' ' . $date_from_for_view . ' ' . $client->lang['date_to'] . ' ' . $date_till_for_view; ?></td>
            </tr>
            <tr>
              <td><span><?php echo $client->lang['number']; ?>:</span></td>
              <td><?php echo $search_result['offers'][0]['locations'][0]['room_type_name']; ?></td>
            </tr>
            <tr>
              <td><span><?php echo $client->lang['accomodation']; ?>:</span></td>
              <td>
                <?php echo $search_result['offers'][0]['adult_amount'] . ($search_result['offers'][0]['adult_amount']==1 ? $client->lang['adults_count_genitive_one'] : $client->lang['adults_count_genitive_many']); ?>
                  <?php
                  if ($search_result['offers'][0]['child_amount']) {
                    $ages = array();
                    for ($i = 1; $i <= $search_result['offers'][0]['child_amount']; $i++) {
                      $age_range = bin_age_to_range($search_result['offers'][0]['child_age0' . $i]);
                      $ages[] = implode('-', $age_range);
                    }
                    ?>
                    + <?php echo $search_result['offers'][0]['child_amount']; ?> <?php echo $client->lang['children_reduction']; ?>
                    <span class="ittour_order_description">
                      <span class="ittour_order_thin_font">
                        (<?php echo implode(', ', $ages); ?> <?php echo $client->lang['old']; ?>)
                      </span>
                    </span>
                  <?php 
                  }
                  ?>
              </td>
            </tr>
            <tr>
              <td><span><?php echo $client->lang['flight_from_short']; ?>:</span></td>
              <td>
                <?php echo $from_city_name . ' ' . $client->lang['at_sm'] . ' '; ?>
                <?php echo $search_result['offers'][0]['duration'] . ' ' . $client->declOfNum($search_result['offers'][0]['duration'], array($client->lang['night'],  $client->lang['nights_low'], $client->lang['nights_hike'])); ?>
              </td>
            </tr>
            <tr>
              <td><span><?php echo $client->lang['avia_flight']; ?>:</span></td>
              <td><?php echo $flight_included; ?></td>
            </tr>
            <tr>
              <td><span><?php echo $client->lang['meal']; ?>:</span></td>
              <td><?php echo $search_result['offers'][0]['locations'][0]['meal_type_description']; ?></td>
            </tr>
            <tr>
              <td></td><td></td>
            </tr>
          </table>
          <ul class="itt_flight_list">
            <li><span><?php echo $client->lang['flight_go']; ?>:</span></li>
            <li class="itt_relative_position">
              <?php if (isset($search_result['offers'][0]['transports']) && isset($search_result['offers'][0]['transports'][0])) { ?>
                <select class="itt_flight_date_custom_select itt_flight_there_package">
                  <?php foreach ($search_result['offers'][0]['transports'][0]['flights'] as $key => $flights) { ?>
                    <option>
                        <?php echo $search_result['offers'][0]['transports'][0]['from_city_name'] . ' '
                                 . '(' . $flights['from_airport'] . ') '
                                 . $flights['date_from'] . ' '
                                 . substr($flights['time_from'], 0, 5) . ' - '
                                 . $search_result['offers'][0]['transports'][0]['to_city_name'] . ' '
                                 . '(' . $flights['to_airport'] . ') '
                                 . $flights['date_till'] . ' '
                                 . substr($flights['time_till'], 0, 5);
                        ?>
                    </option>
                  <?php } ?>
                </select>
              <?php } else { ?>
                <select class="itt_flight_date_custom_select itt_flight_there_package"><option><?php echo $client->lang['no_information']; ?></option></select>
              <?php } ?>
            </li>
          </ul>
          <ul class="itt_flight_list">
            <li><span><?php echo $client->lang['flight_back']; ?>:</span></li>
            <li class="itt_relative_position">
              <?php if (isset($search_result['offers'][0]['transports']) && isset($search_result['offers'][0]['transports'][1])) { ?>
                <select class="itt_flight_date_custom_select itt_flight_back_package">
                  <?php foreach ($search_result['offers'][0]['transports'][1]['flights'] as $key => $flights) { ?>
                    <option>
                      <?php echo $search_result['offers'][0]['transports'][1]['from_city_name'] . ' '
                               . '(' . $flights['from_airport'] . ') '
                               . $flights['date_from'] . ' '
                               . substr($flights['time_from'], 0, 5) . ' - '
                               . $search_result['offers'][0]['transports'][1]['to_city_name'] . ' '
                               . '(' . $flights['to_airport'] . ') '
                               . $flights['date_till'] . ' '
                               . substr($flights['time_till'], 0, 5);
                      ?>
                    </option>
                  <?php } ?>
                </select>
              <?php } else { ?>
                <select class="itt_flight_date_custom_select itt_flight_back_package"><option><?php echo $client->lang['no_information']; ?></option></select>
              <?php } ?>
            </li>
          </ul>
        </div>
      </div>
      <!-- Left block END -->
      <!-- Right block START -->
      <div class="itt_col-lg-3 itt_col-md-3 itt_col-sm-4 itt_col-xs-5 itt_right_block_max-width">
        <div class="itt_price_block itt_border_rd4">
          <ul class="itt_price_info">
            <li><?php echo $client->lang['price']; ?></li>
            <li class="itt_price_size itt_color_green itt_price_tour_view_user"><?php echo $price; ?></li>
            <li class="itt_select_currency">
              <select class="itt itt_border_rd4 itt_select_modp itt_currency_tour_selector">
                <?php
                // Currency with price
                foreach($currencies as $cur=>$val) {
                  if ($cur == $currency_id) {
                    echo '<option value="' . $val['price'] . '" currency_id="' . $cur . '" selected="selected">' . $val['symb'] . '</option>';
                  } else {
                    echo '<option value="' . $val['price'] . '" currency_id="' . $cur . '">' . $val['symb'] . '</option>';
                  }
                }
                ?>
              </select>
              <input type="hidden" name="itt_current_currency_id" value="<?php echo $currency_id; ?>">
            </li>
          </ul>
          <div class="itt_separat_bottom2"></div>
          <ul class="itt_tors_attributes">
            <li><?php echo $client->lang['for_all_tour_participants']; ?></li>
            <li>
              <ul class="itt_tors_actual">
                <li><span class="itt_glyphicon itt_glyphicon-ok itt_color_green"></span> <span class="itt_color_green"><?php echo $client->lang['actual_tour']; ?></span></li>
              </ul>
              <ul class="itt_tors_actual itt_order_desc_price_change">
                <li class="itt_change_price_flight_symbol"><span class="itt_glyphicon itt_glyphicon-warning-sign itt_color_red"></span> <span class="itt_color_red"><?php echo $client->lang['price_change']; ?></span></li>
              </ul>
              <ul class="itt_tors_actual itt_order_desc_stop_price">
                <li class="itt_change_price_flight_symbol"><span class="itt_glyphicon itt_glyphicon-warning-sign itt_color_red"></span> <span class="itt_color_red"><?php echo $client->lang['stop_price']; ?></span></li>
              </ul>
              <ul class="itt_tors_actual itt_order_desc_stop_flight">
                <li class="itt_change_price_flight_symbol"><span class="itt_glyphicon itt_glyphicon-warning-sign itt_color_red"></span> <span class="itt_color_red"><?php echo $client->lang['stop_flight']; ?></span></li>
              </ul>
              <ul class="itt_tors_actual itt_order_desc_no_validate">
                <li class="itt_change_price_flight_symbol"><span class="itt_glyphicon itt_glyphicon-warning-sign itt_color_red"></span> <span class="itt_color_red"><?php echo $client->lang['no_validate_new_mod']; ?></span></li>
              </ul>
            </li>
            <?php
            // Check global_id as new version
            if (isset($search_result['offers'][0]['global_id'])) {
              // Show global_id
              ?>
              <li><?php echo $client->lang['tour_id']; ?>: <?php echo $search_result['offers'][0]['global_id']; ?></li>
              <?php
            } else {
              // Show as old mod
              ?>
              <li><?php echo $client->lang['tour_id']; ?>: <?php echo $search_result['offers'][0]['id']; ?>-<?php echo $search_result['offers'][0]['sharding_rule_id']; ?></li>
              <?php
            }
            ?>
          </ul>
        </div>
        <button name="send" class="itt_mtv_bottom itt_send_btn itt_color_white itt_border_rd4 itt itt_button_reserve" type="submit"><?php echo $client->lang['book']; ?></button>
        <button name="send" class="itt_mtv_bottom itt_send_btn itt_color_white itt_border_rd4 itt itt_button_send_request" type="submit"><?php echo $client->lang['submit_request']; ?></button>
        <ul class="itt_more_information_about_booking itt_mtv_top_10">
          <li><a href="javascript:void(0);" class="itt_button_more_information_about_booking"><?php echo $client->lang['more_information_about_booking']; ?></a></li>
          <li><a href="javascript:void(0);" class="itt_button_more_information_about_booking"><img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/question_images_13.png" alt="question_images" width="13" height="13" /></a></li>
        </ul>
      </div>
      <!-- Right block END -->
    </div>
    
    <?php
    if (!$flag_not_google_map) {
      // Isset map
    ?>
    <!-- Map and photo blcok one START -->
    <div class="itt_row itt_person2_bg itt_map_and_photo_block_one">
      <div class="itt_prew_map itt_border_rd4 itt_map_background">
        <div id="map_canvas_<?php echo $id_for_html_google_map_small; ?>" class="itt_map_small_preview"></div>
        <a href="javascript:void(0);" class="itt_open_map_mini"></a>
      </div>
      <div class="itt_scroll_arrow_back">
        <a href="javascript:void(0);" class="itt_btn_left_gallery_photo"><img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/itt_arrow_photo.png" class="itt" width="19" height="35" alt="itt_arrow_back" /></a>
      </div>
      <div class="itt_position_arrow">
        <?php
        // * Begin image gallery * 
        ?>
        <ul class="itt_scroll_photo_list itt_list_item_position itt_border_rd4">
          <?php
          // Show images for hotel
          if (array_key_exists('otpusk_hotel_images', $search_result['offers'][0])) {
            // Otpusk photo
            $img_count = count($search_result['offers'][0]['otpusk_hotel_images']);
            if ($img_count>0) {
              // Show image
              ?>
              <li class="itt_photo_active"><a href="javascript:void(0);"><img src="<?php echo $search_result['offers'][0]['otpusk_hotel_images'][0]['href_ittour_big']; ?>" class="itt" width="120" height="80" alt="hotel_photo" /></a></li>
              <?php
              for ($i=1; $i<$img_count; $i++) {
              ?>
                <li><a href="javascript:void(0);"><img src="<?php echo $search_result['offers'][0]['otpusk_hotel_images'][$i]['href_ittour_big']; ?>" class="itt" width="120" height="80" alt="hotel_photo" /></a></li>
              <?php
              }
            }
          } elseif (count($search_result['offers'][0]['locations'][0]['hotel_info']['images'])>0) {
            // Ittour photo
            // Delete copy image
            if (count($search_result['offers'][0]['locations'][0]['hotel_info']['images'])>1) {
              unset($search_result['offers'][0]['locations'][0]['hotel_info']['images'][0]);
            }
            foreach ($search_result['offers'][0]['locations'][0]['hotel_info']['images'] as $image) {
              ?>
              <li><a href="javascript:void(0);"><img src="<?php echo $ittour_url . $image; ?>" class="itt" width="120" height="80" alt="hotel_photo" /></a></li>
              <?php
            }
          } elseif (count($search_result['offers'][0]['locations'][0]['hotel_images_ittour'])>0) {
            // Ittour country photo
            foreach ($search_result['offers'][0]['locations'][0]['hotel_images_ittour'] as $image) {
              ?>
              <li><a href="javascript:void(0);"><img src="<?php echo $ittour_url . $image; ?>" class="itt" width="120" height="80" alt="hotel_photo" /></a></li>
              <?php
            }
          } else {
            // No photo
            ?>
            <li class="itt_photo_active"><img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/<?php echo $client->lang['no_img_png']; ?>" class="itt" width="120" height="80" alt="hotel_photo" /></li>
            <?php
          }
          ?>
        </ul>
        <?php
        // * End image gallery *
        ?>
        <!-- Begin pop-up for hotel big image, custom tooltip -->
        <div class="itt_hotel_image_item_custom_tooltip_view_tour"><img src="" /></div>
        <!-- End pop-up for hotel big image, custom tooltip -->  
      </div>
      <div class="itt_scroll_arrow_go">
        <a href="javascript:void(0);" class="itt_btn_right_gallery_photo"><img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/itt_arrow_photo.png" class="itt" width="19" height="35" alt="itt_arrow_back" /></a>
      </div>
    </div>
    <!-- Map and photo blcok one END -->
    <?php
    }// end if (!$flag_not_google_map) { // Isset map
    ?>
    
    <?php
    if ($flag_not_google_map) {
      // Not map
    ?>
    <!-- Map and photo blcok two START -->
    <div class="itt_row itt_person2_bg itt_map_and_photo_block_one">
      <div class="itt_scroll_arrow_back">
        <a href="javascript:void(0);" class="itt_btn_left_gallery_photo"><img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/itt_arrow_photo.png" class="itt" width="19" height="35" alt="itt_arrow_back" /></a>
      </div>
      <div class="itt_position_arrow">
        <ul class="itt_scroll_photo_list itt_list_item_position_two itt_border_rd4">
          <?php
          // Show images for hotel
          if (array_key_exists('otpusk_hotel_images', $search_result['offers'][0])) {
            // Otpusk photo
            $img_count = count($search_result['offers'][0]['otpusk_hotel_images']);
            if ($img_count>0) {
              // Show image
              ?>
              <li class="itt_photo_active"><a href="javascript:void(0);"><img src="<?php echo $search_result['offers'][0]['otpusk_hotel_images'][0]['href_ittour_big']; ?>" class="itt" width="120" height="80" alt="hotel_photo" /></a></li>
              <?php
              for ($i=1; $i<$img_count; $i++) {
              ?>
                <li><a href="javascript:void(0);"><img src="<?php echo $search_result['offers'][0]['otpusk_hotel_images'][$i]['href_ittour_big']; ?>" class="itt" width="120" height="80" alt="hotel_photo" /></a></li>
              <?php
              }
            }
          } elseif (count($search_result['offers'][0]['locations'][0]['hotel_info']['images'])>0) {
            // Ittour photo
            // Delete copy image
            if (count($search_result['offers'][0]['locations'][0]['hotel_info']['images'])>1) {
              unset($search_result['offers'][0]['locations'][0]['hotel_info']['images'][0]);
            }
            foreach ($search_result['offers'][0]['locations'][0]['hotel_info']['images'] as $image) {
              ?>
              <li><a href="javascript:void(0);"><img src="<?php echo $ittour_url . $image; ?>" class="itt" width="120" height="80" alt="hotel_photo" /></a></li>
              <?php
            }
          } elseif (count($search_result['offers'][0]['locations'][0]['hotel_images_ittour'])>0) {
            // Ittour country photo
            foreach ($search_result['offers'][0]['locations'][0]['hotel_images_ittour'] as $image) {
              ?>
              <li><a href="javascript:void(0);"><img src="<?php echo $ittour_url . $image; ?>" class="itt" width="120" height="80" alt="hotel_photo" /></a></li>
              <?php
            }
          } else {
            // No photo
            ?>
            <li class="itt_photo_active"><img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/<?php echo $client->lang['no_img_png']; ?>" class="itt" width="120" height="80" alt="hotel_photo" /></li>
            <?php
          }
          ?>
        </ul>
        <!-- Begin pop-up for hotel big image, custom tooltip -->
        <div class="itt_hotel_image_item_custom_tooltip_view_tour"><img src="" /></div>
        <!-- End pop-up for hotel big image, custom tooltip -->
      </div>
      <div class="itt_scroll_arrow_go">
        <a href="javascript:void(0);" class="itt_btn_right_gallery_photo"><img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/itt_arrow_photo.png" class="itt" width="19" height="35" alt="itt_arrow_back" /></a>
      </div>
    </div>
    <!-- Map and photo blcok two END -->
    <?php
    }// if ($flag_not_google_map) { // Not map
    ?>
    
    <!-- Tour media block START -->
    <div class="itt_info_tour_media_block itt_visible-xs itt_visible-sm itt_hidden-md itt_hidden-lg">
      <h2 class="itt_2h_tittle_tour"><?php echo $client->lang['tour_info']; ?></h2>
      <table class="itt_select_date_table_two_small_width itt_pull-left">
        <tr>
          <td><span><?php echo $client->lang['tour_dates']; ?>:</span></td>
          <td><?php echo $client->lang['date_from'] . ' ' . $date_from_for_view . ' ' . $client->lang['date_to'] . ' ' . $date_till_for_view; ?></td>
        </tr>
        <tr>
          <td><span><?php echo $client->lang['number']; ?>:</span></td>
          <td><?php echo $search_result['offers'][0]['locations'][0]['room_type_name']; ?></td>
        </tr>
        <tr>
          <td><span><?php echo $client->lang['accomodation']; ?>:</span></td>
          <td>
            <?php echo $search_result['offers'][0]['adult_amount'] . ($search_result['offers'][0]['adult_amount']==1 ? $client->lang['adults_count_genitive_one'] : $client->lang['adults_count_genitive_many']); ?>
              <?php
              if ($search_result['offers'][0]['child_amount']) {
                $ages = array();
                for ($i = 1; $i <= $search_result['offers'][0]['child_amount']; $i++) {
                  $age_range = bin_age_to_range($search_result['offers'][0]['child_age0' . $i]);
                  $ages[] = implode('-', $age_range);
                }
                ?>
                + <?php echo $search_result['offers'][0]['child_amount']; ?> <?php echo $client->lang['children_reduction']; ?>
                <span class="ittour_order_description">
                  <span class="ittour_order_thin_font">
                    (<?php echo implode(', ', $ages); ?> <?php echo $client->lang['old']; ?>)
                  </span>
                </span>
              <?php 
              }
              ?>
          </td>
        </tr>
        <tr>
          <td><span><?php echo $client->lang['flight_from_short']; ?>:</span></td>
          <td>
            <?php echo $from_city_name . ' ' . $client->lang['at_sm'] . ' '; ?>
            <?php echo $search_result['offers'][0]['duration'] . ' ' . $client->declOfNum($search_result['offers'][0]['duration'], array($client->lang['night'],  $client->lang['nights_low'], $client->lang['nights_hike'])); ?>
          </td>
        </tr>
      </table>
      <table class="itt_select_date_table_two_small_width itt_pull-left itt_select_date_teble_media_margin">
        <tr>
          <td><span><?php echo $client->lang['avia_flight']; ?>:</span></td>
          <td><?php echo $flight_included; ?></td>
        </tr>
        <tr>
          <td><span><?php echo $client->lang['meal']; ?>:</span></td>
          <td><?php echo $search_result['offers'][0]['locations'][0]['meal_type_description']; ?></td>
        </tr>
        <tr>
          <td></td><td></td>
        </tr>
      </table>
      <table class="itt_flight_list_two">
        <tr>
          <td><span><?php echo $client->lang['flight_go']; ?>:</span></td>
          <td class="itt_relative_position">
            <?php if (isset($search_result['offers'][0]['transports']) && isset($search_result['offers'][0]['transports'][0])) { ?>
              <select class="itt_flight_date_custom_select itt_flight_there_package">
                <?php foreach ($search_result['offers'][0]['transports'][0]['flights'] as $key => $flights) { ?>
                  <option>
                      <?php echo $search_result['offers'][0]['transports'][0]['from_city_name'] . ' '
                               . '(' . $flights['from_airport'] . ') '
                               . $flights['date_from'] . ' '
                               . substr($flights['time_from'], 0, 5) . ' - '
                               . $search_result['offers'][0]['transports'][0]['to_city_name'] . ' '
                               . '(' . $flights['to_airport'] . ') '
                               . $flights['date_till'] . ' '
                               . substr($flights['time_till'], 0, 5);
                      ?>
                  </option>
                <?php } ?>
              </select>
            <?php } else { ?>
              <select class="itt_flight_date_custom_select itt_flight_there_package"><option><?php echo $client->lang['no_information']; ?></option></select>
            <?php } ?>
          </td>
        </tr>
        <tr>
          <td><span><?php echo $client->lang['flight_back']; ?>:</span></td>
          <td class="itt_relative_position">
            <?php if (isset($search_result['offers'][0]['transports']) && isset($search_result['offers'][0]['transports'][1])) { ?>
              <select class="itt_flight_date_custom_select itt_flight_back_package">
                <?php foreach ($search_result['offers'][0]['transports'][1]['flights'] as $key => $flights) { ?>
                  <option>
                    <?php echo $search_result['offers'][0]['transports'][1]['from_city_name'] . ' '
                             . '(' . $flights['from_airport'] . ') '
                             . $flights['date_from'] . ' '
                             . substr($flights['time_from'], 0, 5) . ' - '
                             . $search_result['offers'][0]['transports'][1]['to_city_name'] . ' '
                             . '(' . $flights['to_airport'] . ') '
                             . $flights['date_till'] . ' '
                             . substr($flights['time_till'], 0, 5);
                    ?>
                  </option>
                <?php } ?>
              </select>
            <?php } else { ?>
              <select class="itt_flight_date_custom_select itt_flight_back_package"><option><?php echo $client->lang['no_information']; ?></option></select>
            <?php } ?>
          </td>
        </tr>
      </table>
    </div>
    <!-- Tour media block END -->
    
    <?php
    $spo_comment_string = '';
    $tmp_comment = explode("\n\n--\n\n", $search_result['offers'][0]['spo']['comment']);
    for($i=0; $i<count($tmp_comment); $i++) {
      if (stripos($tmp_comment[$i], 'show_in_new_module_search">1</span>') === false ) {
        $tmp_comment[$i] = '';
      }
    }
    for($i=0; $i<count($tmp_comment); $i++) {
      if ($tmp_comment[$i] != '') {
        $spo_comment_string .= $tmp_comment[$i] . '<br><br>';
      }
    }
    if ($spo_comment_string != '') {
    ?>
    <!-- tour more info block begin -->
    <h2 class="itt_2h_tittle_tour itt_mtv_top_20"><?php echo $client->lang['more_hotel_information']; ?></h2>
    <div class="itt_more_info_about_the_tour"><?php echo $spo_comment_string; ?></div>
    <!-- tour more info block end -->
    <?php
    }
    ?>
    
    <h2 class="itt_2h_tittle_tour itt_mtv_top_20"><?php echo $client->lang['hotel_information']; ?></h2>
  </div>
  <ul class="itt_accordion_tabs">
    <?php
    // Begin text description hotel
    if (array_key_exists('otpusk_hotel_description', $search_result['offers'][0])) {
    ?>
      <li class="itt_tab_head_cont">
        <a href="javascript:void(0);" class="title_sub_tab itt_open_tab"><?php echo $client->lang['description_hotel']; ?></a>
        <section class="itt is-open">
          <?php
          // Description full
          if ($search_result['offers'][0]['otpusk_hotel_description']['deschotel'] != '') {
          ?>
            <p class="itt"><strong class="itt"><?php echo $client->lang['hotel_features']; ?>:</strong> 
              <?php echo $search_result['offers'][0]['otpusk_hotel_description']['deschotel']; ?> 
              <?php echo ($search_result['offers'][0]['otpusk_hotel_description']['disposition']) ? ($search_result['offers'][0]['otpusk_hotel_description']['disposition'] . '.') : ''; ?> 
              <?php echo ($search_result['offers'][0]['otpusk_hotel_description']['featuresapt']) ? ($search_result['offers'][0]['otpusk_hotel_description']['featuresapt'] . '.') : ''; ?> 
              <?php echo ($search_result['offers'][0]['otpusk_hotel_description']['featureshotel']) ? ($search_result['offers'][0]['otpusk_hotel_description']['featureshotel'] . '.') : ''; ?> 
              <?php echo ($search_result['offers'][0]['otpusk_hotel_description']['descapt']) ? ($search_result['offers'][0]['otpusk_hotel_description']['descapt'] . '.') : ''; ?> 
            </p>
          <?php
          }
          
          // Address
          if ($search_result['offers'][0]['otpusk_hotel_description']['address'] != '') {
          ?>
            <p class="itt"><strong class="itt"><?php echo $client->lang['hotel_address']; ?>:</strong> <?php echo $search_result['offers'][0]['otpusk_hotel_description']['address']; ?></p>
          <?php
          }
          
          // Beach
          if ($search_result['offers'][0]['otpusk_hotel_description']['beach'] != '') {
          ?>
            <p class="itt"><strong class="itt"><?php echo $client->lang['service_beach']; ?>:</strong> <?php echo $search_result['offers'][0]['otpusk_hotel_description']['beach']; ?></p>
          <?php
          }
          
          // Sport
          if ($search_result['offers'][0]['otpusk_hotel_description']['sport'] != '') {
          ?>
            <p class="itt"><strong class="itt"><?php echo $client->lang['hotel_sport']; ?>:</strong> <?php echo $search_result['offers'][0]['otpusk_hotel_description']['sport']; ?></p>
          <?php
          }
          
          // Child
          if ($search_result['offers'][0]['otpusk_hotel_description']['child'] != '') {
          ?>
            <p class="itt"><strong class="itt"><?php echo $client->lang['hotel_for_child']; ?>:</strong> <?php echo $search_result['offers'][0]['otpusk_hotel_description']['child']; ?></p>
          <?php
          }
          
          // Addpaid
          if ($search_result['offers'][0]['otpusk_hotel_description']['addpaid'] != '') {
          ?>
            <p class="itt"><strong class="itt"><?php echo $client->lang['service_status_pay']; ?>:</strong> <?php echo str_replace("\n", ", ", $search_result['offers'][0]['otpusk_hotel_description']['addpaid']); ?></p>
          <?php
          }
          
          // Addfree
          if ($search_result['offers'][0]['otpusk_hotel_description']['addfree'] != '') {
          ?>
            <p class="itt"><strong class="itt"><?php echo $client->lang['service_status_free']; ?>:</strong> <?php echo str_replace("\n", ", ", $search_result['offers'][0]['otpusk_hotel_description']['addfree']); ?></p>
          <?php
          }
          ?>
          <ul class="itt_list-inline">
            <?php
            // Rate
            if ($search_result['offers'][0]['otpusk_hotel_description']['rate'] != '') {
            ?>
              <li><strong class="itt"><?php echo $client->lang['hotel_rating']; ?>:</strong> <?php echo $search_result['offers'][0]['otpusk_hotel_description']['rate']; ?></li>
            <?php
            }
              
            // Email
            if ($search_result['offers'][0]['otpusk_hotel_description']['hotelmail'] != '') {
            ?>
              <li><strong class="itt"><?php echo $client->lang['hotel_mail']; ?>:</strong> <?php echo $search_result['offers'][0]['otpusk_hotel_description']['hotelmail']; ?></li>
            <?php
            }
              
            // Phone
            if ($search_result['offers'][0]['otpusk_hotel_description']['phone'] != '') {
            ?>
             <li><strong class="itt"><?php echo $client->lang['hotel_tel']; ?>:</strong> <?php echo $search_result['offers'][0]['otpusk_hotel_description']['phone']; ?></li>
            <?php
            }
              
            // Fax
            if ($search_result['offers'][0]['otpusk_hotel_description']['fax'] != '') {
            ?>
              <li><strong class="itt"><?php echo $client->lang['hotel_fax']; ?>:</strong> <?php echo $search_result['offers'][0]['otpusk_hotel_description']['fax']; ?></li>
            <?php
            }
              
            // Site
            if ($search_result['offers'][0]['otpusk_hotel_description']['url'] != '') {
            ?>
              <li>
                <strong class="itt"><?php echo $client->lang['hotel_site']; ?>:</strong> 
                <a href="<?php echo $search_result['offers'][0]['otpusk_hotel_description']['url']; ?>" target="_blank">
                  <?php
                  $s = str_replace("http://", '', $search_result['offers'][0]['otpusk_hotel_description']['url']);
                  echo $s = str_replace("/", '', $s);
                  ?>
                </a>
              </li>
            <?php
            }
            ?>
          </ul>
        </section>
      </li>
      <?php
    }
    // End text description hotel
    ?>
    
    
    <li class="itt_tab_head_cont">
      <a href="javascript:void(0);" class="title_sub_tab itt_open_tab"><?php echo $client->lang['services']; ?></a>
      <section class="itt">
        <div class="itt_row">
          <div class="itt_col-xs-4">
            <ul class="itt_hotel_services">
              <li><strong class="itt"><?php echo $client->lang['entertainment_and_sports']; ?>:</strong></li>
              <?php
              if (array_key_exists('otpusk_sportservice_info', $search_result['offers'][0])) {
                // There info about service
                // Set relation 'service_name' => 'language_description'
                $sportservice = array('sauna' => $client->lang['sportservice_sauna'],
                                      'jacuzzi' => $client->lang['sportservice_jacuzzi'],
                                      'massage' => $client->lang['sportservice_massage'],
                                      'spa' => $client->lang['sportservice_spa'],
                                      'billiards' => $client->lang['sportservice_billiards'],
                                      'bowling' => $client->lang['sportservice_bowling'],
                                      'table_tennis' => $client->lang['sportservice_table_tennis'],
                                      'tennis_court' => $client->lang['sportservice_tennis_court'],
                                      'squash' => $client->lang['sportservice_squash'],
                                      'golf' => $client->lang['sportservice_golf'],
                                      'volleyball' => $client->lang['sportservice_volleyball'],
                                      'basketball' => $client->lang['sportservice_basketball'],
                                      'football' => $client->lang['sportservice_football'],
                                      'baseball' => $client->lang['sportservice_baseball'],
                                      'equestrian' => $client->lang['sportservice_equestrian'],
                                      'bikes' => $client->lang['sportservice_bikes'],
                                      'alpinism' => $client->lang['sportservice_alpinism'],
                                      'ski_slopes' => $client->lang['sportservice_ski_slopes'],
                                      'training_skiing' => $client->lang['sportservice_training_skiing'],
                                      'fitness' => $client->lang['sportservice_fitness'],
                                      'aerobics' => $client->lang['sportservice_aerobics'],
                                      'yoga' => $client->lang['sportservice_yoga'],
                                      'dance' => $client->lang['sportservice_dance'],
                                      'aquapark' => $client->lang['sportservice_aquapark'],
                                      'waterslides' => $client->lang['sportservice_waterslides'],
                                      'water_sports' => $client->lang['sportservice_water_sports'],
                                      'diving' => $client->lang['sportservice_diving'],
                                      'surfing' => $client->lang['sportservice_surfing'],
                                      'windsurfing' => $client->lang['sportservice_windsurfing'],
                                      'yachts' => $client->lang['sportservice_yachts'],
                                      'fishing' => $client->lang['sportservice_fishing'],
                                      'discotheque' => $client->lang['sportservice_discotheque'],
                                      'music' => $client->lang['sportservice_music'],
                                      'casino' => $client->lang['sportservice_casino'],
                                      'animation' => $client->lang['sportservice_animation'],
                                      'excursions' => $client->lang['sportservice_excursions'],
                                      'weddings' => $client->lang['sportservice_weddings']
                                     );
                // Show service
                foreach ($search_result['offers'][0]['otpusk_sportservice_info'] as $key => $value) {
                  if (($key != 'id') && ($key != 'otpusk_hotel_id')) {
                    // Service status:
                    // "1","yes","есть"
                    // "2","no","нет"
                    // "3","pay","платно"
                    // "4","free","бесплатно"
                     
                    // Check service status
                    switch ($value) {
                      case 1:
                        // ("1","yes","есть")
                        ?>
                        <li><span title="<?php echo $client->lang['service_status_yes']; ?>"><?php echo $sportservice[$key]; ?></span></li>
                        <?php
                        break;
                      case 3:
                        // ("3","pay","платно")
                        ?>
                        <li><?php echo $sportservice[$key]; ?> <span class="itt_glyphicon itt_glyphicon-usd itt_color_red" title="<?php echo $client->lang['service_status_pay']; ?>"></span></li>
                        <?php
                        break;
                      case 4:
                        // ("4","free","бесплатно")
                        ?>
                        <li><?php echo $sportservice[$key]; ?> <span class="itt_glyphicon itt_glyphicon-ok itt_color_green" title="<?php echo $client->lang['service_status_free']; ?>"></span></li>
                        <?php
                        break;
                      default:
                        // Not use default and not use '2' ("2","no","нет")                        
                        break;
                    }
                  }
                }
              } else {
                // Not info about service
                echo '<li>' . $client->lang['no_information'] . '</li>';
              }
              ?>
            </ul>
          </div>
          
          <div class="itt_col-xs-4 itt_col-xs-push-3">
            <ul class="itt_hotel_services">
              <li><strong class="itt"><?php echo $client->lang['in_hotel']; ?>:</strong></li>
              <?php
              if (array_key_exists('otpusk_hotelservice_info', $search_result['offers'][0])) {
                // There info about service
                // Set relation 'service_name' => 'language_description'
                $hotelservice = array('restaurant' => $client->lang['hotelservice_restaurant'],
                                      'a_la_carte' => $client->lang['hotelservice_a_la_carte'],
                                      'cafe' => $client->lang['hotelservice_cafe'],
                                      'dining' => $client->lang['hotelservice_dining'],
                                      'outdoor_pool' => $client->lang['hotelservice_outdoor_pool'],
                                      'indoor_pool' => $client->lang['hotelservice_indoor_pool'],
                                      'conference' => $client->lang['hotelservice_conference'],
                                      'banqueting' => $client->lang['hotelservice_banqueting'],
                                      'business' => $client->lang['hotelservice_business'],
                                      'secretarial' => $client->lang['hotelservice_secretarial'],
                                      'parking' => $client->lang['hotelservice_parking'],
                                      'rent_car' => $client->lang['hotelservice_rent_car'],
                                      'safe' => $client->lang['hotelservice_safe'],
                                      'wifi' => $client->lang['hotelservice_wifi'],
                                      'internet' => $client->lang['hotelservice_internet'],
                                      'elevator' => $client->lang['hotelservice_elevator'],
                                      'tv' => $client->lang['hotelservice_tv'],
                                      'laundry' => $client->lang['hotelservice_laundry'],
                                      'cleaners' => $client->lang['hotelservice_cleaners'],
                                      'salon' => $client->lang['hotelservice_salon'],
                                      'conversion' => $client->lang['hotelservice_conversion'],
                                      'atm' => $client->lang['hotelservice_atm'],
                                      'doctor' => $client->lang['hotelservice_doctor'],
                                      'invalids' => $client->lang['hotelservice_invalids'],
                                      'pets' => $client->lang['hotelservice_pets'],
                                      'non_smoking' => $client->lang['hotelservice_non_smoking'],
                                      'ski_rental' => $client->lang['hotelservice_ski_rental'],
                                      'ski_storage' => $client->lang['hotelservice_ski_storage'],
                                      'transfer' => $client->lang['hotelservice_transfer'],
                                      'late_check' => $client->lang['hotelservice_late_check'],
                                      'dock' => $client->lang['hotelservice_dock'],
                                      'park' => $client->lang['hotelservice_park'],
                                      'visa' => $client->lang['hotelservice_visa']
                                     );
                // Show service
                foreach ($search_result['offers'][0]['otpusk_hotelservice_info'] as $key => $value) {
                  if (($key != 'id') && ($key != 'otpusk_hotel_id')) {
                    // Service status:
                    // "1","yes","есть"
                    // "2","no","нет"
                    // "3","pay","платно"
                    // "4","free","бесплатно"
                     
                    // Check service status
                    switch ($value) {
                      case 1:
                       // ("1","yes","есть")
                        ?>
                        <li><span title="<?php echo $client->lang['service_status_yes']; ?>"><?php echo $hotelservice[$key]; ?></span></li>
                        <?php
                        break;
                      case 3:
                        // ("3","pay","платно")
                        ?>
                        <li><?php echo $hotelservice[$key]; ?> <span class="itt_glyphicon itt_glyphicon-usd itt_color_red" title="<?php echo $client->lang['service_status_pay']; ?>"></span></li>
                        <?php
                        break;
                      case 4:
                        // ("4","free","бесплатно")
                        ?>
                        <li><?php echo $hotelservice[$key]; ?> <span class="itt_glyphicon itt_glyphicon-ok itt_color_green" title="<?php echo $client->lang['service_status_free']; ?>"></span></li>
                        <?php
                        break;
                      default:
                        // Not use default and not use '2' ("2","no","нет")
                        break;
                    }
                  }
                }
              } else {
                // Not info about service
                echo '<li>' . $client->lang['no_information'] . '</li>';
              }
              ?>
            </ul>
          </div>
        </div>
        <div class="itt_row">
          <div class="itt_col-xs-4">
            <ul class="itt_hotel_services">
              <li><strong class="itt"><?php echo $client->lang['service_children']; ?>:</strong></li>
              <?php
              if (array_key_exists('otpusk_childservice_info', $search_result['offers'][0])) {
                // There info about service
                // Set relation 'service_name' => 'language_description'
                $childservice = array('pool' => $client->lang['childservice_pool'],
                                      'playground' => $client->lang['childservice_playground'],
                                      'club' => $client->lang['childservice_club'],
                                      'menu' => $client->lang['childservice_menu'],
                                      'highchairs' => $client->lang['childservice_highchairs'],
                                      'cot' => $client->lang['childservice_cot'],
                                      'nurse' => $client->lang['childservice_nurse']
                                     );
                
                // Show service
                foreach ($search_result['offers'][0]['otpusk_childservice_info'] as $key => $value) {
                  if (($key != 'id') && ($key != 'otpusk_hotel_id')) {
                    // Service status:
                    // "1","yes","есть"
                    // "2","no","нет"
                    // "3","pay","платно"
                    // "4","free","бесплатно"
                     
                    // Check service status
                    switch ($value) {
                      case 1:
                        // ("1","yes","есть")
                        ?>
                        <li><span title="<?php echo $client->lang['service_status_yes']; ?>"><?php echo $childservice[$key]; ?></span></li>
                        <?php
                        break;
                      case 3:
                        // ("3","pay","платно")
                        ?>
                        <li><?php echo $childservice[$key]; ?> <span class="itt_glyphicon itt_glyphicon-usd itt_color_red" title="<?php echo $client->lang['service_status_pay']; ?>"></span></li>
                        <?php
                        break;
                      case 4:
                        // ("4","free","бесплатно")
                        ?>
                        <li><?php echo $childservice[$key]; ?> <span class="itt_glyphicon itt_glyphicon-ok itt_color_green" title="<?php echo $client->lang['service_status_free']; ?>"></span></li>
                        <?php
                        break;
                      default:
                        // Not use default and not use '2' ("2","no","нет")
                        break;
                    }
                  }
                }
              } else {
                // Not info about service
                echo '<li>' . $client->lang['no_information'] . '</li>';
              }
              ?>
            </ul>
          </div>
          <div class="itt_col-xs-4 itt_col-xs-push-3">
            <ul class="itt_hotel_services">
              <li><strong class="itt"><?php echo $client->lang['service_beach']; ?>:</strong></li>
              <?php
              if (array_key_exists('otpusk_beach_info', $search_result['offers'][0])) {
                // There info about service
                // Set relation 'service_name' => 'language_description'
                $beachservice = array('own' => $client->lang['beachservice_own'],
                                      'town' => $client->lang['beachservice_town'],
                                      'sandy' => $client->lang['beachservice_sandy'],
                                      'sandy_pebble' => $client->lang['beachservice_sandy_pebble'],
                                      'pebble' => $client->lang['beachservice_pebble'],
                                      'pontoon' => $client->lang['beachservice_pontoon'],
                                      'chairs' => $client->lang['beachservice_chairs'],
                                      'mats' => $client->lang['beachservice_mats'],
                                      'towels' => $client->lang['beachservice_towels'],
                                      'umbrella' => $client->lang['beachservice_umbrella']
                                     );
                
                // Show service
                foreach ($search_result['offers'][0]['otpusk_beach_info'] as $key => $value) {
                  if (($key != 'id') && ($key != 'otpusk_hotel_id')) {
                    // Service status:
                    // "1","yes","есть"
                    // "2","no","нет"
                    // "3","pay","платно"
                    // "4","free","бесплатно"
                     
                    // Check service status
                    switch ($value) {
                      case 1:
                        // ("1","yes","есть")
                        ?>
                        <li><span title="<?php echo $client->lang['service_status_yes']; ?>"><?php echo $beachservice[$key]; ?></span></li>
                        <?php
                        break;
                      case 3:
                        // ("3","pay","платно")
                        ?>
                        <li><?php echo $beachservice[$key]; ?> <span class="itt_glyphicon itt_glyphicon-usd itt_color_red" title="<?php echo $client->lang['service_status_pay']; ?>"></span></li>
                        <?php
                        break;
                      case 4:
                        // ("4","free","бесплатно")
                        ?>
                        <li><?php echo $beachservice[$key]; ?> <span class="itt_glyphicon itt_glyphicon-ok itt_color_green" title="<?php echo $client->lang['service_status_free']; ?>"></span></li>
                        <?php
                        break;
                      default:
                        // Not use default and not use '2' ("2","no","нет")
                        break;
                    }
                  }
                }
              } else {
                // Not info about service
                echo '<li>' . $client->lang['no_information'] . '</li>';
              }
              ?>
            </ul>
          </div>
        </div>
      </section>
    </li>
    
    
    <?php
    if (!$flag_not_google_map) {
      // Isset map
    ?>
      <li class="itt_tab_head_cont itt_tab_map">
        <a href="javascript:void(0);" class="title_sub_tab itt_open_tab" onclick="setTimeout(function(){initialize_google_map_custom_view_tour(12, 'map_canvas_<?php echo $id_for_html_google_map_big; ?>', <?php echo $google_map_lat; ?>, <?php echo $google_map_lng; ?>);}, 1400);"><?php echo $client->lang['map']; ?></a>
        <section class="itt">
          <div><?php echo $client->lang['map_loading']; ?></div>
          <div class="itt_map_load">
            <div id="map_canvas_<?php echo $id_for_html_google_map_big; ?>" class="itt_map_big_preview"></div>
          </div>
        </section>
      </li>
    <?php
    }
    ?>
  </ul>
  
  
  <?php
  // Show form buy all
  // форма в колторой 3-ри сразу: заказать тур / заказать тур с указанием паспортных данных / тур сразу оплатить
  $params_buy_all_form = array( 'form_id' => 'package_order_form_buy_all' 
                              , 'action' => 'package_tour_order_submit_buy_all'
                              , 'input_price' => $price
                              , 'input_currency_id' => $currency_id
                              , 'tour_id' => $search_result['offers'][0]['id']
                              , 'sharding_rule_id' => $search_result['offers'][0]['sharding_rule_id']
                              , 'date_till' => $search_result['offers'][0]['locations'][0]['date_till']
                              , 'offer_type_id' => $search_result['offers'][0]['type_id'] ? $search_result['offers'][0]['type_id'] : 1
                              , 'offer_kind_id' => $search_result['offers'][0]['kind_id'] ? $search_result['offers'][0]['kind_id'] : 1
                              , 'offer_country_id' => $search_result['offers'][0]['locations'][0]['country_id'] ? $search_result['offers'][0]['locations'][0]['country_id'] : 1
                              , 'offer_region_id' => $search_result['offers'][0]['locations'][0]['region_id'] ? $search_result['offers'][0]['locations'][0]['region_id'] : 1
                              , 'offer_accomodation' => $search_result['offers'][0]['locations'][0]['accomodation_name'] ? $search_result['offers'][0]['locations'][0]['accomodation_name'] : 1
                              , 'offer_hotel_id' => $search_result['offers'][0]['locations'][0]['hotel_id'] ? $search_result['offers'][0]['locations'][0]['hotel_id'] : 1
                              , 'offer_room_type_id' => $search_result['offers'][0]['room_type_id'] ? $search_result['offers'][0]['room_type_id'] : 1
                              , 'offer_room_type_kn_id' => $search_result['offers'][0]['room_type_kn_id'] ? $search_result['offers'][0]['room_type_kn_id'] : 1
                              , 'offer_meal_type_id' => $search_result['offers'][0]['meal_type_id'] ? $search_result['offers'][0]['meal_type_id'] : 1
                              , 'offer_meal_type_kn_id' => $search_result['offers'][0]['meal_type_kn_id'] ? $search_result['offers'][0]['meal_type_kn_id'] : 1
                              , 'offer_operator_id' => $search_result['offers'][0]['operator_id'] ? $search_result['offers'][0]['operator_id'] : 1
                              , 'offer_spo_code' => $search_result['offers'][0]['spo_code'] ? $search_result['offers'][0]['spo_code'] : 1
                              , 'offer_duration' => $search_result['offers'][0]['duration'] ? $search_result['offers'][0]['duration'] : 1
                              , 'offer_date_from' => $search_result['offers'][0]['date_from'] ? $search_result['offers'][0]['date_from'] : 1
                              , 'offer_from_city_id' => $search_result['offers'][0]['from_city_id'] ? $search_result['offers'][0]['from_city_id'] : 1
                              , 'offer_currency_id' => $search_result['offers'][0]['currency_id'] ? $search_result['offers'][0]['currency_id'] : 1
                              , 'offer_price_grn' => $currencies['2']['price']
                              , 'offer_from_plane_id' => $search_result['offers'][0]['transports'][0]['flights'][0]['plane_id'] ? $search_result['offers'][0]['transports'][0]['flights'][0]['plane_id'] : 1
                              , 'offer_to_plane_id' => $search_result['offers'][0]['transports'][1]['flights'][0]['plane_id'] ? $search_result['offers'][0]['transports'][1]['flights'][0]['plane_id'] : 1
                              , 'offer_spo_comment' => $search_result['offers'][0]['spo']['comment'] ? $search_result['offers'][0]['spo']['comment'] : 1
                              , 'offer_tour_price_id' => $search_result['offers'][0]['id']
                              , 'offer_flight_from_id' => $search_result['offers'][0]['transports'][0]['flights'][0]['id'] ? $search_result['offers'][0]['transports'][0]['flights'][0]['id'] : 1
                              , 'offer_flight_to_id' => $search_result['offers'][0]['transports'][1]['flights'][0]['id'] ? $search_result['offers'][0]['transports'][1]['flights'][0]['id'] : 1
                              , 'offers' => $search_result['offers']
                              // Add for captcha
                              , 'offers_captcha_url' => $ittour_url . $client->itt_captcha->url()
                              , 'offers_captcha_value_code' => $client->itt_captcha->value_code
                              // Add dinamic generation adult and child
                              , 'offers_adult_amount' => $search_result['offers'][0]['adult_amount'] ? $search_result['offers'][0]['adult_amount'] : 1
                              , 'offers_child_amount' => $search_result['offers'][0]['child_amount'] ? $search_result['offers'][0]['child_amount'] : 0
                              );
  html_form_buy_all($client, $params_buy_all_form);
  ?>
  
  <!-- End new html 09.04.2015 -->
</div>
  
  <?php
  return ob_get_clean();
}

/**
 * function set time in road
 * @param string
 * @return string 
 */
function get_duration_mod2($duration) {
  if (strpos($duration, ':') > 0)
    return $duration;
  return floor($flights['duration_min'] / 60) . ':' . ($flights['duration_min'] - floor($flights['duration_min'] / 60) * 60);
}

/**
 * function replace in date english month to russian month
 * @param string $date => example: '2014-09-20'
 * @param object $client => use language constant
 * @return string $new_date => example: '07 августа 2014'
 */
function replace_in_date_month_en_to_month_ru($date, &$client) {
  //$new_date = date("d F Y", strtotime($date));// Example: '07 September 2014' // Old long version => work
  $new_date = date("d.m.Y", strtotime($date));// Example: '07 September 2014' // New small version => work
  
  // Replace 'en' to 'ru'
  $new_date = str_replace('January', $client->lang['january'], $new_date);
  $new_date = str_replace('February', $client->lang['february'], $new_date);
  $new_date = str_replace('March', $client->lang['march'], $new_date);
  $new_date = str_replace('April', $client->lang['april'], $new_date);
  $new_date = str_replace('May', $client->lang['may'], $new_date);
  $new_date = str_replace('June', $client->lang['june'], $new_date);
  $new_date = str_replace('July', $client->lang['july'], $new_date);
  $new_date = str_replace('August', $client->lang['august'], $new_date);
  $new_date = str_replace('September', $client->lang['september'], $new_date);
  $new_date = str_replace('October', $client->lang['october'], $new_date);
  $new_date = str_replace('November', $client->lang['november'], $new_date);
  $new_date = str_replace('December', $client->lang['december'], $new_date);
  
  return $new_date;
}

/**
 * function generate nationality select and return html
 * 
 * @param array $offers => array with 'nationality' and other info.
 * @param string $select_name => use for set html property 'name' in select
 * @param object $client => use language
 * @return string
 */
function generate_nationality_select_html($offers, $select_name, $client) {
  if (array_key_exists('nationality', $offers)) {
    // Generate dynamic select
    if(count($offers['nationality']>0)) {
     $html = '<select class="itt_border_rd4" name="' . $select_name . '">';
     foreach ($offers['nationality'] as $key => $value) {
       if ($value['name'] == 'true') {
         // Selected
         $html .= '<option selected="selected" value="' . $value['id']. '">' . $value['short_name'] . '</option>';
       } else {
         $html .= '<option value="' . $value['id']. '">' . $value['short_name'] . '</option>';
       }
     }
     $html .= '</select>';
    }
  } else {
    // Generate static select
    $html = '<select class="itt_border_rd4" name="' . $select_name . '">
               <option selected="selected" value="38">' . $client->lang['ukraine_country_name'] . '</option>
               <option value="39">' . $client->lang['russia_country_name'] . '</option>
             </select>';
  }
  return $html;
}

/**
 * function generate gender select and return html
 * @param string $select_name
 * @param object $client
 * @return string
 */
function generate_gender_select_html($select_name, $client) {
  $html = '<select class="itt_border_rd4" name="' . $select_name . '">
             <option selected="selected" value="26">' . $client->lang['text_mr'] .'</option>
             <option value="27">' . $client->lang['text_ms'] . '</option>
           </select>';
  return $html;
}

/**
 * function set relation 'country_id' and position in sprite
 * 
 * @param void 
 * @return array
 */
function set_relation_country_id_position_sprite_array() {
  // Set relation 'country_id' => position x,y for flag in sprite
  $relation_country_id_position_sprite_array = array(
        // Example: country_id => array('x' => '-531', 'y' => '-241'),
        // 392 => array('x' => '-', 'y' => '-'),//Абхазия // нет на картинке
        29 => array('x' => '-11', 'y' => '-171'),//Австралия
        30 => array('x' => '-11', 'y' => '-195'),//Австрия
        31 => array('x' => '-11', 'y' => '-218'),//Азербайджан
        23 => array('x' => '-12', 'y' => '-32'),//Албания
        362 => array('x' => '-12', 'y' => '-56'),//Алжир
        //2240 => array('x' => '-', 'y' => '-'),//"Американское Самоа" // нет на картинке
        //25 => array('x' => '-', 'y' => '-'),//Ангола // нет на картинке
        24 => array('x' => '-12', 'y' => '-80'),//Андорра
        //3713 => array('x' => '-', 'y' => '-'),//Антарктида // нет на картинке
        1983 => array('x' => '-12', 'y' => '-102'),//"Антигуа и Барбуда"
        26 => array('x' => '-12', 'y' => '-125'),//Аргентина
        27 => array('x' => '-12', 'y' => '-148'),//Армения
        //2313 => array('x' => '-', 'y' => '-'),//Аруба // нет на картинке
        22 => array('x' => '-12', 'y' => '-10'),//Афганистан
        2037 => array('x' => '-11', 'y' => '-241'),//"Багамские Острова"
        33 => array('x' => '-49', 'y' => '-10'),//Бангладеш
        1084 => array('x' => '-49', 'y' => '-33'),//Барбадос
        32 => array('x' => '-12', 'y' => '-264'),//Бахрейн
        406 => array('x' => '-49', 'y' => '-57'),//Беларусь
        2244 => array('x' => '-49', 'y' => '-102'),//Белиз
        36 => array('x' => '-49', 'y' => '-79'),//Бельгия
        834 => array('x' => '-49', 'y' => '-126'),//Бенин
        //3651 => array('x' => '-', 'y' => '-'),//"Бермудские острова" // нет на картинке
        41 => array('x' => '-384', 'y' => '-10'),//Бирма
        39 => array('x' => '-87', 'y' => '-10'),//Болгария
        37 => array('x' => '-197', 'y' => '-102'),//Боливия
        38 => array('x' => '-49', 'y' => '-195'),//"Босния и Герцеговина"
        452 => array('x' => '-49', 'y' => '-218'),//Ботсвана
        18 => array('x' => '-49', 'y' => '-241'),//Бразилия
        2247 => array('x' => '-49', 'y' => '-265'),//Бруней
        1620 => array('x' => '-49', 'y' => '-265'),//Бруней-Даруссалам
        2248 => array('x' => '-86', 'y' => '-33'),//"Буркина Фасо"
        2250 => array('x' => '-86', 'y' => '-56'),//Бурунди
        2245 => array('x' => '-49', 'y' => '-149'),//Бутан
        2309 => array('x' => '-569', 'y' => '-195'),//Вануату
        63 => array('x' => '-570', 'y' => '-102'),//Великобритания
        68 => array('x' => '-235', 'y' => '-33'),//Венгрия
        133 => array('x' => '-570', 'y' => '-242'),//Венесуэла
        134 => array('x' => '-569', 'y' => '-264'),//Вьетнам
        2265 => array('x' => '-197', 'y' => '-10'),//Габон
        67 => array('x' => '-198', 'y' => '-265'),//Гаити
        2271 => array('x' => '-197', 'y' => '-240'),//Гайана
        2266 => array('x' => '-197', 'y' => '-33'),//Гамбия
        311 => array('x' => '-197', 'y' => '-102'),//Гана
        64 => array('x' => '-197', 'y' => '-172'),//Гватемала
        //2270 => array('x' => '-', 'y' => '-'),//Гвинея // нет на картинке
        65 => array('x' => '-197', 'y' => '-218'),//Гвинея-Бисау
        61 => array('x' => '-197', 'y' => '-79'),//Германия
        //62 => array('x' => '-', 'y' => '-'),//Гибралтар // нет на картинке
        835 => array('x' => '-235', 'y' => '-9'),//Гондурас
        454 => array('x' => '-272', 'y' => '-195'),//Гонконг
        2312 => array('x' => '-198', 'y' => '-149'),//Гренада
        2269 => array('x' => '-122', 'y' => '-217'),//Гренландия
        372 => array('x' => '-197', 'y' => '-125'),//Греция
        60 => array('x' => '-198', 'y' => '-56'),//Грузия
        54 => array('x' => '-122', 'y' => '-217'),//Дания
        2258 => array('x' => '-123', 'y' => '-240'),//Джибути
        2794 => array('x' => '-123', 'y' => '-265'),//Доминика
        321 => array('x' => '-160', 'y' => '-9'),//Доминикана
        //2391 => array('x' => '-', 'y' => '-'),//Европа // нет на картинке
        338 => array('x' => '-161', 'y' => '-79'),//Египет
        1532 => array('x' => '-607', 'y' => '-32'),//Замбия
        //2488 => array('x' => '-', 'y' => '-'),//"Западно-Карибские острова" // нет на картинке
        3704 => array('x' => '-457', 'y' => '-78'),//"Западное Самоа"
        450 => array('x' => '-607', 'y' => '-55'),//Зимбабве
        75 => array('x' => '-235', 'y' => '-195'),//Израиль
        69 => array('x' => '-235', 'y' => '-79'),//Индия
        330 => array('x' => '-235', 'y' => '-102'),//Индонезия
        323 => array('x' => '-272', 'y' => '-9'),//Иордания
        72 => array('x' => '-235', 'y' => '-125'),//Ирак
        71 => array('x' => '-235', 'y' => '-148'),//Иран
        73 => array('x' => '-235', 'y' => '-172'),//Ирландия
        74 => array('x' => '-235', 'y' => '-56'),//Исландия
        320 => array('x' => '-495', 'y' => '-149'),//Испания
        76 => array('x' => '-234', 'y' => '-217'),//Италия
        //3486 => array('x' => '-', 'y' => '-'),//"Италия + Франция" // нет на картинке
        135 => array('x' => '-606', 'y' => '-9'),//Йемен
        44 => array('x' => '-85', 'y' => '-150'),//Кабо-Верде
        79 => array('x' => '-271', 'y' => '-32'),//Казахстан
        //1070 => array('x' => '-', 'y' => '-'),//Камбоджа // нет на картинке
        42 => array('x' => '-86', 'y' => '-103'),//Камерун
        43 => array('x' => '-86', 'y' => '-125'),//Канада
        1474 => array('x' => '-420', 'y' => '-194'),//Катар
        //3672 => array('x' => '-', 'y' => '-'),//Катманду // нет на картинке
        80 => array('x' => '-272', 'y' => '-56'),//Кения
        //376 => array('x' => '-', 'y' => '-'),//Кипр // нет на картинке
        81 => array('x' => '-273', 'y' => '-195'),//Киргизия
        2273 => array('x' => '-273', 'y' => '-78'),//Кирибати
        46 => array('x' => '-85', 'y' => '-240'),//Китай
        47 => array('x' => '-85', 'y' => '-266'),//Колумбия
        2253 => array('x' => '-122', 'y' => '-9'),//"Коморские Острова"
        //48 => array('x' => '-', 'y' => '-'),//Конго // нет на картинке
        //2351 => array('x' => '-', 'y' => '-'),//Корея // нет на картинке
        //310 => array('x' => '-', 'y' => '-'),//Косово // нет на картинке
        49 => array('x' => '-122', 'y' => '-79'),//Коста-Рика
        50 => array('x' => '-123', 'y' => '-103'),//Кот-д'Ивуар
        //1498 => array('x' => '-', 'y' => '-'),//Круиз // нет на картинке
        //2461 => array('x' => '-', 'y' => '-'),//Круизы // нет на картинке
        9 => array('x' => '-123', 'y' => '-149'),//Куба
        //3325 => array('x' => '-', 'y' => '-'),//"Куба + Мексика" // нет на картинке
        82 => array('x' => '-272', 'y' => '-172'),//Кувейт 
        //3649 => array('x' => '-', 'y' => '-'),//Кюрасао // нет на картинке
        //1202 => array('x' => '-', 'y' => '-'),//Лаос // нет на картинке
        7 => array('x' => '-11', 'y' => '-195'),//Латвия
        //2590 => array('x' => '-', 'y' => '-'),//"Латинская Америка" // нет на картинке
        2277 => array('x' => '-309', 'y' => '-10'),//Лесото
        2278 => array('x' => '-309', 'y' => '-32'),//Либерия
        83 => array('x' => '-272', 'y' => '-264'),//Ливан
        84 => array('x' => '-309', 'y' => '-56'),//Ливия
        10 => array('x' => '-309', 'y' => '-102'),//Литва
        448 => array('x' => '-309', 'y' => '-78'),//Лихтенштейн
        //3492 => array('x' => '-', 'y' => '-'),//Любая // нет на картинке
        //3666 => array('x' => '-', 'y' => '-'),//"Любая Страна" //  нет на картинке
        86 => array('x' => '-309', 'y' => '-125'),//Люксембург
        90 => array('x' => '-346', 'y' => '-79'),//Маврикий
        2283 => array('x' => '-346', 'y' => '-56'),//Мавритания
        1712 => array('x' => '-309', 'y' => '-171'),//Мадагаскар
        2284 => array('x' => '-161', 'y' => '-264'),//Майотта
        87 => array('x' => '-309', 'y' => '-148'),//Македония
        2281 => array('x' => '-309', 'y' => '-194'),//Малави
        88 => array('x' => '-309', 'y' => '-217'),//Малайзия
        2282 => array('x' => '-309', 'y' => '-265'),//Мали
        //324 => array('x' => '-', 'y' => '-'),//Мальдивы //  нет на картинке
        414 => array('x' => '-346', 'y' => '-9'),//Мальта
        97 => array('x' => '-346', 'y' => '-242'),//Марокко
        3466 => array('x' => '-346', 'y' => '-32'),//"Маршалловы Острова"
        91 => array('x' => '-346', 'y' => '-102'),//Мексика
        1530 => array('x' => '-346', 'y' => '-264'),//Мозамбик
        93 => array('x' => '-346', 'y' => '-147'),//Молдова
        446 => array('x' => '-346', 'y' => '-170'),//Монако
        95 => array('x' => '-346', 'y' => '-194'),//Монголия
        2646 => array('x' => '-383', 'y' => '-10'),//Мьянма
        1842 => array('x' => '-383', 'y' => '-32'),//Намибия
        99 => array('x' => '-383', 'y' => '-80'),//Непал
        2288 => array('x' => '-383', 'y' => '-172'),//Нигер
        102 => array('x' => '-383', 'y' => '-194'),//Нигерия
        98 => array('x' => '-383', 'y' => '-102'),//Нидерланды
        101 => array('x' => '-383', 'y' => '-148'),//Никарагуа
        100 => array('x' => '-383', 'y' => '-125'),//"Новая Зеландия"
        //1762 => array('x' => '-', 'y' => '-'),//"Новая Каледония" //  нет на картинке
        103 => array('x' => '-383', 'y' => '-218'),//Норвегия
        16 => array('x' => '-569', 'y' => '-79'),//ОАЭ
        2219 => array('x' => '-383', 'y' => '-241'),//Оман
        2780 => array('x' => '-383', 'y' => '-241'),//Оман
        104 => array('x' => '-383', 'y' => '-265'),//Пакистан
        2655 => array('x' => '-421', 'y' => '-10'),//Палау
        105 => array('x' => '-271', 'y' => '-10'),//Палестина
        2208 => array('x' => '-420', 'y' => '-33'),//Панама
        2289 => array('x' => '-420', 'y' => '-56'),//"Папуа-Новая Гвинея"
        106 => array('x' => '-421', 'y' => '-79'),//Парагвай
        107 => array('x' => '-421', 'y' => '-102'),//Перу
        109 => array('x' => '-421', 'y' => '-149'),//Польша
        110 => array('x' => '-421', 'y' => '-171'),//Португалия
        //3493 => array('x' => '-', 'y' => '-'),//"Пустое Значение" //  нет на картинке
        //2292 => array('x' => '-', 'y' => '-'),//Пуэрто-Рико // нет на картинке
        15 => array('x' => '-421', 'y' => '-242'),//Россия
        2293 => array('x' => '-421', 'y' => '-264'),//Руанда
        112 => array('x' => '-421', 'y' => '-217'),//Румыния
        2234 => array('x' => '-160', 'y' => '-102'),//Сальвадор
        2297 => array('x' => '-459', 'y' => '-78'),//Самоа
        444 => array('x' => '-485', 'y' => '-102'),//Сан-Марино
        1865 => array('x' => '-459', 'y' => '-149'),//"Саудовская Аравия"
        2303 => array('x' => '-496', 'y' => '-241'),//Свазиленд
        //2358 => array('x' => '-', 'y' => '-'),//"Северная Америка" // нет на картинке
        //2274 => array('x' => '-', 'y' => '-'),//"Северная Корея" // нет на картинке
        //2477 => array('x' => '-', 'y' => '-'),//"Северные Марианские Острова"  // нет на картинке
        1016 => array('x' => '-456', 'y' => '-218'),//"Сейшельские о."
        //2296 => array('x' => '-', 'y' => '-'),//"Сен-Пьер и Микелон" // нет на картинке
        114 => array('x' => '-458', 'y' => '-172'),//Сенегал 
        //3461 => array('x' => '-', 'y' => '-'),//"Сент Бартс" // нет на картинке
        2294 => array('x' => '-458', 'y' => '-9'),//"Сент-Китс и Невис"
        2295 => array('x' => '-458', 'y' => '-34'),//Сент-Люсия
        115 => array('x' => '-458', 'y' => '-195'),//Сербия
        116 => array('x' => '-458', 'y' => '-263'),//Сингапур
        3650 => array('x' => '-420', 'y' => '-125'),//"Синт Мартен"
        123 => array('x' => '-533', 'y' => '-33'),//Сирия
        117 => array('x' => '-494', 'y' => '-10'),//Словакия
        118 => array('x' => '-494', 'y' => '-33'),//Словения
        2301 => array('x' => '-494', 'y' => '-56'),//"Соломоновы острова"
        2237 => array('x' => '-495', 'y' => '-79'),//Сомали
        120 => array('x' => '-495', 'y' => '-195'),//Судан
        2302 => array('x' => '-195', 'y' => '-218'),//Суринам
        131 => array('x' => '-308', 'y' => '-33'),//США
        2300 => array('x' => '-457', 'y' => '-241'),//Сьерра-Леоне
        125 => array('x' => '-532', 'y' => '-79'),//Таджикистан
        332 => array('x' => '-532', 'y' => '-125'),//Таиланд
        124 => array('x' => '-532', 'y' => '-55'),//Тайвань
        1082 => array('x' => '-532', 'y' => '-102'),//Танзания
        //2352 => array('x' => '-', 'y' => '-'),//Тибет  // нет на картинке
        2304 => array('x' => '-160', 'y' => '-33'),//Тимор-Лесте
        2305 => array('x' => '-532', 'y' => '-147'),//Того
        //3567 => array('x' => '-', 'y' => '-'),//Трансфер // нет на картинке
        2306 => array('x' => '-532', 'y' => '-193'),//"Тринидад и Тобаго"
        378 => array('x' => '-532', 'y' => '-218'),//Тунис
        128 => array('x' => '-532', 'y' => '-265'),//Туркменистан
        318 => array('x' => '-531', 'y' => '-241'),//Турция
        460 => array('x' => '-569', 'y' => '-33'),//Уганда
        132 => array('x' => '-569', 'y' => '-171'),//Узбекистан
        6 => array('x' => '-569', 'y' => '-55'),//Украина
        1718 => array('x' => '-569', 'y' => '-147'),//Уругвай
        1760 => array('x' => '-569', 'y' => '-10'),//Фиджи
        108 => array('x' => '-419', 'y' => '-125'),//Филиппины
        354 => array('x' => '-159', 'y' => '-240'),//Финляндия
        420 => array('x' => '-159', 'y' => '-264'),//Франция
        //2263 => array('x' => '-', 'y' => '-'),//"Французская Гвиана" // нет на картинке
        //1080 => array('x' => '-', 'y' => '-'),//"Французская Полинезия" // нет на картинке
        442 => array('x' => '-124', 'y' => '-124'),//Хорватия
        2252 => array('x' => '-86', 'y' => '-170'),//"Центрально-Африканская Республика"
        126 => array('x' => '-86', 'y' => '-194'),//Чад
        434 => array('x' => '-347', 'y' => '-218'),//Черногория
        53 => array('x' => '-124', 'y' => '-195'),//Чехия
        45 => array('x' => '-86', 'y' => '-217'),//Чили
        122 => array('x' => '-533', 'y' => '-10'),//Швейцария
        121 => array('x' => '-494', 'y' => '-264'),//Швеция
        //848 => array('x' => '-', 'y' => '-'),//Шотландия // нет на картинке
        334 => array('x' => '-496', 'y' => '-172'),//"Шри Ланка"
        //3667 => array('x' => '-', 'y' => '-'),//"Шри Ланка + ОАЭ" // нет на картинке
        55 => array('x' => '-161', 'y' => '-56'),//Эквадор
        430 => array('x' => '-161', 'y' => '-125'),//"Экваториальная Гвинея"
        //1240 => array('x' => '-', 'y' => '-'),//"Эмилия Романья" // нет на картинке
        2261 => array('x' => '-161', 'y' => '-148'),//Эритрея
        56 => array('x' => '-161', 'y' => '-172'),//Эстония
        57 => array('x' => '-161', 'y' => '-195'),//Эфиопия
        111 => array('x' => '-495', 'y' => '-102'),//ЮАР
        //2211 => array('x' => '-', 'y' => '-'),//Югославия // нет на картинке
        119 => array('x' => '-272', 'y' => '-125'),//"Южная Корея"
        1086 => array('x' => '-234', 'y' => '-240'),//Ямайка
        77 => array('x' => '-234', 'y' => '-264'),//Япония
  );
  return $relation_country_id_position_sprite_array;
}
?>
