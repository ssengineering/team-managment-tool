<?php
/**
* @file crudApps.php
* @brief This is the controller for the generic CRUD app. New CRUD apps
* should extend this controller and overwrite the __construct function 
* with the appropriate values for the app. Also, if the extended app adds
* Angular directives that need to load template, those template URLs will
* likely require additional functions in the child controller.
*
* @version 1.1
* @date 2015-07-09
*/
namespace TMT\app;

class crudApps extends \TMT\App {

	// string directory name where javascript files are stored
	// i.e. /static/js/$app_name/myfile.js
	protected $app_name; 

	// string permission verb
	protected $verb;

	// string Resource guid to check for permission
	protected $resourceGuid;

	/**
	 * This constructor is the only function that needs to be overwritten for 
	 * new crudApps. The constructor sets so config values needed for the template
	 * to render properly.
	 */
	public function __construct() 
	{
		parent::__construct();
		$this->app_name     = "crudApps";
		$this->verb         = "";
		$this->resourceGuid = "";
	}

	/**
	 * Renders the CRUD app
	 */
	public function index($params) 
	{
		$this->forceAuthentication();
		if ($this->app_name === "crudApps"){
			// Only allow developers to see the empty crudApp
			if(!$this->isSuperuser()) {
				$this->error("You are not authorized to view this page", 403);
				exit();
			}
		}
		$this->forcePermission($this->verb, $this->resourceGuid);
		$this->render("$this->app_name/index", array(
			"app_name" => $this->app_name,
		)); 		
	}

	/**
	 * Render the crud-table directive
	 */
	public function table($params)
	{
		$this->forceAuthentication();
		$this->render("crudApps/crudTable");
	}

	/**
	 * Render the add-dialog directive
	 */
	public function addDialog($params)
	{
		$this->forceAuthentication();
		$this->render("crudApps/addDialog");
	}
}
?>
