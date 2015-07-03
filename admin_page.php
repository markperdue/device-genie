<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
$pageId = $_GET['id'];

//Check if selected pages exist
if(!pageIdExists($pageId)){
	header("Location: admin_pages.php"); die();	
}

$pageDetails = fetchPageDetails($pageId); //Fetch information specific to page

//Forms posted
if(!empty($_POST)){
	$update = 0;
	
	if(!empty($_POST['private'])){ $private = $_POST['private']; }
	
	//Toggle private page setting
	if (isset($private) AND $private == 'Yes'){
		if ($pageDetails['private'] == 0){
			if (updatePrivate($pageId, 1)){
				$successes[] = lang("PAGE_PRIVATE_TOGGLED", array("private"));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
	}
	elseif ($pageDetails['private'] == 1){
		if (updatePrivate($pageId, 0)){
			$successes[] = lang("PAGE_PRIVATE_TOGGLED", array("public"));
		}
		else {
			$errors[] = lang("SQL_ERROR");	
		}
	}
	
	//Remove permission level(s) access to page
	if(!empty($_POST['removePermission'])){
		$remove = $_POST['removePermission'];
		if ($deletion_count = removePage($pageId, $remove)){
			$successes[] = lang("PAGE_ACCESS_REMOVED", array($deletion_count));
		}
		else {
			$errors[] = lang("SQL_ERROR");	
		}
		
	}
	
	//Add permission level(s) access to page
	if(!empty($_POST['addPermission'])){
		$add = $_POST['addPermission'];
		if ($addition_count = addPage($pageId, $add)){
			$successes[] = lang("PAGE_ACCESS_ADDED", array($addition_count));
		}
		else {
			$errors[] = lang("SQL_ERROR");	
		}
	}
	
	$pageDetails = fetchPageDetails($pageId);
}

$pagePermissions = fetchPagePermissions($pageId);
$permissionData = fetchAllPermissions();
?>

<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Admin Page</title>
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
				<a class="content-nav-button" href="admin_permissions.php">Permissions</a> |
				<a class="content-nav-button-selected-right" href="admin_pages.php">Pages</a>
			</div>
			<div class="padding-top-medium"></div>
			<div style="text-align: center;">
				<h1><?= $pageDetails['page'] ?></h1>
				<h2>Determine whether this page is private as well as modify which groups can view the page</h2>
			</div>
			
			<div id="admin">
				<div id="alerts-container">
					<?php echo resultBlockStyled($errors,$successes); ?>
				</div>

				<form name="adminPage" action="<?php echo htmlentities($_SERVER['PHP_SELF']."?id=".$pageId); ?>" method="post">
					<input type="hidden" name="process" value="1">
					<table class="admin">
						<tr>
							<th>Page Information</th>
							<th>Page Access</th>
						</tr>
						<tr>
							<td>
								<p>
									<ul>
										<li>
											<label>ID:</label>
											<span><?= $pageDetails['id']; ?></span>
										</li>
										<li>
											<label>Name:</label>
											<span><?= $pageDetails['page']; ?></span>
										</li>
										<li>
											<label>Private:</label>
											<?php if ($pageDetails['private'] == 1): ?>
												<input type="checkbox" name="private" id="private" value="Yes" checked>
											<?php else: ?>
												<input type="checkbox" name="private" id="private" value="Yes">
											<?php endif; ?>
										</li>
									</ul>
								</p>
							</td>
							<td>
								<p>
									Remove Access:
									<!-- Display list of permission levels with access -->
									<?php foreach ($permissionData as $v1): ?>
										<?php if (isset($pagePermissions[$v1['id']])): ?>
											<br>
											<input type="checkbox" name="removePermission[<?= $v1['id']; ?>]" id="removePermission[<?= $v1['id']; ?>]" value="<?= $v1['id']; ?>"> <?= $v1['name']; ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</p>
								<p>
									Add Access:
									<!-- Display list of permission levels without access -->
									<?php foreach ($permissionData as $v1): ?>
										<?php if (!isset($pagePermissions[$v1['id']])): ?>
											<br>
											<input type="checkbox" name="addPermission[<?= $v1['id']; ?>]" id="addPermission[<?= $v1['id']; ?>]" value="<?= $v1['id']; ?>"> <?= $v1['name']; ?>
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
