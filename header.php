<?php
require_once("models/config.php");
?>
<div id="header">
    <!-- <span id="header-span">Website going down for maintenance. Stand by!</span> -->
    <span id="header-span">
        <a href="index.php">Home</a>
        This is beta software! Bugs? Suggestions? Email <a href="mailto:markaperdue@righteousbanana.com?Subject=Device%20checkout%20email" target="_top">markaperdue@righteousbanana.com</a>
        <?php
        if (isUserLoggedIn()) {
            echo "
            -- <a href='account.php'>$loggedInUser->username</a>
            ";

            // Check if user is a device creator or an admin
            if ($loggedInUser->checkPermission(array(2)) or $loggedInUser->checkPermission(array(3))) {
                echo "
                <a href='add_device.php'>Add Device</a>
                ";
            }
            echo "
            <a href='logout.php'>Logout</a>
            --
            ";
        }
        else {
            echo "
            --
            <a href='login.php'>Login</a>
            <a href='register.php'>Register</a>
            ";
        }
        ?>
    </span>
</div>
