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
		$row = mysqli_fetch_assoc(mysqli_query($conn,$query));
		
		echo "<div class='main-content' style='text-align:center;'>"; 
		
		echo '<h1>Welcome ' . $row['f_name'] . '. Here\'s your student profile.' . '</h1>'; 
		
		echo '<table class="entries student_profile" style=" margin-left:auto;margin-right:auto; width: 70%;">';
		
		// echo '<tr>';
		// echo '<th>Name</th> <th>Username</th> <th>Student ID</th> <th>Are you a current student</th> <th>Graduate or Under-graduate</th> <th>Number of units enrolled(if intern, you have no units)</th> <th>Are you a remote student</th> <th>Survey filled</th> <th>D-Clearance granted</th> <th>Resume</th> <th>Project enrolled</th> <th>Grade</th> <th>Your evaluation</th> <th>Active/Withdrawn</th>';
		// echo '</tr>';

		while($row = mysqli_fetch_assoc($login_info)) {
			echo '<tr>';
			echo '<th>Name</th>';
			echo '<td>' . $row['f_name'] . ' ' . $row['l_name'] . '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<th>Username</th>';
			echo '<td>' . $row['username'] . '</td>';
			echo '</tr>';
			if($row['current_student'] == 'Yes') {
				echo '<tr>';
				echo '<th>Student ID</th>';
				echo '<td>' . $row['s_id'] . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<th>Graduate or Under-graduate?</th>';
				echo '<td>' . $row['student_level'] . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<th>D-Clearance granted?</th>';
				echo '<td>' . $row['d_clearance'] . '</td>';
				echo '</tr>';
			}
			echo '<tr>';
			echo '<th>Are you a current USC student?</th>';
			echo '<td>' . $row['current_student'] . '</td>';
			echo '</tr>';

			echo '<tr>';
			echo '<th>Number of units enrolled (No units for intern)</th>';
			if($row['n_units'] == "1") {
				$units = "1 unit";
			} else if($row['n_units'] == "2") {
				$units = "2 units";
			} else if($row['n_units'] == "3") {
				$units = "3 units";
			} else if($row['n_units'] == "4+") {
				$units = "4+ units";
			} else if($row['n_units'] == "intern") {
				$units = "Unpaid Intern";
			} else {
				$units = "You haven't selected any units.";
			}
			echo '<td>' . $units . '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<th>Are you a remote student?</th>';
			echo '<td>' . $row['remote'] . '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<th>Survey filled?</th>';
			echo '<td>' . $row['survey'] . '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<th>Resume</th>';
			if (!empty($row['resume_name_on_server'])) {
				echo '<td>' . '<a href=\''.$row['resume_name_on_server'].'\' target=\'_blank\'>'.$row['resume_name_by_user'].'</a>' . '</td>';
			} else {
				echo '<td>' . 'No resume on file.' . '</td>';
			}
			echo '</tr>';
			echo '<tr>';
			echo '<th>Project enrolled</th>';
			if(empty($row['project_enrolled'])) {
				echo '<td>' . 'None' . '</td>';
			} else {
				echo '<td>' . $row['project_enrolled'] . '</td>';
			}
			echo '</tr>';
			echo '<tr>';
			echo '<th>Grade</th>';
			if($row['grade'] == NULL) {
				echo '<td>' . 'No grade assigned yet' . '</td>';
			} else {
				echo '<td>' . $row['grade'] . '</td>';
			}
			echo '</tr>';
			echo '<tr>';
			echo '<th>Student Evaluation</th>';
			if($row['student_evaluation'] == NULL) {
				echo '<td>' . 'You haven\'t been evaluated yet.' . '</td>';
			} else {
				echo '<td>' . $row['student_evaluation'] . '</td>';
			}
			echo '</tr>';
			echo '<tr>';
			echo '<th>Active/Withdrawn</th>';
			echo '<td>' . $row['status'] . '</td>';
			echo '</tr>';
		}

		echo '</table>';
		echo '</div><br /><br />';
	}
?>

<?php

if ($_SESSION['u_role'] == "Client") {
	$query = "SELECT f_name, l_name, username ";
	$query .= "FROM login_info ";
	$query .= "WHERE username='".$_SESSION['u_name']."' AND role='Client'";
	
	$login_info = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc(mysqli_query($conn,$query));
	
	$query = "SELECT project_name ";
	$query .= "FROM client_projects ";
	$query .= "WHERE client_email='" . $_SESSION['u_name'] . "'";

	$client_projects = mysqli_query($conn,$query);

	
	echo "<div class='main-content' style='text-align:center;'>"; 
	
	echo '<h1>Welcome ' . $row['f_name'] . '. Here\'s your info.' . '</h1>'; 
	
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
	echo '<br>';
	
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
<script>
$(document).ready(function() {
	$(".student_profile th:odd").css("background-color","#680606");
	$(".student_profile th:even").css("background-color","#590606");
});
</script>
<?php
	include('footer.php');
?>
