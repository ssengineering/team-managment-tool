<?php

namespace TMT\accessor;

class Position extends MysqlAccessor {

    /**
     * Retrieves an employee by positionId
     *
     * @param $positionId string The employee's positionId
     *
     * @return object An employee model object
     */
    public function get($positionId) {
        $query = $this->pdo->prepare("SELECT * FROM positions WHERE positionId=:positionId AND deleted=0");
        $query->execute(array(':positionId' => $positionId));
        return new \TMT\model\Position($query->fetch());
    }

    /**
     * Retrieves an array of employees in a given employeeAreaq
     *
     * @param $employeeArea int The employeeArea number
     *
     * @return array(object) An array of employee model objects
     */
    public function getByArea($employeeArea) {
        $query = $this->pdo->prepare("SELECT * FROM positions WHERE employeeArea=:employeeArea AND deleted=0");
        $query->execute(array(':employeeArea' => $employeeArea));
        $positions = array();

        while ($position = $query->fetch()) {
            $positions[] = new \TMT\model\Position($position);
        }

        return $positions;
    }

    // Delete function
    public function delete($positionId) {
        $query = $this->pdo->prepare("UPDATE positions SET deleted=1 WHERE positionId=:positionId");
		$query->execute(array(':positionId' => $positionId));
		return $this->get($positionId);
    }

    /**
     * Handles both inserting and updating entries into the positions table
     *   It will insert if there is already an entry with the given positionId,
     *   otherwise, it will update the position.
     *
     * @param object A position model object
     */
    public function save($position) {
        // Test if this is a new position or an already existing one
        $query = $this->pdo->prepare("SELECT COUNT(positionId) FROM positions WHERE positionId=:positionId");
        $query->execute(array(':positionId' => $position->positionId));
        if($query->fetch(\PDO::FETCH_NUM)[0] > 0) {
            // Position was found, update
            $query2 = $this->pdo->prepare("UPDATE positions SET positionName=:positionName, positionDescription=:positionDescription, employeeArea=:employeeArea, deleted=:deleted WHERE positionId=:positionId");
            $query2->execute(array(
                ':positionId'          => $position->positionId,
                ':positionName'        => $position->positionName,
                ':positionDescription' => $position->positionDescription,
                ':employeeArea'        => $position->employeeArea,
                ':deleted'             => $position->deleted
			));
			return $this->get($position->positionId);
        } else {
            // No position found with this positionId, insert.
            $query2 = $this->pdo->prepare("INSERT INTO positions (positionName,positionDescription,employeeArea,deleted,guid) VALUES (:positionName,:positionDescription,:employeeArea,:deleted,:guid)");
            $query2->execute(array(
                ':positionName'        => $position->positionName,
                ':positionDescription' => $position->positionDescription,
                ':employeeArea'        => $position->employeeArea,
                ':deleted'             => $position->deleted,
				':guid'                => $this->newGuid()
            ));
			return $this->get($this->pdo->lastInsertId());
        }
    }
}
