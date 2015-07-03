<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
?>

<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Admin</title>
	<link rel="stylesheet" type="text/css" href="genie.css">
</head>
<body>
    <?php include 'navbar-search.php'; ?>
    <div id="margin-top-medium"></div>
	<div id="content-medium">
		<div class="colored-bg-round">
			<div id="content-nav-bar">
				<a class="content-nav-button" href="admin_configuration.php">Configuration</a> |
				<a class="content-nav-button" href="admin_users.php">Users</a> |
				<a class="content-nav-button" href="admin_permissions.php">Permissions</a> |
				<a class="content-nav-button" href="admin_pages.php">Pages</a>
			</div>
			<div class="padding-top-medium"></div>
			<div style="text-align: center;">
				<h1>Administration</h1>
				<h2>Coming Soon.</h2>
				<br/>
			</div>
		</div>
	</div>
	<?php include 'footer.php'; ?>
</body>
</html>
