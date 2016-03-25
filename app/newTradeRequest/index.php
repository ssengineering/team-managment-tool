<?php 

/*	Name: displayTrades.php
*	Application: Trade Request
*
*	Description: This is an application that allows employees to trade certain shifts.
*	This page shows trades that have been submitted on the newSchedule/index.php page
*	as well as requests for more hours.
*/

	//Standard include file for site header
	require('../includes/includeme.php');

	//Common php functions used in the Trade Request app
	require('tradesFunctions.php');
?>

<!--CSS-->
<link rel="stylesheet" type="text/css" href="tradeRequest.css" />

<style>
	li 
	{
		margin: 0px;
		position: relative;
		//padding: 4px 0;
		cursor: pointer;
		float: left;
		list-style: none;
	}

	.tradeTable
	{
		text-align:right;
		width:100%;
		table-style:fixed;
		border:1px solid black;f
		color:black;
	}

	th.tradeTable
	{
		text-align:center;
		background-color:#3F5678;
		color:white;
		border:1px solid; 
		border-color:#3F5678;
	}

	th
	{
		text-align:center;
		border:1px solid black; 
	}

	td
	{
		border:1px solid black;
		padding:2;
	}
</style>

<script>
	// Note: The most important thing to know about this page is the structure
	// of the ID's of the checkboxes generated for this page. Their names have
	// the following syntax:
	//
	//    {netID}_{tradeID}_{hour}


	//When the window loads do the following
	window.onload = function()
	{
		//Makes all buttons into jQuery buttons
		$(':button').button();
	}//window.onload


	//Sends an array of trades to the submitTrades.php
	function submitTrades() 
	{
		//Array of trades to be sent
		var trades = new Array();
		
		//Set array of selected checkbox values
		getSelected(document.getElementById("content"), trades);
		
		//URL and data to send
		var page = "submitTrades.php?trades=" + trades;
		
		var cb = function (result) 
		{
		    if(result == "1"){
				alert ("Trades Submitted Successfully");
			}//if
			else
			{ 
			    alert (result);
		    }//else
		    
		    //Reload the window
			window.location.reload();
			
		}//function
	
		callPhpPage(page, cb);
		
	}//submitTrades


	// This is a recursive function that searches for all checkboxes
	// beneath a given node (or HTML element) and returns an array
	// list of the selected checkboxes' values
	function getSelected(node, array) 
	{
		//If the checkbox is checked add it to the array
		if(node.type == 'checkbox' && node.checked) 
		{
			array.push(node.id);
		}//if
	
		var child = node.firstChild;
		
		while (child) 
		{
			getSelected(child, array);//TODO - Is this the best way to do this, having the function call itself in a while loop?
			child = child.nextSibling;
		}//while
	
		return array;
	}//getSelected
	

	//Sends the id of the bid to be saved to the DB
	function submitBid(id) 
	{
		//URL and data to send
		var page = "submitBid.php?id=" + id;
		
		var cb = function (result) 
		{
			if(result == "1")
			{
				$('<div class="operationStatus success">Bid successfully submitted.</div>').appendTo('body')
			   .animate({'opacity': '0'}, {'duration': 5000,'complete': function()
               {
                       $(this).remove();
               }});
               
			}//if
			else
			{ 
			   $('<div class="operationStatus failure">'+result+'</div>').appendTo('body')
			   .animate({'opacity': '0'}, {'duration': 5000,'complete': function()
               {
                       $(this).remove();
               }});
               
			}//else
			
		}//function

		callPhpPage(page, cb);

	}//submitBid


	//Sends the id of the bid to be deleted
	function deleteBid(id) 
	{
		//URL and data to send
		var page = "deleteBid.php?id=" + id;
		
		var cb = function (result) 
		{
			if(result == "1")
			{
				$('<div class="operationStatus success">Bid successfully removed.</div>').appendTo('body')
			   .animate({'opacity': '0'}, {'duration': 5000,'complete': function()
               {
                       $(this).remove();
               }});
               
			}//if
			else
			{ 
			   $('<div class="operationStatus failure">'+result+'</div>').appendTo('body')
			   .animate({'opacity': '0'}, {'duration': 5000,'complete': function()
               {
                       $(this).remove();
               }});
               
			}//else
			
		}//function

		callPhpPage(page, cb);

	}//deleteBid
	

	//Sends the id of the trade to to be deleted
	function deleteTrade(id)
	{
		//Check to make sure they want to delete the trade
		var r = confirm("Are you sure you want to delete this trade?");
		
		if(r == true)//If they do want to delete the trade
		{
			//URL and data to send
			var page = "deleteTrade.php?id=" + id;
			
			var cb = function (result) 
			{
			
				if(result == "1")
				{
					alert ("Deletion Successful");
				}//if
				else
				{ 
					alert (result);
				}//else
				
				//Reload the window
				window.location.reload();
				 
			}//function
	
			callPhpPage(page, cb);
		}//if

	}//deleteTrade
</script>

<div id='availableTrades' align='center'>
	<h2>Available Trade Requests</h2>
	<h3>Click a date to see the hours available for trade on that day.
		<?php 
			if(can("approve", "8d50e67c-53db-4a56-af2e-502d0d770bef"))//tradeRequest resource
			{
				echo "<a href='displayPendingTrades.php'>Click Here To Approve Pending Trades</a>";
			}//if 
		?>
	</h3>
	<br/>
	
	<?php populateTradesPage();?>
	<div style="clear:both"></div>
</div>
<br/>

<div style="clear:both"></div>

<div id='hourRequests' align='center'>
	<h2>Want More Hours</h2>
	<?php populateHourRequests(); ?>
	<input type="button" value="New Request" onclick="window.location.href='newHourRequest.php'" style="float:right;"/>
	<div style="clear:both"></div>
</div>

<?php require('../includes/includeAtEnd.php'); ?>
