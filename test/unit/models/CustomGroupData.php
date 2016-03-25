<?php

namespace TMT\model;

/**
 * Unit tests for the CustomGroupData model class
 *
 * The following lines are to help with the Accessor classes.
 * PHPUnit is trying to serialize the MysqlAccessor class,
 * and since it has a PDO object as a class member it can't
 * be properly serialized. These two lines allow it to run
 * just fine.
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class CustomGroupDataTest extends \PHPUnit_Framework_TestCase {

	//A custom group data model
	protected $data;

	/**
	 * @covers ::__construct
	 */
	public function testConstruct() {
		$this->data = new CustomGroupData(1, array("field1", "field2"));
		$this->assertEquals($this->data->getFields()[0], "field1");
		$this->assertEquals($this->data->getFields()[1], "field2");
		$this->assertEquals($this->data->getId(), 1);
	}

	/**
	 * @covers ::fieldExists
	 * @depends testConstruct
	 */
	public function testFieldExists() {
		$this->assertTrue($this->data->fieldExists("field1"));
		$this->assertFalse($this->data->fieldExists("field3"));
	}

	/**
	 * @covers ::getFields
	 * @depends testConstruct
	 */
	public function testGetFields() {
		$fields = $this->data->getFields();
		$this->assertEquals($fields[0], "field1");
		$this->assertEquals($fields[1], "field2");
		$this->assertEquals(count($this->data->getFields()), 2);
	}

	/**
	 * @covers ::getId
	 * @depends testConstruct
	 */
	public function testGetId() {
		$id = $this->data->getId();
		$this->assertEquals($id, 1);
	}

	/**
	 * @covers ::addField
	 * @depends testConstruct
	 * @depends testFieldExists
	 * @depends testGetFields
	 */
	public function testAddField() {
		$this->data->addField("field3");
		$this->assertTrue($this->data->fieldExists("field3"));
		$fields = $this->data->getFields();
		$this->assertEquals(count($this->data->getFields()), 3);
		$this->assertTrue($this->data->fieldExists("field2"));
		$caught = false;
		try {
			$this->data->addField("field2");
		} catch(\TMT\exception\CustomGroupDataException $e) {
			$caught = true;
		}
		$this->assertTrue($caught);
	}

	/**
	 * @covers ::deleteField
	 * @depends testConstruct
	 * @depends testAddField
	 */
	public function testDeleteField() {
		$this->data->deleteField("field2");
		$fields = $this->data->getFields();
		$this->assertEquals(count($fields), 2);
	}
}
?>
