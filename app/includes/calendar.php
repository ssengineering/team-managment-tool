<?php
    echo '<script language="JavaScript" src="/includes/templates/scripts/calendar_db.js"></script>';
    echo '<link rel="stylesheet" href="/includes/templates/styles/calendar.css">';

	function calendar($form, $field){
		echo ("<script language=\"JavaScript\">
	
				//Code for calendar
				var d_today = new Date();
				d_today.setDate(d_today.getDate());
				var s_today = f_tcalGenerDate(d_today);

				new tcal ({
					// form name
					'formname': '".$form."',
					// input name
					'controlname': '".$field."',
					'today' : s_today
				});
				
				</script>");
	}
	
	function calendarNoForm($field){
		echo ("<script language=\"JavaScript\">
	
				//Code for calendar
				var d_today = new Date();
				d_today.setDate(d_today.getDate());
				var s_today = f_tcalGenerDate(d_today);

				new tcal ({
					// input name
					'controlname': '".$field."',
					'today' : s_today
				});
				
				</script>");
	}

	function calendarCallback($field, $changeCallback, $activateCallback = ""){
		echo ("<script language=\"JavaScript\">
	
				//Code for calendar
				var d_today = new Date();
				d_today.setDate(d_today.getDate());
				var s_today = f_tcalGenerDate(d_today);
				
				// default value of input is today
				document.getElementById('".$field."').value = s_today;

				new tcal ({
					'controlname': '".$field."',
					'today' : s_today,
					'changeCallback' : $changeCallback
				});
				
				</script>");
	}

?>
