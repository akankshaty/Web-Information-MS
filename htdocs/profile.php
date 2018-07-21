<?php 
	session_start();
	include('header.php');
?>
<?php 

	if(isset($_POST['accept_vacancy_application'])) { // If a client accepted a student's vacancy application
		mysqli_query($conn,"DELETE FROM vacancy_applications WHERE student_email='".$_POST['s_email']."'"); // Delete all vacancy applications of the student
		mysqli_query($conn,"UPDATE login_info SET project_enrolled='".$_POST['p_name']."' WHERE username='".$_POST['s_email']."'");
		$res = mysqli_query($conn,"SELECT * FROM offer_letter_requests WHERE student_email='".$_POST['s_email']."' AND project_name='".$_POST['p_name']."' AND role_name='".$_POST['r_name']."'");
		mysqli_query($conn,"UPDATE offer_letter_requests SET status='Declined' WHERE student_email='".$student_email."' AND status='Pending'");
		if(mysqli_num_rows($res) < 1) {
			mysqli_query($conn,"INSERT INTO offer_letter_requests (student_email,project_name,role_name,status) VALUES ('".$_POST['s_email']."','".$_POST['p_name']."','".$_POST['r_name']."','Added')");
		} else {
			mysqli_query($conn,"UPDATE offer_letter_requests SET status='Added' WHERE student_email='".$_POST['s_email']."' AND project_name='".$_POST['p_name']."' AND role_name='".$_POST['r_name']."'");
		}
	}
	if(isset($_POST['grade_value'])) { // Change the grade of student
		// Update information into login_info table
		mysqli_query($conn,"UPDATE login_info SET grade='".$_POST['grade_value']."' WHERE username='".$_POST['student_email']."'");
	}
	if(isset($_POST['flag_approve'])) { // DR Coordinator approved the flag request of client
		// Update information into login_info table
		mysqli_query($conn,"UPDATE login_info SET project_enrolled='' WHERE username='".$_POST['student_email']."'");
		mysqli_query($conn,"DELETE FROM offer_letter_requests WHERE student_email='".$_POST['student_email']."' AND status='Flagged'");
	}
	if(isset($_POST['flag_decline'])) { // DR Coordinator declined the flag request of client
		mysqli_query($conn,"UPDATE offer_letter_requests SET status='Added' WHERE student_email='".$_POST['student_email']."' AND status='Flagged'");
	}

