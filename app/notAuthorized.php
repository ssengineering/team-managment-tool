<?php
	include_once('includes/CAS/CAS.php');

	// initialize phpCAS
	phpCAS::client(CAS_VERSION_2_0,getenv("CAS_URL"),443,'cas');

	// no SSL validation for the CAS server
	phpCAS::setNoCasServerValidation();

	$auth = phpCAS::checkAuthentication();

	if (isset($_REQUEST['logout']))
		phpCAS::logout();
	
	if(!$auth)
		phpCAS::forceAuthentication();
	else
		$netID = phpCAS::getUser();

?>

<html>
	<head><title>Not Authorized</title></html>
	<body>
		<h1>You are not authorized to view this page!</h1>
		<?php if($auth): ?>
		<p>The NetID <b><u><?php echo $netID ?></u></b> is not authorized to view this page. If you believe this is in error, please contact your supervisor.</p>
		<p>This attempt has been logged.</p>
		<a href="?logout=">Logout</a>
		</p>
		<?php endif; ?>
	</body>
</html>
