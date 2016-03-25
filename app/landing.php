<?php
$page_title = "BYU Network Operations"; //displays the title of your site in the titlebar of the browser
$section = ""; // $section is used only to display the section in the titlebar of the browser
$subsection = ""; // $section is used only to display the section in the titlebar of the browser
$head_content = ""; // $head_content variable allows you to put css styles sheets and other content in the original head html tag
require('includes/CAS/CAS.php');
session_start();
$result="";

// Page to Load after authenticating
$pageToLoad = './whiteboard/index.php';
if ( isset($_GET['request']) )
{
   $pageToLoad = $_GET['request'];
}

if (isset($_SESSION['ldap']) || isset($_SESSION['cas']))
{
	if ($_SESSION['cas'])
	{
			// initialize phpCAS
		phpCAS::client(CAS_VERSION_2_0,getenv("CAS_URL"),443,'cas', false);
		// no SSL validation for the CAS server
		phpCAS::setNoCasServerValidation();
		$auth = phpCAS::checkAuthentication();
		if(!$auth)
		{
			session_destroy();
		}
		else
		{
		   if ( isset($_SESSION['request']) )
         {
            unset($_SESSION['request']);
         }
		   header("Location: ".$pageToLoad);
		}
	}
	echo '<META HTTP-EQUIV="Refresh" Content="0; URL='.$pageToLoad.'">';
}
else
{
	$auth = 0;
}
if (isset($_REQUEST['logout'])) {
	if ($_SESSION['ldap'])
	{
		session_destroy();
		echo '<META HTTP-EQUIV="Refresh" Content="0; URL=../landing.php?request='.$pageToLoad.'">';
		exit;
	}
	else
	{
		session_destroy();
		phpCAS::logout();
	}
}

if (isset($_REQUEST['login']))
{
	// initialize phpCAS
	phpCAS::client(CAS_VERSION_2_0,getenv("CAS_URL"),443,'cas', false);
	// no SSL validation for the CAS server
	phpCAS::setNoCasServerValidation();
	$auth = phpCAS::checkAuthentication();
	if(!$auth)
	{
		$_SESSION['cas'] = true;
		phpCAS::forceAuthentication();
	}	
	else
   {
      $netID = phpCAS::getUser();
      header("Location: ".$pageToLoad);
   }
}
if(isset($_POST['empNetID']) || isset($_POST['pwd']))
{
	$netID = $_POST['empNetID'];
	$pass = $_POST['pwd'];
	$ds=ldap_connect(getenv("LDAP_URL"),389);
		if ($ds)
		{
			$ldapAuth=0;
			ldap_set_option($ds, LDAP_OPT_SIZELIMIT, 2);
			//echo "Binding ...<br />";
			$r= @ldap_bind($ds, 'uid='.$netID.',ou=People,o=byu.edu', $pass);
			if ($r)
			{
				$_SESSION['user'] = $netID;
				$_SESSION['ldap'] = true;
				echo '<META HTTP-EQUIV="Refresh" Content="0; URL='.$pageToLoad.'">';
				//echo "<h2>authenticated.</h2>";
			}
			else
			{
				//echo '<script type="text/javascript">alert("The username or password you entered is incorrect. Please try again.");</script>';
				$result = '<script type="text/javascript"> 
						window.onload = function()
						{
							showForm();
							ldapWrongCredentialsMessage();
						}
					  </script>';
			}
		}
}

require('includes/dbconnect.php'); // Makes connections to the DB
require('includes/employeeFunc.php'); //Used for several employee functions such as employeeFill() for a dropdown menu
require('includes/functions.php'); //Used for general functions used accross the site such as areaSelect() for a dropdown menu with the active areas.  
require('includes/permissionFunctions.php'); //Used for Permission Functions.
require('includes/checkPermission.php'); //required for checking permissions
###################################Template/Style Includes###################################
//require('php_header.php'); // php_header is required to give your website variable and safe email address
//require('templates_lev2.php'); // templates displays the top section of the page
//require('templates_all_popup.php'); // templates_all_popup displays the menu system
//require('css.php'); //Contains CSS
require('includes/header.php');
require('includes/calendar.php'); //Used to print out the javascript calendar 

