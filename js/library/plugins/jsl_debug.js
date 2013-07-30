/**
 * Class: JSL.debug
 * This is actually a plugin - its not included with the main codebase. This class is 
 * 		useful in debugging applications.
 */
JSL["debug"] = {
	/**
	 * This function will get all the arguments and call console.log() Firebug function with that. If 
	 * 	Firebug is not installed, this will alert the data.
	 * Example: JSL.debug.log("Hello World", 4)
	 */
	"log": function(){
		if(!window.console) {
			var log_area = document.getElementById("jsl_log_area");
			if(!log_area) {
				log_area = document.createElement("div");
				log_area.setAttribute("id", "jsl_log_area");
				this.dom(log_area).setStyle({
					"position":"absolute",
					"top":"0px","left":"0px",
					"color":"#000",
					"font-family":"Verdana,Arial,Helvetica,sans-serif",
					"font-size":"11px",
					"background-color":"#d6e4ff",
					"z-index":999});
				document.getElementsByTagName("body")[0].appendChild(log_area);
			}
		}
		for(var i=0; i<arguments.length; i++) {
			if(window.console) console.log(arguments[i]);
			else log_area.innerHTML += arguments[i] + '<br />';
		}
	},
	
	/**
	 * This is simillar to the print_r() of PHP. If you give an array as the argument, it will return
	 * 	a human readable representation of that array.
	 * Arguments: arr - The array to be analysed
	 * Returns: A human readable representation of the array as a String
	 * Example:
	 *		alert(dump([4,3,"hello",["x","y",45,{"a":1,"b":2},"BB"]]))
	 */
	"dump": function(arr,level) {
		var dumped_text = "";
		if(!level) level = 0;
		
		//The padding given at the beginning of the line.
		var level_padding = "";
		for(var j=0;j<level+1;j++) level_padding += "    ";
		
		if(typeof(arr) == 'object') { //Array/Hashes/Objects 
			for(var item in arr) {
				var value = arr[item];
				if(typeof value == 'function') continue;
				
				if(typeof value == 'object') { //If it is an array,
					dumped_text += level_padding + "'" + item + "' ...\n";
					dumped_text += dump(value,level+1);
				} else {
					dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
				}
			}
		} else { //Stings/Chars/Numbers etc.
			dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
		}
		return dumped_text;
	}
}

//Aliases
if(typeof window.p		== "undefined") window.p	= JSL.debug.log;
if(typeof window.dump	== "undefined") window.dump	= JSL.debug.dump;
