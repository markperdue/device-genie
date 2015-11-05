<?php
require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

// Image upload validator
function image_upload_validator() {
	global $errors;
	global $upload_directory;
	$allowed_filetypes = array("gif", "jpeg", "jpg", "png");
	$path = $_FILES["file"]["name"];
	$extension = pathinfo($path, PATHINFO_EXTENSION);
	$max_filesize = 2000000; // 2MB limit
// 	$upload_directory = "images/uploads/devices/";
	
	if (!in_array($extension, $allowed_filetypes)) {
		$errors[] = "Error: The file must be an image.";
		return;
	}
	if (($_FILES["file"]["type"] == "image/gif")
		|| ($_FILES["file"]["type"] == "image/jpeg")
		|| ($_FILES["file"]["type"] == "image/jpg")
		|| ($_FILES["file"]["type"] == "image/pjpeg")
		|| ($_FILES["file"]["type"] == "image/x-png")
		|| ($_FILES["file"]["type"] == "image/png")) {
			;
	} else {
		$errors[] = "Error: The file must be an image";
		return;
	}
	if ($_FILES["file"]["size"] > $max_filesize) {
		$errors[] = "Error: File is too large.";
		return;
	}
	if (!is_writable($upload_directory)) {
		$errors[] = "Error: Cannot write to the upload directory " . $upload_directory;
		return;
	}
	if ($_FILES["file"]["error"] > 0) {
		$errors[] = "Error: Return code '" . $_FILES["file"]["error"] . "' received";
		return;
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie</title>
	<link rel="stylesheet" type="text/css" href="genie.css">
	<script src='models/funcs.js' type='text/javascript'>
	</script>
</head>
<body>
    <?php include 'navbar-search.php'; ?>
    
    <?php
    if (!is_writable($upload_directory)) {
    	$errors[] = "Cannot write to the upload directory. Will not be able to upload device images";
    }
    
	if (isset($_POST['submit'])) {
		
		// Determine if there was a file chosen
		if ($_FILES["file"]["name"]) {
			// Image upload validator. Returns $errors if image is not valid
			image_upload_validator();
			
			// Move file to upload directory if there were not errors
			if (count($errors) === 0) {
				$path = $_FILES["file"]["name"];
				$extension = pathinfo($path, PATHINFO_EXTENSION);
				$upload_directory = "images/uploads/devices/";
				
				// Generate a unique filename
				do {
					$filename = uniqid() . "." . $extension;
				} while (file_exists($upload_directory . $filename));
				
				// Resize image proportionally using max width or height
				$maxDim = 200;
				list($width, $height, $type, $attr) = getimagesize( $_FILES["file"]["tmp_name"] );
				if ( $width > $maxDim || $height > $maxDim ) {
					$fn = $_FILES["file"]["tmp_name"];
					$size = getimagesize( $fn );
					$ratio = $size[0]/$size[1]; // width/height
					if ( $ratio > 1) {
						$width = $maxDim;
						$height = $maxDim/$ratio;
					} else {
						$width = $maxDim*$ratio;
						$height = $maxDim;
					}
					$src = imagecreatefromstring( file_get_contents( $fn ) );
					$dst = imagecreatetruecolor( $width, $height );
					imagecopyresampled( $dst, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1] );
					imagedestroy( $src );
					imagepng( $dst, $upload_directory . "/" . $filename );
					imagedestroy( $dst );
				} else {
					move_uploaded_file($_FILES["file"]["tmp_name"], $upload_directory . "/" . $filename);
				}
				
				$db_image_path = "devices/" . $filename;
			}
		}

		// Continue only if there are no errors
		if (count($errors) === 0) {
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
					"<image_path>$db_image_path</image_path>".
					"</device>";
			
			$ch = curl_init();
			$api = '/rest/v1/devices';
			$url = $dc_base_url . $api;
			$credentials = $loggedInUser->username . ":" . $loggedInUser->hash_pw;
			$headers = array(
					"Content-type: application/xml;charset=\"utf-8\"",
					"Authorization: " . $credentials
			);
			
			// Set curl options to POST
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				
			$server_output = curl_exec($ch);
			$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
				
			// Process the result
			if ($status_code === 201) {
				$successes[] = "Device was added! View the new device <a href='device.php?id=$device_id'>here</a>";
					
				// Clear the form input fields
				clearFields();
			} else {
				$errors[] = "There was an error.";
				$errors[] = $status_code . " - " . $server_output;
			}
		}
	}
	?>
	
	<?php 
    function clearFields() {
		unset($GLOBALS['device_id']);
		unset($GLOBALS['manufacturer']);
		unset($GLOBALS['model']);
		unset($GLOBALS['model_version']);
		unset($GLOBALS['os_type']);
		unset($GLOBALS['os_version']);
		unset($GLOBALS['type']);
		unset($GLOBALS['location']);
		unset($GLOBALS['manager_name']);
		unset($GLOBALS['manager_dept']);
		unset($GLOBALS['carrier']);
		unset($GLOBALS['phone_number']);
		unset($GLOBALS['udid']);
		unset($GLOBALS['note']);
		unset($GLOBALS['dev_provisioned']);
		unset($GLOBALS['jailbroken']);
		unset($GLOBALS['recovery_mode_enabled']);
	}
    ?>
	
	<div id="content-wide">
		<div id="alerts-container">
			<?php echo resultBlockStyled($errors,$successes); ?>
		</div>

        <div class="add-device">
			<form id="add-device-form" name="newDevice" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
				<ul>
					<div class="section-label">Device Basics</div>
					<div id="add-device-basics">
						<li>
							<label>Device ID:</label>
							<input type="text" name="device_id" placeholder="Device_001" value="<?php echo $device_id; ?>" maxlength="32" required />
						</li>
						<li class="multi">
							<div class="col1">
								<label>Manufacturer:</label>
								<input type="text" name="manufacturer" placeholder="Samsung" value="<?php echo $manufacturer; ?>" maxlength="32" required />
							</div>
							<div class="col2">
								<label>Model:</label>
								<input type="text" name="model" placeholder="Galaxy" value="<?php echo $model; ?>" maxlength="32" required />
							</div>
							<div class="col3">
								<label>Model Version:</label>
								<input type="text" name="model_version" placeholder="S6" value="<?php echo $model_version; ?>" maxlength="32" required />
							</div>
						</li>
						<li class="multi">
							<div class="col1">
								<label>OS Type:</label>
								<input type="text" name="os_type" placeholder="Android" value="<?php echo $os_type; ?>" maxlength="32" required />
							</div>
							<div class="col2">
								<label>OS Version:</label>
								<input type="text" name="os_version" placeholder="4.4.4" value="<?php echo $os_version; ?>" maxlength="32" required />
							</div>
						</li>
						<li>
							<label>Type:</label>
							<input type="text" name="type" placeholder="Phone/Tablet/GPS" value="<?php echo $type; ?>" maxlength="32" required />
						</li>
						<li>
							<label>Location:</label>
							<input type="text" name="location" placeholder="San Francisco" value="<?php echo $location; ?>" maxlength="64" required />
						</li>
						<li>
							<label>Device Image:</label>
							<input type="file" name="file" />
						</li>
					</div>
					
					<br/>
					<div class="section-label">Optional Device Details</div>
					<div id="add-device-extras">
						<li class="multi">
							<div class="col1">
								<label>Managed By:</label>
								<input type="text" name="manager_name" placeholder="Contact Person" value="<?php echo $manager_name; ?>" maxlength="64" />
							</div>
							<div class="col2">
								<label>Department:</label>
								<input type="text" name="manager_dept" placeholder="Department Name" value="<?php echo $manager_dept; ?>" maxlength="64" />
							</div>
						</li>
						<li class="multi">
							<div class="col1">
								<label>Phone Carrier:</label>
								<input type="text" name="carrier" placeholder="Verizon/AT&T" value="<?php echo $carrier; ?>" maxlength="32" />
							</div>
							<div class="col2">
								<label>Phone Number:</label>
								<input type="text" name="phone_number" placeholder="555-555-5555" value="<?php echo $phone_number; ?>" maxlength="32" />
							</div>
						</li>
						<li>
							<label>UDID:</label>
							<input class="long" type="text" name="udid" placeholder="Manufacturer Unique Identifer" value="<?php echo $udid; ?>" maxlength="64" />
						</li>
						<li>
							<label>Note:</label>
							<input class="long" type="text" name="note" placeholder="Any other info here" value="<?php echo $note; ?>" maxlength="255" />
						</li>
						<li>
							<label style="margin-top:0;margin-right:10px;">Dev Provisioned:</label>
							<input type="checkbox" name="dev_provisioned" <?php if ($dev_provisioned === 1) echo "checked"; ?> />
						</li>
						<li>
							<label style="margin-top:0;margin-right:10px;">Jailbroken:</label>
							<input type="checkbox" name="jailbroken" <?php if ($jailbroken === 1) echo "checked"; ?> />
						</li>
						<li>
							<label style="margin-top:0;margin-right:10px;">Recovery Enabled:</label>
							<input type="checkbox" name="recovery_mode_enabled" <?php if ($recovery_mode_enabled === 1) echo "checked"; ?> />
						</li>
					</div>
					<li>
						<label>&nbsp;<br>
						<input type="submit" id="submit-button" name="submit" value="Add" />
					</li>
				</ul>
			</form>
			</div>
        </div>
    </div>
    <?php include "footer.php"; ?>
</body>
</html>
