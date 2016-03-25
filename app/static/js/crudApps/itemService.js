(function(){
	var app = angular.module('tmt_page');
	app.factory('items', ['$http', '$q', function($http, $q){
		var items = {};
		items.list = [];
		items.new_item = {};
		// This needs to be overwritten
		items.reload = function() {
			return {
				success : function(fn) {
					var list = [];
					var firstNames = ["John", "Jane", "Emily", "Matt", "Steve", "Stephen", "Jerome", "Ted", "Marshall", "Lily", "Robin"];
					var lastNames = ["Mosby", "Erickson", "Simpson", "Aldren", "Stinson", "Bing", "Buffay", "Geller", "Green", "Brown", "Smith"];
					var i = 1;
					for (i; i <= 50; i++) {
						list.push({
							id : i,
							netID : 'fake' + i,
							firstName : firstNames[Math.ceil(Math.random()*10)],
							nickname : "Hey, Dude!",
							lastName : lastNames[Math.ceil(Math.random()*10)],
							birthday : new Date(Math.ceil(Math.random()*10+1984), Math.floor(Math.random()*12), Math.ceil(Math.random()*28), 0, 0, 0, 0),
							problems : Math.ceil(Math.random()*100),
							awesome : Math.round(Math.random()) ? true : false
						});
					}	
					fn({status : "OK", data: list},200);
					return {error :function(){}}
				},
				error : function(fn) {
					return fn({status : "ERROR", message: "Testing"},500);
				}
			};
		};
		return items;
	}]);
})();
