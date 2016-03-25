var app = angular.module('tmt_page', []);

app.controller("employeePageCtrl", function($scope, $http, $window, $employeeList) {

	$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";

	$scope.fields    = [];// An array of objects. Each object has name and value defined for each custom data field
	$scope.positions = [];// An array of position objects with id and name

	$scope.terminatedPermission = $window.terminatedPerm;// Whether or not the employee has permission to view terminated employees
	$scope.editPermission       = $window.editPerm;// Whether or not the employee has permission to edit other employees
	$scope.userNetId = $window.userNetId;// Current user's netId
	$scope.group     = $window.group;// Current area/group

	// The values that determine whether the page is in edit, add or view only mode
	$scope.edit = false;
	$scope.add  = false;

	// These are for toggling the success/failure alerts
	$scope.success = false;
	$scope.failure = false;
	$scope.getFail = false;
	$scope.customDataError = false;
	$scope.showSpinner = false; // Spinner used when terminating employee
	$scope.loading = true; // Spinner used when initially loading the page


	/*
	 * Treat the following objects, arrays, and values as "constants"
	 */
	$scope.statuses = [
		{name: 'Active',     code:  1},
		{name: 'Inactive',   code:  0},
		{name: 'Terminated', code:  -1}
	];
	$scope.rehirableOpts = [
		"No",
		"Yes"
	];
	$scope.termOptions = [
		"Poor",
		"Fair",
		"Good",
		"Excellent"
	];
	$scope.addText  = "Add Employee";
	$scope.editText = "Edit";
	/*
	 * End "constants"
	 */


	//The following fields are for the employee that is currently being viewed
	$scope.curEmployee = {
		netID:          "",
		firstName:      "",
		lastName:       "",
		phone:          "",
		email:          "",
		byuIDnumber:    "",
		hireDate:       "",
		birthday:       "",
		graduationDate: "",
		supervisor:     "",
		fullTime:       false,
		position:       "",
		status:         $scope.statuses[0],
		wage:           "",
		area:           $scope.group
	};

	//Object to hold the termination information
	$scope.termination = {
		'attendance':  "",
		'attitude':    "",
		'performance': "",
		'rehirable':   "",
		'reasons':     ""
	};

	/**
	 * Saves the user data into the database
	 *   (both Mongo and Mysql)
	 */
	$scope.save = function() {
		if(!$scope.validate()) {
			return;
		}

		var data = {
			firstName:      $scope.curEmployee.firstName,
			lastName:       $scope.curEmployee.lastName,
			phone:          $scope.curEmployee.phone,
			email:          $scope.curEmployee.email,
			byuIDnumber:    $scope.curEmployee.byuIDnumber,
			hireDate:       $scope.curEmployee.hireDate,
			birthday:       $scope.curEmployee.birthday,
			graduationDate: $scope.curEmployee.graduationDate,
			supervisor:     $scope.curEmployee.supervisor,
			position:       $scope.curEmployee.position.id,
			fullTime:       Number($scope.curEmployee.fullTime),
			active:         $scope.curEmployee.status.code
		};

		// Use post if adding
		if($scope.add) {
			data.netID = $scope.curEmployee.netID;
			data.wage = $scope.curEmployee.wage;
			$http.post("/api/employee", $.param(data))
			.success(function(data) {
				// Put saved area specific user data
				$scope.saveGroupData();
				// Update the list of employees to hold the correct data
				$employeeList.getEmployees();
			})
			.error(function(data) {
				if($scope.add) {
					$scope.getUser($scope.userNetId);
				} else {
					$scope.getUser($scope.curEmployee.netID);
				}
				$scope.edit = false;
				$scope.add  = false;
				$scope.editText = "Edit";
				$scope.addText  = "Add Employee";
				$scope.original = {};
				$scope.failure = true;
			});

		// Use put if editing
		} else {
			if($scope.curEmployee.area != $scope.group) {
				$scope.saveGroupData();
				return;
			}
			$http.put("/api/employee/"+$scope.curEmployee.netID, $.param(data))
			.success(function(data) {
				//Post saved area specific user data
				$scope.saveGroupData();
			})
			.error(function(data) {
				if($scope.add) {
					$scope.getUser($scope.userNetId);
				} else {
					$scope.getUser($scope.curEmployee.netID);
				}
				$scope.edit = false;
				$scope.add  = false;
				$scope.editText = "Edit";
				$scope.addText  = "Add Employee";
				$scope.original = {};
				$scope.failure = true;
			});
		}
	}

	/**
	 * Save the custom group data
	 *
	 * NOTE: This never needs to be called except from the save function
	 */
	$scope.saveGroupData = function() {
		//Post saved area specific user data
		var putData = {};
		for(var field in $scope.fields) {
			putData[$scope.fields[field].name] = $scope.fields[field].value;
		}
		$http.put("/api/userGroupData/"+$scope.curEmployee.netID+"/"+$scope.group, $.param(putData))
		.success(function(data) {
			$scope.getUser($scope.curEmployee.netID);
			$scope.edit = false;
			$scope.add  = false;
			$scope.editText = "Edit";
			$scope.addText  = "Add Employee";
			$scope.original = {};
			$scope.success = true;
		})
		.error(function(data) {
			$scope.getUser($scope.curEmployee.netID);
			$scope.edit = false;
			$scope.add  = false;
			$scope.editText = "Edit";
			$scope.addText  = "Add Employee";
			$scope.original = {};
			$scope.failure = true;
		});
	}

	/**
	 * Validates the input when saving an employee's data
	 * 
	 * @return bool, true if successfully validate, false otherwise
	 */
	$scope.validate = function() {
		// Check netID
		if(($scope.curEmployee.netID == $scope.userNetId && $scope.add) || $scope.curEmployee.netID == "") {
			alert("Please enter a valid netId");
			return false;
		}

		// Validate wage when adding part-time employee
		if($scope.add) {
			var wage = +$scope.curEmployee.wage;
			if((isNaN(wage) || wage < 1) && !$scope.curEmployee.fullTime) {
				alert("Please enter a valid wage for this employee formatted as 00.00");
				return false;
			}
		}

		// Check phone 
		if($scope.curEmployee.phone == "") {
			alert("Please enter a phone number");
			return false;
		}

		// Check email
		if($scope.curEmployee.email == "") {
			alert("Please enter an email address");
			return false;
		}

		// Check BYU ID
		var byuIdNumCount = ($scope.curEmployee.byuIDnumber.match(/\d/g) || []).length;
		if(byuIdNumCount < 9 || byuIdNumCount > 11) {
			alert("Please enter a valid 9 digit byu id number and, optionally, a 1-2 digit id card issue number");
			return false;
		}

		return true;
	}

	//Adjusts the page for adding employees
	$scope.setAddEmployee = function() {
		if($scope.edit)
			return;
		$scope.add = !$scope.add;
		if($scope.add) {
			$scope.addText = "Cancel";
			$scope.clear();
		} else {
			$scope.addText = "Add Employee";
			$scope.getUser($scope.userNetId);
			$scope.curEmployee.wage = "";
		}
	};

	/**
	 * Used when editing employees to store the
	 *   data that the employee originally had in case
	 *   cancel is clicked.
	 */
	$scope.original = {};

	//Adjusts the page for editing employees
	$scope.setEditEmployee = function() {
		if($scope.add)
			return;
		$scope.edit = !$scope.edit;
		if($scope.edit) {
			// Standard data
			$scope.original.netID          = $scope.curEmployee.netID;
			$scope.original.firstName      = $scope.curEmployee.firstName;
			$scope.original.lastName       = $scope.curEmployee.lastName;
			$scope.original.phone          = $scope.curEmployee.phone;
			$scope.original.email          = $scope.curEmployee.email;
			$scope.original.byuIDnumber    = $scope.curEmployee.byuIDnumber;
			$scope.original.hireDate       = $scope.curEmployee.hireDate;
			$scope.original.birthday       = $scope.curEmployee.birthday;
			$scope.original.graduationDate = $scope.curEmployee.graduationdate;
			$scope.original.supervisor     = $scope.curEmployee.supervisor;
			$scope.original.position       = $scope.curEmployee.position;
			$scope.original.status         = $scope.curEmployee.status;
			$scope.original.fullTime       = $scope.curEmployee.fullTime;

			// Group data
			$scope.original.groupData = $scope.fields;

			$scope.editText = "Cancel";
		} else {
			// Standard data
			$scope.curEmployee.netID          = $scope.original.netID;
			$scope.curEmployee.firstName      = $scope.original.firstName;
			$scope.curEmployee.lastName       = $scope.original.lastName;
			$scope.curEmployee.phone          = $scope.original.phone;
			$scope.curEmployee.email          = $scope.original.email;
			$scope.curEmployee.byuIDnumber    = $scope.original.byuIDnumber;
			$scope.curEmployee.hireDate       = $scope.original.hireDate;
			$scope.curEmployee.birthday       = $scope.original.birthday;
			$scope.curEmployee.graduationDate = $scope.original.graduationdate;
			$scope.curEmployee.supervisor     = $scope.original.supervisor;
			$scope.curEmployee.position       = $scope.original.position;
			$scope.curEmployee.status         = $scope.original.status;
			$scope.curEmployee.fullTime       = $scope.original.fullTime;

			// Group data
			$scope.fields = $scope.original.groupData;

			$scope.editText = "Edit";
			$scope.original = {};
		}
	};


	// Controls opening of the termination dialog when activity dropdown is changed
	$scope.isTerminated = function(value) {
		if(value == -1) {
 			if(confirm("Are you sure you want to terminate this employee?")) {
				$('#terminationDialog').dialog('open');
			} else {
				$scope.curEmployee.status = $scope.original.status;
			}
		}
	};

	/**
	 * Loads information from PI to populate new employee information
	 *   Takes netId as a parameter
	 */
	$scope.getEmployeePI = function(netId) {
		// Don't allow them to add themselves
		if(netId == userNetId || netId == "") {
			$scope.curEmployee.netID = "";
			return;
		}
		$http.get('/api/getPiInformation/'+netId)
		.success(function(data) {
			if('no_authorization' in data.data) {
				return;
			}

			var name = data.data.names.complete_name.split(',');
			$scope.curEmployee.firstName   = name[1].trim().split(' ')[0];
			$scope.curEmployee.lastName    = name[0];
			$scope.curEmployee.phone       = data.data.contact_information.phone_number;
			$scope.curEmployee.email       = data.data.contact_information.email;
			$scope.curEmployee.birthday    = data.data.personal_information.date_of_birth;
			$scope.curEmployee.byuIDnumber = data.data.identifiers.byu_id+" "+data.data.identifiers.byu_id_issue_number;
			$scope.curEmployee.status      = $scope.statuses[0];

			var today = new Date();
			$scope.curEmployee.hireDate = today.getFullYear() + '-' + (('0' + (today.getMonth()+1)).slice(-2)) + '-' + ('0'+today.getDate()).slice(-2);
		});
	};

	//Clears all inputs for adding a new employee
	$scope.clear = function() {
		for(var field in $scope.fields) {
			$scope.fields[field].value = "";
		}
		$scope.curEmployee.netID          = "";
		$scope.curEmployee.firstName      = "";
		$scope.curEmployee.lastName       = "";
		$scope.curEmployee.phone          = "";
		$scope.curEmployee.email          = "";
		$scope.curEmployee.byuIDnumber    = "";
		$scope.curEmployee.hireDate       = "";
		$scope.curEmployee.birthday       = "";
		$scope.curEmployee.graduationDate = "";
		$scope.curEmployee.supervisor     = "";
		$scope.curEmployee.position       = $scope.positions[0];
		$scope.curEmployee.status         = $scope.statuses[0];
		$scope.curEmployee.fullTime       = false;
		$scope.curEmployee.wage           = "";
		$scope.curEmployee.area           = $scope.group;
	};

	// Called when an employee is chosen from the list
	// and populates the fields with the data for that employee
	$scope.employeeListSelect = function(employee) {
		if($scope.add || $scope.edit) {
			return;
		}
		$scope.getUser(employee.netID);
	}

	// Sets all the fields to the given user's data
	$scope.getUser = function(netId) {
		// Get universal data
		$http.get("/api/employee/"+netId).success(function(data) {
			$scope.curEmployee.netID          = data.data.netID;
			$scope.curEmployee.firstName      = data.data.firstName;
			$scope.curEmployee.lastName       = data.data.lastName;
			$scope.curEmployee.phone          = data.data.phone;
			$scope.curEmployee.email          = data.data.email;
			$scope.curEmployee.byuIDnumber    = data.data.byuIDnumber;
			$scope.curEmployee.hireDate       = data.data.hireDate;
			$scope.curEmployee.birthday       = data.data.birthday;
			$scope.curEmployee.graduationDate = data.data.graduationDate;
			$scope.curEmployee.supervisor     = data.data.supervisor;
			$scope.curEmployee.fullTime       = Boolean(data.data.fullTime);
			$scope.curEmployee.area           = data.data.area;
			if(data.data.active == 1) {
				$scope.curEmployee.status = $scope.statuses[0];
			} else if(data.data.active == 0) {
				$scope.curEmployee.status = $scope.statuses[1];
			} else if(data.data.active == -1) {
				$scope.curEmployee.status = $scope.statuses[2];
			}
			// Choose position if in the area
			if(data.data.area == $scope.group) {
				for(var i in $scope.positions) {
					if(data.data.position == $scope.positions[i].id) {
						$scope.curEmployee.position = $scope.positions[i];
					}
				}
			} else {
				// If the user is not defaulted to the current area, get position name
				$http.get("/api/position/"+data.data.position).success(function(data) {
					$scope.curEmployee.position = {id: data.data.positionId, name: data.data.positionName};
				});
			}
		});

		// Get group-specific data
		$http.get("/api/userGroupData/"+netId+"/"+$scope.group).success(function(data) {
			for(var field in data.data.data) {
				for(var j=0; j < $scope.fields.length; j++) {
					if($scope.fields[j].name == field) {
						$scope.fields[j].value = data.data.data[field];
						break;
					}
				}
			}
		}).error(function() {
			$scope.getFail = true;
		});
	};

	/**
	 * Makes the api call to terminate an employee
	 */
	$scope.terminateEmployee = function(dialog) {
		for(var key in $scope.termination) {
			if($scope.termination[key] == "") {
				alert("Please enter termination data for the "+key+" field");
				$scope.curEmployee.status = $scope.original.status;
				return;
			}
		}
		var terminationDetails   = $scope.termination;
		terminationDetails.netID = $scope.curEmployee.netID;
		terminationDetails.area  = $scope.group;
		$scope.showSpinner = true;
		$http.post("/api/employee/terminate", $.param(terminationDetails))
		.success(function(data) {
			$scope.showSpinner = false;
			$scope.save();// Save other updated data (update status to terminated)
			dialog.dialog("close");
		})
		.error(function(data) {
			$scope.showSpinner = false;
			// An error occurred while revoking permissions
			if(!data.message.permissions) {
				alert("Unable to properly revoke this employee's permissions. If this error persists please contact the Service Desk at 2-4000");
				return;
			}
			// An error occurred when sending rights emails
			if(typeof data.message.rights == "object" && Object.keys(data.message.rights.failed).length > 0) {
				$scope.failedEmails = data.message.rights.failed;
				$("#emailDialog").dialog("open");
				$scope.$digest();
				return;
			}
			if(typeof data.message.rights == "boolean" && !data.message.rights) {
				alert("Unable to properly terminate this user's rights. If this error persists, please contact the Service Desk at 2-4000");
				return;
			}
			// An error occurred inserting termination details
			if(!data.message.terminated) {
				alert("The rights and permissions for this employee were properly revoked, but unable to succesfully mark as terminated. If this error persists, please contact the Service Desk at 2-4000");
				return;
			}
			// Something went wrong, but not sure what
			alert("An error has occurred, please try again. If this error persists, please contact Service Desk at 2-4000");
		});
	}


	/************************************** Run initialization functions *******************************************************/


	// Initialize the employee list
	$employeeList.initialize([$scope.group], false, true, $scope.terminatedPermission, 1);

	// Get the custom data fields for the group
	$http.get("/api/customData/"+$scope.group).success(function(data) {
		for(field in data.data.fields) {
			$scope.fields.push({"name": data.data.fields[field], "value": ""});
		}
		$scope.getUser($scope.userNetId, $scope.group);// Show user's data on page load
	}).error(function() {
		$scope.customDataError = true;
		$scope.getUser($scope.userNetId);// Show user's data on page load
	});

	// Load the positions drop down
	$http.get("/api/position/area/"+$scope.group).success(function(data) {
		for(var position in data.data) {
			$scope.positions.push({
				"id":   data.data[position].positionId,
				"name": data.data[position].positionName
			});
		}
	}).error(function() {
		$scope.getFail = true;
	});

	// Show spinner until document is loaded
	angular.element(document).ready(function() {
		$scope.loading = false;
	});
});







