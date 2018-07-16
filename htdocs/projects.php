<?php 
	session_start();
	include('header.php');
	include('auth.php');
	
	if ($_SESSION['u_role'] != "Coordinator" AND $_SESSION['u_role'] != "Client") {
		header("Location: index.php");
	}
	
	if(isset($_POST['add_vacancies'])) {
			// Insert the values into vacancies table
			$project_name = $_POST['project_name'];
			$role_name = $_POST['role_chosen'];
			$total_seats = isset($_POST['total_seats'])? $_POST['total_seats'] : '0';
			mysqli_query($conn,"INSERT INTO vacancies (project_name,role_name,seats_filled,total_seats) VALUES ('".$project_name."','".$role_name."',0,".intval($total_seats).")");

	}
	if(isset($_POST['add_skill'])) {
			// Insert the values into project_skills table
			$project_name = $_POST['project_name'];
			$skill_name = $_POST['add_skill'];
			mysqli_query($conn,"INSERT INTO project_skills (project_name,skill_type,skill_name) VALUES ('".$project_name."','Required','".$skill_name."')");
	}
	if(isset($_POST['delete_skill'])) {
			// Delete values from project_skills table
			$project_name = $_POST['project_name'];
			$skill_name = $_POST['delete_skill'];
			mysqli_query($conn,"DELETE FROM project_skills WHERE project_name='".$project_name."' AND skill_type='Required' AND skill_name='".$skill_name."'");
	}
	if(isset($_POST['remove_student'])) {
			// Remove Student from project (Functionality available for DR Coordinators Only)
			$student_email = explode("-",$_POST['remove_student'])[1];
			mysqli_query($conn,"UPDATE login_info SET project_enrolled='' WHERE username='".$student_email."'");
	}
