<?php
/**
* @file mimList.php
* @brief CRUD app for Custom Data Fields. This extends the generic CRUD app 
* controller.
*
* @version 1.1
* @date 2015-07-13
 */
namespace TMT\app;

class customData extends \TMT\app\crudApps {

	/**
	 * This constructor is the only function that needs to be overwritten for 
	 */
	public function __construct() 
	{
		parent::__construct();
		$this->app_name = "customData";
		$this->verb = "update";
		$this->resourceGuid = "db9b1290-c9dd-4bad-b3f5-0374dd5ec2a7";
	}

	/**
	 * Renders the CRUD app
	 */
	public function index($params) 
	{
		$this->forceAuthentication();
		$this->forcePermission($this->verb, $this->resourceGuid);

		$this->render("$this->app_name/index", array(
			"app_name" => $this->app_name,
			"area" => $this->user['area']
		)); 		
	}
}
?>
