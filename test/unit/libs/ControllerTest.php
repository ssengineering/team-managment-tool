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
class ControllerTest extends PHPUnit_Framework_TestCase {

	/**
	 * The controller to use for testing
	 */
	private $controller;

	public function setUp() {
		$this->controller = new \TMT\Controller();
	}

	/**
	 * @covers ::getModel
	 */
	public function testGetModel() {
		$model = $this->controller->getModel("Position");
		$this->assertEquals("TMT\\model\\Position", get_class($model));
	}

	/**
	 * @covers ::getModel
	 * @depends testGetModel
	 */
	public function testGetModelWithParams() {
		$position = (object) array(
			"positionId" => 1,
			"positionName" => "pos1",
			"positionDescription" => "Position 1",
			"employeeArea" => 1,
			"deleted" => 0,
			"guid" => null
		);
		$model = $this->controller->getModel("Position", $position);
		$this->assertEquals("TMT\\model\\Position", get_class($model));
		$this->assertEquals(new \TMT\model\Position($position), $model);
	}

	/**
	 * @covers ::getController
	 */
	public function testGetControllerWithParams() {
		$email = (object) array(
			"recipients" => "test@byu.edu",
			"subject"    => "test",
			"message"    => "This is the message"
		);
		$emailController = $this->controller->getController("EmailHandler", new \TMT\model\Email($email));
		$this->assertEquals("TMT\\controller\\EmailHandler", get_class($emailController));
	}

	/**
	 * @covers ::getAccessor
	 */
	public function testGetAccessor() {
		$employeeAccessor = $this->controller->getAccessor("Employee");
		$this->assertEquals("TMT\\accessor\\Employee", get_class($employeeAccessor));
	}
}
