<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

if (!function_exists('decl_of_num')) {
  function decl_of_num($number, $titles) {
    $cases = array (2, 0, 1, 1, 1, 2);
    return $titles[ ($number%100>4 && $number%100<20)? 2 : $cases[min($number%10, 5)] ];
  }
}

if (!function_exists('safe')) {
  function safe($array, $name, $default = null) { 
    return (is_array($array) && strlen($name) && array_key_exists($name, $array) && ($array[$name] || (is_scalar($array[$name]) and strlen($array[$name])))) ? $array[$name] : $default;  
  }
}

if (!function_exists('session')) {
  function session($name, $default = null) { return isset($_SESSION[$name]) ? $_SESSION[$name] : $default; }
}

if (!function_exists('get')) {
  function get($name, $default = null) {   
    if (isset($_GET[$name])) {
      if (is_array($_GET[$name])) {
        $res_array = array();
        foreach($_GET[$name] as $key => $value) {
          $res_array[$key] = trim($value);
        }
        return $res_array;
      } else {
        return trim($_GET[$name]);
      }
    } else {
      return $default;
    }    
  }
}

if (!function_exists('post')) {
  function post($name, $default = null) { return isset($_POST[$name]) ? $_POST[$name] : $default; }
}

function change_charset_array (&$item, $key, $client) {
  if(is_string($item))    
    $item = @iconv('UTF-8', $client->get_config('webservice_encoding') . '//IGNORE', $item);
}

function change_charset_array_revert (&$item, $key, $client) {
  if(is_string($item))      
    $item = @iconv($client->get_config('webservice_encoding'), 'UTF-8//IGNORE', $item);
}

function prepare_mailer_custom(&$mailer) {
    
  $mailer->ClearAddresses();
  $mailer->ClearCCs();
  $mailer->ClearBCCs();
  $mailer->ClearReplyTos();
  $mailer->ClearCustomHeaders();
  $mailer->Username = null;
  $mailer->Password = null;
  $mailer->SMTPAuth = false;
  $mailer->From     = null;
  $mailer->FromName = null;
  $mailer->attachment = array();

}

function send_mail_custom(&$client, &$mailer) {
  
  $mailer->Mailer = $client->get_config('mailer_type');
  if($client->get_config('mailer_type') == 'smtp') {
    $mailer->Host = $client->get_config('mailer_smtp_server');
    if($client->get_config('mailer_smtp_user')) {
      $mailer->Username = $client->get_config('mailer_smtp_user');
      $mailer->Password = $client->get_config('mailer_smtp_password');
      $mailer->SMTPAuth = true;
    }
  }
  
  preg_match_all("/(src|background)=\"(.*)\"/Ui", $mailer->Body, $images);
  if (isset($images[2])) {
    $attached_images = array();
    foreach($images[2] as $img) {
      if(!in_array($img,$attached_images)) {
        $attached_images[] = $img;
        //$img_url = (($img[0] == '/')?WEBSITE_URL.$img:$img);
        $img_url = (($img[0] == '/')?$img:$img);
        if ($encoded_img = file_get_contents($img_url)) {
          $md5 = md5($img_url);
          // Append to $attachment array
          $mailer->attachment[] = array(
            0 => $encoded_img,
            1 => $md5,
            2 => $md5,
            3 => 'base64',
            4 => $mailer->_mime_types(pathinfo($img_url, PATHINFO_EXTENSION)),
            5 => true,  // isStringAttachment
            6 => 'inline',
            7 => $md5
          );
          $mailer->Body = str_replace($img, 'cid:'.md5($img_url), $mailer->Body);
        }
      }
    }
  }
  
  $mailer->AddReplyTo($mailer->From, $mailer->FromName);
  $result = $mailer->Send();  
  
//  if (!$result)
//    echo 'Error: '.$mailer->ErrorInfo;    
  
  return $result;  
}

function get_js_from_html($html, $variable_name) {
  $html = explode("\n", $html);
  $html_js = "\nvar {$variable_name} = '';\n";
  foreach($html as $html_line) {
    $html_line = str_replace("'", "\'", trim($html_line));
    if($html_line) {
      $html_line = "{$variable_name} += '" . $html_line . "';";
      $html_js .= ($html_line . "\n");
    }
  }
  $html_js .= "document.getElementById('{$variable_name}').innerHTML = {$variable_name};\n";
  return $html_js;
}

