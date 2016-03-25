var app = angular.module('tmt_page', []);

app.controller("quicklinksindexCtrl", function($scope, $http, $window) {

	$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
	$http.defaults.headers.put["Content-Type"] = "application/x-www-form-urlencoded";

	$scope.success = false;
	$scope.failure = false;
	$scope.successText = "";
	$scope.failureText = "";

	// Toggles the success and failure alerts
	$scope.toggleAlerts = function(success, text) {
		if(success) {
			$scope.success = true;
			$scope.failure = false;
			$scope.successText = text;
		} else {
			$scope.success = false;
			$scope.failure = true;
			$scope.failureText = text;
		}
	}

	// Initialize list of links
	$scope.links = [];

	// Pull in the user's links
	$scope.getLinks = function() {
		$http.get("/api/quicklinks?netId=" + $window.netId).success(function(data) {
			$scope.links = data.data;
		})
		.error(function(data) {
			$scope.toggleAlerts(false, "Could not retrieve quicklinks. Please refresh the page.");
		});
	};

	// Function to add a new link
	$scope.add = function() {
		$http.post("/api/quicklinks", $.param({"name": "", "url": "", "netId": $window.netId})).success(function(data) {
			$scope.links.push(data.data);
		})
		.error(function(data) {
			$scope.failure = true;
		});
	};

	// Function to save the user's links
	$scope.save = function() {
		if($scope.links.length < 1) {
			$scope.toggleAlerts(false, "No links to update!");
		}
		var successes = 0;
		var failures = 0;
		for(var i=0; i < $scope.links.length; i++) {
			$http.put("/api/quicklinks/" + $scope.links[i].guid, $.param($scope.links[i])).success(function(data) {
				successes++;
				if(successes + failures == $scope.links.length) {
					if(failures > 0) {
						$scope.toggleAlerts(false, "Unable to update all links. Please try again.");
					} else {
						$scope.toggleAlerts(true, "All links successfully updated!");
					}
				}
			})
			.error(function(data) {
				failures++;
				if(successes + failures == $scope.links.length) {
					$scope.toggleAlerts(false, "Unable to update all links. Please try again.");
				}
			});
		}
	};

	// Function to remove a link
	$scope.remove = function(guid) {
		$http.delete("/api/quicklinks/" + guid).success(function(data) {
			for(var i=0; i < $scope.links.length; i++) {
				if($scope.links[i].guid == guid) {
					$scope.links.splice(i, 1);
				}
			}
			$scope.toggleAlerts(true, "Link was removed successfully!");
		})
		.error(function(data) {
			$scope.toggleAlerts(false, "Unable to remove link. Please try again.");
		});
	};

	$scope.getLinks();
});
