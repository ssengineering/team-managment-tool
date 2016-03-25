var app = angular.module("tmt_page");

app.controller("explainCtrl", function($scope, $http, $window, $filter, $employeeList) {
    $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded"; // Make Angular send things encoded in the way we're expecting

	$scope.groups = [];
	$scope.currentEmployee = "Select an employee...";

	$scope.admin = false;
	$scope.initialized = false;
	$employeeList.initialize([$window.group], false, true, false, 1);

	$scope.employeeListSelect = function(employee) {
		if(!$scope.initialized) {
			return;
		}
		for(var j=0; j < $scope.groups.length; j++) {
			for(k=0; k < $scope.groups[j].resources.length; k++) {
				for(var m=0; m < $scope.groups[j].resources[k].verbs.length; m++) {
					$scope.groups[j].resources[k].verbs[m].hasPermission = false;
				}
			}
		}
		$http.get("/api/admin/" + employee.netID + "/" + $window.areaGuid).success(function(data) {
			if(data.data) {
				$scope.admin = true;
			} else {
				$scope.admin = false;
			}
			$http.get("/api/permission/user/" + employee.netID + "/" + $window.areaGuid).success(function(data) {
				// Loop through all the resources and check if the user has the corresponding permission and set the hasPermission variable accordingly
				for(var i=0; i < data.data.length; i++) {
					for(var j=0; j < $scope.groups.length; j++) {
						for(k=0; k < $scope.groups[j].resources.length; k++) {
							for(var m=0; m < $scope.groups[j].resources[k].verbs.length; m++) {
								if((data.data[i].Resource == $scope.groups[j].resources[k].guid
								   && data.data[i].Verb == $scope.groups[j].resources[k].verbs[m].name)) {
									$scope.groups[j].resources[k].verbs[m].hasPermission = true;
								}
							}
						}
					}
				}
				$scope.currentEmployee = employee.firstName + " " + employee.lastName;
			});
		});
		// Identify which groups the user is a member of
		$http.get("/api/permission/groups?area=" + $window.areaGuid + "&netId=" + employee.netID).success(function(data) {
			// Unmark each group with "member"
			for(var j=0; j < $scope.groups.length; j++) {
				$scope.groups[j].member = "";
			}
			// If the selected user is a member of the group add the subtitle "member" to the group name
			for(var i=0; i < data.data.length; i++) {
				for(var j=0; j < $scope.groups.length; j++) {
					if(data.data[i].Guid == $scope.groups[j].Guid) {
						$scope.groups[j].member = "member";
					}
				}
			}
			$http.get("/api/permission/groups?implied=true&area=" + $window.areaGuid + "&netId=" + employee.netID).success(function(data) {
				for(var i=0; i < data.data.length; i++) {
					for(var j=0; j < $scope.groups.length; j++) {
						if(data.data[i].Guid == $scope.groups[j].Guid && $scope.groups[j].member != "member") {
							$scope.groups[j].member = "implied membership";
						}
					}
				}
			});
		});
	};

	// Function to initialize list
	$scope.init = function() {
		// load a list of resources
		$http.get("/api/resource").success(function(data) {
			$scope.resources = data.data;
			// load in all the groups in the area
			$http.get("/api/group?area=" + $window.areaGuid).success(function(data) {
				$scope.groups = data.data;
				var calls = data.data.length;
				var returned = 0;
				if(calls == 0) {
					$scope.initialized = true;
					return;
				}
				for(var i=0; i < data.data.length; i++) {
					$scope.groups[i].resources = [];
					$scope.groups[i].member = "";
					$http.get("/api/permission/groups/" + data.data[i].Guid).success(function(data) {
						returned++;
						for(var j=0; j < data.data.length; j++) {
							$scope.addResource(data.data[j].Resource, data.data[j].Actor, data.data[j].Verb);
						}

						// Mark the initialization as complete
						if(returned == calls) {
							$scope.initialized = true;
						}
					});
				}
			});
		});
	};

	// Return the resource object with the given guid. This is a subroutine for $scope.addResource().
	$scope.getResource = function(guid) {
		for(var i=0; i < $scope.resources.length; i++) {
			if($scope.resources[i].guid == guid) {
				return $scope.resources[i];
			}
		}
		return null;
	}

	// Adds a resource with the given guid to the group in $scope.groups with index groupIndex.
	// It will only add resources the group doesn't already have, and will
	// add the verbs as necessary.
	$scope.addResource = function(guid, groupGuid, verb) {
		var groupIndex = 0;
		for(var i=0; i < $scope.groups.length; i++) {
			if($scope.groups[i].Guid == groupGuid) {
				groupIndex = i;
				break;
			}
		}
		var resource = $scope.getResource(guid);
		if(resource != null) {
			var found = false;
			for(var i=0; i < $scope.groups[groupIndex].resources.length; i++) {
				if($scope.groups[groupIndex].resources[i].guid == guid) {
					found = true;
					$scope.groups[groupIndex].resources[i].verbs.push({name: verb, hasPermission: false});
					break;
				}
			}
			if(!found) {
				$scope.groups[groupIndex].resources.push({guid: guid, name: resource.name, verbs: [{name: verb, hasPermission: false}]});
			}
		}
	};

	// Initialize group list
	$scope.init();

});

app.directive('memberTooltip', function() {
	return {
		restrict: 'A',
		link: function(scope, element, attrs) {
			$(element).hover(function() {
				$(element).tooltip('show');
			}, function() {
				$(element).tooltip('hide');
			});
		}
	};
});
