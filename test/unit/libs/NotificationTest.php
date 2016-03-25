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
class NotificationTest extends \PHPUnit_Framework_TestCase {

    public function testNotifyNoPermission() {
        $recipients = array(
            (object) array("netId" => "netId1", "method" => "email", "email" => "email@byu.edu"),
            (object) array("netId" => "netId2", "method" => "onsite", "email" => "email2@byu.edu"),
        );
        $typeAcc = $this->getMockBuilder('\TMT\accessor\NotificationType')
            ->disableOriginalConstructor()
            ->getMock();
        $typeAcc->expects($this->once())
            ->method('get')
            ->with($this->equalTo('type1'))
            ->willReturn((object) array('resource' => null, 'verb' => null));
        $prefAcc = $this->getMockBuilder('\TMT\accessor\NotificationPreferences')
            ->getMock();
        $prefAcc->expects($this->once())
            ->method('getRecipients')
            ->with("type1", "guid1")
            ->willReturn($recipients);
        $notAcc = $this->getMockBuilder('\TMT\accessor\Notification')
            ->getMock();
        $notAcc->expects($this->once())
            ->method('create')
            ->with(new \TMT\model\Notification((object) array("type" => "type1", "message" => "The message", "area" => "guid1")))
            ->willReturn(new \TMT\model\Notification((object) array("guid" => "notifGuid", "type" => "type1", "message" => "The message", "area" => "guid1")));
        $userAcc = $this->getMockBuilder('\TMT\accessor\UserNotification')
            ->setMethods(array("add"))
            ->getMock();
        $userAcc->expects($this->once())
            ->method('add')
            ->with("netId2", "notifGuid");
        $testApp = $this->getMockBuilder('\TMT\App')
            ->setMethods(array("sendAuthenticatedRequest", "getAccessor"))
            ->disableOriginalConstructor()
            ->getMock();
        $testApp->expects($this->once())
            ->method('sendAuthenticatedRequest')
            ->with($this->equalTo("POST"), $this->stringContains("/notify"), $this->equalTo(array("message" => "The message", "receivers" => json_encode($recipients))));
        $testApp->method('getAccessor')->will($this->onConsecutiveCalls($typeAcc, $prefAcc, $notAcc, $userAcc));
        $testApp->setUserInfo(array("areaGuid" => "guid1"));

        $testApp->notify("type1", "The message");
    }

    public function testNotifyAdminPermission() {
        $recipients = array(
            (object) array("netId" => "netId1", "method" => "email", "email" => "email@byu.edu"),
            (object) array("netId" => "netId2", "method" => "onsite", "email" => "email2@byu.edu"),
        );
        $typeAcc = $this->getMockBuilder('\TMT\accessor\NotificationType')
            ->disableOriginalConstructor()
            ->getMock();
        $typeAcc->expects($this->once())
            ->method('get')
            ->with($this->equalTo('type1'))
            ->willReturn((object) array('resource' => "admin", 'verb' => null));
        $prefAcc = $this->getMockBuilder('\TMT\accessor\NotificationPreferences')
            ->getMock();
        $prefAcc->expects($this->once())
            ->method('getRecipients')
            ->with("type1", "guid1")
            ->willReturn($recipients);
        $notAcc = $this->getMockBuilder('\TMT\accessor\Notification')
            ->getMock();
        $notAcc->expects($this->once())
            ->method('create')
            ->with(new \TMT\model\Notification((object) array("type" => "type1", "message" => "The message", "area" => "guid1")))
            ->willReturn(new \TMT\model\Notification((object) array("guid" => "notifGuid", "type" => "type1", "message" => "The message", "area" => "guid1")));
        $userAcc = $this->getMockBuilder('\TMT\accessor\UserNotification')
            ->setMethods(array("add"))
            ->getMock();
        $userAcc->expects($this->once())
            ->method('add')
            ->with("netId2", "notifGuid");
        $testApp = $this->getMockBuilder('\TMT\App')
            ->setMethods(array("sendAuthenticatedRequest", "getAccessor", "canBeSuperuser", "isAdmin"))
            ->disableOriginalConstructor()
            ->getMock();
        $testApp->expects($this->once())
            ->method('sendAuthenticatedRequest')
            ->with($this->equalTo("POST"), $this->stringContains("/notify"), $this->equalTo(array("message" => "The message", "receivers" => json_encode($recipients))));
        $testApp->method('getAccessor')->will($this->onConsecutiveCalls($typeAcc, $prefAcc, $notAcc, $userAcc));
        $testApp->method("canBeSuperuser")->willReturn(false);
        $testApp->method("isAdmin")->willReturn(true);
        $testApp->setUserInfo(array("areaGuid" => "guid1"));

        $testApp->notify("type1", "The message");
    }

