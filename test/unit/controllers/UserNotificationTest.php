<?php

namespace TMT;

/**
 * The following lines are to help with the Accessor classes.
 * PHPUnit is trying to serialize the MysqlAccessor class,
 * and since it has a PDO object as a class member it can't
 * be properly serialized. These two lines allow it to run
 * just fine.
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class UserNotificationTest extends \PHPUnit_Framework_TestCase {

    public function testGetRecipients() {
        $index = $this->getMockBuilder("\TMT\api\\userNotification\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $userAcc = $this->getMockBuilder("\TMT\accessor\UserNotification")
            ->disableOriginalConstructor()
            ->setMethods(array("getRecipients"))
            ->getMock();

        $notifications = array(
            new \TMT\model\UserNotification((object) array("guid" => "guid1", "timestamp" => null, "type" => "type1", "area" => "areaguid", "message" => "A message", "netId" => "netId1", "read" => false)),
            new \TMT\model\UserNotification((object) array("guid" => "guid1", "timestamp" => null, "type" => "type1", "area" => "areaguid", "message" => "A message", "netId" => "netId2", "read" => true)),
        );

        $userAcc->expects($this->once())
            ->method("getRecipients")
            ->with("guid1")
            ->willReturn($notifications);
        $index->method("getAccessor")
            ->willReturn($userAcc);

        $notifications = array(
            (object) array("guid" => "guid1", "timestamp" => null, "type" => "type1", "area" => "areaguid", "message" => "A message", "netId" => "netId1", "read" => false),
            (object) array("guid" => "guid1", "timestamp" => null, "type" => "type1", "area" => "areaguid", "message" => "A message", "netId" => "netId2", "read" => true),
        );

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $notifications)));
        $index->get(array(
            "url" => array("api", "userNotification"),
            "request" => array("guid" => "guid1"),
        ));
    }

    public function testGetUserNotifications() {
        $index = $this->getMockBuilder("\TMT\api\\userNotification\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $userAcc = $this->getMockBuilder("\TMT\accessor\UserNotification")
            ->disableOriginalConstructor()
            ->setMethods(array("getUserNotifications"))
            ->getMock();

        $notifications = array(
            new \TMT\model\UserNotification((object) array("guid" => "guid1", "timestamp" => null, "type" => "type1", "area" => "areaguid", "message" => "A message", "netId" => "netId1", "read" => false)),
            new \TMT\model\UserNotification((object) array("guid" => "guid2", "timestamp" => null, "type" => "type2", "area" => "areaguid", "message" => "Another message", "netId" => "netId1", "read" => true)),
        );

        $userAcc->expects($this->once())
            ->method("getUserNotifications")
            ->with("netId1", true)
            ->willReturn($notifications);
        $index->method("getAccessor")
            ->willReturn($userAcc);

        $notifications = array(
            (object) array("guid" => "guid1", "timestamp" => null, "type" => "type1", "area" => "areaguid", "message" => "A message", "netId" => "netId1", "read" => false),
            (object) array("guid" => "guid2", "timestamp" => null, "type" => "type2", "area" => "areaguid", "message" => "Another message", "netId" => "netId1", "read" => true),
        );

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $notifications)));
        $index->get(array(
            "url" => array("api", "userNotification"),
            "request" => array("netId" => "netId1"),
        ));
    }

    public function testGetUserNotificationsUnreadOnly() {
        $index = $this->getMockBuilder("\TMT\api\\userNotification\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $userAcc = $this->getMockBuilder("\TMT\accessor\UserNotification")
            ->disableOriginalConstructor()
            ->setMethods(array("getUserNotifications"))
            ->getMock();

        $notifications = array(
            new \TMT\model\UserNotification((object) array("guid" => "guid1", "timestamp" => null, "type" => "type1", "area" => "areaguid", "message" => "A message", "netId" => "netId1", "read" => false)),
        );

        $userAcc->expects($this->once())
            ->method("getUserNotifications")
            ->with("netId1", false)
            ->willReturn($notifications);
        $index->method("getAccessor")
            ->willReturn($userAcc);

        $notifications = array(
            (object) array("guid" => "guid1", "timestamp" => null, "type" => "type1", "area" => "areaguid", "message" => "A message", "netId" => "netId1", "read" => false),
        );

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $notifications)));
        $index->get(array(
            "url" => array("api", "userNotification"),
            "request" => array("netId" => "netId1", "read" => "false"),
        ));
    }

    public function testPut() {
        $index = $this->getMockBuilder("\TMT\api\\userNotification\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $userAcc = $this->getMockBuilder("\TMT\accessor\UserNotification")
            ->disableOriginalConstructor()
            ->setMethods(array("markRead"))
            ->getMock();

        $userAcc->expects($this->once())
            ->method("markRead")
            ->with("netId1", "guid2")
            ->willReturn(null);
        $index->method("getAccessor")
            ->willReturn($userAcc);

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => "success")));
        $index->put(array(
            "url" => array("api", "userNotification", "netId1", "guid2"),
        ));
    }

    public function testDelete() {
        $index = $this->getMockBuilder("\TMT\api\\userNotification\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $userAcc = $this->getMockBuilder("\TMT\accessor\UserNotification")
            ->disableOriginalConstructor()
            ->setMethods(array("delete"))
            ->getMock();

        $userAcc->expects($this->once())
            ->method("delete")
            ->with("netId1", "guid2")
            ->willReturn(null);
        $index->method("getAccessor")
            ->willReturn($userAcc);

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => "success")));
        $index->delete(array(
            "url" => array("api", "userNotification", "netId1", "guid2"),
        ));
    }
}
