<?php 
require('../includes/includeMeBlank.php');

// HEY THIS IS A REMINDER REMEMBER TO MAKE THIS SCRAPE THE RFC'S PAGE TO GET THE PERSON TO CONTACT IN CASE OF ANY PROBLEMS

function printItem($item, $currentTime)
{
	global $count, $print, $closestRfc, $closestCheck;
		$count++;
		
		if (!isset($item->START_TIME))
		{
			$item->START_TIME = 'N/A';
		}
		if (!isset($item->END_TIME))
		{
			$item->END_TIME = 'N/A';
		}

		$start = strtotime($item->START_TIME);
		$startStr = strtotime("+12 minutes", $start);

		$end = strtotime($item->END_TIME);
		$endStr = strtotime("+12 minutes", $end);

		$compareStart = strtotime("+20 minutes", $start);
		$compareEnd = strtotime("+20 minutes", $end);

		$formatedStart = date('j M g:iA', $start); // I KNOW I MISSPELLED FORMATED, FEEL FREE TO FIX IT
		$formatedEnd = date('j M g:iA', $end);

		// IF MORE THAN 15 MINUTES REMIND SUPERVISOR TO CHECK ON RFC
		if ( $currentTime > $startStr &&  $currentTime < $compareStart)
		{
			$print .= "<div class='reminderAlert' title='RFC $item->NAME. should be starting.'></div>";
		}
		
		else if ($currentTime > $endStr && $currentTime < $compareEnd && $item->RFCSTATUS != 'Finished')
		{
			$print .= "<div class='reminderAlert' title='RFC $item->NAME. should be finished, please check on it.'></div>";
		}
		if ( (!$closestCheck) && (time() <= strtotime($item->CALENDARDATE)) )
		{
			$closestRfc = '<a name="closestRfc"></a>';
			$closestCheck = true;
		}
		if (!isset($item->RESPONSIBLE_PARTY))
		{
			$item->RESPONSIBLE_PARTY = 'N/A';
		}
		if (!isset($item->FAILURE_NOTIFICATION))
		{
			$item->FAILURE_NOTIFICATION = 'N/A';
		}
		$print .= '<tr><td sorttable_customkey="'.date('YmdHis', strtotime($item->CALENDARDATE)).'">'.$closestRfc.$formatedStart.'<br /><br />'.$formatedEnd.'</td><td ><a title="Notify:\''.$item->FAILURE_NOTIFICATION.'\' Responsible:\''.$item->RESPONSIBLE_PARTY.'\'" href="javascript:newwindow(\''.getenv("CHANGE_CALENDAR_BASE_URL").'/ci/main.php?from=calendar&itemid='.$item->ITEMID.'\', 660)" '.(($item->TYPENAME=="Request For Change")?"style=\"color:#0000FF;\"":"").(($item->TYPENAME=="Standard Change")?"style=\"color:#00AA00;\"":"").(($item->TYPENAME=="Calendar Note")?"style=\"color:#FF6600;\"":"").'>'.$item->NAME.'</a></td><td>'.$item->PURPOSE.'</td><td>'.$item->RFCSTATUS.'</td></tr>';

		$closestRfc = '';

	
}

$from = date("Y-m-d+H:i:s", strtotime("-12 hours"));
$now = date('YmdHis');
$to = date("Y-m-d+H:i:s", strtotime("+12 hours"));

$url = getenv("CHANGE_CALENDAR_BASE_URL")."/rest/v1/change_calendar/$from/$to.json";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, 'api_key='.getenv("CHANGE_CALENDAR_API_KEY"));
$json = curl_exec($ch);
curl_close ($ch);

$rfc = json_decode($json);

$print = '';
$count = 0;
$closestRfc = '';
$closestCheck = false;

if ($rfc != null)
{
	$items = $rfc->item;

	$print .= '<table id="rfcTable" class="sortable">';
	$print .= '<th style="width: 11%;">Date</th><th style="width: 24%;">Name</th><th style="width: 55%;" class="sorttable_nosort">Purpose</th><th style="width: 10%;">Status</th>';

	if (is_array($items))
	{
		foreach($items as $item)
		{
			printItem($item, $now);
		}
	}
	else 
	{
		printItem($items, $now);
	}

	$print .= '</table>';
	if ($count != 0)
	{
		echo $print;
	}
	else
	{
		echo '<br /><div style="width: 100%; text-align: center; font-weight: bold; margin-bottom: 8px;"><table style="margin-left: auto; margin-right: auto;"><tr><th style="height: 2em; vertical-align: middle;">  There are currently no RFCs.  </th></tr></table></div>';
	}
}
else
{
	echo '<br /><div style="width: 100%; text-align: center; font-weight: bold; margin-bottom: 8px;"><table style="margin-left: auto; margin-right: auto;"><tr><th style="height: 2em; vertical-align: middle;">  There are currently no calendar items of any kind.  </th></tr></table></div>';
}
?>