if (!function_exists('bin_age_to_range')) {
  function bin_age_to_range($age) {

    $from = -1;
    $till = -1;
    
    for ($i = 0; $i <= 18; $i++) {
      if (($from == -1) && ($age & bindec(str_pad('1', 19-$i, '0')))) {
        $from = $i;
        $till = $i;
      }
      if (($from != -1) && (!($age & bindec(str_pad('1', 19-$i, '0'))))) {
        $till = $i-1;
        break;
      }
    }
    
    return array('from' => $from, 'till' => $till);
    
  }
}

if (!function_exists('array_walk_recursive'))
{
    function array_walk_recursive(&$input, $funcname, $userdata = "")
    {
        if (!is_callable($funcname))
        {
            return false;
        }
       
        if (!is_array($input))
        {
            return false;
        }
       
        foreach ($input AS $key => $value)
        {
            if (is_array($input[$key]))
            {
                array_walk_recursive($input[$key], $funcname, $userdata);
            }
            else
            {
                $saved_value = $value;
                if (!empty($userdata))
                {
                    $funcname($value, $key, $userdata);
                }
                else
                {
                    $funcname($value, $key);
                }
               
                if ($value != $saved_value)
                {
                    $input[$key] = $value;
                }
            }
        }
        return true;
    }
}

function color_blend_by_opacity( $foreground, $opacity, $background=null ) {
  
  static $colors_rgb=array(); // stores colour values already passed through the hexdec() functions below.
  
  if( is_null($background) )
      $background = 'FFFFFF'; // default background.

  $pattern = '~^[a-f0-9]{6,6}$~i'; // accept only valid hexadecimal colour values.
  if( !@preg_match($pattern, $foreground)  or  !@preg_match($pattern, $background) )
  {
      trigger_error( "Invalid hexadecimal colour value(s) found", E_USER_WARNING );
      return false;
  }
      
  $opacity = intval( $opacity ); // validate opacity data/number.
  if( $opacity>100  || $opacity<0 )
  {
      trigger_error( "Opacity percentage error, valid numbers are between 0 - 100", E_USER_WARNING );
      return false;
  }

  if( $opacity==100 )    // $transparency == 0
      return strtoupper( $foreground );
  if( $opacity==0 )    // $transparency == 100
      return strtoupper( $background );
  // calculate $transparency value.
  $transparency = 100-$opacity;

  if( !isset($colors_rgb[$foreground]) )
  { // do this only ONCE per script, for each unique colour.
      $f = array(  'r'=>hexdec($foreground[0].$foreground[1]),
                   'g'=>hexdec($foreground[2].$foreground[3]),
                   'b'=>hexdec($foreground[4].$foreground[5])    );
      $colors_rgb[$foreground] = $f;
  }
  else
  { // if this function is used 100 times in a script, this block is run 99 times.  Efficient.
      $f = $colors_rgb[$foreground];
  }
  
  if( !isset($colors_rgb[$background]) )
  { // do this only ONCE per script, for each unique colour.
      $b = array(  'r'=>hexdec($background[0].$background[1]),
                   'g'=>hexdec($background[2].$background[3]),
                   'b'=>hexdec($background[4].$background[5])    );
      $colors_rgb[$background] = $b;
  }
  else
  { // if this FUNCTION is used 100 times in a SCRIPT, this block will run 99 times.  Efficient.
      $b = $colors_rgb[$background];
  }
  
  $add = array(    'r'=>( $b['r']-$f['r'] ) / 100,
                   'g'=>( $b['g']-$f['g'] ) / 100,
                   'b'=>( $b['b']-$f['b'] ) / 100    );
                  
  $f['r'] += intval( $add['r'] * $transparency );
  $f['g'] += intval( $add['g'] * $transparency );
  $f['b'] += intval( $add['b'] * $transparency );
  
  return sprintf( '%02X%02X%02X', $f['r'], $f['g'], $f['b'] );
}

function accomodation_title(&$client, $adult, $child) {
  $string =  $client->lang['price_from_accomodation'].' ';
  $string .= $adult.(($adult>1)?$client->lang['adults_count_genitive_many']:$client->lang['adults_count_genitive_one']);
  if($child) {
    $string .= ' '.$lang['and'].' ';
    $string .= $child.(($child>1)?$client->lang['children_count_genitive_many']:$client->lang['children_count_genitive_one']);
  }
  return $string;
}



