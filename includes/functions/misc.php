<?php
/**
 * Create a link by joining the given URL and the parameters given as the second argument.
 * Arguments :  $url - The base url.
 *				$params - An array containing all the parameters and their values.
 *				$use_existing_arguments - Use the parameters that are present in the current page
 * Return : The new url.
 * Example : 
 *			getLink("http://www.google.com/search",array("q"=>"binny","hello"=>"world","results"=>10));
 *					will return
 *			http://www.google.com/search?q=binny&hello=world&results=10
 */
function getLink($url,$params=array(),$use_existing_arguments=false) {
	if(!$params and !$use_existing_arguments) return $url;
	if($use_existing_arguments) $params = $params + $_GET;
	
	$link = $url;
	
	if(strpos($link,'?') === false) {
		$existing_parameters = array();
	} else { // This will make sure that even if the specified param exists in the given url, it will be over written.
		$url_parts = explode('?', $url);
		$link = $url_parts[0];
		$existing_parameters = array();
		
		if($url_parts[1]) {
			$all_url_parameters = preg_split("/\&(amp\;)?/", $url_parts[1]);
			foreach($all_url_parameters as $part) {
				list($name, $value) = explode("=", $part);
				$existing_parameters[$name] = $value;
			}
		}
	}
	if($existing_parameters) $params = $params + $existing_parameters;
	
	$params_arr = array();
	foreach($params as $key=>$value) {
		if($value === null) continue; // If the value is given as null, don't show it in the query at all. Use arg=>"null" if you want a string null in the query.
		if($use_existing_arguments) {// Success or Error message don't have to be shown.
			if(($key == 'success' and isset($_GET['success']) and $_GET['success'] == $value)
			 	or ($key == 'error' and isset($_GET['error']) and $_GET['error'] == $value)) continue;
		}
		
		if(gettype($value) == 'array') { //Handle array data properly
			foreach($value as $val) {
				$params_arr[] = $key . '[]=' . urlencode($val);
			}
		} else {
			$params_arr[] = $key . '=' . urlencode($value);
		}
	}
	if($params_arr) $link = $link . '?' . implode('&amp;',$params_arr);
	
	return $link;
}

/**
 * Arguments :  $conditions - An array containing all the validaiton information.
 *				$show(Integer) - The value given here decides how the data should be returned - or printed.[OPTIONAL]
 *						1 = Prints the errors as an HTML List
 *						2 = Return the errors as a string(HTML list)
 *						3 = Return the errors as an array 
 *						4 = Return the errors as an array with field name as the key.
 *						Defaults to 1
 * Super powerful validaiton script for form fields. I may make this a class to do both serverside and 
 *			client side validaiton - both in the same package
 * :TODO: This function is not fully tested. It is not even partaily tested.
 * :TODO: Documentation needed desperatly
 * :TODO: Change this function to a class.
 The first argument - $conditions is an array with all the validaiton rule
 Each element of the array is one rule.
 Each rule is an associative array. The following keys are supported

 name	: The name of the field that should be checked. ($_REQUEST['date'] - here the name is 'date')
 is		: What sort data should MAKE AN ERROR. If the given type is found as the field value, an error will be raised. Example 'empty'
 title	: The human friendly name for the field (eg. 'Date of Birth')
 error  : The message that should be shown if there is a validation error
 value	: The programmer provided value. Some rules must have an additional value to be matched against. For example the '<' condition must have a value - the user inputed value and the value given in this index will be compared
 when	: This is a method to short-circut the validation. If this is false, or '0' validaiton will NOT take place. The rule will just be ignored.
 
 Example :
 $conditions = array(
 	array(
 		'name'	=>	'username',
 		'is'	=>	'empty',
 		'error' =>	'Please provide a valid username'
 	),
 	array(
 		'name'	=>	'username',
 		'is'	=>	'length<',
 		'value'	=> 	3,
 		'error' =>	'Make sure that then username has atleast 3 chars'
 	)
 )
 */
