<?php 
	session_start();
	include('header.php');
	include('auth.php');
	
	if ($_SESSION['u_role'] != "Student") {
		header("Location: index.php");
	}
	

?>
<div class="main-content">
	<?php 
		$res = mysqli_query($conn,"SELECT * FROM settings_option WHERE setting_name='Accepting Offer Letter Deadline'");
		$unformatted_deadline = mysqli_fetch_assoc($res)['setting_value'];
		$deadline = date("l jS, F Y h:i:s A",$unformatted_deadline);
		echo '<h1>My Project</h1>';
		echo '<p style="line-height:2em;">You can view all your project details down below.</p>';

		echo '<table class="entries project_details">
		<thead>';
			// Get the student's project name and other details
			$project = mysqli_query($conn, "SELECT * FROM offer_letter_requests WHERE student_email='".$_SESSION['u_name']."' AND (status='Accepted' OR status='Added')");
			$skill_list = mysqli_query($conn,"SELECT olr.project_name,olr.role_name,ps.skill_name FROM project_skills ps LEFT JOIN offer_letter_requests olr ON olr.project_name=ps.project_name WHERE student_email='".$_SESSION['u_name']."' AND (status='Accepted' OR status='Added')");
			$login_info = mysqli_query($conn,"SELECT * FROM login_info WHERE username='".$_SESSION['u_name']."'");
			$login_info = mysqli_fetch_assoc($login_info);
			while($row1 = mysqli_fetch_assoc($project)) { // Prints out each row from offer_letter_requests db table results one by one
				echo '<tr>';
					echo '<th>Project Name</th>';
					echo '<td>';
					echo $row1['project_name'];
					echo '</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<th>Role Name</th>';
					echo '<td>';
					echo $row1['role_name'];
					echo '</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<th>Relevant Skills</th>';
					echo '<td>';
					$skill_arr = array();
					while($skills_row = mysqli_fetch_assoc($skill_list)) {
						$skill_arr[] = $skills_row['skill_name'];
					}
					if(!empty($skill_arr)) {
						echo implode(", ",$skill_arr);
					} else {
						echo '<p style="font-style:italic;">No skills added by client.</p>';
					}
					echo '</td>';
				echo '<tr>';
					echo '<th>Grade</th>';					
					echo '<td>';
					if(empty($row['grade'])) {
						echo '--';
					} else if ($row['grade'] == "Passed"){
						echo '<span class="font_green font_bold">' . $row['grade'] . '</span>';
					} else if ($row['grade'] == "Failed"){
						echo '<span class="font_red font_bold">' . $row['grade'] . '</span>';
					}
					echo '</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<th>Attendance</th>';
					echo '<td>';
					echo '--';
					echo '</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<th>Student Evaluation</th>';
					echo '<td>';
					if (empty($login_info['student_evaluation'])) {
						echo 'Not Available Yet.';
					} else {
						echo '<a href="'.$login_info['student_evaluation'].'">View</a>';
					}
					echo '</td>';
				echo '</tr>';
			}
			if (mysqli_num_rows($project) < 1) { // If student is not enrolled in any project
				echo '<tr>';
					echo '<td colspan="10">';
					echo 'You are not enrolled in any project.';
					echo '</td>';
				echo '</tr>';
			}
			echo '</thead>
				</table><br />';
		
?>
</div>
<script>
$(document).ready(function() {
	$(".project_details th:odd").css("background-color","#790808");
	$(".project_details th:even").css("background-color","#680808");
	$(".project_details td:odd").css("background-color","#f4f4f4");
	$(".project_details td:even").css("background-color","#e3e3e3");
});
</script>

<?php
	include('footer.php');
	mysqli_close($conn);
?>