var app = angular.module("tmt_page");

app.controller("adminController", function($scope, $http, $window) {
    $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded"; // Make Angular send things encoded in the way we're expecting

	$scope.admins = [];
	$scope.superusers = [];

	$scope.loadAdmins = function() {
		$scope.admins = [];
		$http.get("/api/admin?area=" + $window.areaGuid).success(function(data) {
			for(var i=0; i < data.data.length; i++) {
				$scope.admins.push({netId: data.data[i]});
			}
		});
	}

	$scope.addAdmin = function(admin) {
		if(admin == undefined || admin == "") {
			return;
		}

		$http.post("/api/admin", $.param({
			area: $window.areaGuid,
			netId: admin
		})).success(function(data) {
			$scope.loadAdmins();
		});
	}

	$scope.deleteAdmin = function(admin) {
		if(admin == undefined || admin == "") {
			return;
		}

		$http.delete("/api/admin/" + admin + "/" + $window.areaGuid).success(function(data) {
			$scope.loadAdmins();
		});
	}

	$scope.loadSuperusers = function() {
		$scope.superusers = [];
		$http.get("/api/superuser").success(function(data) {
			for(var i=0; i < data.data.length; i++) {
				$scope.superusers.push({netId: data.data[i]});
			}
		});
	}

	$scope.addSU = function(su) {
		if(su == undefined || su == "") {
			return;
		}

		$http.post("/api/superuser", $.param({
			netId: su
		})).success(function(data) {
			$scope.loadSuperusers();
		});
	}

	$scope.deleteSU = function(su) {
		if(su == undefined || su == "") {
			return;
		}

		$http.delete("/api/superuser/" + su).success(function(data) {
			$scope.loadSuperusers();
		});
	}

	$scope.init = function() {
		$scope.loadAdmins();
		$scope.loadSuperusers();
	}

	$scope.init();
});
