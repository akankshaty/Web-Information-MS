<?php 
	session_start();
	include('header.php');
?>

<?php
	if ($_SESSION['u_role'] == "Coordinator") {
		//header("Location: index.php");
		
		// query for students under client
		$res = mysqli_query($conn,"SELECT s.f_name as sfname,s.l_name as slname,s.project_enrolled, c.f_name as cfname, c.l_name as clname
		FROM login_info s , client_projects p, login_info c
		WHERE 
		(s.project_enrolled = p.project_name) and
		p.client_email=c.username order by s.project_enrolled");
		// query for students under DR
		$res1 = mysqli_query($conn,"SELECT s.f_name as sfname,s.l_name as slname,s.project_enrolled
		FROM login_info s
		WHERE 
		s.project_enrolled is not null and s.role='Student' and s.project_enrolled not in (select p.project_name from client_projects p) order by s.project_enrolled");
		
		echo '<div class="main-content">'; 
		echo '<h1>Home</h1>'; 
		
		echo '<table  class="entries">';
		echo '<tr>';
		echo '<th>Student</th> <th>Project</th> <th>Client</th>';
		echo '</tr>';
		
		//students under DR
		while($row = mysqli_fetch_assoc($res1)){
			echo '<tr>';
			
			echo '<td>'.$row['sfname'].' '.$row['slname'].'</td>';
			echo '<td>'.$row['project_enrolled'].'</td>';
			echo '<td>DR Coordinator</td>';
			
			echo '</tr>';
		}
		//students under client
		while($row = mysqli_fetch_assoc($res)){
			echo '<tr>';
			
			echo '<td>'.$row['sfname'].' '.$row['slname'].'</td>';
			echo '<td>'.$row['project_enrolled'].'</td>';
			echo '<td>'.$row['cfname'].' '.$row['clname'].'</td>';
			
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';
	}

?>

<?php
	if ($_SESSION['u_role'] == "Student") {

		$query = "SELECT f_name, l_name, username, s_id, current_student, student_level, n_units, remote, survey, d_clearance, resume_name_by_user, resume_name_on_server, project_enrolled, grade, student_evaluation, status ";
		$query .= "FROM login_info ";
		$query .= "WHERE username='".$_SESSION['u_name']."' AND role='Student'";
		
		$login_info = mysqli_query($conn,$query);

		echo "<div style='margin-left: 20px;'>"; 
		
		echo '<h1>Welcome ' . $_SESSION['u_name'] . '. Here\'s your info.' . '</h1>'; 
		
		echo '<table  class="entries">';
		
		echo '<tr>';
		echo '<th>Name</th> <th>Username</th> <th>Student ID</th> <th>Are you a current student</th> <th>Graduate or Under-graduate</th> <th>Number of units enrolled(if intern, you have no units)</th> <th>Are you a remote student</th> <th>Survey filled</th> <th>D-Clearance granted</th> <th>Resume</th> <th>Project enrolled</th> <th>Grade</th> <th>Your evaluation</th> <th>Active/Withdrawn</th>';
		echo '</tr>';

		while($row = mysqli_fetch_assoc($login_info)) {
			echo '<tr>';

			echo '<td>' . $row['f_name'] . ' ' . $row['l_name'] . '</td>';
			echo '<td>' . $row['username'] . '</td>';
			echo '<td>' . $row['s_id'] . '</td>';
			echo '<td>' . $row['current_student'] . '</td>';
			echo '<td>' . $row['student_level'] . '</td>';
			echo '<td>' . $row['n_units'] . '</td>';
			echo '<td>' . $row['remote'] . '</td>';
			echo '<td>' . $row['survey'] . '</td>';
			echo '<td>' . $row['d_clearance'] . '</td>';
			echo '<td>' . '<a href=\''.$row['resume_name_on_server'].'\' target=\'_blank\'>'.$row['resume_name_by_user'].'</a>' . '</td>';
			echo '<td>' . $row['project_enrolled'] .'</td>';
			if($row['grade'] == NULL) {
				echo '<td>' . 'No grade assigned yet' . '</td>';
			} else {
				echo '<td>' . $row['grade'] . '</td>';
			}
			if($row['student_evaluation'] == NULL) {
				echo '<td>' . 'You haven\'t been evaluated yet.' . '</td>';
			} else {
				echo '<td>' . $row['student_evaluation'] . '</td>';
			}
			echo '<td>' . $row['status'] . '</td>';

			echo '</tr>';
		}

		echo '</table>';
		echo '</div>';
	}
?>

<?php

if ($_SESSION['u_role'] == "Client") {
	$query = "SELECT f_name, l_name, username ";
	$query .= "FROM login_info ";
	$query .= "WHERE username='".$_SESSION['u_name']."' AND role='Client'";
	
	$login_info = mysqli_query($conn,$query);

	$query = "SELECT project_name ";
	$query .= "FROM client_projects ";
	$query .= "WHERE client_email='" . $_SESSION['u_name'] . "'";

	$client_projects = mysqli_query($conn,$query);

	echo "<div style='margin-left: 20px;'>"; 
	
	echo '<h1>Welcome ' . $_SESSION['u_name'] . '. Here\'s your info.' . '</h1>'; 
	
	echo '<table  class="entries">';
	
	echo '<tr>';
	echo '<th>Name</th> <th>Username</th>';
	echo '</tr>';

	while($row = mysqli_fetch_assoc($login_info)) {
		echo '<tr>';

		echo '<td>' . $row['f_name'] . ' ' . $row['l_name'] . '</td>';
		echo '<td>' . $row['username'] . '</td>';

		echo '</tr>';
	}
	echo '</table>';
	echo '<br> <br>';
	
	echo '<h1> Here are your projects </h1>'; 
	
	echo '<table  class="entries">';
	
	echo '<tr>';
	echo '<th>Your projects</th>';
	echo '</tr>';	
	while($row = mysqli_fetch_assoc($client_projects)) {
		echo '<tr>';
		echo '<td>' . $row['project_name'] . '</td>';
		echo '</tr>';
	}
	echo '</table>';

	echo '</div>';
}
?>

<?php
	include('footer.php');
?>
