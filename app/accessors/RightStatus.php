<?php
namespace TMT\accessor;

class RightStatus extends MysqlAccessor {


	/**
	 * Get a list of all RightStatuses in the database for an employee.
	 * 	Allows optionally ignoring rights with a certain status
	 *
	 * 	@param $netId - Net ID of employee whose rights are being checked
	 * 	@param $status - OPTIONAL param to ignore rows with a specific rightStatus
	 *
	 * 	@return array of RightStatuses being being revoked
	 */
	public function getAll($netId, $status = null)
	{
		$queryStr = "SELECT * FROM `employeeRightsStatus` WHERE `netID`=:netId";
		$queryParams = array(":netId" => $netId);
		if ($status) {
			$queryStr .= " AND `rightStatus`!=:status"; 
			$queryParams[':status'] = $status;
		}
		$query = $this->pdo->prepare($queryStr);
		$query->execute($queryParams);
		$results = array();
		while ($right = $query->fetch(\PDO::FETCH_ASSOC))
			$results[] = new \TMT\model\RightStatus($right);	
		return $results;
	}

	/**
	 * Revokes all rights for an employee in the database.
	 * 	NOTE: This only updates the database. The Rights controller should be used 
	 * 	to send emails when revoking all rights.
	 *
	 * 	@param $netId - Net ID of employee getting rights revoked
	 * 	@param $manager - Net ID of manager revoking the rights
	 *
	 * 	@return array of RightStatuses being being revoked
	 */
	public function revokeAll($netId, $manager)
	{
		$date = date("Y-m-d");
		$response = $this->getAll($netId, 3);
		$revokeQuery = $this->pdo->prepare("UPDATE `employeeRightsStatus` SET `rightStatus`=3,
			`removedBy`=:manager, `removedDate`=:day WHERE `rightStatus`!=3 AND `netID`=:employee");
		$revokeQuery->execute(array(
			':manager' => $manager, 
			':day' => date('Y-m-d'), 
			':employee' => $netId));
		$date = date('Y-m-d');
		foreach ($response as $rs) {
			$rs->rightStatus = 3;
			$rs->removedBy = $manager;
			$rs->removedDate = $date;
		}
		return $response;

	}
}
?>
