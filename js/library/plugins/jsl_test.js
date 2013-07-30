/**
 * Class: JSL.test
 * UnitTest library. This is a plugin and not the part of the main codebase. It can be called like this..
 * JSL.test("Test Name").AssertType("The Real Value", "The Correct Value");
 * Example:
 * 	JSL.test_starup();
 * 	JSL.test("Find if 1 is true").assertTrue(1);
 *	JSL.test("Find if 0 is false").assertFalse(0);
 *	JSL.test("Find if 1 is 0").assertEquals(1,0);
 *  JSL.test_run();
 */
(function() {
	function _test_init(test_description, test_id, show_test_details) {
		this.test_description = test_description;
		this._div_id = test_id;
		this.show_test_details = show_test_details;
		return this;
	}
	
	_test_init.prototype = {
		"_current_test_id" : "", /// The ID of the LI element which is the most recent.
		"show_test_details": true, /// taken from JSL._test_show_details
		"_div_id" : "", // Will be taken from JSL._test_div_id variable
		
		//User Defined functions
		"onTest":false,
		"onSuccess":false,
		"onFail":false,
		
		/**
		 * Test will succeed if the given value(1st argument) is true.
		 * Example: JSL.test("Is 1==1 returning true?").assertTrue(1 == 1)
		 */
		"assertTrue" : function() {
			var test = this._startTest(arguments);
			this._saveTest(test, test.real_value );
		},
		/**
		 * Test will succeed if the given value(1st argument) is true.
		 * Example: JSL.test("1==1 should return true").assert(1 == 1)
		 */
		"assert" : function() {
			var test = this._startTest(arguments);
			this._saveTest(test, test.real_value );
		},
		/**
		 * Test will succeed if the given value(1st argument) is false
		 * Example: JSL("1 != 1 should return false").test.assertFalse(1 != 1)
		 */
		"assertFalse" : function() {
			var test = this._startTest(arguments);
			this._saveTest(test, test.real_value == false);
		},
		/**
		 * Test will succeed if the given values are equal. The values could be strings, numbers, chars - basciacally anything that could be compaired by == . Arrays are not supported.
		 * Arguments :  should_be_value - The first argument is the correct value - the ideal value
	 	 *				real_value - The second argument is the real value. Ideally, the 'real_value' should be equal to the 'should_be_value'
	 	 * Example: JSL.test.assertEquals(1,1)
		 */
		"assertEquals" : function() {
			var test = this._startTest(arguments);
			this._saveTest(test, test.real_value == test.should_be_value);
		},
		/**
		 * Test will succeed if the given values are not equal. The values could be strings, numbers, arrays, list - basciacally anything that could be compaired by !=
		 * Arguments : 	$arg_1 - The first value
		 *				$arg_2 - The second value
		 * Example: JSL.test.assertNotEquals(1,2)
		 */
		"assertNotEquals" : function() {
			var test = this._startTest(arguments);
			this._saveTest(test, test.real_value != test.should_be_value);
		},
		
		/**
		 * Test will succeed if the two given arrays are equal. The values could be arrays or lists
		 * Arguments : 	should_be_value - The first argument is the correct value - the ideal value
		 *				real_value - The second argument is the real value. Ideally, the 'real_value' should be equal to the 'should_be_value'
		 * Example: 
		 * 		JSL.test.assertEquals([23,200,3],[23,200,3]);
		 * 		JSL.test.assertEquals({"car":"Porshe", "bike":"Yamaha", "plane":"Lear"},{"bike":"Yamaha", "car":"Porshe", "plane":"Lear"});
		 */
		"assertArrayEquals" : function() {
			var test = this._startTest(arguments);
			this._saveTest(test, this._arrayIsSame(test.real_value, test.should_be_value));
		},

		/**
		 * Test will succeed if the two given arrays are NOT equal. The values could be arrays or lists
		 * Example:
		 *	JSL.test.assertArrayNotEquals([23,200,3],[23,100,13])
		 */
		"assertArrayNotEquals" : function() {
			var test = this._startTest(arguments);
			this._saveTest(test, !this._arrayIsSame(test.real_value, test.should_be_value));
		},
		
		/// Checks to see if the given value is null - the test will succeed if its a null
		"assertNull" : function() {
			var test = this._startTest(arguments);
			this._saveTest(test, (test.real_value == false && typeof(test.real_value) == "object"));
		},
		
		/// Checks to see if the given value is NOT null - the test will succeed if it is NOT null
		"assertNotNull" : function() {
			var test = this._startTest(arguments);
			this._saveTest(test, test.real_value != null);
		},
		
		/// Checks to see if the given value is defined or not - the test will succeed if it is undefined
		"assertUndefined" : function() {
			var test = this._startTest(arguments);
			this._saveTest(test, typeof(test.real_value) == "undefined");
		},
		
		/// Checks to see if the given value is NOT undefined - the test will succeed if it is defined
		"assertNotUndefined" : function() {
			var test = this._startTest(arguments);
			this._saveTest(test, typeof(test.real_value) != "undefined");
		},
		
		/**
		 * Saves the test to an global array so that it could be ran later - when the runTests() is called.
		 */
		"_saveTest": function(test, result) {
			if(window.JSL["_test_run_immediatly"])
				this._decide(test, result);
			else
				window.JSL["_test_all_tests"].push({"test":test, "result":result});
		},
		
		/**
		 * Decides wether the current test is a success or a failure based on the second argument.
		 * 		This function will call _onSuccess or _onFail function based on the the result.
		 */
		"_decide" : function(test, success) {
			if(success) this._onSuccess(test);
			else this._onFail(test);
			return success;
		},
	
		/// Test Initalizer. Gets the arguments, increments the counter and calls _onTest
		"_startTest": function(args) {
			this.test = this._getArgs(args);
			this.test['index'] = ++JSL._test_count;
			if(window.JSL["_test_run_immediatly"]) this._onTest(this.test);
			return this.test;
		},
	
		/// Happens before a test is ran. If onTest function exist, the test details will be send to that.
		"_onTest": function(test, total_tests) {
			if(this.onTest) this.onTest.call(test);
			this._current_test_id = this._div_id + "_test_" + JSL._test_count;
			
			// Show the number of the current test.
			var index;
			if(total_tests) index = test.index + "/" + total_tests + ") ";
			else index = test.index + ") ";
			this._show(index + test.message + " : ")
		},
		
		/** 
		 * Happens when a test succeeds - if the onSuccess function is defined by the user, 
		 *		this function will call it with the details of the current test
		 */
		"_onSuccess": function(test) {
			if(this.onSuccess) this.onSuccess.call(test);
			
			this._show("<span class='test-success'>SUCCESS</span><br />", true);
		},
		
		/** 
		 * Happens when a test fail - if the onFail function is defined by the user, 
		 *		this function will call it with the details of the current test
		 */
		"_onFail": function(test) {
			if(this.onFail) this.onFail.call(test);
			
			this._show("<span class='test-failed'>FAILED</span><br />", true);
		},
		
		/// Shows the result of the current test by appending it to the main list.
		"_show": function(details, test_result) {
			if(this.show_test_details) {
				if(test_result) {
					document.getElementById(this._current_test_id).innerHTML += details;
	
				} else {
					var li = document.createElement("li");
					li.setAttribute("id", this._current_test_id);
					li.appendChild(document.createTextNode(details));
					document.getElementById(this._div_id + "_testing").appendChild(li);
				}
			}
		},
		
		/**
		* This will create and return an associative array with the details of the current test. 
		*		The array will have the following values - 'message', 'real_value' and 'should_be_value'. 
		*		Later, the 'status' will be added to indicate wether the test was a success or not.
		*/
		"_getArgs": function(args) {
			var description = this.test_description;
			var real_value;
			var should_be_value;
	
			if(args.length == 1) {
				real_value = args[0];
	
			} else if(args.length == 2) {
				should_be_value = args[0];
				real_value = args[1];
			}
			
			return {"message":description, "real_value": real_value, "should_be_value":should_be_value};
		},
		
		/**
		 * This function checks to see if the 2 given arrays are the same - works 
		 *		with both list and associative arrays. Returns true if both are the same 
		 *		and false if they are not.
		 */
		"_arrayIsSame" : function (arr1, arr2) {
			var length_check = true;
			if(arr1.length) length_check = (arr1.length == arr2.length); //Applicable for lists/numerical arrays only
			
			if(typeof(arr1) == typeof(arr2) && typeof(arr2) == "object" && length_check) { //Some basic sanity checks
				var element_count_1 = 0;
				for(var index in arr1) {
					if(arr1[index] !== arr2[index]) return false; //Make sure each element is the same
					element_count_1++;
				}
				
				if(!arr1.length) { //Implementing a length check for associative arrays.
					var element_count_2 = 0;
					for(var index in arr2) {element_count_2++;}
					if(element_count_1 !== element_count_2) return false;
				}
				
				return true;
			} else {
				return false;
			}
		}
	}
	
	//Add it to the JSL array.
	window.JSL['test'] = function(test_description) {
		return new _test_init(test_description, JSL._test_div_id, JSL._test_show_details);
	}
	
	/**
	 * This function must be manually called before starting the test. It will create a div with id "jsl_test_details"
	 * 	- this is where all the test details will be shown.
	 * Arguments:
	 * 		run_immediately - If this is true, all the tests will be executed and shown on call. If not, it will wait until JSL.test_run() call is made.
	 * Example: JSL.test_startup()
	 */
	window.JSL['test_startup'] =  function(run_immediately) {
		//Global(wrt this library) stuff
		window.JSL["_test_count"] = 0; /// The number of tests run
		window.JSL["_test_div_id"] = "jsl_test_details"; /// The ID of the DIV that shows the details of the test.
		window.JSL["_test_show_details"] = true; /// If this is true, the details of the test will be shown in the window.
		window.JSL["_test_all_tests"] = []; /// This holds all the test in an array if run_immediately is off
		
		/// If this is true, the result of the test is shown immediatly - if not, it will wait until test_run() function is called.
		window.JSL["_test_run_immediatly"] = (run_immediately) ? true : false; //Could be undefined.
		
		if(!(document.getElementById(this._test_div_id))) {
			if(this._test_show_details) {
				var body = document.getElementsByTagName("body")[0];
				var div = document.createElement("div");
				div.setAttribute("id", this._test_div_id);
				div.setAttribute("style", "position:absolute;top:0px;left:0px;color:#000;font-family:Verdana,Arial,Helvetica,sans-serif;font-size:11px;background-color:#d6e4ff;");
				
				var ul = document.createElement("ul");
				ul.setAttribute("id", this._test_div_id + "_testing");
				div.appendChild(ul);
				body.appendChild(div);
			} else {
				document.getElementById(this._test_div_id + "_testing").innerHTML = "";
			}
		}
	}
	
	/// Run all the cached tests.
	window.JSL["test_run"] = function() {
		if(!window.JSL["_test_all_tests"] || window.JSL["_test_run_immediatly"]) return;
		var total_tests = window.JSL["_test_all_tests"].length;
		var test_object = JSL.test(""); //We need an empty test object.
		
		// _jsl_test_all_tests is a global variable - so using different objects is not an issue.
		JSL.array(window.JSL["_test_all_tests"]).each(function(ele,i, arr, self) {
			self._onTest(ele.test, total_tests);
			self._decide(ele.test, ele.result);
		}, test_object);
	}
	
})();
