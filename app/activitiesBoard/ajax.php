<?php
require('../includes/includeMeBlank.php');

//Categories used in Printlog for array merging and json that is sent to javascript
	$url2 = getenv("BYU_CALENDAR_URL")."/api/Categories";
	$ch2 = curl_init();
	curl_setopt($ch2, CURLOPT_URL, $url2);
	curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
	$json2 = curl_exec($ch2);
	curl_close ($ch2);
	$categoriesList=json_decode($json2);
//this is the json used in both the printLog and edition conditional statements
		if(isset($_GET['selectedCategories']))
		{
			$selectedCategories=implode(',',$_GET['selectedCategories']);
			foreach($categoriesList as $categoryId)
			{
				if($categoryId->ParentCategoryId==null)
				{
					$defaultCategories[]=$categoryId->CategoryId;
				}
			}
			$defaultCategories=implode(',', $defaultCategories);
		}
		else
		{	//api is flawed and so I instead of sending a default category code I'm sending all the upmost parent categories. Without this the page takes almost 12 seconds to load.
			foreach($categoriesList as $categoryId)
			{
				if($categoryId->ParentCategoryId==null)
				{
					$selectedCategories[]=$categoryId->CategoryId;
				}
			}
			$selectedCategories=implode(',', $selectedCategories);
			$defaultCategories=$selectedCategories;
		}
		if(isset($_GET['startDate']) and isset($_GET['dateRange'])){
		$startDate=$_GET['startDate'];
		$dateRange=$_GET['dateRange'];
		if($dateRange=="day"){ $endDate=date('Y-m-d', strtotime("+0 day", strtotime($startDate))); }
		if($dateRange=="3days"){ $endDate=date('Y-m-d', strtotime("+2 day", strtotime($startDate))); }
		if($dateRange=="week"){ $endDate=date('Y-m-d', strtotime("+1 week", strtotime($startDate))); }
		if($dateRange=="month"){ $endDate=date('Y-m-d', strtotime("+1 month", strtotime($startDate))); }
		if($dateRange=="year"){ $endDate=date('Y-m-d', strtotime("+1 year", strtotime($startDate))); }
		} else {
			$startDate="{startdate}";
			$endDate="{enddate}";
		}
	$url3 = getenv("BYU_CALENDAR_URL")."/api/Events?startdate=".$startDate."&enddate=".$endDate."&categories=".$selectedCategories."&price={price}";
	$ch3 = curl_init();
	curl_setopt($ch3, CURLOPT_URL, $url3);
	curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
	$json3 = curl_exec($ch3);
	curl_close ($ch3);
	$calendarItems3 = json_decode($json3);

//This removes entries from the database
if(isset($_GET['removeEntry'])) {
	try {
		$deleteQuery = $db->prepare("DELETE FROM activitiesBoard WHERE `title`=:title AND `date`=:day AND `startTime`=:start AND `id`=:id");
		$success = $deleteQuery->execute(array(':title' => $_GET['titleRemove'], ':day' => $_GET['dateRemove'], ':start' => $_GET['startTime'], ':id' => $_GET['occurrenceId']));
	} catch(PDOException $e) {
		$success = false;
	}
	if($success) {
		echo "Your entry was successfully removed"; 
	} else {
		echo "Your entry could not be removed";
	}
}
//This submits entries to the database
if(isset($_GET['submitEntry'])) {
	$submitDate=$_GET['submitDate'];
	$submitTitle=$_GET['submitTitle'];
	$submitCategory=$_GET['submitCategory'];
	$submitTime=$_GET['submitTime'];
	$submitEndTime=$_GET['submitEndTime'];
	if(!isset($_GET['submitDescription'])) { 
		$submitDescription='';
	} else {
		$submitDescription=$_GET['submitDescription'];
	}
	try {
		$insertQuery = $db->prepare("INSERT INTO activitiesBoard (`date`,`startTime`,`endTime`,`title`,`description`,`categoryId`,`guid`) VALUES (:day,:time,:end,:title,:description,:category,:guid)");
		$insertQuery->execute(array(':day'   => $submitDate,  ':time'        => $submitTime,        ':end'      => $submitEndTime, 
									':title' => $submitTitle, ':description' => $submitDescription, ':category' => $submitCategory, ':guid' => newGuid()));
	} catch(PDOException $e) {
		exit("error in query");
	}
}

