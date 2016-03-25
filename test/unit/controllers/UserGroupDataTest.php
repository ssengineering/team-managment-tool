<?php

namespace TMT\api\userGroupData;

/**
 * Unit tests for the customData controller class
 */
class UserGroupDataTest extends \PHPUnit_Framework_TestCase {

	protected $client;

	protected $ctrl;

	public function testPrepare() {
		if(getenv('MONGO_PASS') !== false && getenv('MONGO_USER') !== false) {
			$this->client = new \MongoClient("mongodb://".getenv('MONGO_USER').':'.getenv('MONGO_PASS').'@'.getenv('MONGO_HOST'));
		} else {
			$this->client = new \MongoClient("mongodb://".getenv('MONGO_HOST'));
		}

		$host = getenv('DB_HOST');
		$user = getenv('DB_USER');
		$pass = getenv('DB_PASS');
		$db   = getenv('DB_NAME');
		$connectStr = "mysql:dbname=".$db.";host=".$host.";port=3306";
		$options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ);
		$pdo = new \PDO($connectStr, $user, $pass, $options);
		$check = $pdo->prepare("SELECT netID FROM employee WHERE netID='TESTING_USER'");
		$check->execute();
		if(!($check->fetch())) {
			$stmt = $pdo->prepare("INSERT INTO employee (netID,active,area,firstName,lastName,maidenName,phone,email,birthday,languages,hometown,major,
									 missionOrStudyAbroad,graduationDate,position,shift,supervisor,hireDate,certificationLevel,international,byuIDnumber,fullTime)
									 VALUES ('TESTING_USER',1,1,'test','user','','','','','','','','','',1,'','','','',0,'',1)");
			$stmt->execute();
		}

		//create test database and collection
		$this->client->selectDB("test");
		$this->client->test->drop();
		$this->client->selectDB("test");

		$this->client->test->groups->remove();
		$this->client->test->users->remove();
		$coll  = $this->client->test->createCollection("groups");
		$coll2 = $this->client->test->createCollection("users");
		$coll->findOne();
		$coll2->findOne();

		// create a test group
		$this->ctrl = new \TMT\api\customData\index();
		$this->expectOutputString(
			json_encode(array("status" => "OK", "data" => array("group" => 1, "fields" => array()))).
			json_encode(array("status" => "OK", "data" => "success")).
			json_encode(array("status" => "OK", "data" => "success"))
		);
		$this->ctrl->get(array("url" => array("api", "customData", "1")));
		$this->ctrl->post(array("url" => array("api", "customData", "1"), "request" => array("field" => "field1")));
		$this->ctrl->post(array("url" => array("api", "customData", "1"), "request" => array("field" => "field2")));
	}

	/**
	 * @covers index::get
	 * @depends testPrepare
	 */
	public function testGet() {
		$this->ctrl = new index();
		$output = array("user" => "TESTING_USER", "group" => 1, "data" => array("field1" => "", "field2" => ""));
		$params = array("url" => array("api", "userGroupData", "TESTING_USER", "1"), "request" => array());
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => $output)));
		$this->ctrl->get($params);
	}

	/**
	 * @covers index::put
	 * @depends testGet
	 */
	public function testEdit() {
		$this->ctrl = new index();
		$output = array("user" => "TESTING_USER", "group" => 1, "data" => array("field1" => "test1", "field2" => "test2"));
		$params1 = array("url" => array("api", "userGroupData", "TESTING_USER", "1"), "request" => array("field1" => "test1", "field2" => "test2"));
		$params2 = array("url" => array("api", "userGroupData", "TESTING_USER", "1"), "request" => array());
		$this->expectOutputString(
			json_encode(array("status" => "OK", "data" => "success")).
			json_encode(array("status" => "OK", "data" => $output))
		);
		$this->ctrl->put($params1);
		$this->ctrl->get($params2);
	}

	/**
	 * @covers index::put
	 * @depends testEdit
	 */
	public function testDelete() {
		$this->ctrl = new index();
		$output = array("user" => "TESTING_USER", "group" => 1, "data" => array("field1" => "", "field2" => ""));
		$params1 = array("url" => array("api", "userGroupData", "TESTING_USER", "1"), "request" => array());
		$params2 = array("url" => array("api", "userGroupData", "TESTING_USER", "1"), "request" => array());
		$this->expectOutputString(
			json_encode(array("status" => "OK", "data" => "success")).
			json_encode(array("status" => "OK", "data" => $output))
		);
		$this->ctrl->delete($params1);
		$this->ctrl->get($params2);
	}

	/**
	 * destroy database
	 * @depends testDelete
	 */
	public function testCleanUp() {
		if(getenv('MONGO_PASS') !== false && getenv('MONGO_USER') !== false) {
			$this->client = new \MongoClient("mongodb://".getenv('MONGO_USER').':'.getenv('MONGO_PASS').'@'.getenv('MONGO_HOST'));
		} else {
			$this->client = new \MongoClient("mongodb://".getenv('MONGO_HOST'));
		}

		$this->client->test->groups->remove();

		$this->client->selectDB('test');
		$this->client->test->drop();

		$host = getenv('DB_HOST');
		$user = getenv('DB_USER');
		$pass = getenv('DB_PASS');
		$db   = getenv('DB_NAME');
		$connectStr = "mysql:dbname=".$db.";host=".$host.";port=3306";
		$options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ);
		$pdo = new \PDO($connectStr, $user, $pass, $options);
		$stmt = $pdo->prepare("DELETE FROM employee WHERE netID='TESTING_USER'");
		$stmt->execute();
	}
}
?>
