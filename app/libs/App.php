<?php

namespace TMT;

/**
 * Base App class
 *
 * Used for controllers that are primarily
 *   responsible for rendering front ends
 */
class App extends \TMT\Controller {

	/**
	 * The file extension used for templates
	 */
	const VIEW_FILE_TYPE = ".twig";

	/**
	 * A constant defining the path to the folder where the views reside
	 */
	const VIEWS_PATH     = "views/";

	/**
	 * The user data which stores the current user's netId and currently viewed area. 
	 *
	 * @var $user associative array
	 */
	protected $user = array();
	
	/**
	 * @var boolean True if the request is authenticated
	 */
	protected $authenticated = false;

	/**
	 * Default Constructor
	 */
	public function __construct() {
		$this->initialize();
	}

	/**
	 * Checks authentication of current user, and retrieves the user's current area
	 *
	 * @return boolean True if the user is authorized
	 */
	protected function initialize() {
		// Needed for unit testing
		if (getenv('ENVIRONMENT') == 'TESTING'){
			$this->user = array("netId" => "TESTING_USER", "area" => 1);
			$this->authenticated = true;
			return;
		}

		// initialize client and set no certificate check
		\phpCAS::client(CAS_VERSION_2_0, getenv("CAS_URL"), 443, "cas");
		\phpCAS::setNoCasServerValidation();

		// check to see if user is authenticated
		if($this->isAuthenticated()) {
			// Pull user information
			$this->getUserInfo();
		} else {
			// User is not authenticated
			$this->user['netId'] = null;
			$this->user['area']  = null;
			$this->authenticated = false;
		}

		return;
	}

	/**
	 * Tests whether a user is logged in through LDAP or CAS
	 *   and updates $this->authenticated accordingly
	 *
	 * @return bool True if the user is authenticated through at least one service, false otherwise
	 */
	protected function isAuthenticated() {
		// Check if the user is authenticated through LDAP
		if(isset($_SESSION['ldap'])) {
			$this->authenticated = true;
			return true;
		}

		// Check if authenticated through CAS
		if(\phpCAS::checkAuthentication()) {
			$this->authenticated = true;
			return true;
		}

		// If the user is not authenticated through LDAP or CAS
		$this->authenticated = false;
		return false;
	}

	/**
	 * Populates this class' session array with the following variables
	 *   netId
	 *   area
	 *
	 * If the user is not authenticated, this function does nothing
	 */
	protected function getUserInfo() {
		// If the user is not authenticated, don't try to retrieve netId or area
		if(!$this->authenticated)
			return;

		// Pull information from CAS or LDAP, whichever way the user is authenticated
		if(isset($_SESSION['ldap'])) {
			$this->user['netId'] = $_SESSION['user'];
		} else if(\phpCAS::checkAuthentication()) {
			$this->user['netId'] = \phpCAS::getUser();
		} else {
			// This should never happen because they would somehow have authenticated set to true
			//   and not be logged in to CAS or LDAP
			$this->user['netId'] = null;
		}

		// In case a problem occurred and netId was not set, don't try to get area
		if($this->user['netId'] == null)
			return;

		// Pull area
		$areaAcc = new \TMT\accessor\AreaAccessor();
		$employeeAcc = new \TMT\accessor\Employee();
		$employee = $employeeAcc->get($this->user['netId']);

		if (isset($_COOKIE['area'])) {
			if($areaAcc->checkAreaRights($this->user['netId'], $_COOKIE['area'])) {
				$this->user['area'] = $_COOKIE['area'];
			} else {
				// The cookie was changed to an area the user does not have rights to
				// So unset the cookie and change to default area
				unset($_COOKIE['area']);
				setcookie("area", "", time()-3600, '/');
				$this->user['area'] = $employee->area;
			}
		} else {
			// Cookie not set, use default area
			$this->user['area'] = $employee->area;
		}

		$area = $areaAcc->get($this->user['area']);
		$this->user['guid'] = $employee->guid;
		$this->user['areaGuid'] = $area->guid;
	}

