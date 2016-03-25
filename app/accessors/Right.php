<?php
namespace TMT\accessor;

class Right extends MysqlAccessor {

	/**
	 * Get a a right by ID.
	 *
	 * 	@return Right model
	 */
	public function get($id)
	{
		$queryStr = "SELECT * FROM `employeeRights` WHERE `ID`=:id";
		$queryParams = array(":id" => $id);
		$query = $this->pdo->prepare($queryStr);
		$query->execute($queryParams);
		if ($right = $query->fetch(\PDO::FETCH_ASSOC))
			return new \TMT\model\Right($right);	
		else 
			return new \TMT\model\Right();
	}

	public function insert($right)
	{
		$queryStr = "INSERT INTO `employeeRights`(
				`rightName`, `description`, 
				`rightType`, `rightLevel`, `area`,`guid`
			) 
			VALUES 
				(
					:rightName, :description, :rightType, 
					:rightLevel, :area, :guid
				)";
		$queryParams = array(
			":rightName" => $right->rightName,
			":description" => $right->description,
			":rightLevel" => $right->rightLevel,
			":rightType" => $right->rightType,
			":area" => $right->area,
			":guid" => $this->newGuid()
		);
		$query = $this->pdo->prepare($queryStr);
		$query->execute($queryParams);
		return $this->get($this->pdo->lastInsertId());
	}

	public function update($right)
	{
		$queryStr = "UPDATE `employeeRights` SET
				`rightName`=:rightName, `description`=:description, 
				`rightType`=:rightType, `rightLevel`=:rightLevel, `area`=:area
			WHERE `ID`=:id";
		$queryParams = array(
			":id" => $right->ID,
			":rightName" => $right->rightName,
			":description" => $right->description,
			":rightLevel" => $right->rightLevel,
			":rightType" => $right->rightType,
			":area" => $right->area
		);
		$query = $this->pdo->prepare($queryStr);
		$query->execute($queryParams);
		return $this->get($right->ID);
	}

	public function delete($right)
	{
		$queryStr = "DELETE FROM `employeeRights` WHERE `ID`=:id";
		$queryParams = array(
			":id" => is_object($right) ? $right->ID : $right,
		);
		$query = $this->pdo->prepare($queryStr);
		$query->execute($queryParams);
		return $this->get($right->ID);
	}

}
?>
