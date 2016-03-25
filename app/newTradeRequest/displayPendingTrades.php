<?php 

/*	Name: displayPendingTrades.php
*	Application: Trade Request
*
*	Description: This page prints out all of the pending trade requests for the supervisors to approve.
*	It uses the function populatePendingTradesPage() from tradesFunctions.php, and then on submital uses 
*	submitApprovedTrades.php to push information to the database.
*/

	//Standard include file for site header
	require('../includes/includeme.php');

	//Common php functions used in the Trade Request app
	require('tradesFunctions.php');


	//Authorization check
	if(!can("approve", "8d50e67c-53db-4a56-af2e-502d0d770bef"))//tradeRequest resource
	{
		echo "<h1>You do not have Authorization to view this page, if you feel this is in error contact your supervisor</h1>";
		require('../includes/includeAtEnd.php');
		return;
	}//if

	$startDate = date('Y-m-d',strtotime("-1 week"));//Used in testing to show trades pending within the past week as well
	$startDate = date('Y-m-d');//Sets the first date that trades are pulled for.

?>

<!--CSS-->
<link rel="stylesheet" type="text/css" href="tradeRequest.css" />

<style>
.tradeTable
{
	text-align:right;
	width:100%;
	table-style:fixed;		
	color:black;
}

th
{
	text-align:center;
	background-color:#3F5678;
	color:white;
	border:1px solid; 
	border-color:#3F5678;
}
</style>


<script>

	//When the window loads do the following
	window.onload = function()
	{
		//Makes all buttons into jQuery buttons
		$(':button').button();
	}//window.onload
	

	//Gets an array of the approved trades and sends them to approveTrades.php
	function submitTrades() 
	{
		//make sure the box is checked to avoid looping
		var isChecked = false;
		$('.approveCheckBox').each(function(){
			if($(this).prop('checked')){
				isChecked = true;
			}
		});
		if(!isChecked){
			window.alert("There are no trades approved");
			return;
		}
		//Get trades that will be sent to the page
		var trades = new Array();
		getSelected(document.getElementById("content"), trades);
	
		var page = "approveTrades.php?trades=" + trades;
		var cb = function (result) {
			
			//It sends a "1" but it receives a "1\n"
			if(result == "1\n" || result == "1")
			{
				alert ("Trade Approval Successful");
				window.location.reload();
			}//if
			else
			{ 
			   alert (result);
			}//else
			
		}//cb function
			
		callPhpPage(page, cb);
		
	}//submitTrades


	// This is a recursive function that searches for all checkboxes
	// beneath a given node (or HTML element) and returns an array
	// list of the selected checkboxes' values
	function getSelected(node, array) 
	{
		if(node.type == 'checkbox' && node.checked)
		{
			array.push(node.id);
		}//if
	
		var child = node.firstChild;
		while (child) 
		{
			getSelected(child, array);
			child = child.nextSibling;
		}//while
	
		return array;
		
	}//getSelected


	//This removes a person from the specified hour.
	function deleteBid(id)
	{
		var page = "deleteBid.php?id=" + id;
		
		var cb = function (result) 
		{
			if(result == "1")
			{
				alert ("Deletion Successful");
				window.location.reload();
			}//if
			else
			{ 
			   alert (result);
			}//else
		}//cb function

		callPhpPage(page, cb);

	}//deleteBid

</script>


<!--HTML-->
<h2 align='center' >Pending Trade Requests</h2>
<h3 align='center' ><a href='displayTrades.php'>Available Trades</a></h3>
<h3>Instructions:</h3>
<p>Check the boxes of the trades to be approved then click the Submit button. This will approve the trades, update the appropriate employee schedules, and send out the appropriate emails.</p>
<p>*A <font color="#47BB47">green</font> highlight signifies a trade where all hours have been taken.</p>

<?php populatePendingTradesPage($startDate); ?>


<?php
	require('../includes/includeAtEnd.php');
?>

