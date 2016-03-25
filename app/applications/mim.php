<?php
/**
* @file mimList.php
* @brief CRUD app for Major Incident Managers. This extends the generic CRUD app 
* controller.
*
* @version 1.1
* @date 2015-07-09
 */
namespace TMT\app;

class mim extends \TMT\app\crudApps {

	/**
	 * This constructor is the only function that needs to be overwritten for 
	 */
	public function __construct() 
	{
		parent::__construct();
		$this->app_name = "mim";
		$this->verb = "update";
		$this->resourceGuid = "8ebcf9bb-3f88-4632-945c-9ef58e1c8cd6";
	}

}
?>