/**
 * A simple datepicker directive
 */
app.directive('datepicker', function($parse) {
	return function(scope, element, attrs, controller) {
		var ngModel = $parse(attrs.ngModel);
		$(function() {
			$(element).datepicker({
				dateFormat: 'yy-mm-dd',
				onSelect: function(dateText, inst) {
					scope.$apply(function($scope) {
						ngModel.assign(scope, dateText);
					});
				}
			});
		});
	}
});

/**
 * This directive is to create a jquery dialog for termination details
 */
app.directive('dialog', function($timeout) {
	return {
		transclude: true,
		templateUrl: "/static/html/employeeList/terminationDialog.html",
		link: function(scope, element, attrs) {
			$timeout(function() {
				$(element).dialog({
					dialogClass: "noClose",
					resizable: true,
					modal: true,
					autoOpen: false,
					title: "Termination Details",
					buttons: {
						"Submit": function() {
							scope.terminateEmployee($(this));
						},
						"Cancel": function() {
							scope.showSpinner = false;
							scope.status = scope.original.status;
							scope.$digest();
							$(this).dialog("close");
						}
					}
				});
			}, 0);
		}
	};
});

/**
 * This directive is to create a jquery dialog that shows all the
 *   termination emails that failed to send correctly
 */
app.directive('emailDialog', function($timeout) {
	return {
		transclude: true,
		templateUrl: "/static/html/employeeList/failedEmailDialog.html",
		link: function(scope, element, attrs) {
			$timeout(function() {
				$(element).dialog({
					dialogClass: "noClose",
					resizable: true,
					modal: true,
					autoOpen: false,
					title: "Unsent Emails",
					buttons: {
						"Close": function() {
							$(this).dialog("close");
						}
					}
				});
			}, 0);
		}
	};
});
