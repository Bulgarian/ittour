<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

/**
 * Function return html form buy online for new module search
 * 
 * @param object $client Object webclient, use language constant
 * @param array $params Array with params for form, see 'key' in array
 * @return void Include html form
 */
function html_form_buy_all($client, $params) {
  if (count($params) === 0) return;
  
  // Set params for view
  $form_id = $params['form_id'];
  $action = $params['action'];
  $input_price = $params['input_price'];
  $input_currency_id = $params['input_currency_id'];
  $tour_id = $params['tour_id'];
  $sharding_rule_id = $params['sharding_rule_id'];
  $date_till = $params['date_till'];
  $offer_type_id = $params['offer_type_id'];
  $offer_kind_id = $params['offer_kind_id'];
  $offer_country_id = $params['offer_country_id'];
  $offer_region_id = $params['offer_region_id'];
  $offer_accomodation = $params['offer_accomodation'];
  $offer_hotel_id = $params['offer_hotel_id'];
  $offer_room_type_id = $params['offer_room_type_id'];
  $offer_room_type_kn_id = $params['offer_room_type_kn_id'];
  $offer_meal_type_id = $params['offer_meal_type_id'];
  $offer_meal_type_kn_id = $params['offer_meal_type_kn_id'];
  $offer_operator_id = $params['offer_operator_id'];
  $offer_spo_code = $params['offer_spo_code'];
  $offer_duration = $params['offer_duration'];
  $offer_date_from = $params['offer_date_from'];
  $offer_from_city_id = $params['offer_from_city_id'];
  $offer_currency_id = $params['offer_currency_id'];
  $offer_price_grn = $params['offer_price_grn'];
  $offer_from_plane_id = $params['offer_from_plane_id'];
  $offer_to_plane_id = $params['offer_to_plane_id'];
  $offer_spo_comment = strip_tags($params['offer_spo_comment']);
  $offer_tour_price_id = $params['offer_tour_price_id'];
  $offer_flight_from_id = $params['offer_flight_from_id'];
  $offer_flight_to_id = $params['offer_flight_to_id'];
  $offers = $params['offers'];
  // Add for captcha
  $offers_captcha_url = $params['offers_captcha_url'];
  $offers_captcha_value_code = $params['offers_captcha_value_code'];
  // Add dinamic generation adult and child
  $offers_adult_amount = $params['offers_adult_amount'];
  $offers_child_amount = $params['offers_child_amount'];
  ?>
  <!-- Begin form buy all -->
  <form class="itt_info_tourists_buy_all_form" id="<?php echo $form_id; ?>" method="POST" action="#">
    
    <input type="hidden" name="action" value="<?php echo $action; ?>" />
    <input type="hidden" class="input_price" name="input_price" value="<?php echo $input_price; ?>" />
    <input type="hidden" class="input_currency_id" name="input_currency_id" value="<?php echo $input_currency_id; ?>" />
    <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>" />
    <input type="hidden" name="sharding_rule_id" value="<?php echo $sharding_rule_id; ?>" />
    <?php 
    // Begin set hidden params for create order in client -> service
    ?>
    <input type="hidden" name="date_till" value="<?php echo $date_till; ?>" />
    <input type="hidden" name="offer_type_id" value="<?php echo $offer_type_id; ?>" />
    <input type="hidden" name="offer_kind_id" value="<?php echo $offer_kind_id; ?>" />
    <input type="hidden" name="offer_country_id" value="<?php echo $offer_country_id; ?>" />
    <input type="hidden" name="offer_region_id" value="<?php echo $offer_region_id; ?>" />
    <input type="hidden" name="offer_accomodation" value="<?php echo $offer_accomodation; ?>" />
    <input type="hidden" name="offer_hotel_id" value="<?php echo $offer_hotel_id; ?>" />
    <input type="hidden" name="offer_room_type_id" value="<?php echo $offer_room_type_id; ?>" />
    <input type="hidden" name="offer_room_type_kn_id" value="<?php echo $offer_room_type_kn_id; ?>" />
    <input type="hidden" name="offer_meal_type_id" value="<?php echo $offer_meal_type_id; ?>" />
    <input type="hidden" name="offer_meal_type_kn_id" value="<?php echo $offer_meal_type_kn_id; ?>" />
    <input type="hidden" name="offer_operator_id" value="<?php echo $offer_operator_id; ?>" />
    <input type="hidden" name="offer_spo_code" value="<?php echo $offer_spo_code; ?>" />
    <input type="hidden" name="offer_duration" value="<?php echo $offer_duration; ?>" />
    <input type="hidden" name="offer_date_from" value="<?php echo $offer_date_from; ?>" />
    <input type="hidden" name="offer_from_city_id" value="<?php echo $offer_from_city_id; ?>" />
    <input type="hidden" name="offer_currency_id" value="<?php echo $offer_currency_id; ?>" />
    <input type="hidden" name="offer_price_grn" value="<?php echo $offer_price_grn; ?>" />
    <input type="hidden" name="offer_from_plane_id" value="<?php echo $offer_from_plane_id; ?>" />
    <input type="hidden" name="offer_to_plane_id" value="<?php echo $offer_to_plane_id; ?>" />
    <input type="hidden" name="offer_spo_comment" value="<?php echo $offer_spo_comment; ?>" />
    <input type="hidden" name="offer_tour_price_id" value="<?php echo $offer_tour_price_id; ?>" />
    <input type="hidden" name="offer_flight_from_id" value="<?php echo $offer_flight_from_id; ?>" />
    <input type="hidden" name="offer_flight_to_id" value="<?php echo $offer_flight_to_id; ?>" />
    <input type="hidden" name="offers_adult_amount" value="<?php echo $offers_adult_amount; ?>" />
    <input type="hidden" name="offers_child_amount" value="<?php echo $offers_child_amount; ?>" />
    
    <!-- Begin new html 15.04.2015 -->
    <div class="itt_form_date_blcok">
      <h2 class="itt_2h_tittle_tour"><?php echo $client->lang['customer_information_tour']; ?></h2>
      <div class="itt_error_msg_from_server"></div>
      <div class="itt_row itt_mtv_top_20">
        <div class="itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name">
              <dt><?php echo $client->lang['your_name']; ?></dt>
              <dd><input name="first_name" type="text" class="itt_form-control" placeholder="<?php echo $client->lang['your_name']; ?>"></dd>
            </dl>
        </div>
        <div class="itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
          <dl class="itt itt_form_name">
            <dt><?php echo $client->lang['your_city']; ?></dt>
            <dd><input name="city" type="text" class="itt_form-control" placeholder="<?php echo $client->lang['your_city']; ?>"></dd>
          </dl>
        </div>
        <div class="itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
          <dl class="itt itt_form_name">
            <dt><?php echo $client->lang['your_email']; ?></dt>
            <dd><input name="email" type="text" class="itt_form-control" placeholder="<?php echo $client->lang['your_email']; ?>"></dd>
          </dl>
        </div>
        <div class="itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
          <dl class="itt itt_form_name">
            <dt><?php echo $client->lang['phone']; ?></dt>
            <dt>
              <ul class="itt_muti_placeholder">
                <li><span class="itt_glyphicon itt_glyphicon-plus itt_color_blue"></span></li>
                <li><input name="phone_code" type="text" class="itt_form-control itt_form-contro4" placeholder="38" maxlength="2" value="38"></li>
                <li><input name="phone" type="text" class="itt_form-control itt_form-contro3" placeholder="___ ___ __ __" maxlength="13"></li>
              </ul>
            </dt>
          </dl>
        </div>
      </div>
      <div class="itt_row">
        <div class="itt_col-xs-12">
          <dl class="itt itt_form_name">
            <dt><?php echo $client->lang['comment']; ?></dt>
            <dd><textarea name="comment" class="itt_form-control" placeholder="<?php echo $client->lang['your_message']; ?>" rows="3"></textarea></dd>
          </dl>
        </div>
      </div>
      <!-- Begin captcha -->
      <div class="itt_row itt_captcha_wrap_main">
        <div class="itt_col-xs-12">
          <div class="itt_float_left itt_captcha_wrap_1">
            <img src="<?php echo $offers_captcha_url; ?>" class="captcha" alt="captcha" title="captcha" />
          </div>
          <div class="itt_float_left itt_captcha_wrap_3">
            <img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/refresh_32x32.png" class="itt_captcha_refresh" title="<?php echo $client->lang['captcha_refresh_text_2']; ?>" />
          </div>
          <input name="value_code" value="<?php echo $offers_captcha_value_code; ?>" type="hidden" />
          <div class="itt_float_left itt_captcha_wrap_2">
            <input name="captcha" type="text" value="" class="itt_form-control itt_form-contro3 itt_captcha_input" placeholder="<?php echo $client->lang['captcha']; ?>" />
          </div>
        </div>
      </div>
      <!-- End captcha -->
      <!-- Формы данных START -->
      <div class="itt_add_person">
        <input name="checkbox1" type="checkbox" value="1" tabindex="1" />
        <label><?php echo $client->lang['i_want_to_add_data_to_the_tour_participants']; ?></label>
      </div>
      <ul class="itt_more_information_about_booking">
        <li><a href="javascript:void(0);" class="itt_button_more_information_about_booking itt_booking_down"><?php echo $client->lang['more_information_about_booking']; ?></a></li>
        <li><a href="javascript:void(0);" class="itt_button_more_information_about_booking itt_booking_down"><img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/question_images_13.png" alt="question_images" width="13" height="13" /></a></li>
      </ul>
    </div>
    
    <!-- Псоле нажатой галочки виден этот блок-->
    <div class="itt_new_person_block">
      <div class="itt_person2_bg itt_row_padding">
        <h2 class="itt_2h_tittle_tour"><?php echo $client->lang['information_about_the_participants_of_the_tour']; ?></h2>
        <?php
        // Add dinamic generation adult and child
        $total_count_tourists = $offers_adult_amount + $offers_child_amount;
        $child_count_for_text = 1;
        for ($tourist_number = 1; $tourist_number <= $total_count_tourists; $tourist_number++ ) {
        ?>
        <!-- Взрослый / Ребёнок START -->
        <div class="itt_row itt_mtv_top_20">
          <div class="itt_person_name itt_font_weight_bd">
            <?php
            // Проверка для вывода надписи 'Взрослый X' или 'Ребёнок X'
            if ($tourist_number <= $offers_adult_amount) {
              // 'Взрослый X'
              echo $client->lang['adult'] . ' ' . $tourist_number;
            } else {
              // 'Ребёнок X'
              echo $client->lang['child'] . ' ' . $child_count_for_text;
              $child_count_for_text++;
            }
            ?>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name itt_form-name_max_width">
              <dt><?php echo $client->lang['name_latin']; ?></dt>
              <dd><input name="clientname_<?php echo $tourist_number; ?>" type="text" class="itt_form-control" placeholder="DENIS"></dd>
            </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name itt_form-name_max_width">
              <dt class="itt_last_name_position"><?php echo $client->lang['last_name_latin']; ?></dt>
              <dd><input name="clientsurname_<?php echo $tourist_number; ?>" type="text" class="itt_form-control" placeholder="POLTUSOV"></dd>
            </dl>
          </div>
           <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <dl class="itt itt_form_name">
                 <dt><?php echo $client->lang['date_of_birth']; ?></dt>
                 <dt><ul class="itt_muti_placeholder">
                   <li><input name="clientDobDay_<?php echo $tourist_number; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_dd']; ?>"></li>
                   <li><input name="clientDobMonth_<?php echo $tourist_number; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_mm']; ?>"></li>
                   <li><input name="clientDobYear_<?php echo $tourist_number; ?>" maxlength="4" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_yyyy']; ?>"></li>
                 </ul></dt>
              </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <ul class="itt_list-inline">
                <li><dl class="itt itt_form_name">
                <dt><?php echo $client->lang['gender']; ?></dt>
                <dd><div class="itt_choice_form"><?php echo generate_gender_select_html('gender_' . $tourist_number, $client); ?></div>
                </dd>
              </dl></li>
              <li><dl class="itt itt_form_name">
              <dt><?php echo $client->lang['citizenship']; ?></dt>
              <dd><div class="itt_choice_form"><?php echo generate_nationality_select_html($offers, 'citizenship_' . $tourist_number, $client); ?></div></dd>
            </dl></li>
          </div>
           <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name">
              <dt><?php echo $client->lang['series_and_number_of_passport']; ?></dt>
              <dt><ul class="itt_muti_placeholder">
                   <li><input name="pass_series_<?php echo $tourist_number; ?>" type="text" class="itt_form-control itt_form-contro2" placeholder="ER"></li>
                   <li><input name="pass_numb_<?php echo $tourist_number; ?>" type="text" class="itt_form-control itt_form-contro3" placeholder="12345"></li>
                 </ul></dt>
            </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <dl class="itt itt_form_name">
                 <dt><?php echo $client->lang['date_of_issue']; ?></dt>
                 <dt><ul class="itt_muti_placeholder">
                   <li><input name="clientPassDay_<?php echo $tourist_number; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_dd']; ?>"></li>
                   <li><input name="clientPassMonth_<?php echo $tourist_number; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_mm']; ?>"></li>
                   <li><input name="clientPassYear_<?php echo $tourist_number; ?>" maxlength="4" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_yyyy']; ?>"></li>
                 </ul></dt>
              </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name itt_pasport_max_width">
              <dt><?php echo $client->lang['issuing_authority']; ?></dt>
              <dd><input name="clientAuthor_<?php echo $tourist_number; ?>" type="text" class="itt_form-control" placeholder="<?php echo $client->lang['issuing_authority']; ?>"></dd>
            </dl>
          </div>
           <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <dl class="itt itt_form_name">
                <dt><?php echo $client->lang['validity_of_passport_to']; ?></dt>
                 <dt><ul class="itt_muti_placeholder">
                   <li><input name="clientPassValidDay_<?php echo $tourist_number; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_dd']; ?>"></li>
                   <li><input name="clientPassValidMonth_<?php echo $tourist_number; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_mm']; ?>"></li>
                   <li><input name="clientPassValidYear_<?php echo $tourist_number; ?>" maxlength="4" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_yyyy']; ?>"></li>
                 </ul></dt>
              </dl>
          </div>
        </div>
        <!-- Взрослый / Ребёнок END -->
        <?php
        }
        ?>
        
        
        <?php
        // Блок для вывода детей => 'Дети до 2х лет'
        ?>
        <div class="itt_add_children itt_color_blue"><span class="itt_glyphicon itt_glyphicon-plus"></span> <a href="javascript:void(0);" class="itt_btn_add_children"><?php echo $client->lang['children_up_to_2_years']; ?></a></div>
        <!-- Ребёнок 1 START -->
        <div class="itt_row itt_row_child_item">
          <div class="itt_person_name itt_font_weight_bd"><?php echo $client->lang['child']; ?> 1 <a href="javascript:void(0);" class="itt_btn_delete_children"><span class="itt_glyphicon itt_minus_icons_red itt_glyphicon-remove" title="<?php echo $client->lang['delete_record']; ?>"></span></a></div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name itt_form-name_max_width">
              <dt><?php echo $client->lang['name_latin']; ?></dt>
              <dd><input name="clientname_<?php echo $tourist_number; ?>" type="text" class="itt_form-control" placeholder="DENIS"></dd>
            </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name itt_form-name_max_width">
              <dt class="itt_last_name_position"><?php echo $client->lang['last_name_latin']; ?></dt>
              <dd><input type="text" name="clientsurname_<?php echo $tourist_number; ?>" class="itt_form-control" placeholder="POLTUSOV"></dd>
            </dl>
          </div>
           <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <dl class="itt itt_form_name">
                 <dt><?php echo $client->lang['date_of_birth']; ?></dt>
                 <dt><ul class="itt_muti_placeholder">
                   <li><input name="clientDobDay_<?php echo $tourist_number; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_dd']; ?>"></li>
                   <li><input name="clientDobMonth_<?php echo $tourist_number; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_mm']; ?>"></li>
                   <li><input name="clientDobYear_<?php echo $tourist_number; ?>" maxlength="4" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_yyyy']; ?>"></li>
                 </ul></dt>
              </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <ul class="itt_list-inline">
                <li><dl class="itt itt_form_name">
                <dt><?php echo $client->lang['gender']; ?></dt>
                <dd><div class="itt_choice_form"><?php echo generate_gender_select_html('gender_' . $tourist_number, $client); ?></div>
                </dd>
              </dl></li>
              <li><dl class="itt itt_form_name">
              <dt><?php echo $client->lang['citizenship']; ?></dt>
              <dd><div class="itt_choice_form"><?php echo generate_nationality_select_html($offers, 'citizenship_' . $tourist_number, $client); ?></div></dd>
            </dl></li>
          </div>
           <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name">
              <dt><?php echo $client->lang['series_and_number_of_passport']; ?></dt>
              <dt><ul class="itt_muti_placeholder">
                   <li><input name="pass_series_<?php echo $tourist_number; ?>" type="text" class="itt_form-control itt_form-contro2" placeholder="ER"></li>
                   <li><input name="pass_numb_<?php echo $tourist_number; ?>" type="text" class="itt_form-control itt_form-contro3" placeholder="12345"></li>
                 </ul></dt>
            </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <dl class="itt itt_form_name">
                 <dt><?php echo $client->lang['date_of_issue']; ?></dt>
                 <dt><ul class="itt_muti_placeholder">
                   <li><input name="clientPassDay_<?php echo $tourist_number; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_dd']; ?>"></li>
                   <li><input name="clientPassMonth_<?php echo $tourist_number; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_mm']; ?>"></li>
                   <li><input name="clientPassYear_<?php echo $tourist_number; ?>" maxlength="4" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_yyyy']; ?>"></li>
                 </ul></dt>
              </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name itt_pasport_max_width">
              <dt><?php echo $client->lang['issuing_authority']; ?></dt>
              <dd><input name="clientAuthor_<?php echo $tourist_number; ?>" type="text" class="itt_form-control" placeholder="<?php echo $client->lang['issuing_authority']; ?>"></dd>
            </dl>
          </div>
           <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <dl class="itt itt_form_name">
                <dt><?php echo $client->lang['validity_of_passport_to']; ?></dt>
                 <dt><ul class="itt_muti_placeholder">
                   <li><input name="clientPassValidDay_<?php echo $tourist_number; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_dd']; ?>"></li>
                   <li><input name="clientPassValidMonth_<?php echo $tourist_number; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_mm']; ?>"></li>
                   <li><input name="clientPassValidYear_<?php echo $tourist_number; ?>" maxlength="4" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_yyyy']; ?>"></li>
                 </ul></dt>
              </dl>
          </div>
        </div>
        <!-- Ребёнок 1 END -->
        <!-- Ребёнок 2 START -->
        <div class="itt_row itt_row_child_item">
          <div class="itt_person_name itt_font_weight_bd"><?php echo $client->lang['child']; ?> 2 <a href="javascript:void(0);" class="itt_btn_delete_children"><span class="itt_glyphicon itt_minus_icons_red itt_glyphicon-remove" title="<?php echo $client->lang['delete_record']; ?>"></span></a></div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name itt_form-name_max_width">
              <dt><?php echo $client->lang['name_latin']; ?></dt>
              <dd><input name="clientname_<?php echo $tourist_number + 1; ?>" type="text" class="itt_form-control" placeholder="DENIS"></dd>
            </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name itt_form-name_max_width">
              <dt class="itt_last_name_position"><?php echo $client->lang['last_name_latin']; ?></dt>
              <dd><input name="clientsurname_<?php echo $tourist_number + 1; ?>" type="text" class="itt_form-control" placeholder="POLTUSOV"></dd>
            </dl>
          </div>
           <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <dl class="itt itt_form_name">
                 <dt><?php echo $client->lang['date_of_birth']; ?></dt>
                 <dt><ul class="itt_muti_placeholder">
                   <li><input name="clientDobDay_<?php echo $tourist_number + 1; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_dd']; ?>"></li>
                   <li><input name="clientDobMonth_<?php echo $tourist_number + 1; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_mm']; ?>"></li>
                   <li><input name="clientDobYear_<?php echo $tourist_number + 1; ?>" maxlength="4" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_yyyy']; ?>"></li>
                 </ul></dt>
              </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <ul class="itt_list-inline">
                <li><dl class="itt itt_form_name">
                <dt><?php echo $client->lang['gender']; ?></dt>
                <dd><div class="itt_choice_form"><?php echo generate_gender_select_html('gender_' . ($tourist_number + 1), $client); ?></div>
                </dd>
              </dl></li>
              <li><dl class="itt itt_form_name">
              <dt><?php echo $client->lang['citizenship']; ?></dt>
              <dd><div class="itt_choice_form"><?php echo generate_nationality_select_html($offers, 'citizenship_' . ($tourist_number + 1), $client); ?></div></dd>
            </dl></li>
          </div>
           <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name">
              <dt><?php echo $client->lang['series_and_number_of_passport']; ?></dt>
              <dt><ul class="itt_muti_placeholder">
                   <li><input name="pass_series_<?php echo $tourist_number + 1; ?>" type="text" class="itt_form-control itt_form-contro2" placeholder="ER"></li>
                   <li><input name="pass_numb_<?php echo $tourist_number + 1; ?>" type="text" class="itt_form-control itt_form-contro3" placeholder="12345"></li>
                 </ul></dt>
            </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <dl class="itt itt_form_name">
                 <dt><?php echo $client->lang['date_of_issue']; ?></dt>
                 <dt><ul class="itt_muti_placeholder">
                   <li><input name="clientPassDay_<?php echo $tourist_number + 1; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_dd']; ?>"></li>
                   <li><input name="clientPassMonth_<?php echo $tourist_number + 1; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_mm']; ?>"></li>
                   <li><input name="clientPassYear_<?php echo $tourist_number + 1; ?>" maxlength="4" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_yyyy']; ?>"></li>
                 </ul></dt>
              </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name itt_pasport_max_width">
              <dt><?php echo $client->lang['issuing_authority']; ?></dt>
              <dd><input name="clientAuthor_<?php echo $tourist_number + 1; ?>" type="text" class="itt_form-control" placeholder="<?php echo $client->lang['issuing_authority']; ?>"></dd>
            </dl>
          </div>   
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <dl class="itt itt_form_name">
                <dt><?php echo $client->lang['validity_of_passport_to']; ?></dt>
                 <dt><ul class="itt_muti_placeholder">
                   <li><input name="clientPassValidDay_<?php echo $tourist_number + 1; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_dd']; ?>"></li>
                   <li><input name="clientPassValidMonth_<?php echo $tourist_number + 1; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_mm']; ?>"></li>
                   <li><input name="clientPassValidYear_<?php echo $tourist_number + 1; ?>" maxlength="4" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_yyyy']; ?>"></li>
                 </ul></dt>
              </dl>
          </div>
        </div> 
        <!-- Ребёнок 2 END -->
        <!-- Ребёнок 3 START -->
        <div class="itt_row itt_row_child_item">
          <div class="itt_person_name itt_font_weight_bd"><?php echo $client->lang['child']; ?> 3 <a href="javascript:void(0);" class="itt_btn_delete_children"><span class="itt_glyphicon itt_minus_icons_red itt_glyphicon-remove" title="<?php echo $client->lang['delete_record']; ?>"></span></a></div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name itt_form-name_max_width">
              <dt><?php echo $client->lang['name_latin']; ?></dt>
              <dd><input name="clientname_<?php echo $tourist_number + 2; ?>" type="text" class="itt_form-control" placeholder="DENIS"></dd>
            </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name itt_form-name_max_width">
              <dt class="itt_last_name_position"><?php echo $client->lang['last_name_latin']; ?></dt>
              <dd><input name="clientsurname_<?php echo $tourist_number + 2; ?>" type="text" class="itt_form-control" placeholder="POLTUSOV"></dd>
            </dl>
          </div>
           <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <dl class="itt itt_form_name">
                <dt><?php echo $client->lang['date_of_birth']; ?></dt>
                 <dt><ul class="itt_muti_placeholder">
                   <li><input name="clientDobDay_<?php echo $tourist_number + 2; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_dd']; ?>"></li>
                   <li><input name="clientDobMonth_<?php echo $tourist_number + 2; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_mm']; ?>"></li>
                   <li><input name="clientDobYear_<?php echo $tourist_number + 2; ?>" maxlength="4" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_yyyy']; ?>"></li>
                 </ul></dt>
              </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <ul class="itt_list-inline">
                <li><dl class="itt itt_form_name">
                <dt><?php echo $client->lang['gender']; ?></dt>
                <dd><div class="itt_choice_form"><?php echo generate_gender_select_html('gender_' . ($tourist_number + 2), $client); ?></div>
                </dd>
              </dl></li>
              <li><dl class="itt itt_form_name">
              <dt><?php echo $client->lang['citizenship']; ?></dt>
              <dd><div class="itt_choice_form"><?php echo generate_nationality_select_html($offers, 'citizenship_' . ($tourist_number + 2), $client); ?></div></dd>
            </dl></li>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name">
              <dt><?php echo $client->lang['series_and_number_of_passport']; ?></dt>
              <dt><ul class="itt_muti_placeholder">
                   <li><input name="pass_series_<?php echo $tourist_number + 2; ?>" type="text" class="itt_form-control itt_form-contro2" placeholder="ER"></li>
                   <li><input name="pass_numb_<?php echo $tourist_number + 2; ?>" type="text" class="itt_form-control itt_form-contro3" placeholder="12345"></li>
                 </ul></dt>
            </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <dl class="itt itt_form_name">
                 <dt><?php echo $client->lang['date_of_issue']; ?></dt>
                 <dt><ul class="itt_muti_placeholder">
                   <li><input name="clientPassDay_<?php echo $tourist_number + 2; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_dd']; ?>"></li>
                   <li><input name="clientPassMonth_<?php echo $tourist_number + 2; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_mm']; ?>"></li>
                   <li><input name="clientPassYear_<?php echo $tourist_number + 2; ?>" maxlength="4" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_yyyy']; ?>"></li>
                 </ul></dt>
              </dl>
          </div>
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
            <dl class="itt itt_form_name itt_pasport_max_width">
              <dt><?php echo $client->lang['issuing_authority']; ?></dt>
              <dd><input name="clientAuthor_<?php echo $tourist_number + 2; ?>" type="text" class="itt_form-control" placeholder="<?php echo $client->lang['issuing_authority']; ?>"></dd>
            </dl>
          </div>   
          <div class="itt_col-lg-3 itt_col-md-6 itt_col-sm-6 itt_col-xs-6">
              <dl class="itt itt_form_name">
                <dt><?php echo $client->lang['validity_of_passport_to']; ?></dt>
                 <dt><ul class="itt_muti_placeholder">
                   <li><input name="clientPassValidDay_<?php echo $tourist_number + 2; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_dd']; ?>"></li>
                   <li><input name="clientPassValidMonth_<?php echo $tourist_number + 2; ?>" maxlength="2" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_mm']; ?>"></li>
                   <li><input name="clientPassValidYear_<?php echo $tourist_number + 2; ?>" maxlength="4" type="text" class="itt_form-control itt_form-contro2" placeholder="<?php echo $client->lang['text_yyyy']; ?>"></li>
                 </ul></dt>
              </dl>
          </div>
        </div>
        <!-- Ребёнок 3 END -->
      </div>
      
      <?php
      // Add settings hide pay form
      if ($_SESSION['agency_config_package_tour_default_form_value']['module2_hide_pay_form'] != '1') {
      ?>
      <!-- Оплата тура START -->
      <div class="itt_pay_tour_add itt_row_padding">
        <input name="checkbox2" type="checkbox" value="1" tabindex="1" />
        <label><?php echo $client->lang['i_want_to_pay_for_a_tour_online_now']; ?></label>
      </div>
      
      <!-- Контент оплаты START -->
      <div class="itt_content_pay_block itt_row_padding">
        <h2 class="itt_2h_tittle_tour"><?php echo $client->lang['choose_payment_method']; ?></h2>
        <!--- Оплата банковской картой START -->
        <div class="itt_row itt_mtv_top_10">
          <div class="itt_col-md-12 itt_col-sm-12 itt_col-xs-12 itt_payment_method_position">
            <div class="itt_credit_card_privat"></div>
            <span class="itt_pull-left itt_payment_method_select_position">
              <input value="2" id="itt_radio1" name="is_card_payment" type="radio" checked="checked" />
              <label class="itt_payment_method_label_color itt_font_weight_bd"><?php echo $client->lang['pay_by_credit_card']; ?></label>
            </span>
            <ul class="itt_read_the_terms">
              <li><a href="javascript:void(0);" class="itt_payment_privat_card"><?php echo $client->lang['read_the_terms']; ?></a></li>
              <li><a href="javascript:void(0);" class="itt_payment_privat_card"><img src="<?php echo $client->get_config('modules_url'); ?>new/images/img_web_30/question_images_13.png" alt="question_images" width="13" height="13" /></a></li>
            </ul>
            <p class="itt_payment_privat_card_description">
              <strong><?php echo $client->lang['procedure_for_payment_by_credit_card_application']; ?>:</strong>
              <br>
              <?php echo $client->lang['procedure_for_payment_by_credit_card_application_description']; ?>
            </p>
          </div>
        </div>
        <!--- Оплата банковской картой END -->        
      </div>
      <!-- Контент оплаты END -->
      <?php } else { ?>
        <input type="hidden" name="is_card_payment" value="1"/>
      <?php } ?>
      <!-- Оплата тура END -->
    </div>
    
    <div class="itt_button_block_width">
      <div class="itt_send_form_button_position">
        <div class="itt_md_info_payment_text"><?php echo $client->lang['sending_query_does_not_obligate']; ?></div>
        <button name="send" class="itt itt_send_btn itt_bg_color_blue itt_color_white itt_border_rd4 itt_payment_btn itt_button_all_in_item" type="submit"><?php echo $client->lang['submit_request']; ?></button>
      </div>
      <div class="itt_wrap_text_go_to_main_form"><a href="javascript:void(0);" class="itt_button_hide_payment_varik"><?php echo $client->lang['back_to_the_sending_request']; ?></a></div>
    </div>
    <div class="itt_wrap_text_original_button_name"><?php echo $client->lang['submit_request']; ?></div>
    <div class="itt_wrap_text_new_button_name"><?php echo $client->lang['go_to_payment']; ?></div>
    <!-- Формы данных END -->
    <!-- End new html 15.04.2015 -->
        
  </form>
  <!-- End form buy all -->
  
  <div id="itt_tour_send_data_loader_buy_online" class="itt_for_beautiful_end_form_buy_all">
    <span><?php echo $client->lang['the_data_is_sent_please_wait']; ?></span>
    <?php $megaNumberForLoader = rand(10000001, 90000009); ?>
    <div id="buy_loader_<?php echo $megaNumberForLoader; ?>"></div>
    <script type="text/javascript">
    setTimeout(function(){
      // Cereate pre-loader in item tour in form 'buy all'
      initCustomPreloaderIttour('buy_loader_<?php echo $megaNumberForLoader; ?>', '#2473b8', 37, 1.7, 23, true);
    }, 500);
    </script>
  </div>
    
  <div id="itt_tour_order_success_message_online" class="itt_for_beautiful_end_form_buy_all"><?php echo $client->lang['tour_order_success']; ?></div>
  
  <?php
  // * div-area for payment form *
  ?>
  <div class="itt_payment_html_form_area"></div>
  
  <?php
}
?>