	/**
	 * Sets the user information
	 * NOTE: This should only need to be used for testing purposes
	 *
	 * @param $user User info array
	 */
	public function setUserInfo($user) {
		$this->user = $user;
	}

	/**
	 * Generates a string JSON Web Token signed with the RSA key
	 *
	 * @return string a signed JSON Web Token
	 */
	protected function createJWT() {
		// Get file name for private key
		$privateKey = getenv("PRIVATE_KEY_FILE");
		$privateKey = ($privateKey != "") ? $privateKey : $_SERVER['DOCUMENT_ROOT']."/keys/key.pem";

		// Create JWT
		$signer = new \Lcobucci\JWT\Signer\Rsa\Sha256();
		$keychain = new \Lcobucci\JWT\Signer\Keychain();
		$builder = new \Lcobucci\JWT\Builder();
		$token = $builder->setIssuer(getenv("PROD_URL"))
			->setIssuedAt(time()) // Issued now
			->setNotBefore(time()-1) // Not valid before now-1 second
			->setExpiration(time()+120) // Expire in 2 minutes
			->set("employee", $this->user['netId']) // store netId in the token
			->set("area", $this->user['areaGuid']) // store area id in the token
			->sign($signer, $keychain->getPrivateKey(file_get_contents($privateKey)))
			->getToken();
		return $token->__toString();
	}

	/**
	 * Send an authenticated request to one of the TMT micro-services
	 *
	 * @param $method string The HTTP method to use ("GET", "POST", "PUT", "DELETE")
	 * @param $url    string The url to make the request to
	 * @param $data   array  Any data to pass in POST data (GET data should be included in the $url)
	 *
	 * @return The response: an array created by json-decoding the response body
	 */
	protected function sendAuthenticatedRequest($method, $url, $data = array()) {
		// Start building options
		$curl_options = array();
		switch($method) {
		case "POST":
			$curl_options[CURLOPT_POST] = true;
			$curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
			break;
		case "PUT":
			$curl_options[CURLOPT_CUSTOMREQUEST] = "PUT";
			$curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
			break;
		case "DELETE":
			$curl_options[CURLOPT_CUSTOMREQUEST] = "DELETE";
			break;
		case "GET":
		default:
			$curl_options[CURLOPT_HTTPGET] = true;
		}
		$curl_options[CURLOPT_URL] = $url;
		$curl_options[CURLOPT_FOLLOWLOCATION] = true;
		$curl_options[CURLOPT_RETURNTRANSFER] = true;
		$curl_options[CURLOPT_SSL_VERIFYPEER] = false;
		$curl_options[CURLOPT_SSL_VERIFYHOST] = false;
		$curl_options[CURLINFO_HEADER_OUT] = true;
		$curl_options[CURLOPT_HTTPHEADER] = array("Authorization: ".$this->createJWT());

		// Set options and execute curl
		$curl_handle = curl_init();
		$options_set = curl_setopt_array($curl_handle, $curl_options);
		$response = curl_exec($curl_handle);
		$code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
		http_response_code($code);
		return json_decode($response, true);
	}

	/**
	 * Forces the unauthenticated users to authenticate through CAS
	 *
	 * This function will force an unauthenticated user to authenticate, if
	 * the request is authenticated the function will do nothing
	 *
	 * WARNING: This function should NEVER be called before initialize
	 * has been called, if so it will throw an error as phpCAS has not been 
	 * initialized. 
	 */
	protected function forceAuthentication() {
		// if the user has not signed in through CAS or LDAP
		if (!$this->authenticated && $this->user['netId'] == null) {
			\phpCAS::forceAuthentication();
		}

		// Ensure the user is a valid user
		$employeeAcc = new \TMT\accessor\Employee();
		$user = $employeeAcc->get($this->user['netId']);
		// Note: The employee accessor will return an empty employee model if no employee has
		//   the given netId. This check tests to see if there IS an employee in the database
		//   and if there is, to make sure he/she has an active status
		if($user->netID == null || $user->active != 1) {
			// User is logged in to CAS or LDAP, but should not have access to the TMT
			$this->render("helpers/notAuthorized");
			exit();
		}
	}

