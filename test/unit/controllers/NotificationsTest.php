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
class NotificationsTest extends \PHPUnit_Framework_TestCase {

    public function testGet() {
        $index = $this->getMockBuilder("\TMT\api\\notification\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $notifAcc = $this->getMockBuilder("\TMT\accessor\Notification")
            ->disableOriginalConstructor()
            ->setMethods(array("get"))
            ->getMock();

        $notifications = new \TMT\model\Notification((object) array("guid" => "guid1", "timestamp" => null, "message" => "A message", "type" => "type1", "area" => "areaguid"));

        $notifAcc->expects($this->once())
            ->method("get")
            ->with("guid1")
            ->willReturn($notifications);
        $index->method("getAccessor")
            ->willReturn($notifAcc);

        $notifications = (object) array("guid" => "guid1", "timestamp" => null, "message" => "A message", "type" => "type1", "area" => "areaguid");

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $notifications)));
        $index->get(array(
            "url" => array("api", "notification", "guid1"),
        ));
    }

    public function testSearchArea() {
        $index = $this->getMockBuilder("\TMT\api\\notification\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $notifAcc = $this->getMockBuilder("\TMT\accessor\Notification")
            ->disableOriginalConstructor()
            ->setMethods(array("search"))
            ->getMock();

        $notifications = array(
            new \TMT\model\Notification((object) array("guid" => "guid1", "timestamp" => null, "message" => "A message", "type" => "type1", "area" => "areaguid")),
            new \TMT\model\Notification((object) array("guid" => "guid2", "timestamp" => null, "message" => "Another message", "type" => "type2", "area" => "areaguid")),
        );

        $notifAcc->expects($this->once())
            ->method("search")
            ->with(array("area" => "areaguid"))
            ->willReturn($notifications);
        $index->method("getAccessor")
            ->willReturn($notifAcc);

        $notifications = array(
            (object) array("guid" => "guid1", "timestamp" => null, "message" => "A message", "type" => "type1", "area" => "areaguid"),
            (object) array("guid" => "guid2", "timestamp" => null, "message" => "Another message", "type" => "type2", "area" => "areaguid"),
        );

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $notifications)));
        $index->get(array(
            "url" => array("api", "notification"),
            "request" => array("area" => "areaguid"),
        ));
    }

    public function testSearchMessage() {
        $index = $this->getMockBuilder("\TMT\api\\notification\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $notifAcc = $this->getMockBuilder("\TMT\accessor\Notification")
            ->disableOriginalConstructor()
            ->setMethods(array("search"))
            ->getMock();

        $notifications = array(
            new \TMT\model\Notification((object) array("guid" => "guid1", "timestamp" => null, "message" => "A message", "type" => "type1", "area" => "areaguid")),
            new \TMT\model\Notification((object) array("guid" => "guid2", "timestamp" => null, "message" => "Another message", "type" => "type2", "area" => "areaguid")),
        );

        $notifAcc->expects($this->once())
            ->method("search")
            ->with(array("message" => "mes"))
            ->willReturn($notifications);
        $index->method("getAccessor")
            ->willReturn($notifAcc);

        $notifications = array(
            (object) array("guid" => "guid1", "timestamp" => null, "message" => "A message", "type" => "type1", "area" => "areaguid"),
            (object) array("guid" => "guid2", "timestamp" => null, "message" => "Another message", "type" => "type2", "area" => "areaguid"),
        );

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $notifications)));
        $index->get(array(
            "url" => array("api", "notification"),
            "request" => array("message" => "mes"),
        ));
    }

    public function testSearchNoParameters() {
        $index = $this->getMockBuilder("\TMT\api\\notification\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $notifAcc = $this->getMockBuilder("\TMT\accessor\Notification")
            ->disableOriginalConstructor()
            ->setMethods(array("search"))
            ->getMock();

        $notifAcc->expects($this->never())
            ->method("search");
        $index->method("getAccessor")
            ->willReturn($notifAcc);

        $this->expectOutputString(json_encode(array("status" => "ERROR", "message" => "Some search parameters must be set ('message', 'type', 'area', 'startDate', or 'endDate')")));
        $index->get(array(
            "url" => array("api", "notification"),
        ));
    }
}
