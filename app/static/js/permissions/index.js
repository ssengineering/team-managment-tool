var app = angular.module("tmt_page");

app.controller("groupIndexController", function($scope, $http, $window, $filter) {
    $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded"; // Make Angular send things encoded in the way we're expecting

    // Search variables referenced in the .twig file
    $scope.searchVerbs = "";

    $scope.resources = []; // List of available resources with their verbs
    $scope.loading = false; // A bool to keep track of if we're showing a loading message so we don't accidentally close non-loading dialogues

    // Basically, this works for magic reasons so don't touch it
    $scope.update = function() { // Make Angular dynamically update the page to show changes
        if (!$scope.$$phase) { // Don't make Angular constipated
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

    $scope.setCurrentGroup = function(group) { // Called in the .twig view when a group is selected in the group dropdown
        $scope.currentGroup = group;

        $scope.populateVerbsList(function() {
            $scope.getVerbsByResource();
            $scope.update();
        });
    };

    $scope.setCurrentApp = function(app) { // Called in the .twig view when a group is selected in the app dropdown
        $scope.currentApp = app;

        $scope.populateVerbsList(function() {
            $scope.setCurrentVerb($scope.verbsList[0]);
        });
    };

    $scope.setCurrentVerb = function(verb) { // Called in the .twig view when a group is selected in the dropdown
        $scope.currentVerb = verb;
        $scope.update();
    };

    $scope.reloadData = function() {
        $scope.populateGroupsList();
        $scope.populateAppsList();
        $scope.update();
        $scope.hideLoading();
    };

    $scope.populateVerbsList = function(callback) {
        $scope.verbsList = []; // Clear the list of apps to start fresh

        if ($scope.resources.length > 0) { // Only proceed if there are groups in the area
            angular.forEach($scope.resources, function(resource) {
                if (resource.guid == $scope.currentApp.guid) {
                    angular.forEach(resource.verbs, function(verb) {
                        var newVerb = {
                            guid: verb.guid,
							description: verb.description,
                            name: verb.verb
                        };

                        $scope.verbsList.push(newVerb);
                    });
                }
            });

            $scope.verbsList = $filter("orderBy")($scope.verbsList, "name"); // Alphabetize those suckers

            if (callback) {
                callback();
            }
        }
    };

    $scope.populateAppsList = function() {
        $scope.appsList = []; // Clear the list of apps to start fresh

        if ($scope.resources.length > 0) { // Only proceed if there are groups in the area
            angular.forEach($scope.resources, function(app) {
                var newApp = {
                    guid: app.guid,
					description: app.description,
                    name: app.name
                };

                $scope.appsList.push(newApp);
            });

            $scope.appsList = $filter("orderBy")($scope.appsList, "name"); // Alphabetize those suckers
        }
    };

    $scope.getGroups = function(callback) {
        $scope.rawGroups = []; // Clean slate

        $http.get("/api/group?area=" + $window.areaGuid) // Get a list of all the groups the user has access to
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

    $scope.populateGroupsList = function(callback) {
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

            if (callback) {
                callback();
            }
        }
    };

    $scope.getVerbsByResource = function() {
        $scope.verbsByResource = []; // Clean slate

        angular.forEach($scope.resources, function(resource) { // Make an array for each resource
            var newResource = {
                name: resource.name,
                guid: resource.guid,
                verbs: []
            };

            $scope.verbsByResource.push(newResource);
        });

        $http.get("/api/permission/groups/" + $scope.currentGroup.guid) // Get a list of all the groups
            .then(function(response) {
                    angular.forEach(response.data.data, function(verb) {
                        for (var i = 0; i < $scope.verbsByResource.length; i++) {
                            if ($scope.verbsByResource[i].guid == verb.Resource) {
                                var newVerb = {
                                    name: verb.Verb
                                };

                                $scope.verbsByResource[i].verbs.push(newVerb);
                                break;
                            }
                        }
                    });

                    $scope.verbsByResource = $filter("orderBy")($scope.verbsByResource, "name"); // Alphabetize those suckers
                    $scope.update();
                },
                function(response) {
                    $scope.logError(response.data);
                });
    };

    $scope.grantPermission = function(callback) {
        $http.post("/api/permission", $.param({
                actor: $scope.currentGroup.guid,
                verb: $scope.currentVerb.name,
                resource: $scope.currentApp.guid
            }))
            .then(function(response) { // Success
                $scope.getVerbsByResource();
                swal("Applied!", "The " + $scope.currentGroup.name + " Group can now " + $scope.currentVerb.name + " the " + $scope.currentApp.name + " resource.", "success");
            }, function(response) { // Error
                swal("Error", "There was an error granting permissions! Can the " + $scope.currentGroup.name + " Group already " + $scope.currentVerb.name + " the " + $scope.currentApp.name + " resource?", "error");
            });
    };

    $scope.revokePermission = function(group, verb, resource, callback) {
        $http.delete("/api/permission/" + group.guid + "/" + verb.name + "/" + resource.guid)
            .then(function(response) { // Success
                $scope.getVerbsByResource();
                swal("Applied!", "The " + group.name + " Group can no longer " + verb.name + " the " + resource.name + " resource.", "success");
            }, function(response) { // Error
                swal("Error", "There was an error revoking permissions!", "error");
            });
    };

    $scope.promptForInfo = function() { // Checks if we've set a current app and current group before nabbing the verbs list
        if (!$scope.currentApp) {
            sweetAlert("Oops...", "Please select an app first!", "error");
        } else if (!$scope.currentGroup) {
            sweetAlert("Oops...", "Please select a permissions group first!", "error");
        }
    };

    $scope.confirmApply = function() {
        if ($scope.currentApp && $scope.currentGroup && $scope.currentVerb) {
            swal({
                title: "Are you sure?",
                text: "The " + $scope.currentGroup.name + " Group will be able to " + $scope.currentVerb.name + " the " + $scope.currentApp.name + " resource!",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Confirm",
                closeOnConfirm: false
            }, function() {
                $scope.grantPermission();
            });
        } else {
            if (!$scope.currentApp) {
                sweetAlert("Oops...", "Please select an app first!", "error");
            } else if (!$scope.currentGroup) {
                sweetAlert("Oops...", "Please select a permissions group first!", "error");
            } else if (!$scope.currentVerb) {
                sweetAlert("Oops...", "Please select a verb first!", "error");
            }
        }
    };

    $scope.confirmRevoke = function(verb, resource) {
        swal({
            title: "Are you sure?",
            text: "The " + $scope.currentGroup.name + " Group will no longer be able to " + verb.name + " the " + resource.name + " resource!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Confirm",
            closeOnConfirm: false
        }, function() {
            $scope.revokePermission($scope.currentGroup, verb, resource);
        });
    };

    var init = function() {
        $http.get("/api/resource").success(function(data) { // Get a complete list of resources
            for (var i = 0; i < data.data.length; i++) {
                $scope.resources.push(data.data[i]);
            }

            $scope.getGroups(function() {
                $scope.populateAppsList();
                $scope.populateGroupsList();

                $scope.setCurrentApp($scope.appsList[0]);
                $scope.setCurrentGroup($scope.permissionGroups[0]);
            });
        });
    };

    init(); // Initialize on page load
});
