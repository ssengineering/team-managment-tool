<?php

namespace TMT\accessor;

/**
 * Base Accessor class for Mysql
 *
 * All tmt accessors for Mysql will be extended from this class
 */
class MysqlAccessor extends Accessor {

	/**
	 * PDO object used to make queries to the database.
	 */
	protected  $pdo;

	/**
	 * Constructor for the Mysql accessor class
	 *
	 * Pulls the following environment variables to connect
	 * to the database:
	 *   1. MYSQL_HOST
	 *   2. MYSQL_USER
	 *   3. MYSQL_PASS
	 *   4. MYSQL_DB
	 *
	 * This constructor connects to the database
	 *
	 * @throws PDOException when it fails to connect to the database
	 */
	public function __construct($db = null) {
		$this->setGuidCreator(new GuidCreator());
		if($db == null) {
			$host = getenv('DB_HOST');
			$user = getenv('DB_USER');
			$pass = getenv('DB_PASS');
			$db   = getenv('DB_NAME');
			$connectStr = "mysql:dbname=".$db.";host=".$host.";port=3306";
			$options = array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ);
			$conn = new \PDO($connectStr, $user, $pass, $options);
			$this->pdo = $conn;
		} else {
			$this->pdo = $db;
		}
	}

	/**
	 * Sets the database connection
	 *
	 * @param $db Object A database connection
	 */
	public function setDB($db) {
		$this->pdo = $db;
	}
}
?>