function check($conditions,$show=1) {
	$errors = array();
	$field_errors = array();
	foreach($conditions as $cond) {
		unset($title,$default_error,$error,$when,$input,$is,$value,$name,$value_field);
		extract($cond);

		if(!isset($title))$title= format($name);
		if(!isset($name)) $name = unformat($title);
		$input = '';
		if(!empty($_REQUEST[$name])) $input = $_REQUEST[$name];
		if(isset($value_field)) {
			$value = $_REQUEST[$value_field];
		}
		
		$default_error = "Error in '$title' field!";
		if(!isset($error)) $error = $default_error;
		
		if(isset($when)) {
			if(($when === 0) or ($when === false)) {//Ok - don't validate this field - ignore erros if any
				continue;
			} else if ($when != "") { //When error
				$errors[] = $error;
			}
		}

		switch($is) {
			case 'empty':
				if(!$input) {
					if($error == $default_error) $error = "The $title is not provided";
					$field_errors[$name][] = $error;
				}
			break;
			case 'not':
				if($error == $default_error) $error = "The $title should be '$value'";
				if($input != $value) $field_errors[$name][] = $error;
			break;
			case 'equal':
				if($error == $default_error) $error = "The $title should field must not be '$value'";
				if($input == $value) $field_errors[$name][] = $error;
			break;
			
			//Numeric Checks			
			case '>':
			case 'greater':
				if($input > $value) $field_errors[$name][] = $error;
			break;
			case '<':
			case 'lesser':
				if($input < $value) $field_errors[$name][] = $error;
			break;
			
			//Length Checks
			case 'length<':
				if(strlen($input) < $value) $field_errors[$name][] = $error;
			break;
			case 'length>':
				if(strlen($input) > $value) $field_errors[$name][] = $error . $value . ' : ' . strlen($input);
			break;

			case 'nan':
			case 'not_number': //Warning: Decimals will get through
				if($input and !is_numeric($input)) {
					$field_errors[$name][] = "The " . $title . " should be a number";
			}
			break;
			
			case 'not_email': //If the field does not match the email regexp, an error is shown
				if(!preg_match('/^[\w\-\.]+\@[\w\-\.]+\.[a-z\.]{2,5}$/',$input)) {
					if($title) $error = "The " . $title . " should be a valid email address";
					else $error = "Invalid Email address provided";
					$field_errors[$name][] = $error;
				}
				break;
			case 'has_weird': //Check for weird chars
				if(!preg_match('/^[\w\-]*$/',$input)) {
					if($title) $error = "The " . $title ." should not have weird characters";
					else $error = "Weird characters where found in the input";
					$field_errors[$name][] = $error;
				}
				break;
			case 'not_name': //Check for chars that cannot appear in a title
				if(!preg_match("/^[\w\'\(\)\,\.\/ ]*$/",$input)) {
					if($title) $error = "The " . $title ." has invalid characters";
					else $error = "Invalid characters where found in the input";
					$field_errors[$name][] = $error;
				}
				break;

			//RegExp
			case 'dont_match':
			case 'not_match':
			case '!match':
				if(!preg_match("/$value/",$input)) $field_errors[$name][] = $error;
			break;
			case 'match':
				if(preg_match("/$value/",$input)) $field_errors[$name][] = $error;
			break;
		}
	}
	
	//Put all errors into one array
	if($field_errors) {
		foreach($field_errors as $name=>$arr) {
			$errors = array_merge($errors,$arr);
		}
		$errors = array_values(array_diff($errors,array('')));
	}
	
	if(!$errors) return '';

	$error_message = "<ul class='validation-errors'>\n<li>";
	$error_message .= implode( "</li>\n<li>",$errors );
	$error_message .= "</li>\n</ul>";
	
	if($show == 1) {//Just show the errors as one list if the user wants it so
		print $error_message;

	} else if($show == 2) { //Return the errors as a string(HTML list)
		return $error_message;

	} else if($show == 3) {//Return the errors as an array
		return $errors;
	
	} else { //Return the errors as a array with field information
		return $field_errors;
	}
}

/**
 * Converts a given PHP array to its eqvalent JSON String
 * Argument : $arr - The PHP array
 * Return : (String) - The JSON String.
 * Link : http://www.bin-co.com/php/scripts/array2json/
 */
