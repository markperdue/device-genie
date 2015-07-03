<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) { header("Location: account.php"); die(); }


// echo "<br/>";
// echo "<br/><br/>_SESSION INFO:";
// foreach ($_SESSION as $name => $value)
// {
// 	echo "<br>".$name."=".$value;
// }
// echo "<br/><br/>_POST INFO:";
// foreach ($_POST as $name2 => $value2)
// {
// 	echo "<br>".$name2."=".$value2;
// }

// NEW ADDITION BASED OFF OF http://usercake.com/thread.php?id=237
if (isset($_SESSION["redirect"])) {
	$errors = array();
	$errors[] = "You must be logged in to do that";
	$redirect = $_SESSION["redirect"];

	destroySession(redirect);
}
else {
	if (isset($_POST['redirect'])) {
		$redirect = $_POST['redirect'];
	}
	else {
		$redirect = "account.php";
	}
}

//Forms posted
if(!empty($_POST))
{
	$errors = array();
	$username = sanitize(trim($_POST["username"]));
	$password = trim($_POST["password"]);
	
	//Perform some validation
	//Feel free to edit / change as required
	if($username == "")
	{
		$errors[] = lang("ACCOUNT_SPECIFY_USERNAME");
	}
	if($password == "")
	{
		$errors[] = lang("ACCOUNT_SPECIFY_PASSWORD");
	}

	if(count($errors) == 0)
	{
		//A security note here, never tell the user which credential was incorrect
		if(!usernameExists($username))
		{
			$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
		}
		else
		{
			$userdetails = fetchUserDetails($username);
			//See if the user's account is activated
			if($userdetails["active"]==0)
			{
				$errors[] = lang("ACCOUNT_INACTIVE");
			}
			else
			{
				//Hash the password and use the salt from the database to compare the password.
				$entered_pass = generateHash($password,$userdetails["password"]);
				
				if($entered_pass != $userdetails["password"])
				{
					//Again, we know the password is at fault here, but lets not give away the combination incase of someone bruteforcing
					$errors[] = lang("ACCOUNT_USER_OR_PASS_INVALID");
				}
				else
				{
					//Passwords match! we're good to go'
					
					//Construct a new logged in user object
					//Transfer some db data to the session object
					$loggedInUser = new loggedInUser();
					$loggedInUser->email = $userdetails["email"];
					$loggedInUser->user_id = $userdetails["id"];
					$loggedInUser->hash_pw = $userdetails["password"];
					$loggedInUser->title = $userdetails["title"];
					$loggedInUser->displayname = $userdetails["display_name"];
					$loggedInUser->username = $userdetails["user_name"];
					
					//Update last sign in
					$loggedInUser->updateLastSignIn();
					$_SESSION["userCakeUser"] = $loggedInUser;
					
					//Redirect to user account page
					// NEW ADDITION BASED OFF OF http://usercake.com/thread.php?id=237
 					header("Location: ".$redirect);
 					die();
				}
			}
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Login</title>
	<link rel="stylesheet" type="text/css" href="genie.css">
	<script src='models/funcs.js' type='text/javascript'>
	</script>
</head>
<body>
    <div id="margin-top-medium"></div>
	<div id="content-medium">
		<div style="text-align: center;">
			<div class="colored-bg-round">
				<div class="padding-top-medium"></div>
				<h1>Welcome to DeviceGenie!</h1>
				<h2>The easy solution to manage your shared device inventory.</h2>
				
				<div id="regbox">
					Please sign in below:
					<div id="alerts-container">
						<?php echo resultBlockStyled($errors,$successes); ?>
					</div>
					
					<form name="login" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
						<ul>
							<li>
								<input type="text" name="username" placeholder="Username" />
							</li>
							<li>
								<input type="password" name="password" placeholder="Password" />
							</li>
							<li>
								<input type="submit" id="submit-button" value="Login" />
							</li>
						</ul>
						<input type="hidden" name="redirect" value="<?= $redirect; ?>">
					</form>
					<ul>
						<li class="li-links"><a href="register.php">Not a member? Sign up!</a></li>
						<li class="li-links"><a href="forgot-password.php">Forgot your Password?</a></li>
						<?php if ($emailActivation == "true"): ?>
							<li class="li-links"><a href="resend-activation.php">Resend activation code</a></li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<?php include 'footer.php'; ?>
</body>
</html>
