<?php
// Get config data
$rating_config = trim($client->get_config('hotel_rating_def'));

// Set dafault
$hotel_rating_def = array(3 => 3, 4 => 4, 78 => 78);
if (($rating_config != '') && ($rating_config != ',')) {
    // Set custom
    $rating_array = explode(",", $rating_config);    
    if (count($rating_array)>0) {
        // Clear def setting
        $hotel_rating_def = array();
        for($i=0; $i<count($rating_array); $i++) {
            // Set custom setting
            if (trim($rating_array[$i]) != '') $hotel_rating_def[$rating_array[$i]] = $rating_array[$i];
        }
    }
}
?>
<ul class="itt_search_module_list_star_inline itt_font_weight_bd">
    <li><a href="javascript:void(0);"><span class="itt_search_module_hotel_rating <?php if(array_key_exists('7', $hotel_rating_def)){echo 'itt_search_module_hotel_rating_active';}?>" rel="7"><div class="itt_text_start_position">2</div></span></a></li>
  <li><a href="javascript:void(0);"><span class="itt_search_module_hotel_rating <?php if(array_key_exists('3', $hotel_rating_def)){echo 'itt_search_module_hotel_rating_active';}?>" rel="3"><div class="itt_text_start_position">3</div></span></a></li>
  <li><a href="javascript:void(0);"><span class="itt_search_module_hotel_rating <?php if(array_key_exists('4', $hotel_rating_def)){echo 'itt_search_module_hotel_rating_active';}?>" rel="4"><div class="itt_text_start_position">4</div></span></a></li>
  <li><a href="javascript:void(0);"><span class="itt_search_module_hotel_rating <?php if(array_key_exists('78', $hotel_rating_def)){echo 'itt_search_module_hotel_rating_active';}?>" rel="78"><div class="itt_text_start_position">5</div></span></a></li>
</ul>
<input class="itt_hotel_rating_total" name="itt_hotel_rating_total" value="<?php echo implode(" ", $hotel_rating_def); ?>" type="hidden">