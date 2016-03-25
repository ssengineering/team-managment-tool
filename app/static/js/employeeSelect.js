(function(){
	var app = angular.module('tmt_page');

	// Service for loading the employee list
	app.factory('tmtEmployeeService', ['$http', '$rootScope', function($http, $rootScope){
		var service = {};
		service.list = [];
		service.refresh = function(scope) {
			$http.get('/api/mim/possible')
				.success(function(data, status){
					service.list = data.data;
					// Re-run the validators on the new list
					if (!scope.$$phase) {
						scope.$apply();
					}
				});
		};
		service.refresh($rootScope);
		return service;
	}]);

	app.filter('tmtFullNameFilter', function() {
		return function(input, fullName) {
			if (!fullName)
				return [];
			var matches = [];
			angular.forEach(input, function(employee){
				var tmpName = employee.firstName + " " + employee.lastName;
				tmpName.replace("\\s+","");
				fullName.replace("\\s+","");
				if (tmpName.toLowerCase() == fullName.toLowerCase()) {
					matches.push(employee);
				}
			});
			return matches;
		};
	});

	// Top-level directive to include a fuzzy search or select menu
	app.directive('tmtEmployee', ['$http', '$filter', function($http, $filter){
		return {
			restrict : 'A',
			scope : {
				// function that accepts a single paramter 'value' that is the employee
				// The function is called everytime the employee value is updated
				tmtCb: '=?',
				// Object of paramters that should be passed to the function alongside a 'value'
				tmtCbParams: '=?',
				// Similar to the previous function, but only called on blur
				tmtBlur : '=?',
				// Object of paramters that should be passed to the function alongside a 'value'
				tmtBlurParams : '=?',
				// Variable that should be updated to the selected employee object
				tmtVal : '=?',
				// Variable that should be updated to the selected employee's netId
				tmtNetid : '=?',
				// Variable for whether the input should be treated as required
				tmtRequired : '=?',
				// String value for the 'name' attribute of the input
				tmtName : '@?',
				// String list of classes that should be applied to the input
				tmtClass : '@?'
			},
			templateUrl : function(elem, attr) {
				if (attr.tmtEmployee == "select"){
					return "/static/html/employeeSelect.html";
				} else {
				   	return "/static/html/employeeSearch.html";
				}
			},
			controllerAs : 'empCtrl',
			controller : function(tmtEmployeeService, $filter, $scope, $element, $attrs){
				function readValFromAttributes(){
					if ($scope.tmtVal) {
						var employee = $filter('filter')(tmtEmployeeService.list, {netID: $scope.tmtNetid}, true)[0];
						$scope.value = employee ? employee.firstName + " " + employee.lastName : $scope.tmtNetid;
						$scope.tmtVal = employee;
						if($scope.tmtVal !== undefined) {
							$scope.value = $scope.tmtVal.firstName + " " + $scope.tmtVal.lastName;
						}
					} else {
						var employee = $filter('filter')(tmtEmployeeService.list, {netID: $scope.tmtNetid}, true)[0];
						$scope.value = employee ? employee.firstName + " " + employee.lastName : $scope.tmtNetid;
						$scope.tmtVal = employee;
					}
				}
				$scope.$watch('tmtVal', readValFromAttributes);
				$scope.$watch('tmtNetid', readValFromAttributes);
				$scope.service = tmtEmployeeService;
				$scope.$watch('service.list', readValFromAttributes);
				$scope.suggestions = [];
				$scope.tempIndex = -1;

				function addFullNames(list) {
					for (var i = 0; i < list.length; i++){
						list[i].fullName = list[i].firstName + " " + list[i].lastName;
					}
					return list;
				}

				$scope.kickout = function(value){
					$scope.tempIndex = -1;
					var list = addFullNames($scope.service.list);
					var options = $filter('filter')(list, {$ : value}, false);
					var selected = (options && options.length == 1) ? options[0] : null;
					if (!selected) {
						return;
					}
					$scope.tmtVal = selected; 
					$scope.tmtNetid = selected ? selected.netID : null;
					if ($scope.tmtCb) {
						if ($scope.tmtCbParams) {
							$scope.tmtCb(selected, $scope.tmtCbParams);
						} else {
							$scope.tmtCb(selected, $scope.tmtCbParams);
						}
					}
				};

				// Kick out to the value/callback function specified in the HTML
				$scope.$watch('value', function(value) {
					var matches = $filter('tmtFullNameFilter')($scope.service.list, value, true);
					if (matches.length == 1){
						$scope.kickout(value);
					}
					$scope.updateSuggestions();
				});

				// Enable keyboard interaction with the list of suggestions
				$scope.checkKeyDown = function(event) {
					// Down key
					if (event.keyCode===40) {
						event.preventDefault();
						if ($scope.tempIndex+1 !== $scope.suggestions.length) {
							$scope.tempIndex++;
						}
					}
					// Up key
					else if(event.keyCode===38) {
						event.preventDefault();
						if ($scope.tempIndex-1 !== -2) {
							$scope.tempIndex--;
						}
					}
					// Enter
					else if (event.keyCode===13) {
						if ($scope.tempIndex != -1) {
							event.preventDefault();
							$scope.value = $scope.suggestions[$scope.tempIndex].fullName;
							$($element).focus();
							$scope.kickout($scope.value);
						} else {
							if ($scope.suggestions.length == 1 && $scope.value!=$scope.suggestions[0].fullName) {
								event.preventDefault();
								$scope.value = $scope.suggestions[0].fullName;
								$($element).focus();
							} 
							$scope.kickout($scope.value);
						}
					}

				}

				// Copy the highlighted suggestion into the input
				$scope.updateSelected = function() {
					$scope.value = $scope.suggestions[$scope.tempIndex].fullName;
				}

				// Update the list of suggestions
				$scope.updateSuggestions = function(){
					$scope.tempIndex = -1;
					if ($scope.value) {
						var suggestions = $filter('filter')($scope.service.list, {$ : $scope.value}, false);
						angular.forEach(suggestions, function(suggestion) {
							suggestion.fullName = suggestion.firstName + " " + suggestion.lastName;
						});
						$scope.suggestions = $filter('limitTo')($filter('orderBy')(suggestions, 
									'fullName', false), 5);
					} else {
						$scope.suggestions = [];
					}
				}
			}
		};
	}]);

	// Directive used for validation of the text input fuzzy search
	app.directive('tmtEmployeeValidate', ['$filter', function($filter){
		return {
			require: 'ngModel',
			link: function(scope, elem, attr, ngModel) {
				function addFullNames(list) {
					for (var i = 0; i < list.length; i++){
						list[i].fullName = list[i].firstName + " " + list[i].lastName;
					}
					return list;
				}

				function validate(value) {
					var valid, list = addFullNames(scope.service.list);
					// If the field is empty, or the employee list hasn't been loaded, consider valid
					if (!value || list.length == 0){
						valid = true;
					} else {
						// Set valid if the value uniquely identifies an employee
						var options = $filter('filter')(list, {$ : value}, false);
						var options_strict = $filter('filter')(list, {$ : value}, false);
						valid = (options.length) === 1 && (options_strict.length === 1);
					}
					ngModel.$setValidity('employee', valid);
					return value;
				}

				// For DOM -> model validation
				ngModel.$parsers.unshift(function(value) {
					return validate(value);
				});

				// For model -> DOM validation
				ngModel.$formatters.unshift(function(value) {
					return validate(value);
				});

				// Force validation when the employee list is loaded/updated
				scope.$watch('service.list', function(value){
					for (var i = 0; i < ngModel.$parsers.length; i++) {
						ngModel.$parsers[i](ngModel.$modelValue);
					}
					for (var i = 0; i < ngModel.$formatters.length; i++) {
						ngModel.$formatters[i](ngModel.$modelValue);
					}
				});
			}
		};	
	}]);

})();

