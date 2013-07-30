<?php
class Tagging {
	public $tags = array();		///Associative array of all the tags with the id of the tag as the key.
	public $tags_hash = array();///Associative array of all the tags with the NAME of the tag as the key.
	
	/// Private stuff
	private $_relations = array();
	
	/// Reference Table details
	private $_reference_table;
	private $_reference_tag_field;
	private $_reference_item_field;
	
	///Tag Table Details
	private $_tag_table;
	private $_tag_id_field;
	private $_tag_name_field;
	
	///Item Table Details
	private $_item_table;
	private $_item_id_field;
	
	/// The user can use this function to set the reference table - eg. 'PostTag'
	function setReferenceTable($table_name, $tag_field, $item_field) {
		$this->_reference_table = $table_name;
		$this->_reference_tag_field = $tag_field;
		$this->_reference_item_field = $item_field;
	}
	
	/**
	 * Sets the name of the table that will be considered as the 'tag' table.
	 * Arguments : $table_name	- The name of the table
	 *				$id_field	- The name of primary key field - Default : 'id'
	 *				$name_field	- The name of the field that contains the tag's name - Default : 'name'
	 */
	function setTagTable($table_name, $id_field = 'id', $name_field = 'name') {
		$this->_tag_table = $table_name;
		$this->_tag_id_field = $id_field;
		$this->_tag_name_field = $name_field;
	}
	
	/**
	 * Sets the name of the 'other' table.
	 * Arguments : $table_name	- The name of the table
	 *				$id_field	- The name of primary key field - Default : 'id'
	 */
	function setItemTable($table_name, $id_field = 'id') {
		$this->_item_table = $table_name;
		$this->_item_id_field = $id_field;
	}
	
	/** 
	 * This function will check the full setup and decide if all necessary inforamtion is available. If there is any problems, it will print an error. Put this call in the developemnt time.
	 */
	function checkSetup() {
		$errors = array();

		// Check reference table details
		if(!$this->_reference_table) $errors[] = "Reference Table name not given. Use the code <code>\$tags->setReferenceTable('TableName', 'Tag_ID_Field', 'Item_ID_Field');</code>";
		if(!$this->_reference_tag_field) $errors[] = "The name of the tag field in the Reference table is not given. Use the code <code>\$tags->setReferenceTable('TableName', 'Tag_ID_Field', 'Item_ID_Field');</code>";
		if(!$this->_reference_item_field) $errors[] = "The name of the item field in the Reference table not given. Use the code <code>\$tags->setReferenceTable('TableName', 'Tag_ID_Field', 'Item_ID_Field');</code>";

		// Check Tag Table Details
		if(!$this->_tag_table) $errors[] = "Tag table's name is not given. Use the code <code>\$tags->setTagTable('TableName', 'ID_Field', 'Name_field');</code>";
		if(!$this->_tag_id_field) $errors[] = "The ID Field's name in the Tag Tablle is not given. Use the code <code>\$tags->setTagTable('TableName', 'ID_Field', 'Name_field');</code>";
		if(!$this->_tag_name_field) $errors[] = "The Tag Name field's name not given. Use the code <code>\$tags->setTagTable('TableName', 'ID_Field', 'Name_field');</code>";

		// Check Item Table Details
		if(!$this->_item_table) $errors[] = "The Item table's name is not given. Use the code <code>\$tags->setItemTable('TableName', 'ID_Field');</code>";
		if(!$this->_item_id_field) $errors[] = "The Item table's ID field's name is not given. Use the code <code>\$tags->setItemTable('TableName', 'ID_Field');</code>";
		
		if($errors) {
			print '<ul class="error-messages"><li>';
			print implode('</li><li>', $errors);
			print '</li></ul>';
		}
	}
	
	//////////////////////////////////////////// Action Functions //////////////////////////////////////
	/**
	 * Returns all the tags as an associative array with the id of the tag as the key.
	 */
	function getAllTags() {
		$this->_cacheTags();
		if($this->tags) return $this->tags; //If the caching is already done, just return tags array;

		return $tags;
	}

	/**
	 * Returns 'false' if the tag does not exists. If it exists, this function will return the id of the tag
	 * Arguments : $tag_name - The name of the tag
	 */
	function getTagId($tag_name) {
		$this->_cacheTags();
		if(isset($this->tags_hash[$tag_name]))
			return $this->tags_hash[$tag_name];

		//Tag was not found
		return false;
	}
	
