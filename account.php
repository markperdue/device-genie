<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

$api = '/rest/v1/devices?query=' . urlencode($query);
$url = $dc_base_url . $api . "&checked_out_to=" . $loggedInUser->username;
$devices = @file_get_contents("$url", false);
if ($devices === FALSE) {
	$devices = NULL;
}
else {
	$devices = simplexml_load_string($devices);
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Account</title>
	<link rel="stylesheet" type="text/css" href="genie.css">
</head>
<body>
    <?php include 'navbar-search.php'; ?>
    <div id="margin-top-medium"></div>
	<div id="content-medium">
		<div class="colored-bg-round">
			<div id="content-nav-bar">
				<a class="content-nav-button-selected-left" href="account.php">Home</a> |
				<a class="content-nav-button" href="user_settings.php">Settings</a>
			</div>
			<div class="padding-top-medium"></div>
			<div style="text-align: center;">
				<h1>Hello <?=$loggedInUser->displayname; ?>.</h1>
				<?php if ($devices === NULL): ?>
					There was a problem connecting to the database. Please try again later.
					<br/><br/>
				<?php else: ?>
					<h2>You currently have <?= count($devices); ?> <?=(count($devices) > 1 || count($devices) == 0)?'items':'item';?> checked out.</h2>
					<br/>
					<?php if (count($devices) > 0): ?>
						<?php foreach($devices as $device): ?>
						<a href="device.php?id=<?= $device->device_id; ?>"><?= $device->device_id; ?></a>
						<br/>
						<?php endforeach; ?>
					<?php else: ?>
						To get started, type in the device you are looking for in the search bar above.
					<?php endif; ?>
					<br/><br/>
					Quick link to view the inventory for <a href="search.php?query=francisco">San Francisco</a>.
					<br/><br/>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php include 'footer.php'; ?>
</body>
</html>
