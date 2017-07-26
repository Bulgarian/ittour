<?php if(!isset($is_title) || $is_title !== false){ ?>
<label><?php echo isset($title) ? $title : $client->lang['price_for_the_tour']; ?></label>
<?php } ?>

<select data-width="<?php echo $width; ?>" class="itt_size-5 itt_price_from" name="price_from" id="price_from">
    <option value="100" selected="selected">100</option>
    <option value="1000">1 000</option>
    <option value="3000">3 000</option>
    <option value="5000">5 000</option>
    <option value="10000">10 000</option>
    <option value="20000">20 000</option>
    <option value="50000">50 000</option>
</select>