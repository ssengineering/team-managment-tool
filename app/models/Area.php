<?php

namespace TMT\model;

/**
 * The model class for Areas
 */
class Area extends Model {

	public $guid;

	public $ID;

	public $area;

	public $longName;

	public $startDay;

	public $endDay;
	
	public $startTime;

	public $endTime;

	public $hourSize;

	public $homePage;

	public $postSchedulesByDefault;

	public $canEmployeesEditWeeklySchedule;

	/**
	 * Constructor for the model which takes an object such as would
	 *  be returned by a query to the database through the PDO connector.
	 *  This allows the result from a query to be passed directly to this
	 *  constructor. 
	 */
	public function __construct($area = null) {
		if($area == null)
			return;
		if (is_array($area)){
			$area = (object) $area;
		}
		$this->guid = \property_exists($area, 'guid') ? $area->guid : null;
		$this->ID = \property_exists($area, 'ID') ? (int)$area->ID : null;
		$this->area = \property_exists($area, 'area') ? $area->area : null;
		$this->longName = \property_exists($area, 'longName') ? $area->longName : null;
		$this->startDay = \property_exists($area, 'startDay') ? (int)$area->startDay : null;
		$this->endDay = \property_exists($area, 'endDay') ? (int)$area->endDay : null;
		$this->startTime = \property_exists($area, 'startTime') ? (float)$area->startTime : null;
		$this->endTime = \property_exists($area, 'endTime') ? (float)$area->endTime : null;
		$this->hourSize = \property_exists($area, 'hourSize') ? (float)$area->hourSize : null;
		$this->homePage = \property_exists($area, 'homePage') ? $area->homePage : null;
		$this->postSchedulesByDefault = \property_exists($area, 'postSchedulesByDefault') ? 
			(int)$area->postSchedulesByDefault : null;
		$this->canEmployeesEditWeeklySchedule = \property_exists($area, 'canEmployeesEditWeeklySchedule') ? 
			(int)$area->canEmployeesEditWeeklySchedule : null;
	}
}

?>
