(function(){
	var app = angular.module('tmt_page');
	// Create service for functions to be called before submitting requests to the API.
	// These can be overwritten/expanded as needed by infividual CRUD apps.
	app.factory('preApiCalls', [function(){
		var preApiCalls = {
			// Called before POST requests (i.e. database INSERTS)
			add : function(callback){
				callback();
			},
			// Called before PUT requests (i.e. database UPDATES)
			update : function(callback){
				callback();
			},
			// Called before DELETE requests (i.e. database DELETES -- or updating a DELETE flag)
			delete : function(callback){
				callback();
			},
			// The current item being acted upon -- gets set before the above functions are called
			item : null 
		};
		return preApiCalls;
	}]);

})();
