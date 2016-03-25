<?php

/**
 * Create a signed Json Web Token for microservice authentication.
 *
 * @return string A signed JSON Web Token
 */
function createJWT() {
    global $db, $areaGuid, $netID;

    // Get file name for private key
    $privateKey = getenv("PRIVATE_KEY_FILE");
    $privateKey = ($privateKey != "") ? $privateKey : $_SERVER['DOCUMENT_ROOT'] . "/keys/key.pem";

    // Create JWT
    $signer = new \Lcobucci\JWT\Signer\Rsa\Sha256();
    $keychain = new \Lcobucci\JWT\Signer\Keychain();
    $builder = new \Lcobucci\JWT\Builder();
    $token = $builder->setIssuer(getenv("PROD_URL"))
        ->setIssuedAt(time())
        ->setNotBefore(time() - 1)
        ->setExpiration(time() + 120) // 2 minutes
        ->set("employee", $netID) // store netId in the token
        ->set("area", $areaGuid) // store area guid in the token
        ->sign($signer, $keychain->getPrivateKey(file_get_contents($privateKey)))
        ->getToken();
    return $token->__toString();
}

/**
 * This function will convert the netIds it receives into a "person" object
 * 	with method and email attributes. This object can then be used as the 
 * 	third parameter in the notify function.
 * 	
 * 	@param $netIDs string/array The netID or array of netIDs for the user(s) in question
 * 			if there is only one netID passed in, the function will convert it to an array of
 * 			size 1.
 * 	@param $area string The area of the type of notification in question
 * 	@param $type The type of notifiction in question.
 * 	@return $newReceivers array The receivers that will be used as the third parameter in
 * 				the notify function.
 */
function getReceivers($netIDs, $area, $type) {
	global $db;
	$newReceivers = array();
	if(!is_array($netIDs)){
		$netIDs = array($netIDs);
	}
	foreach($netIDs as $netID) {
		try{
			$methodQuery = $db->prepare("SELECT method FROM notificationPreferences WHERE netId = :netId AND area = :area AND type = :type");
			$methodQuery->execute(array(':netId' => $netID, ':area' => $area, ':type' => $type));
		} catch(PDOException $e) {
			exit("error in query");
		}
		if($method = $methodQuery->fetch()){
			try{
				$emailQuery = $db->prepare("SELECT email FROM employee WHERE netID = :netId");
				$emailQuery->execute(array(':netId' => $netID));
			} catch(PDOException $e) {
				exit("error in query");
			}
			$email = $emailQuery->fetch()->email;

			$newReceivers[] = (object)array(
				"netId" => $netID,
				"method" => $method->method,
				"email" => $email
			);
		}
	}

	return $newReceivers;
}

/**
 * This function sends out a notification of the given type
 *   with the specified message.
 *
 * @param $type    string A notification type guid
 * @param $message string The message to send
 * @param $persons (object)array The netId, method, and email address of a specific person(s) to receive the message
 * 			usually the person to whom the message is referring (i.e. performance logs)
 */