function array2json($arr) {
	if(function_exists('json_encode')) return json_encode($arr);
	
	$parts = array();
	$is_list = false;

	if(!is_array($arr)) return $arr;

	//Find out if the given array is a numerical array
	$keys = array_keys($arr);
	$max_length = count($arr)-1;
	if(($keys[0] == 0) and ($keys[$max_length] == $max_length)) {//See if the first key is 0 and last key is length - 1
		$is_list = true;
		for($i=0; $i<count($keys); $i++) { //See if each key correspondes to its position
			if($i != $keys[$i]) { //A key fails at position check.
				$is_list = false; //It is an associative array.
				break;
			}
		}
	}

	foreach($arr as $key=>$value) {
		if(is_array($value)) { //Custom handling for arrays
			if($is_list) $parts[] = array2json($value); /* :RECURSION: */
			else $parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */
		} else {
			$str = '';
			if(!$is_list) $str = '"' . $key . '":';

			//Custom handling for multiple data types
			if(is_numeric($value)) $str .= $value; //Numbers
			elseif($value === false) $str .= 'false'; //The booleans
			elseif($value === true) $str .= 'true';
			else $str .= '"' . addslashes($value) . '"'; //All other things
			// :TODO: Is there any more datatype we should be in the lookout for? (Object?)

			$parts[] = $str;
		}
	}
	$json = implode(',',$parts);
	
	if($is_list) return '[' . $json . ']';//Return numerical JSON
	return '{' . $json . '}';//Return associative JSON
}

/**
 * A function for easily uploading files. This function will automatically generate a new 
 *		file name so that files are not overwritten.
 * Arguments:	 $file_id - The name of the input field contianing the file.
 *				$folder  - The folder to which the file should be uploaded to - it must be writable. OPTIONAL
 *				$types   - A list of comma(,) seperated extensions that can be uploaded. If it is empty, anything goes OPTIONAL
 * Returns  : This is somewhat complicated - this function returns an array with two values...
 *				The first element is randomly generated filename to which the file was uploaded to.
 *				The second element is the status - if the upload failed, it will be 'Error : Cannot upload the file 'name.txt'.' or something like that
 */
function upload($file_id, $folder="", $types="") {
	if(!$_FILES[$file_id]['name']) return array('','No file specified');

	$file_title = $_FILES[$file_id]['name'];
	//Get file extension
	$ext_arr = explode(".",basename($file_title));
	$ext = strtolower($ext_arr[count($ext_arr)-1]); //Get the last extension

	//Not really uniqe - but for all practical reasons, it is
	$uniqer = substr(md5(uniqid(rand(),1)),0,5);
	$file_name = $uniqer . '_' . $file_title;//Get Unique Name

	$all_types = explode(",",strtolower($types));
	if($types) {
		if(in_array($ext,$all_types));
		else {
			$result = "'".$_FILES[$file_id]['name']."' is not a valid file."; //Show error if any.
			return array('',$result);
		}
	}

	//Where the file must be uploaded to
	if($folder) $folder .= '/';//Add a '/' at the end of the folder
	$uploadfile = $folder . $file_name;

	$result = '';
	//Move the file from the stored location to the new location
	if (!move_uploaded_file($_FILES[$file_id]['tmp_name'], $uploadfile)) {
		$result = "Cannot upload the file '".$_FILES[$file_id]['name']."'"; //Show error if any.
		if(!file_exists($folder)) {
			$result .= " : Folder don't exist.";
		} elseif(!is_writable($folder)) {
			$result .= " : Folder not writable.";
		} elseif(!is_writable($uploadfile)) {
			$result .= " : File not writable.";
		}
		$file_name = '';
		
	} else {
		if(!$_FILES[$file_id]['size']) { //Check if the file is made
			@unlink($uploadfile);//Delete the Empty file
			$file_name = '';
			$result = "Empty file found - please use a valid file."; //Show the error message
		} else {
			chmod($uploadfile,0777);//Make it universally writable.
		}
	}

	return array($file_name,$result);
}

/**
 * Function  : sendEMail()
 * Agruments : $from - don't make me explain these
 *			  $to
 *			  $message
 *			  $subject 
 * Sends an email with the minimum amount of fuss.
 */