	/**
	 * This will get all the tags from the DB and caches it in two arrays - ::$tag and ::$tag_hash. This will eleminate the need to query the db to get the tags.
	 * Private
	 */
	private function _cacheTags() {
		global $sql;
		if($this->tags) return; //Already Cached.

		$tags = $sql->getById("SELECT `{$this->_tag_id_field}`,`{$this->_tag_name_field}` FROM `{$this->_tag_table}`");
		$this->tags = $tags;
		$this->tags_hash = array();
		foreach($this->tags as $id=>$tag) {
			$this->tags_hash[$tag] = $id;
		}
	}
	
	/**
	 * Insert a new tag into the DB and return its id after caching it.
	 */
	function insertTag($tag_name) {
		global $sql;

		$tag_id = $this->getTagId($tag_name);
		if($tag_id === false) {
			$sql->execQuery("INSERT INTO `{$this->_tag_table}`(`{$this->_tag_name_field}`) VALUE('$tag_name')");
			$tag_id = $sql->fetchInsertId();

			//Add the new tag to the cache
			$this->tags[$tag_id] = $tag_name;
			$this->tags_hash[$tag_name] = $tag_id;
		}
		return $tag_id;
	}
	
	////////////////////////////////// API Functions ////////////////////////////////
	function newTagFor($item_id, $tag_name) {
		global $sql;

		$tag_id = $this->insertTag($tag_name);
		$sql->execQuery("INSERT INTO `{$this->_reference_table}` "
			. " (`{$this->_reference_tag_field}`,`{$this->_reference_item_field}`) VALUES($tag_id, $item_id)");
			
		return $tag_id;
	}
	
	/**
	 * Removes all the tags for a given item.
	 * Arguments : $item_id - The ID of the item that must be 'untagged'
	 */
	function removeAllTagsFor($item_id) {
		global $sql;
		$sql->execQuery("DELETE FROM `{$this->_reference_table}` WHERE `{$this->_reference_item_field}`='$item_id'");
		return $sql->fetchAffectedRows();
	}
	/**
	 * Remove the connection between the given tag and the given item.
	 */
	function removeTagFor($item_id, $tag_id) {
		global $sql;
		$sql->execQuery("DELETE FROM `{$this->_reference_table}` WHERE `{$this->_reference_item_field}`='$item_id' AND `{$this->_reference_tag_field}`='$tag_id'");
	}

	/**
	 * Remove a tag and all the connections associated with it
	 */
	function removeTag($tag_id) {
		global $sql;
		$sql->execQuery("DELETE FROM `{$this->_tag_table}` WHERE `{$this->_tag_id_field}`='$tag_id'");
		$sql->execQuery("DELETE FROM `{$this->_reference_table}` WHERE `{$this->_reference_tag_field}`='$tag_id'");
	}
	
	/**
	 * Removes all existing tags for a item and insertes a new set of tags for it.
	 * Arguments :	$item_id	- The ID of the item that must be tagged with the given tags.
	 *				$tags(Array) - An array of all the tags that must be inserted for this item
	 */
	function setTags($item_id, $tags) {
		global $sql;
		
		$this->removeAllTagsFor($item_id);//First, remove all the tags for the item.

		$insert_query = array();
		foreach ($tags as $tag_name) {
			$tag_id = $this->insertTag($tag_name);
			$insert_query[] = "($tag_id,$item_id)";
		}
		if($insert_query) {
			$sql->execQuery("INSERT INTO `{$this->_reference_table}` (`{$this->_reference_tag_field}`,`{$this->_reference_item_field}`)"
				. " VALUES " .implode(',',$insert_query));
		}
	}
	
	/**
	 * Returns all the tags for the given item.
	 * Arguments: $item_id - The ID of the item of which's tag must be found
	 * Returns	: $tags - All the tags for the given item as an associative array with the id of the tag as the key.
	 */
	function getTagsFor($item_id) {
		global $sql;
		$tags = $sql->getById("SELECT `{$this->_tag_table}`.`{$this->_tag_id_field}`,`{$this->_tag_table}`.`{$this->_tag_name_field}` "
			. " FROM `{$this->_tag_table}` INNER JOIN `{$this->_reference_table}` "
			. " ON `{$this->_reference_table}`.`{$this->_reference_tag_field}`=`{$this->_tag_table}`.`{$this->_tag_id_field}` "
			. " WHERE `{$this->_reference_table}`.`{$this->_reference_item_field}`='$item_id'");
		return $tags;
	}

	/**
	 * Get the ID of all the items tagged with the given tag id.
	 * Arguments : $tag_id - The ID of the tag
	 * Return : The ID of all the items in a numeric array.
	 */
	function getItemsTaggedWith($tag_id) {
		global $sql;
		$items = $sql->getCol("SELECT `{$this->_reference_item_field}` FROM `{$this->_reference_table}` WHERE `{$this->_reference_tag_field}`='$tag_id'");
		return $items;
	}
}
