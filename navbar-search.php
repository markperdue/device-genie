<?php
require_once("models/config.php");
?>
<!-- <link rel="stylesheet" type="text/css" href="genie.css"> -->
<div id="nav-menu-with-search">
	<div id="nav-menu-right">
		<?php if(isUserLoggedIn()): ?>
			<a href="account.php">Account</a>
			<?php if ($loggedInUser->checkPermission(array(2))): ?>
				| <a href="admin_configuration.php">Admin</a>
			<?php endif; ?>
			<?php if ($loggedInUser->checkPermission(array(2)) or $loggedInUser->checkPermission(array(3))): ?>
				| <a href="add_device.php">Add Device</a>
			<?php endif; ?>
			| <a href="logout.php">Logout</a>
		<?php else: ?>
			<a href="login.php">Login</a> | <a href="register.php">Regster</a>
		<?php endif; ?>
	</div>
	<div id="nav-menu-left">&nbsp;</div>
	<div id="nav-menu-searchbar">
		<?php include 'searchbar.php'; ?>
	</div>
</div>
<div class="clearFloats"></div>