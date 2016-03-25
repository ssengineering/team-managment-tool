<?php

namespace TMT\accessor;

/**
 * Unit tests for the CustomGroupData and UserCustomData accessor classes
 */
class UserGroupDataTest extends \PHPUnit_Framework_TestCase {

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
		$coll->findOne();
		$coll2 = $this->client->test->createCollection("users");
		$coll2->findOne();

		$this->groupAcc = new CustomGroupData();
		$this->userAcc  = new UserGroupData();
		$this->groupAcc->setDatabase("test");
		$this->userAcc->setDatabase("test");
		$groupCol = $this->groupAcc->getCollection("groups");
		$userCol  = $this->userAcc->getCollection("users");
		$this->groupAcc->create();
		$this->groupAcc->create();
		$this->groupAcc->addField(1, "field1");
		$this->groupAcc->addField(2, "field2");
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
	public function testCreateUser() {
		// Create user with access to two groups
		$this->userAcc->create("user1", array(1, 2));

		// Ensure proper data for group 1
		$user1 = $this->userAcc->get("user1", 1);
		$this->assertEquals("user1", $user1->getNetId());
		$this->assertEquals(1, $user1->getGroup());
		$this->assertEquals("", $user1->getField("field1"));
		$this->assertEquals(1, count($user1->getData()));

		// Ensure proper data for group 2
		$user2 = $this->userAcc->get("user1", 2);
		$this->assertEquals("user1", $user2->getNetId());
		$this->assertEquals(2, $user2->getGroup());
		$this->assertEquals("", $user2->getField("field2"));
		$this->assertEquals(1, count($user2->getData()));
	}

	/**
	 * @covers ::create
	 * @covers ::get
	 * @depends testCreateUser
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedExceptionMessage Field does not exist
	 */
	public function testFieldNonexistent() {
		// Create user with access to two groups
		$this->userAcc->create("user1", array(1, 2));

		// Ensure exception is thrown if field doesn't exist
		$user = $this->userAcc->get("user1", 2);
		$user->getField("field1");//Should throw exception
	}

	/**
	 * @covers ::create
	 * @covers ::get
	 */
	public function testCreateUserSingleGroup() {
		$this->userAcc->create("user1", 1);
		$user1 = $this->userAcc->get("user1", 1);
		$this->assertEquals("user1", $user1->getNetId());
		$this->assertEquals(1, $user1->getGroup());
		$this->assertEquals("", $user1->getField("field1"));
		$this->assertEquals(1, count($user1->getData()));
	}

	/**
	 * @covers ::create
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedExceptionMessage User already exists
	 */
	public function testCreateUserDouble() {
		$this->userAcc->create("user1", array(1));
		$this->userAcc->create("user1", array(1));//Should throw exception
	}

	/**
	 * @covers ::create
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedExceptionMessage Cannot create user without a group
	 */
	public function testCreateUserNoGroup() {
		$this->userAcc->create("user1", array());//Should throw exception
	}

	/**
	 * @covers ::create
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedExceptionMessage Group does not exist
	 */
	public function testCreateUserGroupNonexistent() {
		$this->userAcc->create("user1", array(3));//Should throw exception
	}

	/**
	 * @covers ::addGroup
	 */
	public function testAddGroup() {
		// Create user and add a group
		$this->userAcc->create("user1", array(1));
		$this->userAcc->addGroup("user1", 2);

		// Ensure user now has access to the group
		$user1 = $this->userAcc->get("user1", 2);
		$this->assertEquals("user1", $user1->getNetId());
		$this->assertEquals(2, $user1->getGroup());
		$this->assertEquals("", $user1->getField("field2"));
		$this->assertEquals(1, count($user1->getData()));
	}

	/**
	 * @covers ::addGroup
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedExceptionMessage Group does not exist
	 */
	public function testAddGroupNonexistent() {
		$this->userAcc->create("user1", array(1));
		$this->userAcc->addGroup("user1", 3);//Should throw exception
	}

	/**
	 * @covers ::addGroup
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedExceptionMessage User does not exist
	 */
	public function testAddGroupUserNonexistent() {
		$this->userAcc->addGroup("user1", 1);//Should throw exception
	}

	/**
	 * @covers ::removeGroup
	 * @depends testCreateUser
	 */
	public function testRemoveGroup() {
		// Create user and remove a group
		$this->userAcc->create("user1", array(1, 2));
		$this->userAcc->removeGroup("user1", 2);

		// Ensure user can still access group 1
		$user = $this->userAcc->get("user1", 1);
		$this->assertEquals($user->getNetId(), "user1");
		$this->assertEquals($user->getGroup(), 1);
		$this->assertEquals(count($user->getData()), 1);
	}

	/**
	 * @covers ::removeGroup
	 * @depends testRemoveGroup
	 */
	public function testRemoveGroupNonexistent() {
		// Create user and remove a group he never had
		$this->userAcc->create("user1", array(1));
		$this->userAcc->removeGroup("user1", 3);

		// Ensure user still has access to his groups
		$user = $this->userAcc->get("user1", 1);
		$this->assertEquals("user1", $user->getNetId());
		$this->assertEquals(1, $user->getGroup());
		$this->assertEquals(1, count($user->getData()));
	}

	/**
	 * @covers ::removeGroupNonexistent
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedException No user exists with the given netId and group
	 */
	public function testRemoveGroupUserOrGroupNonexistent() {
		// Create user and remove a group
		$this->userAcc->create("user1", array(1, 2));
		$this->userAcc->removeGroup("user1", 2);

		// Make sure exception is thrown when trying to get user with removed group
		$this->userAcc->get("user1", 2); // Should throw exception
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateUser() {
		// Create User
		$this->userAcc->create("user1", array(1, 2));

		// Update user data
		$user1 = $this->userAcc->get("user1", 1);
		$user1->editField("field1", 1);
		$this->userAcc->update($user1);

		// Make sure update was performed properly
		$user1 = $this->userAcc->get("user1", 1);
		$this->assertEquals(1, $user1->getField("field1"));
		$this->assertEquals(1, count($user1->getData()));
		// Make sure update to group1 data did not affect data for another group
		$user1 = $this->userAcc->get("user1", 2);
		$this->assertEquals("", $user1->getField("field2"));
		$this->assertEquals(1, count($user1->getData()));
	}

	/**
	 * @covers ::remove
	 * @expectedException \TMT\exception\CustomGroupDataException
	 * @expectedException No user exists with the given netId and group
	 */
	public function testRemoveUser() {
		$this->userAcc->create("user1", array(1));
		$user1 = $this->userAcc->get("user1", 1);
		$this->userAcc->remove("user1");
		$this->userAcc->get("user1", 1);//Should throw exception
	}
}
?>