//this takes care of submitting editions to the database
if(isset($_GET['submitEdit']))
{
	$editTitle=$_GET['editTitle'];
	$editDescription=$_GET['editDescription'];
	$editDate=$_GET['editDate'];
	$editCategory=$_GET['editCategory'];
	$editStartTime=date('H:i', strtotime($_GET['editStartTime']));
	$editEndTime=date('H:i', strtotime($_GET['editEndTime']));
	$originalId=(object) $_GET['originalId'];
	
	foreach($calendarItems3 as $api)
	{
		if((int) $originalId->occurrenceId == $api->OccurrenceId)
		{
			$originalInfo=$api;
		}
	}
	
	if($originalId->database==1)
	{
		//editions to user entries
		try {
			$updateQuery = $db->prepare("UPDATE activitiesBoard SET `categoryId`=:category, `date`=:day, `startTime`=:start, `endTime`=:end, `title`=:title, `description`=:description WHERE `id`=:id");
			$updateQuery->execute(array(':category' => $editCategory,          ':day'         => $editDate,                    ':start' => $editStartTime, ':end' => $editEndTime, 
										':title'    => $editTitle, ':description' => $editDescription, ':id'    => $originalId->occurrenceId));
		} catch(PDOException $e) {
			exit("error in query");
		}
		echo "Your edition was successfully submitted.";
	}
	else
	{
		//editions to calendar entries
		try {
			$query = $db->prepare("SELECT `id` FROM activitiesBoard WHERE `id`=:id");
			$query->execute(array(':id' => $originalInfo->OccurrenceId));
		} catch(PDOException $e) {
			exit("error in query");
		}
		
		if($row = $query->fetch())
		{
			try {
				$updateQuery = $db->prepare("UPDATE activitiesBoard SET `categoryId`=:category, `date`=:day, `startTime`=:start, `endTime`=:end,`title`=:title, `description`=:description WHERE  `id`=:id");
				$updateQuery->execute(array(':category' => $editCategory,          ':day'         => $editDate,                    ':start' => $editStartTime, ':end' => $editEndTime,
									  ':title'    => $editTitle, ':description' => $editDescription, ':id'    => $originalInfo->OccurrenceId));
			} catch(PDOException $e) {
				exit("error in query");
			}
			echo "Your edition was successfully submitted.";
		}
		else
		{
			try {
				$insertQuery = $db->prepare("INSERT INTO activitiesBoard (`categoryId`,`id`,`originalTitle`,`originalDate`,`originalStartTime`,`originalDescription`,`date`,
											`startTime`,`endTime`,`originalEndTime`,`title`,`description`,`originalCategoryId`,`guid`) VALUES 
											(:category,:id,:title,:day,:start,:description,:editDate,:editStart,:editEnd,:endDate,:editTitle,:editDescr,:categoryId,:guid)");
				$insertQuery->execute(array(':category' => $editCategory, ':id' => $originalInfo->OccurrenceId, ':title' => $originalInfo->Title, 
											':day' => date('Y-m-d', strtotime($originalInfo->StartDateTime)), ':start' => date('H:i', strtotime($originalInfo->StartDateTime)),
											':description' => trim($originalInfo->Description), ':editDate' => $editDate, ':editStart' => $editStartTime, ':editEnd' => $editEndTime,
											':endDate' => date('H:i', strtotime($originalInfo->EndDateTime)), ':editTitle' => $editTitle, ':editDescr' => $editDescription, 
											':categoryId' => $originalInfo->CategoryId,':guid' => newGuid()));
			} catch(PDOException $e) {
				exit("error in query");
			}
			echo "Your edition was successfully submitted.";
		}
	}
}
//Change original information with new information from the calendar and adds new information to applicable editions such as description
if(isset($_GET['changeOriginal']))
{
	$occurrenceIdNewInfo=$_GET['occurrenceIdNewInfo'];
	foreach($occurrenceIdNewInfo as &$idOfEvent) //javascript passes integers as strings, here I'm converting them back to integers for the comparison below
	{
		$idOfEvent=(int) $idOfEvent;
	}
	foreach($occurrenceIdNewInfo as $eventId)
	{
		foreach($calendarItems3 as $api)
		{	
			if($api->OccurrenceId==$eventId)
			{
				try {
					$activitiesQuery = $db->prepare("SELECT * FROM activitiesBoard WHERE `id=:id");
					$activitiesQuery->execute(array(':id' => $eventId));
				} catch(PDOException $e) {
					exit("error in query");
				}
				while($row=$activitiesQuery->fetch(PDO::FETCH_ASSOC))
				{
					$mergedInfo['description']=$row['description']." ".$api->Description;
					try {
						$updateQuery = $db->prepare("UPDATE activitiesBoard SET `description`=:descr, `title`=:title, `originalDescription`=:origDescr, `originalTitle`=:origTitle,
													`originalDate`=:origDate, `originalStartTime`=:origStart, `originalEndTime`=:origEnd, `date`=:day, `startTime`=:start, 
													`endTime`=:end, `categoryId`=:category WHERE `id`=:id");
						$updateQuery->execute(array(':descr'    => $mergedInfo['description'], ':title' => $api->Title, ':origDescr' => $api->Description, ':origTitle' => $api->Title,
													':origDate' => date('Y-m-d', strtotime($api->StartDateTime)),       ':origStart' => date('H:i', strtotime($api->StartDateTime)),
													':origEnd'  => date('H:i', strtotime($api->EndDateTime)),           ':day'       => date('Y-m-d', strtotime($api->StartDateTime)),
													':start'    => date('H:i', strtotime($api->StartDateTime)),         ':end'       => date('H:i', strtotime($api->EndDateTime)),
													':category' => $api->CategoryId,                                    ':id'        => $eventId));
					} catch(PDOException $e) {
						exit("error in query");
					}
				}
				$flag=true;
			}
		}
	}
	if($flag==true)
	{
		echo "Changes have been successfully submitted.";
	}
}
//This whole if statement takes care of what is displayed on the page
if(isset($_GET['printLog']))
{
	//creates a comparison array that compares information from the database to information received from the calendar api. 
	//It only contains database entries that have an original title, meaning that they were editions to items already present in the calendar. 
	//The idArrays is used to keep track of which ids have been modified from the calendar and stored in the database, 
	//the calendar events that have ids that match the ones found in the database are not added to what is displayed.
	$comparison=array();
	$idArray=array();
	try {
		$activitiesQuery = $db->prepare("SELECT * FROM activitiesBoard WHERE `originalTitle`!=''");
		$activitiesQuery->execute();
	} catch(PDOException $e) {
		exit("error in query");
	}
	while($result=$activitiesQuery->fetch(PDO::FETCH_ASSOC))
	{
		$comparison[]=(object) array("originalDate"=>$result['originalDate'], 'originalTitle'=>$result['originalTitle'], "originalDescription"=>$result['originalDescription'], 
									 "originalStartTime"=>$result['originalStartTime'], "originalEndTime"=>$result["originalEndTime"] , "OccurrenceId"=>$result['id']);
		$idArray[]=$result['id'];
	}

	//this takes care of making the json that has the information about which events were changed and what was changed.

	if($comparisonQueryResult!=false && isset($calendarItems3))
	{	
		$numChanges=0;
		$occurrenceIdchanged=array();
		$changes = array();
		foreach($comparison as $compare)
		{
			foreach($calendarItems3 as $api)
			{
				if($compare->OccurrenceId==$api->OccurrenceId)//add an OR comparison using the startTime and endTime
				{ 
					if($compare->originalDate!=date('Y-m-d', strtotime($api->StartDateTime)) || $compare->originalStartTime!=date('H:i:s', strtotime($api->StartDateTime)) || 
					   $compare->originalEndTime!=date('H:i:s', strtotime($api->EndDateTime)) || trim($compare->originalTitle)!=trim($api->Title) || 
					   trim($compare->originalDescription)!=trim($api->Description))
					{
						//if any change is found in the event information this will count that event
						//REMEBER THAT THE COMPARISONS BELOW ARE WRONG SO THAT YOU CAN TEST THE APP.
						$numChanges++;
						$occurrenceIdchanged[]= (int) $compare->OccurrenceId;
					}
					if($compare->originalDate!=date('Y-m-d', strtotime($api->StartDateTime)))
					{
						$changes[$compare->OccurrenceId]["originalDate"] = date('Y-m-d', strtotime($compare->originalDate));
						$changes[$compare->OccurrenceId]["newDate"] = date('Y-m-d', strtotime($api->StartDateTime));
					}				
					if($compare->originalStartTime!=date('H:i:s', strtotime($api->StartDateTime)))
					{
						$changes[$compare->OccurrenceId]["originalStartTime"] = date('h:i a', strtotime($compare->originalStartTime));
						$changes[$compare->OccurrenceId]["newStartTime"] = date('h:i a', strtotime($api->StartDateTime));
					}
					if($compare->originalEndTime!=date('H:i:s', strtotime($api->EndDateTime)))
					{
						$changes[$compare->OccurrenceId]["originalEndTime"] = date('h:i a', strtotime($compare->originalEndTime));
						$changes[$compare->OccurrenceId]["newEndTime"] = date('h:i a', strtotime($api->EndDateTime));
					}
					if(trim($compare->originalTitle)!=trim($api->Title))
					{
						$changes[$compare->OccurrenceId]["originalTitle"] = $compare->originalTitle;
						$changes[$compare->OccurrenceId]["newTitle"] = trim($api->Title);
					}
					if(trim($compare->originalDescription)!=trim($api->Description))
					{		
						$changes[$compare->OccurrenceId]["originalDescription"] = (trim(strip_tags($compare->originalDescription)));
						$changes[$compare->OccurrenceId]["newDescription"] = (trim(strip_tags($api->Description)));					
					}
				}
			}
		}
		$changes=json_encode($changes);
	
		$occurrenceIdchanged=json_encode($occurrenceIdchanged);
	
		if($numChanges!=0)
		{
			echo "<script>eventChange(".$numChanges.", ".$occurrenceIdchanged.", '".$changes."')</script>";
		}
	}
	//creates a category filter that matches the calendar filter for the information drawn from the database. It selects the parent and all its children and subchildren
	$selectedCategories=explode(',', $selectedCategories);

	$categoryFilter=array();
	function categoriesFilter($current)
	{
		global $categoriesList, $categoryFilter, $defaultCategories;
		$children=array();
		foreach($current as $currentVal)
		{
			if($currentVal!=$defaultCategories)
			{
				foreach($categoriesList as $api)
				{
					if(array_search($currentVal, $categoryFilter)===false)
					{
						$categoryFilter[]=(int) $currentVal;
					}
					if($currentVal==$api->ParentCategoryId)
					{
						$children[]=$api->CategoryId;
						$categoryFilter[]=$api->CategoryId;
					}
				}
			}
		}
		if($children!=array())
		{
		categoriesFilter($children);
		}
	}
	categoriesFilter($selectedCategories);


	//This below is to merge the data from the database with the API in a new array that is used afterwards. 
	//The $allDates gets all the dates with information from both arrays and then sorts the dates in chronological order. 
	//The dates are then used to make a new array with events ordered by date so that it is displayed in the right order on the page. 
	//so even though it worked well in reseting the array's internal pointer it quickly displayed an error on the page before the right information was displayed.

	try {
		$dateQuery = $db->prepare("SELECT * FROM activitiesBoard WHERE `date` >=:start AND `date`<=:end");
		$dateQuery->execute(array(':start' => $startDate, ':end' => $endDate));
	} catch(PDOException $e) {
		exit("error in query");
	}
	$allDates=array();
	foreach($calendarItems3 as $value)
	{
		if(array_search(date("Y-m-d", strtotime($value->StartDateTime)), $allDates)===false)
		{
			$allDates[]=date("Y-m-d", strtotime($value->StartDateTime));
		}
	}
	while($row=$dateQuery->fetch(PDO::FETCH_ASSOC))
	{
		if(array_search(date("Y-m-d", strtotime($row['date'])), $allDates)===false && isset($row['date']))
		{
			$allDates[]=date("Y-m-d", strtotime($row['date']));
		}
	}
	sort($allDates, SORT_STRING);

	//creates formatted array
	error_reporting(0);
	$formattedArray=array();
	$selectedCategories=implode(',',$selectedCategories);
	foreach($allDates as $date)
	{
		if($selectedCategories==$defaultCategories)
		{
			try {
				$dateQuery->execute();
			} catch(PDOException $e) {
				exit("error in query");
			}
			
			while($row=$dateQuery->fetch(PDO::FETCH_ASSOC))
			{
				if($date==date("Y-m-d", strtotime($row['date'])))
				{	
					$formattedArray[]=(object) array("StartDateTime"=>$row['date'], 'Title'=>$row['title'], "Description"=>$row['description'], 'database'=>1, "startTime"=>$row['startTime'], "OccurrenceId"=>$row['id'], "endTime"=>$row["endTime"], "latitude"=>$row["latitude"], "longitude"=>$row["longitude"],"allDay"=>$row["allDay"], "imageUrl"=>$row["imageUrl"], "MoreInformationUrl"=>$row["moreInfoUrl"], "categoryId"=>$row['categoryId']);
				}
			}
		}
		else
		{
			try {
				$dateQuery->execute();
			} catch(PDOException $e) {
				exit("error in query");
			}

			while($row=$dateQuery->fetch(PDO::FETCH_ASSOC))
			{
				if(in_array($row['categoryId'], $categoryFilter)!=false)
				{
					if($date==date("Y-m-d", strtotime($row['date'])))
					{
						$formattedArray[]=(object) array("StartDateTime"=>$row['date'], 'Title'=>$row['title'], "Description"=>$row['description'], 'database'=>1, "startTime"=>$row['startTime'], "OccurrenceId"=>$row['id'], "endTime"=>$row["endTime"], "latitude"=>$row["latitude"], "longitude"=>$row["longitude"],"allDay"=>$row["allDay"], "imageUrl"=>$row["imageUrl"], "MoreInformationUrl"=>$row["moreInfoUrl"], "categoryId"=>$row['categoryId']);
					}
				}
			}	
		}
		foreach($calendarItems3 as $api)
		{
			if($comparison!=array() && $idArray!=array())
			{		
				if($date==date("Y-m-d", strtotime($api->StartDateTime)) && array_search($api->OccurrenceId, $idArray)===false)
				{
					$formattedArray[]=(object) array("StartDateTime"=>$api->StartDateTime, "Title"=>$api->Title, "Description"=>$api->Description, "OccurrenceId"=>$api->OccurrenceId, "EndDateTime"=>$api->EndDateTime, "Latitude"=>$api->Latitude, "Longitude"=>$api->Longitude, "ImgUrl"=>$api->ImgUrl, "AllDay"=>$api->AllDay, "MoreInformationUrl"=>$api->MoreInformationUrl, "CategoryId"=>$api->CategoryId);
				}
			}
			else
			{
				if($date==date("Y-m-d", strtotime($api->StartDateTime)))
				{
					$formattedArray[]=(object) array("StartDateTime"=>$api->StartDateTime, "Title"=>$api->Title, "Description"=>$api->Description, "OccurrenceId"=>$api->OccurrenceId, "EndDateTime"=>$api->EndDateTime, "Latitude"=>$api->Latitude, "Longitude"=>$api->Longitude, "ImgUrl"=>$api->ImgUrl, "AllDay"=>$api->AllDay, "MoreInformationUrl"=>$api->MoreInformationUrl, "CategoryId"=>$api->CategoryId);
				}
			}
		}
	}
	$comparison=array();
	$idArray=array();
	error_reporting(-1);

	//This gets the information from the API and database merged array and display it on the page in chronological order.
	$sameDate=array();
	$count=0;
	foreach($formattedArray as $value)
	{
		$curDate=date("l d, F Y", strtotime($value->StartDateTime));
		if(array_search($curDate, $sameDate)===false)
		{	
			echo "<br><div class='calendarDay'>".$curDate."</div>";
			$sameDate[]=$curDate;
		}
//-----------------------------------------------------takes care of events found in the database--------------------------------------------------------	
			if(isset($value->database)) 
			{
				echo "<br><a class='calendarEvent selectable' id='calendarTitle".$count."' onClick='$(\"#descriptionBox".$count."\").slideToggle();' 
								onMouseOver='$(\".activityButtons\").button();$(\".editButtons\").css(\"display\",\"none\"); $(\"#eventEdit".$count."\").css(\"display\",\"inline\");
								if(timed!=undefined)
								{
								window.clearTimeout(timed);
								}' 
								onMouseOut='editButtonShow(\"eventEdit".$count."\");'>".$value->Title."</a>
						
							<input type='button' value='Edit' id='eventEdit".$count."' class='activityButtons editButtons' style='display:none; padding:.1em .4em !important' 
								onclick='editWindow(\"".$value->OccurrenceId."\",\"".date('Y-m-d', strtotime($value->StartDateTime))."\" , \"".strtotime($value->startTime)."\" ,\"calendarTitle".$count."\", \"description".$count."\", \"".$value->database."\", \"".strtotime($value->endTime)."\", \"".$value->categoryId."\");' 
								onMouseOver='window.clearTimeout(timed); $(\"#eventEdit".$count."\").css(\"display\",\"inline\")' 
								onMouseOut=' $(\"#eventEdit".$count."\").css(\"display\",\"none\")'>
						
						<input type='button' 
							onclick='removeEntry(\"calendarTitle".$count."\", \"".$value->StartDateTime."\", \"".$value->startTime."\", \"".$value->OccurrenceId."\")' style='float:right' value='Remove Entry' class='ui-button ui-widget ui-state-default ui-corner-all'>";					
			
				echo "<div class='eventDescription' id='descriptionBox".$count."' style='display:none;'>";			
				echo "<span id='eventStartTimeDescription".$count."' style='float:left;'>Start Time: ".date('h:i a', strtotime($value->startTime))."<br> End Time: ".date('h:i a', strtotime($value->endTime))."</span><br><br><br>";		
				echo "<div class='eventDescription' id='description".$count."'>".$value->Description."</div>";
				echo "</div><br>";
				
				//remove on click attribute if there is no description available for event
				echo "<script> if($('#descriptionBox".$count."').text()==''){ $('#calendarTitle".$count."').removeAttr('onClick');$('#calendarTitle".$count."').attr('style', 'text-decoration:none'); $('#calendarTitle".$count."').attr('class', 'calendarEvent'); }</script>";
			}
//------------------------------------------------------Information in array from calendar API--------------------------------------------------------------
			else
			{
				echo "<br><a class='calendarEvent selectable' id='calendarTitle".$count."' onClick='$(\"#descriptionBox".$count."\").slideToggle();' 
						onMouseOver='$(\".activityButtons\").button();$(\".editButtons\").css(\"display\",\"none\"); $(\"#eventEdit".$count."\").css(\"display\",\"inline\");
						if(timed!=undefined)
						{
						window.clearTimeout(timed);
						}' 
						onMouseOut='editButtonShow(\"eventEdit".$count."\");'>".$value->Title."</a>
						<input type='button' value='Edit' id='eventEdit".$count."'  class='activityButtons editButtons' style='display:none; padding:.1em .4em !important' 
						onclick='editWindow(\"".$value->OccurrenceId."\",\"".date('Y-m-d', strtotime($value->StartDateTime))."\", \"".strtotime($value->StartDateTime)."\" ,\"calendarTitle".$count."\", \"description".$count."\", 0 , \"".strtotime($value->EndDateTime)."\", \"".$value->CategoryId."\");' 		
						onMouseOver='window.clearTimeout(timed); $(\"#eventEdit".$count."\").css(\"display\",\"inline\")' onMouseOut=' $(\"#eventEdit".$count."\").css(\"display\",\"none\")'>";
			
				echo "<div class='eventDescription' id='descriptionBox".$count."' style='display:none;'>";			
				echo "<span id='eventStartTimeDescription".$count."' style='float:left;'>Start Time: ".date('h:i a', strtotime($value->StartDateTime))."<br> End Time: ".date('h:i a', strtotime($value->EndDateTime))."</span><br><br>";		
				echo "<div class='eventDescription' id='description".$count."'>".$value->Description."</div>";
				echo "</div><br>";
				
				echo "<script> if($('#descriptionBox".$count."').text()==''){ $('#calendarTitle".$count."').removeAttr('onClick');$('#calendarTitle".$count."').attr('style', 'text-decoration:none'); $('#calendarTitle".$count."').attr('class', 'calendarEvent'); }</script>";
			}

		$count++;
	}
	$sameDate=array();
	$count=0; 
}

//this generates the category options displayed on the page and has the call to the javascript function that keeps track of the category ids selected
if(isset($_GET['categories']))
{
	$url2 = getenv("BYU_CALENDAR_URL")."/api/Categories";
	$ch2 = curl_init();
	curl_setopt($ch2, CURLOPT_URL, $url2);
	curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
	$json2 = curl_exec($ch2);
	curl_close ($ch2);
	$calendarItems2 = json_decode($json2);

	echo "<h3 style='text-transform: uppercase;'>Categories Filter</h3>";
	function categories($parent)
	{
		global $calendarItems2;
		foreach($calendarItems2 as $api)
		{
			if($api->ParentCategoryId==$parent)
			{
				echo "<input type='checkbox' class='category' id='category".$api->CategoryId."' onclick='showHide(\"parentCategory".$api->CategoryId."\");categoryFilter(\"".$api->CategoryId."\");'><label for='category".$api->CategoryId."'>".$api->Name."</label><br>";
				echo "<div id='parentCategory".$api->CategoryId."' class='invisible' style='padding-left:20px'>";
				echo "<ul>";
				categories($api->CategoryId);
				echo "</ul>";
				echo "</div>";
			}
		}
	}
	foreach($calendarItems2 as $category)
	{
		if($category->ParentCategoryId===null)
		{
			echo "<input type='checkbox' class='category' id='category".$category->CategoryId."' onclick='showHide(\"parentCategory".$category->CategoryId."\");categoryFilter(\"".$category->CategoryId."\");'><label for='category".$category->CategoryId."'>".$category->Name."</label><br>";
			echo "<ul>";
			echo "<div id='parentCategory".$category->CategoryId."' class='invisible' style='padding-left:20px'>";
			categories($category->CategoryId);
			echo "</div>";
			echo "</ul>";
		}
	}	
}
//This sends the json from the categories API to javascript to keep track of selected categories and show only information for them
if(isset($_GET['categoryJson']))
{
	echo $json2;
}

?>