?>
<style type="text/css">
div.picture 
{
	  width:450px;
	  height:300px;
	  margin-left: auto;
	  margin-right: auto;
	  float:right;
	  border: solid 2px #CCC;
	  -moz-box-shadow: 1px 1px 5px #999;
	  -webkit-box-shadow: 1px 1px 5px #999;
      box-shadow: 1px 1px 5px #999;
	  	
}
div.title
{
	 margin-left: 18px;
	 text-align:center;
}

div.subheadline
{
/*	font-family: "Lucida Grande", Tahoma;
	font-size: 13px;
	font-weight: lighter;
	font-variant: normal; */
	/*text-transform: uppercase; */
	/*color: #666666;*/
    margin-top: 10px;
	margin-left:50px;
	/*text-align: center!important; */
	/*letter-spacing: 0.3em; */
	width:415px;
	
}
div.headline2
{
	text-shadow: 2px 4px 3px rgba(0,0,0,0.3);
	/*background: #002255; */
	font-family:  'Hoefler Text', Georgia, 'Times New Roman', serif;
	font-weight: normal;
    font-size: 1.75em;
	letter-spacing: .2em;
	line-height: 1.1em;
	margin:0px;
	text-align: center;
	text-transform: uppercase;
	width:465px;
}
div.ldapLogInForm
{
	float:right;
	display:none;
	width:225px;
	font-weight:bold;
	padding:15px;
	border: solid 2px #CCC;
	-moz-box-shadow: 1px 1px 5px #999;
	-webkit-box-shadow: 1px 1px 5px #999;
	box-shadow: 1px 1px 5px #999;
	
}
div.ldapInstructions
{
	float:right;
	margin-top:-20px;
	width:450px;
}
#ldapWrongCredentials
{
	display:none;
	font-weight:normal;
	color: red; 
	
}
</style>

<script type="text/javascript">
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

window.onload = function ()
{
   // I don't approve of this code (I wrote it), I don't like mixing the php in with the js this way and I don't like altering the href this way either.
   // Unfortunately, it is the easiest way to get the CAS auth page to reroute to the originally requested page. I was using a session variable but that
   // gets overwritten every time another request is made to some other page. In which case only the last page from all those requested is remembered.
   $('a[href*="login"]').prop('href', $('a[href*="login"]').prop('href')+'&request=<?php echo rawurlencode($pageToLoad); ?>');
}
</script>
<?php
	if($result!="")
	{
		echo $result;
	}
?>
<div class="title">
<H1>OIT Production Services</H1>
</div>

<br />
<br />
<div class="picture">
	<img src="itb.jpg" alt="Picture" height="300" width="450"> 
</div>
<div>
	<div class="headline2">
		Mission
	</div>
	<div class="subheadline">
		We enrich the BYU environment by making it inspiring, comfortable, productive and safe. <br /><br />
	</div>
	<div class="headline2">
		Vision
	</div>
	<div class="subheadline">
		To provide legendary service and value. <b style="color:red">Every university associate knows us and trusts us to take care of them</b>. <br /><br />
	</div>
	<div class="headline2">
		Values
	</div>
	<div class="subheadline">
		
		<ul>
		<li>Competency</li>
		<li>Respect for Sacred Resources</li>
		<li>Integrity</li>
		<li>Teamwork</li>
		<li>Exceeding Customer Expectations</li>
		<li>Respect for All Individuals</li>
		<li>Innovation</li>
		<li>Accountability</li>
		</ul>
		
	</div> 
</div>
<div class="ldapInstructions">
If CAS is inaccessible, log in through LDAP <a href="#" onClick="showForm();">HERE!</a>
</div><br />
<div id="logInForm" class="ldapLogInForm">
		<div id="ldapWrongCredentials"> The Net ID or password you entered is incorrect. Please try again.  </div>
	
		<br />
		<form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
			Net ID:<br /> <input type="text" name="empNetID" /><br />
			Password: <br /><input type="password" name="pwd" /><br /><br />
			<input type="submit" value="Submit" />
			
		</form>
</div>

<?php
require('includes/footer.php');
?>
