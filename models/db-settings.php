<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

//Device Manager Information
$dc_base_url = "http://localhost:8080/device-manager";

//Database Information
$db_host = "localhost"; //Host address (most likely localhost)
$db_name = "device_manager"; //Name of Database
$db_user = "admin"; //Name of database user
$db_pass = "admin"; //Password for database user
$db_table_prefix = "uc_";

GLOBAL $dc_base_url;
GLOBAL $errors;
GLOBAL $successes;

$errors = array();
$successes = array();

/* Create a new mysqli object with database connection parameters */
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
GLOBAL $mysqli;

if(mysqli_connect_errno()) {
	echo "Connection Failed: " . mysqli_connect_errno();
	exit();
}

//Direct to install directory, if it exists
if(is_dir("install/"))
{
	header("Location: install/");
	die();

}

?>
