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
		if($_POST['vacancy_application_limit']) { // Limit for the number of vacancy applications students can send
			$res = mysqli_query($conn, "SELECT * FROM settings_option WHERE setting_name='Vacancy Applications Limit'");
			
			if (mysqli_num_rows($res) < 1) {
				mysqli_query($conn, "INSERT INTO settings_option (setting_name,setting_value) VALUES ('Vacancy Applications Limit',".$_POST['vacancy_application_limit'].")");
			} else {
				mysqli_query($conn, "UPDATE settings_option SET setting_value=".$_POST['vacancy_application_limit']." WHERE setting_name='Vacancy Applications Limit'");
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
	if(isset($_POST['add_client'])) { // Adding new client
		$f_name = mysqli_real_escape_string($conn,$_POST['f_name']);
		$l_name = mysqli_real_escape_string($conn,$_POST['l_name']);
		$c_email = mysqli_real_escape_string($conn,$_POST['c_email']);
		$res = mysqli_query($conn,"SELECT * FROM login_info WHERE username='".$c_email."'");
		if(mysqli_num_rows($res) < 1) { // Check if user already exist in database, add invitation if not.
			$string_not_unique = true;
			do { // Loop until the created random string is unique (random string which is not found in database already)
				$random_string = "";
				$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
				for($i=0;$i<=6;$i++) {
					$random_string.=substr($chars,rand(0,strlen($chars)),1); // Generating a random string as a form of access code to email users
				}
				$res = mysqli_query($conn,"SELECT * FROM client_invitations WHERE access_token='".$random_string."'");
				if (mysqli_num_rows($res) < 1) { // Generated string is unique
					$string_not_unique = false; 
				}
			} while($string_not_unique);
			// Insert the values into client_invitations table
			mysqli_query($conn,"INSERT INTO client_invitations (f_name,l_name,email,access_token) VALUES ('".$f_name."','".$l_name."','".$c_email."','".$random_string."')");
			// Insert projects owned by clients into client_projects table
			foreach($_POST['projects'] as $project_name) {
				mysqli_query($conn,"INSERT INTO client_projects (client_email,project_name) VALUES ('".$c_email."','".$project_name."')");
			}
			
			$url = parse_url((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_HOST); // Parses the domain of the DR Website
			$curr_path = dirname((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
			
			// Email for inviting client to join
			$to = $c_email;

			// Subject
			$subject = 'CSCI 590 DR Course Client Invitation';

			// Message
			$message = '
			<html>
			<head>
			</head>
			<body>
				<p>Dear '.$f_name.',<br /><br />This message is to notify you that the DR Coordinator has invited you to join the CSCI 590 Directed Research course website.
				Please click on the link below to sign-up to the website. <br /><strong>Email Verification Link: </strong><a href="'.$curr_path.'/client_signup.php?access='.$random_string.'">'.$curr_path.'/client_signup.php?access='.$random_string.'</a></p><br />
				Thanks, <br />
				CSCI 590 DR Management Team
			</body>
			</html>
			';

			// To send HTML mail, the Content-type header must be set
			$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-type: text/html; charset=iso-8859-1';
			$headers[] = 'From: DR CSCI-590 <no-reply@'.$url.'>'; // Format of the variable ("From: DR CSCI-590 <no-reply@domain_name.com>")
			// Mail it to client
			mail($to, $subject, $message, implode("\r\n", $headers));
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
					echo 'value=15'; // Default number of offer letters that clients can send if the setting is not set.
				}
			?> /></p>
			<p>Set the deadline for students to apply for project vacancies: <input type="datetime-local" name="vacancy_application_deadline" <?php 
				$res = mysqli_query($conn, "SELECT setting_value FROM settings_option WHERE setting_name='Vacancy Application Deadline'");
				if (mysqli_num_rows($res) > 0) {
					$row = mysqli_fetch_assoc($res);
					echo 'value="'.date('Y-m-d\TH:i', intval($row['setting_value'])).'"';
				}
			?> /></p>
			<p>Limit the number of vacancy application requests students can submit: <input type="number" step="1" min="0" max="99" pattern="\d+" name="vacancy_application_limit" <?php 
				$res = mysqli_query($conn, "SELECT setting_value FROM settings_option WHERE setting_name='Vacancy Applications Limit'");
				if (mysqli_num_rows($res) > 0) {
					$row = mysqli_fetch_assoc($res);
					echo 'value="'.intval($row['setting_value']).'"';
				} else {
					echo 'value=5'; // Default number of vacancy applications that students can send if the setting is not set.
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

			<!-- Add Client Section -->

			<h3>Clients (<a id="toggle_client_form" href="javascript:void(0)">Add Client?</a>)</h3>

			<div class="survey-client" style="width: 300px; overflow: auto;text-align: center;">
				<form id="new_client" action="" method="POST">
				<div><p>Client's First Name <span class="font_red">*</span></p><input type="text" name="f_name" placeholder="First Name" /></div>
				<div><p>Client's Last Name <span class="font_red">*</span></p><input type="text" name="l_name" placeholder="Last Name" /></div>
				<div><p>Client's Email Address <span class="font_red">*</span></p><input type="text" name="c_email" placeholder="Email Address" /></div>
				<div><p>Projects managed by Client</p>
					<?php 
						$res = mysqli_query($conn,"SELECT project_name FROM projects");
						$count = 1;
						if(mysqli_num_rows($res) > 0) {
							echo '<div style="text-align: left;">';
							while($row = mysqli_fetch_assoc($res)) {
								echo '<input type="checkbox" name="projects[]" value="'.trim($row['project_name']).'" />';
								echo trim($row['project_name']).'<br />';
								$count++;
							}
							echo '</div>';
						} else {
							echo 'No projects added.';
						}

						
					?>
					
				</div>
				<div class="error"><p id="error_txt"></p></div>
				<input type="submit" value="Add Client" name="add_client" />
				</form>
			</div>

			<h4>List of all client invitations which has been sent. But the client hasn't signed up yet to this website.</h4>
			<h4>The access code link sent to the client is in this format: <?php $curr_path = dirname((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); echo $curr_path; ?>/client_signup.php?access=[ACCESS CODE]</h4>
			<h4><span class="font_bold">Extra Tip:</span> Email this access code link directly to the client in case the client is not receiving the invitation email automatically.</h4>
			<table class="entries">
				<thead>
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email Address</th>
					<th>Projects Managed</th>
					<th>Access Code</th>
				</tr>
				<?php 
					// Get a list of all client invitations sent by the DR Coordinator
					$clients = mysqli_query($conn, "SELECT f_name,l_name,email,access_token FROM client_invitations");
					while($row = mysqli_fetch_assoc($clients)) { // Prints out each row from client_invitations db table results one by one
						$project_managed = mysqli_query($conn, "SELECT * FROM client_projects WHERE client_email='".$row['email']."'");
						echo '<tr>';
							echo '<td>';
							echo $row['f_name'];
							echo '</td>';
							echo '<td>';
							echo $row['l_name'];
							echo '</td>';
							echo '<td>';
							echo $row['email'];
							echo '</td>';
							echo '<td>';
							$p_array = array();
							while($p_managed = mysqli_fetch_assoc($project_managed)) {
								$p_array[] = $p_managed['project_name'];
							}
							if (!empty($p_array)) {
								echo implode(", ",$p_array);
							} else {
								echo "No projects.";
							}
							echo '</td>';
							echo '<td>';
							echo $row['access_token'];
							echo '</td>';
						echo '</tr>';
					}
					if (mysqli_num_rows($clients) < 1) { // If no client invitations is sent
						echo '<tr>';
							echo '<td colspan="10">';
							echo 'No client invitations sent.';
							echo '</td>';
						echo '</tr>';
					}
				?>
				</thead>
			</table><br />
			
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

$(".error").hide();
$(".survey-client").hide();
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

	$("#toggle_client_form").on("click", function() {
		$(".survey-client").toggle();
	});
	$("#new_client").submit(function(e) {
		if (!$("input[name=f_name]").val() || !$("input[name=l_name]").val() || !$("input[name=c_email]").val()) {
			$("#error_txt").text("Please enter all required fields.");
			$(".error").show();
			return false;
		} 
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
