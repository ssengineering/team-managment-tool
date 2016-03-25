<?php

namespace TMT;
class TMT
{

	/** @var string The controller **/
	private $controller = "TMT\\"; 
	
	/** @var string The method or action **/
	private $action = null;
	
	/** @var array The pararmeters **/
	private $parameters = array(
		"url"     => null,
		"request" => null,
	);
	
	/**
	 * Constructs a new insance of the TMT application
	 */
	public function __construct()
	{
		$this->parseUrl();
	}

	/**
	 * Handles the request that has been given
	 *
	 * Calls the appropriate controllers and methods based on the request
	 */ 
	public function handle()
	{
		// check to see if controller exits
		if (class_exists($this->controller)) {

			// instantiate controller
			$this->controller = new $this->controller();
			
			// check to see if given action exists
			if (method_exists($this->controller, $this->action)) {
				
				// call the method and pass in the parameters
				try {
					$this->controller->{$this->action}($this->parameters);
				} catch(\Exception $e) {
					$error = "It looks like we've run into a problem. If this issue persists please contact the Service Desk at extension 2-4000";
					$this->controller->error($error, 500);
				}
			
			} else {
				
				// fallback for nonexistant method
				$this->controller->fallback();

			}
		} else {
			
			//Respond with fallback if the controller doesn't exist
			if(strpos($this->controller, 'TMT\\api') === 0) {
				$defaultCtrl = new \TMT\APIController();
				$defaultCtrl->fallback();
			} else {
				$fallback = new \TMT\app\fallback();
				$fallback->index($this->parameters);
			}

		}
	}

	/**
	 * Allows middleware to be registered to track the requests given
	 * 
	 * @TODO: implement this
	 */
	public function registerMiddleware()
	{
	}

	private function parseUrl() 
	{
		
		$url = ltrim($_GET["url"], "/");
		$url = filter_var($url, FILTER_SANITIZE_URL);
		$url = explode("/", $url);

		switch($url[0]){
			case "api":
				if (isset($url[2]) && class_exists($this->controller."api\\$url[1]\\$url[2]")) {
					$this->controller .= "api\\$url[1]\\$url[2]";
				} else {
					$this->controller .= "api\\$url[1]\\index";
				}
				$this->action = strtolower($_SERVER["REQUEST_METHOD"]);
				break;
				
			default:
				$this->controller .= "app\\$url[0]";
				$this->action = (isset($url[1]) ? $url[1] : "index");
				break;
		}

		$this->parameters["url"] = $url;
		// Parse data from AngularJS's $http service
		switch ($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			$this->parameters['request'] = $_GET;
			break;
		case 'POST':
			$this->parameters['request'] = $_POST;
			break;
		case 'PUT':
		case 'DELETE':
			$request = array();
			parse_str(file_get_contents("php://input"), $request);
			$this->parameters["request"] = $request;
			break;
		default:
			$this->parameters['request'] = $_REQUEST;
		}
	}

	/** Functions used for testing the router **/

	/** 
	 * Returns the controller if in a testing environment 
	 */
	public function getController() {
		if (getenv("ENVIRONMENT") == "TESTING")
			return $this->controller;
	}
	
	/** 
	 * Returns the action if in a testing environment 
	 */
	public function getAction() {
		if (getenv("ENVIRONMENT") == "TESTING")
			return $this->action;
	}
}
