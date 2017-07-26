<select data-width="<?php echo $width; ?>" class="itt_size-7-2 itt_departure_city">
    <?php foreach($list as $departure_city_item) {
       $selected = $default == $departure_city_item['id'] ? "selected='selected'" : '';
       echo "<option value='".$departure_city_item['id']."'". $selected." >".$departure_city_item['name']."</option>";
    } ?>
</select>