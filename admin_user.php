<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}
$userId = $_GET['id'];

//Check if selected user exists
if(!userIdExists($userId)){
	header("Location: admin_users.php"); die();
}

$userdetails = fetchUserDetails(NULL, NULL, $userId); //Fetch user details

//Forms posted
if(!empty($_POST))
{	
	//Delete selected account
	if(!empty($_POST['delete'])){
		$deletions = $_POST['delete'];
		if ($deletion_count = deleteUsers($deletions)) {
			$successes[] = lang("ACCOUNT_DELETIONS_SUCCESSFUL", array($deletion_count));
		}
		else {
			$errors[] = lang("SQL_ERROR");
		}
	}
	else
	{
		//Update display name
		if ($userdetails['display_name'] != $_POST['display']){
			$displayname = trim($_POST['display']);
			
			//Validate display name
			if(displayNameExists($displayname))
			{
				$errors[] = lang("ACCOUNT_DISPLAYNAME_IN_USE",array($displayname));
			}
			elseif(minMaxRange(5,25,$displayname))
			{
				$errors[] = lang("ACCOUNT_DISPLAY_CHAR_LIMIT",array(5,25));
			}
			elseif(!ctype_alnum($displayname)){
				$errors[] = lang("ACCOUNT_DISPLAY_INVALID_CHARACTERS");
			}
			else {
				if (updateDisplayName($userId, $displayname)){
					$successes[] = lang("ACCOUNT_DISPLAYNAME_UPDATED", array($displayname));
				}
				else {
					$errors[] = lang("SQL_ERROR");
				}
			}
			
		}
		else {
			$displayname = $userdetails['display_name'];
		}
		
		//Activate account
		if(isset($_POST['activate']) && $_POST['activate'] == "activate"){
			if (setUserActive($userdetails['activation_token'])){
				$successes[] = lang("ACCOUNT_MANUALLY_ACTIVATED", array($displayname));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		//Update email
		if ($userdetails['email'] != $_POST['email']){
			$email = trim($_POST["email"]);
			
			//Validate email
			if(!isValidEmail($email))
			{
				$errors[] = lang("ACCOUNT_INVALID_EMAIL");
			}
			elseif(emailExists($email))
			{
				$errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));
			}
			else {
				if (updateEmail($userId, $email)){
					$successes[] = lang("ACCOUNT_EMAIL_UPDATED");
				}
				else {
					$errors[] = lang("SQL_ERROR");
				}
			}
		}
		
		//Update title
		if ($userdetails['title'] != $_POST['title']){
			$title = trim($_POST['title']);
			
			//Validate title
			if(minMaxRange(1,50,$title))
			{
				$errors[] = lang("ACCOUNT_TITLE_CHAR_LIMIT",array(1,50));
			}
			else {
				if (updateTitle($userId, $title)){
					$successes[] = lang("ACCOUNT_TITLE_UPDATED", array ($displayname, $title));
				}
				else {
					$errors[] = lang("SQL_ERROR");
				}
			}
		}
		
		//Remove permission level
		if(!empty($_POST['removePermission'])){
			$remove = $_POST['removePermission'];
			if ($deletion_count = removePermission($remove, $userId)){
				$successes[] = lang("ACCOUNT_PERMISSION_REMOVED", array ($deletion_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		if(!empty($_POST['addPermission'])){
			$add = $_POST['addPermission'];
			if ($addition_count = addPermission($add, $userId)){
				$successes[] = lang("ACCOUNT_PERMISSION_ADDED", array ($addition_count));
			}
			else {
				$errors[] = lang("SQL_ERROR");
			}
		}
		
		$userdetails = fetchUserDetails(NULL, NULL, $userId);
	}
}

$userPermission = fetchUserPermissions($userId);
$permissionData = fetchAllPermissions();
?>

<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Admin User</title>
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
				<h1><?= $userdetails['display_name']; ?></h1>
				<h2>Edit the settings for this user as well as modify their permission levels</h2>
			</div>
			
			<div id="admin">
				<div id="alerts-container">
					<?php echo resultBlockStyled($errors,$successes); ?>
				</div>

				<form name="adminUser" action="<?php echo htmlentities($_SERVER['PHP_SELF']."?id=".$userId); ?>" method="post">
					<table class="admin">
						<tr>
							<th>User Information</th>
							<th>Permission Membership</th>
						</tr>
						<tr>
							<td>
								<p>
									<ul>
										<li>
											<label>ID:</label>
											<span><?= $userdetails['id']; ?></span>
										</li>
										<li>
											<label>Username:</label>
											<span><?= $userdetails['user_name']; ?></span>
										</li>
										<li>
											<label>Display Name:</label>
											<input type="text" name="display" value="<?= $userdetails['display_name']; ?>" />
										</li>
										<li>
											<label>Email:</label>
											<input type="text" name="email" value="<?= $userdetails['email']; ?>" />
										</li>
										<li>
											<label>Active:</label>
											<?php if ($userdetails['active'] == '1'): ?>
												<span>Yes</span>
											<?php else: ?>
												<span>No</span>
												</li>
												<li>
													<label>Activate:</label>
													<input type="checkbox" name="activate" id="activate" value="activate">
											<?php endif; ?>
										</li>
										<li>
											<label>Title:</label>
											<input type="text" name="title" value="<?= $userdetails['title']; ?>" />
										</li>
										<li>
											<label>Signed Up:</label>
											<span><?= date("j M, Y", $userdetails['sign_up_stamp']) ?></span>
										</li>
										<li>
											<label>Last Sign In:</label>
											<?php if ($userdetails['last_sign_in_stamp'] == '0'): ?>
												<span>Never</span>
											<?php else: ?>
												<span><?= date("j M, Y", $userdetails['last_sign_in_stamp']); ?></span>
											<?php endif; ?>
										</li>
										<li>
											<label>Delete:</label>
											<input type="checkbox" name="delete[<?= $userdetails['id']; ?>]" id="delete[<?= $userdetails['id']; ?>]" value="<?= $userdetails['id']; ?>">
										</li>
									</ul>
								</p>
							</td>
							<td>
								<p>
									Remove Permission:
									<!-- List of permission levels user is apart of -->
									<?php foreach ($permissionData as $v1): ?>
										<?php if (isset($userPermission[$v1['id']])): ?>
											<br>
											<input type="checkbox" name="removePermission[<?= $v1['id']; ?>]" id="removePermission[<?= $v1['id']; ?>]" value="<?= $v1['id']; ?>"> <?= $v1['name']; ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</p>
								<p>
									Add Permission:
									<!-- List of permission levels user is not apart of -->
									<?php foreach ($permissionData as $v1): ?>
										<?php if (!isset($userPermission[$v1['id']])): ?>
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
