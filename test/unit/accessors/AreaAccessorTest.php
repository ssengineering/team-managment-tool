<?php

namespace TMT\accessor;

/**
 * Unit tests for the base Area data accessor class
 *
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class AreaAccessorTest extends \PHPUnit_Framework_TestCase {

	protected $areaAcc;

	/**
	 * @before
	 */
	public function setUpAccessor()
	{
		$this->areaAcc = new AreaAccessor();
	}

	/**
	 * @covers AreaAccessor::get
	 * @covers AreaAccessor::getByShortName
	 * @covers AreaAccessor::getAll
	 */
	public function testGet() 
	{
		// Expectations
		$expected_empty = new \TMT\model\Area();
		$expected_area_1 = new \TMT\model\Area(array(
			"ID" => "1",
			"area" => "TEST1",
			"longName" => "Test Area 1",
			"startDay" => 0,
			"endDay" => 6,
			"startTime" => "0.0",
			"endTime" => "23.0",
			"hourSize" => "1.0",
			"homePage" => "whiteboard",
			"postSchedulesByDefault" => "1",
			"canEmployeesEditWeeklySchedule" => "1",
			"guid" => "a6cec04d-8629-44a8-b8c0-1a61c40c64fb"
		));
		$expected_area_2 = new \TMT\model\Area(array(
			"ID" => "2",
			"area" => "TEST2",
			"longName" => "Test Area 2",
			"startDay" => 2,
			"endDay" => 6,
			"startTime" => "0.0",
			"endTime" => "23.0",
			"hourSize" => "1.0",
			"homePage" => "whiteboard",
			"postSchedulesByDefault" => "1",
			"canEmployeesEditWeeklySchedule" => "1",
			"guid" => "12ba0b6c-57ef-4d98-fc10-c367ad10b8c7"
		));
		// Exists
		$area = $this->areaAcc->get(1);
		$this->assertEquals($area, $expected_area_1);
		$area = $this->areaAcc->get(2);
		$this->assertEquals($area, $expected_area_2);
		$area = $this->areaAcc->getByShortName("TEST1");
		$this->assertEquals($area, $expected_area_1);
		$area = $this->areaAcc->getByShortName("TEST2");
		$this->assertEquals($area, $expected_area_2);
		// Does not Exist
		$area = $this->areaAcc->get(0);
		$this->assertEquals($area, $expected_empty);
		$area = $this->areaAcc->getByShortName("NOT_REAL");
		$this->assertEquals($area, $expected_empty);

		// getAll
		$areas = $this->areaAcc->getAll();
		$this->assertEquals($areas, array($expected_area_1, $expected_area_2));
		// Limit to areas visible to user
		$areas = $this->areaAcc->getAll("netId");
		$this->assertEquals($areas, array($expected_area_1));
	}

	/**
	 * 	@depends testGet
	 *	@covers AreaAccessor::save
	 */
	public function testSave()
	{
		// Expectations
		$expected_area_edited = new \TMT\model\Area(array(
			"ID" => "1",
			"area" => "TEST_EDITED",
			"longName" => "Test Area 1",
			"startDay" => 0,
			"endDay" => 6,
			"startTime" => "0.0",
			"endTime" => "23.0",
			"hourSize" => "1.0",
			"homePage" => "whiteboard",
			"postSchedulesByDefault" => "1",
			"canEmployeesEditWeeklySchedule" => "1",
			"guid" => "a6cec04d-8629-44a8-b8c0-1a61c40c64fb"
		));
		$expected_area_inserted = new \TMT\model\Area(array(
			"ID" => "3",
			"area" => "TEST3",
			"longName" => "Test Area 3",
			"startDay" => 0,
			"endDay" => 6,
			"startTime" => "0.0",
			"endTime" => "23.0",
			"hourSize" => "1.0",
			"homePage" => "whiteboard",
			"postSchedulesByDefault" => "1",
			"canEmployeesEditWeeklySchedule" => "1",
			"guid" => null
		));

		// Update
		$area = $this->areaAcc->get(1);
		$area->area = "TEST_EDITED";
		$edited = $this->areaAcc->save($area);
		$this->assertEquals($edited, $expected_area_edited);

		// Insert
		$area = new \TMT\model\Area($expected_area_inserted);
		$area->ID = null;
		$inserted = $this->areaAcc->save($area);
		// ignore guid
		$inserted->guid = null;
		$this->assertEquals($inserted, $expected_area_inserted);
	}
}
?>
