<?php

namespace TMT\accessor;

/**
 * Unit tests for the CustomGroupData and UserCustomData accessor classes
 */
class CustomGroupDataTest extends \PHPUnit_Framework_TestCase {

	//Mongo client used for setUp and tearDown
	protected $client;

	//Group accessor
	protected $groupAcc;

	//User accessor
	protected $userAcc;

	public function setup() {
		if(getenv('MONGO_PASS') !== false && getenv('MONGO_USER') !== false) {
			$this->client = new \MongoClient("mongodb://".getenv('MONGO_USER').':'.getenv('MONGO_PASS').'@'.getenv('MONGO_HOST'));
		} else {
			$this->client = new \MongoClient("mongodb://".getenv('MONGO_HOST'));
		}
		//create test database and collection
		$this->client->selectDB("test");
		$coll = $this->client->test->createCollection("groups");
		if($coll->findOne()) {
			$coll->remove();
		}
		$coll2 = $this->client->test->createCollection("users");
		if($coll2->findOne()) {
			$coll2->remove();
		}
		$this->groupAcc = new CustomGroupData();
		$this->userAcc  = new UserGroupData();
		$this->groupAcc->setDatabase("test");
		$this->userAcc->setDatabase("test");
		$groupCol = $this->groupAcc->getCollection("groups");
		$userCol  = $this->userAcc->getCollection("users");
	}

	/**
	 * destroy database
	 */
	public function teardown() {
		$this->client->selectDB('test');
		$this->client->test->drop();
	}

	/**
	 * @covers ::create
	 * @covers ::get
	 */
	public function testCreateGroup() {
		$fields1 = array("field1", "field2", "field3");

		// Create groups and get them
		$id1 = $this->groupAcc->create($fields1);
		$id2 = $this->groupAcc->create();
		$group1 = $this->groupAcc->get($id1);
		$group2 = $this->groupAcc->get($id2);

		// Ensure ids are correctly assigned
		$this->assertEquals(1, $group1->getId());
		$this->assertEquals(2, $group2->getId());

		// Ensure fields were properly created
		$this->assertEquals(3, count($group1->getFields()));
		$this->assertEquals(0, count($group2->getFields()));
		$this->assertTrue($group1->fieldExists("field3"));
	}

	/**
	 * @covers ::create
	 * @covers ::get
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedExceptionMessage Group does not exist
	 */
	public function testGetNonexistent() {
		$this->groupAcc->get(4);//Should throw exception
	}

	/**
	 * @covers ::create
	 */
	public function testCreateGroupDefinedId() {
		// Create group with given id
		$this->groupAcc->create(array(), 32);
		$group1 = $this->groupAcc->get(32);
		$this->assertEquals(32, $group1->getId());

		// Ensure that the next group created without a specified
		//   id has id = <previous id>+1
		$id2 = $this->groupAcc->create();
		$this->assertEquals(33, $id2);
	}

	/**
	 * @covers ::create
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedExceptionMessage Group already exists
	 */
	public function testCreateGroupAlreadyExists() {
		$this->groupAcc->create();
		$this->groupAcc->create(array(), 1);//Should throw exception
	}

	/**
	 * @covers ::addField
	 */
	public function testAddField() {
		// Create group
		$id1 = $this->groupAcc->create(array());
		$group1 = $this->groupAcc->get($id1);
		$this->assertEquals(0, count($group1->getFields()));

		// Add fields
		$this->groupAcc->addField($id1, "newField");
		$this->groupAcc->addField($id1, "some field");

		// Retrieve group after updates have been made
		$group1 = $this->groupAcc->get($id1);

		$this->assertEquals(2, count($group1->getFields()));
		$this->assertTrue($group1->fieldExists("newField"));
		$this->assertTrue($group1->fieldExists("some field"));
	}

