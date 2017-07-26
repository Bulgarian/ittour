<select class="itt_border_rd4 itt_form-control itt_mod_search_custom_select itt_transport">
  <?php foreach($list as $transport_item) {
    $selected = safe($default, $transport_item['id'])?'selected="selected"':'';
    echo "<option value='".$transport_item['id']."'". $selected. ">".$transport_item['name']."</option>";
  }?>
</select>