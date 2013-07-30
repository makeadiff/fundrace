var monthLength = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
function parseDate(date,format) {
	//Find the date seperator
	var seperator = '-';
	if(format.indexOf('/')) {
		seperator = '/'
	} else if(format.indexOf('\\')) {
		seperator = '\\'
	}
	format = format.replace(/\%/g,'');

	var date_parts  = date.split(seperator);
	var format_parts= format.toLowerCase().split(seperator);

	//If there was time along with the date.
	if(format_parts[2].length>4) {
		var more_parts = format_parts[2].split(' ');
		format_parts[2] = more_parts[0];
		more_parts = date_parts[2].split(' ');
		date_parts[2] = more_parts[0];
	}

	//Find the location of Month, date and time
	// :TODO: We also want hour, minute and second.
	var m=-1,d=-1,y=-1;
	for(var i=0;i<format_parts.length;i++) {
		if(format_parts[i] == 'm') m = i;
		if(format_parts[i] == 'd') d = i;
		if(format_parts[i] == 'y') y = i;
	}
	//Create the associative array with the daa.
	var date = {
		'date' :date_parts[d],
		'month':date_parts[m],
		'year' :date_parts[y]
	};
	
	return date;
}
function checkDate(date_str,when) {
	var date_arr = parseDate(date_str,date_format);
	var year = date_arr['year'];
	var month = date_arr['month'];
	var day = date_arr['date'];
	
	if (!day || !month || !year)
		return false;
	if (month>12)
		return false;

	//February case
	if (year/4 == parseInt(year/4))
		monthLength[1] = 29;
	else
		monthLength[1] = 28; //February have 28 Months by default

	if (day > monthLength[month-1])
		return false;

	var now = new Date();
	now = now.getTime(); //NN3

	var dateToCheck = new Date();
	dateToCheck.setYear(year);
	dateToCheck.setMonth(month-1);
	dateToCheck.setDate(day);
	var checkDate = dateToCheck.getTime();
	
	if(when == "") return true;
	if(when == "future" || when == 1) {
		if (now < checkDate) {
			return true;
		} else { 
			return false;
		}
	} else if(when == "past" || when == 0 || when == -1) {
		if (now > checkDate) {
			return true;
		} else { 
			return false;
		}
	} else {
		return true;
	}
} 
