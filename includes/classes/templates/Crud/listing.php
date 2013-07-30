<?php
global $config;
$html = new HTML;
$column_count = count($this->listing_fields);
?>
<h2><?php echo $this->title?></h2>

<div class="with-icon error" <?php echo ($this->error) ? '' : 'style="display:none;"';?>><?php echo $this->error?></div>
<div class="with-icon success" <?php echo ($this->success) ? '':'style="display:none;"';?>><?php echo $this->success?></div>

<?php if($this->allow['searching']) { ?>
<form name="search-form" method="post" action="">
<label for="search">Search</label>
<?php 
$html->buildInput('search', '');
if(count($this->search_fields) == 1) { // If there is only one field to search in, don't show the select box.
	$searh_keys = array_keys($this->search_fields);
	$html->buildInput('search_in','', 'hidden', $searh_keys[0]);
}
else $html->buildInput('search_in','', 'select', i($GLOBALS['PARAM'], 'search_in'), array('options'=>$this->search_fields));
$html->buildInput('action', '', 'submit', 'Search');
?>
</form>
<?php } ?>
 
<form name="display-form" id='display-form' method="post" action="">
<table class="data-table">
<tr class="header-row">
<?php if($this->allow['bulk_operations']) { $column_count++; ?>
<th class="header-select"><input id="selection-toggle" type="checkbox" value="" name="selection-toggle" /></th>
<?php } ?>
<?php foreach($this->listing_fields as $field_name) {
	print "<th>" . $this->fields[$field_name]['name'];
	if($this->allow['sorting'] and $this->fields[$field_name]['type'] != 'virtual') { //Links to Sort the data.
		print "<a href='".getLink($this->urls['main'], array("sortasc"=>$field_name, "sortdesc"=>null), true)."'><img src='" . $this->urls['image_folder'] . "up.png' alt='Sort Ascending' /></a>";
		print "<a href='".getLink($this->urls['main'], array("sortdesc"=>$field_name, "sortasc"=>null), true)."'><img src='" . $this->urls['image_folder'] . "down.png' alt='Sort Descending' /></a>";
	}
	
	print "</th>";
}

$action_colspan = 0;
if($this->allow['edit']) $action_colspan++;
if($this->allow['delete']) $action_colspan++;

if($action_colspan) {
	$column_count += $action_colspan;
?><th colspan="<?php echo $action_colspan?>">Action</th><?php } ?>
</tr>

<?php
$item_count = 0;
$sort_field = false;
foreach($this->current_page_data as $row) {
	$item_count++;
	$row_class = ($item_count%2) ? 'even' : 'odd';
	$id = $row[$this->primary_key];
	
	print '<tr class="item-row-'.$row_class.' '.$row_class.'">';
	
	if($this->allow['bulk_operations']) {
		print '<td class="item-select"><input type="checkbox" class="select-row" id="select_row_'.$id.'" value="'.$id.'" name="select_row[]" /></td>';
	}
	
	$field_count = 0;
	foreach($this->listing_fields as $field_name) {
		$field_count++;
		$f = $this->fields[$field_name];
		$value = $row[$field_name];
		print '<td>';
		
		// The Active/Deactive Status column.
		if($f['field_type'] == 'checkbox' and $f['value_type'] == 'status') {
			$toggle_action = 'activate';
			$status_class = 'deactive';
			$state = 'Disabled';
			if($value) {
				$toggle_action = 'deactivate';
				$status_class = 'active';
				$state = 'Enabled';
			}
			
			print "<a href='" . getLink($this->urls['main'], array('select_row[]'=>$id,'action'=>'toggle_status','field_name'=>$field_name), true) . "' title='".ucfirst($toggle_action)."' class='icon $status_class'>$state</a>";
		
		// The sorter...
		} elseif($f['value_type'] == 'sort') {
			$sort_field = $field_count;
			?>
<input type="hidden" name="sort_row_id[]" value="<?php echo $id?>" />
<input type="text" size="3" name="sort_order[]" tabindex="<?php echo $field_count?>" id="sort_order_<?php echo $id?>" class="sorter" value="<?php echo $value?>" />
			<?php
		
		// Every other field.
		} else {
			print $value;
		}
		print "</td>\n";
	}
	
if($this->allow['edit']) { ?><td class="action"><a href="<?php echo getLink($this->urls['edit'], array('id'=>$id, 'action'=>'edit'), true);?>" class="icon edit">Edit</a></td><?php } ?>

<?php if($this->allow['delete']) { ?><td class="action"><a href="<?php echo getLink($this->urls['delete'], array('select_row[]'=>$id, 'action'=>'delete'), true);?>" title="Delete <?php echo i($row, 'name', 'row')?>" class="icon delete confirm">Delete</a></td><?php } ?>
</tr>
<?php }

// Extra action area...
if($this->current_page_data) {
	print "<tr class='final-row'>";
	$starting_point = 1;
	
	if($this->allow['bulk_operations']) {
		$starting_point++; // - to make sure our rowspan = 2 is taken into account.
		?><td colspan="2">
<ul class="actions-multiple vertical">
<?php if($this->allow['delete']) { ?><li><a href="javascript:submit('delete');" class="with-icon delete">Delete Selected</a></li><?php } ?>
<?php if($this->allow['status_change'] and !empty($this->status_field)) { ?><li><a href="javascript:submit('activate');" class="with-icon activate">Activate Selected</a></li>
<li><a href="javascript:submit('deactivate');" class="with-icon deactivate">Deactivate Selected</a></li><?php } ?>
<?php echo $this->code['multi_select_choice']; ?>
</ul></td>
<?php } else print "<td>&nbsp;</td>";
	
	for($i=$starting_point; $i<=count($this->listing_fields); $i++) {
		if($sort_field and $sort_field == $i) print "<td><a href=\"javascript:submit('sort');\" class='with-icon save'>Sort</a></td>";
		else print "<td>&nbsp;</td>";
	}
	
	if($this->allow['edit']) print "<td>&nbsp;</td>";
	if($this->allow['delete']) print "<td>&nbsp;</td>";
	print "</tr>";
	

// No data.
} else { ?>
<tr><td class="no-records-found" colspan="<?php echo $column_count?>">No <?php echo $this->title_plural?> found.</td></tr>
<?php } ?>
</table>
<input type='hidden' name='action' id='list-form-action' value='list' />
<?php

$this->pager->link_template = '<a href="%%PAGE_LINK%%" class="page-%%CLASS%%"><img alt="%%TEXT%%" src="images/icons/arrows/%%CLASS%%.png" /></a>';
if($this->pager->total_pages > 1) {
	print $this->pager->getLink("first") . $this->pager->getLink("back");
	$this->pager->printPager();
	print $this->pager->getLink("next") . $this->pager->getLink("last") . '<br />';
}
print $this->pager->getStatus();

$save_current_state = array('search','search_in', 'sp_page','sp_items_per_page', 'sortasc', 'sortdesc');
foreach($save_current_state as $state_name) {
	if(!empty($QUERY[$state_name]))
		$html->buildInput($state_name, "", "hidden", $QUERY[$state_name]);
}
?>
</form><br />

<?php if($this->allow['add']) { ?>
<a href="<?php echo getLink($this->urls['add'], array('action'=>'add'), true)?>" class="with-icon add">Add New <?php echo $this->title?></a><br />
<?php } ?>
