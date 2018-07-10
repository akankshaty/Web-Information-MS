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

		echo '<table class="entries">
		<thead>
			<tr>
				<th>Project Name</th>
				<th>Role Name</th>
				<th>Relevant Skills</th>
				<th>Grade</th>
				<th>Attendance</th>
				<th>Student Evaluation</th>
			</tr>';
			// Get a list of all offer letters sent to this student
			$offers = mysqli_query($conn, "SELECT * FROM offer_letter_requests WHERE student_email='".$_SESSION['u_name']."' AND status='Accepted'");
			$skill_list = mysqli_query($conn,"SELECT olr.project_name,olr.role_name,ps.skill_name FROM project_skills ps LEFT JOIN offer_letter_requests olr ON olr.project_name=ps.project_name WHERE student_email='".$_SESSION['u_name']."' AND status='Accepted'");
			$login_info = mysqli_query($conn,"SELECT * FROM login_info WHERE username='".$_SESSION['u_name']."'");
			$login_info = mysqli_fetch_assoc($login_info);
			while($row1 = mysqli_fetch_assoc($offers)) { // Prints out each row from offer_letter_requests db table results one by one
				echo '<tr>';
					echo '<td>';
					echo $row1['project_name'];
					echo '</td>';
					echo '<td>';
					echo $row1['role_name'];
					echo '</td>';
					echo '<td>';
					$skill_arr = array();
					while($skills_row = mysqli_fetch_assoc($skill_list)) {
						$skill_arr[] = $skills_row['skill_name'];
					}
					echo implode(", ",$skill_arr);
					echo '</td>';
					echo '<td>';
					if (empty($login_info['grade'])) {
						echo '--';
					} else {
						echo $login_info['grade'];
					}
					echo '</td>';
					echo '<td>';
					echo '</td>';
					echo '<td>';
					if (empty($login_info['student_evaluation'])) {
						echo 'Not Available Yet.';
					} else {
						echo '<a href="'.$login_info['student_evaluation'].'">View</a>';
					}
					echo '</td>';
				echo '</tr>';
			}
			if (mysqli_num_rows($offers) < 1) { // If student received no offer letters
				echo '<tr>';
					echo '<td colspan="10">';
					echo 'No offer letters received.';
					echo '</td>';
				echo '</tr>';
			}
			echo '</thead>
				</table><br />';
		
?>
</div>
<script>
$(".error").hide();
$(".survey").hide();
$(document).ready(function() {
	$(".toggle_project_form").on("click", function() {
		$(".error").hide();
		$(".s"+$(this).attr("id")).toggle();
	});
	$("form[class*=new_vacancy]").submit(function(e) {
		if (!$("."+$(this).attr("class")+" input[name=total_seats]").val()) {
			$(".error_txt_cls").text("Please enter the total number of seats available for this role.");
			$(".error").show();
			return false;
		} 
		if (!confirm('Are you sure you want to add a vacancy for this role with these values? The values cannot be changed later.')) {
			return false;
		}
	});
	$("img[id^=accept]").mouseenter(function(e) {
		$(this).attr("src","images/accept_hover.png");
		$(this).css("cursor","pointer");
	});
	$("img[id^=accept]").mouseleave(function(e) {
		$(this).attr("src","images/accept.png");
	});	
	$("img[id^=decline]").mouseenter(function(e) {
		$(this).attr("src","images/decline_hover.png");
		$(this).css("cursor","pointer");
	});
	$("img[id^=decline]").mouseleave(function(e) {
		$(this).attr("src","images/decline.png");
	});
	$("img[id^=accept]").click(function(e) {
		if (confirm('Are you sure you want to accept this offer and join this project? All other offers will be automatically declined and this action cannot be undone.')) {
			var v_name = $(this).attr("name");
			$.ajax({
				url: "update_info.php",
				type: "POST",
				data: { "accept_offer": v_name },
				success: function(response){
					window.location.replace("offers.php");
				}
			});
		} else {
			return false;
		}
	});
	$("img[id^=decline]").click(function(e) {
		if (confirm('Are you sure you want to decline this offer? This action cannot be undone.')) {
			var v_name = $(this).attr("name");
			$.ajax({
				url: "update_info.php",
				type: "POST",
				data: { "decline_offer": v_name },
				success: function(response){
					window.location.replace("offers.php");
				}
			});
		} else {
			return false;
		}
	});
});
</script>

<?php
	include('footer.php');
	mysqli_close($conn);
?>