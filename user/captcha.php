<?php
//error_reporting(0);
session_start();

function hex2rbg($color) {
	$color_array = array();
	$hex_color = strtoupper($color);
	$hex_color = str_replace('#','',$hex_color);
	for($i=0; $i<6; $i++) {
		$hex = substr($hex_color,$i,1);
		switch($hex) {
			case "A": $num = 10; break;
			case "B": $num = 11; break;
			case "C": $num = 12; break;
			case "D": $num = 13; break;
			case "E": $num = 14; break;
			case "F": $num = 15; break;
			default: $num = $hex; break;
		}
		array_push($color_array,$num);
	}
	$R = (($color_array[0] * 16) + $color_array[1]);
	$G = (($color_array[2] * 16) + $color_array[3]);
	$B = (($color_array[4] * 16) + $color_array[5]);
	return array($R,$G,$B);
}

function getAnyColorFrom($array_of_colours) {
	global $image;
	
	$color = $array_of_colours[rand(0,count($array_of_colours) - 1)];
	list($r,$b,$g) = hex2rbg($color);
	return imagecolorallocate($image, $r, $b, $g);
}

function getRandomString() {
	//Generate a key with which the text will be made.
	$charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$pass = str_shuffle($charset);
	$text = substr($pass,0,rand(3,6));
	return $text;

}

//Configurations.
$text = getRandomString();
$_SESSION['captcha_key'] = $text;

$font_size = 22;

$width = 170;
$height = 50;

$start_x = 10;
$incre = 17;

$fonts = array('arial.ttf','tahoma.ttf','verdana.ttf','times.ttf','georgia.ttf');
$use_default_fonts = 1;

$rand_color_start= 0;
$rand_color_end  = 255;

$y = 30;

//Some Random colours
$light_colours = array('#f29eff','#3bfff8','#f0ff1a','#ffdc51','#ffa0ad','#fff1f9','#eaffe5','#dbdbdb','#fbcbf8',
'#f8d6fb','#fbf59d','#bbfbc8','#fbd6dc','#fbeab5','#fbbbd7','#8ef7fb','#fbfbfb','#ffffff','#e8fb9d');
$dark_colours = array('#fb0c68','#bc34fb','#611b82','#07821a','#ff1a4f','#120974','#120974','#33862d','#86140e',
'#865845','#528646','#3b2c86','#821886','#bc22c1','#1e39c1','#0b1545','#767676','#000000','#662c2d');

//If the background is a light color, the text color must be dark and vice versa.
$dark_or_light_choice = rand(0,1);
if($dark_or_light_choice) {
	$background_colors = $light_colours;
	$text_colors = $dark_colours;
	
} else {
	$background_colors = $dark_colours;
	$text_colors = $light_colours;
}

// Create a image
$image = imagecreatetruecolor($width, $height);

// Background 
$bgcolor   = getAnyColorFrom($background_colors);
imagefill($image,10,10,$bgcolor);

$dark_or_light = ($dark_or_light_choice) ? 250 : 50;
$shadow_color = imagecolorallocate($image, $dark_or_light, $dark_or_light, $dark_or_light);

//Add some unique effect for every letter.
for($i=0;$i<strlen($text);$i++) {
	$letter = $text[$i];
	
	$x = (imagefontwidth( $font_size ) + $incre) * $i + $start_x;
	
	//Randomizations
	$textcolor = getAnyColorFrom($text_colors);//Randomize the Colours for each letter.
	$y_changed = $y + rand(-5,5); //Randomize location
	$angle = rand(-15,15); //Randomize Angle.
	//Randomize Fonts
	$random_font_index = rand(0,count($fonts)-1);
	$font = $fonts[$random_font_index];

	if($use_default_fonts) {
		$y_changed -= 10;
		imagestring($image, 8, $x+2, $y_changed+2, $letter, $shadow_color);
		imagestring($image, 9, $x, $y_changed, $letter, $textcolor);
	} else {
		print $font;
		imagettftext($image, $font_size, $angle, $x+3, $y_changed+3, $shadow_color, $font, $letter); //Create a shadow effect
		imagettftext($image, $font_size, $angle, $x, $y_changed, $textcolor, $font, $letter);
	}
}

header("Content-type: image/png");
imagepng($image);
