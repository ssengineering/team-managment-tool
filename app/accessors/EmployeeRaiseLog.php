<?php

namespace TMT\accessor;

class EmployeeRaiseLog extends MysqlAccessor {

	/**
	 * Retrieves a Raise by index
	 */
	public function get($index) {
		$query = $this->pdo->prepare("SELECT * FROM `employeeRaiseLog` WHERE
			`index`=:index");
		$query->execute(array(':index' => $index));
		if($raise = $query->fetch(\PDO::FETCH_OBJ)) {
			return new \TMT\model\Raise($raise);
		} else {
			return new \TMT\model\Raise();
		}
	}

	/**
	 * Retrieves the most recent raise for the owner of $netID
	 */
	public function getCurrent($netID) {
		$query = $this->pdo->prepare("SELECT * FROM `employeeRaiseLog` WHERE
			`netID`=:netID ORDER BY `index` DESC LIMIT 1");
		$query->execute(array(':netID' => $netID));
		if($raise = $query->fetch(\PDO::FETCH_OBJ)) {
			return new \TMT\model\Raise($raise);
		} else {
			return new \TMT\model\Raise();
		}
	}

	/**
	 * Retrieves an array of all raises submitted for the owner of $netID
	 */
	public function getAll($netID) {
		$query = $this->pdo->prepare("SELECT * FROM `employeeRaiseLog` WHERE
			`netID`=:netID ORDER BY `index` DESC");
		$query->execute(array(':netID' => $netID));
		$raises = array();
		while($raise = $query->fetch()) {
			$raises[] = new \TMT\model\Raise($raise);
		}
		return $raises;
	}

	/**
	 * Insert a new Raise into the database
	 */
	public function insert($raise) {
		$query = $this->pdo->prepare("INSERT INTO `employeeRaiseLog` 
			(`netID`,`raise`,`newWage`,`submitter`,`date`,`comments`,`isSubmitted`, `guid`) 
			VALUES (:netID,:raise,:newWage,:submitter,:date,:comments,:isSubmitted,:guid)");
		$query->execute(array(
			':netID' => $raise->netID,
			':raise' => $raise->raise,
			':newWage' => $raise->newWage,
			':submitter' => $raise->submitter,
			':date' => $raise->date,
			':comments' => $raise->comments,
			':isSubmitted' => $raise->isSubmitted,
			':guid' => $this->newGuid()
		));
		return $this->get($this->pdo->lastInsertId());
	}

}
?>
