<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) { header("Location: account.php"); die(); }

//Forms posted
if(!empty($_POST))
{
	$errors = array();
	$email = trim($_POST["email"]);
	$username = trim($_POST["username"]);
	$displayname = trim($_POST["displayname"]);
	$password = trim($_POST["password"]);
	$confirm_pass = trim($_POST["passwordc"]);
	$captcha = md5($_POST["captcha"]);


	if ($captcha != $_SESSION['captcha'])
	{
		$errors[] = lang("CAPTCHA_FAIL");
	}
	if(minMaxRange(5,25,$username))
	{
		$errors[] = lang("ACCOUNT_USER_CHAR_LIMIT",array(5,25));
	}
	if(!ctype_alnum($username)){
		$errors[] = lang("ACCOUNT_USER_INVALID_CHARACTERS");
	}
	if(minMaxRange(5,25,$displayname))
	{
		$errors[] = lang("ACCOUNT_DISPLAY_CHAR_LIMIT",array(5,25));
	}
    $allowedchars = array(' ', '.');
    if(!ctype_alnum(str_replace($allowedchars, '', $displayname))) {
	//if(!ctype_alnum($displayname)){
		$errors[] = lang("ACCOUNT_DISPLAY_INVALID_CHARACTERS");
	}
	if(minMaxRange(8,50,$password) && minMaxRange(8,50,$confirm_pass))
	{
		$errors[] = lang("ACCOUNT_PASS_CHAR_LIMIT",array(8,50));
	}
	else if($password != $confirm_pass)
	{
		$errors[] = lang("ACCOUNT_PASS_MISMATCH");
	}
	if(!isValidEmail($email))
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	//End data validation
	if(count($errors) == 0)
	{
		//Construct a user object
		$user = new User($username,$displayname,$password,$email);

		//Checking this flag tells us whether there were any errors such as possible data duplication occured
		if(!$user->status)
		{
			if($user->username_taken) $errors[] = lang("ACCOUNT_USERNAME_IN_USE",array($username));
			if($user->displayname_taken) $errors[] = lang("ACCOUNT_DISPLAYNAME_IN_USE",array($displayname));
			if($user->email_taken) 	  $errors[] = lang("ACCOUNT_EMAIL_IN_USE",array($email));
		}
		else
		{
			//Attempt to add the user to the database, carry out finishing  tasks like emailing the user (if required)
			if(!$user->userCakeAddUser())
			{
				if($user->mail_failure) $errors[] = lang("MAIL_ERROR");
				if($user->sql_failure)  $errors[] = lang("SQL_ERROR");
			}
		}
	}
	if(count($errors) == 0) {
		$successes[] = $user->success;
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Register</title>
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
				<h1>Let's get registered!</h1>
				<h2>You will be checking out devices in no time.</h2>
				
				<div id="regbox">
					<div id="alerts-container">
						<?php echo resultBlockStyled($errors,$successes); ?>
					</div>
					
					<form name="newUser" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
						<ul class="register">
							<li>
								<label>Username:</label>
								<input type="text" name="username" placeholder="Username" data-validation-help=" 5-25 characters are required" />
							</li>
							<li>
								<label>Display Name:</label>
								<input type="text" name="displayname" placeholder="Display Name" data-validation-help=" 5-25 characters are required" />
							</li>
							<li>
								<label>Email:</label>
								<input type="text" name="email" placeholder="Email" data-validation-help=" Enter a valid email address" />
							</li>
							<li>
								<label>Password:</label>
								<input type="password" name="password" placeholder="Password" data-validation-help=" 8-50 characters are required" />
							</li>
							<li>
								<label>Confirm Password:</label>
								<input class="left-align" type="password" name="passwordc" placeholder="Confirm Password" />
							</li>
							<li>
								<label>Security Code:</label>
								<img id="captcha" src="models/captcha.php">
							</li>
							<li>
								<label>Enter Security Code:</label>
								<input type="text" name="captcha" placeholder="Security Code" />
							</li>
							<li>
								<label>&nbsp;</label>
								<input type="submit" id="submit-button" value="Register" />
							</li>
						</ul>
					</form>
					<div class="clearFloats"></div>
					<div id="regLinks">
						<ul>
							<li class="li-links"><a href="login.php">Already registered? Click to login</a></li>
							<li class="li-links"><a href="forgot-password.php">Forgot your Password?</a></li>
						<?php if ($emailActivation == "true"): ?>
							<li class="li-links"><a href="resend-activation.php">Resend activation code</a></li>
						<?php endif; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php include 'footer.php'; ?>
 	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
 	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.1.47/jquery.form-validator.min.js"></script>
	<script> $.validate(); </script>
</body>
</html>