	/**
	 * @covers ::addField
	 * @depends testAddField
	 */
	public function testAddFieldWithUsers() {
		// Create group
		$fields1 = array("field1", "field2", "field3");
		$id1 = $this->groupAcc->create($fields1);
		$group1 = $this->groupAcc->get(1);

		// Create user
		$this->userAcc->create('user1', 1);
		$user1 = $this->userAcc->get('user1', 1);
		$this->assertEquals(3, count($user1->getData()));

		// Add field now that user is created and make sure it added properly
		$this->groupAcc->addField(1, "newField");
		$group1 = $this->groupAcc->get(1);
		$this->assertEquals(count($group1->getFields()), 4);
		$this->assertTrue($group1->fieldExists("newField"));
		$this->assertTrue($group1->fieldExists("field1"));
		$this->assertTrue($group1->fieldExists("field2"));
		$this->assertTrue($group1->fieldExists("field3"));

		// Retrieve user and ensure it has the new field
		$user1 = $this->userAcc->get('user1', 1);
		$this->assertEquals(4, count($user1->getData()));
		$this->assertEquals("", $user1->getField("newField"));
	}

	/**
	 * @covers ::removeField
	 */
	public function testRemoveField() {
		// Create group
		$fields1 = array("field1", "field2", "field3");
		$id1 = $this->groupAcc->create($fields1);
		$group1 = $this->groupAcc->get($id1);

		// Remove field
		$this->groupAcc->removeField($id1, "field2");

		// Ensure it was removed
		$group1 = $this->groupAcc->get($id1);
		$this->assertEquals(2, count($group1->getFields()));
		$this->assertTrue($group1->fieldExists("field1"));
		$this->assertTrue($group1->fieldExists("field3"));
		$this->assertFalse($group1->fieldExists("field2"));
	}

	/**
	 * @covers ::removeField
	 * @depends testRemoveField
	 */
	public function testRemoveFieldWithUsers() {
		// Create group and user
		$fields1 = array("field1", "field2", "field3");
		$id1 = $this->groupAcc->create($fields1);
		$this->userAcc->create('user1', $id1);

		// Remove field now that user exists
		$this->groupAcc->removeField($id1, "field2");

		// Make sure field was removed properly
		$group1 = $this->groupAcc->get($id1);
		$this->assertEquals(2, count($group1->getFields()));
		$this->assertTrue($group1->fieldExists("field1"));
		$this->assertFalse($group1->fieldExists("field2"));
		$this->assertTrue($group1->fieldExists("field3"));

		// Ensure user does not have field
		$user1 = $this->userAcc->get('user1', $id1);
		$this->assertEquals(2, count($user1->getData()));
		$this->assertEquals("", $user1->getField("field1"));
		$this->assertEquals("", $user1->getField("field3"));
	}

	/**
	 * @covers ::removeField
	 * @depends testRemoveFieldWithUsers
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedExceptionMessage Field does not exist
	 */
	public function testRemoveFieldWithUsersException() {
		// Create group and user
		$fields1 = array("field1", "field2", "field3");
		$id1 = $this->groupAcc->create($fields1);
		$this->userAcc->create('user1', $id1);

		// Remove field now that user exists
		$this->groupAcc->removeField($id1, "field2");

		// Ensure user does not have field
		$user1 = $this->userAcc->get("user1", $id1);
		$field2 = $user1->getField("field2");//should throw exception
	}

	/**
	 * @covers ::renameField
	 */
	public function testRenameField() {
		// Create group
		$fields1 = array("field1", "field2");
		$id1 = $this->groupAcc->create($fields1);

		// Rename field1 to newField
		$this->groupAcc->renameField($id1, "field1", "newField");
		$group1 = $this->groupAcc->get($id1);
		$this->assertEquals(count($group1->getFields()), 2);
		$this->assertFalse($group1->fieldExists("field1"));
		$this->assertTrue($group1->fieldExists("field2"));
		$this->assertTrue($group1->fieldExists("newField"));
	}

	/**
	 * @covers ::renameField
	 * @depends testRenameField
	 */
	public function testRenameFieldWithUsers() {
		// Create group and user
		$fields1 = array("field1", "field2", "field3");
		$id1 = $this->groupAcc->create($fields1);
		$group1 = $this->groupAcc->get($id1);
		$this->userAcc->create('user1', $id1);

		// Rename field2 to newField
		$this->groupAcc->renameField($id1, "field2", "newField");

		// Ensure the changes are made to the group
		$group1 = $this->groupAcc->get($id1);
		$this->assertEquals(3, count($group1->getFields()));
		$this->assertTrue($group1->fieldExists("field1"));
		$this->assertFalse($group1->fieldExists("field2"));
		$this->assertTrue($group1->fieldExists("field3"));
		$this->assertTrue($group1->fieldExists("newField"));

		// Ensure changes are made to the user
		$user1 = $this->userAcc->get('user1', $id1);
		$this->assertEquals(3, count($user1->getData()));
		$this->assertEquals("", $user1->getField("field1"));
		$this->assertEquals("", $user1->getField("field3"));
		$this->assertEquals("", $user1->getField("newField"));
	}

