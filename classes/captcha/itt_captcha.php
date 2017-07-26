<?php
/*
 *  IT-Tour. 2016
 *  Внешние модули Айтитур.
 *  www.ittour.com.ua
 *  Версия 2.1 
 */

define('ITT_CAPTCHA_PARAM_MODULE', '_ittmd');
define('ITT_CAPTCHA_MODULE_NAME',  'itt_captcha');

define('ITT_CAPTCHA_PARAM_ACTION', '_ac');
define('ITT_CAPTCHA_ACTION_GET',   'itt_get');
define('ITT_CAPTCHA_ACTION_RESET', 'itt_reset');
define('ITT_CAPTCHA_PARAM_RESET',  '_ittrs');

define('ITT_CAPTCHA_PARAM_CODE', '_code');

define('ITT_CAPTCHA_SESSION_TAG',  'EAA152AB-3922-4254-AEDE-34F488473CE0');
define('ITT_CAPTCHA_SESSION_TAG_PARAMS',  'C38EA481-BF3D-4D56-8A50-2236B291C698');

class itt_captcha {

		// How many chars the generated text should have
		var $chars	= 6;

    // The minimum size a Char should have
		var $min_size	= 20;

    // The maximum size a Char can have
		var $max_size	= 25;

    // The maximum degrees a Char should be rotated. Set it to 30 means a random rotation between -30 and 30.
		var $max_rotation = 30;

    // Background noise On/Off (if is Off, a grid will be created)
		var $noise = true;

    // Noise factor
    var $noise_factor = 9;  // this will multiplyed with number of chars

    // Background color
    var $background_color = null;  

    // This will only use the 216 websafe color pallette for the image.
		var $websafe_colors = false;

    // This will only use the 216 websafe color pallette for the image.
    var $secret_key = '16084700-C444-4E94-915A-0A10CFBF8595-3C9F94AF-1EEB-43B6-B0E2-C92B40228957-4907E544-0AF6-4F67-AA50-E19B047F54FC';

    // generated value
    var $value = null;
    var $value_code = null;
    
		/** @private **/
		var $lx;				// width of picture
		/** @private **/
		var $ly;				// height of picture
		/** @private **/
		var $jpegquality = 80;	// image quality
		/** @private **/
		var $nb_noise;			// number of background-noise-characters
		/** @private **/
		var $key;				// md5-key
		/** @private **/
		var $gd_version;		// holds the Version Number of GD-Library
		/** @private **/
		var $r;
		/** @private **/
		var $g;
		/** @private **/
		var $b;

    var $fonts_folder;
    var $fonts = array('comic.ttf', 'arial.ttf', 'times.ttf');

		function itt_captcha() {


    $this->fonts_folder = dirname(__FILE__) . '/fonts/';

			 // Test for GD-Library(-Version)
			 $this->gd_version = $this->get_gd_version();
    
		}
  
  function handler() {

    $this->set_custom_parameters();
    $this->render();    
    if (get(ITT_CAPTCHA_PARAM_MODULE) == ITT_CAPTCHA_MODULE_NAME) {
      if (get(ITT_CAPTCHA_PARAM_RESET))
        $this->reset();
      if (get(ITT_CAPTCHA_PARAM_ACTION) == ITT_CAPTCHA_ACTION_GET)
        $this->draw();
    }
      
  }
    
