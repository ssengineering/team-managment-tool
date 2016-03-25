var app = angular.module('tmt_page', []);

app.controller("tableCtrl", function($scope, $http, $window) {
	
	// List of the employees in the table
	$scope.employees = [];
	// List of manager netIds
	$scope.managers  = [];
	// List of positions
	$scope.positions = [];
	$scope.positionIds = [];
	// The current area
	$scope.area = $window.area

	// Boolean values controlling which groups of employees are shown
	$scope.active     = true;
	$scope.inactive   = false;
	$scope.terminated = false;

	// set all columns to show
	$scope.columns = {
		photo: true,
		netId: true,
		firstName: true,
		lastName: true,
		byuId: true,
		phone: true,
		email: true,
		position: true,
		status: true,
		manager: true,
		birth: true,
		hire: true,
		graduation: true
	};

	// Controls the sorting of the table
	$scope.sortColumn = "netID";
	$scope.reverse    = false;

	// Updates the column the table is sorted by
	$scope.getSortColumn = function(column) {
		if(column == $scope.sortColumn) {
			$scope.reverse = !$scope.reverse;
		}
		$scope.sortColumn = column;
	}

	// The function that is used by the filter to sort the table
	$scope.sort = function(employees) {
		return employees[$scope.sortColumn];
	}

	// Get employee list
	$http.get("/api/employee/area/"+$scope.area+"?defaultOnly=false&customData=true").success(function(data) {
		$scope.employees = data.data;

		for(var i in $scope.employees) {
			// Get an array of each manager referenced by the employees
			if($scope.managers.indexOf($scope.employees[i].supervisor) == -1 && $scope.employees[i].supervisor != "") {
				$scope.managers.push($scope.employees[i].supervisor);
			}
			if($scope.positionIds.indexOf($scope.employees[i].position) == -1 && $scope.employees[i].position != 0) {
				$scope.positionIds.push($scope.employees[i].position);
			}
		}

		// Change to show manager name instead of netId
		for(var i in $scope.managers) {
			$http.get("/api/employee/"+$scope.managers[i]).success(function(data) {
				for(var j in $scope.employees) {
					if($scope.employees[j].supervisor == data.data.netID) {
						$scope.employees[j].supervisor = data.data.firstName + " " + data.data.lastName;
					}
				}
			});
		}

		// Retrieve position list
		$http.get("/api/position/area/"+$scope.area).success(function(data) {
			$scope.positions = data.data;
			for(var i in $scope.positions) {
				$scope.positions[i].isChecked = true;
			}
		});

		// Retrieve other positions from other areas
		for(var i in $scope.positionIds) {
			$http.get("/api/position/"+$scope.positionIds[i]).success(function(data) {
				var found = false;
				for(var j in $scope.positions) {
					if($scope.positions[j].positionName == data.data.positionName) {
						found = true;
						var oldId = data.data.positionId;
						var newId = $scope.positions[j].positionId;
						break;
					}
				}
				// Found a position with the same name and different id
				if(!found) {
					$scope.positions.push(data.data);
					for(var j in $scope.positions) {
						if($scope.positions[j].positionId == data.data.positionId) {
							$scope.positions[j].isChecked = true;
						}
						if($scope.positions[j].positionId == null) {
							$scope.positions.splice(j, 1);
						}
					}
				} else {
					for(var j in $scope.employees) {
						for(var k in $scope.positions) {
							if($scope.employees[j].position == oldId) {
								$scope.employees[j].position = newId;
								break;
							}
						}
					}
				}
			});
		}

	});

	// Controls the toggling of the advanced search div
	$scope.advSearch = false;
	$scope.toggleAdvanced = function() {
		$scope.advSearch = !$scope.advSearch;
		// Clear manager and position search boxes
		if(!$scope.advSearch) {
			$scope.managerSearch = "";
			for(var i in $scope.positions) {
				$scope.positions[i].isChecked = true;
			}
		}
	}

	// Set all jQuery button sets and check the appropriate defaults
	$("#statusDiv").buttonset();
	$("#columnDiv").buttonset();
	$("#positionsDiv").buttonset();
	$("#activeBox").prop('checked', true).button("refresh");
	$("#photoCheckbox").prop('checked', true).button("refresh");
	$("#netIdCheckbox").prop('checked', true).button("refresh");
	$("#firstNameCheckbox").prop('checked', true).button("refresh");
	$("#lastNameCheckbox").prop('checked', true).button("refresh");
	$("#byuIdCheckbox").prop('checked', true).button("refresh");
	$("#phoneCheckbox").prop('checked', true).button("refresh");
	$("#emailCheckbox").prop('checked', true).button("refresh");
	$("#positionCheckbox").prop('checked', true).button("refresh");
	$("#statusCheckbox").prop('checked', true).button("refresh");
	$("#managerCheckbox").prop('checked', true).button("refresh");
	$("#birthCheckbox").prop('checked', true).button("refresh");
	$("#hireCheckbox").prop('checked', true).button("refresh");
	$("#graduationCheckbox").prop('checked', true).button("refresh");
	for(var i in $window.customDataFields) {
		$("[id='"+$window.customDataFields[i]+"Checkbox']").prop('checked', true).button("refresh");
	}
});




/***************************** Filters *****************************************/



// Show the status word instead of number
app.filter("status", function() {
	return function(input) {
		switch(input) {
			case -1:
				return "Terminated";
			case 0:
				return "Inactive";
			case 1:
				return "Active";
			default:
				return input;
		}
	};
});

// Show position name instead of id
app.filter("positionName", function() {
	return function(input, positions) {
		for(var i in positions) {
			if(positions[i].positionId == input) {
				return positions[i].positionName;
			}
		}
	}
});

// Filter employees by activity -1/0/1
app.filter('activity', function() {
	return function(employees, active, inactive, terminated) {
		var filtered = [];
		for(var i = 0; i < employees.length; i++) {
			switch(employees[i].active) {
				case -1:
					if(terminated) {
						filtered.push(employees[i]);
					}
					break;
				case 0:
					if(inactive) {
						filtered.push(employees[i]);
					}
					break;
				case 1:
					if(active) {
						filtered.push(employees[i]);
					}
					break;
			}
		}
		return filtered;
	}
});

// Filter employees by netID, firstName, or lastName
app.filter('employeeFilter', function() {
	return function(employees, search) {
		if(search === undefined || search == "") {
			return employees;
		}
		search = search.toLowerCase();

		var filtered = [];

		for(var i = 0; i < employees.length; i++) {
			var fullName = (employees[i].firstName + " " + employees[i].lastName).toLowerCase();
			if(employees[i].netID.toLowerCase().indexOf(search) != -1 || fullName.indexOf(search) != -1) {
				filtered.push(employees[i]);
			}
		}
		return filtered;
	};
});

// Filter employees by position
app.filter('position', function() {
	return function(employees, positions) {
		if(positions === undefined) {
			return employees;
		}
		var filtered = [];

		for(var i = 0; i < employees.length; i++) {
			for(var j = 0; j < positions.length; j++) {
				if(employees[i].position == positions[j].positionId && positions[j].isChecked) {
					filtered.push(employees[i]);
					continue;
				}
			}
		}
		return filtered;
	};
});

// Filter employees by manager name
app.filter('manager', function() {
	return function(employees, search) {
		if(search === undefined || search == "") {
			return employees;
		}
		search = search.toLowerCase();

		var filtered = [];

		for(var i = 0; i < employees.length; i++) {
			if(employees[i].supervisor.toLowerCase().indexOf(search) != -1) {
				filtered.push(employees[i]);
			}
		}
		return filtered;
	};
});
