var app = angular.module('tmt_page', []);

//Filter to convert mySQL timestamp to a format that can use the angular date filter.
app.filter('formatDate', function() {
	return function(input) {
			var d = new Date(input);
			return d;
	};
});


app.controller("notificationCtrl", function($scope, $http, $window) {

	$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
	$http.defaults.headers.put["Content-Type"] = "application/x-www-form-urlencoded";

	// Name, description, and api endpoint of new resource to be added
	$scope.name = "";
	$scope.description = "";
	$scope.api = "";

	

	/************************* Initialize ******************************/

 	$scope.read=[];//Make this two empty arrays of read vs. unread
	$scope.unread=[];
	$scope.notificationTypes=[];
	// Get and display all notifications
	$scope.getAll = function() {
		$http.get("/api/userNotification?netId="+$window.netId).success(function(data) {
			for(var i in data.data){
					//Some sort of logic to look for an anchor tag. If it finds one, pulls it out of the message, leaves message as was,
					//without link, and then appends another key to object: url. Then assign the link to that key.
					if(data.data[i].read == 1){
						$scope.read.push(data.data[i]);
					}
					else if(data.data[i].read == 0){
						$scope.unread.push(data.data[i]);
					}
			}	

			$http.get("/api/notificationType").success(function(data) {
					$scope.notificationTypes = data.data;
					
			//Make a double for loop that compares each notification tpe guid with each guid in the notificationTypes table to find the typeName and add it to the object.
			for(var k in $scope.read){
					for(var j in $scope.notificationTypes){
							if($scope.read[k].type == $scope.notificationTypes[j].guid){
									$scope.read[k].typeName = $scope.notificationTypes[j].name;
							}
					}
			}
			for(var l in $scope.unread){
					for(var m in $scope.notificationTypes){
							if($scope.unread[l].type == $scope.notificationTypes[m].guid){
									$scope.unread[l].typeName = $scope.notificationTypes[m].name;
							}
					}
			}

			});


		});
	}

	$scope.getAll();

	$scope.markRead = function(guid) {
			$http.put("/api/userNotification/"+$window.netId+"/"+guid).error(function(data) {
				alert("Message could not be marked as read.");	
			}).success(function(data) {
			for(var n in $scope.unread){
				if($scope.unread[n].guid == guid){
					$scope.read.push($scope.unread[n]);
					$scope.unread.splice(n, 1);
					break;
				}

			}
		});
	}

	$scope.delete = function(guid) {
			$http.delete("/api/userNotification/"+$window.netId+"/"+guid).error(function(data) {
					alert("Message could not be deleted.");
			}).success(function(data) {
				for(var p in $scope.read){
					if($scope.read[p].guid == guid){
						$scope.read.splice(p, 1);
						break;
					}
				}	
			});
	}

	


});
