<?php
function render($file='', $use_layout=true, $use_exact_path = false) {
	//If it is an ajax request, we don't have to render the page.
	if(isset($_REQUEST['ajax'])) {
		print '{"success":"Done","error":false}';
		return;
	}

	//Otherwise, render it.
	$GLOBALS['template']->render($file, $use_layout, $use_exact_path);
}

/**
 * This function makes sure that the appearance of the pager remains consistant accross the pages
 */
function showPager() {
	global $pager,$abs;
	$pager->printPager();
}

//////////////////////// Layout Functions - DEPRECATED ///////////////////
function showHead($title='') {
	global $template, $config;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<title><?php echo $title; ?></title>
<link href="<?php echo joinPath($config['site_url'],'css/style.css')?>" rel="stylesheet" type="text/css" />
<link href="<?php echo joinPath($config['site_url'],'images/silk_theme.css')?>" rel="stylesheet" type="text/css" />
<?php echo implode("\n", $template->css_includes);?>
<?php
}


function showBegin() {
	global $config, $QUERY;
?>
</head>
<body>
<?php if(isset($config['site_title'])) { ?>
<div id="header">
<h1 id="logo"><a href="<?php echo $config['site_url']?>"><?php echo $config['site_title']?></a></h1>
</div>
<?php } ?>

<div id="content">
<div id="error-message" <?php echo ($QUERY['error']) ? '':'style="display:none;"';?>><?php
	if(isset($PARAM['error'])) print strip_tags($PARAM['error']); //It comes from the URL
	else print $QUERY['error']; //Its set in the code(validation error or something.
?></div>
<div id="success-message" <?php echo ($QUERY['success']) ? '':'style="display:none;"';?>><?php echo strip_tags(stripslashes($QUERY['success']))?></div>
<!-- Begin Content -->
<?php
}

function showTop($title='') {
	showHead($title);
	showBegin();
}

function showEnd() {
	global $template, $config;
?>
<!-- End Content -->
</div>

<script src="<?php echo joinPath($config['site_url'],'js/library/jsl.js')?>" type="text/javascript"></script>
<script src="<?php echo joinPath($config['site_url'],'js/application.js')?>" type="text/javascript"></script>
<?php echo implode("\n", $template->js_includes);?>
</body>
</html>
<?php }
