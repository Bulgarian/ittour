<select class="itt_border_rd4 itt_form-control itt_mod_search_custom_select itt_departure_city">
  <?php foreach($list as $departure_city_item) {
    $selected = $default == $departure_city_item['id'] ? "selected='selected'" : '';
    echo "<option value='".$departure_city_item['id']."'". $selected." >".$departure_city_item['name']."</option>";
  }?>
</select>