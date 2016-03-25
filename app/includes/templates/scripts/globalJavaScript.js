
	// Converts hour id to a textual representation of the time (ex, 14.5 = 2:30p)
	function hourToTime(hour) {
		var time = Math.floor(hour) % 12;
		if (time == 0) time = 12;
		
		var minute = Math.round(hour * 60 % 60);
		
		time += ":";
		
		if (minute < 10) time += "0";
		time += minute;
		
		time += (hour % 24 >= 12)?"p":"a";
		
		return time; 
	}
	
	// Pass in a textual date (ie "2011-08-24"), get a javascript date object
	function textToDate(text) {
	    // Little known fact: Firefox doesn't understand -'s in dates, it prefers /'s
		return new Date(text.replace(/-/gi,"/"));
	}
	
	// Pass in a javascript date object, get a textual date (ie "2012-12-21")
	function dateToText(date) {
	    var d = new Date(date);
		var year = d.getFullYear();
		var month = d.getMonth() + 1;
		var day = d.getDate();
		
		var text = year +"-"+(month<10?"0":"")+month+"-"+(day<10?"0":"")+day;
		
		return text;
	}
	
	function dateToPrettyText(date) {
		var days = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
		var fullDays = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
	    var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
	    var d = new Date(date);
		var year = d.getFullYear();
		var month = d.getMonth();
		var day = d.getDate();
		
		var text = days[d.getDay()] + " " + months[month] + " " + day + ", " + year;
		
		return text;
	}
	
	function zeroArray(arr) {
		for (var i = 0; i < arr.length; i++)
			arr[i] = 0;
		return arr;
	}
	
	function getSaturday(text) {
		var d = textToDate(text);
		
		if (d.getDay() != 6) {
			d.setDate(d.getDate() - d.getDay() - 1);
		}
		
		return d;
	}	
	function getSaturdayByText(text) {
		var d = textToDate(text);
		
		if (d.getDay() != 6) {
			d.setDate(d.getDate() - d.getDay() - 1);
		}
		
		return dateToText(d);
	}	
	function getSaturdayByDate(date) {
		var d = new Date(date);
		
		if (d.getDay() != 6) {
			d.setDate(d.getDate() - d.getDay() - 1);
		}
		
		return d;
	}
	
	// Pass in a page address, complete with query string if desired (ie "page.php?var=value&foo=bar"),
	// and a callback with one input variable (ie "function (result) {alert(result);}"), and
	// your callback with get the results of the call to the PHP page (in other words, everything
	// that was echo-ed. You can pop it into an innerHTML if you want.
	function callPhpPage(page, callback) {
		var xmlhttp;
		if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		}else{// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange=function(){
			if (xmlhttp.readyState==4 && xmlhttp.status==200){
			    var result = xmlhttp.responseText;
			    callback(result);
			}
		}
		xmlhttp.open("GET", page, true);
		xmlhttp.send();
	}
	
	// I didn't write this, but it prevents	the enter key from refreshing the page
	// for text input fields (use onKeyPress="return disableEnterKey(event);")
	function disableEnterKey(e)
	{
		 var key;
		 if(window.event)
		      key = window.event.keyCode;     //IE
		 else
		      key = e.which;     //firefox
		 return (key != 13);
	}
	
	// This function takes an element and makes it (and its child elements) selectable; it's even cross-browser compatible, except in IE9, apparently, because it sucks
    function makeSelectable(node)  {
		if (node.nodeType == 1) {
		    if (typeof node.unselectable != 'undefined') {       // Internet Explorer, Opera
		        node.unselectable = false;
		    } 
		    else {
		        if (window.getComputedStyle) {
		            var style = window.getComputedStyle (node, null);
		            if ('MozUserSelect' in style) { // Firefox
		                node.style.MozUserSelect = "text";
		            }
		            else {
		                if ('webkitUserSelect' in style) {      // Google Chrome and Safari
		                    node.style.webkitUserSelect = "text";
		                }
		                else {
		                    //alert1 ("Your browser doesn't support node.style.MozUserSelect or node.style.webkitUserSelect!");
		                }
		            }
		        }
		        else {
		            //alert1 ("Your browser doesn't support window.getComputedStyle!");
		        }
		    }
		}
		var child = node.firstChild;
		while (child) {
		    makeSelectable(child);
		    child = child.nextSibling;
		}
	}

	// This function takes an element and makes it (and its child elements) unselectable; it's even cross-browser compatible, except in IE9, apparently, because it sucks    
    function makeUnselectable(node) {
		if (node.nodeType == 1) {
		    if (typeof node.unselectable != 'undefined') {       // Internet Explorer, Opera
		        node.unselectable = true;
		    } 
		    else {
		        if (window.getComputedStyle) {
		            var style = window.getComputedStyle (node, null);
		            if ('MozUserSelect' in style) { // Firefox
		                node.style.MozUserSelect = "-moz-none";
		            }
		            else {
		                if ('webkitUserSelect' in style) {      // Google Chrome and Safari
		                    node.style.webkitUserSelect = "none";
		                }
		                else {
		                    //alert1 ("Your browser doesn't support node.style.anything!");
		                }
		            }
		        }
		        else {
		            //alert1 ("Your browser doesn't support window.getComputedStyle!");
		        }
		    }
		}
		var child = node.firstChild;
		while (child) {
		    makeUnselectable(child);
		    child = child.nextSibling;
		}
	}
	
	function togglediv(divname) {                
		div = document.getElementById(divname);
		div.style.display = (div.style.display == "none" ? "block" : "none");
	}
