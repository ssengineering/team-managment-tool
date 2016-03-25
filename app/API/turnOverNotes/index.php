<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/includeMeBlank.php');

// Get requested end-point (i.e. "$call")
$URI = $_SERVER['REQUEST_URI'];
$APIDirectory = '/API/turnOverNotes/';
$call = substr($URI, strlen($APIDirectory));

// Determine the type of interaction being attempted
$method = $_SERVER['REQUEST_METHOD'];

//Create variables with default values of NULL
$status = $error =  $data = NULL;

// For now just handling a get for read-only events
switch ($method)
{
case "GET":
	// Explode $call to get our nice pretty variables
	list($submittedBy,$ownedBy,$startDate,$endDate,$cleared,$noteText,$closingComment) = explode('/', $call);
	if ($cleared === NULL) $cleared = '';
	// The weird cleared stuff in the next line is all because MySQL likes to convert NULL into 0, so there isn't a way for me to distinguish between NULL and 0 when I make my query.
	if($startDate) == ""{ $startDate = "0000-00-00";}
	if($endDate) == ""{$endDate = "0000-00-00";}
	try {
		$notesQuery = $db->prepare("CALL getTurnOverNotes(:submittedBy,:ownedBy,:startDate,:endDate,:clear,:note,:closing)");
		$success = $notesQuery->execute(array(':submittedBy' => $submittedBy, ':ownedBy' => $ownedBy,
											  ':startDate'   => $startDate,   ':endDate' => $endDate,
											  ':clear'       => $cleared===''? '2': $cleared,
											  ':note'        => $noteText,
											  ':closing'     => $closingComment));
	} catch(PDOException $e) {
		$success = false;
	}
	if ($success)
	{
		// If we have results 
		$status = 'OK';
		$data = array();
		while ($note = $notesQuery->fetch(PDO::FETCH_ASSOC))
		{
			$data[] = $note;
		}
	}
	else
	{
		$status = 'FAIL';
		$error = 'An error occured while accessing the database';
	}
	break;
default:
    $status = 'FAIL';
	$error = "$method method is un-handled by the API, feel free to implement it!";
	break;
}

// Prepare our JSON response
$response = array('status'=>$status, 'error'=>$error, 'data'=>$data);

// Return our response
echo json_encode($response);
?>
