<?php

namespace TMT\accessor;

/**
 * Unit tests for the base Mongodb data accessor class
 *
 * The following lines are to help with the Accessor classes.
 * PHPUnit is trying to serialize the MysqlAccessor class,
 * and since it has a PDO object as a class member it can't
 * be properly serialized. These two lines allow it to run
 * just fine.
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class MongoTest extends \PHPUnit_Framework_TestCase {

	protected $client;

	public function setUp() {
		if(getenv('MONGO_PASS') !== false && getenv('MONGO_USER') !== false) {
			$this->client = new \MongoClient("mongodb://".getenv('MONGO_USER').':'.getenv('MONGO_PASS').'@'.getenv('MONGO_HOST'));
		} else {
			$this->client = new \MongoClient("mongodb://".getenv('MONGO_HOST'));
		}
		//create test database and collection
		$this->client->selectDB("testing");
		$coll = $this->client->test->createCollection("colTest");
		$coll->findOne();
		$coll2 = $this->client->testing->createCollection("colTest");
		$coll2->findOne();
	}

	public function tearDown() {
		$this->client->test->drop();
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct() {
		try {
			$m = new MongoAccessor();
		} catch(\MongoConnectionException $e) {
			$this->fail("Failed to connect");
		}
		$this->assertEquals($m->getDatabaseName(), "test");
	}

	/**
	 * @covers ::getCollection
	 * @depends testConstruct
	 * @expectedException Exception
	 */
	public function testGetCollectionNonexistent() {
		$m = new MongoAccessor();
		$col = $m->getCollection("randomCollection");
	}

	/**
	 * @covers ::setDatabase
	 * @depends testConstruct
	 */
	public function testSetDatabase() {
		$m = new MongoAccessor();
		$this->assertEquals($m->getDatabaseName(), "test");
		$caught = false;
		try {
			$m->setDatabase("testing");
		} catch(Exception $e) {
			$caught = true;
		}
		$this->assertFalse($caught);
		$this->assertEquals($m->getDatabaseName(), "testing");
	}

	/**
	 * @covers ::getCollection
	 * @depends testConstruct
	 */
	public function testGetCollection() {
		$m = new MongoAccessor();
		$this->assertEquals($m->getDatabaseName(), "test");
		$caught = false;
		$col;
		try {
			$col = $m->getCollection("colTest");
		} catch(Exception $e) {
			$caught = true;
		}
		$this->assertFalse($caught);
		$this->assertEquals($col->getName(), "colTest");
		$dbName = explode(".", $col->__toString())[0];
		$this->assertEquals($dbName, "test");
	}

	/**
	 * @covers ::setDatabase
	 * @depends testConstruct
	 * @expectedException Exception
	 */
	public function testSetDatabaseNonexistent() {
		$m = new MongoAccessor();
		$m->setDatabase("randomDB");
	}
}
?>
