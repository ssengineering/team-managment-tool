<?php

namespace TMT;

/**
 * API Controller base class
 *
 * All API Controllers will extend off of this class
 */
class APIController extends \TMT\App {

	/**
	 * Send JSON response to the client
	 *
	 * @param $data   mixed The data to return to the client
	 * @param $status string The status to set, defaults to "OK"
	 */
	protected function respond($data, $status = "OK") {
		$response = array(
			'status' => $status,
			'data'   => $data
		);

		echo(json_encode($response));
	}

	/**
	 * Force the user to be authenticated
	 *
	 * If the user is not authenticated, an error is returned to the client
	 */
	protected function requireAuthentication() {
		if (!$this->authenticated) {
			$this->error("Unauthorized");
			exit();
		}
	}

	/**
	* Send JSON error message to the client
	*
	* @param $message string The error message
	*/
	public function error($message = "", $status = 403) {
		$this->renderError($message, $status, true);
	}

	/**
	* Used for when a controller given controller does not exist
	*   sends a JSON error message to the client
	*/
	public function fallback() {
		$this->renderError("This route does not exist", 404, true);
	}
}
