<?php
/**
 * This file contains some iFrame specific functions.
 */

/**
 * Prints out an error message if there is an error
 * Arguments:
 * 	$msg - The error message
 *	$file - The file at which the error happened [OPTIONAL]
 *	$line - The line where the error occured [OPTIONAL]
 *	$priority - The priority or the error - if its to high(>=10) the app will die. 10 has more priority than 1
 */
function error($msg, $file="", $line="", $priority=5) {
	global $config,$abs;
	
	if($config['mode'] == 'd' or $config['mode'] == 't') {
		print <<<END
<link href="${abs}css/error.css" type="text/css" rel="stylesheet" />
<div class="error-message priority$priority">
<h1>Error!</h1>
<div id="message">$msg</div><br />
END;

		if($file and $line) {
			$line = $line - 1;
			print "In file '$file' at line $line..<br /><pre>";
			
			//Get the 5 lines surronding the error lines - before and after
			$lines = explode("\n",file_get_contents($file));
			for($i=$line-5; $i<$line+5; $i++) {
				if($i == $line) print '<span class="error-line">';
				print "\n<span class='line-number'>$i)</span> ";
				print str_replace(
					array('<',"\t"),
					array('&lt;','  '),
					$lines[$i]
				);//Trim it?
				if($i == $line) print '</span>';
			}
			print '</pre>';
		}
		print '</div>';
		exit();
	} else {
		if($priority >= 10) die($msg);
	}
}

/**
 * Shows the status of the system. If there is many success message, it will show up as a list. If there is just 1, 
 *		it shows as a div message. Same goes for error message - it uses a different classname. Success uses the classname
 *		'message-success' and Errors use the classname 'message-error'
 */
function showStatus() {
	global $QUERY;
	if($QUERY['success']) {
		if(is_array($QUERY['success'])) {
			print "<ul class='message-success'>\n";
			foreach($QUERY['success'] as $msg) print "<li>$msg</li>\n";
			print "</ul>\n";
		} else {
			print "<div class='message-success'>$QUERY[success]</div>\n";
		}
	}
	
	if($QUERY['error']) {
		if(is_array($QUERY['error'])) {
			print "<ul class='message-error'>\n";
			foreach($QUERY['error'] as $msg) print "<li>$msg</li>\n";
			print "</ul>\n";
		} else {
			print "<div class='message-error'>$QUERY[error]</div>\n";
		}
	}
}

/**
 * Shows the final message - redirects to a new page with the message in the URL
 */
function showMessage($message, $url='', $status="success",$extra_data=array(), $use_existing_params=true) {
	//If it is an ajax request, Just print the data
	if(isset($_REQUEST['ajax'])) {
		$success = '';
		$error = '';
		$insert_id = '';

		if($status == 'success') $success = $message;
		if($status == 'error' or $status == 'failure') $error = $message;

		$data = array(
			"success"	=> $success,
			"error"		=> $error
		) + $extra_data;

		print json_encode($data);

	} elseif(isset($_REQUEST['layout']) and $_REQUEST['layout']==='cli') {
		if($status === 'success') print $message . "\n";

	} else {
		if(!$url) {
			global $QUERY;
			$QUERY[$status] = $message;
			return;
		}
	
		if(strpos($url, 'http://') === false) {
			global $config;
			$url = joinPath($config['site_url'], $url);
		}
		
		$goto = str_replace('&amp;', '&', getLink($url, array($status=>$message) + $extra_data, $use_existing_params));
		header("Location:$goto");
	}
	exit;
}

/**
 * Converts the given MySQL Date format to PHP date formatting string. %Y-%m-%d becomes Y-m-d.
 */
function phpDateFormat($format_string) {
	$replace_rules = array(
		'%a' => 'D',
		'%b' => 'M',
		'%c' => 'n',
		'%D' => 'jS',
		'%e' => 'j',
		'%f' => 'u',
		'%j' => 'z',
		'%k' => 'G',
		'%l' => 'g',
		'%p' => 'A',
		'%r' => 'h:i:s A',
		'%S' => 's',
		'%T' => 'H:i:s',
		'%U' => 'W', // Limited functionality.
		'%u' => 'W',
		'%v' => 'W', // Limited functionality.
		'%V' => 'W', // Limited functionality.
		'%W' => 'l',
		'%'  => ''
	);

	return str_replace(array_keys($replace_rules), array_values($replace_rules), $format_string);
}

