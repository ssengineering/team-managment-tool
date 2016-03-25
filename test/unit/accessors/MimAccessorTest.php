<?php

namespace TMT\accessor;

/**
 * Unit tests for the base Mim data accessor class
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class MimAccessorTest extends \PHPUnit_Framework_TestCase {

	protected $mimAcc;

	/**
	 * @before
	 */
	public function setUpAccessor()
	{
		$this->mimAcc = new MimAccessor();
	}

	/**
	 * @covers MimAccessor::get
	 * @covers MimAccessor::getAll
	 */
	public function testGet() 
	{
		// Valid Mim
		$mim = $this->mimAcc->get("netId");
		$this->assertTrue($mim->netID == "netId");
		$this->assertTrue($mim->firstName == "First");
		$this->assertTrue($mim->lastName == "Last");
		// Mim exists, but employee is inactive
		$mim = $this->mimAcc->get("employee2");
		$this->assertFalse(isset($mim->netID));
		$this->assertFalse(isset($mim->firstName));
		$this->assertFalse(isset($mim->lastName));
		// Employee exists but is not a Mim
		$mim = $this->mimAcc->get("other");
		$this->assertFalse(isset($mim->netID));
		$this->assertFalse(isset($mim->firstName));
		$this->assertFalse(isset($mim->lastName));

		// Test getAll
		$netId = $this->mimAcc->get("netId");
		$mims = $this->mimAcc->getAll();
		$this->assertTrue(json_encode($mims[0]) == json_encode($netId));
	}

	/**
	 * 	@depends testGet
	 *	@covers MimAccessor::insert
	 *	@covers MimAccessor::delete
	 */
	public function testInsertDelete()
	{
		// Delete existing
		$before = $this->mimAcc->get("netId");
		$after = $this->mimAcc->delete($before);
		$this->assertFalse(json_encode($before) == json_encode($after));
		$this->assertFalse(isset($after->netID));
		$this->assertFalse(isset($after->firstName));
		$this->assertFalse(isset($after->lastName));

		// Re-insert existing
		$before = new \TMT\model\Mim();
		$before->netID = "netId";
		$after = $this->mimAcc->insert($before);
		$this->assertTrue($after->netID == "netId");
		$this->assertTrue($after->firstName == "First");
		$this->assertTrue($after->lastName == "Last");
		
		// Delete non-existing
		$before = $this->mimAcc->get("other");
		$after = $this->mimAcc->delete($before);
		$this->assertFalse(isset($after->netID));
		$this->assertFalse(isset($after->firstName));
		$this->assertFalse(isset($after->lastName));

		// Add new MIM
		$other = new \TMT\model\Mim();
		$other->netID = "other";
		$after = $this->mimAcc->insert($other);
		$after = $this->mimAcc->get("other");
		$this->assertTrue($after->netID == "other");
		$this->assertTrue($after->firstName == "Other");
		$this->assertTrue($after->lastName == "Person");
		
		// Delete new insertion
		$before = $this->mimAcc->get("other");
		$after = $this->mimAcc->delete($before);
		$this->assertFalse(json_encode($before) == json_encode($after));
		$this->assertFalse(isset($after->netID));
		$this->assertFalse(isset($after->firstName));
		$this->assertFalse(isset($after->lastName));
	}

	/**
	 * Should fail foreign key constraint
	 *
	 * @covers MimAccessor::insert
	 * @expectedException \PDOException
	 */
	public function testNonEmployeeInsert()
	{
		$bad = new \TMT\model\Mim();
		$bad->netID = "badNetId";
		$this->mimAcc->insert($bad);

	}

	/**
	 * @covers MimAccessor::getPossible 
	 */
	public function testGetPossible()
	{
		// No areas
		$possible = $this->mimAcc->getPossible(array());
		$this->assertEquals(count($possible), 0);
		// Single match
		$possible = $this->mimAcc->getPossible(array(1));
		$this->assertEquals(count($possible), 1);
		// Ignore inactive employees
		$possible = $this->mimAcc->getPossible(array(2));
		$this->assertEquals(count($possible), 1);
	}

}
?>
