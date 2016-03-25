<?php

namespace TMT\accessor;

/**
 * Unit tests for the NotificationType accessor
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class UserNotificationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers ::getRecipients
     */
    public function testGetRecipients() {
        $mockdb = new \TMT\MockDB();
        $mockdb->expectPrepare("SELECT notifications.*, userNotifications.netId, userNotifications.read FROM notifications JOIN userNotifications
			ON notifications.guid=userNotifications.notificationGuid WHERE notifications.guid=:guid AND userNotifications.deleted=0");
        $mockdb->expectExecute(array(':guid' => "guid1"));
        $mockdb->setReturnData(array(
            (object) array("guid" => "guid1", "netId" => "netId1", "timestamp" => "2000-01-01 00:00:00", "message" => "message", "area" => "areaguid", "type" => "type1", "read" => "0"),
            (object) array("guid" => "guid1", "netId" => "netId2", "timestamp" => "2000-01-01 00:00:00", "message" => "message", "area" => "areaguid", "type" => "type1", "read" => "1"),
        ));
        $expected = array(
            new \TMT\model\UserNotification(array("guid" => "guid1", "netId" => "netId1", "timestamp" => "2000-01-01 00:00:00", "message" => "message", "area" => "areaguid", "type" => "type1", "read" => "0")),
            new \TMT\model\UserNotification(array("guid" => "guid1", "netId" => "netId2", "timestamp" => "2000-01-01 00:00:00", "message" => "message", "area" => "areaguid", "type" => "type1", "read" => "1")),
        );
        $accessor = new UserNotification($mockdb);
        $actual = $accessor->getRecipients("guid1");
        $this->assertEquals($expected, $actual);
        $mockdb->verify();
    }

    /**
     * @covers ::getUserNotifications
     */
    public function testGetUserNotifications() {
        $mockdb = new \TMT\MockDB();
        $mockdb->expectPrepare("SELECT notifications.*, userNotifications.netId, userNotifications.read FROM notifications JOIN userNotifications
			ON notifications.guid=userNotifications.notificationGuid WHERE userNotifications.netId=:netId AND userNotifications.deleted=0");
        $mockdb->expectExecute(array(':netId' => "netId"));
        $mockdb->setReturnData(array(
            (object) array("guid" => "guid1", "netId" => "netId", "timestamp" => "2000-01-01 00:00:00", "message" => "message", "area" => "areaguid", "type" => "type1", "read" => "0"),
            (object) array("guid" => "guid2", "netId" => "netId", "timestamp" => "2000-01-01 11:11:11", "message" => "stuff", "area" => "area2", "type" => "type2", "read" => "1"),
        ));
        $expected = array(
            new \TMT\model\UserNotification(array("guid" => "guid1", "netId" => "netId", "timestamp" => "2000-01-01 00:00:00", "message" => "message", "area" => "areaguid", "type" => "type1", "read" => "0")),
            new \TMT\model\UserNotification(array("guid" => "guid2", "netId" => "netId", "timestamp" => "2000-01-01 11:11:11", "message" => "stuff", "area" => "area2", "type" => "type2", "read" => "1")),
        );
        $accessor = new UserNotification($mockdb);
        $actual = $accessor->getUserNotifications("netId");
        $this->assertEquals($expected, $actual);
        $mockdb->verify();
    }

    /**
     * @covers ::getUserNotifications
     */
    public function testGetUserNotificationsUnreadOnly() {
        $mockdb = new \TMT\MockDB();
        $mockdb->expectPrepare("SELECT notifications.*, userNotifications.netId, userNotifications.read FROM notifications JOIN userNotifications
			ON notifications.guid=userNotifications.notificationGuid WHERE userNotifications.netId=:netId AND userNotifications.deleted=0 AND userNotifications.read=0");
        $mockdb->expectExecute(array(':netId' => "netId"));
        $mockdb->setReturnData(array(
            (object) array("guid" => "guid1", "netId" => "netId", "timestamp" => "2000-01-01 00:00:00", "message" => "message", "area" => "areaguid", "type" => "type1", "read" => "0"),
            (object) array("guid" => "guid2", "netId" => "netId", "timestamp" => "2000-01-01 11:11:11", "message" => "stuff", "area" => "area2", "type" => "type2", "read" => "0"),
        ));
        $expected = array(
            new \TMT\model\UserNotification(array("guid" => "guid1", "netId" => "netId", "timestamp" => "2000-01-01 00:00:00", "message" => "message", "area" => "areaguid", "type" => "type1", "read" => "0")),
            new \TMT\model\UserNotification(array("guid" => "guid2", "netId" => "netId", "timestamp" => "2000-01-01 11:11:11", "message" => "stuff", "area" => "area2", "type" => "type2", "read" => "0")),
        );
        $accessor = new UserNotification($mockdb);
        $actual = $accessor->getUserNotifications("netId", false);
        $this->assertEquals($expected, $actual);
        $mockdb->verify();
    }

    /**
     * @covers ::getByName
     */
    public function testMarkRead() {
        $mockdb = new \TMT\MockDB();
        $mockdb->expectPrepare("UPDATE userNotifications SET `read`=1 WHERE netId=:netId AND notificationGuid=:guid");
        $mockdb->expectExecute(array(':netId' => "netId", ':guid' => "guid1"));
        $accessor = new UserNotification($mockdb);
        $actual = $accessor->markRead("netId", "guid1");
        $mockdb->verify();
    }

    /**
     * @covers ::add
     */
    public function testAdd() {
        $mockdb = new \TMT\MockDB();
        $mockdb->expectPrepare("INSERT INTO userNotifications (netId, notificationGuid) VALUES(:netId, :guid)");
        $mockdb->expectExecute(array(":netId" => "netId", ":guid" => "guid1"));
        $accessor = new UserNotification($mockdb);
        $accessor->add("netId", "guid1");
        $mockdb->verify();
    }

    /**
     *    @covers ::delete
     */
    public function testDelete() {
        $mockdb = new \TMT\MockDB();
        $mockdb->expectPrepare("UPDATE userNotifications SET deleted=1 WHERE netId=:netId AND notificationGuid=:guid");
        $mockdb->expectExecute(array(":netId" => "netId", ":guid" => "guid1"));
        $accessor = new UserNotification($mockdb);
        $accessor->delete("netId", "guid1");
        $mockdb->verify();
    }
}
?>
