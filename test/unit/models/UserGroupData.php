<?php

namespace TMT\model;

/**
 * Unit tests for the UserGroupData model class
 *
 * The following lines are to help with the Accessor classes.
 * PHPUnit is trying to serialize the MysqlAccessor class,
 * and since it has a PDO object as a class member it can't
 * be properly serialized. These two lines allow it to run
 * just fine.
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class UserGroupDataTest extends \PHPUnit_Framework_TestCase {

	//User model object
	protected $user;

	/**
	 * @covers ::__construct
	 * @covers ::getData
	 * @covers ::getGroup
	 * @covers ::getNetId
	 */
	public function testConstruct() {
		$this->user = new UserGroupData("test", 1, array(
			"field1" => "something",
			"field2" => "stuff"
		));
		$data = $this->user->getData();
		$this->assertEquals($data['field1'], "something");
		$this->assertEquals($data['field2'], "stuff");
		$this->assertEquals($this->user->getGroup(), 1);
		$this->assertEquals($this->user->getNetId(), "test");
	}

	/**
	 * @covers ::getField
	 * @depends testConstruct
	 */
	public function testGetField() {
		$this->assertEquals($this->user->getField("field1"), "something");
		$this->assertEquals($this->user->getField("field2"), "stuff");
		$caught = false;
		try {
			$this->user->getField("nonexistent");
		} catch(\TMT\exception\CustomGroupDataException $e) {
			$caught = true;
		}
		$this->assertTrue($caught);
	}

	/**
	 * @covers ::editField
	 * @depends testConstruct
	 */
	public function testEditField() {
		$this->user->editField("field1", "changed");
		$this->assertEquals($this->user->getField("field1"), "changed");
		$this->assertEquals($this->user->getField("field2"), "stuff");
		$caught = false;
		try {
			$this->user->editField("newfield", "new stuff");
		} catch(\TMT\exception\CustomGroupDataException $e) {
			$caught = true;
		}
		$this->assertTrue($caught);
	}
}
?>
