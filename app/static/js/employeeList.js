/** Factory with the list of employees that allows the functionality to be dependency injected into other modules
 *  The factory includes the following:
 *    employees: A array of employee objects, each employee has the firstName, lastName, netId, active
 *    showActivity: a boolean value to determines if the activity status select menu is shown
 *    activity: -1, 0, 1 The currently selected activity status used to filter the list
 *      -1 = terminated
 *      0  = inactive
 *      1  = active
 *    initialize: a function that, when called, will populate the list of employees through an API call
 *      as well as set the activity if it is different than 1(active) and whether or not the select menu
 *      is shown.
 *      This function must be called to properly use the employee list!!
 *
 *      Parameters:
 *        groups: array(int) An array of group ids of groups whose employees should be in the list
 *        defaultOnly:  bool True to list employees only from the area, false to list all with access to the area
 *        showActivity: bool Whether or not to show the activity status select menu
 *        activity:      int The default for the activity select menu. Defaults to 1(active)
 */
app.factory('$employeeList', function($q, $http) {
	return {
		"employees": [],
		"showActivity": false,
		"activity": 1,
		"defaultOnly": true,
		"showAsterisk": false,
		"groups": [],
		"initialize": function(groups, defaultOnly, showAsterisk, showActivity, activity) {
			this.groups = groups;
			if(defaultOnly !== undefined) {
				this.defaultOnly = defaultOnly;
			}
			if(activity !== undefined) {
				this.activity = activity;
			}
			if(showActivity !== undefined) {
				this.showActivity = showActivity;
			}
			if(showAsterisk !== undefined) {
				this.showAsterisk = showAsterisk;
			}
		},
		"getEmployees": function() {
			var query = "?";
			if(!this.defaultOnly) {
				query += "defaultOnly=false&";
			}
			for(var i=0; i < this.groups.length; i++) {
				query += "areas[]=" + this.groups[i] + "&";
			}
			query = query.substring(0, query.length - 1);
			var defer = $q.defer();
			$http.get("/api/employee/area"+query).success(function(data) {
				defer.resolve(data);
			});
			return defer.promise;
		}
	};
});

//Used to filter out employees based on activity status (i.e. active(1), inactive(0), terminated(-1))
//  The filter assumes that the object passed in has a field called 'active'.
app.filter('activityFilter', function() {
	return function(employees, activity) {
		var filtered = [];
		for(var i = 0; i < employees.length; i++) {
			if(employees[i].active == activity) {
				filtered.push(employees[i]);
			}
		}
		return filtered;
	}
});

//Filters through employees based on the search typed into the box. This filter will match
//  all employees whose first name, last name, full name, or netId contains the search text.
//  This assumes that the objects passed in have fields labeled 'netId', 'firstName', and 'lastName'
app.filter('employeeFilter', function() {
	return function(employees, search) {
		//return all if no search is defined
		if(search === undefined || search == "") {
			return employees;
		}
		//set search to lowercase in order to match all cases
		search = search.toLowerCase();

		var filtered = [];
		//filter through employees checking to see if the search terms match a first name, last name, netId, or full name
		for(var i = 0; i < employees.length; i++) {
			var fullName = (employees[i].firstName + " " + employees[i].lastName).toLowerCase();
			if(employees[i].netID.toLowerCase().indexOf(search) != -1 || fullName.indexOf(search) != -1) {
				filtered.push(employees[i]);
			}
		}
		return filtered;
	};
});

//Controller for the employee list
app.controller('listCtrl', function ($scope, $employeeList, $window) {
	//Allow the employee list controller to access the factory and employee list
	$scope.list = $employeeList;

	// Populate list
	$employeeList.getEmployees().then(function(data) {
		$scope.list.employees = data.data;
		if($employeeList.showAsterisk) {
			for(var i in $scope.list.employees) {
				if($scope.list.employees[i].area != $window.group) {
					$scope.list.employees[i].lastName += "*";
				}
			}
		}
	});
});
