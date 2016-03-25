/**
* 
* In order to use notify:
* (param-string) text - is the string you would like the user notified with, you can pass in a string with html to create those html elements in the notification as well
* (param-object) optionsObject - is a basic javascript object that has preset properties you can override
* 	(property-bool) clickToDismiss - This determines whether the notification fades away on its own or if the user must click it before it will disappear; default: false
* 	(property-int) duration - the number of milliseconds before the notification finishes fading out; default: 5000
* 	(property-string) status - accepts 'success', 'warning', 'failure', and 'info' which all affect the color scheme of the notification; default: 'info'
* 	(property-object) position - this allows you to determine where you would like the notification to be displayed on the page. This prevents any kind of auto positioning that would normally prevent notifications from covering each other. e.g. {'my': 'center center', 'at': 'center center', 'of': window} would place the center of the notification in the center of the user's window
* 	 	(property-string) my - defines which of the points of the current element (Acceptable horizontal values: "left", "center", "right". Acceptable vertical values: "top", "center", "bottom".) will be used for aligning the element
* 	 	(property-string) at - defines which point (Acceptable horizontal values: "left", "center", "right". Acceptable vertical values: "top", "center", "bottom".) the current element should aim at on the target element (defined by the 'of' parameter) to align itself with (or more exactly for example where its "left center" will attempt to align with)
* 		(property-jQuery selector/element/event/window) of - defines which element should be used as a point of reference when trying to align the 'my' variable with the 'at' variable
* 	(property-function) callBack - this is a callback function that runs as soon as the notification has finished fading out (can be used to log in the database that a user acknowledged a notification, etc.); default: function(){}
* 
* (return-jQuery object) - reference to the notification's jQuery object
* 
**/
function notify(text, optionsObject)
{
    // Do nothing if no text was passed for notification
    if (text)
    {
        // Set default parameters
        var clickToDismiss = false;
        var duration = 5000;
        var status = '';
        var position = false;
        var callBack = function()
        {
        };
        
        function getTrueHeightAndBuffer($notification)
        {
            var heightAndBuffer = 0;
            // Add the other notifications' height
            heightAndBuffer += $notification.height();
            // Add double the other notifications' padding-top to account for an assumed equivalent padding-bottom
            heightAndBuffer += 2*parseFloat($notification.css('padding-top'));
            // Add 6 to bottom, 2 for spacing the notifications from each other, and 4 to account for the otherNotifications' borders
            heightAndBuffer += 2*parseFloat($notification.css('border-top-width'));
            heightAndBuffer += 2;
            return heightAndBuffer;
        }
        
        // Set any optional parameters that have been passed
        if (optionsObject)
        {
            if (optionsObject['clickToDismiss'])
            {
                clickToDismiss = optionsObject['clickToDismiss'];
                duration = 500;
            }
            if (optionsObject['duration'])
            {
                duration = optionsObject['duration'];
            }
            if (optionsObject['status'])
            {
                status = optionsObject['status'];
            }
            if (optionsObject['position'])
            {
            	position = optionsObject['position'];
            }
            if (optionsObject['callBack'])
            {
                callBack = optionsObject['callBack'];
            }
        }
        
        var otherNotifications = $('.operationStatus:not(.positionedNotification)');
        // How much farther from the bottom should this notification be placed due to other notifications
        var additionalBottom = 0;
        for (var i=0; i < otherNotifications.length; i++)
        {
            additionalBottom += getTrueHeightAndBuffer($(otherNotifications[i]));
        }
        
        // Create notification div--using css defaults from notify.css
        var $alert = $('<div class="operationStatus '+status+'">'+text+'</div>').appendTo('body');
        
        if (position)
        {
        	$alert.css('bottom', 'auto');
        	$alert.css('right', 'auto');
        	$alert.position(position);
        	if (detectBrowser() != 'Firefox')
        	{
        		$alert.css('top', "-="+$(document).scrollTop());
        	}
        	$alert.addClass('positionedNotification');
        }
        
        function adjustOtherNotificationBottoms(toBeRemoved)
        {
            var notifications = $('.operationStatus:not(.positionedNotification)');
            var index = notifications.index(toBeRemoved)+1;
            var bottomToRemove = getTrueHeightAndBuffer($(toBeRemoved));
            for (index; index < notifications.length; index++)
            {
                $(notifications[index]).animate({'bottom': '-='+bottomToRemove}, {'duration': 400});
            }
        }
        
        // Depending on whether this ought to disappear only on click or not we do different things with the notification div
        if (clickToDismiss)
        {
            $alert.addClass('clickToDismissParent');
            $alert.append('<div class="clickToDismiss">**Click to Dismiss**</div>');
            $alert.click(function()
            {
                $(this).fadeOut(duration,function()
                {
                	if (!position)
                	{
                		adjustOtherNotificationBottoms(this);
                	}
                    callBack();
                    $(this).remove();
                });
            });
        }
        else
        {
            $alert.animate({'opacity': '0'}, {'duration': duration,'complete': function()
            {
            	if (!position)
                {
                	adjustOtherNotificationBottoms(this);
                }
                callBack();
                $(this).remove();
            }});
        }
        if (!position)
        {
        	var bottom = parseFloat($alert.css('bottom'));
        	$alert.css('bottom', (bottom+additionalBottom)+'px');
        }
    }
    $alert.css('z-index', '999');
    return $alert;
}

// This just returns the browser being used, I use it for css issues
function detectBrowser()
{
    function testCSS(prop)
    {
        return prop in document.documentElement.style;
    }

    // FF 0.8+
    var isFirefox = testCSS('MozBoxSizing');
    if (isFirefox)
        return "Firefox";

    // At least Safari 3+: "[object HTMLElementConstructor]"
    var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0;
    if (isSafari)
        return "Safari";

    // Chrome 1+
    var isChrome = !isSafari && testCSS('WebkitTransform');
    if (isChrome)
        return "Chrome";

    // Opera 8.0+
    var isOpera = !!(window.opera && window.opera.version);
    if (isOpera)
        return "Opera";

    // At least IE6
    var isIE =  /*@cc_on!@*/false || testCSS('msTransform');
    if (isIE)
        return "IE";
}