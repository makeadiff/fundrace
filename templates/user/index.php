<h1><?=($name) ? "$name (Username: $username)" : $username?></h1>

<p>Member since <?=$added_on?>.</p>

<?php if($organization_id) { ?>
<a href="advertisements.php">Manage your Ads</a>
<?php } else { ?>
<h3>Your HTML Code</h3>
<textarea name="code" rows="5" cols="50">
&lt;script type="text/javascript" src="<?=$config['site_url']?>ad.js.php?user=<?=$id?>"&gt;&lt;/script&gt;
</textarea><br />

<h3>Ad Preference</h3>

<form action="" method="post">
<?php
$html->buildInput("ngos[]", "Show Ads from these NGOs<br />", 'select', '', array('options'=>$ngo_list,'multiple'=>'multiple', 'id'=>'ngos'));
$html->buildInput("action","","submit","Save Preference");
?>
</form>

<?php } ?>