<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */
?>

<div id="itt_tour_search_module" class="itt_tour_search_module_ itt_search-module">


    <?php
    // Add check servise active or not active
    if ($module->module_new_is_service_active === true) {
    ?>


    <!-- Search module / main -->
    <div class='itt_package' <?php if(!$module->is_package_block_active){ echo 'style="display:none"';}?> id="itt_tour_search_module_package_block">
         <?php 
         echo $module->getPackageSearchForm();?>
    </div>
    
    <div class='itt_hike' <?php if(!$module->is_hike_block_active){ echo 'style="display:none"';} ?> id="itt_tour_search_module_hike_block">
        <?php echo $module->getHikeSearchForm();?>
    </div>
    <!-- end / Search module / main -->
    
    <div class="itt_display_none" id="itt_tour_search_result"></div>
    
    <div class="itt_display_none" id="itt_tour_search_load">
        <b><?php echo $module->client->lang['go_tour_search']; ?></b><br>
        <img src="<?php echo $module->client->get_config('modules_url');?>/images/ajax_loader.gif"><br>
        <?php echo $module->client->lang['please_wait']; ?>
    </div>
    
    <!-- Search module / result blocks -->
    <div class="itt_display_none" id="itt_search_results">
        <?php echo $module->client->lang['search_results']; ?>
    </div>
    <div class="itt_display_none" id="itt_close">
        <?php echo $module->client->lang['close']; ?>
    </div>
    <div class="itt_display_none" id="tour_order_caption">
        <?php echo $module->client->lang['tour_request']; ?>
    </div>
    <div class="itt_display_none" id="tour_search_result_caption">
        <strong><?php echo $module->client->lang['tour_search_result']; ?></strong>
    </div>
    <div class="itt_display_none" id="go_tour_search_caption">
        <strong><?php echo $module->client->lang['go_tour_search']; ?></strong>
    </div>
    <div class="itt_display_none" id="please_wait_caption">
        <strong><?php echo $module->client->lang['please_wait']; ?></strong>
    </div>
    <div class="itt_display_none" id="show_info_caption">
        <strong><?php echo $module->client->lang['show_info']; ?></strong>
    </div>
    <div class="itt_display_none" id="hide_info_caption">
        <strong><?php echo $module->client->lang['hide_info']; ?></strong>
    </div>
    <div class="itt_display_none" class="itt_error_msg">
        <?php echo $module->client->lang['error_msg']; ?>
    </div>
    <!-- end / Search module / result blocks -->


    <?php
    } else {
      // $module->module_new_is_service_active !== true
      ?>
      <div id="module_new_search_is_not_active">
          <div class="it_no_results"><div><?php echo $module->client->lang['service_not_activated']; ?></div></div>
      </div>
      <?php
    }
    ?>


</div>
