/**
 * @file config.js
 * @brief This is an example config file that can be used with the generic crud-table module.
 *   For instructions on how to use the generic crud-table, see the Wiki article.
 *
 * @version 1.0
 * @date 2015-06-04
 */
(function(){
	// Get instance of angular app
	var app = angular.module('tmt_page');

	// Configuration service for the CRUD app
	app.factory('config', [function () {
		return {
			// Name of the application -- this will appear near the top of the page
			name : 'Sample CRUD app',
			// Description/instructions for the app 
			description: "This is a template for applications that perform simple CRUD operations",
			// URL for the API endpoint used for CRUD operations. The API must be truly RESTful 
			base_url : '/api/CRUD_EXAMPLE',
			// URL for the API endpoint used for CRUD operations. The API must be truly RESTful 
			unique_key : 'id',
			// Some CRUD apps may require multiple unique keys for their API, in which case 
			// this should be set to an array of those keys
			unique_keys : undefined,
			// Number of items per page
			page_size : 10,
			// Boolean true if inserts are disabled
			no_inserts : false,
			// Boolean true if deletes are disabled
			no_deletes : false,
			// Definitions of the columns to be displayed
			columns : [{
				no_updates : true,
				title : "Employee",
				key : "netID",
				sort_key : "firstName",
				type: "employee",
				required : true
			},{
				title : "Nickname",
				key : "nickname",
				type: "text"
			},{
				title : "Problems",
				key : "problems",
				type: "number",
				required : true
			},{
				title : "Birthday",
				key : "birthday",
				type: "date",
				required : true
			},{
				title : "Awesome",
				key : "awesome",
				type: "checkbox"
			}]
		};
	}]);
})();
