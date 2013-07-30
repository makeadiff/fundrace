function getFileExtention(file) {
	var pos_format = file.lastIndexOf(".");
	var pos_name = file.lastIndexOf("/");
	var length = pos_format - pos_name; //The length from the last '/' to the last '.' will give the name of the file 
	var ext = file.substr(pos_format+1,file.length);
	return ext;
}
function stripWierd(txt) {
	re = new RegExp(/[\W_]/g); //
	str = txt.replace(re,"");
	re = new RegExp(/^\s+/g);//Strips all spaces to the left
	str = str.replace(re,"");
	re = new RegExp(/\s+$/g);//Strips all spaces to the right
	str = str.replace(re,"");
	return str;
}

if(window.main) main();