	/**
	 * Checks whether or not the permission is available for the user
	 *
	 * @param $perm string The short name of the permission
	 *
	 * @return bool True if the user has the permission, false otherwise
	 */
	protected function checkPermission($perm) {

		// If username or area is not set, return false
		if(!isset($this->user['netId']) || !isset($this->user['area']))
			return false;

		$permission = new \TMT\accessor\Permission();

		//Check if the area has access
		try {
			$areaPerm = $permission->getAreaPermission($perm, $this->user['area']);
		} catch(\TMT\exception\PermissionException $e) {
			return false;
		}

		//Check if the employee has access
		try {
			$userPerm = $permission->getUserPermission($areaPerm, $this->user['netId']);
		} catch(\TMT\exception\PermissionException $e) {
			return false;
		}

		return true;
	}

	/**
	 * Calls out to the new permission system to
	 *   ask if a user can perform the given action
	 *   on the specified resource
	 *
	 * @param $verb         string The action being performed
	 * @param $resourceGuid string The guid of the resource being accessed
	 * @param $netId        sting  (optional) The user to check for permission
	 *   Note: Defaults to the current user
	 *
	 * @return bool True if the user can perform the action, false otherwise
	 */
	protected function can($verb, $resourceGuid, $netId = null) {
		if($netId == null)
			$netId = $this->user['netId'];

		$domain = getEnv('PERMISSIONS_URL');

		$url = $domain."/permission".
			"?employeeGuid=".$netId.
			"&areaGuid=".$this->user['areaGuid'].
			"&verb=".$verb.
			"&resource=".$resourceGuid;

		$response = $this->sendAuthenticatedRequest("GET", $url);
		// Return response (true OR false) if request was successful, return false if an error occured
		if($response["status"] == "OK") {
			return \filter_var($response["data"], \FILTER_VALIDATE_BOOLEAN);
		} else {
			return false;
		}
	}

	/**
	 * Calls out to the new permission system to check
	 *   if a user is an admin
	 *
	 * @param $netId string (optional) The netId to check for admin rights
	 * @param $area  string (optional) The areaGuid to check if user is admin
	 *   Note: The default is the current user and current area.
	 *     You must specify either no parameters or both parameters for it to work properly.
	 *
	 * @return bool true if the user is an admin in the current area, false otherwise
	 */
	function isAdmin($netId = null, $area = null) {
		if($netId == null || $area == null) {
			$netId = $this->user['netId'];
			$area  = $this->user['areaGuid'];
		}

		$domain = getEnv('PERMISSIONS_URL');

		$url = $domain."/admin/".$netId."/".$area;

		$response = $this->sendAuthenticatedRequest("GET", $url);
		// Return response (true OR false) if request was successful, return false otherwise
		if($response["status"] == "OK") {
			return filter_var($response["data"], FILTER_VALIDATE_BOOLEAN);
		} else {
			return false;
		}
	}

	/**
	 * Calls out to the new permission system to check
	 *   if a user is superuser
	 *
	 * @param $netId string (optional) The netId to check for superuser
	 *   defaults to the current user.
	 *
	 * @return bool true if the user can be superuser, false otherwise
	 */
	function isSuperuser($netId = null) {
		if($netId == null)
			$netId = $this->user['netId'];	

		$domain = getEnv('PERMISSIONS_URL');

		$url = $domain."/superuser/is/".$netId;

		$response = $this->sendAuthenticatedRequest("GET", $url);
		// Return response (true OR false) if request was successful, return false otherwise
		if($response["status"] == "OK") {
			return filter_var($response["data"], FILTER_VALIDATE_BOOLEAN);
		} else {
			return false;
		}
	}

