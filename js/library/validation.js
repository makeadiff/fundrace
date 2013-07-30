function check(conditions) {
	for(var index=0; index<conditions.length; index++ ) {
		var cond = conditions[index]
		var is = cond['is'].toLowerCase();
		var id = cond['id'];
		var msg = cond['msg'];
		if(cond['error']) msg = cond['error'];
		var name = cond['name'];
		if(!id) id = name;
		
		if(cond['when'] == 0 || cond['when'] == false || !document.getElementById(id)) {
			continue;
		}/* else if (cond['when'] != "" && typeof cond['when'] != 'undefined') {
			alert(msg);
			return false;
		}*/

		if(is == 'empty') {
			var error = "Some mandatory fields are not filled.";
			if(!document.getElementById(id).value) {
				if(msg) error = msg;
				else if(name) error = "The " + name +" is not provided";
				alert(error);
				document.getElementById(id).focus();
				return false;
			}
		} else if(is == 'not') {
			if(document.getElementById(id).value != cond.value) {
				alert(msg);
				return false;
			}
		} else if(is == 'equal') {
			if(document.getElementById(id).value == cond.value) {
				alert(msg);
				return false;
			}
		} else if(is == 'greater') {
			if(document.getElementById(id).value > cond.value) {
				alert(msg);
				return false;
			}
		} else if(is == 'lesser') {
			if(document.getElementById(id).value < cond.value) {
				alert(msg);
				return false;
			}
		} else if(is == 'file') { //The valid file types should be given in the 'value' field as a comma seperated list
			if(document.getElementById(id).value) {
				var parts;
				if(document.getElementById(id).value.indexOf("/") + 1) parts = document.getElementById(id).value.split("/");
				else parts = document.getElementById(id).value.split("\\");
	
				var ext = parts[parts.length-1].split(".");
				ext = ext[ext.length-1].toLowerCase();
				var allowed = cond.value.toLowerCase().split(",");
	
				var found = false;
				for(var i in allowed) {
					if(ext == allowed[i]) {
						found = true;
						break;
					}
				}
				if(!found) {
					if(msg) error = msg;
					else if(name) error = "The " + name +" should be any of these file types : " + cond.value;
					else error = "Invalid file type";
					alert(error);
					return false;
				}
			}
		} else if(is == 'nan' || is == 'not_number') { //Warning: Decimals will get thru
			if(isNaN(document.getElementById(id).value)) {
				if(msg) error = msg;
				else if(name) error = "The " + name +" should be a number";
				alert(error);
				document.getElementById(id).focus();
				return false;
			}
		} else if(is == 'not_email') { //If the field does not match the email regexp, an error is shown
			if(document.getElementById(id).value.search(/^[\w\-\.]+\@[\w\-\.]+\.[a-z\.]{2,5}$/) == -1) {
				if(msg) error = msg;
				else if(name) error = "The " + name +" should be a valid email address";
				else error = "Invalid Email address provided";
				alert(error);
				document.getElementById(id).focus();
				return false;
			}
		} else if(is == 'has_weird') { //Check for weird chars
			if(document.getElementById(id).value.search(/^[\w\-]*$/) == -1) {
				if(msg) error = msg;
				else if(name) error = "The " + name +" should not have weird characters";
				else error = "Weird characters where found in the input";
				alert(error);
				document.getElementById(id).focus();
				return false;
			}
		} else if(is == 'not_name') { //Check for chars that cannot appear in a name
			if(document.getElementById(id).value.search(/^[\w\'\(\)\,\.\/ ]*$/) == -1) {
				if(msg) error = msg;
				else if(name) error = "The " + name +" has invalid characters";
				else error = "Invalid characters where found in the input";
				alert(error);
				document.getElementById(id).focus();
				return false;
			}
		} else if(is == 'match') {
			if(document.getElementById(id).value.search(cond.value) + 1) {
				if(msg) error = msg;
				else if(name) error = "The " + name +" is not in the right format.";
				else error = "Invalid format.";
				alert(error);
				return false;
			}
		} else if(is == 'not_match') {
			if(document.getElementById(id).value.search(cond.value) == -1) {
				if(msg) error = msg;
				else if(name) error = "The " + name +" is not in the right format.";
				else error = "Invalid format.";
				alert(error);
				return false;
			}
		}
	}

	return true;
}