	/**
	 * @covers ::renameField
	 * @depends testRenameFieldWithUsers
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedExceptionMessage Field does not exist
	 */
	public function testRenameFieldWithUsersException() {
		// Create group and user
		$fields1 = array("field1", "field2", "field3");
		$id1 = $this->groupAcc->create($fields1);
		$group1 = $this->groupAcc->get($id1);
		$this->userAcc->create('user1', $id1);

		// Rename field2 to newField
		$this->groupAcc->renameField($id1, "field2", "newField");

		// Ensure user does not have access to field
		$user1 = $this->userAcc->get('user1', $id1);
		$field2 = $user1->getField("field2");//should throw exception
	}

	/**
	 * @covers ::remove
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedExceptionMessage Group does not exist
	 */
	public function testRemoveGroup() {
		$fields1 = array("field1", "field2", "field3");
		$id1 = $this->groupAcc->create($fields1);
		$group1 = $this->groupAcc->get($id1);
		$this->assertEquals($group1->getId(), $id1);
		$this->groupAcc->remove($id1);
		$this->groupAcc->get($id1);//should throw exception
	}

	/**
	 * @covers ::remove
	 * @depends testRemoveGroup
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedExceptionMessage Group does not exist
	 */
	public function testRemoveNonexistentGroup() {
		$id1 = $this->groupAcc->create(array());
		$this->groupAcc->remove($id1);
		$this->groupAcc->get($id1);//should throw exception
	}

	/**
	 * @covers ::remove
	 */
	public function testRemoveGroupWithUsers() {
		// Create group and user
		$fields1 = array("field1", "field2", "field3");
		$id1 = $this->groupAcc->create($fields1);
		$id2 = $this->groupAcc->create();
		$this->userAcc->create("user1", array(1,2));

		// Ensure user has access to both groups
		$user1 = $this->userAcc->get("user1", 1);
		$this->assertEquals(count($user1->getData()), 3);
		$this->assertEquals($user1->getNetId(), "user1");
		$this->assertEquals($user1->getGroup(), 1);
		$user2 = $this->userAcc->get("user1", 2);
		$this->assertEquals(count($user2->getData()), 0);
		$this->assertEquals($user2->getNetId(), "user1");
		$this->assertEquals($user2->getGroup(), 2);

		// Delete group 1
		$this->groupAcc->remove($id2);

		// Ensure user has access to group 2 still
		$user3 = $this->userAcc->get("user1", 1);
		$this->assertEquals(count($user3->getData()), 3);
		$this->assertEquals($user3->getNetId(), "user1");
		$this->assertEquals($user3->getGroup(), 1);
	}

	/**
	 * @covers ::remove
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedExceptionMessage No user with the given group and netId exists
	 */
	public function testRemoveGroupWithUserException() {
		// Create group and user
		$fields1 = array("field1", "field2", "field3");
		$id1 = $this->groupAcc->create($fields1);
		$id2 = $this->groupAcc->create();
		$this->userAcc->create("user1", array(1,2));

		// Ensure user has access to both groups
		$user1 = $this->userAcc->get("user1", 1);
		$this->assertEquals(count($user1->getData()), 3);
		$this->assertEquals($user1->getNetId(), "user1");
		$this->assertEquals($user1->getGroup(), 1);
		$user2 = $this->userAcc->get("user1", 2);
		$this->assertEquals(count($user2->getData()), 0);
		$this->assertEquals($user2->getNetId(), "user1");
		$this->assertEquals($user2->getGroup(), 2);

		// Delete group 1
		$this->groupAcc->remove($id2);

		// Ensure user doesn't have access to group 2
		$user4 = $this->userAcc->get("user1", 2);//Should throw exception
	}
}
?>
