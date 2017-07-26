<?php 
if(!isset($days)) {
   $days = $client->lang['days'];
}

if($is_short){
    $days = mb_substr($days,0,1,"UTF-8").'.';
}
?>
<div class="itt_field-holder">
    <div class="itt_bg-r">
        <input class="itt_date_from" type="text" value="<?php echo isset($departure_date) ? $departure_date : date("d.m.Y"); ?>">
        <a href="#" class="itt_link-calendar itt_bg-color"></a>
    </div>
</div>
<div class="itt_select-holder itt_select-holder2">
    <label for="itt_night3">+</label>
    <select data-width="<?php echo $width; ?>" id="itt_night3" class="itt_size-2 itt_date_period">
        <option value="1">1<?php echo $days;?></option>
        <option value="3">3<?php echo $days;?></option>
        <option value="7">7<?php echo $days;?></option>
        <option value="12">12<?php echo $days;?></option>
        <option value="15">15<?php echo $days;?></option>
    </select>
</div>