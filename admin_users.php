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
	$deletions = $_POST['delete'];
	if ($deletion_count = deleteUsers($deletions)){
		$successes[] = lang("ACCOUNT_DELETIONS_SUCCESSFUL", array($deletion_count));
	}
	else {
		$errors[] = lang("SQL_ERROR");
	}
}

$userData = fetchAllUsers(); //Fetch information for all users
?>

<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Admin Users</title>
	<link rel="stylesheet" type="text/css" href="genie.css">
</head>
<body>
    <?php include 'navbar-search.php'; ?>
    <div id="margin-top-medium"></div>
	<div id="content-medium">
		<div class="colored-bg-round">
			<div id="content-nav-bar">
				<a class="content-nav-button-left" href="admin_configuration.php">Configuration</a> |
				<a class="content-nav-button-selected" href="admin_users.php">Users</a> |
				<a class="content-nav-button" href="admin_permissions.php">Permissions</a> |
				<a class="content-nav-button-right" href="admin_pages.php">Pages</a>
			</div>
			<div class="padding-top-medium"></div>
			<div style="text-align: center;">
				<h1>Admin Users</h1>
				<h2>Delete a user or drill down into the settings of an existing user</h2>
			</div>
			
			<div id="regbox">
				<div id="alerts-container">
					<?php echo resultBlockStyled($errors,$successes); ?>
				</div>

				<form name="adminUsers" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
					<table class="admin">
						<tr>
							<th>Delete</th><th>Username</th><th>Display Name</th><th>Title</th><th>Last Sign In</th>
						</tr>
						
						<!-- Cycle through users -->
						<?php foreach ($userData as $v1): ?>
						<tr>
							<td><input type="checkbox" name="delete[<?= $v1['id']; ?>]" id="delete[<?= $v1['id']; ?>]" value="<?= $v1['id']; ?>"></td>
							<td><a href="admin_user.php?id=<?= $v1['id']; ?>"><?= $v1['user_name']; ?></a></td>
							<td><?= $v1['display_name']; ?></td>
							<td><?= $v1['title']; ?></td>
							<td>
								<!-- Interprety last login -->
								<?php if ($v1['last_sign_in_stamp'] == '0'): ?>
									Never
								<?php else: ?>
									<?= date("j M, Y", $v1['last_sign_in_stamp']) ?>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</table>
					
					<div id="admin-lower">
						<input type="submit" id="delete-button" value="Delete" />
					</div>
				</form>
				<div class="clearFloats"></div>
			</div>
		</div>
	</div>
	<?php include 'footer.php'; ?>
</body>
</html>
