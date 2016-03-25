<?php require("../includes/includeme.php"); 

	if (!can("update", "1689443f-8c4c-4874-8ee3-a3137db32d85"))/*schedule resource*/ {
		echo "<h1>You are not authorized to view this page.</h1>";
		require("../includes/includeAtEnd.php");
		return;
	}

?>
<style>
.postTable{
	text-align:right;
	width:50%;
	table-style:fixed;
	border:1px solid black;
	color:black;
}
th.postTable
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
td{
	border:1px solid black;
	padding:2;
}
li {
	margin: 0px;
	position: relative;
	//padding: 4px 0;
	cursor: pointer;
	float: left;
	list-style: none;
}
</style>
<script>
var year = <?php echo date('Y', strtotime('today')); ?>;

window.onload = getPosts;

function submitPosts() {
	var posts = new Array();
	getSelected(document.getElementById("content"), posts);
	
	var page = "postingAjax/setPostings.php?year="+year+"&posts="+posts;
	var cb = function (result) {
		alert(result);
	}
	
	callPhpPage(page, cb);
}

function getPosts() {
	year = document.getElementById("year").value;
	var page = "postingAjax/getPostings.php?year="+year;
	var cb = function (result) {
		document.getElementById("postings").innerHTML = result;
	}
	
	callPhpPage(page, cb);
}


// This is a recursive function that searches for all checkboxes
// beneath a given node (or HTML element) and returns an array
// list of the selected checkboxes' values
function getSelected(node, array) {
	if(node.type == 'checkbox' && node.checked) {
		array.push(node.id);
	}
	
	var child = node.firstChild;
	while (child) {
	    getSelected(child, array);
	    child = child.nextSibling;
	}
	
	return array;
}
</script>

<h1>Post Schedules</h1>

<label>Year: <input id='year' type="text" size="4" value="<?php echo date('Y', strtotime('today')); ?>"></label>

<input type="button" value="Get Posts" onClick="getPosts()">

<br /><br />

<div id="postings"></div>
<?php

require("../includes/includeAtEnd.php"); ?>
