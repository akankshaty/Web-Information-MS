<?php
date_default_timezone_set('America/Los_Angeles');
if(!isset($_SESSION['u_name']) && basename($_SERVER['SCRIPT_FILENAME']) != "verify.php") { // Redirect user to login page if user is not logged-in (except for when user is verifying email (verify.php))
    header('Location: index.php');
    exit();
}
include('auth.php');
$url = parse_url((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_HOST); // Parses the domain of the DR Website
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="dr.css" />
<title>DR Profile</title>
</head>
<body>
	<div class="top_heading">
		<img id="usc_logo" src="images/usc_logo.png" />
	</div>
		<?php 
		$res = mysqli_query($conn,"SELECT * FROM login_info WHERE username='".$_SESSION['u_name']."'");
		$row = mysqli_fetch_assoc($res);
		if (isset($_SESSION['u_role'])) {
			echo '<div class="nav">';
				echo '<div class="menu-bar">';
					echo '<a href="profile.php"><p>Home</p></a>';
			echo '</div>';
			if ($_SESSION['u_role'] == "Student") {
				echo '<div class="menu-bar">';
					echo '<a href="survey.php"><p>Survey</p></a>';
				echo '</div>';
				echo '<div class="menu-bar">';
					echo '<a href="offers.php"><p>Offer Letters</p></a>';
				echo '</div>';
				if (!empty($row['project_enrolled'])) {
					echo '<div class="menu-bar">';
						echo '<a href="my_project.php"><p>My Project</p></a>';
					echo '</div>';				
				}
				echo '<div class="menu-bar">';
					echo '<a href="vacancies.php"><p>Vacancies</p></a>';
				echo '</div>';				
			} else if ($_SESSION['u_role'] == "Coordinator") {
				echo '<div class="menu-bar">';
					echo '<a href="students.php"><p>Students</p></a>';
				echo '</div>';
				echo '<div class="menu-bar">';
					echo '<a href="clients.php"><p>Clients</p></a>';
				echo '</div>';
				echo '<div class="menu-bar">';
					echo '<a href="projects.php"><p>Projects</p></a>';
				echo '</div>';
				echo '<div class="menu-bar">';
					echo '<a href="settings.php"><p>Settings</p></a>';
				echo '</div>';
			}  else if ($_SESSION['u_role'] == "Admin") {
				echo '<div class="menu-bar">';
					echo '<a href="students.php"><p>Students</p></a>';
				echo '</div>';
				echo '<div class="menu-bar">';
					echo '<a href="coordinators.php"><p>Coordinators</p></a>';
				echo '</div>';
			} else if  ($_SESSION['u_role'] == "Client") {
				echo '<div class="menu-bar">';
					echo '<a href="students.php"><p>Students</p></a>';
				echo '</div>';
				echo '<div class="menu-bar">';
					echo '<a href="projects.php"><p>Projects</p></a>';
				echo '</div>';
			}
				echo '<div class="menu-bar">';
					echo '<a href="logout.php"><p>Logout</p></a>';
				echo '</div>';
			echo '</div>';
		}
		?>

	