<?php
/*
UserCake Version: 2.0.2
http://usercake.com
*/

require_once("models/config.php");
if (!securePage($_SERVER['PHP_SELF'])){die();}

$pages = getPageFiles(); //Retrieve list of pages in root usercake folder
$dbpages = fetchAllPages(); //Retrieve list of pages in pages table
$creations = array();
$deletions = array();

//Check if any pages exist which are not in DB
foreach ($pages as $page){
	if(!isset($dbpages[$page])){
		$creations[] = $page;	
	}
}

//Enter new pages in DB if found
if (count($creations) > 0) {
	createPages($creations)	;
}

if (count($dbpages) > 0){
	//Check if DB contains pages that don't exist
	foreach ($dbpages as $page){
		if(!isset($pages[$page['page']])){
			$deletions[] = $page['id'];	
		}
	}
}

//Delete pages from DB if not found
if (count($deletions) > 0) {
	deletePages($deletions);
}

//Update DB pages
$dbpages = fetchAllPages();
?>

<!DOCTYPE html>
<html>
<head>
	<title>DeviceGenie - Admin Pages</title>
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
				<h1>Admin Pages</h1>
				<h2>View privacy settings for existing pages</h2>
			</div>
			
			<div id="admin">
				<div id="alerts-container">
					<?php echo resultBlockStyled($errors,$successes); ?>
				</div>

				<table class="admin">
					<tr>
						<tr><th>Id</th><th>Page</th><th>Access</th></tr>
					</tr>
					
					<!-- Display list of pages -->
					<?php foreach ($dbpages as $page): ?>
					<tr>
						<td><?= $page['id']; ?></td>
						<td><a href="admin_page.php?id=<?= $page['id']; ?>"><?= $page['page']; ?></a></td>
						<td>
							<?php if ($page['private'] == 0): ?>
							Public
							<?php else: ?>
							Private
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</table>
				<div class="clearFloats"></div>
			</div>
		</div>
	</div>
	<?php include 'footer.php'; ?>
</body>
</html>
