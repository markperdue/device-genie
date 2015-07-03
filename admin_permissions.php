<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Forms posted
if(!empty($_POST))
{
	//Delete permission levels
	if(!empty($_POST['delete'])){
		$deletions = $_POST['delete'];
		if ($deletion_count = deletePermission($deletions)){
		$successes[] = lang("PERMISSION_DELETIONS_SUCCESSFUL", array($deletion_count));
		}
	}
	
	//Create new permission level
	if(!empty($_POST['newPermission'])) {
		$permission = trim($_POST['newPermission']);
		
		//Validate request
		if (permissionNameExists($permission)){
			$errors[] = lang("PERMISSION_NAME_IN_USE", array($permission));
		}
		elseif (minMaxRange(1, 50, $permission)){
			$errors[] = lang("PERMISSION_CHAR_LIMIT", array(1, 50));	
		}
		else{
			if (createPermission($permission)) {
			$successes[] = lang("PERMISSION_CREATION_SUCCESSFUL", array($permission));
		}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
}

$permissionData = fetchAllPermissions(); //Retrieve list of all permission levels
?>

<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Admin Permissions</title>
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
				<h1>Admin Permissions</h1>
				<h2>Delete a group, create a new group, or drill down into the settings of an exisiting group</h2>
			</div>
			
			<div id="admin">
				<div id="alerts-container">
					<?php echo resultBlockStyled($errors,$successes); ?>
				</div>

				<form name="adminPermissions" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
					<table class="admin">
						<tr>
							<th>Delete</th>
							<th>Permission Name</th>
						</tr>
						
						<!-- List each permission level -->
						<?php foreach ($permissionData as $v1): ?>
						<tr>
							<td><input type="checkbox" name="delete[<?= $v1['id']; ?>]" id="delete[<?= $v1['id']; ?>]" value="<?= $v1['id']; ?>"></td>
							<td><a href="admin_permission.php?id=<?= $v1['id']; ?>"><?= $v1['name']; ?></a></td>
						</tr>
						<?php endforeach; ?>
					</table>
					<br /><br />
					<div id="admin-lower">
						<ul>
							<li>
								<label>New Permission:</label>
								<input type="text" name="newPermission" />
							</li>
						</ul>
	
						<input type="submit" id="submit-button" value="Submit" />
					<div id="admin-lower">
				</form>
				<div class="clearFloats"></div>
			</div>
		</div>
	</div>
	<?php include 'footer.php'; ?>
</body>
</html>
