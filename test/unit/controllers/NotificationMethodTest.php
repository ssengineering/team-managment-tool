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
class NotificationMethodTest extends \PHPUnit_Framework_TestCase {

    public function testGetAll() {
        $index = $this->getMockBuilder("\TMT\api\\notificationMethod\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $typeAcc = $this->getMockBuilder("\TMT\accessor\NotificationMethod")
            ->disableOriginalConstructor()
            ->setMethods(array("getAll"))
            ->getMock();

        $notifications = array(
            (object) array("guid" => "poot", "name" => "stuff", "resource" => "stuff", "verb" => "lalala"),
            (object) array("guid" => "stuff", "name" => "stuff", "resource" => "stuff", "verb" => "stuff"),
        );

        $typeAcc->expects($this->once())
            ->method("getAll")
            ->willReturn($notifications);
        $index->method("getAccessor")
            ->willReturn($typeAcc);

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $notifications)));
        $index->get(array(
            "url" => array("api", "notificationMethod"),
        ));
    }

    public function testPost() {
        $index = $this->getMockBuilder("\TMT\api\\notificationMethod\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $typeAcc = $this->getMockBuilder("\TMT\accessor\NotificationMethod")
            ->disableOriginalConstructor()
            ->setMethods(array("add"))
            ->getMock();

        $typeAcc->expects($this->once())
            ->method("add")
            ->with("stuff")
            ->willReturn("Success");
        $index->method("getAccessor")
            ->willReturn($typeAcc);

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => "Success")));
        $index->post(array(
            "url" => array("api", "notificationMethod"),
            "request" => array("name" => "stuff"),
        ));
    }

    public function testDelete() {
        $index = $this->getMockBuilder("\TMT\api\\notificationMethod\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $typeAcc = $this->getMockBuilder("\TMT\accessor\NotificationMethod")
            ->disableOriginalConstructor()
            ->setMethods(array("delete"))
            ->getMock();

        $typeAcc->expects($this->once())
            ->method("delete")
            ->with("stuff")
            ->willReturn("Success");
        $index->method("getAccessor")
            ->willReturn($typeAcc);

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => "Success")));
        $index->delete(array(
            "url" => array("api", "notificationMethod", "stuff"),
        ));
    }

}