if (!function_exists('sql_compile_placeholder'))
{
  function sql_compile_placeholder($tmpl) {

    $compiled  = array();
    $p         = 0;
    $i         = 0;
    $has_named = false;

    while (false !== ($start = $p = strpos($tmpl, "?", $p))) {

      switch ($c = substr($tmpl, ++$p, 1)) {
        case '&':
        case '%':
        case '@':
        case '#':
          $type = $c;
          ++$p;
          break;
        default:
          $type = '';
          break;
      }

      if (preg_match('/^((?:[^\s[:punct:]]|_)+)/', substr($tmpl, $p), $pock)) {

        $key = $pock[1];
        if ($type != '#')
          $has_named = true;
        $p += strlen($key);

      } else {

        $key = $i;
        if ($type != '#')
          $i++;

      }

      $compiled[] = array($key, $type, $start, $p - $start);
    }

    return array($compiled, $tmpl, $has_named);

  }

}

if (!function_exists('sql_placeholder_ex')) {
  function sql_placeholder_ex($tmpl, $args, &$errormsg) {

    global $db;

    if (is_array($tmpl)) {
      $compiled = $tmpl;
    } else {
      $compiled = sql_compile_placeholder($tmpl);
    }

    list ($compiled, $tmpl, $has_named) = $compiled;

    if ($has_named) 
      $args = @$args[0];

    $p   = 0;
    $out = '';
    $error = false;

    foreach ($compiled as $num=>$e) {

      list ($key, $type, $start, $length) = $e;

      $out .= substr($tmpl, $p, $start - $p);
      $p = $start + $length;

      $repl = '';
      $errmsg = '';

      do {
        
        if (!isset($args[$key]))
          $args[$key] = "";

        if ($type === '#') {
          $repl = @constant($key);
          if (NULL === $repl)
            $error = $errmsg = "UNKNOWN_CONSTANT_$key";
          break;
        }

        if (!isset($args[$key])) {
          $error = $errmsg = "UNKNOWN_PLACEHOLDER_$key";
          break;
        }

        $a = $args[$key];
        if ($type === '&') {
          if ($a === "")
            $repl = "null";
          else  
            $repl = "'".addslashes($a)."'";
          break;
        } else
        if ($type === '') {
          if (is_array($a)) {
            $error = $errmsg = "NOT_A_SCALAR_PLACEHOLDER_$key";
            break;
          }
          if ($a === "")
            $repl = "null";
          else {
            $repl = (preg_match('#^[-]?([1-9][0-9]*|[0-9])($|[.,][0-9]+$)#', $a)) ? str_replace(',', '.', $a) : "'".addslashes($a)."'";
          }
          break;
        }

        if (!is_array($a)) {
          $error = $errmsg = "NOT_AN_ARRAY_PLACEHOLDER_$key";
          break;
        }

        if ($type === '@') {
          foreach ($a as $v) {
            $repl .= ($repl===''? "" : ",").(preg_match('#^[-]?([1-9][0-9]*|[0-9])($|[.,][0-9]+$)#', $v) ? str_replace(',', '.', $v):"'".$v."'");
          }
        } else
        if ($type === '%') {
          $lerror = array();
          foreach ($a as $k=>$v) {
            if (!is_string($k)) {
              $lerror[$k] = "NOT_A_STRING_KEY_{$k}_FOR_PLACEHOLDER_$key";
            } else {
              $k = preg_replace('/[^a-zA-Z0-9_]/', '_', $k);
            }
            $repl .= ($repl===''? "" : ", ").$k."='".@addslashes($v)."'";
          }
          if (count($lerror)) {
            $repl = '';
            foreach ($a as $k=>$v) {
              if (isset($lerror[$k])) {
                $repl .= ($repl===''? "" : ", ").$lerror[$k];
              } else {
                $k = preg_replace('/[^a-zA-Z0-9_-]/', '_', $k);
                $repl .= ($repl===''? "" : ", ").$k."=?";
              }
            }
            $error = $errmsg = $repl;
          }
        }

      } while (false);

      if ($errmsg) 
        $compiled[$num]['error'] = $errmsg;

      if (!$error) 
        $out .= $repl;

    }
    $out .= substr($tmpl, $p);

    if ($error) {
      $out = '';
      $p   = 0;
      foreach ($compiled as $num=>$e) {
        list ($key, $type, $start, $length) = $e;
        $out .= substr($tmpl, $p, $start - $p);
        $p = $start + $length;
        if (isset($e['error'])) {
          $out .= $e['error'];
        } else {
          $out .= substr($tmpl, $start, $length);
        }
      }
      $out .= substr($tmpl, $p);
      $errormsg = $out;
      return false;
    } else {
      $errormsg = false;
      return $out;
    }

  }

}


if (!function_exists('placeholder')) {
  function placeholder() {

    $args = func_get_args();
    $tmpl = array_shift($args);
    $result = sql_placeholder_ex($tmpl, $args, $error);
    if ($result === false)
      return 'ERROR: '.$error;
    else
      return $result;

  }

}
  
?>