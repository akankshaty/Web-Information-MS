<?php 
	session_start();
	include('header.php');
	include 'auth.php';
	if ($_SESSION['u_role'] != "Coordinator" AND $_SESSION['u_role'] != "Admin") {
		header("Location: index.php");
	}
	if (isset($_POST['save'])) {
		if($_POST['survey_deadline']) { // Deadline for filling survey form
			$year_month = explode('-',$_POST['survey_deadline']);
			$year = $year_month[0];
			$month = $year_month[1];
			$day_hour_min = explode('T',$year_month[2]);
			$day = $day_hour_min[0];
			$hour_min = explode(':',$day_hour_min[1]);
			$hour = $hour_min[0];
			$min = $hour_min[1];
			$d = strval(strtotime($year."-".$month."-".$day." ".$hour.":".$min.":00"));
			
			$res = mysqli_query($conn, "SELECT * FROM settings_option WHERE setting_name='Filling Survey Form Deadline'");
			if (mysqli_num_rows($res) < 1) {
				mysqli_query($conn, "INSERT INTO settings_option (setting_name,setting_value) VALUES ('Filling Survey Form Deadline','".$d."')");
			} else {
				mysqli_query($conn, "UPDATE settings_option SET setting_value='".$d."' WHERE setting_name='Filling Survey Form Deadline'");
			}
		}
		if($_POST['offer_letter_send_deadline']) { // Deadline for students to accept offer letters
			$year_month = explode('-',$_POST['offer_letter_send_deadline']);
			$year = $year_month[0];
			$month = $year_month[1];
			$day_hour_min = explode('T',$year_month[2]);
			$day = $day_hour_min[0];
			$hour_min = explode(':',$day_hour_min[1]);
			$hour = $hour_min[0];
			$min = $hour_min[1];
			$d = strval(strtotime($year."-".$month."-".$day." ".$hour.":".$min.":00"));
			
			$res = mysqli_query($conn, "SELECT * FROM settings_option WHERE setting_name='Sending Offer Letter Deadline'");
			if (mysqli_num_rows($res) < 1) {
				mysqli_query($conn, "INSERT INTO settings_option (setting_name,setting_value) VALUES ('Sending Offer Letter Deadline','".$d."')");
			} else {
				mysqli_query($conn, "UPDATE settings_option SET setting_value='".$d."' WHERE setting_name='Sending Offer Letter Deadline'");
			}
		}
		if($_POST['offer_letter_accept_deadline']) { // Deadline for students to accept offer letters
			$year_month = explode('-',$_POST['offer_letter_accept_deadline']);
			$year = $year_month[0];
			$month = $year_month[1];
			$day_hour_min = explode('T',$year_month[2]);
			$day = $day_hour_min[0];
			$hour_min = explode(':',$day_hour_min[1]);
			$hour = $hour_min[0];
			$min = $hour_min[1];
			$d = strval(strtotime($year."-".$month."-".$day." ".$hour.":".$min.":00"));
			
			$res = mysqli_query($conn, "SELECT * FROM settings_option WHERE setting_name='Accepting Offer Letter Deadline'");
			if (mysqli_num_rows($res) < 1) {
				mysqli_query($conn, "INSERT INTO settings_option (setting_name,setting_value) VALUES ('Accepting Offer Letter Deadline','".$d."')");
			} else {
				mysqli_query($conn, "UPDATE settings_option SET setting_value='".$d."' WHERE setting_name='Accepting Offer Letter Deadline'");
			}
		}
		if($_POST['offer_letter_limit']) { // Limit for the number of offer letters that can be sent by clients	
			$res = mysqli_query($conn, "SELECT * FROM settings_option WHERE setting_name='Offer Letters Limit'");
			
			if (mysqli_num_rows($res) < 1) {
				mysqli_query($conn, "INSERT INTO settings_option (setting_name,setting_value) VALUES ('Offer Letters Limit',".$_POST['offer_letter_limit'].")");
			} else {
				mysqli_query($conn, "UPDATE settings_option SET setting_value=".$_POST['offer_letter_limit']." WHERE setting_name='Offer Letters Limit'");
			}
		}
		if($_POST['vacancy_application_deadline']) { // Deadline for students to apply for vacancies
			$year_month = explode('-',$_POST['vacancy_application_deadline']);
			$year = $year_month[0];
			$month = $year_month[1];
			$day_hour_min = explode('T',$year_month[2]);
			$day = $day_hour_min[0];
			$hour_min = explode(':',$day_hour_min[1]);
			$hour = $hour_min[0];
			$min = $hour_min[1];
			$d = strval(strtotime($year."-".$month."-".$day." ".$hour.":".$min.":00"));
			
			$res = mysqli_query($conn, "SELECT * FROM settings_option WHERE setting_name='Vacancy Application Deadline'");
			if (mysqli_num_rows($res) < 1) {
				mysqli_query($conn, "INSERT INTO settings_option (setting_name,setting_value) VALUES ('Vacancy Application Deadline','".$d."')");
			} else {
				mysqli_query($conn, "UPDATE settings_option SET setting_value='".$d."' WHERE setting_name='Vacancy Application Deadline'");
			}
		}
		if($_POST['eval_submit_deadline']) { // Deadline for clients to submit student evaluation
			$year_month = explode('-',$_POST['eval_submit_deadline']);
			$year = $year_month[0];
			$month = $year_month[1];
			$day_hour_min = explode('T',$year_month[2]);
			$day = $day_hour_min[0];
			$hour_min = explode(':',$day_hour_min[1]);
			$hour = $hour_min[0];
			$min = $hour_min[1];
			$d = strval(strtotime($year."-".$month."-".$day." ".$hour.":".$min.":00"));
			
			$res = mysqli_query($conn, "SELECT * FROM settings_option WHERE setting_name='Submitting Evaluation Deadline'");
			if (mysqli_num_rows($res) < 1) {
				mysqli_query($conn, "INSERT INTO settings_option (setting_name,setting_value) VALUES ('Submitting Evaluation Deadline','".$d."')");
			} else {
				mysqli_query($conn, "UPDATE settings_option SET setting_value='".$d."' WHERE setting_name='Submitting Evaluation Deadline'");
			}
		}
		if($_POST['skill_list']) { // Updating Skillsets Section
			$skill_arr = explode(',',$_POST['skill_list']);
			mysqli_query($conn, "TRUNCATE TABLE `skills`");
			foreach($skill_arr as $sa) {
				if (preg_match('/\S/', $sa)) {
					$sa = mysqli_real_escape_string($conn, $sa);
					mysqli_query($conn, "INSERT INTO skills (skill_name) VALUES ('".trim($sa)."')");	
				}
			}
		}
		if($_POST['role_list']) { // Updating Project Roles
			$role_arr = explode(',',str_replace('-*',' ',$_POST['role_list']));
			mysqli_query($conn, "TRUNCATE TABLE `project_roles`");
			foreach($role_arr as $sa) {
				if (preg_match('/\S/', $sa)) {
					$sa = mysqli_real_escape_string($conn, $sa);
					mysqli_query($conn, "INSERT INTO project_roles (role_name) VALUES ('".trim($sa)."')");	
				}
			}
		}
		if($_POST['project_list']) { // Updating List of Projects
			$project_arr = explode(',',str_replace('-*',' ',$_POST['project_list']));
			mysqli_query($conn, "TRUNCATE TABLE `projects`");
			foreach($project_arr as $sa) {
				if (preg_match('/\S/', $sa)) {
					$sa = mysqli_real_escape_string($conn, $sa);
					mysqli_query($conn, "INSERT INTO projects (project_name) VALUES ('".trim($sa)."')");
				}
			}
			mysqli_query($conn, "DELETE FROM client_projects WHERE client_projects.project_name NOT IN (SELECT projects.project_name FROM projects)");
		}
	}
	if (isset($_POST['current_semester']) && !empty($_POST['current_semester'])) {
		$res = mysqli_query($conn,"SELECT * FROM settings_option WHERE setting_name='Current Semester'");
		if (mysqli_num_rows($res) < 1) {
			mysqli_query($conn,"INSERT INTO settings_option (setting_name,setting_value) VALUES ('Current Semester','".$_POST['current_semester']."')");
		} else {
			mysqli_query($conn,"UPDATE settings_option SET setting_value='".$_POST['current_semester']."' WHERE setting_name='Current Semester'");
		}
	}
	if (isset($_POST['delete_all_students'])) {
		mysqli_query($conn,"DELETE FROM login_info WHERE role='Student'");
		mysqli_query($conn,"TRUNCATE TABLE email_verifications");
		mysqli_query($conn,"TRUNCATE TABLE student_skills");
		mysqli_query($conn,"TRUNCATE TABLE changed_units");
		mysqli_query($conn,"TRUNCATE TABLE offer_letter_requests");
		mysqli_query($conn,"TRUNCATE TABLE password_requests");
		mysqli_query($conn,"TRUNCATE TABLE project_preferences");
		mysqli_query($conn,"TRUNCATE TABLE project_skills");
		mysqli_query($conn,"TRUNCATE TABLE reviewed_students");
		mysqli_query($conn,"TRUNCATE TABLE role_preferences");
		mysqli_query($conn,"TRUNCATE TABLE vacancy_applications");

		foreach(glob("resumes/*") as $file) { // Delete all resume files stored in the 'resumes' directory because all students are deleted
			unlink($file);
		}
	}
	if (isset($_POST['delete_all_clients'])) {
		mysqli_query($conn,"DELETE FROM login_info WHERE role='Client'");
		mysqli_query($conn,"TRUNCATE TABLE client_invitations");
		mysqli_query($conn,"TRUNCATE TABLE client_projects");
		mysqli_query($conn,"TRUNCATE TABLE reviewed_students");
	}
	if (isset($_POST['delete_all_projects'])) {
		mysqli_query($conn,"TRUNCATE TABLE client_projects");
		mysqli_query($conn,"TRUNCATE TABLE password_requests");
		mysqli_query($conn,"TRUNCATE TABLE projects");
		mysqli_query($conn,"TRUNCATE TABLE offer_letter_requests");
		mysqli_query($conn,"TRUNCATE TABLE project_preferences");
		mysqli_query($conn,"TRUNCATE TABLE project_skills");
		mysqli_query($conn,"TRUNCATE TABLE vacancies");
		mysqli_query($conn,"TRUNCATE TABLE vacancy_applications");	
	}
