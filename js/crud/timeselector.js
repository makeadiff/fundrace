/*********************************************************************
* Name		: TimeSelector
* Version	: 1.00.A Beta
* Author	: Binny V A (http://www.geocities.com/binnyva/)
* Created On: Thursday, October 20 2005
* Last Updated On: Thursday, October 20 2005
*********************************************************************/
/////////////////////////////// TimeSelector Class //////////////////////////////////////////////
//Constructor
function TimeSelector(id,sh) {
	//Member Varaibles
	this.id=id;
	if(sh == "hidden")
		this.display_show_hide = 0;
	else
		this.display_show_hide = 1;
	
	
	this.createSelectors();
	document.getElementById(id+'_time-selector-hour').onchange  =this.selectHour;
	document.getElementById(id+'_time-selector-minute').onchange=this.selectMin;
}

//Function that creates the HTML necessary for the Time selector
function createSelectors() {
	var id = this.id;
	var html = "";
	if(this.display_show_hide) html += '<a href="#" id="'+id+'_time-selector-sh" onclick="timeShowHide(\''+id+'\');">Show Time Selector</a>';
	html += '<div id="'+id+'_time-selector"';
	if(this.display_show_hide) html += ' style="display:none;"';
	html += '><select id="'+id+'_time-selector-hour"><option value="00">12 Midnight</option>\
<option value="01">1 AM</option><option value="02">2 AM</option><option value="03">3 AM</option><option value="04">4 AM</option>\
<option value="05">5 AM</option><option value="06">6 AM</option><option value="07">7 AM</option><option value="08">8 AM</option>\
<option value="09">9 AM</option><option value="10">10 AM</option><option value="11">11 AM</option><option value="12">12 Noon</option>\
<option value="13">1 PM</option><option value="14">2 PM</option><option value="15">3 PM</option><option value="16">4 PM</option><option value="17">5 PM</option><option value="18">6 PM</option>\
<option value="19">7 PM</option><option value="20">8 PM</option><option value="21">9 PM</option><option value="22">10 PM</option><option value="23">11 PM</option></select>'+
'<select id="'+id+'_time-selector-minute"><option value="00">00</option><option value="01">01</option><option value="02">02</option><option value="03">03</option>\
<option value="04">04</option><option value="05">05</option><option value="06">06</option><option value="07">07</option><option value="08">08</option>\
<option value="09">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option>\
<option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option>\
<option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option>\
<option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option>\
<option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option><option value="32">32</option>\
<option value="33">33</option><option value="34">34</option><option value="35">35</option><option value="36">36</option><option value="37">37</option>\
<option value="38">38</option><option value="39">39</option><option value="40">40</option><option value="41">41</option><option value="42">42</option>\
<option value="43">43</option><option value="44">44</option><option value="45">45</option><option value="46">46</option><option value="47">47</option>\
<option value="48">48</option><option value="49">49</option><option value="50">50</option><option value="51">51</option><option value="52">52</option>\
<option value="53">53</option><option value="54">54</option><option value="55">55</option><option value="56">56</option><option value="57">57</option>\
<option value="58">58</option><option value="59">59</option></select>';
	if(this.display_show_hide) html += '<a href="#" onclick="timeShowHide(\''+id+'\');">Hide</a>\</div>';
	document.write(html);
	
	var parts = document.getElementById(id).value.split(':');
	document.getElementById(id+'_time-selector-hour').value  = parts[0];
	document.getElementById(id+'_time-selector-minute').value= parts[1];
	
}
function selectHour() {
	//Extract the id of the element from the id of the Downdown
	var id = this.id;
	var rest_pos = id.indexOf("_time-selector-hour");
	var element_id = id.slice(0,rest_pos);
	
	var element = document.getElementById(element_id);
	var parts = element.value.split(':');
	element.value = document.getElementById(element_id+'_time-selector-hour').value + ":" + parts[1] + ":00";
}
function selectMin() {
	//Extract the id of the element from the id of the Downdown
	var id = this.id;
	var rest_pos = id.indexOf("_time-selector-minute");
	var element_id = id.slice(0,rest_pos);
	
	var element = document.getElementById(element_id);
	var parts = element.value.split(':');
	element.value = parts[0] + ":" + document.getElementById(element_id+'_time-selector-minute').value + ":00";
}
TimeSelector.prototype.selectHour = selectHour;
TimeSelector.prototype.selectMin = selectMin;
TimeSelector.prototype.createSelectors = createSelectors;

//Other Functions
function timeShowHide(id) {
	var element = document.getElementById(id + "_time-selector");
	element.style.display=(element.style.display=="none") ? "inline" : "none";
	var element_sh = document.getElementById(id+"_time-selector-sh");
	element_sh.style.display=(element_sh.style.display=="none") ? "inline" : "none";
}