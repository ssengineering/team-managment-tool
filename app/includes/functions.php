<?php

require_once('guid.php');

/** THIS FUNCTION IS FOR GENERATING A LIST OF ALL CURRENT EMPLOYEES THAT IS DYNAMICALLY UPDATED
  * ACCORDING TO THE USERS INPUT.  YOU SHOULD PLACE THIS FUNCTION INSIDE A "<div>" OF YOUR OWN MAKING.
  * POSTS Selected Employee to the current page as 'employeeNetId'
  *
  * PLACE THE "employeeList()" FUNCTION IN A "<div>" WITH THIS BASIC FORMAT:
  * "<div style="position: relative; height: yourHeightHere; width: yourWidthHere;"> </div>"
  * YOU CAN INCLUDE WHATEVER OTHER STYLING YOU WOULD LIKE IN ORDER TO CENTER THE DIV, ETC.
  *
  * @param $allEmployees string("true" or "false") This determines whether or not all
  *     employees with access to the area are shown or not. If it is set to false then
  *     only employees whose default area matches the current area will be shown.
  *     It defaults to true since the list originally only showed all employees
  */
function employeeList($allEmployees = true)
{
	$showAllEmployees = "";
	if($allEmployees) {
		$showAllEmployees = "&e=true";
	} else {
		$showAllEmployees = "&e=false";
	}
	if (isset($_POST['terminated'])) $terminated=$_POST['terminated'];
	else $terminated=1;
	if (isset($_POST['searchValue']))
	{
		$searchValue = $_POST['searchValue'];
		$searchForLastEnteredSearchValue = 'showEmployeeList("'.$searchValue.'", employeeListTerminated);';
	}
	else
	{
		$searchValue="";
		$searchForLastEnteredSearchValue = 'showEmployeeList("", employeeListTerminated);';
	}
	$showTerminated = '';
	if(can("read", "59b0f789-6bb6-414d-a860-ca61fdcf372f"))//terminatedEmployees resource
		{
			$activeSelected = "";
			$terminatedSelected = "";
			$inactiveSelected = "";

			if ($terminated == 1)
			{
				$activeSelected = "selected ";
			}
			else if ($terminated == -1)
			{
				$terminatedSelected = "selected ";
			}
			else if ($terminated == 0)
			{
				$inactiveSelected = "selected ";
			}
		$showTerminated = '<div id="terminatedStuff" class="terminatedStuff"><span style="font-size: 1.5em;">View: </span>
					<select id="employeeListTerminatedButton" class="employeeListTerminatedButton" onChange="employeeListTerminated = this.value; terminatedEmployees();">
						<option '.$activeSelected.'value="1">Active</option>
						<option '.$terminatedSelected.'value="-1">Terminated</option>
						<option '.$inactiveSelected.'value="0">Inactive</option>
					</select>
				</div>';
		}
	echo '
	<!-- EMPLOYEELIST FUNCTION STARTS HERE -->
	<link rel="stylesheet" href="../includes/employeeListCssTemplate.css">
	<div id="menuPos" class="menuPos">
		<div class="searchHeading">
			<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">

				var employeeListTerminated='.$terminated.';

				function terminatedEmployees()
				{
					
					showEmployeeList(document.getElementById("nameSearch").value, employeeListTerminated);
				}

				// Ajax function to get the list of employees.
				function showEmployeeList(str, terminated)
				{
					var xmlhttp;
					var activeVal = 1;
					document.getElementById("searchValue").value = str;

					if (window.XMLHttpRequest)
					  {// code for IE7+, Firefox, Chrome, Opera, Safari
					  xmlhttp=new XMLHttpRequest();
					  }
					else
					  {// code for IE6, IE5
					  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
					  }
					xmlhttp.onreadystatechange=function()
					  {
					  if (xmlhttp.readyState==4 && xmlhttp.status==200)
					    {
					    document.getElementById("searchResultsList").innerHTML=xmlhttp.responseText;
					    }
					  }
				xmlhttp.open("GET","../includes/employeeListPhpFunction.php?q="+str+"&t="+terminated+"'.$showAllEmployees.'",true);
				xmlhttp.send();
				}

				// Function to get the information of the employee that is selected.
				function employeeListSubmit(employee)
				{
					var firstEmployee = document.getElementById("employeeListId0").title;
					if (employee==1) document.getElementById("employeeNetIdInput").value=firstEmployee;
					else document.getElementById("employeeNetIdInput").value=employee;
					document.getElementById("terminatedInput").value=employeeListTerminated;
					document.getElementById("employeeListForm").submit();
				}
			</SCRIPT>

			<form class="employeeListForm" id="employeeListForm" method="POST">
			<input id ="searchValue" type="hidden" name="searchValue" value="'.$searchValue.'" />
			<input id ="employeeNetIdInput" type="hidden" name="employeeNetId" value="default" />
			<input id ="terminatedInput" type="hidden" name="terminated" value="'.$terminated.'" />
			</form>

			<!--New search textbox-->
			<br />
			<div id="employeeListSearchTitleText" class="employeeListSearchTitleText">Search:</div>'.$showTerminated.'
		        <br />
			<div id="employeeListSearchBar" class="employeeListSearchBar"><input type="text" id="nameSearch" class="nameSearch" autofocus="autofocus" value="'.$searchValue.'" onkeypress="javascript:if (event.keyCode==13) employeeListSubmit(1);" onkeyup="showEmployeeList(this.value, employeeListTerminated)" size="16" placeholder=" Name or NetID" /></div>
			Suggestions: 
		</div>
	
		<div id="searchResultsList" class="searchResultsList"></div>

			<script type="text/javascript">
				'.$searchForLastEnteredSearchValue.'
			</script>
	</div>';
}

