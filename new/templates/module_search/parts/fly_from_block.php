<strong class="itt_label"><?php echo isset($title) ? $title : $client->lang['flight_from'];?></strong>
<div class="itt_select-holder">
    <?php echo $this->renderTemplate($parts.'fly_from_select.php', array(
        'width'     => $width,
        'list'      => $list,
        'default'   => $default,
        'client'    => $client,
    )); ?>  
</div>