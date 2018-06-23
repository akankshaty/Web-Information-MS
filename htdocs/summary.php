<?php
date_default_timezone_set('America/Los_Angeles');
session_start();
include('auth.php');
if(!isset($_SESSION['u_name']) && basename($_SERVER['SCRIPT_FILENAME']) != "verify.php") { // Redirect user to login page if user is not logged-in (except for when user is verifying email (verify.php))
    header('Location: index.php');
    exit();
}
$url = parse_url((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_HOST); // Parses the domain of the DR Website
?>
<!DOCTYPE html>
<html lang="en" style="overflow:hidden;background-color: #fff;">
<head>
<meta charset="UTF-8">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="dr.css" />
<title>DR Course Summary</title>
</head>
<body>

<?php 
	$res = mysqli_query($conn,"SELECT * FROM login_info WHERE role='Student' AND verified_email='Yes'");
	$semester = mysqli_query($conn,"SELECT * FROM settings_option WHERE setting_name='Current Semester'");
	if(mysqli_num_rows($semester) > 0) {
		$semester = mysqli_fetch_assoc($semester)['setting_value'];
		echo '<h1 style="text-align: center;">DR Course Summary - '.$semester.' Semester</h1>';
	} else {
		echo '<h1 style="text-align: center;">DR Course Summary</h1>';
	}
	echo '<h3 style="text-align: center;text-decoration: underline;">Students</h3>';
	echo '<table class="summary" style="margin-left:auto;margin-right:auto;">';
	echo '
		<th>First Name</th>
		<th>Last Name</th>
		<th>Student ID</th>
		<th>USC Student</th>
		<th>Student Level</th>
		<th>Units</th>
		<th>Project</th>
		<th>Grade</th>';
		
	while($row = mysqli_fetch_assoc($res)) {
		echo '<tr>';
		echo '<td>';
		echo $row['f_name'];
		echo '</td>';
		echo '<td>';
		echo $row['l_name'];
		echo '</td>';
		echo '<td>';
		if (!empty($row['s_id'])) {
			echo $row['s_id'];
		} else {
			echo '-';
		}
		echo '</td>';
		echo '<td>';
		echo $row['current_student'];
		echo '</td>';
		echo '<td>';
		if (!empty($row['student_level'])) {
			echo $row['student_level'];
		} else {
			echo '-';
		}
		echo '</td>';
		echo '<td>';
		if (!empty($row['n_units'])) {
			if($row['n_units'] == "intern") {
				echo "Unpaid Intern";
			} else {
				echo $row['n_units'];
			}
		} else {
			echo '-';
		}
		echo '</td>';
		echo '<td>';
		if (!empty($row['project_enrolled'])) {
			echo $row['project_enrolled'];
		} else {
			echo '-';
		}
		echo '</td>';
		echo '<td>';
		if (!empty($row['grade'])) {
			echo $row['grade'];
		} else {
			echo '-';
		}
		echo '</td>';
		echo '</tr>';
	}
	echo '</table>';
	$res = mysqli_query($conn,"SELECT f_name,l_name,username FROM login_info WHERE role='Client'");
	echo '<h3 style="text-align: center;text-decoration: underline;">Clients</h3>';
	echo '<table class="summary" style="margin-left:auto;margin-right:auto;">';
	echo '
		<th>First Name</th>
		<th>Last Name</th>
		<th>Email</th>
		<th>Project</th>';
		
	while($row = mysqli_fetch_assoc($res)) {
		echo '<tr>';
		echo '<td>';
		echo $row['f_name'];
		echo '</td>';
		echo '<td>';
		echo $row['l_name'];
		echo '</td>';
		echo '<td>';
		echo $row['username'];
		echo '</td>';
		echo '<td>';
		$project_names = mysqli_query($conn,"SELECT * FROM client_projects WHERE client_email='".$row['username']."'");
		$project_arr = array();
		while($project_row = mysqli_fetch_assoc($project_names)) {
			$project_arr[] = $project_row['project_name'];
		}
		echo implode(", ",$project_arr);
		echo '</td>';
		echo '</tr>';
	}
	echo '</table>';
?>
<?php
	include('footer.php');
	mysqli_close($conn);
?>