<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */
?>
<div id="itt_tour_search_module">

  <?php
  // Add check servise active or not active
  if ($module->module_new_is_service_active === true) {
  ?>
    
    
    <!-- Begin set bg image for hotel stars -->
    <?php
    // Get color
    $button_color = $module->config_agency['button_color'];
    
    // Check file exist
    $file_exists = false;
    $source_full_path_to_image = $module->client->get_config('modules_url') . 'images/bg_cache/start_active_new_mod_' . $button_color . '_'. $button_color . '.png';
    if(file_get_contents($source_full_path_to_image)) $file_exists = true;
    
    // Set new bg for stars hotel
    if (($button_color != '') && $file_exists) {
    ?>
      <style>
      #tour_search_module_mod2 ul > li > a > span.itt_search_module_hotel_rating_active:after {
        background: url(<?php echo $source_full_path_to_image; ?>) no-repeat !important;
      }
      </style>
    <?php
    }
    ?>
    <!-- End set bg image for hotel stars -->
    
    
    <?php /* Begin module search */ ?>
    <div class="itt_content_position itt_bg_color_search_module itt_bg-color itt_search-module_new">
      <ul class="itt_search_module_tab">
        <li class="itt_search_module_tab_head itt_pull-right itt_font_weight_bd itt_radio_position">
          <span class="itt_check_label_city"><label for="itt_radio1" class="itt_label_radio_position"><?php echo $module->client->lang['yes_caps'];?></label><input name="itt_tour_type" id="itt_radio1" type="radio" value="1" checked="checked"/></span>
          <span class="itt_check_label_city"><label for="itt_radio2" class="itt_label_radio_position"><?php echo $module->client->lang['no_caps'];?></label><input name="itt_tour_type" id="itt_radio2" type="radio" value="2" /></span>
          <input type="hidden" value="1" id="itt_tour_type">
        </li>
        <li class="itt_search_module_tab_head itt_pull-right itt_directions_position"><?php echo $module->client->lang['transport_included'];?>:</li>
        <li class="itt_search_module_tab_head itt_package_tab_title">
          <a href="javascript:void(0);" class="itt_search_module_tab_title itt_font_weight_bd itt_border_rd4 itt_bg-color_conten"><span><?php echo $module->client->lang['package_tours'];?></span></a>
          <section class="itt itt_package itt_bg-color_conten">
            <?php echo $module->getPackageSearchForm();?>
          </section>
        </li>
        <?php /* Данный блок закрыт до окончания разработки hike tours */?>
        <li class="itt_search_module_tab_head" style="display: none;">
          <a href="javascript:void(0);" class="itt_search_module_tab_title itt_font_weight_bd itt_border_rd4 itt_search_module_tab_width itt_bg-color_conten"><span><?php echo $module->client->lang['hike'];?></span></a>
          <section class="itt itt_hike itt_bg-color_conten"></section>
        </li>
      </ul>
    </div>
    <?php /* End module search */ ?>
    
    
    
    <?php /* Begin new html for module search result */ ?>
    <!-- Page loaders START -->
    <div class="itt_page_loader" id="itt_tour_search_load">
      <span><?php echo $module->client->lang['please_wait']; ?></span>
      <div id="canvasloader_itt_tour_search_load" class="itt_page_loader_img"><span><?php echo $module->client->lang['search_tours']; ?>...</span></div>
    </div> 
    <!-- Page loaders END -->
    
    <div id="itt_issuing_search_package_tours" class="itt_container-fluid"> 
      <div class="itt_row itt_margin_top_15_px">
          
        <!-- Content and tabs START -->
        <ul class="itt_issuing_search_tabs" id="itt_issuing_search_tabs_main_block">
          <li class="itt_issuing_search_tabs_head_cont" id="search_result_tab_1">
            <a href="#" class="is-active itt_issuing_search_links_head_tab"><span class="itt_tab_bg_write"><?php echo $module->client->lang['all_tours']; ?></span></a>
            
            <!-- Search module / result blocks -->
            <section id="itt_tour_search_result"></section>
            <!-- end / Search module / result blocks -->
          </li>
        </ul>
        <!-- Content and tabs END -->
        
      </div>
    </div>
    
    <?php /* HTML for pop-up with google.map */ ?>
    <div id="my-content-id" style="display:none;">
      <div id="map_canvas_view_in_search_result" class="itt_map_big_preview" style="height: 600px; width: 100%;"></div>
    </div>
    
    <?php /* HTML bufer for change select in 'other date' */ ?>
    <div class="itt_class_for_save_bufer"></div>
    
    <?php /* Begin pop-up */ ?>
    <div class="itt_popup_more_information_about_booking">
      <div class="itt_popup_header_bg">
        <h3><?php echo $module->client->lang['more_information_about_booking_long']; ?></h3>
        <span class="itt_glyphicon itt_glyphicon-remove itt_close_popup_more_information_about_booking"></span>
      </div>
      <div class="itt_popup_content">
        <p>
          <strong><?php echo $module->client->lang['procedure_for_payment_by_credit_card_application']; ?>:</strong>
          <br>
          <?php echo $module->client->lang['procedure_for_payment_by_credit_card_application_description']; ?>
        </p>
        <p>
          <br>
          <strong><?php echo $module->client->lang['terms_of_payment_via_privat24']; ?>:</strong>
          <br>
          <?php echo $module->client->lang['terms_of_payment_via_privat24_description']; ?>
        </p>
      </div>
    </div>
    <?php /* End pop-up */ ?>
    
    <?php /* Begin lang text constant for js */?>
    <div id="lang_text_constant_for_js" style="display:none;">
      <span class="please_wait_ttt"><?php echo $module->client->lang['please_wait_ttt']; ?></span>
    </div>
    <?php /* End lang text constant for js */?>
    
    <?php /* End new html for module search result */ ?>
  <?php
  } else {
    // $module->module_new_is_service_active !== true
  ?>
    <div id="module_new_search_is_not_active">
      <div class="it_no_results"><div><?php echo $module->client->lang['service_not_activated']; ?></div></div>
    </div>
    
    <?php /* Begin new html for module search result */ ?>
    <!-- Page loaders START -->
    <div class="itt_page_loader" id="itt_tour_search_load">
      <span><?php echo $module->client->lang['please_wait']; ?></span>
      <div id="canvasloader_itt_tour_search_load" class="itt_page_loader_img"><span><?php echo $module->client->lang['search_tours']; ?>...</span></div>
    </div> 
    <!-- Page loaders END -->
  <?php
  }
  ?>

</div>