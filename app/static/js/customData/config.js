/**
 * @file config.js
 * @brief This is the angular config file for the Custom Data Fields CRUD app.
 *
 * @version 1.0
 * @date 2015-07-13
 */
(function(){
	// Get instance of angular app
	var app = angular.module('tmt_page');

	// Configuration constant for the CRUD app
	app.factory('config', [function(){
		return {
			// Name of the application -- this will appear near the top of the page
			name : 'Custom Data Fields',
			// Description/instructions for the app 
			description: "This page is used to update custom fields for employee information in your area.",
			// URL for the API endpoint used for CRUD operations. The API must be truly RESTful 
			base_url : '/api/customData/'+area,
			// URL for the API endpoint used for CRUD operations. The API must be truly RESTful 
			unique_key : 'field',
			// Number of items per page
			page_size : 10,
			// Set this flag if the crud_table should not use the default reload function
			override_reload : true,
			// Boolean true if inserts are disabled
			no_inserts : false,
			// Boolean true if deletes are disabled
			no_deletes : false,
			// Definitions of the columns to be displayed
			columns : [{
				title : "Field Name",
				key : "field",
				type: "text",
				required : true
			}]
		};
	}]);


})();
