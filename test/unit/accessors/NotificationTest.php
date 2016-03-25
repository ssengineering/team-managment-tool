<?php

namespace TMT\accessor;

/**
 * Unit tests for the NotificationType accessor
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class NotificationTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::get
	 */
	public function testGet() {
		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("SELECT * FROM notifications WHERE guid=:guid");
		$mockdb->expectExecute(array(':guid' => "guid1"));
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "message", "area" => "areaguid", "type" => "type1"),
		));
		$expected = new \TMT\model\Notification(array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "message", "area" => "areaguid", "type" => "type1"));
		$accessor = new Notification($mockdb);
		$actual = $accessor->get("guid1");
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::create
	 * @depends testGet
	 */
	public function testCreate() {
		$expected = new \TMT\model\Notification(array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "message", "area" => "areaguid", "type" => "type1"));

		$mockdb = new \TMT\MockDB();
		$mockdb->expectPrepare("INSERT INTO notifications (message, type, area, guid) VALUES (:message, :type, :area, :guid)");
		$mockdb->expectExecute(array(':message' => "message", ':type' => "type1", ':area' => "areaguid", ":guid" => "guid1"));
		$mockdb->expectPrepare("SELECT * FROM notifications WHERE guid=:guid");
		$mockdb->expectExecute(array(':guid' => "guid1"));
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "message", "area" => "areaguid", "type" => "type1"),
		));
		$accessor = new Notification($mockdb);
		$accessor->setGuidCreator(new MockGuidCreator("guid1"));

		$actual = $accessor->create(new \TMT\model\Notification(array("message" => "message", "area" => "areaguid", "type" => "type1")));
		$this->assertEquals($expected, $actual);
		$mockdb->verify();
	}

	/**
	 * @covers ::search
	 */
	public function testSearchNothing() {
		$mockdb = new \TMT\MockDB();
		$accessor = new Notification($mockdb);
		$result = $accessor->search();
		$this->assertEquals(array(), $result);
		$mockdb->verify();
	}

	/**
	 * @covers ::search
	 */
	public function testSearchMessage() {
		$mockdb = new \TMT\MockDB();
		$accessor = new Notification($mockdb);
		$mockdb->expectPrepare("SELECT * FROM notifications WHERE (message LIKE :message)");
		$mockdb->expectExecute(array(':message' => "%something%"));
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "something happened", "area" => "areaguid", "type" => "type1"),
		));
		$result = $accessor->search(array('message' => 'something'));
		$this->assertEquals(array(new \TMT\model\Notification(array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "something happened", "area" => "areaguid", "type" => "type1"))), $result);
		$mockdb->verify();
	}

	/**
	 * @covers ::search
	 */
	public function testSearchType() {
		$mockdb = new \TMT\MockDB();
		$accessor = new Notification($mockdb);
		$mockdb->expectPrepare("SELECT * FROM notifications WHERE (type=:type)");
		$mockdb->expectExecute(array(':type' => "type1"));
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "something happened", "area" => "areaguid", "type" => "type1"),
		));
		$result = $accessor->search(array('type' => 'type1'));
		$this->assertEquals(array(new \TMT\model\Notification(array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "something happened", "area" => "areaguid", "type" => "type1"))), $result);
		$mockdb->verify();
	}

	/**
	 * @covers ::search
	 */
	public function testSearchArea() {
		$mockdb = new \TMT\MockDB();
		$accessor = new Notification($mockdb);
		$mockdb->expectPrepare("SELECT * FROM notifications WHERE (area=:area)");
		$mockdb->expectExecute(array(':area' => "area1"));
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "something happened", "area" => "area1", "type" => "type1"),
		));
		$result = $accessor->search(array('area' => 'area1'));
		$this->assertEquals(array(new \TMT\model\Notification(array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "something happened", "area" => "area1", "type" => "type1"))), $result);
		$mockdb->verify();
	}

	/**
	 * @covers ::search
	 */
	public function testSearchStartDate() {
		$mockdb = new \TMT\MockDB();
		$accessor = new Notification($mockdb);
		$mockdb->expectPrepare("SELECT * FROM notifications WHERE (timestamp >= :startDate)");
		$mockdb->expectExecute(array(':startDate' => "2000-01-01 00:00:00"));
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "something happened", "area" => "area1", "type" => "type1"),
		));
		$result = $accessor->search(array('startDate' => "2000-01-01 00:00:00"));
		$this->assertEquals(array(new \TMT\model\Notification(array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "something happened", "area" => "area1", "type" => "type1"))), $result);
		$mockdb->verify();
	}

	/**
	 * @covers ::search
	 */
	public function testSearchEndDate() {
		$mockdb = new \TMT\MockDB();
		$accessor = new Notification($mockdb);
		$mockdb->expectPrepare("SELECT * FROM notifications WHERE (timestamp <= :endDate)");
		$mockdb->expectExecute(array(':endDate' => "2000-01-01 00:00:00"));
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "something happened", "area" => "area1", "type" => "type1"),
		));
		$result = $accessor->search(array('endDate' => "2000-01-01 00:00:00"));
		$this->assertEquals(array(new \TMT\model\Notification(array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "something happened", "area" => "area1", "type" => "type1"))), $result);
		$mockdb->verify();
	}

	/**
	 * @covers ::search
	 */
	public function testSearchDateRange() {
		$mockdb = new \TMT\MockDB();
		$accessor = new Notification($mockdb);
		$mockdb->expectPrepare("SELECT * FROM notifications WHERE (timestamp >= :startDate AND timestamp <= :endDate)");
		$mockdb->expectExecute(array(':startDate' => "2000-01-01 00:00:00", ':endDate' => "2000-01-01 00:00:00"));
		$mockdb->setReturnData(array(
			(object)array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "something happened", "area" => "area1", "type" => "type1"),
		));
		$result = $accessor->search(array('startDate' => "2000-01-01 00:00:00", 'endDate' => "2000-01-01 00:00:00"));
		$this->assertEquals(array(new \TMT\model\Notification(array("guid" => "guid1", "timestamp" => "2000-01-01 00:00:00", "message" => "something happened", "area" => "area1", "type" => "type1"))), $result);
		$mockdb->verify();
	}
}
