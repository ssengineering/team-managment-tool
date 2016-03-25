<?php
namespace TMT\accessor;

class RightEmail extends MysqlAccessor {

	/**
	 * Get template email for requesting/removing a right by the right ID.
	 *
	 * 	@param int $id - ID of the Right to retrieve the email template for
	 * 	@param bool $type - true if requesting a right, false if removing
	 *
	 * 	@return email model
	 */
	public function getByRight($id, $type)
	{
		$body = $type ? 'add_body' : 'del_body';
		$title = $type ? 'add_title' : 'del_title';
		$queryStr = "SELECT `address` AS `recipients`, `cc`, `$body` AS `message`,
		   	`$title` AS `subject` FROM `employeeRightsEmails` WHERE `rightID`=:id";
		$queryParams = array(":id" => $id);
		$query = $this->pdo->prepare($queryStr);
		$query->execute($queryParams);
		if ($email = $query->fetch(\PDO::FETCH_ASSOC)){
			$email['bcc'] = null;
			return new \TMT\model\Email($email);	
		}
		else 
			return new \TMT\model\Email();	
	}

}
?>
