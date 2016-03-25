var app = angular.module('tmt_page', []);

app.controller("notificationspreferencesCtrl", function($scope, $http, $window) {

	$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
	$http.defaults.headers.put["Content-Type"] = "application/x-www-form-urlencoded";

	$scope.types = [];
	$scope.methods = [];

	$scope.setMethod = function(typeGuid, method) {
		for(var i in $scope.types) {
			if($scope.types[i].guid == typeGuid) {
				var previousMethod = $scope.types[i].method;
				$scope.types[i].method = method;
				$http.delete("/api/notificationPreference/" + $window.netId + "/" + typeGuid + "/" + $window.areaGuid).success(function(data) {
					if(method != "none") {
						var pref = {
							netId: $window.netId,
							type: typeGuid,
							method: method,
							area: $window.areaGuid
						};
						$http.post("/api/notificationPreference", $.param(pref))
						.error(function(data) {
							$scope.types[i].method = previousMethod;
							alert("An error occurred while updating your preference for this notification");
						});
					}
				});
				return;
			}
		}
	}

	$scope.init = function() {
		// Get notification methods, e.g. ("email", "onsite", "all")
		$http.get("/api/notificationMethod").success(function(data) {
			$scope.methods = data.data;
			$scope.methods.push("none"); // Add the "none" option

			// Retrieve the user's permissions
			$http.get("/api/permission/user/" + $window.netId + "/" + $window.areaGuid).success(function(data) {
				var permissions = data.data;
				// Get all the notification types
				$http.get("/api/notificationType").success(function(data) {
					var types = data.data;

					// Remove types the user is not authorized to access
					if(!($window.isAdmin || $window.isSU)) {
						for(var i=types.length-1; i >= 0; i--) {
							// skip if no permission is required
							if(types[i].resource == "" || types[i].resource == null) {
								continue;
							} 
							// They are not admin and it requires admin permission, so remove
							if(types[i].resource == "admin") {
								types.splice(i, 1);
								continue;
							}
							// Check that they have permission
							var found = false;
							for(var j in permissions) {
								if(permissions[j].Resource == types[i].resource && permissions[j].Verb == types[i].verb) {
									found = true;
									break;
								}
							}
							// They do not have permission, remove this type from the list
							if(!found) {
								types.splice(i, 1);
							}
						}
					}
					// Add types
					for(var k=0; k < types.length; k++) {	
						$scope.types.push({
							guid: types[k].guid,
							name: types[k].name,
							method: "none"
						});
					}

					// Set the dropdown to the correct method
					$http.get("/api/notificationPreference?netId=" + $window.netId + "&area=" + $window.areaGuid).success(function(data) {
						for(var i in $scope.types) {
							for(var j in data.data) {
								if($scope.types[i].guid == data.data[j].type) {
									$scope.types[i].method = data.data[j].method;
									break;
								}
							}
						}
					});
				});
			});
		});
	};

	$scope.init();
});
