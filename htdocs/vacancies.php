<?php 
	session_start();
	include('header.php');
	include('auth.php');
	
	if ($_SESSION['u_role'] != "Coordinator" AND $_SESSION['u_role'] != "Student") {
		header("Location: index.php");
	}
	
	if(isset($_POST['add_vacancies'])) {
			// Insert the values into vacancies table
			$project_name = $_POST['project_name'];
			$role_name = $_POST['role_chosen'];
			$total_seats = isset($_POST['total_seats'])? $_POST['total_seats'] : '0';
			mysqli_query($conn,"INSERT INTO vacancies (project_name,role_name,seats_filled,total_seats) VALUES ('".$project_name."','".$role_name."',0,".intval($total_seats).")");

	}
?>
<div class="main-content">
	<?php 
		$res = mysqli_query($conn,"SELECT project_enrolled FROM login_info WHERE username='".$_SESSION['u_name']."'");
		$not_enrolled = empty(mysqli_fetch_assoc($res)['project_enrolled'])? true : false;
		$res = mysqli_query($conn,"SELECT DISTINCT project_name FROM vacancies");
		if ((mysqli_num_rows($res) < 1) && $_SESSION['u_role'] == 'Coordinator') {
			echo '<p style="text-align: center;font-size: 16pt;margin:20%;color:#006600;font-weight: bold;"><img src="images/green_check_mark.png" style="width: 20px;" />All projects are being managed by at least one client.</p>';
		} else if ((mysqli_num_rows($res) < 1) && $_SESSION['u_role'] == 'Client') {
			echo '<p style="text-align: center;font-size: 16pt;margin:20%;color:#006600;font-weight: bold;"><img src="images/red_cross_mark.png" style="width: 20px;" />No projects have been assigned to you by DR Coordinator.</p>';
		}

		$deadline = mysqli_query($conn,"SELECT * FROM settings_option WHERE setting_name='Vacancy Application Deadline'");
		if(mysqli_num_rows($deadline) > 0) {
			$unformatted_deadline = mysqli_fetch_assoc($deadline)['setting_value'];
			$deadline = date("l jS, F Y h:i:s A",$unformatted_deadline);
			$deadline_passed = $unformatted_deadline < time()? true : false; 			
		} else {
			$deadline = "not set.";
			$deadline_passed = false; 	
		}
		echo '<h1>Project Vacancies</h1>';
		echo '<p style="line-height:2em;">You may apply for project vacancies which has not been closed by the client and has seats available. <br />The deadline to apply for project vacancies is <span class="font_bold" style="text-decoration: underline;">'.$deadline.'</span></p>';
		$count = 1;
		while ($row = mysqli_fetch_assoc($res)) {
			$res_value = mysqli_query($conn,"SELECT setting_value FROM settings_option WHERE setting_name='Offer Letters Limit'");
			$offer_letter_limit = intval(mysqli_fetch_assoc($res_value)['setting_value']);
			$res_value = mysqli_query($conn,"SELECT SUM(offer_letters_sent) AS project_offer_letters FROM vacancies WHERE project_name='".$row['project_name']."'");
			$project_offer_letters = mysqli_fetch_assoc($res_value)['project_offer_letters'];
			$remain = $offer_letter_limit-$project_offer_letters;
			$proj_name_encoded = str_replace(')','.',str_replace('(',':',str_replace(' ','_',$row['project_name'])));
			
			echo '<h2>'.$row['project_name'].'</h2>';
	
			echo '<p class="font_bold">Skills required for this project and/or the skills you would learn from this project:</p>

			<div>';
			$add_skills_chosen = mysqli_query($conn,"SELECT * FROM project_skills WHERE project_name='".$row['project_name']."' AND skill_type='Required'");
			while($asc = mysqli_fetch_assoc($add_skills_chosen)) {
				echo '<div class="skill_select" style="padding:10px;">'.$asc['skill_name'].'</div>';
			}
			if (mysqli_num_rows($add_skills_chosen) < 1) {
				echo '<p style="font-style:italic;">No skills added for this project.</p>';
			}
			echo '</div>
			<p class="font_bold">Vacancies list for this project:</p>

		<table class="entries">
			<thead>
				<tr>
					<th>Vacant Role</th>
					<th>Seats Filled</th>
					<th>Apply for Vacancy?</th>

				</tr>';
				// Get a list of all vacancies in this project
				$vacancies = mysqli_query($conn, "SELECT * FROM vacancies WHERE project_name='".$row['project_name']."'");
				$vacancy_applications = mysqli_query($conn,"SELECT * FROM vacancies v LEFT JOIN vacancy_applications va ON v.project_name=va.project_name AND v.role_name=v.project_name WHERE student_email='".$_SESSION['u_name']."'");
				while($row1 = mysqli_fetch_assoc($vacancies)) { // Prints out each row from vacancies db table results one by one
					$seat_update = mysqli_query($conn, "SELECT COUNT(status) AS num_seats_filled FROM offer_letter_requests WHERE project_name='".$row['project_name']."' AND role_name='".$row1['role_name']."' AND status='Accepted'");
					$seat_update = mysqli_fetch_assoc($seat_update)['num_seats_filled'];
					mysqli_query($conn,"UPDATE vacancies SET seats_filled=".$seat_update." WHERE project_name='".$row['project_name']."' AND role_name='".$row1['role_name']."'");

					echo '<tr>';
						echo '<td>';
						echo $row1['role_name'];
						echo '</td>';
						echo '<td>';
						if($row1['seats_filled'] >= $row1['total_seats']) {
							echo '<p class="font_red">'.$row1['seats_filled'].'/'.$row1['total_seats'].'</p>';
						} else {
							echo '<p class="font_green">'.$row1['seats_filled'].'/'.$row1['total_seats'].'</p>';							
						}
						echo '</td>';
							echo '<td>';
							if ($not_enrolled) { // If the student is not enrolled in any project
								if (($row1['closed'] != "Yes") && ($row1['seats_filled'] < $row1['total_seats'])) {
									$p_name = str_replace(' ','_',$row1['project_name']);
									$r_name = str_replace(' ','_',$row1['role_name']);
									if(!$deadline_passed) {
										echo '<img src="images/apply.png" id="apply-*'.$p_name.'-*'.$r_name.'" class="apply" style="width: 90px;cursor: pointer;" />';
									} else {
										echo '<p style="color:gray;">The deadline to apply for project vacancies is over!</p>';
									}
								} else {
									echo 'Vacancy Closed!';
									mysqli_query($conn,"UPDATE vacancies SET closed='Yes' WHERE project_name='".$row1['project_name']."' AND role_name='".$row1['role_name']."'");
								}
							} else {
								echo 'You are already enrolled in a project!';
							}
							echo '</td>';
					echo '</tr>';
				}
				if (mysqli_num_rows($vacancies) < 1) { // If no vacancies added
					echo '<tr>';
						echo '<td colspan="10">';
						echo 'No vacancies added.';
						echo '</td>';
					echo '</tr>';
				}
				echo '</thead>
					</table><br />';
			$count++;
		}
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
	$(".apply").click(function(event){
		if (confirm('Are you sure you want to apply for this vacancy? This action cannot be undone.')) {
			var v_name = event.target.id;
			$.ajax({
				url: "update_info.php",
				type: "POST",
				data: { "apply_vacancy": v_name },
				success: function(response){
					
				}
			});
			window.location.replace("projects.php");
		}
	});

});
</script>

<?php
	include('footer.php');
	mysqli_close($conn);
?>