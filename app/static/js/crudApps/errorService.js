(function(){
	var app = angular.module('tmt_page');
	app.factory('error', [function(){
		var error = {};
		error.messages = [];
		return error;
	}]);
})();
