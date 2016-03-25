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
class NotificationTypeTest extends \PHPUnit_Framework_TestCase {

    public function testGetAll() {
        $index = $this->getMockBuilder("\TMT\api\\notificationType\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $typeAcc = $this->getMockBuilder("\TMT\accessor\NotificationType")
            ->disableOriginalConstructor()
            ->setMethods(array("getAll"))
            ->getMock();

        $notificationTypes = array(
            new \TMT\model\NotificationType((object) array("guid" => "poot", "name" => "stuff", "resource" => "stuff", "verb" => "lalala")),
            new \TMT\model\NotificationType((object) array("guid" => "stuff", "name" => "stuff", "resource" => "stuff", "verb" => "stuff")),
        );

        $typeAcc->expects($this->once())
            ->method("getAll")
            ->willReturn($notificationTypes);
        $index->method("getAccessor")
            ->willReturn($typeAcc);

        $notifications = array(
            (object) array("guid" => "poot", "name" => "stuff", "resource" => "stuff", "verb" => "lalala"),
            (object) array("guid" => "stuff", "name" => "stuff", "resource" => "stuff", "verb" => "stuff"),
        );

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $notifications)));
        $index->get(array(
            "url" => array("api", "notificationType"),
        ));
    }

    public function testGetByGuid() {
        $index = $this->getMockBuilder("\TMT\api\\notificationType\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $typeAcc = $this->getMockBuilder("\TMT\accessor\NotificationType")
            ->disableOriginalConstructor()
            ->setMethods(array("get"))
            ->getMock();

        $notificationTypes = new \TMT\model\NotificationType((object) array("guid" => "12345", "name" => "stuff", "resource" => "stuff", "verb" => "lalala"));

        $typeAcc->expects($this->once())
            ->method("get")
            ->with("12345")
            ->willReturn($notificationTypes);
        $index->method("getAccessor")
            ->willReturn($typeAcc);

        $notifications = array("guid" => "12345", "name" => "stuff", "resource" => "stuff", "verb" => "lalala");

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $notifications)));
        $index->get(array(
            "url" => array("api", "notificationType", "12345"),
        ));
    }

    public function testPost() {
        $index = $this->getMockBuilder("\TMT\api\\notificationType\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $typeAcc = $this->getMockBuilder("\TMT\accessor\NotificationType")
            ->disableOriginalConstructor()
            ->setMethods(array("add"))
            ->getMock();

        $notification = new \TMT\model\NotificationType((object) array("guid" => null, "name" => "stuff", "resource" => null, "verb" => null));

        $typeAcc->expects($this->once())
            ->method("add")
            ->with($notification)
            ->willReturn($notification);
        $index->method("getAccessor")
            ->willReturn($typeAcc);

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $notification)));
        $index->post(array(
            "url" => array("api", "notificationType"),
            "request" => array("name" => "stuff"),
        ));
    }

    public function testPut() {
        $index = $this->getMockBuilder("\TMT\api\\notificationType\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $typeAcc = $this->getMockBuilder("\TMT\accessor\NotificationType")
            ->disableOriginalConstructor()
            ->setMethods(array("update"))
            ->getMock();

        $notification = new \TMT\model\NotificationType((object) array("guid" => "12345", "name" => "stuff", "resource" => "poots", "verb" => null));

        $typeAcc->expects($this->once())
            ->method("update")
            ->with($notification)
            ->willReturn($notification);
        $index->method("getAccessor")
            ->willReturn($typeAcc);

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => "Success")));
        $index->put(array(
            "url" => array("api", "notificationType", "12345"),
            "request" => array("name" => "stuff", "resource" => "poots"),
        ));
    }

    public function testDelete() {
        $index = $this->getMockBuilder("\TMT\api\\notificationType\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $typeAcc = $this->getMockBuilder("\TMT\accessor\NotificationType")
            ->disableOriginalConstructor()
            ->setMethods(array("delete"))
            ->getMock();

        $typeAcc->expects($this->once())
            ->method("delete")
            ->with("12345")
            ->willReturn("Success");
        $index->method("getAccessor")
            ->willReturn($typeAcc);

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => "Success")));
        $index->delete(array(
            "url" => array("api", "notificationType", "12345"),
        ));
    }

}
