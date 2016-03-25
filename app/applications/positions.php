<?php
/**
* @file positions.php
* @brief CRUD app for Employee positions. This extends the generic CRUD app 
* controller.
*
* @version 1.1
* @date 2015-07-09
 */
namespace TMT\app;

class positions extends \TMT\app\crudApps {

	/**
	 * This constructor is the only function that needs to be overwritten for 
	 */
	public function __construct() 
	{
		parent::__construct();
		$this->app_name = "positions";
		$this->permission = "editEmployeeInfo";	
		$this->verb = "update";
		$this->resourceGuid = "1450ff35-82a7-45ed-adcf-ffa254ebafa2";
	}

	public function deleteDialog($params)
	{
		$this->forceAuthentication();
		$this->render("$this->app_name/deleteDialog");
	}

}
?>
