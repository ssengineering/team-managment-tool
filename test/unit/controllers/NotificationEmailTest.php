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
class NotificationEmailTest extends \PHPUnit_Framework_TestCase {

    public function testGetByType() {
        $index = $this->getMockBuilder("\TMT\api\\notificationEmail\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $emailAcc = $this->getMockBuilder("\TMT\accessor\NotificationEmail")
            ->disableOriginalConstructor()
            ->setMethods(array("getByType"))
            ->getMock();

        $emails = array(
            new \TMT\model\NotificationEmail((object) array("area" => "areaguid", "email" => "email1@byu.edu", "guid" => "guid1", "type" => "type1")),
            new \TMT\model\NotificationEmail((object) array("area" => "areaguid", "email" => "email2@byu.edu", "guid" => "guid2", "type" => "type1")),
        );

        $emailAcc->expects($this->once())
            ->method("getByType")
            ->with("type1", "areaguid")
            ->willReturn($emails);
        $index->method("getAccessor")
            ->willReturn($emailAcc);

        $output = array(
            (object) array("guid" => "guid1", "email" => "email1@byu.edu", "type" => "type1", "area" => "areaguid"),
            (object) array("guid" => "guid2", "email" => "email2@byu.edu", "type" => "type1", "area" => "areaguid"),
        );

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $output)));
        $index->get(array(
            "url" => array("api", "notificationEmail"),
            "request" => array("type" => "type1", "area" => "areaguid"),
        ));
    }

    public function testGetByArea() {
        $index = $this->getMockBuilder("\TMT\api\\notificationEmail\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $emailAcc = $this->getMockBuilder("\TMT\accessor\NotificationEmail")
            ->disableOriginalConstructor()
            ->setMethods(array("getByArea"))
            ->getMock();

        $emails = array(
            new \TMT\model\NotificationEmail((object) array("area" => "areaguid", "email" => "email1@byu.edu", "guid" => "guid1", "type" => "type1")),
            new \TMT\model\NotificationEmail((object) array("area" => "areaguid", "email" => "email2@byu.edu", "guid" => "guid2", "type" => "type2")),
        );

        $emailAcc->expects($this->once())
            ->method("getByArea")
            ->with("areaguid")
            ->willReturn($emails);
        $index->method("getAccessor")
            ->willReturn($emailAcc);

        $output = array(
            (object) array("guid" => "guid1", "email" => "email1@byu.edu", "type" => "type1", "area" => "areaguid"),
            (object) array("guid" => "guid2", "email" => "email2@byu.edu", "type" => "type2", "area" => "areaguid"),
        );

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $output)));
        $index->get(array(
            "url" => array("api", "notificationType"),
            "request" => array("area" => "areaguid"),
        ));
    }

    public function testGet() {
        $index = $this->getMockBuilder("\TMT\api\\notificationEmail\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $emailAcc = $this->getMockBuilder("\TMT\accessor\NotificationEmail")
            ->disableOriginalConstructor()
            ->setMethods(array("get"))
            ->getMock();

        $emails = new \TMT\model\NotificationEmail((object) array("area" => "areaguid", "email" => "email1@byu.edu", "guid" => "guid1", "type" => "type1"));

        $emailAcc->expects($this->once())
            ->method("get")
            ->with("guid1")
            ->willReturn($emails);
        $index->method("getAccessor")
            ->willReturn($emailAcc);

        $output = (object) array("guid" => "guid1", "email" => "email1@byu.edu", "type" => "type1", "area" => "areaguid");

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => $output)));
        $index->get(array(
            "url" => array("api", "notificationType", "guid1"),
        ));
    }

    public function testPost() {
        $index = $this->getMockBuilder("\TMT\api\\notificationEmail\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $emailAcc = $this->getMockBuilder("\TMT\accessor\NotificationEmail")
            ->disableOriginalConstructor()
            ->setMethods(array("add"))
            ->getMock();

        $emailAcc->expects($this->once())
            ->method("add")
            ->with(new \TMT\model\NotificationEmail((object) array("guid" => null, "email" => "email@byu.edu", "type" => "type1", "area" => "areaguid")));
        $index->method("getAccessor")
            ->willReturn($emailAcc);

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => "success")));
        $index->post(array(
            "url" => array("api", "notificationType"),
            "request" => array("email" => "email@byu.edu", "type" => "type1", "area" => "areaguid"),
        ));
    }

    public function testDelete() {
        $index = $this->getMockBuilder("\TMT\api\\notificationEmail\index")
            ->disableOriginalConstructor()
            ->setMethods(array("getAccessor"))
            ->getMock();
        $emailAcc = $this->getMockBuilder("\TMT\accessor\NotificationEmail")
            ->disableOriginalConstructor()
            ->setMethods(array("delete"))
            ->getMock();

        $emailAcc->expects($this->once())
            ->method("delete")
            ->with("guid1");
        $index->method("getAccessor")
            ->willReturn($emailAcc);

        $this->expectOutputString(json_encode(array("status" => "OK", "data" => "success")));
        $index->delete(array(
            "url" => array("api", "notificationType", "guid1"),
        ));
    }
}
