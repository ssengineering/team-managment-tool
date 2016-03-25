var app = angular.module("tmt_page");

app.controller("groupController", function($scope, $http, $window, $filter) {
    $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded"; // Make Angular send things encoded in the way we're expecting

    // Search variables referenced in the .twig file
    $scope.searchUngrouped = "";
    $scope.searchGroup = "";

    $scope.loading = false; // A bool to keep track of if we're showing a loading message so we don't accidentally close non-loading dialogues

    $scope.currentGroup = {}; // Keeps track of the currently-selected group's name and ID
    $scope.permissionGroups = []; // A list of all the permission groups in the area

    $scope.rawGroups = [];

    $scope.rawUngroupedEmployees = [];
    $scope.rawgroupedEmployees = [];

    // Used to prevent a strange "animation" on removing grouped employees from the ungrouped list (and vice versa)
    $scope.siftedUngroupedEmployees = [];
    $scope.siftedgroupedEmployees = [];

    $scope.ungroupedEmployees = [];
    $scope.groupedEmployees = [];

    // Basically, this works for magic reasons so don't touch it
    $scope.update = function() { // Make Angular dynamically update the page to show changes
        if (!$scope.$$phase) { // Don't make Angular constapated
            $scope.$apply(); // THIS IS WHERE THE AWESOME HAPPENS
        }
    };

    $scope.logError = function(message) {
        console.log("Error! " + JSON.stringify(message));
    };

    $scope.clearSearch = function(section) { // Called when the "X" in either search bar is clicked
        $scope[section] = "";
    };

    $scope.showLoading = function() {
        $scope.loading = true;

        swal({
            title: "Please wait...",
            text: "<div class='spinner'><div class='bounce1'></div><div class='bounce2'></div><div class='bounce3'></div></div>",
            html: true,
            showConfirmButton: false
        });
    };

    $scope.hideLoading = function() {
        if ($scope.loading) {
            swal.close();
            $scope.loading = false;
        }
    };

    $scope.checkSelectedUngrouped = function() {
        for (var i = 0; i < $scope.ungroupedEmployees.length; i++) {
            if ($scope.ungroupedEmployees[i].selected) {
                return true;
            }
        }

        sweetAlert("Oops...", "Please select some employees to move!", "error");
        return false;
    };

    $scope.checkSelectedGrouped = function() {
        for (var i = 0; i < $scope.groupedEmployees.length; i++) {
            if ($scope.groupedEmployees[i].selected) {
                return true;
            }
        }

        sweetAlert("Oops...", "Please select some employees to move!", "error");
        return false;
    };

    $scope.findGroup = function(name) {
        for (var i = 0; i < $scope.permissionGroups.length; i++) {
            if ($scope.permissionGroups[i].name == name) {
                return $scope.permissionGroups[i];
            }
        }

        return false;
    };

    $scope.setCurrentGroup = function(group) { // Called in the .twig view when a group is selected in the dropdown
        $scope.currentGroup = group;

        $scope.populateUngroupedEmployees();
        $scope.populateGroupMembers(function() {
            $scope.siftEmployees();
            $scope.update();
        });
    };

    $scope.reloadData = function(callback) {
        $scope.populateUngroupedEmployees();
        $scope.populateGroupsList();

        $scope.populateGroupMembers(function() {
            $scope.siftEmployees();
            $scope.update();

            if (callback) {
                callback();
            }

            $scope.hideLoading();
        });
    };

    $scope.getRawData = function(callback) { // Callback so we don't proceed until we actually have data
        $scope.getGroups(function() {
            $scope.getAllEmployees(callback);
        });
    };

    $scope.getGroups = function(callback) {
        $scope.rawGroups = []; // Clean slate

		// Only show groups the user has access to
		var url = "/api/permission/groups?implied=true&netId=" + $window.user + "&area=" + $window.areaGuid;
		if($window.admin) {
			url = "/api/group?area=" + $window.areaGuid;
		}
        $http.get(url) // Get a list of all the groups the user has access to
            .then(function(response) {
                    $scope.rawGroups = response.data;

                    if (callback) {
                        callback();
                    }
                },
                function(response) {
                    $scope.logError(response.data);
                });
    };

    $scope.getAllEmployees = function(callback) {
        // Starting fresh
        $scope.rawUngroupedEmployees = [];
        $scope.siftedUngroupedEmployees = [];

        $http.get("/api/employee/area/" + $window.currentArea + "?defaultOnly=false&active=1") // Get a list of all the employees in the area
            .then(function(response) {
                    $scope.rawUngroupedEmployees = response.data.data;

                    if (callback) {
                        callback();
                    }
                },
                function(response) {
                    $scope.logError(response.data);
                });
    };

    $scope.populateUngroupedEmployees = function() { // Needed so we can have a fresh list of ungrouped employees for each group
        $scope.siftedUngroupedEmployees = []; // Wipe for reasons

        angular.forEach($scope.rawUngroupedEmployees, function(rawEmployee) { // Populate the ungrouped list with all possible employees
            var employee = {
                name: rawEmployee.firstName + " " + rawEmployee.lastName,
                netId: rawEmployee.netID,
                selected: false
            };

            $scope.siftedUngroupedEmployees.push(employee);
        });
    };

    $scope.populateGroupsList = function() {
        $scope.permissionGroups = []; // Clear the permission groups to start fresh

        if ($scope.rawGroups.data.length > 0) { // Only proceed if there are groups in the area
            angular.forEach($scope.rawGroups.data, function(rawGroup) {
                var group = {
                    guid: rawGroup.Guid,
                    name: rawGroup.Name
                };

                $scope.permissionGroups.push(group);
            });

            $scope.permissionGroups = $filter("orderBy")($scope.permissionGroups, "name"); // Alphabetize those suckers
        }
    };

    $scope.populateGroupMembers = function(callback) {
        // Start fresh
        $scope.rawgroupedEmployees = [];
        $scope.siftedgroupedEmployees = [];

        $http.get("/api/groupMember/" + $scope.currentGroup.guid)
            .then(function(response) {
                $scope.rawgroupedEmployees = response.data.data;

                var requestCount = 0;

                if ($scope.rawgroupedEmployees.length > 0) {
                    angular.forEach($scope.rawgroupedEmployees, function(rawEmployee) { // Gets names of the grouped employees because that information isn't given to us by the API
                        requestCount++;

                        $http.get("/api/employee/" + rawEmployee)
                            .then(function(response) {
                                requestCount--;

                                var data = response.data.data;

                                var employee = {
                                    name: data.firstName + " " + data.lastName,
                                    netId: data.netID
                                };

                                $scope.siftedgroupedEmployees.push(employee);

                                if (requestCount === 0) {
                                    $scope.groupedEmployees = $scope.siftedgroupedEmployees;

                                    if (callback) {
                                        callback();
                                    }
                                }
                            }, function(response) {
                                $scope.logError(response.data);
                            });
                    });
                } else {
                    $scope.groupedEmployees = $scope.siftedgroupedEmployees;

                    if (callback) {
                        callback();
                    }
                }
            }, function(response) {
                $scope.logError(response.data);
            });
    };

    $scope.siftEmployees = function() { // Remove employees from the ungrouped list if they're grouped in currentGroup
        angular.forEach($scope.groupedEmployees, function(employee) { // Remove ungrouped employees that are already in the group
            angular.forEach($scope.siftedUngroupedEmployees, function(ungroupedEmployee) {
                if (employee.netId == ungroupedEmployee.netId) {
                    var index = $scope.siftedUngroupedEmployees.indexOf(ungroupedEmployee);

                    $scope.siftedUngroupedEmployees.splice(index, 1);
                    return;
                }
            });
        });

        $scope.ungroupedEmployees = $scope.siftedUngroupedEmployees; // Transfer the sifted employees into the list that actually displays
    };

    $scope.addToGroup = function() { // Add employees to the permission group from the ungrouped employees
        var requestCount = 0;
        if ($scope.checkSelectedUngrouped()) {
            $scope.showLoading();
        }

        for (var i = $scope.ungroupedEmployees.length - 1; i >= 0; i--) { // Work backwards so we don't get spliching
            var employee = $scope.ungroupedEmployees[i];

            if (employee.selected) {
                requestCount++;

                $http.post("/api/groupMember", $.param({
                        netId: employee.netId,
                        group: $scope.currentGroup.guid
                    }))
                    .then(function(response) { // Success
                        requestCount--;

                        if (requestCount === 0) {
                            $scope.reloadData();
                        }
                    }, function(response) { // Error
                        swal("Error", "There was an error adding employees!", "error");
                    });
            }
        }
    };

    $scope.removeFromGroup = function() { // Remove selected employees from the permission group
        var requestCount = 0;
        if ($scope.checkSelectedGrouped()) {
            $scope.showLoading();
        }

        for (var i = $scope.groupedEmployees.length - 1; i >= 0; i--) { // Work backwards so we don't get spliching
            var employee = $scope.groupedEmployees[i];

            if (employee.selected) {
                requestCount++;

                $http.delete("/api/groupMember/" + employee.netId + "/" + $scope.currentGroup.guid)
                    .then(function(response) { // Success
                        requestCount--;

                        if (requestCount === 0) {
                            $scope.reloadData();
                        }
                    }, function(response) { // Error
                        swal("Error", "There was an error removing employees!", "error");
                    });
            }
        }
    };

    $scope.addAllToGroup = function() { // Add all ungrouped employees to the current permission group
        var requestCount = 0;
        $scope.showLoading();

        angular.forEach($scope.ungroupedEmployees, function(employee) {
            requestCount++;

            $http.post("/api/groupMember", $.param({
                    netId: employee.netId,
                    group: $scope.currentGroup.guid
                }))
                .then(function(response) { // Success
                    requestCount--;

                    if (requestCount === 0) {
                        $scope.reloadData();
                    }
                }, function(response) { // Error
                    swal("Error", "There was an error adding all employees!", "error");
                });
        });
    };

    $scope.removeAllFromGroup = function() { // Remove all employees from the current permission group
        var requestCount = 0;
        $scope.showLoading();

        angular.forEach($scope.groupedEmployees, function(employee) {
            requestCount++;

            $http.delete("/api/groupMember/" + employee.netId + "/" + $scope.currentGroup.guid)
                .then(function(response) { // Success
                    requestCount--;

                    if (requestCount === 0) {
                        $scope.reloadData();
                    }
                }, function(response) { // Error
                    swal("Error", "There was an error removing all employees!", "error");
                });
        });
    };

    $scope.makeGroup = function() {
        swal({
            title: "New Permission Group",
            text: "Enter the new permission group's name:",
            type: "input",
            showCancelButton: true,
            closeOnConfirm: false,
            inputPlaceholder: "Name",
            showLoaderOnConfirm: true
        }, function(inputValue) {
            if (inputValue === false) {
                return false;
            }

            if (inputValue === "") {
                swal.showInputError("You need to write something!");
                return false;
            }

            var duplicate = false;

            angular.forEach($scope.permissionGroups, function(group) {
                if (inputValue == group.name) {
                    duplicate = true;
                    swal.showInputError("A group already exists with that name!");
                    return;
                }
            });

            if (!duplicate) {
                $http.post("/api/group", $.param({
                        area: $window.areaGuid,
                        name: inputValue
                    }))
                    .then(function(response) { // Success
                        var group = {
                            area: $window.areaGuid,
                            name: inputValue
                        };

                        $scope.getRawData(function() {
                            $scope.reloadData(function() {
                                $scope.setCurrentGroup($scope.findGroup(group.name)); // Set the new group as the current group

                                swal("Success!", "The group was successfully created.", "success");
                            });
                        });
                    }, function(response) { // Error
                        swal.showInputError("There was an error creating the group!");
                    });
            }
        });
    };

    $scope.renameGroup = function() {
        swal({
            title: "Rename Permission Group",
            text: "Enter a new name for the " + $scope.currentGroup.name + " Group:",
            type: "input",
            showCancelButton: true,
            closeOnConfirm: false,
            inputPlaceholder: "Name",
            showLoaderOnConfirm: true
        }, function(inputValue) {
            if (inputValue === false) {
                return false;
            }

            if (inputValue === "") {
                swal.showInputError("You need to write something!");
                return false;
            }

            var duplicate = false;

            angular.forEach($scope.permissionGroups, function(group) {
                if (inputValue == group.name) {
                    duplicate = true;
                    swal.showInputError("A group already exists with that name!");
                    return;
                }
            });

            if (!duplicate) {
                $http.put("/api/group/" + $scope.currentGroup.guid, $.param({
                        name: inputValue
                    }))
                    .then(function(response) { // Success
                        $scope.getRawData(function() {
                            $scope.reloadData(function() {
                                $scope.setCurrentGroup($scope.findGroup(inputValue)); // Set the new group as the current group

                                swal("Success!", "The group was successfully renamed.", "success");
                            });
                        });
                    }, function(response) { // Error
                        swal.showInputError("There was an error renaming the group!");
                    });
            }
        });
    };

    $scope.deleteGroup = function() {
        swal({
            title: "Are you sure?",
            text: "Do you really want to delete the " + $scope.currentGroup.name + " permission group?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
            closeOnConfirm: false,
            showLoaderOnConfirm: true
        }, function() {
            $http.delete("/api/group/" + $scope.currentGroup.guid)
                .then(function(response) { // Success function
                    init();
                    swal("Deleted!", "The " + $scope.currentGroup.name + " permission group has been deleted.", "success");
                }, function(response) { // Error function
                    swal.showInputError("There was an error deleting the group");
                });
        });
    };

    var init = function() {
        $scope.getRawData(function() {
            var rawGroup = $filter("orderBy")($scope.rawGroups.data, "Name")[0]; // Select the first, alphabetically-sorted group as the current group
            $scope.currentGroup = {
                guid: rawGroup.Guid,
                name: rawGroup.Name
            };

            $scope.reloadData();
        });
    };

    init(); // Initialize on page load
});
