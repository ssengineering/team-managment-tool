(function(){
	// Get instance of angular app
	var app = angular.module('tmt_page');

	app.directive('deleteDialog', ['$http', '$q', function($http, $q) {
		return {
			restrict: 'E',
			templateUrl : '/positions/deleteDialog',
			controller: function($http, $scope, $q, preApiCalls, items, error) {
				$scope.errors = error.messages;
				$scope.items = items;
				$scope.$watch('items.list', function(newValue, oldValue){
					$scope.positions = newValue;
				});
				$scope.affected = [];
				$scope.valid = false;
				$scope.updateAll = updateAll;
				$scope.submitDelete = submitDelete;
				$scope.checkValid = checkValid;
				preApiCalls.delete = function(callback) {
					var id = preApiCalls.item.positionId;
					$http.get("/api/employee", {'params' : {'position':id, 'active' : 1}})
					.success(function(data, status){
						$scope.affected = data.data;	
						if (!$scope.$$phase)
						 $scope.$apply();
						if ($scope.affected.length) {
							angular.forEach($scope.affected, function(key, value){
								value.position = "";
							});
							openDialog();
							$scope.finishDelete = callback;
						} else { 
							callback();
						}
					}).error(function(data, status){
						error.messages.push(data.message);
					});
				};

				function updateAll() {
					for (var i = 0; i < $scope.affected.length; i++) {
						var employee = $scope.affected[i];
						employee.position = $scope.updateMultiple;
					}
					checkValid();
				}

				function checkValid() {
					for (var i = 0; i < $scope.affected.length; i++) {
						var employee = $scope.affected[i];
						if (employee.position == $scope.deleting.positionId || !employee.position){
							$scope.valid = false;
							return;
						}
					}
					$scope.valid = true;
					return;
				}

				function submitDelete() {
					var update_requests = [];
					angular.forEach($scope.affected, function(employee, index) {
						var data = {position: employee.position};
						update_requests.push($http.put('/api/employee/'+employee.netID, $.param(data)));
					});
					$q.all(update_requests).then(function(){
						$scope.finishDelete();
						closeDialog();
					}, function(){
						$scope.errors.push("Unable to update the position for one or more of the "+
							"affected employees");
						closeDialog();
						return;
					});

				}

				function closeDialog() {
					$("#delete-dialog").dialog("close");
				}

				function openDialog() {
					$scope.deleting = preApiCalls.item;
					$scope.updateMultiple = $scope.deleting.positionId;
					checkValid();
					$("#delete-dialog").dialog({autoOpen: false, modal:true, width : $("#crud-table").width(),
						position: {my: "left top", at: "left top", of: $("#add-item-btn")}});
					$("#delete-dialog").dialog("open");
				}

			}
		};	
	}]);

})();
