<?php

namespace TMT\accessor;

class EmployeeTermination extends MysqlAccessor {

    /**
     * Retrieves an employee's record by netID
     *
     * @param $netID string The terminated employee's netID
     *
     * @return object An employee model object
     */
    public function get($netID) {
        $query = $this->pdo->prepare("SELECT * FROM employeeTerminationDetails WHERE netID=:netID");
        $query->execute(array(':netID' => $netID));
        return new \TMT\model\EmployeeTermination($query->fetch());
    }

    /**
     * Handles inserting entries into the employeeTerminationDetails table
     *
     * @param object A position model object
     */
    public function save($employee) {
        $query = $this->pdo->prepare("INSERT INTO employeeTerminationDetails (reasons,attendance,attitude,performance,netID,submitter,area,rehirable,guid) VALUES (:reasons,:attendance,:attitude,:performance,:netID,:submitter,:area,:rehirable,:guid)");
        $query->execute(array(
            ':reasons'     => $employee->reasons,
            ':attendance'  => $employee->attendance,
            ':attitude'    => $employee->attitude,
            ':performance' => $employee->performance,
            ':netID'       => $employee->netID,
            ':submitter'   => $employee->submitter,
            ':area'        => $employee->area,
            ':rehirable'   => $employee->rehirable,
			':guid'        => $this->newGuid()
        ));
    }
}
