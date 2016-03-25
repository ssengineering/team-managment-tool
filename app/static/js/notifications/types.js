var app = angular.module('tmt_page', []);

app.controller("notifCtrl", function($scope, $http) {

	$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
	$http.defaults.headers.put["Content-Type"] = "application/x-www-form-urlencoded";

	$scope.newType = "";

	$scope.resources = [];
	$scope.types = [];

	// Create a new notification type
	$scope.addType = function() {
		if($scope.newType == undefined || $scope.newType == "") {
			return;
		}

		// Create notification type
		$http.post("/api/notificationType", $.param({name: $scope.newType})).success(function(data) {
			$scope.init(false);
			$scope.newType = "";
		})
		.error(function(data) {
			alert("An error occurred trying to add the " + $scope.newType + " notification type. Please try again");
		});
	};

	$scope.deleteType = function(guid) {
		if(confirm("Are you sure you want to delete this notification type? It can cause permanent data loss!")) {
			$http.delete("/api/notificationType/" + guid).success(function(data) {
				$scope.init(false);
			});
		}
	};

	// Set permission dropdowns to the appropriate settings
	$scope.setPermissionDropdowns = function(typeGuid, permType) {
		var typeIndex = -1;
		for(var i=0; i < $scope.types.length; i++) {
			if($scope.types[i].guid == typeGuid) {
				typeIndex = i;
				break;
			}
		}
		switch(permType) {
			case "none":
			case "admin":
				$scope.types[typeIndex].verbObj = null;
				$scope.types[typeIndex].resourceObj = null;
				break;
			case "permission":
				$scope.types[typeIndex].resourceObj = $scope.returnResource($scope.resources[0].guid);
				$scope.types[typeIndex].verbObj = $scope.chooseResource($scope.types[typeIndex].resourceObj, typeGuid);
				break;
		}
	};

	// Make updates to a notification type
	$scope.updateType = function(type) {
		var data = {name: type.name};

		if(type.permType == "admin") {
			data.resource = "admin";
			data.verb = null;
		} else if(type.permType == "none") {
			data.resource = null;
			data.verb = null;
		} else {
			data.resource = type.resourceObj.guid;
			data.verb = type.verbObj.verb;
		}

		$http.put("/api/notificationType/" + type.guid, $.param(data))
		.error(function(data) {
			alert("Failed to update the " + type.resource.name + " resource! Please try again.");
			$scope.getSingleType(type.guid);
		});
	};

	// Retrieves data on a single notification type and updates the changes
	$scope.getSingleType = function(guid) {
		$http.get("/api/notificationType/" + guid).success(function(data) {
			var perm = "none";
			if(data.data.resource == "admin") {
				perm = "admin";
			} else if(!(data.data.resource == "" || data.data.resource == null)) {
				perm = "permission";
			}
			var verbObj = {};
			var resourceObj = {};
			var verbs = [];
			if(perm == "permission") {
				resourceObj = $scope.returnResource(data.data.resource);
				verbs = resourceObj.verbs;
				for(var j=0; j < resourceObj.verbs.length; j++) {
					if(resourceObj.verbs[j].verb == data.data.verb) {
						verbObj = resourceObj.verbs[j];
						break;
					}
				}
			}
			for(var i=0; i < $scope.types.length; i++) {
				if($scope.types[i].guid == guid) {
					$scope.types[i] = {
						guid: data.data.guid,
						name: data.data.name,
						resource: data.data.resource,
						resourceObj: resourceObj,
						verb: data.data.verb,
						verbs: verbs,
						verbObj: verbObj,
						permType: perm
					};
					if(Object.keys(resourceObj).length > 0) {
						$scope.chooseResource(resourceObj, data.data.guid);
					}
				}
			}
		});
	};

	// Pull notification types
	$scope.getTypes = function() {
		$http.get("/api/notificationType").success(function(data) {
			$scope.types = [];
			for(var i=0; i < data.data.length; i++) {
				var perm = "none";
				if(data.data[i].resource == "admin") {
					perm = "admin";
				} else if(!(data.data[i].resource == "" || data.data[i].resource == null)) {
					perm = "permission";
				}
				var verbObj = {};
				var resourceObj = {};
				var verbs = [];
				if(perm == "permission") {
					resourceObj = $scope.returnResource(data.data[i].resource);
					verbs = resourceObj.verbs;
					for(var j=0; j < resourceObj.verbs.length; j++) {
						if(resourceObj.verbs[j].verb == data.data[i].verb) {
							verbObj = resourceObj.verbs[j];
							break;
						}
					}
				}
				$scope.types.push({
					guid: data.data[i].guid,
					name: data.data[i].name,
					resource: data.data[i].resource,
					resourceObj: resourceObj,
					verb: data.data[i].verb,
					verbs: verbs,
					verbObj: verbObj,
					permType: perm
				});
				if(Object.keys(resourceObj).length > 0) {
					$scope.chooseResource(resourceObj, data.data[i].guid);
				}
			}
		});
	};

	// Finds the resource object with the given guid
	$scope.returnResource = function(guid) {
		for(var i=0; i < $scope.resources.length; i++) {
			if($scope.resources[i].guid == guid) {
				return $scope.resources[i];
			}
		}
		return {};
	};

	$scope.chooseResource = function(resource, type) {
		for(var i=0; i < $scope.types.length; i++) {
			if($scope.types[i].guid == type) {
				$scope.types[i].verbs = resource.verbs;
				return $scope.types[i].verbs[0];
			}
		}
	};

	// Initialize page
	$scope.init = function(loadResources) {
		if(loadResources) {
			$http.get("/api/resource").success(function(data) {
				// Sorts resources alphabetically by name
				$scope.resources = data.data.sort(function(a, b) {
					if(a.name < b.name) {
						return -1;
					} else {
						if(a.name == b.name) {
							return 0;
						} else {
							return 1;
						}
					}
				});
				$scope.getTypes();
			});
		} else {
			$scope.getTypes();
		}
	}

	$scope.init(true);
});
