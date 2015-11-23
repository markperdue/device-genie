<?php
if (isset($_GET["query"])) {
	// Sanitize input
	$query = $_GET["query"];
	$available = $_GET["available"];
	$dev = $_GET["dev"];
	$checked_out_to = $_GET["checked_out_to"];
} else {
    header("Location: index.php");
    die();
}
function IsNullOrEmptyString($str){
	return (!isset($str) || trim($str)==='');
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie</title>
	<link rel="stylesheet" type="text/css" href="genie.css" />
</head>
<body>
    <?php include 'navbar-search.php'; ?>
    <div id="content-wide">
		<?php
			$api = '/rest/v1/devices?query=' . urlencode($query);
		    $url = $dc_base_url . $api;
		    if ($available === 'true') {
				$url = $url . "&available=true";
			}
			if ($available === 'false') {
				$url = $url . "&available=false";
			}
		    if ($dev === 'true') {
				$url = $url . "&dev=true";
			}
			if ($dev === 'false') {
				$url = $url . "&dev=false";
			}
			if (!IsNullOrEmptyString($checked_out_to)) {
				$url = $url . "&checked_out_to=" . $checked_out_to;
			}
		    $devices = @file_get_contents("$url", false);
		    if ($devices === FALSE) {
		    	$devices = NULL;
		    }
		    else {
		    	$devices = simplexml_load_string($devices);
		    }
		?>
		
		<?php if ($devices === NULL): ?>
			<div>
				There was a problem connecting to the database. Please try again later.
				<br/><br/>
			</div>
		<?php else: ?>
			<div>
				Found <?=count($devices);?> matching <?=(count($devices) > 1 || count($devices) == 0)?'devices':'device';?> for '<?=$_GET["query"];?>'
				<?php if ($available === 'true' || $dev === 'true'): ?>
					matching filters:
					<?php if ($available === 'true'): ?>
						'available'
					<?php endif; ?>
					<?php if ($dev === 'true'): ?>
						'dev provisioned'
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<br/>
			<?php $i = 0; ?>
			<?php foreach($devices as $device): ?>
			<?php
				$imgPath = "images/uploads/" . $device->image_path;
			?>
			<div class="search-section">
	            <div class="search-section-body">
	                <div class="search-section-body-image-div">
	                	<a href="device.php?id=<?=$device->device_id;?>">
	                    <?php if ($device->image_path && $imgPath && file_exists($imgPath)): ?>
	                        <img src="<?=$imgPath;?>" class="search-section-body-image" alt="<?=$device->device_id;?>"/>
	                    <?php else: ?>
	                        <img src="images/default.png" class="search-section-body-image" alt="<?=$device->device_id;?>"/>
	                    <?php endif; ?>
	                    </a>
	                </div>
	                <div class="section-body-text">
	                    <ul>
	                    	<li><span class="label-key">Device ID: </span><span><a href="device.php?id=<?=$device->device_id;?>"><?=$device->device_id;?></a></span></li>
	                        <li><span class="label-key">Status: </span><span><?=($device->available == 'true')?'Available':'Not Available';?></span></li>
	                        <li><span class="label-key">Device: </span><span><?=$device->manufacturer;?> <?=$device->model;?> <?=$device->model_version;?></span></li>
	                        <li><span class="label-key">OS Version: </span><span><?=$device->os_type;?> <?=$device->os_version;?></span></li>
	                        <li><span class="label-key">Type: </span><span><?=$device->type;?></span></li>
	                        <li><span class="label-key">Location: </span><span><?=$device->location;?></span></li>
	                    </ul>
	                </div>
			        <div id="checkout">
			            <?php if ($device->available == 'true'): ?>
			                <a class="green-button" href="checkout.php?id=<?=$device->device_id;?>">Checkout</a>
			                <span id="checkout-status">Available</span>
			            <?php else: ?>
			            	<?php if (isUserLoggedIn() && $loggedInUser->username == $device->checked_out_to): ?>
			            		<a class="red-button-short" href="checkin.php?id=<?=$device->device_id;?>">Checkin</a><a class="red-button-dropdown" id="red-button-dropdown-<?= $i; ?>" onclick="toggle_visibility('checkin-dropdown-<?= $i; ?>', '<?= $i; ?>');" href="javascript:void(0);">v</a>
			            		<span id="checkout-status">Device is checked out to you</span>
		            			<ul class="checkin-dropdown" id="checkin-dropdown-<?= $i; ?>" style="display: none;">
									<li class="search-dropdown-header">Add a note to this checkin:</li>
		            				<form action="checkin.php" method="get">
		            					<input type="hidden" name="id" value="<?=$device->device_id;?>">
										<li><input class="search" type="text" name="note" placeholder="wifi problem, etc"><input class="green-button-small" type="submit" value="Checkin"></li>
									</form>
								</ul>
			            	<?php else: ?>
			            		<span class="red-button">Unavailable</span>
			                	<span id="checkout-status">Loaned To: <?=$device->checked_out_to;?></span>
			            	<?php endif; ?>
			            <?php endif; ?>
			        </div>
	            </div>
	        </div>
	        <div style="clear:both;"></div>
	        <?php ++$i; ?>
	    	<?php endforeach; ?>
    	<?php endif; ?>
    </div>
    <?php include 'footer.php'; ?>
	<script type="text/javascript">
	    function toggle_visibility(id, i) {
	        var e = document.getElementById(id);
	        if (e.style.display == 'block') {
	           e.style.display = 'none';
 	           $("#red-button-dropdown-" + i).css('background-color', '');
	        }
	        else {
	           e.style.display = 'block';
 	           $("#red-button-dropdown-" + i).css('background-color', '#b63a09');
	        }
	    }
	</script>
</body>
</html>
