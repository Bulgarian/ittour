<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */
function get_package_search_result_hotel_table_new_mod($client, &$filter, &$search_result, $is_cart = false) {
    
  // Set url ittour
  $ittour_url = 'http://www.ittour.com.ua/';
  
  // Set current currency in module
  $current_currency_in_mod = 2;
  if (array_key_exists('switch_price', $_GET)) {
      if (trim($_GET['switch_price']) == 'USD') $current_currency_in_mod = 1;
      if (trim($_GET['switch_price']) == 'UAH') $current_currency_in_mod = 2;
      if (trim($_GET['switch_price']) == 'EUR') $current_currency_in_mod = 10;
  }  
  
  ob_start();
  
  // Check traffic 'stop search text'
  if(safe($search_result, 'stop_search_text') || safe($search_result, 'stop_search_text_ru')) {
    ?>
    <script type="text/javascript">
      jQueryMod2('.itt_issuing_search_tabs li.itt_issuing_search_tabs_head_cont section').css('background', '#FFFFFF');
    </script>
    <div style="color: #ff0000 !important; font-size: 14px !important; font-weight: normal !important; margin: 15px auto !important; text-align: center !important;">
      <?php echo $client->lang['stop_search_text_service_is_unavailable']; ?>
    </div>
    <?php
    return ob_get_clean();
  }  
  
  // Set relation 'country_id' => position x,y for flag in sprite
  $relation_country_id_position_sprite_array = set_relation_country_id_position_sprite_array();
  ?>

  <!-- Begin add google.map v2 -->
  <script type="text/javascript">
  // Set all big photo for hotel
  var all_big_photo_for_hotel_obj = {};
  
  // Set var for thickbox
  var tb_pathToImage = "http://www.ittour.com.ua/classes/handlers/ittour_external_modules/ittour_modules/images/ajax_loader.gif";
  
  // DOM ready => load google map
  jQueryMod2(document).ready(function(){
      // Init thickbox
      tb_init('a.thickbox, area.thickbox, input.thickbox');//pass where to apply thickbox
      
      // Handler click to .thickbox => open map
      jQueryMod2('.itt_issuing_search_city_and_map a.thickbox').click(function(){
          // Get coordinats
          var lat = jQueryMod2(this).attr('lat');
          var lng = jQueryMod2(this).attr('lng');
                    
          // Check coordinats
          if (typeof lat == 'undefined') return;
          if (typeof lng == 'undefined') return;
          
          setTimeout(function(){
              initialize_google_map_custom_view_tour(10, 'map_canvas_view_in_search_result', lat, lng);
              
              // Hide text
              jQueryMod2('#TB_ajaxContent .text_load_map').hide();
          }, 1700);
          
          // Add text 'load map'
          jQueryMod2('#my-content-id').prepend('<div class="text_load_map">Загрузка карты...</div>');
      });
  });
  </script>
  <!-- End add google.map v2 -->
  
  
  <input type="hidden" id="current_currency_in_mod" name="current_currency_in_mod" value="<?php echo $current_currency_in_mod;?>">
  
  
  <div class="itt_search_result_header_content">
    <!-- Информация по выдачи и переключатель валют START -->
    <ul class="itt_issuing_search_results_information">
      <?php
      // New set count tours
      $count_all_package_tours = 0;
      foreach($search_result['offers'] as $key => $offer) {
        if (array_key_exists('date_from', $offer['data_grop_new_mod_result_package'])) {
          for ($i=0; $i<count($offer['data_grop_new_mod_result_package']['date_from']); $i++) {
            $count_all_package_tours++;
          }
        } else {
          $count_all_package_tours++;
        }
      }

      // New show count tours
      if ($count_all_package_tours > 0) {
        //$str = decl_of_num($count_all_package_tours, array($client->lang['tour_variant1'], $client->lang['tour_variant2'], $client->lang['tour_variant5']));// width bug
        $str = decl_of_num($search_result['pager']['items_total_amount'], array($client->lang['tour_variant1'], $client->lang['tour_variant2'], $client->lang['tour_variant5']));
        ?>
        <li><?php echo $client->lang['for_the_parameters_found']; ?> <span><?php echo $client->lang['more_than']; ?> <?php echo $search_result['pager']['items_total_amount'] . ' ' . $str; ?></span></li>
        <?php
      } else {
        // Туров нет
        echo '<li>' . $client->lang['tour_not_found'] . '</li>';
      }
      ?>
      
      <?php
      // Check count result search > 0
      if ($count_all_package_tours) {
      ?>
      <li>
        <ul class="itt_flag_plus_country_position">
          <li>
            <?php
            // Check isset flag => show flag
            // Вывод флага страны
            if (array_key_exists($search_result['offers'][0]['locations'][0]['country_id'], $relation_country_id_position_sprite_array)) {
            ?>
            <span class="itt_flag_counter itt_border_rd4" style="background: url(<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/flags-web3.png) no-repeat <?php echo $relation_country_id_position_sprite_array[ $search_result['offers'][0]['locations'][0]['country_id'] ]['x']; ?>px <?php echo $relation_country_id_position_sprite_array[ $search_result['offers'][0]['locations'][0]['country_id'] ]['y']; ?>px;"></span>
            <?php
            }
            ?>
          </li>
          <li>
            <?php
            // Вывод названия страны
            if($countries = safe($filter, 'countries')) {
              foreach($countries as $key => $country)
                echo ($key?", {$country}":$country);
            }
            ?>
          </li>
        </ul>
      </li>
      <?php
      }
      ?>
      
    </ul>
    <ul class="itt_issuing_search_currency_swith">
      <li><span><?php echo $client->lang['show_price_in']; ?>:</span></li>
      <li>* <?php
        if (empty($search_result['offers'][0]['adult_amount'])) {
          $search_result['offers'][0]['adult_amount'] = 2;
        }
        echo $client->lang['tour_search_title_comment_small'] . ' ' . safe($search_result['offers'][0], 'adult_amount') . (safe($search_result['offers'][0], 'adult_amount')==1?$client->lang['adults_count_genitive_one']:$client->lang['adults_count_genitive_many']) . (safe($search_result['offers'][0], 'child_amount')?' ' . $client->lang['and'] . ' ' . safe($search_result['offers'][0], 'child_amount') . (safe($search_result['offers'][0], 'child_amount')==1?$client->lang['children_count_genitive_one']:$client->lang['children_count_genitive_many']):'');
        ?>
      </li>
    </ul>
    <div class="itt_currency_swith_position">
      <div class="itt_issuing_search_currency_info">
        <select class="itt_issuing_search_custom_select_swith_currency">
          <option value="2" <?php echo (($current_currency_in_mod == 2) ? ('selected="selected"') : ''); ?>><?php echo $client->lang['uah']; ?></option>
          <option value="1" <?php echo (($current_currency_in_mod == 1) ? ('selected="selected"') : ''); ?>><?php echo $client->lang['dollar_symbol']; ?></option>
          <option value="10" <?php echo (($current_currency_in_mod == 10) ? ('selected="selected"') : ''); ?>><?php echo $client->lang['euro_symbol']; ?></option>
        </select>
      </div>
    </div>
    <!-- Информация по выдачи и переключатель валют END -->
  </div>
  
    
  <input type="hidden" value="<?php echo $count_all_package_tours; ?>" name="count_all_package_tours">
  
  
  <?php
  // Check count result search == 0
  if ($count_all_package_tours == 0) {
     // Туров нет => exit
     return ob_get_clean();
  }
  ?>
  
  
  <?php
  // Show item tour
  foreach($search_result['offers'] as $key => $offer) {
  ?>
  <!-- Content result 1 START -->
  <div class="itt_issuing_search_block_background">
    <!-- header START -->
    <ul class="itt_issuing_search_tours_name itt_issuing_search_city_and_map">
      <!-- Название отеля -->
      <li>
        <a href="javascript:void(0);" class="itt_issuing_search_tours_name_link_castom" onclick="return openPackageTourViewInTab(<?php echo $offer['id']; ?>, <?php echo $offer['sharding_rule_id']; ?>);">
          <?php
          if (isset($offer['otpusk_hotel_description']['name'])) {
            // Show hotel_name from otpusk
            echo $offer['otpusk_hotel_description']['name'];
          } else {
            echo $offer['locations'][0]['default_hotel_name'];
          }
          ?>
        </a>
      </li>
      
      <!-- Звездности отеля -->
      <li>
        <?php
        //$offer['locations'][0]['hotel_rating_name'] = 5;// For debug
        ?>
        <div class="itt_issuing_search_rate_<?php echo $offer['locations'][0]['hotel_rating_name']; ?>"></div>
      </li>
      
      <!-- Показать на карте -->
      <?php
      // * Begin блока показать на карте с линком на мапу *
      // Check google map coordinats
      $flag_not_google_map = true;
      
      // For debug 
      //$offer['otpusk_hotel_description']['lat'] = 36.79753;
      //$offer['otpusk_hotel_description']['lng'] = 41.87954;
      
      if (array_key_exists('otpusk_hotel_description', $offer)) {
        if (isset($offer['otpusk_hotel_description']['lat']) && isset($offer['otpusk_hotel_description']['lng'])) {
          $flag_not_google_map = false;
          $google_map_lat = $offer['otpusk_hotel_description']['lat'];
          $google_map_lng = $offer['otpusk_hotel_description']['lng'];
        } else {
          $flag_not_google_map = true;
        }
      }
      ?>
      <?php
      if (!$flag_not_google_map) {
        // Map isset
      ?>
      <li><a href="#TB_inline?width=800&height=600&inlineId=my-content-id" class="thickbox itt_map-open" lat="<?php echo $google_map_lat; ?>" lng="<?php echo $google_map_lng; ?>"><?php echo $client->lang['show_on_map']; ?></a></li>
      <?php
      }
      ?>
      <?php
      // * End блока показать на карте с линком на мапу
      ?>
      
      <!-- Иконка wi-fi -->
      <?php
      // Check 'free wifi'
      $img_wi_fi = '';// For work
      //$img_wi_fi = '<img src="' . $client->get_config('modules_url') . 'new/images/img_web_30/icon_wifi.gif" width="16" height="16" alt="' . $client->lang['free_wi_fi'] . '" title="' . $client->lang['free_wi_fi'] . '" />';// For debug
      if (array_key_exists('otpusk_hotelservice_info', $offer)) {
        // Service status:
        // "1","yes","есть"
        // "2","no","нет"
        // "3","pay","платно"
        // "4","free","бесплатно"
        if (($offer['otpusk_hotelservice_info']['wifi'] == 1) || ($offer['otpusk_hotelservice_info']['wifi'] == 4)) {
          $img_wi_fi = '<img src="' . $client->get_config('modules_url') . 'new/images/img_web_30/icon_wifi.gif" width="16" height="16" alt="' . $client->lang['free_wi_fi'] . '" title="' . $client->lang['free_wi_fi'] . '" />';
        }
      }
      ?>
      <li><?php echo $img_wi_fi; ?></li>
    </ul>
    <!-- header END -->
    
    
    <!-- left content START -->
    <div class="itt_col-xs-9 itt_col-sm-9 itt_col-md-9 itt_col-lg-9 itt_clear_both">
        
      <!-- Блок с фото -->
      <div class="itt_issuing_search_photo_block">
        <div class="itt_issuing_search_photo_prew">
          <?php
          if (isset($offer['otpusk_hotel_images'][0]['href_ittour_big'])) {
            // Otpusk photo
            $big_photo_src = $offer['otpusk_hotel_images'][0]['href_ittour_big'];
            if (isset($offer['otpusk_hotel_images'][0]['href_ittour_small'])) {
              $small_photo_src = $offer['otpusk_hotel_images'][0]['href_ittour_small'];
            } else {
              $small_photo_src = $big_photo_src;
            }
            ?>
            <img src="<?php echo $small_photo_src; ?>" bigsrc="<?php echo $big_photo_src; ?>" width="160" height="120" alt="hotel_photo" />
            <?php
          } elseif($offer['locations'][0]['hotel_image'] != '') {
            // Ittour photo
            $big_photo_src = $ittour_url . $offer['locations'][0]['hotel_image'];
            if ($offer['locations'][0]['hotel_image_thumb'] != '') {
              $small_photo_src = $ittour_url . $offer['locations'][0]['hotel_image_thumb'];
            } else {
              $small_photo_src = $big_photo_src;
            }
            ?>
            <img src="<?php echo $small_photo_src; ?>" bigsrc="<?php echo $big_photo_src; ?>" width="160" height="120" alt="hotel_photo" />
            <?php
          } elseif ($offer['locations'][0]['hotel_images_ittour'][0]) {
            // Ittour country photo
            $img_num_for_view = rand(0, (count($offer['locations'][0]['hotel_images_ittour']) - 1));
            $big_photo_src = $ittour_url . $offer['locations'][0]['hotel_images_ittour'][$img_num_for_view];
            if ($offer['locations'][0]['hotel_image_thumb'] != '') {
              // Set patch for thumb
              $small_photo_src = $ittour_url . $offer['locations'][0]['hotel_images_ittour'][$img_num_for_view];
              $small_photo_src = str_replace("file_name", "file_name_small", $small_photo_src);
              $small_photo_src = str_replace(".jpg", "_160x120.jpg", $small_photo_src);
            } else {
              $small_photo_src = $big_photo_src;
            }
            ?>
            <img src="<?php echo $small_photo_src; ?>" bigsrc="<?php echo $big_photo_src; ?>" width="160" height="120" alt="hotel_photo" />
            <?php
          } else {
            // No photo
            ?>
            <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/<?php echo $client->lang['no_img_png']; ?>" width="160" height="120" alt="hotel_photo" />
            <?php
          }
          ?>
        </div>
        <ul class="itt_issuing_search_more_photo">
          <?php
          // Otpusk photo
          if (array_key_exists('otpusk_hotel_images', $offer)) {
            // Isset image
            $img_count = count($offer['otpusk_hotel_images']);
            if ($img_count>0) {
              // Show image
              for ($i=0; $i<$img_count; $i++) {
                $big_photo_src = $offer['otpusk_hotel_images'][$i]['href_ittour_big'];
                if (isset($offer['otpusk_hotel_images'][$i]['href_ittour_small'])) {
                  $small_photo_src = $offer['otpusk_hotel_images'][$i]['href_ittour_small'];
                } else {
                  $small_photo_src = $big_photo_src;
                }
                ?>
                <li><img src="<?php echo $small_photo_src; ?>" bigsrc="<?php echo $big_photo_src; ?>" width="50" height="40" alt="more_photo" /></li>
                <?php
              }
            }
          } elseif (count($offer['locations'][0]['hotel_images_ittour'])>0) {
            // Ittour photo
            for ($i=1; $i<count($offer['locations'][0]['hotel_images_ittour']); $i++) {
              $big_photo_src = $ittour_url . $offer['locations'][0]['hotel_images_ittour'][$i];
              if ($offer['locations'][0]['hotel_image_thumb'] != '') {
                // Set patch for thumb
                $small_photo_src = $ittour_url . $offer['locations'][0]['hotel_images_ittour'][$i];
                $small_photo_src = str_replace("file_name", "file_name_small", $small_photo_src);
                $small_photo_src = str_replace(".jpg", "_160x120.jpg", $small_photo_src);
              } else {
                $small_photo_src = $big_photo_src;
              }
              ?>  
              <li><img src="<?php echo $small_photo_src; ?>" bigsrc="<?php echo $big_photo_src; ?>" width="50" height="40" alt="more_photo" /></li>
              <?php
            }
          } else {
            // Not photo
            ?>
            <li></li>
            <?php
          }
          ?>
        </ul>
        <!-- Begin pop-up for hotel big image, custom tooltip -->
        <div class="itt_hotel_image_item_custom_tooltip"><img src="" /></div>
        <!-- End pop-up for hotel big image, custom tooltip -->
      </div>
      
      <!-- Блок с контентом -->
      <div class="itt_center_media_content">
        <div class="itt_location_marker"><?php echo $offer['locations'][0]['country_name']; ?>, <?php echo $offer['locations'][0]['region_name']; ?></div>
        
        <!-- firt small table START -->
        <div class="itt_media_min_table">
          <table class="itt_issuing_search_table_text">
            <tr>
              <td><?php echo $client->lang['departure']; ?>:</td>
              <td>
                <span class="itt_tour_departure_date_in_search_result_package">
                  <?php
                  // * Выезд: '15.11.14 пя. на 8дн./7ноч.' *
                  $strtime = strtotime($offer['date_from']);
                  $day_number = date("w", $strtime);
                  $day_view = array(0 => "вс", 1 => "пн", 2 => "вт", 3 => "ср", 4 => "чт", 5 => "пт", 6 => "сб");
                  $days = $offer['duration'] + 1;
                  $date_view = date("d.m.y", $strtime);
                  $x_days_y_nights_view = $client->lang['at_sm']
                                        . ' '
                                        . $days
                                        . $client->lang['days_sh']
                                        . '/'
                                        . $offer['duration']
                                        . $client->lang['nights_sh']
                                        ;
                  ?>
                  <span class="itt_font-bold"><?php echo $date_view; ?></span> <span><?php echo $day_view[$day_number]; ?>.</span> <span class="itt_font-bold"><?php echo $x_days_y_nights_view; ?></span>
                </span>
              </td>
            </tr>
            <tr>
              <td><?php echo $client->lang['accomodation']; ?>:</td>
              <td>
                <span class="itt_font-bold"><?php echo $offer['locations'][0]['accomodation_name']; ?></span> 
                <span>(<?php echo $offer['adult_amount'] . ' ' . $client->lang['adults_reduction'];
                if ($offer['child_amount'] == 1) {
                  echo ' ' . $client->lang['and'] . ' ' . $offer['child_amount'] . ' ' . $client->lang['children_reduction'];
                } elseif ($offer['child_amount'] >= 2) {
                  echo ' ' . $client->lang['and'] . ' ' . $offer['child_amount'] . ' ' . $client->lang['children_reduction_many'];
                }?>)</span>
              </td>
            </tr>
            <tr>
              <td><?php echo $client->lang['meal']; ?>:</td>
              <td class="itt_meal_description">
                <span class="itt_font-bold"><?php echo $offer['locations'][0]['meal_type_name']; ?></span> <span>(<?php echo $offer['locations'][0]['meal_type_description']; ?>)</span>
              </td>
            </tr>
            <tr>
              <td><?php echo $client->lang['flight_from_short']; ?>:</td>
              <td>
                <?php 
                // * Вылет: 'Харьков 14:10' *
                ?>
                <span class="itt_transports_flights_time_city_from">
                  <span class="itt_font-bold"><?php echo $offer['transports'][0]['from_city_name']; ?></span> <span><?php echo $offer['transports'][0]['flights'][0]['time_from']; ?></span>
                </span>
              </td>
            </tr>
          </table>
        </div>
        <!-- firt small table END -->
        
        <!-- Last big table START -->
        <div class="itt_media_max_table itt_row">
          <table class="itt_issuing_search_table_text">
            <tr>
              <td><?php echo $client->lang['departure']; ?>:</td>
              <td class="itt_table-media_width">
                <span class="itt_tour_departure_date_in_search_result_package">
                  <span class="itt_font-bold"><?php echo $date_view; ?></span> <span><?php echo $day_view[$day_number]; ?>.</span> <span class="itt_font-bold"><?php echo $x_days_y_nights_view; ?></span>
                </span>
              </td>
              <td class="itt_table-media_width-two"><?php echo $client->lang['meal']; ?>:</td>
              <td class="itt_meal_description">
                <span class="itt_font-bold"><?php echo $offer['locations'][0]['meal_type_name']; ?></span> <span>(<?php echo $offer['locations'][0]['meal_type_description']; ?>)</span>
              </td>
            </tr>
            <tr>
              <td><?php echo $client->lang['accomodation']; ?>:</td>
              <td>
                <span class="itt_font-bold"><?php echo $offer['locations'][0]['accomodation_name']; ?></span> 
                <span>(<?php echo $offer['adult_amount'] . ' ' . $client->lang['adults_reduction'];
                if ($offer['child_amount'] == 1) {
                  echo ' ' . $client->lang['and'] . ' ' . $offer['child_amount'] . ' ' . $client->lang['children_reduction'];
                } elseif ($offer['child_amount'] >= 2) {
                  echo ' ' . $client->lang['and'] . ' ' . $offer['child_amount'] . ' ' . $client->lang['children_reduction_many'];
                }?>)</span>
              </td>
              <td><?php echo $client->lang['flight_from_short']; ?>:</td>
              <td>
                <?php 
                // * Вылет: 'Харьков 14:10' *
                ?>
                <span class="itt_transports_flights_time_city_from">
                  <span class="itt_font-bold"><?php echo $offer['transports'][0]['from_city_name']; ?></span> <span><?php echo $offer['transports'][0]['flights'][0]['time_from']; ?></span>
                </span>
              </td>
            </tr>
          </table>
        </div>
        <!-- Last big table END -->
        
        <ul class="itt_other_dates_list">
          <li><?php echo $client->lang['other_dates']; ?>:</li>
          <li class="itt_issuing_search_custom_select_position">
            
            <?php
            // * Begin generate select UAH '2' *
            ?>
            <select class="itt_custom_select_other_date itt_item_tour_price_currency_2">
              <?php
              $currency_id_for_current_block = 2;
              $selected_flag = true;
              // Проверяем есть ли даннеы и формируем новые select
              if (array_key_exists('date_from', $offer['data_grop_new_mod_result_package'])) {
                for ($i=0; $i<count($offer['data_grop_new_mod_result_package']['date_from']); $i++) {
                  $strtime = strtotime($offer['data_grop_new_mod_result_package']['date_from'][$i]);
                  $date_view = date("d.m.y", $strtime);
                  $night_view = $offer['data_grop_new_mod_result_package']['duration'][$i];
                  $days_view = $offer['data_grop_new_mod_result_package']['duration'][$i] + 1;
                  $tour_data_link = explode(";", $offer['data_grop_new_mod_result_package']['for_open_link'][$i]);
                  $selected = '';
                  if ($offer['date_from'] == $offer['data_grop_new_mod_result_package']['date_from'][$i] && $selected_flag) {
                    $selected = 'selected="selected"';
                    $selected_flag = false;
                  }
                  $value = $date_view 
                         . ' ' 
                         . $client->lang['at_sm'] 
                         . ' '
                         . $days_view
                         . $client->lang['days_sh'] 
                         . '/'
                         . $night_view 
                         . $client->lang['nights_sh'] 
                         . ' - '
                         //. ceil($offer['data_grop_new_mod_result_package']['prices'][$i][$currency_id_for_current_block])//old
                         . number_format(ceil($offer['data_grop_new_mod_result_package']['prices'][$i][$currency_id_for_current_block]), 0, ',', ' ')
                         . ' '
                         . $client->lang['uah']
                         ;
                  $value_view = $value;
                  //    <option>14.11.14 на 8дн./7ноч. - 3020грн.</option>
                  echo  '<option ' 
                        . $selected 
                        . ' tour_id="' . $tour_data_link[0] . '"'
                        . ' sharding_rule_id="' . $tour_data_link[1] . '"'
                        . ' transports="' . $offer['data_grop_new_mod_result_package']['transports'][$i] . '"'
                        //. ' prices="' . implode(";", $offer['data_grop_new_mod_result_package']['prices'][$i]) . '"'// old
                        . ' prices="' . number_format(ceil($offer['data_grop_new_mod_result_package']['prices'][$i][1]), 0, ',', ' ') . ';' . number_format(ceil($offer['data_grop_new_mod_result_package']['prices'][$i][2]), 0, ',', ' ') . ';' . number_format(ceil($offer['data_grop_new_mod_result_package']['prices'][$i][10]), 0, ',', ' ') . '"'// new
                        . ' meals="' . $offer['data_grop_new_mod_result_package']['meals'][$i] . '"'
                        . ' value="' . $value . '"'
                        . '>' 
                        . $value_view
                        . '</option>';
                }
              } else {
                // Показать одну дату если нет больше дат, только текущую
                $selected = 'selected="selected"';
                $strtime = strtotime($offer['date_from']);
                $days = $offer['duration'] + 1;
                $value = date("d.m.y", $strtime)
                       . ' '
                       . $client->lang['at_sm']
                       . ' '
                       . $days
                       . $client->lang['days_sh']
                       . '/'
                       . $offer['duration']
                       . $client->lang['nights_sh']
                       . ' - '
                       //. ceil($offer['prices'][$currency_id_for_current_block])// old
                       . number_format(ceil($offer['prices'][$currency_id_for_current_block]), 0, ',', ' ')// new
                       . ' '
                       . $client->lang['uah']
                       ;
                $value_view = $value;
                echo  '<option ' 
                        . $selected 
                        . ' tour_id="' . $offer['id'] . '"'
                        . ' sharding_rule_id="' . $offer['sharding_rule_id'] . '"'
                        . ' transports="' . $offer['transports'][0]['from_city_name'] . ' ' . $offer['transports'][0]['flights'][0]['time_from'] . '"'
                        //. ' prices="' . ceil($offer['prices'][1]) . ';' . ceil($offer['prices'][2]) . ';' . ceil($offer['prices'][10]) . '"'// old
                        . ' prices="' . number_format(ceil($offer['prices'][1]), 0, ',', ' ') . ';' . number_format(ceil($offer['prices'][2]), 0, ',', ' ') . ';' . number_format(ceil($offer['prices'][10]), 0, ',', ' ') . '"'// new
                        . ' meals="' . $offer['locations'][0]['meal_type_name'] . '_sep_'. $offer['locations'][0]['meal_type_description'] . '"'
                        . ' value="' . $value . '"'
                        . '>' 
                        . $value_view
                        . '</option>';
              }
              ?>
            </select>
            <?php
            // * End generate select UAH '2' *
            ?>
            
            <?php 
            //* Begin generate select USD '1' *
            ?>
            <select class="itt_custom_select_other_date itt_item_tour_price_currency_1">
              <?php
              $currency_id_for_current_block = 1;
              $selected_flag = true;
              // Проверяем есть ли даннеы и формируем новые select
              if (array_key_exists('date_from', $offer['data_grop_new_mod_result_package'])) {
                for ($i=0; $i<count($offer['data_grop_new_mod_result_package']['date_from']); $i++) {
                  $strtime = strtotime($offer['data_grop_new_mod_result_package']['date_from'][$i]);
                  $date_view = date("d.m.y", $strtime);
                  $night_view = $offer['data_grop_new_mod_result_package']['duration'][$i];
                  $days_view = $offer['data_grop_new_mod_result_package']['duration'][$i] + 1;
                  $tour_data_link = explode(";", $offer['data_grop_new_mod_result_package']['for_open_link'][$i]);
                  $selected = '';
                  if ($offer['date_from'] == $offer['data_grop_new_mod_result_package']['date_from'][$i] && $selected_flag) {
                    $selected = 'selected="selected"';
                    $selected_flag = false;
                  }
                  $value = $date_view 
                         . ' ' 
                         . $client->lang['at_sm'] 
                         . ' '
                         . $days_view
                         . $client->lang['days_sh'] 
                         . '/'
                         . $night_view 
                         . $client->lang['nights_sh'] 
                         . ' - '
                         //. ceil($offer['data_grop_new_mod_result_package']['prices'][$i][$currency_id_for_current_block])// old
                         . number_format(ceil($offer['data_grop_new_mod_result_package']['prices'][$i][$currency_id_for_current_block]), 0, ',', ' ')// new
                         . ' '
                         . $client->lang['dollar_symbol']
                         ;
                  $value_view = $value;
                  //    <option>14.11.14 на 8дн./7ноч. - 300.58$</option>
                  echo  '<option ' 
                        . $selected 
                        . ' tour_id="' . $tour_data_link[0] . '"'
                        . ' sharding_rule_id="' . $tour_data_link[1] . '"'
                        . ' transports="' . $offer['data_grop_new_mod_result_package']['transports'][$i] . '"'
                        //. ' prices="' . implode(";", $offer['data_grop_new_mod_result_package']['prices'][$i]) . '"'// old
                        . ' prices="' . number_format(ceil($offer['data_grop_new_mod_result_package']['prices'][$i][1]), 0, ',', ' ') . ';' . number_format(ceil($offer['data_grop_new_mod_result_package']['prices'][$i][2]), 0, ',', ' ') . ';' . number_format(ceil($offer['data_grop_new_mod_result_package']['prices'][$i][10]), 0, ',', ' ') . '"'// new
                        . ' meals="' . $offer['data_grop_new_mod_result_package']['meals'][$i] . '"'
                        . ' value="' . $value . '"'
                        . '>' 
                        . $value_view
                        . '</option>';
                }
              } else {
                // Показать одну дату если нет больше дат, только текущую
                $selected = 'selected="selected"';
                $strtime = strtotime($offer['date_from']);
                $days = $offer['duration'] + 1;
                $value = date("d.m.y", $strtime)
                       . ' '
                       . $client->lang['at_sm']
                       . ' '
                       . $days
                       . $client->lang['days_sh']
                       . '/'
                       . $offer['duration']
                       . $client->lang['nights_sh']
                       . ' - '
                       //. ceil($offer['prices'][$currency_id_for_current_block])// old
                       . number_format(ceil($offer['prices'][$currency_id_for_current_block]), 0, ',', ' ')// new 
                       . ' '
                       . $client->lang['dollar_symbol']
                       ;
                $value_view = $value;
                echo  '<option ' 
                        . $selected 
                        . ' tour_id="' . $offer['id'] . '"'
                        . ' sharding_rule_id="' . $offer['sharding_rule_id'] . '"'
                        . ' transports="' . $offer['transports'][0]['from_city_name'] . ' ' . $offer['transports'][0]['flights'][0]['time_from'] . '"'
                        //. ' prices="' . ceil($offer['prices'][1]) . ';' . ceil($offer['prices'][2]) . ';' . ceil($offer['prices'][10]) . '"'// old
                        . ' prices="' . number_format(ceil($offer['prices'][1]), 0, ',', ' ') . ';' . number_format(ceil($offer['prices'][2]), 0, ',', ' ') . ';' . number_format(ceil($offer['prices'][10]), 0, ',', ' ') . '"'// new
                        . ' meals="' . $offer['locations'][0]['meal_type_name'] . '_sep_'. $offer['locations'][0]['meal_type_description'] . '"'
                        . ' value="' . $value . '"'
                        . '>' 
                        . $value_view
                        . '</option>';
              }
              ?>
            </select>
            <?php
            // * End generate select USD '1' *
            ?>
            
            <?php
            // * Begin generate select EUR '10' * 
            ?>
            <select class="itt_custom_select_other_date itt_item_tour_price_currency_10">
              <?php
              $currency_id_for_current_block = 10;
              $selected_flag = true;
              // Проверяем есть ли даннеы и формируем новые select
              if (array_key_exists('date_from', $offer['data_grop_new_mod_result_package'])) {
                for ($i=0; $i<count($offer['data_grop_new_mod_result_package']['date_from']); $i++) {
                  $strtime = strtotime($offer['data_grop_new_mod_result_package']['date_from'][$i]);
                  $date_view = date("d.m.y", $strtime);
                  $night_view = $offer['data_grop_new_mod_result_package']['duration'][$i];
                  $days_view = $offer['data_grop_new_mod_result_package']['duration'][$i] + 1;
                  $tour_data_link = explode(";", $offer['data_grop_new_mod_result_package']['for_open_link'][$i]);
                  $selected = '';
                  if ($offer['date_from'] == $offer['data_grop_new_mod_result_package']['date_from'][$i] && $selected_flag) {
                    $selected = 'selected="selected"';
                    $selected_flag = false;
                  }
                  $value = $date_view 
                         . ' ' 
                         . $client->lang['at_sm'] 
                         . ' '
                         . $days_view
                         . $client->lang['days_sh'] 
                         . '/'
                         . $night_view 
                         . $client->lang['nights_sh'] 
                         . ' - '
                         //. ceil($offer['data_grop_new_mod_result_package']['prices'][$i][$currency_id_for_current_block])// old
                         . number_format(ceil($offer['data_grop_new_mod_result_package']['prices'][$i][$currency_id_for_current_block]), 0, ',', ' ')// New
                         . ' '
                         . $client->lang['euro_symbol']
                         ;
                  $value_view = $value;
                  //    <option>14.11.14 на 8дн./7ноч. - 300.58€</option>
                  echo  '<option ' 
                        . $selected 
                        . ' tour_id="' . $tour_data_link[0] . '"'
                        . ' sharding_rule_id="' . $tour_data_link[1] . '"'
                        . ' transports="' . $offer['data_grop_new_mod_result_package']['transports'][$i] . '"'
                        //. ' prices="' . implode(";", $offer['data_grop_new_mod_result_package']['prices'][$i]) . '"'// old
                        . ' prices="' . number_format(ceil($offer['data_grop_new_mod_result_package']['prices'][$i][1]), 0, ',', ' ') . ';' . number_format(ceil($offer['data_grop_new_mod_result_package']['prices'][$i][2]), 0, ',', ' ') . ';' . number_format(ceil($offer['data_grop_new_mod_result_package']['prices'][$i][10]), 0, ',', ' ') . '"'// new
                        . ' meals="' . $offer['data_grop_new_mod_result_package']['meals'][$i] . '"'
                        . ' value="' . $value . '"'
                        . '>' 
                        . $value_view
                        . '</option>';
                }
              } else {
                // Показать одну дату если нет больше дат, только текущую
                $selected = 'selected="selected"';
                $strtime = strtotime($offer['date_from']);
                $days = $offer['duration'] + 1;
                $value = date("d.m.y", $strtime)
                       . ' '
                       . $client->lang['at_sm']
                       . ' '
                       . $days
                       . $client->lang['days_sh']
                       . '/'
                       . $offer['duration']
                       . $client->lang['nights_sh']
                       . ' - '
                       //. ceil($offer['prices'][$currency_id_for_current_block])// old
                       . number_format(ceil($offer['prices'][$currency_id_for_current_block]), 0, ',', ' ')// new
                       . ' '
                       . $client->lang['euro_symbol']
                       ;
                $value_view = $value;
                echo  '<option ' 
                        . $selected 
                        . ' tour_id="' . $offer['id'] . '"'
                        . ' sharding_rule_id="' . $offer['sharding_rule_id'] . '"'
                        . ' transports="' . $offer['transports'][0]['from_city_name'] . ' ' . $offer['transports'][0]['flights'][0]['time_from'] . '"'
                        //. ' prices="' . ceil($offer['prices'][1]) . ';' . ceil($offer['prices'][2]) . ';' . ceil($offer['prices'][10]) . '"'// old
                        . ' prices="' . number_format(ceil($offer['prices'][1]), 0, ',', ' ') . ';' . number_format(ceil($offer['prices'][2]), 0, ',', ' ') . ';' . number_format(ceil($offer['prices'][10]), 0, ',', ' ') . '"'// new
                        . ' value="' . $value . '"'
                        . '>' 
                        . $value_view
                        . '</option>';
              }
              ?>
            </select>
            <?php
            // * End generate select EUR '10'
            ?>
            
          </li>
          <li>
            <?php
            $number_tour_count_for_view = 0;
            if (array_key_exists('date_from', $offer['data_grop_new_mod_result_package'])) {
              for ($i=0; $i<count($offer['data_grop_new_mod_result_package']['date_from']); $i++) $number_tour_count_for_view++;
            }
            if ($number_tour_count_for_view == 0) $number_tour_count_for_view++;
            $number_tour_word = decl_of_num($number_tour_count_for_view, array($client->lang['tour_variant1'], $client->lang['tour_variant2'], $client->lang['tour_variant5']));
            $xxx_tours = ' ' . $number_tour_count_for_view . ' ' . $number_tour_word;// 24 тура
            ?>
            <a href="javascript:void(0);" class="itt_button_copy_click_custom_select_other_date" title="<?php echo $client->lang['in_all'] . $xxx_tours; ?>">
              <?php echo $client->lang['in_all'] . $xxx_tours; ?>
            </a>
          </li>
        </ul>
      </div>
    </div>
    <!-- left content END -->
    
    <!-- Right content block START -->
    <div class="itt_col-xs-3 itt_col-sm-3 itt_col-md-3 itt_col-lg-3">
      <div class="itt_right_content_block">
        <ul class="itt_price_result_list">
          <li>
            <a href="javascript:void(0);" onclick="return openPackageTourViewInTab(<?php echo $offer['id']; ?>, <?php echo $offer['sharding_rule_id']; ?>);" class="itt_issuing_search_link_in_main_big_price">
              <ul class="itt_issuing_search_text_price_list">
                <li><?php echo $client->lang['price_from']; ?></li>
                <?php 
                // * '2' => UAH *
                ?>
                <li class="itt_item_tour_price_currency_2" <?php echo (($current_currency_in_mod == 2) ? ('style="display:block;"') : ''); ?>>
                  <div class="itt_currency_link_chex itt_price_color_in_result_search">
                    <span class="itt_issuing_search_price"><?php echo number_format(ceil($offer['prices'][2]), 0, ',', ' '); ?></span>
                    <span class="itt_issuing_search_currency"><?php echo $client->lang['uah']; ?></span>
                  </div>
                </li>
                <?php
                // * '1' => USD *
                ?>
                <li class="itt_item_tour_price_currency_1" <?php echo (($current_currency_in_mod == 1) ? ('style="display:block;"') : ''); ?>>
                  <div class="itt_currency_link_chex itt_price_color_in_result_search">
                    <span class="itt_issuing_search_price"><?php echo number_format(ceil($offer['prices'][1]), 0, ',', ' '); ?></span>
                    <span class="itt_issuing_search_currency">&#36;</span>
                  </div>
                </li>
                <?php
                // * '10' => EUR *
                ?>
                <li class="itt_item_tour_price_currency_10" <?php echo (($current_currency_in_mod == 10) ? ('style="display:block;"') : ''); ?>>
                  <div class="itt_currency_link_chex itt_price_color_in_result_search">
                    <span class="itt_issuing_search_price"><?php echo number_format(ceil($offer['prices'][10]), 0, ',', ' '); ?></span>
                    <span class="itt_issuing_search_currency">&euro;</span>
                  </div>
                </li>
              </ul>
            </a>
          </li>
          <li class="itt_text_price_center_position"><span class="itt_all_peaople_grey_color"><?php echo $client->lang['for_all']; ?></span></li>
          <li>
            <a class="itt_issuing_search_read_more_btn itt_read_more_button itt_border_rd4" href="javascript:void(0);" onclick="return openPackageTourViewInTab(<?php echo $offer['id']; ?>, <?php echo $offer['sharding_rule_id']; ?>);">
              <?php echo $client->lang['more']; ?>
            </a>
          </li>
        </ul>
      </div>
    </div>
    <!-- Right content block END -->
    
  </div>
  <!-- Content result 1 END -->
  
  <?php
  }// end foreach($search_result['offers'] as $key => $offer) {
  ?>
  
  
  <?php
  // * Begin paginator *
  ?>
  <ul class="itt_issuing_search_pagi_navigator">
    <?php
    if (count($search_result['pager']['pages']) > 1) {
      foreach($search_result['pager']['pages'] as $page) {
        if ($page['type'] == 'page') {
          if (safe($page, 'href')) {
            echo '<li><a href="javascript:void(0);" onclick="return ajax_load_mod2(\''.safe($page, 'href').'\', \'#search_result_tab_1 section#itt_tour_search_result\', '.($open_in_popup?'true':'false').');" >' . (safe($page, 'text')) . '</a></li>';
          } else {
            // Current page
            echo '<li class="itt_issuing_search_active_page"><a href="javascript:void(0);">' . (safe($page, 'text')) . '</a></li>';
          }
        } elseif(safe($page, 'type') == '...') {
          echo '<li>...</li>';
        } elseif(safe($page, 'is_prior')) {
            echo '<li><a href="javascript:void(0);" onclick="return ajax_load_mod2(\''.safe($page, 'href').'\', \'#search_result_tab_1 section#itt_tour_search_result\', '.($open_in_popup?'true':'false').');" >' . $client->lang['prev_new'] . '</a></li>';
        } elseif(safe($page, 'is_next')) {
          echo '<li><a href="javascript:void(0);" onclick="return ajax_load_mod2(\''.safe($page, 'href').'\', \'#search_result_tab_1 section#itt_tour_search_result\', '.($open_in_popup?'true':'false').');" >' . $client->lang['next_new'] . '</a></li>';
        }
      }
    }
    ?>
  </ul>
  <?php
  // * End paginator *
  ?>
  
  
  <?php
  return ob_get_clean();
}

?>