function sendEMail($from_email,$to,$message,$subject) {
	global $config;
	
	$from_name = $config['site_title'];
	$site = $config['site_url'];
	if(!$from_email) $from_email = $config['site_email'];
	
	/*Clean The mail of BCC Header Injections before sending the mail*/
	//Code taken from http://in.php.net/manual/en/ref.mail.php#59012

	// Attempt to defend against header injections: 
	$badStrings = array("Content-Type:", 
						"MIME-Version:", 
						"Content-Transfer-Encoding:", 
						"bcc:", 
						"cc:"); 
	
	// Loop through each POST'ed value and test if it contains 
	// one of the $badStrings: 
	foreach($_POST as $k => $v){ 
		foreach($badStrings as $v2){ 
			if(strpos($v, $v2) !== false){ 
				header("HTTP/1.0 403 Forbidden"); 
				exit; 
			} 
		} 
	}	 
	/*******************************************************************************/
	$from_str = "$from_name <$from_email>";
	
	if(strpos($message,"<br")===false) { //A Plain Text message
		$type = "text/plain";
	} else { //HTML message
		$type = "text/html";
	}

	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: $type; charset=iso-8859-1\r\n";
	$headers .= "From: $from_str";
	
	if(mail($to,$subject,$message,$headers)) return true;
	else return false;
}
 
/**
 * Link: http://www.bin-co.com/php/scripts/load/
 * Version : 2.00.A
 */
