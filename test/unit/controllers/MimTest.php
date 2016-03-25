<?php

namespace TMT\api\mim;

/**
 * Unit tests for the Mim controller class
 */
class MimTest extends \PHPUnit_Framework_TestCase {

    protected $mimCtrl;
    protected $emptyParams;

    /**
     * @before
     */
    public function setUpAccessor() {
        $this->mimCtrl = $this->getMock('\TMT\api\mim\index', array('checkPermission'));
        $this->emptyParams = array("url" => array(), "request" => array());
    }

    /**
     * @covers index::get
     */
    public function testGet() {
        $this->mimCtrl = $this->getMock('\TMT\api\mim\index', array('can'));
        $expected = array("status" => "OK", "data" => array(array("netID" => "netId",
            "firstName" => "First", "lastName" => "Last")));
        ob_start();
        $this->mimCtrl->get($this->emptyParams);
        $output = ob_get_contents();
        ob_end_clean();
        $result = json_decode($output, true);
        unset($result["data"][0]["guid"]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers index::post
     * @covers index::delete
     * @covers index::get
     */
    public function testPostDelete() {
        $this->mimCtrl = $this->getMock('\TMT\api\mim\index', array('can'));
        $this->mimCtrl->method('can')->willReturn(true);
        $output1 = array("status" => "OK", "data" => array("netID" => "other",
            "firstName" => "Other", "lastName" => "Person"));
        $output2 = array("status" => "OK", "data" => array(array("netID" => "netId",
            "firstName" => "First", "lastName" => "Last"), array("netID" => "other",
            "firstName" => "Other", "lastName" => "Person")));
        $output3 = array("status" => "OK", "data" => array("netID" => null,
            "firstName" => null, "lastName" => null));
        $output4 = array("status" => "OK", "data" => array(array("netID" => "netId",
            "firstName" => "First", "lastName" => "Last")));
        $input = array("url" => array("api", "mim", "other"),
            "request" => array("netID" => "other"));
        $this->expectOutputString(json_encode($output1) . json_encode($output2) .
            json_encode($output3) . json_encode($output4));
        ob_start();
        $this->mimCtrl->post($input);
        $output = ob_get_contents();
        ob_end_clean();
        $result = json_decode($output, true);
        unset($result["data"]["guid"]);
        echo json_encode($result);

        ob_start();
        $this->mimCtrl->get($this->emptyParams);
        $output = ob_get_contents();
        ob_end_clean();
        $result = json_decode($output, true);
        unset($result["data"][0]["guid"]);
        unset($result["data"][1]["guid"]);
        echo json_encode($result);

        ob_start();
        $this->mimCtrl->delete($input);
        $output = ob_get_contents();
        ob_end_clean();
        $result = json_decode($output, true);
        unset($result["data"]["guid"]);
        echo json_encode($result);

        ob_start();
        $this->mimCtrl->get($this->emptyParams);
        $output = ob_get_contents();
        ob_end_clean();
        $result = json_decode($output, true);
        unset($result["data"][0]["guid"]);
        echo json_encode($result);
    }

    /**
     * @covers index::insert
     * @covers index::delete
     */
    public function testPermission() {
        $this->mimCtrl = $this->getMock('\TMT\api\mim\index', array('can'));
        $this->mimCtrl->method('can')->willReturn(false);
        $output = json_encode(array("status" => "ERROR", "message" => "You do not have permissions to edit the list of Major Incident Managers"));
        $this->expectOutputString($output . $output);
        $input = array("url" => array("api", "mim", "other"),
            "request" => array("netID" => "other"));
        $this->mimCtrl->post($input);
        $this->mimCtrl->delete($input);
    }

}
?>