function notify($type, $message, $persons = null) {
	global $areaGuid, $db;

    // Find permission needed to receive notification
    try {
        $stmt = $db->prepare("SELECT * FROM notificationTypes WHERE guid=:guid");
        $stmt->execute(array(':guid' => $type));
    } catch (PDOException $e) {
        exit("error in query");
    }
    $notType = $stmt->fetch();

    // Get notifications url
    $url = getEnv('NOTIFICATIONS_URL');

    // Get recipients
    try {
        $stmt = $db->prepare("SELECT notificationPreferences.*, employee.email FROM notificationPreferences JOIN employee
			ON notificationPreferences.netId=employee.netID WHERE type=:type AND notificationPreferences.area=:area");
        $stmt->execute(array(':type' => $type, ':area' => $areaGuid));
    } catch (PDOException $e) {
        exit("error in query");
    }

    // Make sure each recipient can recieve the notification
    // If not, remove them from the list and delete that preference
    $receivers = array();

	if($persons !== null){
		$receivers = $persons;	
	// If no permission is required, send to all
	} else if($notType->resource == null) {
		while($recipient = $stmt->fetch()) {
			$receivers[] = (object)array(
				"netId"  => $recipient->netId,
				"method" => $recipient->method,
				"email"  => $recipient->email
			);
		}

	// If user must be an admin to receive this notification
	} else if($notType->resource == "admin") {
		while($recipient = $stmt->fetch()) {
			// Add to send list only if the user is an admin or can be superuser
			if(isAdmin($recipient->netId, $areaGuid) || canBeSuperuser($recipient->netId)) {
				$receivers[] = (object)array(
					"netId"  => $recipient->netId,
					"method" => $recipient->method,
					"email"  => $recipient->email
				);
			} else {
				// User is not authorized to receive permission, remove entry from table
				try {
					$stmt2 = $db->prepare("DELETE FROM notificationPreferences WHERE netId=:netId AND type=:type AND area=:area");
					$stmt2->execute(array(':netId' => $recipient->netId, ':type' => $type, ':area' => $areaGuid));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
		}
		
	// Normal permission check
	} else {
		while($recipient = $stmt->fetch()) {
			// Add to send list only if the user is an admin or can be superuser
			if(can($notType->verb, $notType->resource, $recipient->netId) || canBeSuperuser($recipient->netId)) {
				$receivers[] = (object)array(
					"netId"  => $recipient->netId,
					"method" => $recipient->method,
					"email"  => $recipient->email
				);
			} else {
				// User is not authorized to receive permission, remove entry from table
				try {
					$stmt2 = $db->prepare("DELETE FROM notificationPreferences WHERE netId=:netId AND type=:type AND area=:area");
					$stmt2->execute(array(':netId' => $recipient->netId, ':type' => $type, ':area' => $areaGuid));
				} catch(PDOException $e) {
					exit("error in query");
				}
			}
		}
		
	}

	$guid = newGuid();
	try {
		$stmt3 = $db->prepare("INSERT INTO notifications (message, type, area, guid) VALUES (:message, :type, :area, :guid)");
		$stmt3->execute(array(":message" => $message, ":type" => $type, ":area" => $areaGuid, ":guid" => $guid));
	} catch(PDOException $e) {
		exit("error in query");
	}
	if(count($receivers) > 0) {
		sendAuthenticatedRequest("POST", "https://".$url."/notify", array("message" => $message, "receivers" => json_encode($receivers)));
		foreach($receivers as $receiver) {
			if($receiver->method == "onsite" || $receiver->method == "all") {
				try {
					$stmt4 = $db->prepare("INSERT INTO userNotifications (netId, notificationGuid) VALUES (:netId, :guid)");
					$stmt4->execute(array(":netId" => $receiver->netId, ":guid" => $guid));
				} catch(PDOException $e) {} // catch exceptions if they arise, but try to add as many as possible
			}
		}
	}
}

/**
 * Forces an onsite notification to all people in the area regardless of preferences, or to one person, if the fourth
 * parameter is filled.
 * @param $type string The notification type GUID
 * @param $message string The message to send
 * @param $persons (object)array The NetId, method, and email addres of a specific person(s) to receive the message, 
 * 			usually the person to whom the message is referring (i.e. performance logs)
 */
function forceNotify($type, $message, $persons = null) {
	global $area, $areaGuid, $db;

	// Get notifications url
	$url = getEnv('NOTIFICATIONS_URL');

	$receivers = array();
	if($persons !== NULL){
		foreach($persons as $person){
			$receivers[] = (object)array(
				"netId" => $person->netId,
				"method" => "onsite",
				"email" => $person->email
			);
		}
	} else {
		// Get recipients
		try {
			$stmt = $db->prepare("SELECT netID, email FROM employee WHERE area=:area AND active=1");
			$stmt->execute(array(':area' => $area));
		} catch(PDOException $e) {
			exit("error in query");
		}

		while($recipient = $stmt->fetch()) {
			$receivers[] = (object)array(
				"netId"  => $recipient->netID,
				"method" => "onsite",
				"email"  => $recipient->email
			);
		}
	}

	$guid = newGuid();
	try {
		$stmt3 = $db->prepare("INSERT INTO notifications (message, type, area, guid) VALUES (:message, :type, :area, :guid)");
		$stmt3->execute(array(":message" => $message, ":type" => $type, ":area" => $areaGuid, ":guid" => $guid));
	} catch(PDOException $e) {
		exit("error in query");
	}
	if(count($receivers) > 0) {
		sendAuthenticatedRequest("POST", "https://".$url."/notify", array("message" => $message, "receivers" => json_encode($receivers)));
		foreach($receivers as $receiver) {
			try {
				$stmt4 = $db->prepare("INSERT INTO userNotifications (netId, notificationGuid) VALUES (:netId, :guid)");
				$stmt4->execute(array(":netId" => $receiver->netId, ":guid" => $guid));
			} catch(PDOException $e) {} // catch exceptions if they arise, but try to add as many as possible
		}
	}
}
?>