function load($url,$options=array()) {
	$default_options = array(
		'method'		=> 'get',
		'post_data'		=> array(),		// The data that must be send to the URL as post data.
		'return_info'	=> false,		// If true, returns the headers, body and some more info about the fetch.
		'return_body'	=> true,		// If false the function don't download the body - useful if you just need the header or last modified instance.
		'cache'			=> false,		// If true, saves a copy to a local file - so that the file don't have multiple times.
		'cache_folder'	=> '/tmp/php-load-function/', // The folder to where the cache copy of the file should be saved to.
		'cache_timeout'	=> 0,			// If the cached file is older that given time in minutes, it will download the file again and cache it.
		'referer'		=> '',			// The referer of the url.
		'headers'		=> array(),		// Custom headers
		'session'		=> false,		// If this is true, the following load() calls will use the same session - until load() is called with session_close=true.
		'session_close'	=> false,
	);
	// Sets the default options.
	foreach($default_options as $opt=>$value) {
		if(!isset($options[$opt])) $options[$opt] = $value;
	}

	$url_parts = parse_url($url);
	$ch = false;
	$info = array(//Currently only supported by curl.
		'http_code'	=> 200
	);
	$response = '';
	
	
	$send_header = array(
		'User-Agent' => 'BinGet/1.00.A (http://www.bin-co.com/php/scripts/load/)'
	) + $options['headers']; // Add custom headers provided by the user.
	
	if($options['cache']) {
		$cache_folder = $options['cache_folder'];
		if(!file_exists($cache_folder)) {
			$old_umask = umask(0); // Or the folder will not get write permission for everybody.
			mkdir($cache_folder, 0777);
			umask($old_umask);
		}
		
		$cache_file_name = md5($url) . '.cache';
		$cache_file = joinPath($cache_folder, $cache_file_name); //Don't change the variable name - used at the end of the function.
		
		if(file_exists($cache_file) and filesize($cache_file) != 0) { // Cached file exists - return that.
			$timedout = false;
			if($options['cache_timeout']) {
				if(((time() - filemtime($cache_file)) / 60) > $options['cache_timeout']) $timedout = true;  // If the cached file is older than the timeout value, download the URL once again.
			}
			
			if(!$timedout) {
				$response = file_get_contents($cache_file);
				
				//Seperate header and content
				$seperator_charector_count = 4;
				$separator_position = strpos($response,"\r\n\r\n");
				if(!$separator_position) {
					$separator_position = strpos($response,"\n\n");
					$seperator_charector_count = 2;
				}
				// If the real seperator(\r\n\r\n) is NOT found, search for the first < char.
				if(!$separator_position) {
					$separator_position = strpos($response,"<"); //:HACK:
					$seperator_charector_count = 0;
				}
				
				$body = '';
				$header_text = '';
				if($separator_position) {
					$header_text = substr($response,0,$separator_position);
					$body = substr($response,$separator_position+$seperator_charector_count);
				}
				
				foreach(explode("\n",$header_text) as $line) {
					$parts = explode(": ",$line);
					if(count($parts) == 2) $headers[$parts[0]] = chop($parts[1]);
				}
				$headers['cached'] = true;
				
				if(!$options['return_info']) return $body;
				else return array('headers' => $headers, 'body' => $body, 'info' => array('cached'=>true));
			}
		}
	}

	///////////////////////////// Curl /////////////////////////////////////
	//If curl is available, use curl to get the data.
	if(function_exists("curl_init") 
				and (!(isset($options['use']) and $options['use'] == 'fsocketopen'))) { //Don't use curl if it is specifically stated to use fsocketopen in the options
		
		if(isset($options['post_data']) and $options['post_data']) { //There is an option to specify some data to be posted.
			$page = $url;
			$options['method'] = 'post';
			
			if(is_array($options['post_data'])) { //The data is in array format.
				$post_data = array();
				foreach($options['post_data'] as $key=>$value) {
					if($value)  $post_data[] = "$key=" . urlencode($value);
					else $post_data[] = $key;
				}
				$url_parts['query'] = implode('&', $post_data);
			
			} else { //Its a string
				$url_parts['query'] = $options['post_data'];
			}
		} else {
			if(isset($options['method']) and $options['method'] == 'post') {
				$page = $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'];
			} else {
				$page = $url;
			}
		}

		if($options['session'] and isset($GLOBALS['_binget_curl_session'])) $ch = $GLOBALS['_binget_curl_session']; //Session is stored in a global variable
		else $ch = curl_init($url_parts['host']);
		
		curl_setopt($ch, CURLOPT_URL, $page) or die("Invalid cURL Handle Resouce");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Just return the data - not print the whole thing.
		curl_setopt($ch, CURLOPT_HEADER, true); //We need the headers
		curl_setopt($ch, CURLOPT_NOBODY, !($options['return_body'])); //The content - if true, will not download the contents. There is a ! operation - don't remove it.
		if(isset($options['encoding'])) curl_setopt($ch, CURLOPT_ENCODING, $options['encoding']); // Used if the encoding is gzip.
		if(isset($options['method']) and $options['method'] == 'post' and isset($url_parts['query'])) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $url_parts['query']);
		}
		//Set the headers our spiders sends
		curl_setopt($ch, CURLOPT_USERAGENT, $send_header['User-Agent']); //The Name of the UserAgent we will be using ;)
		unset($send_header['User-Agent']);
		
		$custom_headers = array();
		foreach($send_header as $key => $value) $custom_headers[] = "$key: $value";
		if(isset($options['modified_since']))
			$custom_headers[] = "If-Modified-Since: ".gmdate('D, d M Y H:i:s \G\M\T',strtotime($options['modified_since']));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);
		if($options['referer']) curl_setopt($ch, CURLOPT_REFERER, $options['referer']);

		curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/binget-cookie.txt"); //If ever needed...
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if(isset($url_parts['user']) and isset($url_parts['pass']))
			$custom_headers[] = "Authorization: Basic ".base64_encode($url_parts['user'].':'.$url_parts['pass']);
	   
		if($custom_headers) curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);
		$response = curl_exec($ch);
		$info = curl_getinfo($ch); //Some information on the fetch
		
		if($options['session'] and !$options['session_close']) $GLOBALS['_binget_curl_session'] = $ch; //Dont close the curl session. We may need it later - save it to a global variable
		else curl_close($ch);  //If the session option is not set, close the session.

	//////////////////////////////////////////// FSockOpen //////////////////////////////
	} else { //If there is no curl, use fsocketopen - but keep in mind that most advanced features will be lost with this approch.
		if(isset($url_parts['query'])) {
			if(isset($options['method']) and $options['method'] == 'post')
				$page = $url_parts['path'];
			else
				$page = $url_parts['path'] . '?' . $url_parts['query'];
		} else {
			$page = $url_parts['path'];
		}
		
		if(!isset($url_parts['port'])) $url_parts['port'] = 80;
		$fp = fsockopen($url_parts['host'], $url_parts['port'], $errno, $errstr, 30);
		if ($fp) {
			$out = '';
			if(isset($options['method']) and $options['method'] == 'post' and isset($url_parts['query'])) {
				$out .= "POST $page HTTP/1.1\r\n";
			} else {
				$out .= "GET $page HTTP/1.0\r\n"; //HTTP/1.0 is much easier to handle than HTTP/1.1
			}
			$out .= "Host: $url_parts[host]\r\n";
			if(isset($send_header['Accept'])) $out .= "Accept: $send_header[Accept]\r\n";
			$out .= "User-Agent: {$send_header['User-Agent']}\r\n";
			if(isset($options['modified_since']))
				$out .= "If-Modified-Since: ".gmdate('D, d M Y H:i:s \G\M\T',strtotime($options['modified_since'])) ."\r\n";

			$out .= "Connection: Close\r\n";
			
			//HTTP Basic Authorization support
			if(isset($url_parts['user']) and isset($url_parts['pass'])) {
				$out .= "Authorization: Basic ".base64_encode($url_parts['user'].':'.$url_parts['pass']) . "\r\n";
			}

			//If the request is post - pass the data in a special way.
			if(isset($options['method']) and $options['method'] == 'post' and $url_parts['query']) {
				$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$out .= 'Content-Length: ' . strlen($url_parts['query']) . "\r\n";
				$out .= "\r\n" . $url_parts['query'];
			}
			$out .= "\r\n";

			fwrite($fp, $out);
			while (!feof($fp)) {
				$response .= fgets($fp, 128);
			}
			fclose($fp);
		}
	}

	//Get the headers in an associative array
	$headers = array();

	if($info['http_code'] == 404) {
		$body = "";
		$headers['Status'] = 404;
	} else {
		//Seperate header and content
		$header_text = '';
		$body = $response;
		if(isset($info['header_size'])) {
		  $header_text = substr($response, 0, $info['header_size']);
		  $body = substr($response, $info['header_size']);
		} else {
			$header_text = reset(explode("\r\n\r\n", trim($response)));
			$body = str_replace($header_text."\r\n\r\n", '', $response);
		}		
		
		// If there is a redirect, there will be multiple headers in the response. We need just the last one.
		$header_parts = explode("\r\n\r\n", trim($header_text));
		$header_text = end($header_parts);
		
		foreach(explode("\n",$header_text) as $line) {
			$parts = explode(": ",$line);
			if(count($parts) == 2) $headers[$parts[0]] = chop($parts[1]);
		}
		
		// :BUGFIX: :UGLY: Some URLs(IMDB has this issue) will do a redirect without the new Location in the header. It will be in the url part of info. If we get such a case, set the header['Location'] as info['url']
		if(!isset($header['Location']) and isset($info['url'])) {
			$header['Location'] = $info['url'];
			$header_text .= "\r\nLocation: $header[Location]";
		}
		
		$response = $header_text . "\r\n\r\n" . $body;
	}
	
	if(isset($cache_file)) { //Should we cache the URL?
		file_put_contents($cache_file, $response);
	}

	if($options['return_info']) return array('headers' => $headers, 'body' => $body, 'info' => $info, 'curl_handle'=>$ch);
	return $body;
} 


