<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
$query = $_GET['id'];
if ($query) {
	$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
	$api = '/rest/v1/device/';
	$url = $dc_base_url . $api . $query;

	$device = file_get_contents($url, false, $context);
	$device = simplexml_load_string($device);

	if (IsNullOrEmptyString($device->device_id)) {
		header("Location: error.php");
		die();
	}

	// Convert created_on timestamp to a date
	if (!IsNullOrEmptyString($device->created_on)) {
		$timeCreatedOn = strtotime($device->created_on);
		$dateCreatedOn = date('F jS Y \a\t g:ia', $timeCreatedOn);
	}

	// Convert checked_out_date timestamp to a date
	if (!IsNullOrEmptyString($device->checked_out_date)) {
		$timeCheckedOut = strtotime($device->checked_out_date);
		$dateCheckedOut = date('F jS Y \a\t g:ia', $timeCheckedOut);
	}

	// Create a path to an image asset
	if (!IsNullOrEmptyString($device->image_path)) {
		$imgPath = "images/uploads/" . $device->image_path;
	}
} else {
// 	header("Location: error.php");
// 	die();
}
function IsNullOrEmptyString($str){
	return (!isset($str) || trim($str)==='');
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Edit</title>
	<link rel="stylesheet" type="text/css" href="genie.css">
	<script src='models/funcs.js' type='text/javascript'>
	</script>
</head>
<body>
    <?php include 'navbar-search.php'; ?>
    
    <?php
	if(isset($_POST['submit'])) {
	    $device_id = $_POST['device_id'];
	    $manufacturer = $_POST['manufacturer'];
	    $model = $_POST['model'];
	    $model_version = $_POST['model_version'];
	    $os_type = $_POST['os_type'];
	    $os_version = $_POST['os_version'];
	    $type = $_POST['type'];
	    $location = $_POST['location'];
	    $manager_name = $_POST['manager_name'];
	    $manager_dept = $_POST['manager_dept'];
	    $carrier = $_POST['carrier'];
	    $phone_number = $_POST['phone_number'];
	    $udid = $_POST['udid'];
	    $note = $_POST['note'];
	    $dev_provisioned = ($_POST['dev_provisioned']) ? 1 : 0;
	    $jailbroken = ($_POST['jailbroken']) ? 1 : 0;
	    $recovery_mode_enabled = ($_POST['recovery_mode_enabled']) ? 1 : 0;
		
		$xml_data = "<device>".
        		"<device_id>$device_id</device_id>".
        		"<manufacturer>$manufacturer</manufacturer>".
        		"<model>$model</model>".
        		"<model_version>$model_version</model_version>".
        		"<os_type>$os_type</os_type>".
        		"<os_version>$os_version</os_version>".
        		"<type>$type</type>".
        		"<location>$location</location>".
        		"<manager_name>$manager_name</manager_name>".
        		"<manager_dept>$manager_dept</manager_dept>".
        		"<carrier>$carrier</carrier>".
        		"<phone_number>$phone_number</phone_number>".
        		"<udid>$udid</udid>".
        		"<note>$note</note>".
        		"<dev_provisioned>$dev_provisioned</dev_provisioned>".
        		"<jailbroken>$jailbroken</jailbroken>".
        		"<recovery_mode_enabled>$recovery_mode_enabled</recovery_mode_enabled>".
			"</device>";
		
		$ch = curl_init();
		$api = '/rest/v1/device/';
		$url = $dc_base_url . $api . $device_id;
		$credentials = $loggedInUser->username . ":" . $loggedInUser->hash_pw;
		$headers = array(
				"Content-type: application/xml;charset=\"utf-8\"",
				"Authorization: " . $credentials
		);
		
		// Set curl options to POST
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			
		$server_output = curl_exec($ch);
		$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
			
		// Process the result
		if ($status_code === 201) {
			$successes[] = "Device was updated! View the updated device <a href='device.php?id=$device_id'>here</a>";
			
			// Update the form input fields
			$device->manufacturer = $manufacturer;
			$device->model = $model;
			$device->model_version = $model_version;
			$device->os_type = $os_type;
			$device->os_version = $os_version;
			$device->type = $type;
			$device->location = $location;
			$device->manager_name = $manager_name;
			$device->manager_dept = $manager_dept;
			$device->carrier = $carrier;
			$device->phone_number = $phone_number;
			$device->udid = $udid;
			$device->note = $note;
			$device->dev_provisioned = $dev_provisioned ? "true": "false";
			$device->jailbroken = $jailbroken ? "true": "false";
			$device->recovery_mode_enabled = $recovery_mode_enabled ? "true": "false";
		} else {
			$errors[] = "Error: Device not updated. Server returned the following - '" . $status_code . " - " . $server_output . "'";
		}
	}
	?>
	
	<div id="content-wide">
		<div id="alerts-container">
			<?= resultBlockStyled($errors,$successes); ?>
		</div>

        <div class="add-device">
			<form id="add-device-form" name="newDevice" action="<?= htmlentities($_SERVER['PHP_SELF']."?id=".$device->device_id); ?>" method="post">
				<ul>
					<div class="section-label">Device Basics</div>
					<div id="add-device-basics">
						<li>
							<label>Device ID:</label>
							<input type="text" name="device_id" placeholder="<?= $device->device_id; ?>" value="<?= $device->device_id; ?>" maxlength="32" readonly="readonly" />
						</li>
						<li class="multi">
							<div class="col1">
								<label>Manufacturer:</label>
								<input type="text" name="manufacturer" placeholder="<?= $device->manufacturer; ?>" value="<?= $device->manufacturer; ?>" maxlength="32" />
							</div>
							<div class="col2">
								<label>Model:</label>
								<input type="text" name="model" placeholder="<?= $device->model; ?>" value="<?= $device->model; ?>" maxlength="32" />
							</div>
							<div class="col3">
								<label>Model Version:</label>
								<input type="text" name="model_version" placeholder="<?= $device->model_version; ?>" value="<?= $device->model_version; ?>" maxlength="32" />
							</div>
						</li>
						<li class="multi">
							<div class="col1">
								<label>OS Type:</label>
								<input type="text" name="os_type" placeholder="<?= $device->os_type; ?>" value="<?= $device->os_type; ?>" maxlength="32" />
							</div>
							<div class="col2">
								<label>OS Version:</label>
								<input type="text" name="os_version" placeholder="<?= $device->os_version; ?>" value="<?= $device->os_version; ?>" maxlength="32" />
							</div>
						</li>
						<li>
							<label>Type:</label>
							<input type="text" name="type" placeholder="<?= $device->type; ?>" value="<?= $device->type; ?>" maxlength="32" />
						</li>
						<li>
							<label>Location:</label>
							<input type="text" name="location" placeholder="<?= $device->location; ?>" value="<?= $device->location; ?>" maxlength="64" />
						</li>
					</div>
					
					<br/>
					<div class="section-label">Optional Device Details</div>
					<div id="add-device-extras">
						<li class="multi">
							<div class="col1">
								<label>Managed By:</label>
								<input type="text" name="manager_name" placeholder="<?= $device->manager_name; ?>" value="<?= $device->manager_name; ?>" maxlength="64" />
							</div>
							<div class="col2">
								<label>Department:</label>
								<input type="text" name="manager_dept" placeholder="<?= $device->manager_dept; ?>" value="<?= $device->manager_dept; ?>" maxlength="64" />
							</div>
						</li>
						<li class="multi">
							<div class="col1">
								<label>Phone Carrier:</label>
								<input type="text" name="carrier" placeholder="<?= $device->carrier; ?>" value="<?= $device->carrier; ?>" maxlength="32" />
							</div>
							<div class="col2">
								<label>Phone Number:</label>
								<input type="text" name="phone_number" placeholder="<?= $device->phone_number; ?>" value="<?= $device->phone_number; ?>" maxlength="32" />
							</div>
						</li>
						<li>
							<label>UDID:</label>
							<input class="long" type="text" name="udid" placeholder="<?= $device->udid; ?>" value="<?= $device->udid; ?>" maxlength="64" />
						</li>
						<li>
							<label>Note:</label>
							<input class="long" type="text" name="note" placeholder="<?= $device->note; ?>" value="<?= $device->note; ?>" maxlength="255" />
						</li>
						<li>
							<label style="margin-top:0;margin-right:10px;">Dev Provisioned:</label>
							<input type="checkbox" name="dev_provisioned" <?php if ($device->dev_provisioned == "true") echo "checked"; ?> />
						</li>
						<li>
							<label style="margin-top:0;margin-right:10px;">Jailbroken:</label>
							<input type="checkbox" name="jailbroken" <?php if ($device->jailbroken == "true") echo "checked"; ?> />
						</li>
						<li>
							<label style="margin-top:0;margin-right:10px;">Recovery Enabled:</label>
							<input type="checkbox" name="recovery_mode_enabled" <?php if ($device->recovery_mode_enabled == "true") echo "checked"; ?> />
						</li>
					</div>
					<li>
						<label>&nbsp;<br>
						<input type="submit" id="submit-button" name="submit" value="Update" />
					</li>
				</ul>
			</form>
			</div>
        </div>
    </div>
    <?php include "footer.php"; ?>
</body>
</html>
