var app = angular.module('tmt_page', []);

app.controller("resourceCtrl", function($scope, $http) {

	$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
	$http.defaults.headers.put["Content-Type"] = "application/x-www-form-urlencoded";

	// Name, description, and api endpoint of new resource to be added
	$scope.name = "";
	$scope.description = "";
	$scope.api = "";

	// The array of resources populated by getAll
	$scope.resources = [];

	// Handle adding resources
	$scope.addText = "Add Resource";
	$scope.adding = false;
	$scope.add = function() {
		if($scope.addText == "Add Resource") {
			$scope.addText = "Close";
			$scope.adding = true;
		} else {
			$scope.addText = "Add Resource";
			$scope.adding = false;
		}
	};

	/************************* Resources ******************************/

	$scope.addResource = function() {
		if($scope.name.length < 1 || $scope.description.length < 1 || $scope.api.length < 1) {
			return;
		}

		var n = $scope.name;
		var d = $scope.description;
		var a = $scope.api;
		$scope.name = "";
		$scope.description = "";
		$scope.api = "";
		$http.post("/api/resource", $.param({name: n, description: d, api: a})).success(function(data) {
			$scope.getAll();
		});
	}

	$scope.updateResource = function(resource) {
		if(resource.name.length < 1 || resource.description.length < 1 || resource.apiEndpoint.length < 1) {
			return;
		}
		var data = {
			name: resource.name,
			description: resource.description,
			api: resource.apiEndpoint
		};
		$http.put("/api/resource/"+resource.guid, $.param(data)).success(function(data) {
			$scope.get(resource.guid);
		});
	}

	$scope.deleteResource = function(guid) {
		if(confirm("Are you sure you want to delete this resource and associated verbs? It can cause loss of data!")) {
			$http.delete("/api/resource/"+guid).success(function(data) {
				$scope.getAll();
			});
		}
	}


	/************************* Verbs ******************************/

	$scope.insertVerb = function(verb, description, resourceGUID) {
		var data = {
			verb: verb,
			description: description,
			resourceGUID: resourceGUID
		};
		$http.post("/api/resourceVerb", $.param(data)).success(function(data) {
			$scope.getVerbs(resourceGUID);
		});
		verb = '';
		description = '';
	}

	$scope.updateVerb = function(verb) {
		$http.put("/api/resourceVerb/"+verb.guid, $.param({description: verb.description})).success(function(data) {
			$scope.getVerbs(verb.resourceGUID);
		});
	}

	$scope.getVerbs = function(resourceGUID) {
		$http.get("/api/resourceVerb/"+resourceGUID).success(function(data) {
			for(var res in $scope.resources) {
				if($scope.resources[res].guid == resourceGUID) {
					$scope.resources[res].verbs = data.data;
				}
			}
		});
	}

	// Deletes a verb
	$scope.deleteVerb = function(verb) {
		if(confirm("Are you sure you want to remove this verb? It may cause data loss!")) {
			$http.delete("/api/resourceVerb/"+verb.guid).success(function(data) {
				$scope.getVerbs(verb.resourceGUID);
			});
		}
	}


	/************************* Initialize ******************************/

	// Get and display all resources
	$scope.getAll = function() {
		$http.get("/api/resource").success(function(data) {
			$scope.resources = data.data;
		});
	}

	// Get a single resource so as not to have to populate the whole list again
	$scope.get = function(guid) {
		$http.get("/api/resource/"+guid).success(function(data) {
			// Find the resource in the list and apply changes
			for(var res in $scope.resources) {
				if(res.guid == data.data.guid) {
					$scope.resources[res] = data.data;
					break;
				}
			}
		});
	}	

	$scope.getAll();
});


// Directives

app.directive('popover', function() {
	return function(scope, element, attrs) {
		element.popover({
			placement: 'top',
			html: true
		});
	};
});

app.directive('modal', function() {
	return function(scope, element, attrs) {
		element.modal({
			backdrop: false,
			show: false
		});
	};
});