/**
 * This funtion will take a pattern and a folder as the argument and go thru it(recursivly if needed)and return the list of 
 *			   all files in that folder.
 * Link			 : http://www.bin-co.com/php/scripts/filesystem/ls/
 * Arguments	 :  $pattern - The pattern to look out for [OPTIONAL]
 *					$folder - The path of the directory of which's directory list you want [OPTIONAL]
 *					$recursivly - The funtion will traverse the folder tree recursivly if this is true. Defaults to false. [OPTIONAL]
 *					$options - An array of values 'return_files' or 'return_folders' or both
 * Returns	   : A flat list with the path of all the files(no folders) that matches the condition given.
 */
function ls($pattern="*", $folder="", $recursivly=false, $options=array('return_files','return_folders')) {
	if($folder) {
		$current_folder = realpath('.');
		if(in_array('quiet', $options)) { // If quiet is on, we will suppress the 'no such folder' error
			if(!file_exists($folder)) return array();
		}
		
		if(!chdir($folder)) return array();
	}
	
	$get_files	= in_array('return_files', $options);
	$get_folders= in_array('return_folders', $options);
	$both = array();
	$folders = array();
	
	// Get the all files and folders in the given directory.
	if($get_files) $both = glob($pattern, GLOB_BRACE + GLOB_MARK);
	if($recursivly or $get_folders) $folders = glob("*", GLOB_ONLYDIR + GLOB_MARK);
	
	//If a pattern is specified, make sure even the folders match that pattern.
	$matching_folders = array();
	if($pattern !== '*') $matching_folders = glob($pattern, GLOB_ONLYDIR + GLOB_MARK);
	
	//Get just the files by removing the folders from the list of all files.
	$all = array_values(array_diff($both,$folders));
		
	if($recursivly or $get_folders) {
		foreach ($folders as $this_folder) {
			if($get_folders) {
				//If a pattern is specified, make sure even the folders match that pattern.
				if($pattern !== '*') {
					if(in_array($this_folder, $matching_folders)) array_push($all, $this_folder);
				}
				else array_push($all, $this_folder);
			}
			
			if($recursivly) {
				// Continue calling this function for all the folders
				$deep_items = ls($pattern, $this_folder, $recursivly, $options); # :RECURSION:
				foreach ($deep_items as $item) {
					array_push($all, $this_folder . $item);
				}
			}
		}
	}
	
	if($folder) chdir($current_folder);
	return $all;
}