	/**
	 * Calls out to the new permission system to check
	 *   if a user can be a superuser
	 *
	 * @param $netId string (optional) The netId to check for superuser
	 *   defaults to the current user.
	 *
	 * @return bool true if the user can be superuser, false otherwise
	 */
	function canBeSuperuser($netId = null) {
		if($netId == null)
			$netId = $this->user['netId'];

		$domain = getEnv('PERMISSIONS_URL');

		$url = $domain."/superuser/can/".$netId;

		$response = $this->sendAuthenticatedRequest("GET", $url);
		// Return response (true OR false) if request was successful, return false otherwise
		if($response["status"] == "OK") {
			return filter_var($response["data"], FILTER_VALIDATE_BOOLEAN);
		} else {
			return false;
		}
	}

	/**
	 * Calls out to the permissions microservice and retrieves
	 *   all of a user's permissions.
	 *
	 * @return array(array("Verb" => "", "Resource" => ""))
	 */
	public function getAllUserPermissions() {
		// Hit the correct url
		$url = getEnv('PERMISSIONS_URL');

		// Retrieve permissions
		$result = $this->sendAuthenticatedRequest("GET", $url."/permission/user/".$this->user["netId"]."/".$this->user["areaGuid"]);

		// Verify that result was returned
		if($result == null || $result["status"] == "ERROR") {
			return null;
		}

		return $result["data"];
	}

	/**
	 * A wrapper function for checkPermission that will respond with an error and exit
	 *   if the user does not have the required permission
	 * NOTE: If more control is needed, the checkPermission function is still available.
	 *
	 * @param $verb     string The action
	 * @param $resource string The resource guid type to check
	 * @param $message  string The message to display if it needs to be different from the default
	 */
	protected function forcePermission($verb, $resourceGuid, $message = "You do not have the permission necessary to perform this action") {
		if (getenv('ENVIRONMENT') == 'TESTING')
			return;
		if(!$this->can($verb, $resourceGuid)) {
			$this->error($message, 403);
			exit();
		}
	}

	/**
	 * Render view
 	 *
	 * @param $view string The name of the view
	 * @param $data array  The data to use in rendering in the view
	 */
	public function render($view, $data = array()) {
		// Retrieve data necessary for properly rendering header and footer, and
		//   add that data to the template data
		$areaAcc     = new \TMT\accessor\AreaAccessor();
		$employeeAcc = new \TMT\accessor\Employee();
		$linkAcc     = new \TMT\accessor\Links();

		// Determine if user is admin or superuser
		$admin = $this->isAdmin();
		$su    = $this->isSuperuser();

		// Get user and area information
		$user      = $employeeAcc->get($this->user['netId']);
		$areaArray = $areaAcc->getAll($this->user['netId']);
		$areas = array();
		if(isset($this->user['area'])) {
			foreach($areaArray as $area) {
				$areas[] = array('id' => $area->ID, 'name' => $area->longName);
			}

			// Retrieve link tree
			$links = $linkAcc->getTree($this->user['area']);
			$this->cleanLinkTree($links, $admin, $su);
		}

		// Check environment
		$environment = $this->getEnvironment();

		// Get quicklinks
		$quicklinks = $this->getAccessor("Quicklinks")->getByUser($this->user['netId']);

		$notificationsUrl = getenv("NOTIFICATIONS_URL");

		// Add data necessary for the main header and footer to load properly
		$data['templateData'] = array (
			"area"             => isset($this->user['area']) ? $this->user['area'] : null,
			"areaName"         => isset($this->user['area']) ? $areaAcc->get($this->user['area'])->longName : null,
			"areaGuid"         => isset($this->user['areaGuid']) ? $this->user['areaGuid'] : null,
			"areas"            => $areas,
			"authenticated"    => $this->authenticated,
			"canSU"            => $this->canBeSuperuser(),
			"environment"      => $environment,
			"firstName"        => $user->firstName,
			"isSU"             => $su,
			"jwt"              => $this->createJWT(),
			"lastName"         => $user->lastName,
			"links"            => isset($links) ? $links : null,
			"netId"            => $this->user['netId'],
			"notificationsUrl" => $notificationsUrl,
			"quicklinks"       => $quicklinks,
			"server"           => $_SERVER['SERVER_NAME']
		);

		// load twig
		$twigLoader = new \Twig_Loader_Filesystem(self::VIEWS_PATH);
		$twig = new \Twig_Environment($twigLoader);

		// to avoid conflicts with angularjs use of {{ }}
		$lexer = new \Twig_Lexer($twig, array(
			'tag_comment'   => array('[#', '#]'),
			'tag_block'     => array('[%', '%]'),
			'tag_variable'  => array('[[', ']]'),
			'interpolation' => array('#[', ']')
		));

		$twig->setLexer($lexer);

		// render a view
		echo $twig->render($view . self::VIEW_FILE_TYPE, $data);
	}

