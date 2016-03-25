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

	// Configuration constant for the CRUD app
	app.factory('config', [function(){
		return {
			// Name of the application -- this will appear near the top of the page
			name : 'Major Incident Managers',
			// Description/instructions for the app 
			description: "This page is used to update the list of Major Incident Managers",
			// URL for the API endpoint used for CRUD operations. The API must be truly RESTful 
			base_url : '/api/mim',
			// URL for the API endpoint used for CRUD operations. The API must be truly RESTful 
			unique_keys : ['netID'],
			// Number of items per page
			page_size : 100,
			// Boolean true if inserts are disabled
			no_inserts : false,
			// Boolean true if deletes are disabled
			no_deletes : false,
			// Definitions of the columns to be displayed
			columns : [{
				no_updates : true,
				title : "Name",
				key : "netID",
				type: "employee",
				required : true
			}]
		};
	}]);

})();
