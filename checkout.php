<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
$device = $_GET['id'];

//Send PUT to checkout api
$ch = curl_init();
	
$api = '/rest/v1/device/';
$url = $dc_base_url . $api . $device . '/checkout';
$credentials = $loggedInUser->username . ":" . $loggedInUser->hash_pw;
$headers = array(
		"Content-type: application/xml;charset=\"utf-8\"",
		"Authorization: " . $credentials
);
	
// Set curl options to POST
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
$server_output = curl_exec($ch);
$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
	
$deviceurl = 'device.php?id='. $device;
$redirect_message = "Redirecting to device details in 5 seconds or click <a href='".$deviceurl."'>here</a>.";

if ($status_code === 200) {
	$header = "Success!";
	$message = "Device '".$device."' is now checked out to you.";
}
else {
	$header = "Oh boy...";
	$message = "There was an error. The server returned '".$status_code." - ".$server_output."'";
}

// Redirect to the device details page
header("Refresh: 5;url=$deviceurl");

?>

<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Checkout</title>
	<link rel="stylesheet" type="text/css" href="genie.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div id="margin-top-medium"></div>
	<div id="content-medium">
		<div class="colored-bg-round">
			<div class="padding-top-medium"></div>
			<div style="text-align: center;">
				<h1><?= $header; ?></h1>
				<h2><?= $message; ?></h2>
				<br/><br/>
				<?= $redirect_message; ?>
				<br/><br/>
			</div>
		</div>
	</div>
    <?php include 'footer.php'; ?>
</body>
</html>

