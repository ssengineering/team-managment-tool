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
class NotificationPreferenceTest extends \PHPUnit_Framework_TestCase {

    public function testGetRecipients() {
        $index = $this->getMockBuilder("\TMT\api\\notificationPreference\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $typeAcc = $this->getMockBuilder("\TMT\accessor\NotificationPreference")
            ->disableOriginalConstructor()
            ->setMethods(array("getRecipients"))
            ->getMock();

        $notificationPreferences = array(
            new \TMT\model\NotificationPreference((object) array("type" => "poot", "area" => "stuff", "netId" => "netId1", "method" => "email", "email" => "test@test.com")),
            new \TMT\model\NotificationPreference((object) array("type" => "poot", "area" => "stuff", "netId" => "netId2", "method" => "email", "email" => "test@test.com")),
        );

        $typeAcc->expects($this->once())
            ->method("getRecipients")
            ->with("sandwich", "poots")
            ->willReturn($notificationPreferences);
        $index->method("getAccessor")
            ->willReturn($typeAcc);

        $notifications = array(
            (object) array("type" => "poot", "area" => "stuff", "netId" => "netId1", "method" => "email", "email" => "test@test.com"),
            (object) array("type" => "poot", "area" => "stuff", "netId" => "netId2", "method" => "email", "email" => "test@test.com"),
        );

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $notifications)));
        $index->get(array(
            "url" => array("api", "notificationPreference"),
            "request" => array("type" => "sandwich", "area" => "poots"),
        ));
    }

    public function testGetUserPreferences() {
        $index = $this->getMockBuilder("\TMT\api\\notificationPreference\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $typeAcc = $this->getMockBuilder("\TMT\accessor\NotificationPreference")
            ->disableOriginalConstructor()
            ->setMethods(array("getUserPreferences"))
            ->getMock();

        $notificationPreferences = array(
            new \TMT\model\NotificationPreference((object) array("type" => "poot", "area" => "stuff", "netId" => "netId1", "method" => "email", "email" => "test@test.com")),
            new \TMT\model\NotificationPreference((object) array("type" => "poot", "area" => "stuff", "netId" => "netId2", "method" => "email", "email" => "test@test.com")),
        );

        $typeAcc->expects($this->once())
            ->method("getUserPreferences")
            ->with("netId1", "poots")
            ->willReturn($notificationPreferences);
        $index->method("getAccessor")
            ->willReturn($typeAcc);

        $notifications = array(
            (object) array("type" => "poot", "area" => "stuff", "netId" => "netId1", "method" => "email", "email" => "test@test.com"),
            (object) array("type" => "poot", "area" => "stuff", "netId" => "netId2", "method" => "email", "email" => "test@test.com"),
        );

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $notifications)));
        $index->get(array(
            "url" => array("api", "notificationPreference"),
            "request" => array("netId" => "netId1", "area" => "poots"),
        ));
    }

    public function testPost() {
        $index = $this->getMockBuilder("\TMT\api\\notificationPreference\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $typeAcc = $this->getMockBuilder("\TMT\accessor\NotificationPreference")
            ->disableOriginalConstructor()
            ->setMethods(array("add"))
            ->getMock();

        $notification = new \TMT\model\NotificationPreference((object) array("netId" => "netId1", "type" => "poot", "method" => "email", "area" => "sandwich"));

        $typeAcc->expects($this->once())
            ->method("add")
            ->with($notification);
        $index->method("getAccessor")
            ->willReturn($typeAcc);

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => "success")));
        $index->post(array(
            "url" => array("api", "notificationPreference"),
            "request" => array("netId" => "netId1", "type" => "poot", "method" => "email", "area" => "sandwich"),
        ));
    }

    public function testDelete() {
        $index = $this->getMockBuilder("\TMT\api\\notificationPreference\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $typeAcc = $this->getMockBuilder("\TMT\accessor\NotificationPreference")
            ->disableOriginalConstructor()
            ->setMethods(array("delete"))
            ->getMock();

        $typeAcc->expects($this->once())
            ->method("delete")
            ->with("netId1", "poot", "sandwich");
        $index->method("getAccessor")
            ->willReturn($typeAcc);

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => "success")));
        $index->delete(array(
            "url" => array("api", "notificationPreference", "netId1", "poot", "sandwich"),
        ));
    }

}
