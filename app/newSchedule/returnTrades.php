<?php
require("../includes/includeMeBlank.php");
//This function returns all the shifts for an employee in a given period

$weekStart = $_GET['startDate'];
$employee = $_GET['employee'];

$endDate = date("Y-m-d",strtotime($weekStart."+6 days"));
$date = $weekStart;
$tradeArray = array();

while($date <= $endDate)
{
	try {
		$tradesQuery = $db->prepare("SELECT * FROM `scheduleTrades` WHERE postedBy = :employee AND startDate = :day AND `deleted`=0");
		$tradesQuery->execute(array(':employee' => $employee, ':day' => $date));
	} catch(PDOException $e) {
		exit("error in query");
	}
    while($trade = $tradesQuery->fetch(PDO::FETCH_ASSOC))
    {
        $tradeArray[] = $trade;
    }
    
    $date = date("Y-m-d",strtotime($date."+1 day"));
}

echo json_encode($tradeArray);
?>