	/**
	 * Responds with an error instead of requested content
	 *
	 * @param $message string The message to display
	 * @param $status  int    The status code to respond with (defaults to 403)
	 * @param $json    bool   True to respond with json, false to respond with a plain message (defaults to false/plain html)
	 */
	protected function renderError($message = "", $status = 403, $json = false) {
		http_response_code($status);
		if($json) {
			$error = array(
				"status"  => "ERROR",
				"message" => $message
			);
			echo json_encode($error);
		} else {
			$this->render("helpers/error", array("message" => $message));
		}
	}

	/**
	 * This function abstracts the call to renderError
	 *
	 * @param $message string The message to display (defaults to "Page not found")
	 */
	public function error($message = "", $status = 403) {
		$this->renderError($message, $status);
	}

	/**
	 * Responds with 404 status code (not found) and an error page
	 *
	 * @param $message string The message to display (defaults to the generic message below)
	 */
	public function fallback($message = "If you believe this is an error, please contact the Service Desk at extension 2-4000") {
		if(!$this->authenticated) {
			header('Location: /landing');
			exit();
		}

		// If the user is authenticated display not found page
		http_response_code(404);
		$this->render("helpers/notFound", array("message" => $message));
	}

	/**
	 * This function sends out a notification of the given type
	 *   with the specified message.
	 *
	 * @param $type    string A notification type guid
	 * @param $message string The message to send
	 * @param $person  (object)array The person who the message is specifically directed to, if any
	 * 					i.e. the employee who is given the performance log
	 */
	public function notify($type, $message, $persons = NULL) {
		// Find permission needed to receive notification
		$typeAcc = $this->getAccessor("NotificationType");
		$notType = $typeAcc->get($type);

		// Get notifications url
		$url = getEnv('NOTIFICATIONS_URL');

		// Get recipients
		$prefAcc = $this->getAccessor("NotificationPreferences");
		$recipientArray = $prefAcc->getRecipients($type, $this->user['areaGuid']);

		// Make sure each recipient can recieve the notification
		// If not, remove them from the list and delete that preference
		$receivers = array();

		if($persons !== null){
			$receivers = $persons;

		// If no permission is required, send to all
		} else if($notType->resource == null) {
			foreach($recipientArray as $recipient) {
				$receivers[] = (object)array(
					"netId"  => $recipient->netId,
					"method" => $recipient->method,
					"email"  => $recipient->email
				);
			}
		}

		// If user must be an admin to receive this notification
		 else if($notType->resource == "admin") {
			foreach($recipientArray as $recipient) {
				// Add to send list only if the user is an admin or can be superuser
				if($this->isAdmin($recipient->netId, $this->user['areaGuid']) || $this->canBeSuperuser($recipient->netId)) {
					$receivers[] = (object)array(
						"netId"  => $recipient->netId,
						"method" => $recipient->method,
						"email"  => $recipient->email
					);
				} else {
					// User is not authorized to receive permission, remove entry from table
					$prefAcc->delete($recipient->netId, $type, $this->user['areaGuid']);
				}
			}

		// Normal permission check
		} else {
			foreach($recipientArray as $recipient) {
				// Add to send list only if the user is an admin or can be superuser
				if($this->can($notType->verb, $notType->resource, $recipient->netId) || $this->canBeSuperuser($recipient->netId)) {
					$receivers[] = (object)array(
						"netId"  => $recipient->netId,
						"method" => $recipient->method,
						"email"  => $recipient->email
					);
				} else {
					// User is not authorized to receive permission, remove entry from table
					$prefAcc->delete($recipient->netId, $type, $this->user['areaGuid']);
				}
			}
		}

		// Add notification
		$notAcc = $this->getAccessor("Notification");
		$notification = $notAcc->create(new \TMT\model\Notification((object)array(
			"type" => $type,
			"area" => $this->user['areaGuid'],
			"message" => $message
		)));
		if(count($receivers) > 0) {
			// Send notification
			$this->sendAuthenticatedRequest("POST", "https://".$url."/notify", array("message" => $message, "receivers" => json_encode($receivers)));

			// Create notifications for each user
			$userNotAcc = $this->getAccessor("UserNotification");
			foreach($receivers as $receiver) {
				if($receiver->method == "onsite" || $receiver->method == "all")
					$userNotAcc->add($receiver->netId, $notification->guid);
			}
		}
	}

