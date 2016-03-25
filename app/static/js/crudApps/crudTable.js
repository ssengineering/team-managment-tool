(function(){
	var app = angular.module('tmt_page');
	/**
	 * @brief Filter used to allow pagination in the table
	 */
	app.filter('offset', function() {
		return function(input, start) {
			start = parseInt(start);
			return input.slice(start);
		}
	});

	/**
	 * @brief Modular table for CRUD operations on simple tables.
	 * Add to an html/php page with the tag: '<crud-table></crud-table>'
	 */
	app.directive('crudTable', ['$http', '$filter', function($http, $filter){
		return {
			restirct: 'E',
		templateUrl: '/crudApps/table',
		controller: function($http, $element, $scope, items, config, preApiCalls, error){
			$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
			$http.defaults.headers.put["Content-Type"] = "application/x-www-form-urlencoded";
			var table = this;
			var live = true;
			$scope.config = config;
			// Read in the configuration module
			table.no_deletes = config.no_deletes;
			table.no_inserts = config.no_inserts;
			table.name = config.name;
			table.description = config.description;
			table.base_url = config.base_url;
			table.unique_keys = (config.unique_keys && config.unique_keys.length > 0) ? 
				config.unique_keys : [config.unique_key];
			table.columns = config.columns;
			table.validateDelete = config.validateDelete;
			table.page_size = config.page_size;
			// Set up other variables
			table.sort_field= table.columns[0].key;
			table.items = items;
			table.new_item = {};
			table.page = 0;
			table.search = {};
			table.sort_order = false;
			table.pages = [];
			table.error = error;
			table.add_error = null;

			table.updatePages = function(){
				var visible_items = $filter('filter')(items.list, table.search);
				var num_pages = Math.ceil(visible_items.length/table.page_size);
				var arr = [];
				for (var i = 0; i < num_pages; i++){
					arr.push(i);
				}
				table.pages = arr;
				if (table.page >= num_pages){
					table.page = num_pages -1;
					table.page = table.page > -1 ? table.page : 0;
				}
			};

			table.onItemBlur = function(params, value) {
					params.item.editing[params.column.key]=false; 
					if (params.valid)
					   	table.updateItem(params.item);
			};

			table.loadData = function(){
				items.reload()
				.success(function(data, status){
						items.list = data.data;
						table.updatePages();
					})
				.error(function(data, status){
					table.addError(data, status);
					table.updatePages();
				});
			};

			$scope.$watch('config.base_url', function(newValue, oldValue) {
				if (config.override_reload = true) {
					live = true;
					table.loadData();
					return;
				};
				if (newValue != "/api/CRUD_EXAMPLE") {
					items.reload = function() {
						return $http.get(table.base_url);
					};
				} else {
					live = false;
				}
				table.loadData();
			});
			
			if (config.base_url != "/api/CRUD_EXAMPLE" && !config.override_reload) {
				items.reload = function() {
					return $http.get(table.base_url);
				};
			}; 
			
			table.loadData();

			/**
			 * @brief Update an item.
			 * This function validates the new data, then sends a request to the server to update the item.
			 *
			 * @param item Item to be updated
			 */
			table.updateItem = function(item){
				preApiCalls.item = item;
				preApiCalls.update(function(){
					var url_extra = "";
				   	angular.forEach(table.unique_keys, function(value, key){
						url_extra += "/" + item[value];
					});
					if (live) {
						$http.put(table.base_url + url_extra, $.param(item))
							.success(function(data, status){
							})
							.error(function(data, status){
								table.addError(data, status);
							});
					}
				});
			};

			/**
			 * @brief Delete an item.
			 * Validates that the item can be deleted, then sends a request to the server to delete the item.
			 *
			 * @param item Item to delete
			 */
			table.deleteItem = function(item){
				if (table.no_deletes)
					return;
				preApiCalls.item = item;
				preApiCalls.delete(function(){
					var index = items.list.indexOf(item);
					if (index > -1){
						items.list.splice(index, 1);
					}
					table.updatePages();
					var url_extra = "";
				   	angular.forEach(table.unique_keys, function(value, key){
						url_extra += "/" + item[value];
					});
					if (live) {
						$http.delete(table.base_url + url_extra, {data : $.param(item)})
							.success(function(data, status){
							})
							.error(function(data, status){
								table.addError(data, status);
								table.updatePages();
							});	
					}
				});
			};	

			/**
			 * @brief Add a new item.
			 * Validates the data for the new item, then closes the new item dialog and sends a request to the
			 * server to add the item.
			 */
			table.addItem = function(){
				if (table.no_inserts)
					return;
				preApiCalls.item = table.new_item;
				preApiCalls.add(function(){
					var url_extra = "";
					if (live) {
						$http.post(table.base_url + url_extra, $.param(table.new_item))
							.success(function(data, status){
								if (data.data && typeof data.data === 'object'){
									items.list.push(data.data);
								} else {
									items.list.push(table.new_item);
								}
								delete table.new_item;
								delete table.add_error;
								table.updatePages();
								$("#add-dialog").dialog("close");
								if (!$scope.$$phase){
									$scope.$apply();
								}
							})
							.error(function(data, status){
								table.addError(data, status);
								table.updatePages();
								delete table.new_item;
								delete table.add_error;
								$("#add-dialog").dialog("close");
							});
					} else {
						items.list.push(table.new_item);
						delete table.new_item;
						delete table.add_error;
						table.updatePages();
						$("#add-dialog").dialog("close");
						if (!$scope.$$phase){
							$scope.$apply();
						}
					}
				});
			};

			/**
			 * @brief Open a dialog to add a new item
			 */
			table.openAddDialog = function(){
				if (table.no_inserts)
					return;
				$("#add-dialog").dialog({autoOpen: false, modal:true, width : $("#crud-table").width(),
					position: {my: "left top", at: "left top", of: $("#add-item-btn")}});
				$("#add-dialog").dialog("open");
			};

			table.addError = function(data, status){
				try{
					if (data.message){
						error.messages.push(data.message);
					} else {
						error.messages.push(status);
					}
				} catch(e)  {
					error.messages.push(status);
				}
			}

			// Open the new item dialog on Alt+N
			$(document).keyup(function(event){
				if (event.keyCode == 78 && event.altKey){
					table.openAddDialog();
				}			
			});

		},
		controllerAs: 'table'
		}
	}]);


	app.directive('addDialog', function(){
		return {
			restirct: 'A',
			templateUrl: '/crudApps/addDialog',
			scope : {
				table : '='
			}
		};
	});

})();