/**
 * xml2array() will convert the given XML text to an array in the XML structure.
 * Link: http://www.bin-co.com/php/scripts/xml2array/
 * Arguments : $contents - The XML text
 *				$get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
 *				$priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
 * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.
 * Examples: $array =  xml2array(file_get_contents('feed.xml'));
 * 			 $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute'));
 */
function xml2array($contents, $get_attributes=1, $priority = 'tag') {
	if(!$contents) return array();

	if(!function_exists('xml_parser_create')) {
		//print "'xml_parser_create()' function not found!";
		return array();
	}

	//Get the XML parser of PHP - PHP must have this module for the parser to work
	$parser = xml_parser_create('');
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, trim($contents), $xml_values);
	xml_parser_free($parser);

	if(!$xml_values) return;//Hmm...

	//Initializations
	$xml_array = array();
	$parents = array();
	$opened_tags = array();
	$arr = array();

	$current = &$xml_array; //Refference

	//Go through the tags.
	$repeated_tag_index = array();//Multiple tags with same name will be turned into an array
	foreach($xml_values as $data) {
		unset($attributes,$value);//Remove existing values, or there will be trouble
		
		//This command will extract these variables into the foreach scope
		// tag(string), type(string), level(int), attributes(array).
		extract($data);//We could use the array by itself, but this cooler.

		$result = array();
		$attributes_data = array();
		
		if(isset($value)) {
			if($priority == 'tag') $result = $value;
			else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
		}

		//Set the attributes too.
		if(isset($attributes) and $get_attributes) {
			foreach($attributes as $attr => $val) {
				if($priority == 'tag') $attributes_data[$attr] = $val;
				else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
			}
		}

		//See tag status and do the needed.
		if($type == "open") {//The starting of the tag '<tag>'
			$parent[$level-1] = &$current;
			if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
				$current[$tag] = $result;
				if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
				$repeated_tag_index[$tag.'_'.$level] = 1;

				$current = &$current[$tag];

			} else { //There was another element with the same tag name

				if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
					$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
					$repeated_tag_index[$tag.'_'.$level]++;
				} else {//This section will make the value an array if multiple tags with the same name appear together
					$current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
					$repeated_tag_index[$tag.'_'.$level] = 2;
					
					if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
						$current[$tag]['0_attr'] = $current[$tag.'_attr'];
						unset($current[$tag.'_attr']);
					}

				}
				$last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
				$current = &$current[$tag][$last_item_index];
			}

		} elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
			//See if the key is already taken.
			if(!isset($current[$tag])) { //New Key
				$current[$tag] = $result;
				$repeated_tag_index[$tag.'_'.$level] = 1;
				if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

			} else { //If taken, put all things inside a list(array)
				if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

					// ...push the new element into that array.
					$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
					
					if($priority == 'tag' and $get_attributes and $attributes_data) {
						$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
					}
					$repeated_tag_index[$tag.'_'.$level]++;

				} else { //If it is not an array...
					if(!is_array($current)) $current = array($current);
					
					$current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
					$repeated_tag_index[$tag.'_'.$level] = 1;
					if($priority == 'tag' and $get_attributes) {
						if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
							
							$current[$tag]['0_attr'] = $current[$tag.'_attr'];
							unset($current[$tag.'_attr']);
						}
						
						if($attributes_data) {
							$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
						}
					}
					$repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
				}
			}

		} elseif($type == 'close') { //End of tag '</tag>'
			$current = &$parent[$level-1];
		}
	}
	
	return($xml_array);
}



/// Parses the given HTML string using the domDocument class and returns a dom node.
function parseHTML($html) {
	$dom = new domDocument;
	@$dom->loadHTML($html);
	$dom->preserveWhiteSpace = false;
	return $dom;
}

