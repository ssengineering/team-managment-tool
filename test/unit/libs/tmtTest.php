<?php

/**
 * The following lines are to help with the Accessor classes.
 * PHPUnit is trying to serialize the MysqlAccessor class,
 * and since it has a PDO object as a class member it can't
 * be properly serialized. These two lines allow it to run
 * just fine.
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class TMTTest extends PHPUnit_Framework_TestCase
{
	
	public function testIndex() {
		$_GET['url'] = "/";
		$_SERVER["REQUEST_METHOD"] = "GET";
		$tmt = new \TMT\TMT();
		$expectedController = "TMT\app\\";
		$expectedAction = "index";
		$this->assertEquals($expectedController, $tmt->getController());
		$this->assertEquals($expectedAction, $tmt->getAction());
	}

	public function testAppIndex() {
		$_GET['url'] = "/whiteboard";
		$_SERVER["REQUEST_METHOD"] = "GET";
		$tmt = new \TMT\TMT();
		$expectedController = "TMT\app\\whiteboard";
		$expectedAction = "index";
		$this->assertEquals($expectedController, $tmt->getController());
		$this->assertEquals($expectedAction, $tmt->getAction());
	}
	
	public function testAppAction() {
		$_GET['url'] = "/whiteboard/new";
		$_SERVER["REQUEST_METHOD"] = "GET";
		$tmt = new \TMT\TMT();
		$expectedController = "TMT\app\\whiteboard";
		$expectedAction = "new";
		$this->assertEquals($expectedController, $tmt->getController());
		$this->assertEquals($expectedAction, $tmt->getAction());
	}
	
	public function testAPIindex() {
		$_GET['url'] = "/api/employee";
		$_SERVER["REQUEST_METHOD"] = "GET";
		$tmt = new \TMT\TMT();
		$expectedController = "TMT\api\\employee\\index";
		$expectedAction = "get";
		$this->assertEquals($expectedController, $tmt->getController());
		$this->assertEquals($expectedAction, $tmt->getAction());
	}
	
	public function testAPIindexData() {
		$_GET['url'] = "/api/employee/netId1";
		$_SERVER["REQUEST_METHOD"] = "GET";
		$tmt = new \TMT\TMT();
		$expectedController = "TMT\api\\employee\\index";
		$expectedAction = "get";
		$this->assertEquals($expectedController, $tmt->getController());
		$this->assertEquals($expectedAction, $tmt->getAction());
	}
	
	/** @TODO: Need to test for third level api with an existing controller
	 * IE: /API/employee/search
	 * This will need to be tested once such an api exists, as the router will not
	 * load a non existant controller
	 */
}
