<?php

namespace TMT\model;

/**
 * Unit tests for the Area model class
 */
class AreaTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers Area::__construct
	 * @covers Area::jsonSerialize
	 */
	public function testConstruct() 
	{
		$area = new Area();
		$expected = '{"guid":null,"ID":null,"area":null,"longName":null,"startDay":null,"endDay":null,"startTime":null,"endTime":null,"hourSize":null,"homePage":null,"postSchedulesByDefault":null,"canEmployeesEditWeeklySchedule":null}';
		$this->assertEquals(json_encode($area), $expected);
		$area->ID = 5;	
		$area->area = "TEST";	
		$area->longName = "Testing";	
		$area->guid = "11111111-1111-1111-1111-111111111111";
		$expected = '{"guid":"11111111-1111-1111-1111-111111111111","ID":5,"area":"TEST","longName":"Testing","startDay":null,"endDay":null,"startTime":null,"endTime":null,"hourSize":null,"homePage":null,"postSchedulesByDefault":null,"canEmployeesEditWeeklySchedule":null}';
		$this->assertEquals(json_encode($area), $expected);
		$area_copy = new Area($area);
		$this->assertEquals($area_copy, $area);
	}

}
?>
