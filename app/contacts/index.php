<?php
include("../includes/includeme.php");
?>

<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">

	// Ajax function to get the list of employees.
	function contactsSearch(str)
		{
		var xmlhttp;
		var activeVal = 1;

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
				document.getElementById("contactsSearchResults").innerHTML=xmlhttp.responseText;
				}
			}
		xmlhttp.open("GET","../contacts/contactsQuery.php?q="+str,true);
		xmlhttp.send();
		}

	function submitContactsEditForm(edit)
		{
		if (edit == 0)
			{
			parent.location = 'contactsEdit.php';
			}
		else if (edit == 1)
			{
			parent.location = 'contactsEditHierarchy.php';
			}
		}

	contactsSearch('');

</SCRIPT>

<div id="contactsSearchInput" style="margin-left: auto; margin-right: auto; text-align: center;">
	<h1> Contacts Search </h1><input type="text" onkeyup="contactsSearch(this.value)" size="26" /><br /><br />
</div>

<?php if(can("update", "f49362ef-983b-4615-ac64-727b769a713f"))//contacts Resource 
{ ?>
<div id="contactsEditForm" style="margin-left: auto; margin-right: auto; text-align: center;">		
	<form id="editContactsForm" method="POST">
		<input type="button" onclick="submitContactsEditForm(0)" value="Edit Contacts" />
		<input type="button" onclick="submitContactsEditForm(1)" value="Edit Hierarchy" />
	</form>
</div>
<?php } ?>

<div id="contactsSearchResults" style="margin-left: 3%; margin-right: 3%; width: 94%;"></div>

<?php
include("../includes/includeAtEnd.php");
?>
