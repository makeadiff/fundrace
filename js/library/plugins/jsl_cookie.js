/**
 * Class: JSL.cookie
 * This is a plugin that provides cookie functionality - to access and modify cookies
 * Example:
 *	JSL.cookie("name").get(); //Returns the value of the cookie 'name'
 *	JSL.cookie("name").set("New Value"); // Sets the new value.
 */
(function() {
	function _cookie_init(name) {
		this.name = name;
		return this;
	}

	_cookie_init.prototype = {
		/**
		 * An easy shortcut to access the curret cookie's value. Warning: You cannot set the value of a cookie using this. 
		 * Example: JSL.cookie("cookie_name").value;
		 */
		value: '', 
		
		/**
		 * Fetches the value of the cookie whose name is given.
		 * Example: 
		 * 		JSL.cookie("cookie_name").get();
	 	 */
		"get": function(){
			return this.value;
		},
		
		/**
		 * Sets the value of the given cookie.
		 * Arguments: value - The new value of cookie
		 * 			expire - [OPTIONAL] The expiry time of the current cookie - should be given in days.
		 * Example:
		 * 		JSL.cookie("hello").set("World");
		 * 		JSL.cookie("foo").set("bar", 20); //Expires in 20 days
		 */
		"set": function(value, expire) {
			var expiry_time = '';
			if(expire != undefined) {
				var expires_in = expire * 1000 * 60 * 60 * 24; //convert days to milliseconds
				var expires_date = new Date(new Date().getTime() + (expires_in));
				expiry_time = ";expires="+expires_date.toGMTString();
			}

			this.value = value;
			window.JSL._cookies[this.name] = value;//Cache the new value 
			document.cookie = this.name+"="+escape(value) + expiry_time;
		},
		
		/**
		 * Deletes the said cookie.
		 */
		"remove": function() {
			this.set('',-1); //removes the cookie by setting it as null and giving it an expiry date in the past
		},
		
		/**
		 * Fetches all available cookies for this page and caches it.
		 */
		"fetchAll": function() {
			var all_cookies = JSL.array(document.cookie.split("; "));
			var cookies = {};
			all_cookies.each(function(value){
				var parts = value.split("=")
				cookies[parts[0]] = unescape(parts[1]);
			});
			window.JSL._cookies = cookies; //Cache it as a global varable so that we can fetch it from another object as well.
		},
		
		// :TODO: Remove
		"_cacheCookies":function(cookies) {
			 window.JSL._cookies = cookies; //Cache it as a global varable so that we can fetch it from another object as well.
		}
	}
	
	window.JSL["cookie"] = function(name, refresh) {
		var cookie = new _cookie_init(name);
		
		//The script will do fetch all the cookies if we use it for the first time(the global var is not set) of if the user explecitly said to fetch
		if (refresh || window.JSL._cookies == undefined) {
			cookie.fetchAll();
		}
		cookie.value = window.JSL._cookies[name];
		
		return cookie;
	}
})();
