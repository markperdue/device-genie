<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
$permissionId = $_GET['id'];

//Check if selected permission level exists
if(!permissionIdExists($permissionId)){
	header("Location: admin_permissions.php"); die();	
}

$permissionDetails = fetchPermissionDetails($permissionId); //Fetch information specific to permission level

//Forms posted
if(!empty($_POST)){
	
	//Delete selected permission level
	if(!empty($_POST['delete'])){
		$deletions = $_POST['delete'];
		if ($deletion_count = deletePermission($deletions)){
		$successes[] = lang("PERMISSION_DELETIONS_SUCCESSFUL", array($deletion_count));
		}
		else {
			$errors[] = lang("SQL_ERROR");	
		}
	}
	else
	{
		//Update permission level name
		if($permissionDetails['name'] != $_POST['name']) {
			$permission = trim($_POST['name']);
			
			//Validate new name
			if (permissionNameExists($permission)){
				$errors[] = lang("ACCOUNT_PERMISSIONNAME_IN_USE", array($permission));
			}
			elseif (minMaxRange(1, 50, $permission)){
				$errors[] = lang("ACCOUNT_PERMISSION_CHAR_LIMIT", array(1, 50));	
			}
			else {
				if (updatePermissionName($permissionId, $permission)){
					$successes[] = lang("PERMISSION_NAME_UPDATE", array($permission));
				}
				else {
					$errors[] = lang("SQL_ERROR");
				}
			}
		}
		
		//Remove access to pages
		if(!empty($_POST['removePermission'])){
			$remove = $_POST['removePermission'];
			if ($deletion_count = removePermission($permissionId, $remove)) {
				$successes[] = lang("PERMISSION_REMOVE_USERS", array($deletion_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		//Add access to pages
		if(!empty($_POST['addPermission'])){
			$add = $_POST['addPermission'];
			if ($addition_count = addPermission($permissionId, $add)) {
				$successes[] = lang("PERMISSION_ADD_USERS", array($addition_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		//Remove access to pages
		if(!empty($_POST['removePage'])){
			$remove = $_POST['removePage'];
			if ($deletion_count = removePage($remove, $permissionId)) {
				$successes[] = lang("PERMISSION_REMOVE_PAGES", array($deletion_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		//Add access to pages
		if(!empty($_POST['addPage'])){
			$add = $_POST['addPage'];
			if ($addition_count = addPage($add, $permissionId)) {
				$successes[] = lang("PERMISSION_ADD_PAGES", array($addition_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
			$permissionDetails = fetchPermissionDetails($permissionId);
	}
}

$pagePermissions = fetchPermissionPages($permissionId); //Retrieve list of accessible pages
$permissionUsers = fetchPermissionUsers($permissionId); //Retrieve list of users with membership
$userData = fetchAllUsers(); //Fetch all users
$pageData = fetchAllPages(); //Fetch all pages
?>

<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Admin Permission</title>
	<link rel="stylesheet" type="text/css" href="genie.css">
</head>
<body>
    <?php include 'navbar-search.php'; ?>
    <div id="margin-top-medium"></div>
	<div id="content-medium">
		<div class="colored-bg-round">
			<div id="content-nav-bar">
				<a class="content-nav-button-left" href="admin_configuration.php">Configuration</a> |
				<a class="content-nav-button" href="admin_users.php">Users</a> |
				<a class="content-nav-button-selected" href="admin_permissions.php">Permissions</a> |
				<a class="content-nav-button-right" href="admin_pages.php">Pages</a>
			</div>
			<div class="padding-top-medium"></div>
			<div style="text-align: center;">
				<h1><?= $permissionDetails['name']; ?></h1>
				<h2>Control membership to this group as well as access levels</h2>
			</div>
			
			<div id="admin">
				<div id="alerts-container">
					<?php echo resultBlockStyled($errors,$successes); ?>
				</div>

				<form name="adminPermission" action="<?php echo htmlentities($_SERVER['PHP_SELF']."?id=".$permissionId); ?>" method="post">
					<table class="admin">
						<tr>
							<th>Permission Information</th>
							<th>Permission Membership</th>
							<th>Permission Access</th>
						</tr>
						<tr>
							<td>
								<p>
									<ul id="short">
										<li>
											<label>ID:</label>
											<span><?= $permissionDetails['id']; ?></span>
										</li>
										<li>
											<label>Name:</label>
											<input type="text" name="name" value="<?= $permissionDetails['name']; ?>" />
										</li>
										<li>
											<label>Delete:</label>
											<input type="checkbox" name="delete[<?= $permissionDetails['id']; ?>]" id="delete[<?= $permissionDetails['id']; ?>]" value="<?= $permissionDetails['id']; ?>">
										</li>
									</ul>
								</p>
							</td>
							<td>
								<p>
									Remove Members:
									<!-- List users with permission level -->
									<?php foreach ($userData as $v1): ?>
										<?php if (isset($permissionUsers[$v1['id']])): ?>
											<br>
											<input type="checkbox" name="removePermission[<?= $v1['id']; ?>]" id="removePermission[<?= $v1['id']; ?>]" value="<?= $v1['id']; ?>"> <?= $v1['display_name']; ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</p>
								<p>
									Add Permission:
									<!-- List of permission levels user is not apart of -->
									<?php foreach ($userData as $v1): ?>
										<?php if (!isset($permissionUsers[$v1['id']])): ?>
											<br>
											<input type="checkbox" name="addPermission[<?= $v1['id']; ?>]" id="addPermission[<?= $v1['id']; ?>]" value="<?= $v1['id']; ?>"> <?= $v1['display_name']; ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</p>
							</td>
							<td>
								<p>
									Public Access:
									<!-- List public pages -->
									<?php foreach ($pageData as $v1): ?>
										<?php if ($v1['private'] != 1): ?>
											<br>
											<span><?= $v1['page']; ?></span>
										<?php endif; ?>
									<?php endforeach; ?>
								</p>
								<p>
									Remove Access:
									<!-- List pages accessible to permission level -->
									<?php foreach ($pageData as $v1): ?>
										<?php if (isset($pagePermissions[$v1['id']]) AND $v1['private'] == 1): ?>
											<br>
											<input type="checkbox" name="removePage[<?= $v1['id']; ?>]" id="removePage[<?= $v1['id']; ?>]" value="<?= $v1['id']; ?>"> <?= $v1['page']; ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</p>
								<p>
									Add Access:
									<!-- List pages inaccessible to permission level -->
									<?php foreach ($pageData as $v1): ?>
										<?php if (!isset($pagePermissions[$v1['id']]) AND $v1['private'] == 1): ?>
											<br>
											<input type="checkbox" name="addPage[<?= $v1['id']; ?>]" id="addPage[<?= $v1['id']; ?>]" value="<?= $v1['id']; ?>"> <?= $v1['page']; ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</p>
							</td>
						</tr>
					</table>
					
					<div id="admin-lower">
						<input type="submit" id="submit-button" value="Update" />
					</div>
				</form>
				<div class="clearFloats"></div>
			</div>
		</div>
	</div>
	<?php include 'footer.php'; ?>
</body>
</html>
