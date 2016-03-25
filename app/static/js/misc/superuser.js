function elevate(netId) {
	$.ajax({
		url: "/api/superuser/" + netId,
		data: {
			elevate: true
		},
		type: "PUT"
	}).success(function(data) {
		$('#superuserButton').text("Stop Superuser")
		$('#superuserButton').attr("onclick", "stop('"+netId+"')");
		location.reload();
	});
}

function stop(netId) {
	$.ajax({
		url: "/api/superuser/" + netId,
		data: {
			elevate: false
		},
		type: "PUT"
	}).success(function(data) {
		$('#superuserButton').text("Elevate to Superuser");
		$('#superuserButton').attr("onclick", "elevate('"+netId+"')");
		location.reload();
	});
}
