	</div>

		<div id="footer-bottom">
			<p>
				<a target='_blank' href="http://byui.edu/">BYU-Idaho</a>
				<a target='_blank' href="http://byuh.edu/">BYU-Hawaii</a>
				<a target='_blank' href="http://www.ldsbc.edu/">LDS Business College</a>
				<a target='_blank' href="http://lds.org/">The Church of Jesus Christ of Latter-day Saints</a>
			</p>
			<p><a target='_blank' href="http://home.byu.edu/home/copyright">Copyright&#169; 2011, All Rights Reserved</a></p>
		</div>
	</footer>
	<!--CONTENT END-->

	<script src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/template/js/libs/jquery-1.8.2.min.js"></script>
	<script src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/template/js/libs/raphael-min.js"></script>
	<script src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/template/js/script.js"></script>

	<script src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/template/js/libs/jquery-ui-1.9.0.custom.min.js"></script>
	<link rel="stylesheet" href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/template/css/jquery-ui-1.9.0.custom.css" />
	<link rel="stylesheet" href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/template/js/plugins/demo_tables.css"/>

	<link rel='stylesheet' href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/template/js/libs/timeentry/jquery.timeentry.css" />
	<script type='text/javascript' src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/template/js/libs/timeentry/jquery.timeentry.js"></script>
	<script type='text/javascript' src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/template/js/libs/jquery.mousewheel.js"></script>
	<script type='text/javascript' src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/template/js/libs/src/date.format.js"></script>
	<script type='text/javascript' src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/template/js/plugins/jquery.dataTables.min.js"></script>
	<script src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/templates/scripts/ckeditor/ckeditor.js"></script>

	<script>
		var notificationNetId = "<?php echo $netID ?>";
		var connection = new WebSocket("wss://<?php echo getenv('NOTIFICATIONS_URL'); ?>/listen?auth=<?php echo createJWT(); ?>");
	</script>

	<!--Notification System-->
	<link rel="stylesheet" href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/notify.css" />
	<link rel="stylesheet" href="https://<?php echo $_SERVER['SERVER_NAME']; ?>/static/css/misc/notifications.css" />
	<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
	<script src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/static/js/misc/notifications.js"></script>
	<script src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/includes/notify.js"></script>

	<!--[if lt IE 7 ]>
	<script src="template/js/libs/dd_belatedpng.js"></script>
	<script> DD_belatedPNG.fix('.arrow a, header h1, #search-button');</script>
	<![endif]-->
</body>
</html>
