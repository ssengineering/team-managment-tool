var toggleFlag = 1;
function showForm()
{
	if(toggleFlag)
	{
		document.getElementById("logInForm").style.display = "block";
		toggleFlag = 0;
	}
	else
	{
		document.getElementById("logInForm").style.display = "none";
		toggleFlag = 1;
	}
}

function ldapWrongCredentialsMessage()
{
	document.getElementById("ldapWrongCredentials").style.display = "block";
}
