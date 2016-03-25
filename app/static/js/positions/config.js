/**
 * @file config.js
 * @brief This is the angular configuration for the employee positions CRUD app.
 *
 * @version 1.0
 * @date 2015-06-04
 */
(function(){
	// Get instance of angular app
	var app = angular.module('tmt_page');

	// Configuration constant for the CRUD app
	app.factory('config', [function () {
		return {
		// Name of the application -- this will appear near the top of the page
		name : 'Employee Positions',
		// Description/instructions for the app 
		description: "This app is used to update the available positions for employees in your area."
		+"Note that positions may only be deleted when no employees are assigned to the position.",
		// URL for the API endpoint used for CRUD operations. The API must be truly RESTful 
		base_url : '/api/position',
		// Unique key to be appended to the URL for updates and deletes
		unique_key : 'positionId',
		// Number of items per page
		page_size : 10,
		// Boolean true if inserts are disabled
		no_inserts : false,
		// Boolean true if deletes are disabled
		no_deletes : false,
		// Definitions of the columns to be displayed
		columns : [{
			title : "Title",
			key : "positionName",
			type: "text",
			required : true
		},{
			title : "Description",
			key : "positionDescription",
			type: "text",
			required : true
		}]
	};
	}]);


})();