?>
<div class="main-content">
	<?php 
		if ($_SESSION['u_role'] == 'Client') {
			$res = mysqli_query($conn,"SELECT * FROM client_projects WHERE client_email='".$_SESSION['u_name']."'");
		} else if ($_SESSION['u_role'] == 'Coordinator') {
			$res = mysqli_query($conn,"SELECT project_name FROM projects p WHERE NOT EXISTS (SELECT cp.project_name FROM client_projects cp WHERE p.project_name = cp.project_name)");
		}
		if ((mysqli_num_rows($res) < 1) && $_SESSION['u_role'] == 'Coordinator') {
			echo '<p style="text-align: center;font-size: 16pt;margin:20%;color:#006600;font-weight: bold;"><img src="images/green_check_mark.png" style="width: 20px;" />All projects are being managed by at least one client.</p>';
		} else if ((mysqli_num_rows($res) < 1) && $_SESSION['u_role'] == 'Client') {
			echo '<p style="text-align: center;font-size: 16pt;margin:20%;color:#006600;font-weight: bold;"><img src="images/red_cross_mark.png" style="width: 20px;" />No projects have been assigned to you by DR Coordinator.</p>';
		}
		$count = 1;
		while ($row = mysqli_fetch_assoc($res)) {
			$res_value = mysqli_query($conn,"SELECT setting_value FROM settings_option WHERE setting_name='Offer Letters Limit'");
			$offer_letter_limit = intval(mysqli_fetch_assoc($res_value)['setting_value']);
			$res_value = mysqli_query($conn,"SELECT SUM(offer_letters_sent) AS project_offer_letters FROM vacancies WHERE project_name='".$row['project_name']."'");
			$project_offer_letters = mysqli_fetch_assoc($res_value)['project_offer_letters'];
			$remain = $offer_letter_limit-$project_offer_letters;
			$proj_name_encoded = str_replace(')','.',str_replace('(',':',str_replace(' ','_',$row['project_name'])));
			
			if ($_SESSION['u_role'] == "Client") {
				echo '<h1>'.$row['project_name'].' (<a id="project'.$count.'" class="toggle_project_form" href="javascript:void(0)">Add Vacancy?</a>)</h1>';				
			} else {
				echo '<h1>'.$row['project_name'].'</h1>';
			}

			// Vacancies form starts here
			echo '<div class="survey sproject'.$count.'" style="display:none;width: 300px; overflow: auto;text-align: center;">
				<form class="new_vacancy'.$count.'" action="" method="POST">
				<div><p>Project Name (Read-only) <span class="font_red">*</span></p><input type="text" name="project_name" value="'.$row['project_name'].'" readonly /></div>
				<div class="font_bold" style="margin: 0 0 20px;">Role Type: ';
				$res1 = mysqli_query($conn,"SELECT * FROM project_roles");
				if (mysqli_num_rows($res1) > 0) {
					echo '<select name="role_chosen">';
					while($row1 = mysqli_fetch_assoc($res1)) {
						echo '<option value="'.$row1['role_name'].'">'.$row1['role_name'].'</option>';
					}
					echo '</select>';
				} else {
					echo 'No roles added.';
				}
				echo '</div>
				<div class="font_bold">Total Seats Required: <input type="number" step="1" min="1" max="20" pattern="\d+" name="total_seats" /></div>

				<div class="error" style="margin: 20px auto 0; width:95%;"><p class="error_txt_cls"></p></div>
				<input type="submit" value="Add Vacancy" name="add_vacancies" />
				</form>
			</div>';
			// Vacancies form ends here				
			echo '<p class="font_bold">Add skills required for this project and/or the skills students would learn from this project:</p>
			<select id="'.$proj_name_encoded.'" class="skills" multiple="">';
			$project_skills = mysqli_query($conn,"SELECT * FROM skills WHERE skill_name NOT IN (SELECT skill_name FROM project_skills WHERE project_name='".$row['project_name']."' AND skill_type='Required')");
			if (mysqli_num_rows($project_skills) > 0) {
				while($ps = mysqli_fetch_assoc($project_skills)) {
					echo '<option class="'.$proj_name_encoded.'" value="'.$ps['skill_name'].'">'.$ps['skill_name'].'</option>';
				}
			}

			echo '</select>
			<div>';
			$add_skills_chosen = mysqli_query($conn,"SELECT * FROM project_skills WHERE project_name='".$row['project_name']."' AND skill_type='Required'");
			while($asc = mysqli_fetch_assoc($add_skills_chosen)) {
				echo '<div class="skill_select">'.$asc['skill_name'].' <img class="delete_skill" src="images/delete_skill.png" /></div>';
			}
			echo '</div>';
			if ($_SESSION['u_role'] == "Client") {
				echo '<p style="line-height:2em;">You may send a total of '.$offer_letter_limit.' offer letter(s) to students for each project. You still have '.$remain.' offer letters remaining for this project. Keep in mind that you will not be allowed to send more offer letters than what your vacancy for each role allows.</p>

			<table class="entries">
				<thead>
					<tr>
						<th>Vacant Role</th>
						<th>Seats Filled</th>
						<th>Offer Letters Sent</th>
						<th>Close A Vacancy?</th>

					</tr>';
					// Get a list of all vacancies in this project
					$vacancies = mysqli_query($conn, "SELECT * FROM vacancies WHERE project_name='".$row['project_name']."'");

					while($row1 = mysqli_fetch_assoc($vacancies)) { // Prints out each row from vacancies db table results one by one
						$seat_update = mysqli_query($conn, "SELECT COUNT(status) AS num_seats_filled FROM offer_letter_requests WHERE project_name='".$row['project_name']."' AND role_name='".$row1['role_name']."' AND status='Accepted'");
						$seat_update = mysqli_fetch_assoc($seat_update)['num_seats_filled'];
						mysqli_query($conn,"UPDATE vacancies SET seats_filled=".$seat_update." WHERE project_name='".$row['project_name']."' AND role_name='".$row1['role_name']."'");
						$offer_sent_update = mysqli_query($conn, "SELECT COUNT(status) AS num_offer_sent FROM offer_letter_requests WHERE project_name='".$row['project_name']."' AND role_name='".$row1['role_name']."' AND (status='Pending' OR status='Accepted')");
						$offer_sent_update = mysqli_fetch_assoc($offer_sent_update)['num_offer_sent'];
						mysqli_query($conn,"UPDATE vacancies SET offer_letters_sent=".$offer_sent_update." WHERE project_name='".$row['project_name']."' AND role_name='".$row1['role_name']."'");
						echo '<tr>';
							echo '<td>';
							echo $row1['role_name'];
							echo '</td>';
							echo '<td>';
							if($row1['seats_filled'] >= $row1['total_seats']) {
								echo '<p class="font_green">'.$row1['seats_filled'].'/'.$row1['total_seats'].'</p>';
							} else {
								echo '<p class="font_red">'.$row1['seats_filled'].'/'.$row1['total_seats'].'</p>';							
							}
							echo '</td>';
							echo '<td>';
							if($row1['offer_letters_sent'] >= $row1['total_seats']) {
								echo '<p class="font_red">'.$row1['offer_letters_sent'].'/'.$row1['total_seats'].'</p>';
							} else {
								echo '<p class="font_green">'.$row1['offer_letters_sent'].'/'.$row1['total_seats'].'</p>';							
							}
							echo '</td>';
								echo '<td>';

								if (($row1['closed'] != "Yes") && ($row1['seats_filled'] < $row1['total_seats'])) {
									$p_name = str_replace(' ','_',$row1['project_name']);
									$r_name = str_replace(' ','_',$row1['role_name']);
									echo '<img src="images/close.png" id="close-*'.$p_name.'-*'.$r_name.'" class="close" style="width: 90px;cursor: pointer;" />';
								} else {
									echo 'Vacancy Closed!';
									mysqli_query($conn,"UPDATE vacancies SET closed='Yes' WHERE project_name='".$row1['project_name']."' AND role_name='".$row1['role_name']."'");
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
			}
			echo '<p class="font_bold" style="font-size: 14pt;">Students enrolled in this project</p>';
			echo '<table class="entries">';
			echo '<th>Name</th>';
			echo '<th>Email Address</th>';
			if($_SESSION['u_role'] == "Client") {echo '<th>Role Name</th>';} else {echo '<th>Remove From Project?</th>';}
			if($_SESSION['u_role'] == "Client") {
				$student_enrolled = mysqli_query($conn,"SELECT li.f_name,li.l_name,li.username,olr.project_name,olr.role_name FROM login_info li LEFT JOIN offer_letter_requests olr ON li.username=olr.student_email WHERE olr.project_name='".$row['project_name']."' AND olr.status='Accepted'");
			} else {
				$student_enrolled = mysqli_query($conn,"SELECT * FROM login_info WHERE role='Student' AND project_enrolled='".$row['project_name']."'");
			}
			if(mysqli_num_rows($student_enrolled) > 0) {
				while($srow = mysqli_fetch_assoc($student_enrolled)) {
					echo '<tr>';
						echo '<td>';
							echo $srow['f_name'].' '.$srow['l_name'];
						echo '</td>';
						echo '<td>';
							echo $srow['username'];
						echo '</td>';
						if($_SESSION['u_role'] == "Client") {
							echo '<td>';
								echo $srow['role_name'];
							echo '</td>';
						} else {
							echo '<td>';
								echo '<img src="images/delete_cross_mark.png" id="delete-'.$srow['username'].'" class="delete" style="width:30px;cursor:pointer;" />';
							echo '</td>';
						}
					echo '</tr>';
				}
			} else {
					echo '<tr>';
						echo '<td colspan="10">';
							echo 'No students have currently enrolled in this project.';
						echo '</td>';
					echo '</tr>';
			}
			echo '</table><br />';
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
	$(".close").click(function(event){
		if (confirm('Are you sure you want to close this vacancy? You will no longer be able to add more students to this role and this action cannot be undone.')) {
			var v_name = event.target.id;
			$.ajax({
				url: "update_info.php",
				type: "POST",
				data: { "close_vacancy": v_name },
				success: function(response){
					
				}
			});
			window.location.replace("projects.php");
		} else {
			return false;
		}
	});
	$(".delete").click(function(event){
		if (confirm('Are you sure you want to remove this student from this project?')) {
			var v_name = event.target.id;
			$.ajax({
				url: "projects.php",
				type: "POST",
				data: { "remove_student": v_name },
				success: function(response){
					
				}
			});
			window.location.replace("projects.php");
		} else {
			return false;
		}
	});

	$(document).on("click", "select[class=skills] option", function(e) {
		$(this).parent().next().append('<div class="skill_select">'+$(this).text()+' <img class="delete_skill" src="images/delete_skill.png" /></div>');
		$(this).remove();
		var proj_name = $(this).attr("class").replace(/_/g,' ').replace(/\:/,'(').replace(/\./,')');
		var s_id = $(this).val();
		$.ajax({
			url: "projects.php",
			type: "POST",
			data: { "add_skill": s_id, "project_name": proj_name },
			success: function(response){
				
			}
		});

	});
  $(document).on("click",".delete_skill",function(e){
		var value = $(this).parent().text();
		var proj_encoded = $(this).parent().parent().prev().attr("id");
		var proj_name = $(this).parent().parent().prev().attr("id").replace(/_/g,' ').replace(/\:/,'(').replace(/\./,')');
		var s_id = $(this).parent().text();
		$(this).parent().parent().prev().append('<option class="'+proj_encoded+'" value="'+value+'">'+value+'</option>');
		$(this).parent().remove();
		$.ajax({
			url: "projects.php",
			type: "POST",
			data: { "delete_skill": s_id, "project_name": proj_name },
			success: function(response){

			}
		});
	});

});
</script>

<?php
	include('footer.php');
	mysqli_close($conn);
?>