  function guid() {
       
       // The field names refer to RFC 4122 section 4.1.2

       return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
           mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
           mt_rand(0, 65535), // 16 bits for "time_mid"
           mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
           bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
               // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
               // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
               // 8 bits for "clk_seq_low"
           mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"  
       );  
       
    }
    
    function reset() {
        
      $key = md5($this->guid());
      //$this->value = substr(md5($key), 16 - $this->chars / 2, $this->chars);// Work => генерация чисел и букв
      // Генерируем только цифры
      $str_val = '';
      for ($i=0; $i<$this->chars; $i++) {
        $str_val .= rand(0, 9);
      }
      $this->value = $str_val;
      $this->value_code = md5(md5(strtolower($this->value)));

      $_SESSION[ITT_CAPTCHA_SESSION_TAG] = $this->value;
      
    }

    function render()  {         
      $value = null;
      if(count($_GET) && isset($_GET[ITT_CAPTCHA_PARAM_CODE])) {
        $value = $this->xorShipher(base64_decode($_GET[ITT_CAPTCHA_PARAM_CODE]), 'JHkfekf');
      }
      if (!($this->value = $value))
        $this->reset();
      
    }
    
    function draw() {
			
      if ($this->gd_version == 0) 
        die('There is no GD-Library-Support enabled. The Captcha-Class cannot be used!');
        
      // check TrueTypeFonts
      foreach ($this->fonts as $font_name)
        if (!is_readable($this->fonts_folder.$font_name))
          die('Font "'.$font_name.'" not found');
          
      header('Pragma: no-cache');
      header('Expires: 0');
      header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
      header('Content-Type: image/jpeg');

      // get number of noise-chars for background if is enabled
      $this->nb_noise = $this->noise ? ($this->chars * $this->noise_factor) : 0;

      // set dimension of image
      if (!$this->lx)
        $this->lx = ($this->chars + 1) * (int)(($this->max_size + $this->min_size) / 1.5);
      if (!$this->ly)
        $this->ly = (int)(2.4 * $this->max_size);

      if($this->gd_version >= 2 && !$this->websafe_colors) {
        $func1 = 'imagecreatetruecolor';
        $func2 = 'imagecolorallocate';
      } else {
        $func1 = 'imageCreate';
        $func2 = 'imagecolorclosest';
      }
      
      $image = $func1($this->lx, $this->ly);

      // Set Backgroundcolor
      if ($this->background_color) {
        $rgb = $this->html2rgb($this->background_color);
        $back = @imagecolorallocate($image, safe($rgb, 0), safe($rgb, 1), safe($rgb, 2));
      } else {
        $this->random_color(224, 255);
        $back = @imagecolorallocate($image, $this->r, $this->g, $this->b);
      }
      @ImageFilledRectangle($image,0,0,$this->lx,$this->ly,$back);

      // allocates the 216 websafe color palette to the image
      if (($this->gd_version < 2) or $this->websafe_colors) 
        $this->make_websafe_colors($image);

      // fill with noise or grid
      if($this->nb_noise > 0) {
        // random characters in background with random position, angle, color
        for($i=0; $i < $this->nb_noise; $i++) {
          srand((double)microtime()*1000000);
          $size  = intval(rand((int)($this->min_size / 2.3), (int)($this->max_size / 1.7)));
          srand((double)microtime()*1000000);
          $angle  = intval(rand(0, 360));
          srand((double)microtime()*1000000);
          $x    = intval(rand(0, $this->lx));
          srand((double)microtime()*1000000);
          $y    = intval(rand(0, (int)($this->ly - ($size / 5))));
          $this->random_color(160, 224);
          $color  = $func2($image, $this->r, $this->g, $this->b);
          srand((double)microtime()*1000000);
          $text  = chr(intval(rand(45,250)));
          @ImageTTFText($image, $size, $angle, $x, $y, $color, $this->random_font(), $text);
        }
      } else {
        // generate grid
        for($i=0; $i < $this->lx; $i += (int)($this->min_size / 1.5)) {
          $this->random_color(160, 224);
          $color  = $func2($image, $this->r, $this->g, $this->b);
          @imageline($image, $i, 0, $i, $this->ly, $color);
        }
        for($i=0 ; $i < $this->ly; $i += (int)($this->min_size / 1.8)) {
          $this->random_color(160, 224);
          $color  = $func2($image, $this->r, $this->g, $this->b);
          @imageline($image, 0, $i, $this->lx, $i, $color);
        }
      }

      // generate Text
      for($i=0, $x = intval(rand($this->min_size,$this->max_size)); $i < $this->chars; $i++) {
        $text  = strtoupper(substr($this->value, $i, 1));
        srand((double)microtime()*1000000);
        $angle  = intval(rand(($this->max_rotation * -1), $this->max_rotation));
        srand((double)microtime()*1000000);
        $size  = intval(rand($this->min_size, $this->max_size));
        srand((double)microtime()*1000000);
        $y    = intval(rand((int)($size * 1.5), (int)($this->ly - ($size / 7))));
        $this->random_color(0, 127);
        $color  =  $func2($image, $this->r, $this->g, $this->b);
        $this->random_color(0, 127);
        $shadow = $func2($image, $this->r + 127, $this->g + 127, $this->b + 127);
        @ImageTTFText($image, $size, $angle, $x + (int)($size / 15), $y, $shadow, $this->random_font(), $text);
        @ImageTTFText($image, $size, $angle, $x, $y - (int)($size / 15), $color, $this->random_font(), $text);
        $x += (int)($size + ($this->min_size / 5));
      }
      @ImageJPEG($image, '', $this->jpegquality);
      @ImageDestroy($image);

      exit();
			//echo('<img border="0" src="'.$this->url().'">');
      
		}

		function make_websafe_colors(&$image)	{

			//$a = array();
			for($r = 0; $r <= 255; $r += 51) {
				for($g = 0; $g <= 255; $g += 51) {
					for($b = 0; $b <= 255; $b += 51) {
						$color = imagecolorallocate($image, $r, $g, $b);
					}
				}
			}

    }

		function random_color($min,$max) {
      
			srand((double)microtime() * 1000000);
			$this->r = intval(rand($min,$max));
			srand((double)microtime() * 1000000);
			$this->g = intval(rand($min,$max));
			srand((double)microtime() * 1000000);
			$this->b = intval(rand($min,$max));
      
		}

    function random_font() {

      srand((float)microtime() * 10000000);
      $key = array_rand($this->fonts);
      $font_name = $this->fonts[$key];
      return $this->fonts_folder.$font_name;
      
    }
    
		function url() {
      
      //return '?'.ITT_CAPTCHA_PARAM_MODULE.'='.ITT_CAPTCHA_MODULE_NAME.'&'.ITT_CAPTCHA_PARAM_ACTION.'='.ITT_CAPTCHA_ACTION_GET . '&' . ITT_CAPTCHA_PARAM_CODE . '=' . $this->value;
			return '?'.ITT_CAPTCHA_PARAM_MODULE.'='.ITT_CAPTCHA_MODULE_NAME.'&'.ITT_CAPTCHA_PARAM_ACTION.'='.ITT_CAPTCHA_ACTION_GET . '&' . ITT_CAPTCHA_PARAM_CODE . '=' . base64_encode($this->xorShipher(strtolower($this->value), 'JHkfekf'));
      
		}
    
		function get_gd_version() {

			static $gd_version_number = null;
			if ($gd_version_number === null) {
 		    ob_start();
			  phpinfo(8);
			  $module_info = ob_get_contents();
			  ob_end_clean();
			  if (preg_match("/\bgd\s+version\b[^\d\n\r]+?([\d\.]+)/i", $module_info, $matches)) {
				  $gd_version_number = $matches[1];
			  } else {
				   $gd_version_number = 0;
		    }
			}
			return $gd_version_number;
		}
    
    function init_custom_parameters($parameters = array()) {
      
      if (count($parameters)) {
        foreach ($parameters as $pname => $pvalue) {
          $_SESSION[ITT_CAPTCHA_SESSION_TAG_PARAMS][$pname] = $pvalue;
        } 
      }

    }
    
    function unset_custom_parameters() {
      
      unset($_SESSION[ITT_CAPTCHA_SESSION_TAG_PARAMS]);

    }
    
    function set_custom_parameters() {
      
      $parameters = session(ITT_CAPTCHA_SESSION_TAG_PARAMS);
      if (safe($parameters, 'chars')) {
        $this->chars = safe($parameters, 'chars');
      }
      if (safe($parameters, 'width')) {
        $this->lx = safe($parameters, 'width');
      }
      if (safe($parameters, 'height')) {
        $this->ly = safe($parameters, 'height');
      }
      if (safe($parameters, 'min_size')) {
        $this->min_size = safe($parameters, 'min_size');
      }
      if (safe($parameters, 'max_size')) {
        $this->max_size = safe($parameters, 'max_size');
      }
      if (safe($parameters, 'max_rotation')) {
        $this->max_rotation = safe($parameters, 'max_rotation');
      }
      if (safe($parameters, 'noise')) {
        $this->noise = safe($parameters, 'noise');
      }
      if (safe($parameters, 'noise_factor')) {
        $this->noise_factor = safe($parameters, 'noise_factor');
      }
      
      
      
    }
    
    function html2rgb($color) {
      if ($color[0] == '#')
          $color = substr($color, 1);

      if (strlen($color) == 6)
          list($r, $g, $b) = array($color[0].$color[1],
                                   $color[2].$color[3],
                                   $color[4].$color[5]);
      elseif (strlen($color) == 3)
          list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
      else
          return false;

      $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

      return array($r, $g, $b);
    }
    
    function xorShipher($string,$password)
    {
      //simple xor
      $str_len = strlen($string);
      $gamma = '';
      $seq = '';
      while (strlen($gamma)< $str_len)
      {
        $seq = pack("H*",sha1($gamma.$password));
        $gamma.=substr($seq,0,8);
      }
      return $string^$gamma;
    }    

}

?>