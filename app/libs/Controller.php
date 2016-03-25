<?php

namespace TMT;

/** 
 * Base controller class
 *
 * All controllers will extend this class
 */
class Controller {

	/**
	 * Default Constructor
	 */
	public function __construct() {}

	/**
	 * Retrieves the current environment.
	 * 	Checks to see if an environment cookie is set. If not, checks
	 * 	for the server's default configuration.
	 *
	 * @return string DEV, STAGE or PROD
	 */
	public function getEnvironment() {
		$environment = isset($_COOKIE['environment']) ? $_COOKIE['environment'] : $this->getConfigValue("ENVIRONMENT");
		switch($environment) {
		case 0:
			$environment = "DEV";
			break;
		case 1:
			$environment = "STAGE";
			break;
		case 2:
			$environment = "PROD";
			break;
		default:
		}
		return $environment;
	}

	/**
	 * Retrieves a configuration value
	 *
	 * NOTE: This function is used as an abstraction. For now,
	 *   we just need it to get environment variables, but
	 *   in the future it could be used to retrieve values
	 *   from a key/value store, or other source.
	 *
	 * @param $value string The configuration value to get
	 *
	 * @return string The configuration value
	 */
	public function getConfigValue($value) {
		return getenv($value);
	}

	/**
	 * Returns a controller of the given type
	 *
	 * @param $name   string The name of the controller (excluding namespace)
	 * @param $params mixed  Any data that needs to be passed in to the constructor
	 *   of the controller
	 *
	 * @return \TMT\controller\<Controller Type> 
	 */
	public function getController($name, $params = null) {
		$class = "\\TMT\\controller\\".$name;
		return new $class($params);
	}

	/**
	 * Returns a model of the given type
	 *
	 * @param $name   string The name of the model (excluding namespace)
	 * @param $params mixed  Any data that needs to be passed in to the constructor
	 *   of the model
	 *
	 * @return \TMT\model\<Model Type> 
	 */
	public function getModel($name, $params = null) {
		$class = "\\TMT\\model\\".$name;
		return new $class($params);
	}

	/**
	 * Returns an accessor of the given type
	 *
	 * @param $name   string The name of the accessor (excluding namespace)
	 *
	 * @return \TMT\accessor\<Accessor Type> 
	 */
	public function getAccessor($name) {
		$class = "\\TMT\\accessor\\".$name;
		return new $class();
	}
}
