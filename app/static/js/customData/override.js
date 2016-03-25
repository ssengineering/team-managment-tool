/**
 * @file config.js
 * @brief This controller modifies the API calls for the custom data fields CRUD app. This
 * is necessary because the custom data fields will occasionally contain spaces, so the values
 * cannot be included in the URL like in other APIs.
 *
 * @version 1.0
 * @date 2015-07-13
 */
(function(){
	// Get instance of angular app
	var app = angular.module('tmt_page');

	app.controller('Override', ['$http', 'items', 'error', 'preApiCalls', function($http, items, error, preApiCalls){
			// Overload the import function
			items.reload = function () {
				return {
					success : function (fn) {
						$http.get("/api/customData/"+area).success(function(data, status) {
							var data_adjusted = [];
							for (var i = 0; i < data.data.fields.length; i++) {
								data_adjusted.push({field: data.data.fields[i], old :data.data.fields[i]});
							}
							fn({status: status, data: data_adjusted}, status); 
						}).error(function(data, status) {
							error.messages.push(data.message);
						});
						return {error : function f(){}};
					}
				};
			};
			// Edit the add function
			preApiCalls.add = function (callback) {
				preApiCalls.item.old = preApiCalls.item.field;
				callback();
			};

			// Edit the update function
			preApiCalls.update = function (callback) {
				var updating = preApiCalls.item;
				$http.put("/api/customData/"+area, $.param(updating)).success(function(data, status) {
					updating.old = updating.field;
				}).error(function(data, status) {
					error.messages.push(data.message);
				});
			};

			// Override the delete function
			preApiCalls.delete = function (callback) {
				$("#delete-dialog").dialog({autoOpen: false, modal:true, width : $("#crud-table").width(),
                    position: {my: "left top", at: "left top", of: $("#add-item-btn")},
					buttons : [{
						text : "Cancel",
						click : function() {
							$(this).dialog("close");
						}
					},{
						text : "Confirm",
						click : function() {
							callback();
							$(this).dialog("close");
						}
					}]});
                $("#delete-dialog").dialog("open");
				
			}
	}]);

})();
