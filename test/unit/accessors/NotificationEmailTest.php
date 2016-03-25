<?php

namespace TMT\accessor;

/**
 * Unit tests for the NotificationEmail accessor
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class NotificationEmailTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::get
	 */
	public function testGet() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("SELECT * FROM notificationEmails WHERE guid=:guid");
		$mockdb->expectExecute(array(':guid' => "guid1"));
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "email" => "email@byu.edu", "type" => "type1", "area" => "areaguid"),
		));
		$expected = new \TMT\model\NotificationEmail(array("guid" => "guid1", "email" => "email@byu.edu", "type" => "type1", "area" => "areaguid"));
		$accessor = new NotificationEmail($mockdb);
		$actual = $accessor->get("guid1");
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::getByType
	 */
	public function testGetByType() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("SELECT * FROM notificationEmails WHERE type=:type AND area=:area");
		$mockdb->expectExecute(array(':type' => "type1", ':area' => "areaguid"));
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "email" => "email@byu.edu", "type" => "type1", "area" => "areaguid"),
			(object)array("guid" => "guid2", "email" => "email1@gmail.com", "type" => "type1", "area" => "areaguid")
		));
		$expected = array(
			new \TMT\model\NotificationEmail(array("guid" => "guid1", "email" => "email@byu.edu", "type" => "type1", "area" => "areaguid")),
			new \TMT\model\NotificationEmail(array("guid" => "guid2", "email" => "email1@gmail.com", "type" => "type1", "area" => "areaguid"))
		);
		$accessor = new NotificationEmail($mockdb);
		$actual = $accessor->getByType("type1", "areaguid");
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::getByArea
	 */
	public function testGetByArea() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("SELECT * FROM notificationEmails WHERE area=:area");
		$mockdb->expectExecute(array(':area' => "areaguid"));
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "email" => "email@byu.edu", "type" => "type1", "area" => "areaguid"),
			(object)array("guid" => "guid2", "email" => "email1@gmail.com", "type" => "type2", "area" => "areaguid")
		));
		$expected = array(
			new \TMT\model\NotificationEmail(array("guid" => "guid1", "email" => "email@byu.edu", "type" => "type1", "area" => "areaguid")),
			new \TMT\model\NotificationEmail(array("guid" => "guid2", "email" => "email1@gmail.com", "type" => "type2", "area" => "areaguid"))
		);
		$accessor = new NotificationEmail($mockdb);
		$actual = $accessor->getByArea("areaguid");
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::add
	 */
	public function testAdd() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("INSERT INTO notificationEmails (guid, email, type, area) VALUES (:guid, :email, :type, :area)");
		$mockdb->expectExecute(array(":guid" => "guid1", ":email" => "email@byu.edu", ":type" => "type1", ":area" => "areaguid"));
		$accessor = new NotificationEmail($mockdb);
		$accessor->setGuidCreator(new MockGuidCreator("guid1"));
		$accessor->add(new \TMT\model\NotificationEmail(array("email" => "email@byu.edu", "type" => "type1", "area" => "areaguid")));
		$mockdb->verify();
	}

	/**
	 *	@covers ::delete
	 */
	public function testDelete() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("DELETE FROM notificationEmails WHERE guid=:guid");
		$mockdb->expectExecute(array(":guid" => "guid1"));
		$accessor = new NotificationEmail($mockdb);
		$accessor->delete("guid1");
		$mockdb->verify();
	}
}
?>
