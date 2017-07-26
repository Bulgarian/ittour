<div class="scrollbarY">
  <div class="scrollbar">
    <div class="track">
      <div class="thumb"></div> 
    </div>  
  </div>
  
  <div class="viewport">
    <div class="overview">
      <div class="itt_list-checkbox">
        <?php foreach($hike_tour_form_field['tour_city'] as $tour_city_item) {
          $selected = (safe($hike_tour_default_form_value['transport_city'], $tour_city_item['id']))?'checked="checked"':'';?>
          <div class="itt_row itt_row_in_list_region">
            <input value="<?php echo $tour_city_item['id'];?>" <?php echo $selected;?>type="checkbox" id="tour_city_<?php echo $tour_city_item['id'];?>" class="itt_tour_city_list"/>
            <label for="tour_city_<?php echo $tour_city_item['id'];?>"><?php echo $tour_city_item['name'];?></label>
          </div>
        <?php }?>
      </div>
    </div>
  </div>
</div>