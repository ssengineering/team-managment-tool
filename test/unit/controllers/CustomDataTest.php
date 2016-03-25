<?php

namespace TMT\api\customData;

/**
 * Unit tests for the customData controller class
 */
class CustomDataTest extends \PHPUnit_Framework_TestCase {

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
		$stmt = $pdo->prepare("INSERT INTO employee (netID,active,area,firstName,lastName,maidenName,phone,email,birthday,languages,hometown,major,
		                         missionOrStudyAbroad,graduationDate,position,shift,supervisor,hireDate,certificationLevel,international,byuIDnumber,fullTime)
		                         VALUES ('TESTING_USER',1,1,'test','user','','','','','','','','','',1,'','','','',0,'',1)");
		$stmt->execute();

		//create test database and collection
		$this->client->selectDB("test");
		$this->client->test->drop();
		$this->client->selectDB("test");

		$this->client->test->groups->remove();
		$coll = $this->client->test->createCollection("groups");
		$coll->findOne();
	}

	/**
	 * @covers index::get
	 * @depends testPrepare
	 */
	public function testGet() {
		$this->ctrl = new index();
		$output = array("group" => 1, "fields" => array());
		$params = array("url" => array("api", "customData", "1"), "request" => array());
		$this->expectOutputString(json_encode(array("status" => "OK", "data" => $output)));
		$this->ctrl->get($params);
	}

	/**
	 * @covers index::post
	 * @depends testGet
	 */
	public function testAdd() {
		$this->ctrl = new index();
		$params1 = array("url" => array("api", "customData", "1"), "request" => array("field" => "field1"));
		$params2 = array("url" => array("api", "customData", "1"), "request" => array("field" => "field2"));
		$params3 = array("url" => array("api", "customData", "1"), "request" => array());
		$this->expectOutputString(
			json_encode(array("status" => "OK", "data" => "success")).
			json_encode(array("status" => "OK", "data" => "success")).
			json_encode(array("status" => "OK", "data" => array("group" => 1, "fields" => array("field1", "field2"))))
		);
		$this->ctrl->post($params1);
		$this->ctrl->post($params2);
		$this->ctrl->get($params3);
	}

	/**
	 * @covers index::put
	 * @depends testAdd
	 */
	public function testRename() {
		$this->ctrl = new index();
		$params1 = array("url" => array("api", "customData", "1", "field1"), "request" => array("old" => "field1", "field" => "changed"));
		$params2 = array("url" => array("api", "customData", "1"), "request" => array());
		$this->expectOutputString(
			json_encode(array("status" => "OK", "data" => "success")).
			json_encode(array("status" => "OK", "data" => array("group" => 1, "fields" => array("field2", "changed"))))
		);
		$this->ctrl->put($params1);
		$this->ctrl->get($params2);
	}

	/**
	 * @covers index::delete
	 * @depends testRename
	 */
	public function testDelete() {
		$this->ctrl = new index();
		$params1 = array("url" => array("api", "customData", "1"), "request" => array("field" => "changed"));
		$params2 = array("url" => array("api", "customData", "1"), "request" => array("field" => "field2"));
		$params3 = array("url" => array("api", "customData", "1"), "request" => array());
		$this->expectOutputString(
			json_encode(array("status" => "OK", "data" => "success")).
			json_encode(array("status" => "OK", "data" => "success")).
			json_encode(array("status" => "OK", "data" => array("group" => 1, "fields" => array())))
		);
		$this->ctrl->delete($params1);
		$this->ctrl->delete($params2);
		$this->ctrl->get($params3);
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