	/**
	 * Converts netIds into "person" objects so that they can be used as the third parameter in the notify function
	 * @param $netIds string/array the netID or array of netIDs that will be converted into "person" object(s)
	 * @param $area string the GUID of the area of the notification in question
	 * @param $type string the GUID of the type of notification in question.
	 *
	 * 	If only one netID is passed in, it is converted into an array of size 1 in the function.
	 */
	public function getReceivers($netIDs, $area, $type) {
		$newReceivers = array();
		$prefAcc = $this->getAccessor("NotificationPreferences");
		$empAcc = $this->getAccessor("Employee");
		if(!is_array($netIDs)){
			$netIDs = ["$netIDs"];
		}
		foreach($netIDs as $netID) {
			$preferences = $prefAcc->getOneUserPreference($netID, $area, $type);
			if($preferences->method != null){
				$newReceivers[] = (object)array(
					"netId" => $netID,
					"method"=> $preferences->method,
					"email" => $empAcc->get($netID)->email
				);
			}
		}
		return $newReceivers;
	}

	/**
	 * Sends out an onsite notification regardless of user preferences
	 * @param $type string A notification type GUID
	 * @param $message string The message to send
	 * @param $persons (object)array The netId, method, and email address of a specific person(s) to receive the message 
	 * 			usually the person to whom the message is referring (i.e. performance logs)
	 */
	public function forceNotify($type, $message, $persons = null) {
		$typeAcc = $this->getAccessor("NotificationType");
		$notType = $typeAcc->get($type);

		// Get notifications url
		$url = getEnv('NOTIFICATIONS_URL');
		

		// Make sure each recipient can recieve the notification
		// If not, remove them from the list and delete that preference
		$receivers = array();

		if($persons !== null){
			foreach($persons as $person){
				$receivers[] = (object)array(
					"netId"  => $person->netId,
					"method" => "onsite", 
					"email"  => $person->email
				);
			}

		} else {
			// Get recipients
			$empAcc = $this->getAccessor("Employee");
			$recipientArray = $empAcc->getByArea($this->user['area'], true, 1);

			foreach($recipientArray as $recipient) {
				$receivers[] = (object)array(
					"netId"  => $recipient->netId,
					"method" => "onsite", 
					"email"  => $recipient->email
				);
			}
		}	
				
		// Add notification
		$notAcc = $this->getAccessor("Notification");
		$notification = $notAcc->create(new \TMT\model\Notification((object)array(
			"type" => $type,
			"area" => $this->user['areaGuid'],
			"message" => $message
		)));
		if(count($receivers) > 0) {
			// Send notification
			$this->sendAuthenticatedRequest("POST", "https://".$url."/notify", array("message" => $message, "receivers" => json_encode($receivers)));

			// Create notifications for each user
			$userNotAcc = $this->getAccessor("UserNotification");
			foreach($receivers as $receiver) {
				$userNotAcc->add($receiver->netId, $notification->guid);
			}
		}
	}

