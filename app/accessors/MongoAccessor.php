<?php

namespace TMT\accessor;

/**
 * Base Accessor class for Mongodb
 *
 * All tmt accessors for Mongodb will be extended from this class
 */
class MongoAccessor extends Accessor {

	/**
	 * A Mongo client
	 */
	protected $client;

	/**
	 * The current database
	 * default: tmt, which is set in the constructor
	 */
	protected $db;

	/**
	 * Constructor for the Mongo accessor class
	 *
	 * Pulls the following environment variables to connect
	 * to the database:
	 *   1. MONGO_HOST (required)
	 *   2. MONGO_USER (optional)
	 *   3. MONGO_PASS (optional)
	 *
	 * If the user or password are ommitted, it is assumed it
	 * is in a dev environment and tries to connect to the 
	 * unauthenticated development database.
	 *
	 * It sets the database by default to the tmt database,
	 * which can be changed by the setDatabase function if necessary.
	 *
	 * @throws MongoConnectionException on failure to connect
	 */
	public function __construct() {
		$this->setGuidCreator(new GuidCreator());
		$connectStr = "mongodb://";
		if(getenv('MONGO_USER') !== false && getenv('MONGO_PASS') !== false) {
			$connectStr .= getenv('MONGO_USER').':'.getenv('MONGO_PASS').'@';
		}
		$connectStr .= getenv('MONGO_HOST');
		$this->client = new \MongoClient($connectStr);
		$this->db = $this->client->selectDB(getenv('MONGO_DB_NAME'));
	}

	/**
	 * Sets the database to the database with the given name
	 *
	 * NOTE: The constructor sets the database as tmt by default,
	 * so this function is only needed if switching to another database
	 *
	 * @param $dbName string The name of the database
	 *
	 * @throws Exception when no database exists with the given name
	 */
	public function setDatabase($dbName) {
		$dbs = $this->client->listDBs();
		foreach ($dbs['databases'] as $db) {
			if($db['name'] == $dbName) {
				$this->db = $this->client->selectDB($dbName);
				return;
			}
		}
		throw new \Exception("No database exists that is named ".$dbName);
	}

	/**
	 * Returns the name of the currently selected database
	 *
	 * @return string The database's name
	 */
	public function getDatabaseName() {
		return $this->db->__toString();
	}

	/**
	 * Returns a collection object from the current database
	 *
	 * @param $collection string The name of the collection to get
	 *
	 * @return MongoCollection A MongoCollection object corresponding to the collection asked for
	 *
	 * @throws Exception when no collection exists with the given name
	 */
	public function getCollection($collection) {
		$cols = $this->db->listCollections();
		foreach($cols as $col) {
			if($col->getName() == $collection) {
				return $col;
			}
		}
		throw new \Exception("No collection exists that is named ".$collection);
	}
}
?>