    public function testNotifyAdminPermissionCanSU() {
        $recipients = array(
            (object) array("netId" => "netId1", "method" => "email", "email" => "email@byu.edu"),
            (object) array("netId" => "netId2", "method" => "onsite", "email" => "email2@byu.edu"),
        );
        $typeAcc = $this->getMockBuilder('\TMT\accessor\NotificationType')
            ->disableOriginalConstructor()
            ->getMock();
        $typeAcc->expects($this->once())
            ->method('get')
            ->with($this->equalTo('type1'))
            ->willReturn((object) array('resource' => "admin", 'verb' => null));
        $prefAcc = $this->getMockBuilder('\TMT\accessor\NotificationPreferences')
            ->getMock();
        $prefAcc->expects($this->once())
            ->method('getRecipients')
            ->with("type1", "guid1")
            ->willReturn($recipients);
        $notAcc = $this->getMockBuilder('\TMT\accessor\Notification')
            ->getMock();
        $notAcc->expects($this->once())
            ->method('create')
            ->with(new \TMT\model\Notification((object) array("type" => "type1", "message" => "The message", "area" => "guid1")))
            ->willReturn(new \TMT\model\Notification((object) array("guid" => "notifGuid", "type" => "type1", "message" => "The message", "area" => "guid1")));
        $userAcc = $this->getMockBuilder('\TMT\accessor\UserNotification')
            ->setMethods(array("add"))
            ->getMock();
        $userAcc->expects($this->once())
            ->method('add')
            ->with("netId2", "notifGuid");
        $testApp = $this->getMockBuilder('\TMT\App')
            ->setMethods(array("sendAuthenticatedRequest", "getAccessor", "canBeSuperuser", "isAdmin"))
            ->disableOriginalConstructor()
            ->getMock();
        $testApp->expects($this->once())
            ->method('sendAuthenticatedRequest')
            ->with($this->equalTo("POST"), $this->stringContains("/notify"), $this->equalTo(array("message" => "The message", "receivers" => json_encode($recipients))));
        $testApp->method('getAccessor')->will($this->onConsecutiveCalls($typeAcc, $prefAcc, $notAcc, $userAcc));
        $testApp->method("canBeSuperuser")->willReturn(true);
        $testApp->method("isAdmin")->willReturn(false);
        $testApp->setUserInfo(array("areaGuid" => "guid1"));

        $testApp->notify("type1", "The message");
    }

    public function testNotifyAdminPermissionNotAllCan() {
        $recipients = array(
            (object) array("netId" => "netId1", "method" => "email", "email" => "email@byu.edu"),
            (object) array("netId" => "netId2", "method" => "onsite", "email" => "email2@byu.edu"),
        );
        $finalRecipients = array(
            (object) array("netId" => "netId1", "method" => "email", "email" => "email@byu.edu"),
        );
        $typeAcc = $this->getMockBuilder('\TMT\accessor\NotificationType')
            ->disableOriginalConstructor()
            ->getMock();
        $typeAcc->expects($this->once())
            ->method('get')
            ->with($this->equalTo('type1'))
            ->willReturn((object) array('resource' => "admin", 'verb' => null));
        $prefAcc = $this->getMockBuilder('\TMT\accessor\NotificationPreferences')
            ->getMock();
        $prefAcc->expects($this->once())
            ->method('getRecipients')
            ->with("type1", "guid1")
            ->willReturn($recipients);
        $prefAcc->expects($this->once())
            ->method('delete')
            ->with("netId2", "type1", "guid1")
            ->willReturn($recipients);
        $notAcc = $this->getMockBuilder('\TMT\accessor\Notification')
            ->getMock();
        $notAcc->expects($this->once())
            ->method('create')
            ->with(new \TMT\model\Notification((object) array("type" => "type1", "message" => "The message", "area" => "guid1")))
            ->willReturn(new \TMT\model\Notification((object) array("guid" => "notifGuid", "type" => "type1", "message" => "The message", "area" => "guid1")));
        $userAcc = $this->getMockBuilder('\TMT\accessor\UserNotification')
            ->setMethods(array("add"))
            ->getMock();
        $userAcc->expects($this->never())
            ->method('add');
        $testApp = $this->getMockBuilder('\TMT\App')
            ->setMethods(array("sendAuthenticatedRequest", "getAccessor", "canBeSuperuser", "isAdmin"))
            ->disableOriginalConstructor()
            ->getMock();
        $testApp->expects($this->once())
            ->method('sendAuthenticatedRequest')
            ->with($this->equalTo("POST"), $this->stringContains("/notify"), $this->equalTo(array("message" => "The message", "receivers" => json_encode($finalRecipients))));
        $testApp->method('getAccessor')->will($this->onConsecutiveCalls($typeAcc, $prefAcc, $notAcc, $userAcc));
        $testApp->method("canBeSuperuser")->willReturn(false);
        $testApp->method("isAdmin")->will($this->onConsecutiveCalls(true, false));
        $testApp->setUserInfo(array("areaGuid" => "guid1"));

        $testApp->notify("type1", "The message");
    }

