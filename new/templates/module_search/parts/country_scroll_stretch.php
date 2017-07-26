<?php 
$popular_list = array('318', '338');
$regular = $popular = '';
foreach($client->get_optimize_hike_country_list(false) as $country_item) {
    $selected = safe($hike_tour_default_form_value['country'], $country_item['id'])?'selected="selected"':'';
    $type = 'regular';
    if(in_array($country_item['id'],$popular_list)){
        $type = 'popular';
    }
    $$type .= "<option value='".$country_item['id']."' {$selected} >".$country_item['name']."</option>";
 }?>

<select class="itt_border_rd4 itt_form-control_multiple itt_form-control itt_search_module_form_background itt_font_weight_bd itt_counry_list" size="5" multiple="multiple" name="country">
  <?php if(count($popular) > 0){?>
    <optgroup label="<?php echo isset($client->lang['popular_destinations']) ? $client->lang['popular_destinations'] : $client->lang['popular_destinations']; ?>">
      <?php echo $popular; ?>
    </optgroup>
  <?php } ?>
  <optgroup label="<?php echo isset($client->lang['other_destinations']) ? $client->lang['other_destinations'] : $client->lang['other_destinations']; ?>">
    <?php echo $regular; ?>
  </optgroup>    
</select>
<div class="scrollbarY">
  <div class="scrollbar"><div class="track"><div class="thumb"></div></div></div>
  <div class="viewport">
    <div class="overview"></div>
  </div>
</div>