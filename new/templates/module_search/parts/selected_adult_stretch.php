<?php
$adult_amount_def = trim($client->get_config('adult_amount_def'));
$default = 2;
if (($adult_amount_def != '') && ($adult_amount_def > 0) && ($adult_amount_def <=5)) $default = $adult_amount_def;
?>
<option value="1" <?php if($default==1){echo 'selected="selected"';} ?>>1</option>
<option value="2" <?php if($default==2){echo 'selected="selected"';} ?>>2</option>
<option value="3" <?php if($default==3){echo 'selected="selected"';} ?>>3</option>
<option value="4" <?php if($default==4){echo 'selected="selected"';} ?>>4</option>
<option value="5" <?php if($default==5){echo 'selected="selected"';} ?>>5</option>