/**
 * Get DOM elements based on the given CSS Selector - V 1.00.A Beta
 * Direct port of http://www->openjs->com/scripts/dom/css_selector/
 */
function getElementsBySelector($all_selectors, $document) {
	$selected = array();
	
	$all_selectors = preg_replace(array('/^\s*([^\w])\s*$/', '/\s{2,}/'),array("$1", ' '), $all_selectors);//Remove the 'beutification' spaces
	$selectors = explode(",", $all_selectors);
	
	// COMMA:
	$comma_count = 0;
	foreach ($selectors as $selector) {
		$comma_count++;
		$context = array($document);
		$inheriters = explode(" ", $selector);

		// SPACE:
		$space_count = 0;
		foreach($inheriters as $element) {
			$space_count++;
			//This part is to make sure that it is not part of a CSS3 Selector
			$left_bracket = strpos($element, "[");
			$right_bracket= strpos($element, "]");
			$pos = strpos($element, "#"); //ID
			if($pos !== false and !($pos > $left_bracket and $pos < $right_bracket)) {
				$parts = explode("#", $element);
				$tag = $parts[0];
				$id = $parts[1];
				$ele = false;
				
				//$ele = $document->getElementById($id); // Does'nt work - PHP bug, I guess.
				$all = getElementsBySelectorGetElements($context, $tag);
				foreach($all as $eles) {
					if($eles->getAttribute("id") == $id) {
						$ele = $eles;
						break;
					}
				}
				
				if(!$ele or ($tag and strtolower($ele->nodeName) != $tag)) { //Specified element not found
					continue 2;
				}
				
				//If Id is the last element, return it as a single element and not as an array.
				if(count($inheriters) == $space_count and count($selectors) == $comma_count) return $ele;

				$context = array($ele);
				continue;
			}

			$pos = strpos($element, ".");//Class
			if($pos !== false and !($pos > $left_bracket and $pos < $right_bracket)) {
				$parts = explode('.', $element);
				$tag = $parts[0];
				$class_name = $parts[1];

				$found = getElementsBySelectorGetElements($context, $tag);
				$context = array();
				
 				foreach($found as $fnd) {
 					if(preg_match('/(^|\s)'.$class_name.'(\s|$)/', $fnd->getAttribute("class"))) $context[] = $fnd;
 				}
				continue;
			}

			if(strpos($element, '[') !== false) {//If the char '[' appears, that means it needs CSS 3 parsing
				// Code to deal with attribute selectors
				$tag = '';
				if (preg_match('/^(\w*)\[(\w+)([=~\|\^\$\*]?)=?[\'"]?([^\]\'"]*)[\'"]?\]$/', $element, $matches)) {
					$tag = $matches[1];
					$attr = $matches[2];
					$operator = $matches[3];
					$value = $matches[4];
				}
				$found = getElementsBySelectorGetElements($context, $tag);
				$context = array();
				foreach ($found as $fnd) {
 					if($operator == '=' and $fnd->getAttribute($attr) != $value) continue;
					if($operator == '~' and !preg_match('/(^|\\s)'.$value.'(\\s|$)/', $fnd->getAttribute($attr))) continue;
					if($operator == '|' and !preg_match('/^'.$value.'-?/', $fnd->getAttribute($attr))) continue;
					if($operator == '^' and strpos($value, $fnd->getAttribute($attr)) === false) continue;
					if($operator == '$' and strrpos($value, $fnd->getAttribute($attr)) != (strlen($fnd->getAttribute($attr)) - strlen($value))) continue;
					if($operator == '*' and strpos($value, $fnd->getAttribute($attr)) !== false) continue;
					else if(!$fnd->getAttribute($attr)) continue;
					
					$context[] = $fnd;
 				}

				continue;
			}

			//Tag selectors - no class or id specified->
			$found = getElementsBySelectorGetElements($context,$element);
			$context = $found;
		}
		foreach($context as $con) $selected[] = $con;
	}
	return $selected;
}

// Grab all of the tagName elements within current context	
// Helper function for getElementsBySelector()
function getElementsBySelectorGetElements($context, $tag='*') {
	if(empty($tag)) $tag = '*';
	// Get elements matching tag, filter them for class selector
	$found = array();
	foreach ($context as $con) {
		$eles = $con->getElementsByTagName($tag);
		foreach($eles as $ele) $found[] = $ele;
	}
	return $found;
}
