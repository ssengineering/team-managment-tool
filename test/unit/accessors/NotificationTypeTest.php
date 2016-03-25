<?php

namespace TMT\accessor;

/**
 * Unit tests for the NotificationType accessor
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class NotificationTypeTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::getAll
	 */
	public function testGetAll() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("SELECT * FROM notificationTypes");
		$mockdb->expectExecute();
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "name" => "type1", "resource" => "resguid1", "verb" => "read"),
			(object)array("guid" => "guid2", "name" => "type2", "resource" => null, "verb" => null),
		));
		$expected = array(
			new \TMT\model\NotificationType(array("guid" => "guid1", "name" => "type1", "resource" => "resguid1", "verb" => "read")),
			new \TMT\model\NotificationType(array("guid" => "guid2", "name" => "type2", "resource" => null, "verb" => null)),
		);
		$accessor = new NotificationType($mockdb);
		$actual = $accessor->getAll();
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::get
	 */
	public function testGet() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("SELECT * FROM notificationTypes WHERE guid=:guid");
		$mockdb->expectExecute(array(':guid' => "guid1"));
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "name" => "type1", "resource" => "resguid1", "verb" => "read"),
		));
		$expected = new \TMT\model\NotificationType(array("guid" => "guid1", "name" => "type1", "resource" => "resguid1", "verb" => "read"));
		$accessor = new NotificationType($mockdb);
		$actual = $accessor->get("guid1");
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::getByName
	 */
	public function testGetByName() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("SELECT * FROM notificationTypes WHERE name=:name");
		$mockdb->expectExecute(array(':name' => "type1"));
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "name" => "type1", "resource" => "resguid1", "verb" => "read"),
		));
		$expected = new \TMT\model\NotificationType(array("guid" => "guid1", "name" => "type1", "resource" => "resguid1", "verb" => "read"));
		$accessor = new NotificationType($mockdb);
		$actual = $accessor->getByName("type1");
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::add
	 */
	public function testAdd() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("INSERT INTO notificationTypes (guid, name, resource, verb) VALUES(:guid, :name, :resource, :verb)");
		$mockdb->expectExecute(array(":guid" => "guid1", ":name" => "type1", ":resource" => "resguid1", ":verb" => "read"));
		$accessor = new NotificationType($mockdb);
		$accessor->setGuidCreator(new MockGuidCreator("guid1"));
		$accessor->add(new \TMT\model\NotificationType(array("name" => "type1", "resource" => "resguid1", "verb" => "read")));
		$mockdb->verify();
	}

	/**
	 * @covers ::update
	 */
	public function testUpdate() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("UPDATE notificationTypes SET name=:name, resource=:resource, verb=:verb WHERE guid=:guid");
		$mockdb->expectExecute(array(":name" => "type1", ":resource" => "resguid1", ":verb" => "read", ":guid" => "guid1"));
		$accessor = new NotificationType($mockdb);
		$accessor->update(new \TMT\model\NotificationType(array("guid" => "guid1", "name" => "type1", "resource" => "resguid1", "verb" => "read")));
		$mockdb->verify();
	}

	/**
	 *	@covers ::delete
	 */
	public function testDelete() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("DELETE FROM notificationTypes WHERE guid=:guid");
		$mockdb->expectExecute(array(":guid" => "guid1"));
		$accessor = new NotificationType($mockdb);
		$accessor->delete("guid1");
		$mockdb->verify();
	}
}
?>
