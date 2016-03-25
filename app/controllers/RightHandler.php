<?php

namespace TMT\controller;

class RightHandler extends \TMT\Controller {

	// Accessors
	private $rightAcc;
	private $rightStatusAcc;
	private $rightEmailAcc;
	private $employeeAcc;

	/**
	 * Initialize accessots
	 */
	public function __construct() {
		parent::__construct();
		$this->rightAcc = new \TMT\accessor\Right();
		$this->rightStatusAcc = new \TMT\accessor\RightStatus();
		$this->rightEmailAcc = new \TMT\accessor\RightEmail();
		$this->employeeAcc = new \TMT\accessor\Employee();
	}

	/**
	 * Revoke all of an employee's rights and send emails to the appropriate groups.
	 * 	NOTE: Permissions should be checked prior to calling this function.
	 *
	 * @return 
	 * 	{
	 * 		"sent" => array(right models),
	 * 		"failed" => array(right models),
	 * 		"manual" => array(right models)
	 * 	}
	 */
	public function revokeAll($netId, $manager) {
		$rightStatuses = $this->rightStatusAcc->revokeAll($netId, $manager);
		$employee = $this->employeeAcc->get($netId);
		$results = array("sent" => array(), "failed" => array(), "manual" => array());
		foreach($rightStatuses as $rightStatus) {
			$right = $this->rightAcc->get($rightStatus->rightID);
			if (!($right->ID)){
				continue;
			}
			if ($right->rightType == "EMAIL") {
				$email = $this->rightEmailAcc->getByRight($right->ID, false);
				$email->message .= "\n\n\tName: $employee->firstName $employee->lastName"
					."\n\n\tNet ID: $employee->netID"
					."\n\n\tBYU ID: $employee->byuIDnumber";
				try {
					$emailCtrl = new \TMT\controller\EmailHandler($email);
				} catch (\TMT\exception\EmailException $e) {
					$results['failed'][] = $right;
					continue;
				}
				if ($emailCtrl->sendEmail()) {
					$results["sent"][] = $right;
				} else {
					$results["failed"][] = $right;
				}
			} else {
				$results["manual"][] = $right;
			}
		}
		return $results;
	}

}