	/**
	 * Parses a link tree and removes all links that
	 *   the user does not have permission to see
	 *
	 * Invoked by $this->render
	 *
	 * @param $links a link tree in the format of array(\TMT\model\Link)
	 * @param $admin boolean, true if the user is an admin
	 * @param $su    boolean, true if the user is elevated to superuser
	 *
	 * NOTE: The link tree is passed by reference,
	 *   so it is edited in place, not passed by value
	 */
	private function cleanLinkTree(&$links, $admin, $su) {
		$perms = $this->getAllUserPermissions();

		// Remove links from the tree that the user does not have permission to see
		foreach($links as $pIndex => $parent) {
			if(count($parent->children) > 0) {

				// Loop through child links
				foreach($parent->children as $cIndex => $child) {
					if(count($child->children) > 0) {

						// Loop through grandchildren
						foreach($child->children as $gIndex => $grandchild) {
							// check grandchild permission

							// No permission needed, ignore
							if($grandchild->resource == null)
								continue;

							// permission is required, check for permission
							if(!$this->checkLinkPermission($perms, $grandchild->verb, $grandchild->resource, $admin, $su))
								unset($child->children[$gIndex]);

						}

						// If all grandchildren have been removed, see if child should be removed also
						if(count($child->children) < 1) {
							// If the link does not have a url
							if($child->url == null) {
								unset($parent[$cIndex]);
							} else if($child->resource != null) {
								// If there is a url and they do not have permission, remove the link
								if(!$this->checkLinkPermission($perms, $child->verb, $child->resource, $admin, $su))
									unset($parent->children[$cIndex]);
							}
						}

					} else {
						// child has no children, check permission

						// No permission needed, ignore
						if($child->resource == null)
							continue;

						// Permission required, check for valid permission
						if(!$this->checkLinkPermission($perms, $child->verb, $child->resource, $admin, $su)) {
							unset($parent->children[$cIndex]);
						}
					}

				}

				// If all children have been removed, see if parent should be removed
				if(count($parent->children) < 1) {
					if($parent->url == null) {
						// If there is not a url, remove the link
						unset($links[$pIndex]);
					} else if($parent->resource != null) {
						// If there is a url, and they do not have permission, remove the link
						if(!$this->checkLinkPermission($perms, $parent->verb, $parent->resource, $admin, $su))
							unset($links[$pIndex]);
					}
				}

			} else {
				// Parent has no children, check permission

				// No permission needed, ignore
				if($parent->resource == null)
					continue;

				// Permission required, check for valid permission
				if(!$this->checkLinkPermission($perms, $parent->verb, $parent->resource, $admin, $su))
					unset($links[$pIndex]);
			}
		}
	}

	/**
	 * Looks in the permissions $perms (passed by reference and should be retrieved
	 *   by $this->getAllUserPermissions()) to see if the user has permission to
	 *   perform the action $verb on $resource
	 *
	 * Used by $this->cleanLinkTree()
	 *
	 * @param $perms    A list of permissions returned by $this->getAllUserPermissions
	 * @param $verb     An action to be performed on $resource
	 * @param $resource A resource Guid
	 * @param $admin    Boolean, true if the user is an admin in the current area
	 * @param $su       Boolean, true if the user is currently elevated to superuser
	 *
	 * @return boolean  True if the link should be shown, false otherwise
	 */
	private function checkLinkPermission(&$perms, $verb, $resource, $admin, $su) {
		// Check if admin permission is required and return true if the user is admin or su
		if($resource == "admin") {
			if($admin || $su)
				return true;
			else
				return false;
		}

		// return true if the link doesn't require admin rights but user is admin or su
		if($admin || $su)
			return true;

		foreach($perms as $permission) {
			if($permission["Resource"] == $resource && $permission["Verb"] == $verb) {
				return true;
			}
		}
		return false;
	}
}