    public function testNotifyNormalPermissionNotAllCan() {
        $recipients = array(
            (object) array("netId" => "netId1", "method" => "email", "email" => "email@byu.edu"),
            (object) array("netId" => "netId2", "method" => "onsite", "email" => "email2@byu.edu"),
        );
        $finalRecipients = array(
            (object) array("netId" => "netId1", "method" => "email", "email" => "email@byu.edu"),
        );
        $typeAcc = $this->getMockBuilder('\TMT\accessor\NotificationType')
            ->disableOriginalConstructor()
            ->getMock();
        $typeAcc->expects($this->once())
            ->method('get')
            ->with($this->equalTo('type1'))
            ->willReturn((object) array('resource' => "resource1", 'verb' => "edit"));
        $prefAcc = $this->getMockBuilder('\TMT\accessor\NotificationPreferences')
            ->getMock();
        $prefAcc->expects($this->once())
            ->method('getRecipients')
            ->with("type1", "guid1")
            ->willReturn($recipients);
        $prefAcc->expects($this->once())
            ->method('delete')
            ->with("netId2", "type1", "guid1")
            ->willReturn($recipients);
        $notAcc = $this->getMockBuilder('\TMT\accessor\Notification')
            ->getMock();
        $notAcc->expects($this->once())
            ->method('create')
            ->with(new \TMT\model\Notification((object) array("type" => "type1", "message" => "The message", "area" => "guid1")))
            ->willReturn(new \TMT\model\Notification((object) array("guid" => "notifGuid", "type" => "type1", "message" => "The message", "area" => "guid1")));
        $userAcc = $this->getMockBuilder('\TMT\accessor\UserNotification')
            ->setMethods(array("add"))
            ->getMock();
        $userAcc->expects($this->never())
            ->method('add');
        $testApp = $this->getMockBuilder('\TMT\App')
            ->setMethods(array("sendAuthenticatedRequest", "getAccessor", "can", "canBeSuperuser"))
            ->disableOriginalConstructor()
            ->getMock();
        $testApp->expects($this->once())
            ->method('sendAuthenticatedRequest')
            ->with($this->equalTo("POST"), $this->stringContains("/notify"), $this->equalTo(array("message" => "The message", "receivers" => json_encode($finalRecipients))))
            ->willReturn(true);
        $testApp->method('getAccessor')->will($this->onConsecutiveCalls($typeAcc, $prefAcc, $notAcc, $userAcc));
        $testApp->method("canBeSuperuser")->willReturn(false);
        $testApp->method("can")->will($this->onConsecutiveCalls(true, false));
        $testApp->setUserInfo(array("areaGuid" => "guid1"));

        $testApp->notify("type1", "The message");
    }

