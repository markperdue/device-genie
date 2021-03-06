<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Forms posted
if(!empty($_POST) && $emailActivation)
{
	$email = $_POST["email"];
	$username = $_POST["username"];
	
	//Perform some validation
	//Feel free to edit / change as required
	if(trim($email) == "")
	{
		$errors[] = lang("ACCOUNT_SPECIFY_EMAIL");
	}
	//Check to ensure email is in the correct format / in the db
	else if(!isValidEmail($email) || !emailExists($email))
	{
		$errors[] = lang("ACCOUNT_INVALID_EMAIL");
	}
	
	if(trim($username) == "")
	{
		$errors[] =  lang("ACCOUNT_SPECIFY_USERNAME");
	}
	else if(!usernameExists($username))
	{
		$errors[] = lang("ACCOUNT_INVALID_USERNAME");
	}
	
	if(count($errors) == 0)
	{
		//Check that the username / email are associated to the same account
		if(!emailUsernameLinked($email,$username))
		{
			$errors[] = lang("ACCOUNT_USER_OR_EMAIL_INVALID");
		}
		else
		{
			$userdetails = fetchUserDetails($username);
			
			//See if the user's account is activation
			if($userdetails["active"]==1)
			{
				$errors[] = lang("ACCOUNT_ALREADY_ACTIVE");
			}
			else
			{
				if ($resend_activation_threshold == 0) {
					$hours_diff = 0;
				}
				else {
					$last_request = $userdetails["last_activation_request"];
					$hours_diff = round((time()-$last_request) / (3600*$resend_activation_threshold),0);
				}
				
				if($resend_activation_threshold!=0 && $hours_diff <= $resend_activation_threshold)
				{
					$errors[] = lang("ACCOUNT_LINK_ALREADY_SENT",array($resend_activation_threshold));
				}
				else
				{
					//For security create a new activation url;
					$new_activation_token = generateActivationToken();
					
					if(!updateLastActivationRequest($new_activation_token,$username,$email))
					{
						$errors[] = lang("SQL_ERROR");
					}
					else
					{
						$mail = new userCakeMail();
						
						$activation_url = $websiteUrl."activate-account.php?token=".$new_activation_token;
						
						//Setup our custom hooks
						$hooks = array(
							"searchStrs" => array("#ACTIVATION-URL","#USERNAME#"),
							"subjectStrs" => array($activation_url,$userdetails["display_name"])
							);
						
						if(!$mail->newTemplateMsg("resend-activation.txt",$hooks))
						{
							$errors[] = lang("MAIL_TEMPLATE_BUILD_ERROR");
						}
						else
						{
							if(!$mail->sendMail($userdetails["email"],"Activate your ".$websiteName." Account"))
							{
								$errors[] = lang("MAIL_ERROR");
							}
							else
							{
								//Success, user details have been updated in the db now mail this information out.
								$successes[] = lang("ACCOUNT_NEW_ACTIVATION_SENT");
							}
						}
					}
				}
			}
		}
	}
}

//Prevent the user visiting the logged in page if he/she is already logged in
if(isUserLoggedIn()) { header("Location: account.php"); die(); }
?>

<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Resend Activation</title>
	<link rel="stylesheet" type="text/css" href="genie.css">
	<script src='models/funcs.js' type='text/javascript'>
	</script>
</head>
<body>
    <?php include 'navbar-search.php'; ?>
    <div id="margin-top-medium"></div>
	<div id="content-medium">
		<div style="text-align: center;">
			<div class="colored-bg-round">
				<div class="padding-top-medium"></div>
				
				<div id="regbox">
					
					<h1>Resend Activation</h1>
					<!-- Show disabled if email activation not required -->
					<?php if ($emailActivation === 'true'): ?>
						<h2>Please provide the username and email that you used to register. A new activation code will be emailed to you.</h2>
						<div id="alerts-container">
							<?php echo resultBlockStyled($errors,$successes); ?>
						</div>
						
						<form name="resendActivation" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
							<ul class="register">
								<li>
									<label>Username:</label>
									<input type="text" name="username" />
								</li>
								<li>
									<label>Email:</label>
									<input type="text" name="email" />
								</li>
								<li>
									<label>&nbsp;</label>
									<input type="submit" id="submit-button" value="Resend Activation" />
								</li>
							</ul>
						</form>
					<?php else: ?>
						<h2><?php echo lang("FEATURE_DISABLED"); ?></h2>
						<br/>
					<?php endif; ?>
					<div class="clearFloats"></div>
					<ul>
						<li class="li-links"><a href="login.php">Already registered? Click to login</a></li>
						<li class="li-links"><a href="register.php">Not a member? Sign up!</a></li>
						<li class="li-links"><a href="forgot-password.php">Forgot your Password?</a></li>
					</ul>
				</div>
				<div class="clearFloats"></div>
			</div>
		</div>
	</div>
	<?php include 'footer.php'; ?>
</body>
</html>