?>
<div class="main-content">
	<div class="survey">
		<h1>Project Settings</h1>
		<form id = "settings_form" action="settings.php" method="POST">
			<p>Select the current semester: <select name="current_semester">
			<?php 
				$current_year = intval(date('Y'));
				$res = mysqli_query($conn,"SELECT * FROM settings_option WHERE setting_name='Current Semester'");
				$row = mysqli_fetch_assoc($res);
				$select_it = $row['setting_value'];
				echo '<option value="">Select Semester</option>';
				for($i=0;$i<=4;$i++) {
					if($i == 0) {
						if(strcmp('Fall '.($current_year-1),$select_it) == 0) {
							echo '<option value="Fall '.($current_year-1).'" selected>Fall '.($current_year-1).'</option>';
						} else {
							echo '<option value="Fall '.($current_year-1).'">Fall '.($current_year-1).'</option>';
						}
					} else if($i == 1) {
						if(strcmp(strval('Spring '.$current_year),$select_it) == 0) {
							echo '<option value="Spring '.$current_year.'" selected>Spring '.$current_year.'</option>';
						} else {
							echo '<option value="Spring '.$current_year.'">Spring '.$current_year.'</option>';
						}
					} else if($i == 2) {
						if(strcmp('Summer '.$current_year,$select_it) == 0) {
							echo '<option value="Summer '.$current_year.'" selected>Summer '.$current_year.'</option>';
						} else {
							echo '<option value="Summer '.$current_year.'">Summer '.$current_year.'</option>';
						}
					} else if($i == 3) {
						if(strcmp('Fall '.$current_year,$select_it) == 0) {
							echo '<option value="Fall '.$current_year.'" selected>Fall '.$current_year.'</option>';
						} else {
							echo '<option value="Fall '.$current_year.'">Fall '.$current_year.'</option>';
						}
					} else if($i == 4) {
						if(strcmp('Spring '.($current_year+1),$select_it) == 0) {
							echo '<option value="Spring '.($current_year+1).'" selected>Spring '.($current_year+1).'</option>';
						} else {
							echo '<option value="Spring '.($current_year+1).'">Spring '.($current_year+1).'</option>';
						}
					}
				}
				
			?>
			</select>

			</p>
			<p>Set the deadline for students to submit/change survey: <input type="datetime-local" name="survey_deadline" <?php 
				$res = mysqli_query($conn, "SELECT setting_value FROM settings_option WHERE setting_name='Filling Survey Form Deadline'");
				if (mysqli_num_rows($res) > 0) {
					$row = mysqli_fetch_assoc($res);
					echo 'value="'.date('Y-m-d\TH:i', intval($row['setting_value'])).'"';
				}				
			?> /></p>
			<p>Set the deadline for clients to send out offer letters to students: <input type="datetime-local" name="offer_letter_send_deadline" <?php 
				$res = mysqli_query($conn, "SELECT setting_value FROM settings_option WHERE setting_name='Sending Offer Letter Deadline'");
				if (mysqli_num_rows($res) > 0) {
					$row = mysqli_fetch_assoc($res);
					echo 'value="'.date('Y-m-d\TH:i', intval($row['setting_value'])).'"';
				}
			?> /></p>
			<p>Set the deadline for students to accept offer letters: <input type="datetime-local" name="offer_letter_accept_deadline" <?php 
				$res = mysqli_query($conn, "SELECT setting_value FROM settings_option WHERE setting_name='Accepting Offer Letter Deadline'");
				if (mysqli_num_rows($res) > 0) {
					$row = mysqli_fetch_assoc($res);
					echo 'value="'.date('Y-m-d\TH:i', intval($row['setting_value'])).'"';
				}
			?> /></p>
			<p>Limit the number of offer letters that can be sent by clients for each project: <input type="number" step="1" min="0" max="99" pattern="\d+" name="offer_letter_limit" <?php 
				$res = mysqli_query($conn, "SELECT setting_value FROM settings_option WHERE setting_name='Offer Letters Limit'");
				if (mysqli_num_rows($res) > 0) {
					$row = mysqli_fetch_assoc($res);
					echo 'value="'.intval($row['setting_value']).'"';
				} else {
					echo 'value=15';
				}
			?> /></p>
			<p>Set the deadline for students to apply for project vacancies: <input type="datetime-local" name="vacancy_application_deadline" <?php 
				$res = mysqli_query($conn, "SELECT setting_value FROM settings_option WHERE setting_name='Vacancy Application Deadline'");
				if (mysqli_num_rows($res) > 0) {
					$row = mysqli_fetch_assoc($res);
					echo 'value="'.date('Y-m-d\TH:i', intval($row['setting_value'])).'"';
				}
			?> /></p>
			<p>Set the deadline for clients to submit student evaluations: <input type="datetime-local" name="eval_submit_deadline" <?php 
				$res = mysqli_query($conn, "SELECT setting_value FROM settings_option WHERE setting_name='Submitting Evaluation Deadline'");
				if (mysqli_num_rows($res) > 0) {
					$row = mysqli_fetch_assoc($res);
					echo 'value="'.date('Y-m-d\TH:i', intval($row['setting_value'])).'"';
				}
			?> /></p>
			<!----- Skills Section ----->
			
			<h3>Skills (<a id="edit_skills" href="javascript:void(0)">Add/Edit Skills?</a>)</h3>
			<div id="skill_setting">
				List the required skills as comma-separated values. The skills listed here will be used to generate the survey form.<br /><br />
				<textarea rows="8" cols="50" name="skill_list" placeholder="Add skills separated by commas. (Eg: Python, Java)"><?php 
					$skills = mysqli_query($conn, "SELECT skill_name from skills");
					$skills_list = "";
					while($row = mysqli_fetch_assoc($skills)) {
						$skills_list .= trim($row['skill_name']).", ";
					}
					$skills_list = rtrim(trim($skills_list),',');
					echo $skills_list;
				?></textarea><br /><br />
			</div>
			<table class="entries">
				<thead>
					<tr>
						<th>List of skills added</th>
					</tr>
			<?php 
				$skills = mysqli_query($conn, "SELECT skill_name from skills");
				while($row = mysqli_fetch_assoc($skills)) {
					echo '<tr>';
						echo '<td>';
						echo $row['skill_name'];
						echo '</td>';
					echo '</tr>';
				}
				if (mysqli_num_rows($skills) < 1) {
					echo '<tr>';
						echo '<td>';
						echo 'No skills added.';
						echo '</td>';
					echo '</tr>';
				}
			?>
				</thead>
			</table>
			
			<!----- Project Roles Section ----->
			
			<h3>Project Roles (<a id="edit_roles" href="javascript:void(0)">Add/Edit Roles?</a>)</h3>
			<div id="role_setting">
				List the required roles in projects. The roles listed here will be used to generate the survey form.<br /><br />
				<textarea rows="8" cols="50" name="role_list" placeholder="Add roles separated by commas. (Eg: Front-end developer, Tester)"><?php 
					$roles = mysqli_query($conn, "SELECT role_name from project_roles");
					$roles_list = "";
					while($row = mysqli_fetch_assoc($roles)) {
						$roles_list .= trim($row['role_name']).", ";
					}
					$roles_list = rtrim(trim($roles_list),',');
					echo $roles_list;
				?></textarea><br /><br />
			</div>
			<table class="entries">
				<thead>
					<tr>
						<th>List of project roles added</th>
					</tr>
			<?php 
				include 'auth.php';
				$roles = mysqli_query($conn, "SELECT role_name from project_roles");
				while($row = mysqli_fetch_assoc($roles)) {
					echo '<tr>';
						echo '<td>';
						echo $row['role_name'];
						echo '</td>';
					echo '</tr>';
				}
				if (mysqli_num_rows($roles) < 1) {
					echo '<tr>';
						echo '<td>';
						echo 'No roles added.';
						echo '</td>';
					echo '</tr>';
				}
			?>
				</thead>
			</table>
			
			<!----- Projects Section ----->
			
			<h3>Projects (<a id="edit_projects" href="javascript:void(0)">Add/Edit Projects?</a>)</h3>
			<div id="project_setting">
				List the projects available this semester. The projects listed here will be used to generate the survey form.<br /><br />
				<textarea rows="8" cols="50" name="project_list" placeholder="Add projects separated by commas. (Eg: COCOMO, UCC (Java))"><?php 
					$projects = mysqli_query($conn, "SELECT project_name from projects");
					$projects_list = "";
					while($row = mysqli_fetch_assoc($projects)) {
						$projects_list .= trim($row['project_name']).", ";
					}
					$projects_list = rtrim(trim($projects_list),',');
					echo $projects_list;
				?></textarea><br /><br />
			</div>
			<table class="entries">
				<thead>
					<tr>
						<th>List of projects added</th>
					</tr>
			<?php 
				include 'auth.php';
				$projects = mysqli_query($conn, "SELECT project_name from projects");
				while($row = mysqli_fetch_assoc($projects)) {
					echo '<tr>';
						echo '<td>';
						echo $row['project_name'];
						echo '</td>';
					echo '</tr>';
				}
				if (mysqli_num_rows($projects) < 1) {
					echo '<tr>';
						echo '<td>';
						echo 'No projects added.';
						echo '</td>';
					echo '</tr>';
				}
			?>
				</thead>
			</table>
			
			<!----- Semester Reset Section ----->
			
			<h3>Semester Reset Options (<a href="summary.php" target="_blank">View/print semester summary</a>)</h3>
			<?php 
				$res = mysqli_query($conn,"SELECT * FROM login_info WHERE role='Student'");
				$students_exist = (mysqli_num_rows($res) > 0)? true : false;
				$res = mysqli_query($conn,"SELECT * FROM login_info WHERE role='Client'");
				$clients_exist = (mysqli_num_rows($res) > 0)? true : false;
				$res = mysqli_query($conn,"SELECT * FROM projects");
				$projects_exist = (mysqli_num_rows($res) > 0)? true : false;
			?>
			<p style="text-align: center;font-size: 14pt;"></p>
			<div style="text-align:center;"><input type="submit" style="font-size: 12pt;margin-top: 5px;" name="delete_all_students" <?php if(!$students_exist) echo 'disabled'; ?> value="Delete all students" />	
			<input type="submit" style="font-size: 12pt;margin-top: 5px;" name="delete_all_clients" <?php if(!$clients_exist) echo 'disabled'; ?> value="Delete all clients" />	
			<input type="submit" style="font-size: 12pt;margin-top: 5px;" name="delete_all_projects" <?php if(!$projects_exist) echo 'disabled'; ?> value="Delete all projects" /></div><br />
				<input style="margin-left:43%;" type="submit" name="save" value="Save" />
			
		</form>
	</div>
</div>
<script>
$("#skill_setting").hide();
$("#role_setting").hide();
$("#project_setting").hide();
$(document).ready(function(){
	$("#edit_skills").on("click", function() {
		$("#skill_setting").toggle();
	});
	$("#edit_roles").on("click", function() {
		$("#role_setting").toggle();
	});
	$("#edit_projects").on("click", function() {
		$("#project_setting").toggle();
	});
	$("input[name=delete_all_students]").click(function(e) {
		if (!confirm('Are you sure you want to delete all students?')) {
			return false;
		}
	});
	$("input[name=delete_all_clients]").click(function(e) {
		if (!confirm('Are you sure you want to delete all clients?')) {
			return false;
		}
	});
	$("input[name=delete_all_projects]").click(function(e) {
		if (!confirm('Are you sure you want to delete all projects?')) {
			return false;
		}
	});
});
</script>
<?php
	include('footer.php');
	mysqli_close($conn);
?>