    public function testNotifyNormalPermission() {
        $recipients = array(
            (object) array("netId" => "netId1", "method" => "email", "email" => "email@byu.edu"),
            (object) array("netId" => "netId2", "method" => "onsite", "email" => "email2@byu.edu"),
        );
        $typeAcc = $this->getMockBuilder('\TMT\accessor\NotificationType')
            ->disableOriginalConstructor()
            ->getMock();
        $typeAcc->expects($this->once())
            ->method('get')
            ->with($this->equalTo('type1'))
            ->willReturn((object) array('resource' => "resource1", 'verb' => "edit"));
        $prefAcc = $this->getMockBuilder('\TMT\accessor\NotificationPreferences')
            ->getMock();
        $prefAcc->expects($this->once())
            ->method('getRecipients')
            ->with("type1", "guid1")
            ->willReturn($recipients);
        $notAcc = $this->getMockBuilder('\TMT\accessor\Notification')
            ->getMock();
        $notAcc->expects($this->once())
            ->method('create')
            ->with(new \TMT\model\Notification((object) array("type" => "type1", "message" => "The message", "area" => "guid1")))
            ->willReturn(new \TMT\model\Notification((object) array("guid" => "notifGuid", "type" => "type1", "message" => "The message", "area" => "guid1")));
        $userAcc = $this->getMockBuilder('\TMT\accessor\UserNotification')
            ->setMethods(array("add"))
            ->getMock();
        $userAcc->expects($this->once())
            ->method('add')
            ->with("netId2", "notifGuid");
        $testApp = $this->getMockBuilder('\TMT\App')
            ->setMethods(array("sendAuthenticatedRequest", "getAccessor", "can"))
            ->disableOriginalConstructor()
            ->getMock();
        $testApp->expects($this->once())
            ->method('sendAuthenticatedRequest')
            ->with($this->equalTo("POST"), $this->stringContains("/notify"), $this->equalTo(array("message" => "The message", "receivers" => json_encode($recipients))));
        $testApp->method('getAccessor')->will($this->onConsecutiveCalls($typeAcc, $prefAcc, $notAcc, $userAcc));
        $testApp->method("canBeSuperuser")->willReturn(false);
        $testApp->method("can")->willReturn(true);
        $testApp->setUserInfo(array("areaGuid" => "guid1"));

        $testApp->notify("type1", "The message");
    }

    public function testNotifyNoReceivers() {
        $recipients = array(
            (object) array("netId" => "netId1", "method" => "email", "email" => "email@byu.edu"),
            (object) array("netId" => "netId2", "method" => "onsite", "email" => "email2@byu.edu"),
        );
        $typeAcc = $this->getMockBuilder('\TMT\accessor\NotificationType')
            ->disableOriginalConstructor()
            ->getMock();
        $typeAcc->expects($this->once())
            ->method('get')
            ->with($this->equalTo('type1'))
            ->willReturn((object) array('resource' => "resource1", 'verb' => "edit"));
        $prefAcc = $this->getMockBuilder('\TMT\accessor\NotificationPreferences')
            ->getMock();
        $prefAcc->expects($this->once())
            ->method('getRecipients')
            ->with("type1", "guid1")
            ->willReturn($recipients);
        $prefAcc->expects($this->exactly(2))
            ->method('delete')
            ->withConsecutive(
                array("netId1", "type1", "guid1"),
                array("netId2", "type1", "guid1")
            )
            ->willReturn($recipients);
        $notAcc = $this->getMockBuilder('\TMT\accessor\Notification')
            ->getMock();
        $notAcc->expects($this->once())
            ->method('create')
            ->with(new \TMT\model\Notification((object) array("type" => "type1", "message" => "The message", "area" => "guid1")))
            ->willReturn(new \TMT\model\Notification((object) array("guid" => "notifGuid", "type" => "type1", "message" => "The message", "area" => "guid1")));
        $testApp = $this->getMockBuilder('\TMT\App')
            ->setMethods(array("sendAuthenticatedRequest", "getAccessor", "can", "canBeSuperuser"))
            ->disableOriginalConstructor()
            ->getMock();
        $testApp->expects($this->never())->method('sendAuthenticatedRequest');
        $testApp->expects($this->exactly(3))->method('getAccessor')->will($this->onConsecutiveCalls($typeAcc, $prefAcc, $notAcc));
        $testApp->method("canBeSuperuser")->willReturn(false);
        $testApp->method("can")->willReturn(false);
        $testApp->setUserInfo(array("areaGuid" => "guid1"));

        $testApp->notify("type1", "The message");
    }
}
