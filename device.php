<?php
require_once("models/config.php");
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
    header("Location: error.php");
    die();
}
function IsNullOrEmptyString($str){
    return (!isset($str) || trim($str)==='');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>DeviceGenie - <?=($device->device_id)?></title>
    <link rel="stylesheet" type="text/css" href="genie.css">
    <style type="text/css">div.section-body-details.hide { display: none; }</style>
    <script type="text/javascript">
    var tabLinks = new Array();
    var contentDivs = new Array();

    function init() {

      // Grab the tab links and content divs from the page
      var tabListItems = document.getElementById('tabs').childNodes;
      for ( var i = 0; i < tabListItems.length; i++ ) {
        if ( tabListItems[i].nodeName == "LI" ) {
          var tabLink = getFirstChildWithTagName( tabListItems[i], 'A' );
          var id = getHash( tabLink.getAttribute('href') );
          tabLinks[id] = tabLink;
          contentDivs[id] = document.getElementById( id );
        }
      }

      // Assign onclick events to the tab links, and
      // highlight the first tab
      var i = 0;

      for ( var id in tabLinks ) {
        tabLinks[id].onclick = showTab;
        tabLinks[id].onfocus = function() { this.blur() };
        if ( i == 0 ) tabLinks[id].className = 'selected';
        i++;
      }

      // Hide all content divs except the first
      var i = 0;

      for ( var id in contentDivs ) {
        if ( i != 0 ) contentDivs[id].className = 'section-body-details hide';
        i++;
      }
    }

    function showTab() {
      var selectedId = getHash( this.getAttribute('href') );

      // Highlight the selected tab, and dim all others.
      // Also show the selected content div, and hide all others.
      for ( var id in contentDivs ) {
        if ( id == selectedId ) {
          tabLinks[id].className = 'selected';
          contentDivs[id].className = 'section-body-details';
        } else {
          tabLinks[id].className = '';
          contentDivs[id].className = 'section-body-details hide';
        }
      }

      // Stop the browser following the link
      return false;
    }

    function getFirstChildWithTagName( element, tagName ) {
      for ( var i = 0; i < element.childNodes.length; i++ ) {
        if ( element.childNodes[i].nodeName == tagName ) return element.childNodes[i];
      }
    }

    function getHash( url ) {
      var hashPos = url.lastIndexOf ( '#' );
      return url.substring( hashPos + 1 );
    }

    </script>
    </style>
</head>
<body onload="init()">
    <?php include 'navbar-search.php'; ?>
    <div id="content-wide">
        <div class="section">
            <div class="section-label"><?=($device->device_id)?($device->device_id):'N/A';?></div>
            <div class="section-body-device">
                <div class="section-body-image-div">
                    <?php if ($imgPath && file_exists($imgPath)): ?>
                        <img src="<?=$imgPath;?>" class="section-body-image" alt="<?=$device->device_id;?>"/>
                    <?php else: ?>
                        <img src="images/default.png" class="section-body-image" alt="<?=$device->device_id;?>"/>
                    <?php endif; ?>
                </div>
                <div class="section-body-text">
                    <ul>
                        <li><span class="label-key">Status: </span><span><?=($device->available == 'true')?'Available':'Not Available';?></span></li>
                        <li><span class="label-key">Device: </span><span><?=$device->manufacturer;?> <?=$device->model;?> <?=$device->model_version;?></span></li>
                        <li><span class="label-key">OS Version: </span><span><?=$device->os_type;?> <?=$device->os_version;?></span></li>
                        <li><span class="label-key">Type: </span><span><?=$device->type;?></span></li>
                        <li><span class="label-key">Location: </span><span><?=$device->location;?></span></li>
                        <li><span class="label-key">Managed By: </span><span><?=$device->manager_dept;?></span></li>
                        <li><span class="label-key">Dev Provisioned: </span><span><?=($device->dev_provisioned == 'true')?'Yes':'No';?></span></li>
                    </ul>
                </div>
                <div id="checkout">
                    <?php if ($device->available == 'true'): ?>
                        <a class="green-button" href="checkout.php?id=<?=$device->device_id;?>">Checkout</a>
                        <span id="checkout-status">Available</span>
                    <?php else: ?>
                        <?php if (isUserLoggedIn() && $loggedInUser->username == $device->checked_out_to): ?>
                            <a class="red-button-short" href="checkin.php?id=<?=$device->device_id;?>">Checkin</a><a class="red-button-dropdown" id="red-button-dropdown" onclick="toggle_visibility('checkin-dropdown');" href="javascript:void(0);">v</a>
                            <span id="checkout-status">Device is checked out to you</span>
                            <ul class="checkin-dropdown" id="checkin-dropdown" style="display: none;">
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

        <div class="section">
            <ul id="tabs">
                <li><a href="#details">Details</a></li>
                <li><a href="#history">History</a></li>
            </ul>
            <div class="section-body-details" id="details">
                <div class="section-body-text">
                    <ul>
                        <li><span class="label-key">UDID: </span><span><?=($device->udid && strlen(trim($device->udid)) > 0)?($device->udid):'N/A';?></span></li>
                        <li><span class="label-key">Carrier: </span><span><?=($device->carrier && strlen(trim($device->carrier)) > 0)?($device->carrier):'N/A';?></span></li>
                        <li><span class="label-key">Phone Number: </span><span><?=($device->phone_number && strlen(trim($device->phone_number)) > 0)?($device->phone_number):'N/A';?></span></li>
                        <li><span class="label-key">Jailbroken: </span><span><?=($device->jailbroken == 'true')?'Yes':'No';?></span></li>
                        <li><span class="label-key">Notes: </span><span><?=($device->note && strlen(trim($device->note)) > 0)?($device->note):'N/A';?></span></li>
                        <li><span class="label-key">Last Checked Out: </span><span><?=($dateCheckedOut)?($dateCheckedOut):'N/A';?></span></li>
                        <li><span class="label-key">Checked Out Count: </span><span><?=$device->checked_out_count;?></span></li>
                        <li><span class="label-key">Device Added: </span><span><?=($dateCreatedOn)?($dateCreatedOn):'N/A';?></span></li>
                    </ul>
                </div>
            </div>
            <div class="section-body-details" id="history">
                <div class="section-body-text">
                    <span class="label-key">Recent History:</span>
                    <ul>
                        <?php foreach($device->recent_activity[0] as $activity): ?>
                        <?php
                        if (!IsNullOrEmptyString($activity['created_on'])) {
                            $activityCreatedOn = strtotime($activity['created_on']);
                            $activityDate = date('F jS Y g:ia', $activityCreatedOn);
                        }
                        ?>
                        <li>
                            <span>
                                <?= $activityDate; ?> - <?= $activity['type']; ?> by <?= $activity['user']; ?>
                                <?php if (!IsNullOrEmptyString($activity['note'])): ?>
                                    with note '<?= $activity['note']; ?>'
                                <?php endif; ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script type="text/javascript">
        function toggle_visibility(id) {
            var e = document.getElementById(id);
            if(e.style.display == 'block') {
               e.style.display = 'none';
               $("#red-button-dropdown").css('background-color', '');
            }
            else {
               e.style.display = 'block';
               $("#red-button-dropdown").css('background-color', '#b63a09');
            }
        }
    </script>
</body>
</html>
