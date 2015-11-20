<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("../models/db-settings.php");

function resultBlock($results,$error_count) {
	//Success block
	if (count($results) > 0) {
		if ($error_count > 0) {
			echo "<div id='alerts-error'><ul><li>There were <strong>".$error_count."</strong> errors.</li><li>Check database settings and try again.</li><br>";
		}
		else {
			echo "<div id='alerts-success'><ul>";
		}
		foreach ($results as $result) {
			echo "<li>".$result."</li>";
		}
		echo "</ul>";
		echo "</div>";
	}
}

if (isset($_GET["install"])) {
	$db_issue = false;
	$results = array();
	$error_count = 0;

	$permissions_sql = "
	CREATE TABLE IF NOT EXISTS `".$db_table_prefix."permissions` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(150) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY (`name`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	";

	$permissions_entry = "
	INSERT IGNORE INTO `".$db_table_prefix."permissions` (`name`) VALUES
	('New Member'),
	('Administrator');
	";

	$users_sql = "
	CREATE TABLE IF NOT EXISTS `".$db_table_prefix."users` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`user_name` varchar(50) NOT NULL,
	`display_name` varchar(50) NOT NULL,
	`password` varchar(225) NOT NULL,
	`email` varchar(150) NOT NULL,
	`activation_token` varchar(225) NOT NULL,
	`last_activation_request` int(11) NOT NULL,
	`lost_password_request` tinyint(1) NOT NULL,
	`active` tinyint(1) NOT NULL,
	`title` varchar(150) NOT NULL,
	`sign_up_stamp` int(11) NOT NULL,
	`last_sign_in_stamp` int(11) NOT NULL,
	PRIMARY KEY (`id`),
  	UNIQUE KEY (`user_name`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	";

	$user_permission_matches_sql = "
	CREATE TABLE IF NOT EXISTS `".$db_table_prefix."user_permission_matches` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`permission_id` int(11) NOT NULL,
	PRIMARY KEY (`id`),
  	UNIQUE KEY (`user_id`, `permission_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	";

	$user_permission_matches_entry = "
	INSERT IGNORE INTO `".$db_table_prefix."user_permission_matches` (`user_id`, `permission_id`) VALUES
	(1, 2);
	";

	$configuration_sql = "
	CREATE TABLE IF NOT EXISTS `".$db_table_prefix."configuration` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(150) NOT NULL,
	`value` varchar(150) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY (`name`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	";

	$configuration_entry = "
	INSERT IGNORE INTO `".$db_table_prefix."configuration` (`name`, `value`) VALUES
	('website_name', 'DeviceGenie'),
	('website_url', 'localhost/'),
	('email', 'noreply@righteousbanana.com'),
	('activation', 'false'),
	('resend_activation_threshold', '0'),
	('language', 'models/languages/en.php'),
	('template', 'models/site-templates/default.css');
	";

	$pages_sql = "CREATE TABLE IF NOT EXISTS `".$db_table_prefix."pages` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`page` varchar(150) NOT NULL,
	`private` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE KEY (`page`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;
	";

	$pages_entry = "INSERT IGNORE INTO `".$db_table_prefix."pages` (`page`, `private`) VALUES
	('account.php', 1),
	('activate-account.php', 0),
	('add_device.php', 1),
	('admin_configuration.php', 1),
	('admin_page.php', 1),
	('admin_pages.php', 1),
	('admin_permission.php', 1),
	('admin_permissions.php', 1),
	('admin_user.php', 1),
	('admin_users.php', 1),
	('checkin.php', 1),
	('checkout.php', 1),
	('device.php', 0),
	('edit.php', 0),
	('error.php', 0),
	('footer.php', 0),
	('forgot-password.php', 0),
	('index.php', 0),
	('login.php', 0),
	('logout.php', 1),
	('navbar-search.php', 0),
	('navbar.php', 0),
	('register.php', 0),
	('resend-activation.php', 0),
	('search.php', 0),
	('searchbar.php', 0),
	('user_settings.php', 1);
	";

	$permission_page_matches_sql = "CREATE TABLE IF NOT EXISTS `".$db_table_prefix."permission_page_matches` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`permission_id` int(11) NOT NULL,
	`page_id` int(11) NOT NULL,
	PRIMARY KEY (`id`),
  	UNIQUE KEY (`permission_id`, `page_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;
	";

	$permission_page_matches_entry = "INSERT IGNORE INTO `".$db_table_prefix."permission_page_matches` (`permission_id`, `page_id`) VALUES
	(1, 1),
	(1, 11),
	(1, 12),
	(1, 21),
	(1, 28),
	(2, 1),
	(2, 3),
	(2, 4),
	(2, 5),
	(2, 6),
	(2, 7),
	(2, 8),
	(2, 9),
	(2, 10),
	(2, 11),
	(2, 12),
	(2, 21),
	(2, 28);
	";

	$device_list_sql = "CREATE TABLE IF NOT EXISTS `device_list` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`device_id` varchar(32) NOT NULL,
	`available` bit(1) NOT NULL DEFAULT b'1',
	`type` varchar(32) NOT NULL,
	`manufacturer` varchar(32) NOT NULL,
	`model` varchar(32) NOT NULL,
	`model_version` varchar(32) NOT NULL,
	`os_type` varchar(32) NOT NULL,
	`os_version` varchar(32) NOT NULL,
	`dev_provisioned` bit(1) NOT NULL,
	`jailbroken` bit(1) NOT NULL,
	`location` varchar(64) NOT NULL,
	`manager_dept` varchar(64) DEFAULT NULL,
	`manager_name` varchar(64) DEFAULT NULL,
	`checked_out_to` varchar(64) DEFAULT NULL,
	`checked_out_date` timestamp NULL DEFAULT NULL,
	`udid` varchar(64) DEFAULT NULL,
	`note` varchar(255) DEFAULT NULL,
	`carrier` varchar(32) DEFAULT NULL,
	`phone_number` varchar(32) DEFAULT NULL,
	`recovery_mode_enabled` bit(1) NOT NULL,
	`checked_out_count` int(10) unsigned NOT NULL DEFAULT '0',
	`image_path` varchar(255) DEFAULT NULL,
	`created_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	`changed_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE KEY (`device_id`),
	FULLTEXT KEY (`device_id`,`type`,`manufacturer`,`model`,`model_version`,`os_type`,`os_version`,`location`,`carrier`),
	FULLTEXT KEY (`type`,`manufacturer`,`model`),
	FULLTEXT KEY (`type`,`manufacturer`,`model`,`os_type`,`location`,`carrier`),
	FULLTEXT KEY (`device_id`,`type`,`manufacturer`,`model`,`os_type`,`location`,`carrier`),
	FULLTEXT KEY (`device_id`,`type`,`manufacturer`,`model`,`model_version`,`os_type`,`location`,`carrier`),
	FULLTEXT KEY (`device_id`,`type`,`manufacturer`,`model`,`model_version`,`os_type`,`os_version`,`location`,`carrier`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	";

	$device_list_activity_sql = "CREATE TABLE IF NOT EXISTS `device_list_activity` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`user` varchar(50) NOT NULL,
	`activity` varchar(32) NOT NULL,
	`device_id` varchar(32) NOT NULL,
	`note` varchar(255) DEFAULT NULL,
	`created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	KEY (`device_id`),
	KEY (`user`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	";

	$stmt = $mysqli->prepare($configuration_sql);
	if ($stmt->execute()) {
		$results[] = "Added ".$db_table_prefix."configuration table";
	}
	else {
		$results[] = "<strong>Unable to add ".$db_table_prefix."configuration table</strong>";
		$error_count++;
		$db_issue = true;
	}

	$stmt = $mysqli->prepare($configuration_entry);
	if ($stmt->execute()) {
		$results[] = "Configured ".$db_table_prefix."configuration table";
	}
	else {
		$results[] = "<strong>Error configuring ".$db_table_prefix."configuration table</strong>";
		$error_count++;
		$db_issue = true;
	}

	$stmt = $mysqli->prepare($permissions_sql);
	if ($stmt->execute()) {
		$results[] = "Added ".$db_table_prefix."permissions table";
	}
	else {
		$results[] = "<strong>Error adding ".$db_table_prefix."permissions table</strong>";
		$error_count++;
		$db_issue = true;
	}

	$stmt = $mysqli->prepare($permissions_entry);
	if ($stmt->execute()) {
		$results[] = "Configured ".$db_table_prefix."permissions table";
	}
	else {
		$results[] = "<strong>Error configurating ".$db_table_prefix."permissions table</strong>";
		$error_count++;
		$db_issue = true;
	}

	$stmt = $mysqli->prepare($user_permission_matches_sql);
	if ($stmt->execute()) {
		$results[] = "Added ".$db_table_prefix."user_permission_matches table";
	}
	else {
		$results[] = "<strong>Error adding ".$db_table_prefix."user_permission_matches table</strong>";
		$error_count++;
		$db_issue = true;
	}

	$stmt = $mysqli->prepare($user_permission_matches_entry);
	if ($stmt->execute()) {
		$results[] = "Created admin permission";
	}
	else {
		$results[] = "<strong>Error creating admin permission</strong>";
		$error_count++;
		$db_issue = true;
	}

	$stmt = $mysqli->prepare($pages_sql);
	if ($stmt->execute()) {
		$results[] = "Added ".$db_table_prefix."pages table";
	}
	else {
		$results[] = "<strong>Error adding ".$db_table_prefix."pages table</strong>";
		$error_count++;
		$db_issue = true;
	}

	$stmt = $mysqli->prepare($pages_entry);
	if ($stmt->execute()) {
		$results[] = "Created default page permissions";
	}
	else {
		$results[] = "<strong>Error creating default page permissions</strong>";
		$error_count++;
		$db_issue = true;
	}

	$stmt = $mysqli->prepare($permission_page_matches_sql);
	if ($stmt->execute()) {
		$results[] = "Added ".$db_table_prefix."permission_page_matches table";
	}
	else {
		$results[] = "<strong>Error adding ".$db_table_prefix."permission_page_matches table</strong>";
		$error_count++;
		$db_issue = true;
	}

	$stmt = $mysqli->prepare($permission_page_matches_entry);
	if ($stmt->execute()) {
		$results[] = "Added default access entries";
	}
	else {
		$results[] = "<strong>Error adding default access entries</strong>";
		$error_count++;
		$db_issue = true;
	}

	$stmt = $mysqli->prepare($users_sql);
	if ($stmt->execute()) {
		$results[] = "Added ".$db_table_prefix."users table";
	}
	else {
		$results[] = "<strong>Error adding ".$db_table_prefix."users table</strong>";
		$error_count++;
		$db_issue = true;
	}

	$stmt = $mysqli->prepare($device_list_sql);
	if ($stmt->execute()) {
		$results[] = "Added device_list table";
	}
	else {
		$results[] = "<strong>Error adding device_list table</strong>";
		$error_count++;
		$db_issue = true;
	}

	$stmt = $mysqli->prepare($device_list_activity_sql);
	if ($stmt->execute()) {
		$results[] = "Added device_list_activity table";
	}
	else {
		$results[] = "<strong>Error adding device_list_activity table</strong>";
		$error_count++;
		$db_issue = true;
	}


	if (!$db_issue) {
		$results[] = "<p><strong>Database setup completed successfully<br><br>Please delete the install folder before continuing</strong></p><a class='blue-button-install' href='../register.php' style='color: white;'>Create Admin Account</a><br><br>";
	}
	else {
		$results[] = "<p><a class='green-button' href='?install=true' style='color: white;'>Try Again</a></p>";
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Installation</title>
	<link rel="stylesheet" type="text/css" href="../genie.css">
	<script src='../models/funcs.js' type='text/javascript'>
	</script>
</head>
<body>
    <div id="margin-top-medium"></div>
	<div id="content-medium">
		<div style="text-align: center;">
			<div class="colored-bg-round">
				<div class="padding-top-medium"></div>
				<h1>Let's get things setup!</h1>
				<h2>We need to get some database tables created. This will take no time at all.</h2>
				
				<div id="regbox">
				    <?php if (!isset($_GET["install"])) echo "<a class='green-button' href='?install=true' style='color: white;'>Install</a><br/><br/>"; ?>
					
					<div id="alerts-container">
						<?php echo resultBlock($results,$error_count); ?>
					</div>
					<br><br>
				</div>
			</div>
		</div>
	</div>
	<?php include '../footer.php'; ?>
</body>
</html>