?>
<?php
	if ($_SESSION['u_role'] == "Coordinator") {
		//header("Location: index.php");
		
		
		if (isset($_GET['filterBy']) && $_GET['filterBy'] == "not_graded")  { // Students who are have not received grades
			// query for students under client
			$res = mysqli_query($conn,"SELECT s.f_name as sfname,s.l_name as slname,s.username,s.project_enrolled,s.grade,s.status, c.f_name as cfname, c.l_name as clname
			FROM login_info s , client_projects p, login_info c
			WHERE 
			(s.project_enrolled = p.project_name) and
			p.client_email=c.username and s.project_enrolled is not null and not s.project_enrolled='' and s.grade='' and s.status='Active' and s.role='Student' order by s.project_enrolled");
			// query for students under DR
			$res1 = mysqli_query($conn,"SELECT s.f_name as sfname,s.l_name as slname,s.username,s.project_enrolled,s.grade,s.status
			FROM login_info s
			WHERE 
			s.project_enrolled is not null and not s.project_enrolled='' and s.grade='' and s.role='Student' and s.status='Active' and s.project_enrolled not in (select p.project_name from client_projects p) order by s.project_enrolled");
		} else {
			// query for students under client
			$res = mysqli_query($conn,"SELECT s.f_name as sfname,s.l_name as slname,s.username,s.project_enrolled,s.grade,s.status, c.f_name as cfname, c.l_name as clname
			FROM login_info s , client_projects p, login_info c
			WHERE 
			(s.project_enrolled = p.project_name) and
			p.client_email=c.username and s.project_enrolled is not null and not s.project_enrolled='' order by s.project_enrolled");
			// query for students under DR
			$res1 = mysqli_query($conn,"SELECT s.f_name as sfname,s.l_name as slname,s.username,s.project_enrolled,s.grade,s.status
			FROM login_info s
			WHERE 
			s.project_enrolled is not null and not s.project_enrolled='' and s.role='Student' and s.project_enrolled not in (select p.project_name from client_projects p) order by s.project_enrolled");
		}
		
		echo '<div class="main-content">'; 
		echo '<h1>Home</h1>'; 
		echo '<h2>Flagged Students</h2>';
		echo '<p>List of all students who were flagged by client. You can either approve the request or decline it. If approved, the student will be removed from the project. If not, then they will stay in their current project.</p>';
		echo '<table class="entries">';
		echo '<tr>';
		echo '<th>Student Name</th><th>Student Email</th><th>Project</th><th>Accept/Decline</th>';
		echo '</tr>';
		$flagged_students = mysqli_query($conn,"SELECT li.f_name,li.l_name,li.username,li.project_enrolled FROM login_info li LEFT JOIN (SELECT * FROM offer_letter_requests WHERE status='Flagged') olr ON li.username=olr.student_email WHERE li.username=olr.student_email");
		while($fs = mysqli_fetch_assoc($flagged_students)) {
			echo '<tr>';
			echo '<td>'.$fs['f_name'].' '.$fs['l_name'].'</td>';
			echo '<td>'.$fs['username'].'</td>';
			echo '<td>'.$fs['project_enrolled'].'</td>';
			echo '<td><img src="images/accept.png" class="accept_btn" onclick="get_data(\'accept\',\''.$fs['username'].'\');"><img src="images/decline.png" class="decline_btn" onclick="get_data(\'decline\',\''.$fs['username'].'\')"></td>';
			echo '</tr>';
		}
		if(mysqli_num_rows($flagged_students) < 1) {
			echo '<td colspan="10">No flag requests to show.</td>';
		}
		echo '</table><br />';
		echo '<p>List of all students who are enrolled in project and their corresponding client.</p>';
		echo '<table class="entries">';
		echo '<tr>';
		echo '<th>Student</th> <th>Project</th> <th>Client</th> <th>Assign Grade</th> <th>Status</th>';
		echo '</tr>';
		
		//students under DR
		while($row = mysqli_fetch_assoc($res1)){
			echo '<tr>';
			
			echo '<td>'.$row['sfname'].' '.$row['slname'].'</td>';
			echo '<td>'.$row['project_enrolled'].'</td>';
			echo '<td>DR Coordinator</td>';
			echo '<td>';
			echo '<select name="grade" class="grade-*'.$row['username'].'" style="padding:5px 10px;margin:0 5px;border-radius:5px;" >';
			if(empty($row['grade'])) {
				echo '<option value="" selected>Select Grade</option>';
				echo '<option value="Passed">Passed</option>';
				echo '<option value="Failed">Failed</option>';
			} else if ($row['grade'] == "Passed") {
				echo '<option value="">Select Grade</option>';
				echo '<option value="Passed" selected>Passed</option>';
				echo '<option value="Failed">Failed</option>';									
			} else {
				echo '<option value="">Select Grade</option>';
				echo '<option value="Passed">Passed</option>';
				echo '<option value="Failed" selected>Failed</option>';									
			}
			echo '</select>';
			echo '<img src="images/green_check_mark.png" class="grade-*'.$row['username'].'" style="display:none;width:15px;margin:14px 0 0 4px;"/>';
			echo '</td>';
			echo '<td>'.$row['status'].'</td>';
			
			echo '</tr>';
		}
		//students under client
		while($row = mysqli_fetch_assoc($res)){
			echo '<tr>';
			
			echo '<td>'.$row['sfname'].' '.$row['slname'].'</td>';
			echo '<td>'.$row['project_enrolled'].'</td>';
			echo '<td>'.$row['cfname'].' '.$row['clname'].'</td>';
			echo '<td>';
			echo '<select name="grade" class="grade-*'.$row['username'].'" style="padding:5px 10px;margin:0 5px;border-radius:5px;" >';
			if(empty($row['grade'])) {
				echo '<option value="" selected>Select Grade</option>';
				echo '<option value="Passed">Passed</option>';
				echo '<option value="Failed">Failed</option>';
			} else if ($row['grade'] == "Passed") {
				echo '<option value="">Select Grade</option>';
				echo '<option value="Passed" selected>Passed</option>';
				echo '<option value="Failed">Failed</option>';									
			} else {
				echo '<option value="">Select Grade</option>';
				echo '<option value="Passed">Passed</option>';
				echo '<option value="Failed" selected>Failed</option>';									
			}
			echo '</select>';
			echo '<img src="images/green_check_mark.png" class="grade-*'.$row['username'].'" style="display:none;width:15px;margin:14px 0 0 4px;"/>';
			echo '</td>';
			echo '<td>'.$row['status'].'</td>';
			
			echo '</tr>';
		}
		if ((mysqli_num_rows($res) < 1) && (mysqli_num_rows($res1) < 1)) {
			echo '<tr>';
				echo '<td colspan="10">';
				echo 'No results to show.';
				echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
	echo '<p>See students who: 
	<select name="filter_student" onchange="updateFilter(this.value);">
			<option value="all">Show all students who are enrolled in project</option>';
			if (isset($_GET["filterBy"]) && ($_GET["filterBy"] == "not_graded")) {
				echo '<option value="not_graded" selected>are active and enrolled in project but have not been graded</option>';
			} else {
				echo '<option value="not_graded">is active and enrolled in project but have not been graded</option>';				
			}
			
	echo '</select></p>';
		echo '</div><br /><br />';
	}

?>
	<?php 
	if ($_SESSION['u_role'] == "Admin") {
		echo '<div class="main-content">';
		echo '<h1 style="text-align:center;">Welcome Admin</h1>';
		echo '<h2>Students - changed units</h2>';
		echo '<p>List of students who recently changed the registered number of units. (<span id="download_changed_units_file" style="cursor: pointer; text-decoration: underline; color: blue;"><strong>Download this list</strong></span> | <span id="clear_list" style="cursor: pointer; text-decoration: underline; color: blue;"><strong>Clear this list</strong></span>)</p>
	<table class="entries">
		<thead>
			<tr>
				<th>Name</th>
				<th>Email Address</th>';
				if($_SESSION['u_role'] == "Coordinator" OR $_SESSION['u_role'] == "Admin") echo '<th>Changed From</th>';
				if($_SESSION['u_role'] == "Coordinator" OR $_SESSION['u_role'] == "Admin") echo '<th>Changed To</th>';
				if($_SESSION['u_role'] == "Coordinator" OR $_SESSION['u_role'] == "Admin") echo '<th>Timestamp</th>';
		echo '</tr>';

				
		$students = mysqli_query($conn, "SELECT li.f_name,li.l_name,cu.username,cu.from_units,cu.to_units,cu.timestamp FROM changed_units cu LEFT JOIN login_info li ON li.username=cu.username WHERE li.role='Student' AND li.status='Active'");
		if (mysqli_num_rows($students) > 0) {
			while($row = mysqli_fetch_assoc($students)) {
				echo '<tr>';
					echo '<td>';
					echo $row['f_name'].' '.$row['l_name'];
					echo '</td>';
					echo '<td>';
					echo $row['username'];
					echo '</td>';
					

						echo '<td>';
							if($row['from_units'] == "1") {
								echo '1 unit';
							} else if($row['from_units'] == "2") {
								echo '2 units';
							} else if($row['from_units'] == "3") {
								echo '3 units';
							} else if($row['from_units'] == "4+") {
								echo '4+ units';
							} else if($row['from_units'] == "intern") {
								echo 'Unpaid Intern';
							} else {
								echo '-';
							}
						echo '</td>';
						echo '<td>';
							if($row['to_units'] == "1") {
								echo '1 unit';
							} else if($row['to_units'] == "2") {
								echo '2 units';
							} else if($row['to_units'] == "3") {
								echo '3 units';
							} else if($row['to_units'] == "4+") {
								echo '4+ units';
							} else if($row['to_units'] == "intern") {
								echo 'Unpaid Intern';
							} else {
								echo '-';
							}
						echo '</td>';
						echo '<td>';
							echo $row['timestamp'];
						echo '</td>';
			}
				echo '</tr>';
		} else {
			echo '<tr>';
				echo '<td colspan="10">';
				echo 'No results to show.';
				echo '</td>';
			echo '</tr>';
		}

	echo '</thead>
	</table>';
	echo '</div><br />';
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
			if(empty($row['grade'])) {
				echo '<td>' . 'No grade assigned yet' . '</td>';
			} else if ($row['grade'] == "Passed"){
				echo '<td class="font_green font_bold">' . $row['grade'] . '</td>';
			} else if ($row['grade'] == "Failed"){
				echo '<td class="font_red font_bold">' . $row['grade'] . '</td>';
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
	$vacancy_applications = mysqli_query($conn,"SELECT * FROM (SELECT li.f_name,li.l_name,li.username,li.survey,li.project_enrolled,li.status,va.project_name,va.role_name FROM vacancy_applications va LEFT JOIN login_info li ON va.student_email=li.username) dt LEFT JOIN client_projects cp ON dt.project_name=cp.project_name WHERE client_email='".$_SESSION['u_name']."' AND status='Active' AND (project_enrolled IS NULL OR project_enrolled='')");
	echo "<div class='main-content' style='text-align:center;'>"; 
	
	echo '<h1>Welcome ' . $row['f_name'] . '. Here\'s your info.' . '</h1>'; 
	
	echo '<table class="entries client_profile" style=" margin-left:auto;margin-right:auto; width: 70%;">';

	while($row = mysqli_fetch_assoc($login_info)) {
		echo '<tr>';
		echo '<th>Name</th>';
		echo '<td>' . $row['f_name'] . ' ' . $row['l_name'] . '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th>Username</th>';
		echo '<td>' . $row['username'] . '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th>Your Projects</th>';
		$proj_arr = array();
		while($row = mysqli_fetch_assoc($client_projects)) {
			$proj_arr[] = $row['project_name'];
		}
		if(!empty($proj_arr)) {
			echo '<td>' . implode(", ",$proj_arr) . '</td>';
		} else {
			echo "<td>You have no projects.</td>";
		}
		echo '</tr>';
	}
	echo '</table>';
	echo '<br>';
	
	echo '<h1>Vacancy Applications</h1>';
	echo '<p style="text-align: left;">Here\'s a list of applications you have received from students for each vacancies you have listed.</p>';
	echo '<table  class="entries">';
	echo '<tr>';
	echo '<th>Student Name</th>';
	echo '<th>Email</th>';
	echo '<th>Survey</th>';
	echo '<th>Project Applied</th>';
	echo '<th>Role Applied</th>';
	echo '<th>Accept Student?</th>';
	echo '</tr>';	
	while($row = mysqli_fetch_assoc($vacancy_applications)) {
		$name = $row['f_name']." ".$row['l_name'];
		echo '<tr>';
		echo '<td>'.$name.'</td>';
		echo '<td>'.$row['username'].'</td>';
		echo '<td>';
		if ($row['survey'] == "No") {
			echo "Not Filled";
		} else {
			echo '<a href="survey.php?student='.$row['username'].'" target="_blank">View</a>';
		}
		echo '</td>';
		echo '<td>'.$row['project_name'].'</td>';
		echo '<td>'.$row['role_name'].'</td>';
		$proj_encoded = str_replace(')','.',str_replace('(',':',str_replace(' ','_',$row['project_name'])));
		$role_encoded = str_replace(')','.',str_replace('(',':',str_replace(' ','_',$row['role_name'])));
		
		echo '<td><img id="accept-*'.$row['username'].'-*'.$proj_encoded.'-*'.$role_encoded.'" class="accept" src="images/accept.png" style="cursor:pointer;"/></td>';
		echo '</tr>';
	}
	if(mysqli_num_rows($vacancy_applications) < 1) { // If there are no vacancy applications for this client
		echo '<tr>';
		echo '<td colspan="10">You have not received any vacancy applications from students.</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</div><br /><br />';
}
?>
<script>
$(document).ready(function() {
	$(".student_profile th:odd,.client_profile th:odd").css("background-color","#790808");
	$(".student_profile th:even,.client_profile th:even").css("background-color","#680808");
	$(".student_profile td:odd,.client_profile td:odd").css("background-color","#f4f4f4");
	$(".student_profile td:even,.client_profile td:even").css("background-color","#e3e3e3");
	
	$(".accept_btn,.decline_btn").css("margin","0 10px");
	$(".accept_btn").mouseenter(function(e){
		$(".accept_btn").attr("src","images/accept_hover.png");
		$(".accept_btn").css("cursor","pointer");
	});
	$(".accept_btn").mouseout(function(e){
		$(".accept_btn").attr("src","images/accept.png");
	});
	$(".decline_btn").mouseenter(function(e){
		$(".decline_btn").attr("src","images/decline_hover.png");
		$(".decline_btn").css("cursor","pointer");
	});
	$(".decline_btn").mouseout(function(e){
		$(".decline_btn").attr("src","images/decline.png");
	});
	
	$(".accept").click(function(e) {
		s_email = e.target.id.split("-*")[1];
		p_name = e.target.id.split("-*")[2].replace(".",")").replace(":","(").replace("_"," ");
		r_name = e.target.id.split("-*")[3].replace(".",")").replace(":","(").replace("_"," ");
		if (confirm('Are you sure you want to accept this student as a "'+r_name+'" for '+p_name+' project? This action cannot be undone.')) {
			$.ajax({
				url: "profile.php",
				type: "POST",
				data: { "accept_vacancy_application": true, "s_email": s_email, "p_name": p_name, "r_name": r_name },
				success: function(response){
					
				}
			});
			window.replace.location("profile.php");
		} else {
			return false;
		}
	});
	$("select[name=grade]").change(function(e) {
		var student_email = $(this).attr("class").replace('grade-*','');
		var grade_value = $("."+$.escapeSelector($(this).attr("class"))+" option:selected").val();
		$.ajax({
			url: "profile.php",
			type: "POST",
			data: { "grade_value": grade_value, "student_email": student_email},
			success: function(response){

			}
			
		});
		$("img[class="+$.escapeSelector($(this).attr("class"))+"]").show();
	});
});
function updateFilter(value) {
	if (value == "not_graded") {
		window.location.replace("profile.php?filterBy=not_graded");
		$("option[value=not_graded]").attr('selected', true);
	} else {
		window.location.replace("profile.php");
		$("option[value=all]").attr('selected', true);		
	}
}
function get_data(val_type,student_email) {
	if(val_type == "accept") {
		if (confirm('Are you sure you want to approve this request? The student will be removed from this project. This action cannot be undone.')) {
			$.ajax({
				url: "profile.php",
				type: "POST",
				data: { "flag_approve": true, "student_email": student_email},
				success: function(response){
					window.location.reload();
				}
				
			});
		} else {
			return false;
		}
	} else if (val_type == "decline") {
		if (confirm('Are you sure you want to decline this request? The student will stay in this project. This action cannot be undone.')) {
			$.ajax({
				url: "profile.php",
				type: "POST",
				data: { "flag_decline": true, "student_email": student_email},
				success: function(response){
					window.location.reload();
				}
				
			});
		} else {
			return false;
		}
	}
}
</script>
<?php
	include('footer.php');
?>
