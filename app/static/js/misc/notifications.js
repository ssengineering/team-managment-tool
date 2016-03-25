toastr.options = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-bottom-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

connection.onmessage = function(event) {
    var data = JSON.parse(event.data);

    if("message" in data) {
		connection.send("received");
        toastr.success(data.message);
        refreshNotifications();
    } else if("ping" in data) {
		connection.send("received");
	}
};

connection.onclose = function(event) {
	console.log("Web socket closed");
}

$(document).ready(function() {
    refreshNotifications();
});

var refreshNotifications = function() {
    $("#notificationsDropdown").empty();
    populateNotifications();
};

var populateNotifications = function() {
    $("#notificationsDropdown").append("<a href='/notifications/index'><b>View All Notifications</b></a>");
    $("#notificationsDropdown").append("<a href='/notifications/preferences'><b>Notifications Preferences</b></a>");

    $.get("/api/userNotification?netId=" + notificationNetId + "&read=false", function(data) {
        var notifications = JSON.parse(data).data;

        if (notifications.length > 0) {
            for (var i = 0; i < notifications.length; i++) {
                $("#notificationsDropdown").append('<a id="' + notifications[i].guid + '" class="notificationsDropdownItem" href="#">' + notifications[i].message + '<span class="read-x">X</span></a>');

                $('#' + notifications[i].guid).click({
                    guid: notifications[i].guid
                }, function(event) {
                    $.ajax({
                        url: "/api/userNotification/" + notificationNetId + "/" + event.data.guid,
                        type: 'PUT',
                        success: function(result) {
                            // swal("", "Notification marked as read.", "success");
                            refreshNotifications();
                        }
                    });
                });
            }
        }

        if (notifications.length > 0) { // Display notification count in navigation header
            $("#notificationsDropdownHeader").text("Notifications (" + notifications.length + ")");
        } else {
            $("#notificationsDropdownHeader").text("Notifications");
        }
    });
};