function passEncrypt($text) 
{ 
	return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, '41c/FcNYrRDyxDXV/nOTqTi7C8jszRG6', $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))); 
} 

function passDecrypt($text) 
{ 
	return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, '41c/FcNYrRDyxDXV/nOTqTi7C8jszRG6', base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))); 
}

function genericPage ($gpTableName, $netID, $associatedPermission = null)

// THIS FUNCTION NEEDS A TABLE IN THE DATABASE AND A PERMISSION TO FUNCTION. THE TABLE'S NAME SHOULD BE PASSED AS A PARAMETER
// AND THE PERMISSION SHOULD HAVE THE SAME NAME AS THE TABLE BUT WITH "Edit" APPENDED AT THE END. THE TABLE SHOULD HAVE
// THE FOLLOWING FIELDS (1) "content" (2) "submittedBy" (3) "dateSubmitted" AND (4) SOME KIND OF "ID" FIELD AS THE PRIMARY KEY

{
	global $db;
	if ($associatedPermission == null)
	{
		$associatedPermission = $gpTableName;
	}
	try {
		$contentQuery = $db->prepare("SELECT `table`, `content`, `submittedBy`, `dateSubmitted` FROM `genericPage` WHERE `table` = :tableName ORDER BY `contentId` DESC LIMIT 1");
		$contentQuery->execute(array(':tableName' => $gpTableName));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	$contentFields = $contentQuery->fetch(PDO::FETCH_ASSOC);
	if (isset($_POST['editorInput']))
	{			
		
		$whitespace = array("&#09;", "&#10;", "&#11;", "&#13;",	"&#12;", "&#32;", " ", "\r", "\n", "\r\n", "\t");
		$scriptCheck = str_replace($whitespace, "", $_POST['editorInput']);
		$scriptCheck = strtolower($scriptCheck);
		$oldContent = trim($contentFields['content']);
		$newContent = trim($_POST['editorInput']);
		if(strcmp($oldContent, $newContent) != 0)
		{
			if(stristr($scriptCheck, "<script"))
			{
				echo "<h2 style='color: red;'>Error: Page was not updated. Only basic HTML is allowed!</h2>";
				//insert user's comments into the database
				try {
					$insertQuery = $db->prepare("INSERT INTO `genericPage` (submittedBy, content, table, guid) VALUES (:netID, 'This attempt has been recorded.', :tableName, :guid)");
					$insertQuery->execute(array(':netID' => $netID, ':tableName' => $gpTableName, ':guid' => newGuid()));
				} catch(PDOException $e) {
					exit("error in query");
				}				
			}

			else
			{
				//insert user's comments into the database
				try {
					$insertQuery = $db->prepare("INSERT INTO `genericPage` (`submittedBy`, `table`, `content`, `guid`) VALUES (:netID, :tableName, :newContent, :guid)");
					$insertQuery->execute(array(':netID' => $netID, ':tableName' => $gpTableName, ':newContent' => $newContent, ':guid' => newGuid()));
				} catch(PDOException $e) {
					exit("error in query");
				}				
			}
		}
	}
	try {
		$contentQuery->execute(array(':tableName' => $gpTableName));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	$contentFields = $contentQuery->fetch(PDO::FETCH_ASSOC);
	//Be able to restore by date
	try {
		$versionQuery = $db->prepare("SELECT `table`, `content`, `submittedBy`, `dateSubmitted` FROM `genericPage` WHERE `table` = :tableName ORDER BY `dateSubmitted` DESC LIMIT 6");
		$versionQuery->execute(array(':tableName' => $gpTableName));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$selectRestoration = array();
	$restorationContent = array();
	$i=0;  
	while ($row = $versionQuery->fetch(PDO::FETCH_ASSOC))
	{
		$selectRestoration[$i] = $row['dateSubmitted'];
		$restorationContent[$i]= $row['content'];
		$i++;
	}
	try {
		$countQuery = $db->prepare("SELECT COUNT(contentId) FROM `genericPage` WHERE `table` = :tableName ORDER BY `dateSubmitted` DESC LIMIT 6");
		$countQuery->execute(array(':tableName' => $gpTableName));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$countResult = $countQuery->fetch(PDO::FETCH_NUM);
	$restorationCount = $countResult[0];
	 
	//...............HTML Code Here.................

	echo '<link rel="stylesheet" type="text/css" href="/includes/genericPageStyle.css" />
	      
	<br />';

	if (can("update", "531b8771-b2df-46ad-a431-4373681aee40")) // genericPage resource
	{
	echo '<div id="selectContentPoint">
			<form method="post" name="submitOption">
			<select onChange="this.form.submit()" name="getRestorationOption">
				<option value="default">View Restore Point</option>';
				for ($i = 0; $i<$restorationCount; $i++)
				{
				  echo '<option value='.$i.'>'.$selectRestoration[$i].'</option>';
				}
				echo '</select>
		
			</form>
				
		</div>
		<div id = "editButton">
			<button onclick="showCkEditor();">Edit</button>
		</div>';
	}
	
	echo '<div style="clear: both;"></div><div id="messageStyle">';if (isset($_POST['getRestorationOption']))
						{	
							$optionNumber = (int)$_POST['getRestorationOption'];
							echo $restorationContent[$optionNumber];
						} else 
							echo $contentFields['content'];
	echo '
	</div><div id="editorHolder" name="editorHolder" style="display:none"><textarea name="editor1" id="editor1"></textarea> </div><div style="clear: both;"></div>';
	if(can("update", "531b8771-b2df-46ad-a431-4373681aee40"))
	{
		echo '<div id = textAreaStyle>
			
			<form id="editorForm" method="POST">
			<input type ="hidden" name="editorInput" id="editorInput" value="" />
			</form>
			<!---- Java Scriptfunctions----->
			<script type="text/javascript">
			var liveUpdateEnabled = 1;
								
			var inEdit = 0;
			window.onload = function(){
				CKEDITOR.replace( "editor1" );
			};
			
			function showCkEditor() {
				if(inEdit){
					var editor_data = CKEDITOR.instances.editor1.getData();
					if (editor_data.search(/<script/i) != -1)
					{
						alert("Only basic HTML is allowed.");
					}
					else
					{
						$("#messageStyle").html(editor_data);
						$("#editorHolder").css("display","none");
						$("#messageStyle").css("display","block");
						inEdit = 0;
					}
				}else{
					var message = $("#messageStyle").html();
					CKEDITOR.instances.editor1.setData(message);
					var editHeight = $("#messageStyle").css("height");
					$(".cke_contents").css("height","675px");
					$("#messageStyle").css("display","none");
					$("#editorHolder").css("display","block");
					inEdit = 1;
				}
			}
			
			function submitNicTextArea()
			{
				var check = CKEDITOR.instances.editor1.getData();
				if (check == null)
				{
					check = document.getElementById("messageStyle").innerHTML;
				}
				var str = check;
				check = check.replace(/\s/g, "");
				check = check.replace(/\r/g, "");
				check = check.replace(/\n/g, "");
				check = check.replace(/\t/g, "");
				check = check.toLowerCase();
				if (check.search(/<script/i) != -1)
				{
					alert("Only basic HTML is allowed.");
				}
				else 
				{					
					document.getElementById("editorInput").value = str;
					document.getElementById("editorForm").submit();
				}
			
			}

			</script>
			<hr />
			<div id="submitInfo">
			Submitted by: '.$contentFields['submittedBy'].'
			<br />
			Date Modified: '.$contentFields['dateSubmitted'].'
			</div>
			<div id="submitButton">
				<button onclick="submitNicTextArea();">Submit</button>
			</div>
		</div>';
	}
} 

function areaSelect($netID){
	global $db;
	try {
		$areaQuery = $db->prepare("SELECT * FROM employeeAreas WHERE 1");
		$areaQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($area=$areaQuery->fetch(PDO::FETCH_ASSOC)){
		try {
			$hasPermissionQuery= $db->prepare("SELECT * FROM employeeAreaPermissions WHERE netid=:netID");
			$hasPermissionQuery->execute(array(':netID' => $netID));
		} catch(PDOException $e) {
			exit("error in query");
		}		
		$hasPermission=$hasPermissionQuery->fetch(PDO::FETCH_ASSOC);
		if($hasPermission[$area['ID']]=='1'){
			echo ("<option value=".$area['ID'].">".$area['area']."</option>");
		}
	}
}
function getEmployeeByuIdByNetId($netID){
	global $db;
	try {
		$idQuery = $db->prepare("SELECT byuIDnumber FROM employee WHERE netID=:netID");
		$idQuery->execute(array(':netID' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$result = $idQuery->fetch();
	$return = $result->byuIDnumber;
	return $return;
}

function nameByNetId($netID){
	global $db;
	try {
		$nameQuery = $db->prepare("SELECT firstName, lastName FROM employee WHERE netID=:netID");
		$nameQuery->execute(array(':netID' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$result = $nameQuery->fetch(PDO::FETCH_ASSOC);
	return $result['firstName']." ".$result['lastName'];
}

function getEmployeePhoneByNetId($netId){
	global $db;
	try {
		$phoneQuery = $db->prepare("SELECT `phone` FROM employee WHERE netID=:netID");
		$phoneQuery->execute(array(':netID' => $netId));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$result = $phoneQuery->fetch();
	return $result->phone;
}

function getEmployeeEmailByNetId($netId){
	global $db;
	try {
		$emailQuery = $db->prepare("SELECT `email` FROM employee WHERE netID=:netID");
		$emailQuery->execute(array(':netID' => $netId));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$result = $emailQuery->fetch();
	return $result->email;
}

function getEmployeeName(){
	global $netID, $db;
	try {
		$nameQuery = $db->prepare("SELECT firstName, lastName FROM employee WHERE netID=:netID");
		$nameQuery->execute(array(':netID' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$result = $nameQuery->fetch();
	return $result->firstName." ".$result->lastName;
}

function getEmployeeNameByNetId($netId) {
	global $db;
	try {
		$nameQuery = $db->prepare("SELECT firstName, lastName FROM employee WHERE netID=:netID");
		$nameQuery->execute(array(':netID' => $netId));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$result = $nameQuery->fetch();
	return $result->firstName." ".$result->lastName;
}

function getEmployeeArea(){
	global $netID, $db;
	try {
		$areaQuery = $db->prepare("SELECT area FROM employee WHERE netID=:netID");
		$areaQuery->execute(array(':netID' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$result = $areaQuery->fetch(PDO::FETCH_ASSOC);
	return $result['area'];
}

function getEmployeeAreaByNetId($netId) {
	global $db;
	try {
		$areaQuery = $db->prepare("SELECT area FROM employee WHERE netID=:netID");
		$areaQuery->execute(array(':netID' => $netId));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$result = $areaQuery->fetch();
	return $result->area;
}

function getEmployeeCertificationLevel($netID){
	global $db;
	try {
		$certLevelQuery = $db->prepare("SELECT certificationLevel FROM employee WHERE netID = :netID");
		$certLevelQuery->execute(array(':netID' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$result = $certLevelQuery->fetch();
	return $result->certificationLevel;
}

function getEmployeeWageByNetId($netID){
	global $db;
	try {
		$wageQuery = $db->prepare("SELECT wage FROM employeeWages WHERE netID = :netID");
		$wageQuery->execute(array(':netID' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$result = $wageQuery->fetch();
	return $result->wage;
}

function getEmployeeManagerByNetId($netID){
	global $db;
	try {
		$supervisorQuery = $db->prepare("SELECT supervisor FROM employee WHERE netID=:netID");
		$supervisorQuery->execute(array(':netID' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$result = $supervisorQuery->fetch();
	return $result->supervisor;
}

//**************************************

function getAreaName() {
	global $area, $db;
	try {
		$areaQuery = $db->prepare("SELECT longName FROM employeeAreas WHERE ID=:area");
		$areaQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$result = $areaQuery->fetch(PDO::FETCH_ASSOC);
	return $result['longName'];
}

function getAreaShortName(){
	global $area, $db;
	try {
		$areaQuery = $db->prepare("SELECT area FROM employeeAreas WHERE ID = :area");
		$areaQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$result = $areaQuery->fetch();
	return $result->area;
}

function getAreaShortNameById($area){
	global $db;
	try {
		$areaQuery = $db->prepare("SELECT area FROM employeeAreas WHERE ID=:area");
		$areaQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$result = $areaQuery->fetch();
	return $result->area;
}

function getAreaNameById($area){
	global $db;
	try {
		$nameQuery = $db->prepare("SELECT longName FROM employeeAreas WHERE ID=:area");
		$nameQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$result = $nameQuery->fetch();
	return $result->longName;
}

function getAreas(){
	global $netID, $db;
	try {
		$areaQuery = $db->prepare("SELECT area FROM employeeAreaPermissions WHERE netID=:netID");
		$areaQuery->execute(array(':netID' => $netID));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	$areas   = array();
	$areas[] = getEmployeeArea();
	
	while($row = $areaQuery->fetch()){
		$areas[]=$row->area;
	}
	return $areas;
}

function getHourSelect(){
	for ($i = 0.0; $i < 24.0; $i += getHourSize()) {
		echo "<option value=\"".hourToMilitary($i)."\">".hourToTimeFullSuffix($i)."</option>";
	}
}

function getHourSelectByHalfHour(){
	echo "
	<option value=\"00:00\">12:00 AM</option>
	<option value=\"00:30\">12:30 AM</option>
	<option value=\"01:00\">1:00 AM</option>
	<option value=\"01:30\">1:30 AM</option>
	<option value=\"02:00\">2:00 AM</option>
	<option value=\"02:30\">2:30 AM</option>
	<option value=\"03:00\">3:00 AM</option>
	<option value=\"03:30\">3:30 AM</option>
	<option value=\"04:00\">4:00 AM</option>
	<option value=\"04:30\">4:30 AM</option>
	<option value=\"05:00\">5:00 AM</option>
	<option value=\"05:30\">5:30 AM</option>
	<option value=\"06:00\">6:00 AM</option>
	<option value=\"06:30\">6:30 AM</option>
	<option value=\"07:00\">7:00 AM</option>
	<option value=\"07:30\">7:30 AM</option>
	<option value=\"08:00\">8:00 AM</option>
	<option value=\"08:30\">8:30 AM</option>
	<option value=\"09:00\">9:00 AM</option>
	<option value=\"09:30\">9:30 AM</option>
	<option value=\"10:00\">10:00 AM</option>
	<option value=\"10:30\">10:30 AM</option>
	<option value=\"11:00\">11:00 AM</option>
	<option value=\"11:30\">11:30 AM</option>
	<option value=\"12:00\">12:00 PM</option>
	<option value=\"12:30\">12:30 PM</option>
	<option value=\"13:00\">1:00 PM</option>
	<option value=\"13:30\">1:30 PM</option>
	<option value=\"14:00\">2:00 PM</option>
	<option value=\"14:30\">2:30 PM</option>
	<option value=\"15:00\">3:00 PM</option>
	<option value=\"15:30\">3:30 PM</option>
	<option value=\"16:00\">4:00 PM</option>
	<option value=\"16:30\">4:30 PM</option>
	<option value=\"17:00\">5:00 PM</option>
	<option value=\"17:30\">5:30 PM</option>
	<option value=\"18:00\">6:00 PM</option>
	<option value=\"18:30\">6:30 PM</option>
	<option value=\"19:00\">7:00 PM</option>
	<option value=\"19:30\">7:30 PM</option>
	<option value=\"20:00\">8:00 PM</option>
	<option value=\"20:30\">8:30 PM</option>
	<option value=\"21:00\">9:00 PM</option>
	<option value=\"21:30\">9:30 PM</option>
	<option value=\"22:00\">10:00 PM</option>
	<option value=\"22:30\">10:30 PM</option>
	<option value=\"23:00\">11:00 PM</option>
	<option value=\"23:30\">11:30 PM</option>";
}

function getHourSelectByHour(){
	echo "
	<option value=\"00:00\">12:00 AM</option>
	<option value=\"01:00\">1:00 AM</option>
	<option value=\"02:00\">2:00 AM</option>
	<option value=\"03:00\">3:00 AM</option>
	<option value=\"04:00\">4:00 AM</option>
	<option value=\"05:00\">5:00 AM</option>
	<option value=\"06:00\">6:00 AM</option>
	<option value=\"07:00\">7:00 AM</option>
	<option value=\"08:00\">8:00 AM</option>
	<option value=\"09:00\">9:00 AM</option>
	<option value=\"10:00\">10:00 AM</option>
	<option value=\"11:00\">11:00 AM</option>
	<option value=\"12:00\">12:00 PM</option>
	<option value=\"13:00\">1:00 PM</option>
	<option value=\"14:00\">2:00 PM</option>
	<option value=\"15:00\">3:00 PM</option>
	<option value=\"16:00\">4:00 PM</option>
	<option value=\"17:00\">5:00 PM</option>
	<option value=\"18:00\">6:00 PM</option>
	<option value=\"19:00\">7:00 PM</option>
	<option value=\"20:00\">8:00 PM</option>
	<option value=\"21:00\">9:00 PM</option>
	<option value=\"22:00\">10:00 PM</option>
	<option value=\"23:00\">11:00 PM</option>";
}

function monthSelect(){
	echo"
		<option value='january'>January</option>
		<option value='february'>February</option>
		<option value='march'>March</option>
		<option value='april'>April</option>	
		<option value='may'>May</option>
		<option value='june'>June</option>
		<option value='july'>July</option>
		<option value='august'>August</option>
		<option value='september'>September</option>
		<option value='october'>October</option>
		<option value='november'>November</option>
		<option value='december'>December</option>		
		";
}

		
function getEmployeeLookup($area) {
	global $db;
	$lookup = array();
	try {
		$employeeQuery = $db->prepare("SELECT netID, firstName, lastName FROM employee WHERE area=:area");
		$employeeQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	while ($employee = $employeeQuery->fetch(PDO::FETCH_ASSOC)) {
		$lookup[] = $employee;
	}

	return $lookup;
}

function getHourTypeLookup($area) {
	global $db;
	$lookup = array();
	try {
		$hourTypeQuery = $db->prepare("SELECT value, name FROM scheduleHourTypes WHERE area=:area");
		$hourTypeQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}	
	while ($hourType = $hourTypeQuery->fetch(PDO::FETCH_ASSOC)) {
		$lookup[] = $hourType;
	}
	return $lookup;
}

function getEmployeeNameFromLookup($lookup, $netID) {
	for($i = 0; $i < count($lookup); $i++) {
		$emp = $lookup[$i];
		if($emp["netID"] == $netID)
			return $emp["firstName"]." ".$emp["lastName"];
	}
}

function getHourNameFromLookup($lookup, $value) {
	for($i = 0; $i < count($lookup); $i++) {
		$hour = $lookup[$i];
		if($hour["value"] == $value)
			return $hour["name"];
	}
}

function updateEmployeeShift($employee,$manager){
	global $area, $db;
	try {
		$teamQuery = $db->prepare("SELECT `ID` FROM `teams` WHERE `ID` IN (SELECT `teamID` FROM `teamMembers` WHERE `netID` = :employee) AND `isShift` = 1");
		$teamQuery->execute(array(':employee' => $employee));

		$teamInfo = $teamQuery->fetch(PDO::FETCH_ASSOC);

		$removeOldTeamQuery = $db->prepare("DELETE FROM teamMembers WHERE netID = :employee AND teamID = :teamID");
		$removeOldTeamQuery->execute(array(':employee' => $employee, ':teamID' => $teamInfo['ID']));

		$newTeamQuery = $db->prepare("SELECT `ID` FROM `teams` WHERE `lead` = :manager AND `isShift` = '1'");
		$newTeamQuery->execute(array(':manager' => $manager));

		$newTeamInfo = $newTeamQuery->fetch(PDO::FETCH_ASSOC);

		$addNewTeamQuery = $db->prepare("INSERT INTO teamMembers (netID,teamID,area,guid) VALUES (:employee,:newID,:area,:guid)");
		$addNewTeamQuery->execute(array(':employee' => $employee, ':newID' => $newTeamInfo['ID'], ':area' => $area, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

function pullTeams($area){
	global $db;
	try {
		$teamsQuery = $db->prepare("SELECT * FROM teams WHERE area = :area");
		$teamsQuery->execute(array(':area' => $area));
	} catch(PDOException $e) {
		exit("error in query");
	}
	
	while($cur = $teamsQuery->fetch()){
		echo "<option value='".$cur->ID."' id='".$cur->email."_|".$cur->lead."' >".$cur->name."</option>";
	}
}

?>
