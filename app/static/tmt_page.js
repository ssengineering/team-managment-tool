/**
 * @file tmt_page.js
 * @brief Define an empty angular app for pages in the Team Management Tool. This file should
 *  be included by any angular applications.
 *
 * @version 1.0
 * @date 2015-06-08
 */
(function(){
	var app = angular.module('tmt_page', []);

	app.factory('tmtPostHeaderOverride', function($http) {
		$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
		$http.defaults.headers.put["Content-Type"] = "application/x-www-form-urlencoded";
	});
})();

