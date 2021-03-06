<?php 
	session_start();
	include('header.php');
	include('auth.php');
	if ($_SESSION['u_role'] != "Coordinator" AND $_SESSION['u_role'] != "Admin" AND $_SESSION['u_role'] != "Client") {
		header("Location: index.php");
	}
	$res = mysqli_query($conn,"SELECT setting_value FROM settings_option WHERE setting_name='Sending Offer Letter Deadline'");
	$sending_offer_deadline = mysqli_fetch_assoc($res)['setting_value']; // Deadline for clients to send offer letters
	$sending_offer_deadline = date("l jS, F Y h:i:s A",$sending_offer_deadline); // Formatted deadline with correct date and time format
?>
<?php 
		if(isset($_POST['have_filter'])) {
			$current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$str_builder = "Location: students.php";
			$count = 1;
			foreach($_GET as $key => $value) {
				if($key != "have_skills") {
					if($count == 1) {
						$str_builder .= "?".$key."=".urlencode($value);
					} else {
						$str_builder .= "&".$key."=".urlencode($value);
					}
					$count++;
				}
				
			}
			if(!empty($_POST['have_skills'])) {
				if($str_builder != "Location: students.php") {
					header($str_builder."&have_skills=".urlencode(implode(",",$_POST['have_skills'])));
				} else {
					header($str_builder."?have_skills=".urlencode(implode(",",$_POST['have_skills'])));
				}
			} else {
				header($str_builder);
			}
		}
		if(isset($_POST['want_filter'])) {
			$current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$str_builder = "Location: students.php";
			$count = 1;
			foreach($_GET as $key => $value) {
				if($key != "want_skills") {
					if($count == 1) {
						$str_builder .= "?".$key."=".urlencode($value);
					} else {
						$str_builder .= "&".$key."=".urlencode($value);
					}
					$count++;
				}
				
			}
			if(!empty($_POST['want_skills'])) {
				if($str_builder != "Location: students.php") {
					header($str_builder."&want_skills=".urlencode(implode(",",$_POST['want_skills'])));
				} else {
					header($str_builder."?want_skills=".urlencode(implode(",",$_POST['want_skills'])));
				}
			} else {
				header($str_builder);
			}
		}
?>
<?php if($_SESSION['u_role'] == "Client") {echo '<div class="main-content" style="width:100%;">';} else {echo '<div class="main-content">';} ?>
	<h1>Students</h1>
	<p>List of all the students signed up in this website. <?php if($_SESSION['u_role'] != "Client") {echo '<span id="download_dclearance_file" style="cursor: pointer; text-decoration: underline; color: blue;"><strong>Click Here</strong></span> to download a list of students (USC students and those students who are not enrolled as "Unpaid Intern") who do not have D-Clearance.</p>';} else {echo 'The final deadline to send offer letters to students is <span style="text-decoration: underline" class="font_bold">'.$sending_offer_deadline.'</span>';}?>
	<form id="popup" method="POST" style="display:none;background-color: #fff;box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);border-radius:0 15px 0 0;width:auto;min-width:300px;margin:0;position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); -webkit-transform: translate(-50%, -50%);">
	<img src="images/red_cross_mark.png" id="close_popup" style="position:absolute;top:-3px;right:-3px;width:30px;cursor:pointer;"/>
	<p style="margin:30px 20px 20px;">Student Name: <input type="text" style="padding:5px 10px;" id="student_name" name="student_name" value="" readonly /></p>
	<p style="margin:20px;">Student Email: <input type="text" style="padding:5px 10px;" id="email" name="email" value="" readonly /></p>
	
	<p style="margin:20px;">Project Name: <select name="p_name"><?php 
		$res = mysqli_query($conn,"SELECT * FROM client_projects WHERE client_email='".$_SESSION['u_name']."'");
		while($row_loop = mysqli_fetch_assoc($res)) {
			echo '<option value="'.$row_loop['project_name'].'">'.$row_loop['project_name'].'</option>';
		}
	?></select></p>
	<p style="margin:20px;">Project Role: <select name="p_role"><?php 
		$res = mysqli_query($conn,"SELECT * FROM project_roles");
		while($row_loop = mysqli_fetch_assoc($res)) {
			echo '<option value="'.$row_loop['role_name'].'">'.$row_loop['role_name'].'</option>';
		}
	?></select></p>
	<div class="error" style="display:none;"><p id="error_txt"></p></div>
	<p style="margin:20px;text-align: center;"><input type="submit" name="send_offer_letter" value="Send Offer Letter" /></p>
	</form>
	<?php 
	if($_SESSION['u_role'] == "Client") {
		echo '<p><strong>Tip: </strong>You may filter students by using the dropdown filter below and/or by clicking on the <img src="images/filter_dropdown_button.png" style="border-radius: 4px;background-color:#660000;" /> icon on some of the table column names to filter students by column.</p>';
	}
	?>
	<p>See students who: 
	<select name="filter_student" onchange="updateFilter(this.value);">
			<option value="all">Show All Students</option>
			<option class="coords_and_admin" value="dclearance" <?php if (isset($_GET['filterBy'])) {if ($_GET['filterBy'] == "dclearance") echo 'selected'; } ?> >Needs D-Clearance</option>
			<option class="coords" value="survey" <?php if (isset($_GET['filterBy'])) {if ($_GET['filterBy'] == "survey") echo 'selected'; } ?> >Haven't filled out survey</option>
			<option class="coords" value="not_enrolled" <?php if (isset($_GET['filterBy'])) {if ($_GET['filterBy'] == "not_enrolled") echo 'selected'; } ?> >Not enrolled in any project</option>
			<option class="client" value="available_students" <?php if (isset($_GET['filterBy'])) {if ($_GET['filterBy'] == "available_students") echo 'selected'; } ?> >Only show students who are available</option>
			<option class="client" value="not_reviewed_list" <?php if (isset($_GET['filterBy'])) {if ($_GET['filterBy'] == "not_reviewed_list") echo 'selected'; } ?> >I haven't reviewed and is available</option>
			<option class="client" value="contacted_list" <?php if (isset($_GET['filterBy'])) {if ($_GET['filterBy'] == "contacted_list") echo 'selected'; } ?> >I have contacted</option>
			<option class="client" value="offer_letter_sent_list" <?php if (isset($_GET['filterBy'])) {if ($_GET['filterBy'] == "offer_letter_sent_list") echo 'selected'; } ?> >I sent offer letters</option>
			<?php
				$cp_query = mysqli_query($conn,"SELECT project_name FROM client_projects WHERE client_email='".$_SESSION['u_name']."'");
				while($cp_row = mysqli_fetch_assoc($cp_query)) {
					if(!empty($_GET['filterBy']) && urldecode($_GET['filterBy']) == "1st_pref_".$cp_row['project_name']) {
						echo '<option class="client" value="1st_pref_'.$cp_row['project_name'].'" selected>Students who set '.$cp_row['project_name'].' as 1st preference and are available</option>';
					} else {
						echo '<option class="client" value="1st_pref_'.$cp_row['project_name'].'">Students who set '.$cp_row['project_name'].' as 1st preference and are available</option>';
					}
					if(!empty($_GET['filterBy']) && urldecode($_GET['filterBy']) == "2nd_pref_".$cp_row['project_name']) {
						echo '<option class="client" value="2nd_pref_'.$cp_row['project_name'].'" selected>Students who set '.$cp_row['project_name'].' as 2nd preference or higher and are available</option>';
					} else {
						echo '<option class="client" value="2nd_pref_'.$cp_row['project_name'].'">Students who set '.$cp_row['project_name'].' as 2nd preference or higher and are available</option>';
					}
					
				}
			?>
	</select></p>
	
	<?php 
		
		if(isset($_POST['send_offer_letter'])) {
			$res = mysqli_query($conn,"SELECT * FROM vacancies WHERE project_name='".$_POST['p_name']."' AND role_name='".$_POST['p_role']."'");
			$row = mysqli_fetch_assoc($res);
			$total_seats = $row['total_seats'];
			$closed = $row['closed'] == "Yes"? true : false;
			$role_limit_reached = $row['offer_letters_sent']; // Get the number of offer letters sent for this role on this project
			$role_limit_reached = (($total_seats - $role_limit_reached) > 0)? 'false' : 'true';
			
			if(mysqli_num_rows($res) < 1) { // No vacancy was created for that role on that project.
				echo '<div class="verified"><p id="error_txt" style="font-size: 16pt;">No such vacancies. Please add vacancies to that role before sending offer letters.</p></div>';
			} else {
				$all_query_ok = true; // Set to false if one of the command fails
				// Count the number of offer letters sent for that project
				$res = mysqli_query($conn,"SELECT SUM(offer_letters_sent) AS total_sent FROM vacancies WHERE project_name='".$_POST['p_name']."'");
				$total_sent = intval(mysqli_fetch_assoc($res)['total_sent']); // Total offer letters sent
				$res = mysqli_query($conn,"SELECT setting_value FROM settings_option WHERE setting_name='Offer Letters Limit'");
				$letter_limit = intval(mysqli_fetch_assoc($res)['setting_value']); // Limit on # of offer letters that can be sent
				$res = mysqli_query($conn,"SELECT setting_value FROM settings_option WHERE setting_name='Accepting Offer Letter Deadline'");
				$offer_acceptance_deadline = mysqli_fetch_assoc($res)['setting_value']; // Deadline for students to accept offer letters
				$offer_acceptance_deadline = date("l jS, F Y h:i:s A",$offer_acceptance_deadline); // Formatted deadline with correct date and time format
				$res = mysqli_query($conn,"SELECT setting_value FROM settings_option WHERE setting_name='Sending Offer Letter Deadline'");
				$sending_offer_deadline = mysqli_fetch_assoc($res)['setting_value']; // Deadline for clients to send offer letters
				$sending_offer_deadline_reached = $sending_offer_deadline < time()? true : false;
				$sending_offer_deadline = date("l jS, F Y h:i:s A",$sending_offer_deadline); // Formatted deadline with correct date and time format
				$res = mysqli_query($conn,"SELECT * FROM offer_letter_requests WHERE student_email='".$_POST['email']."' AND project_name='".$_POST['p_name']."'");

				// Check if the number of offer letters sent is within the limit set by DR Coordinator and within the limit of what the vacancy allows.
				if (($total_sent < $letter_limit) && (mysqli_num_rows($res) < 1) && ($role_limit_reached == 'false') && !$closed && !$sending_offer_deadline_reached) { 
					
					mysqli_autocommit($conn, FALSE); // Disable auto-commit. 
					mysqli_query($conn,"UPDATE vacancies SET offer_letters_sent=offer_letters_sent+1 WHERE project_name='".$_POST['p_name']."' AND role_name='".$_POST['p_role']."'")? NULL : $all_query_ok = false;
					mysqli_query($conn,"INSERT INTO offer_letter_requests (student_email,project_name,role_name,status) VALUES ('".$_POST['email']."','".$_POST['p_name']."','".$_POST['p_role']."','Pending')")? NULL : $all_query_ok = false;
					$all_query_ok? mysqli_commit($conn) : mysqli_rollback($conn); // Rollback if one of the two commands fail
					mysqli_autocommit($conn, TRUE); // Re-enable auto-commit. 				
					
					$url = parse_url((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_HOST); // Parses the domain of the DR Website
					$curr_path = dirname((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
			
					if ($all_query_ok) { 
						// Change values of "Marked As" to "Offer Letter Sent" for that student
						mysqli_autocommit($conn, FALSE); // Disable auto-commit. 
						mysqli_query($conn,"DELETE FROM reviewed_students WHERE client_email='".$_SESSION['u_name']."' AND student_email='".$_POST['email']."'")? NULL : $all_query_ok = false;
						mysqli_query($conn,"INSERT INTO reviewed_students (client_email,student_email,marked_as) VALUES ('".$_SESSION['u_name']."','".$_POST['email']."','Offer Letter Sent')")? NULL : $all_query_ok = false;
						$all_query_ok? mysqli_commit($conn) : mysqli_rollback($conn); // Rollback if one of the two commands fail
						mysqli_autocommit($conn, TRUE); // Re-enable auto-commit.
						
						
						// Email for notifying student that they have received an offer letter.
						$to = $_POST['email'];

						// Subject
						$subject = 'CSCI 590 DR Course '.$_POST['p_name'].' - Offer Letter';

						// Message
						$message = '
						<html>
						<head>
						</head>
						<body>
							<p>Dear '.$_POST['student_name'].',<br /><br />This message is to notify you that you have received an offer letter to join the '.$_POST['p_name'].' project as a '.$_POST['p_role'].'.<br />
							Please note that the deadline to accept offer letters is '.$offer_acceptance_deadline.'. So don\'t forget to accept an offer before this date. <br /><br />
							Thanks, <br />
							CSCI 590 DR Management Team
							</p>
						</body>
						</html>
						';

						// To send HTML mail, the Content-type header must be set
						$headers[] = 'MIME-Version: 1.0';
						$headers[] = 'Content-type: text/html; charset=iso-8859-1';
						$headers[] = 'From: DR CSCI-590 <no-reply@'.$url.'>'; // Format of the variable ("From: DR CSCI-590 <no-reply@domain_name.com>")
						// Mail it to student
						mail($to, $subject, $message, implode("\r\n", $headers));
						
						$res_update = mysqli_query($conn,"SELECT SUM(offer_letters_sent) AS total_sent FROM vacancies WHERE project_name='".$_POST['p_name']."'");
						$total_sent = intval(mysqli_fetch_assoc($res_update)['total_sent']); // Update total offer letters sent
						$total = $letter_limit-$total_sent;
						echo '<div class="verified"><p id="success_txt" style="font-size: 16pt;">Offer letter sent! You can send '.$total.' more offer letter(s) for '.$_POST['p_name'].' project.</p></div>';
					}
					
				} else if(mysqli_num_rows($res) > 0) { // Offer letter already sent to the student for this project
					echo '<div class="verified"><p id="error_txt" style="font-size: 16pt;">An offer letter had already been sent to this student for this project.</p></div>';
				} else if($sending_offer_deadline_reached) { // Deadline for sending offer letters ended
					echo '<div class="verified"><p id="error_txt" style="font-size: 16pt;">Deadline for sending offer letters has ended! No more offer letters can be sent.</p></div>';
				} else if (($role_limit_reached == 'true')) {
					echo '<div class="verified"><p id="error_txt" style="font-size: 16pt;">Cannot send more offer letters than the number of vacancy you have for each role.</p></div>';
				} else if ($closed) {
					echo '<div class="verified"><p id="error_txt" style="font-size: 16pt;">Vacancy for this role is closed. Cannot send offer letters for this role.</p></div>';
				} else { // Maximum limit reached for sending offer letters to students for each project
					echo '<div class="verified"><p id="error_txt" style="font-size: 16pt;">Cannot send any more offer letters for this project. Maximum limit reached!</p></div>';
				}
			}
		}
		if(isset($_POST['mark_change'])) { // Marked as value has changed for a student
			// Delete the row from the marked student if it already exists
			mysqli_query($conn,"DELETE FROM reviewed_students WHERE student_email='".$_POST['student_email']."'");
			// Insert the updated information into table
			mysqli_query($conn,"INSERT INTO reviewed_students (client_email,student_email,marked_as) VALUES ('".$_SESSION['u_name']."','".$_POST['student_email']."','".$_POST['mark_change']."')");
		}
		if(isset($_POST['enroll_project'])) { // Enroll the student to the project selected by DR Coordinator
			// Insert the updated information into table
			mysqli_query($conn,"UPDATE login_info SET project_enrolled='".$_POST['enroll_project']."' WHERE username='".$_POST['student_email']."'");
			// Insert the updated information into table
			mysqli_query($conn,"INSERT into offer_letter_requests (student_email,project_name,role_name,status) VALUES ('".$_POST['student_email']."','".$_POST['enroll_project']."','--','Added')");
		}
		if(isset($_POST['units_change'])) { // # 'Units registered' has changed for a student
			// Update information into login_info table
			mysqli_query($conn,"UPDATE login_info SET n_units='".$_POST['units_change']."' WHERE username='".$_POST['student_email']."'");
		}
		if(isset($_POST['active_change'])) { // Status (Active/Withdrawn) has changed for a student
			// Update information into login_info table
			mysqli_query($conn,"UPDATE login_info SET status='".$_POST['active_change']."' WHERE username='".$_POST['student_email']."'");
		}
		if(isset($_POST['clear_list'])) { // Clear the list of students who changed # of units
			// Delete all rows in the changed_units table
			mysqli_query($conn,"TRUNCATE TABLE changed_units");
		}
		if(isset($_POST['download_changed_units_file'])) { // Download the file if there is at least one student in the "Changed # Units" list
			$query = mysqli_query($conn, "SELECT li.f_name,li.l_name,li.d_clearance,cu.username,cu.from_units,cu.to_units,cu.timestamp FROM changed_units cu LEFT JOIN login_info li ON li.username=cu.username WHERE li.role='Student' AND li.status='Active'");
			if(mysqli_num_rows($query) > 0) {
				echo "true";
			} else {
				echo "false";
			}
		}
	?>
	<?php 
		if($_SESSION['u_role'] == 'Client') {
			echo '<span class="filter_toggle" style="cursor: pointer; text-decoration: underline; color: blue;"><strong><p style="display:inline;">Filter students by the skills that they have</p></strong></span><span style="color:blue;cursor:pointer;" class="arrow">⯆</span><br />';
			echo '<table class="filter_form" style="display:none;">';
			echo '<tr>';
			echo '<td>';
			if(!empty($_GET['filterBy'])) {
				echo '<form class="post_filter_form" action="students.php?filterBy='.$_GET['filterBy'].'" method="POST">';
			} else {
				echo '<form class="post_filter_form" action="students.php" method="POST">';
			}
			
			$all_skills = mysqli_query($conn,"SELECT * FROM skills");
			$skills_arr = array();
			if(!empty($_GET['have_skills'])) { // If the filter is applied
				$skills_arr = explode(",",urldecode($_GET['have_skills']));
				while($all_s = mysqli_fetch_assoc($all_skills)) {
					if(in_array($all_s['skill_name'],$skills_arr)) {
						echo '<label><input type="checkbox" name="have_skills[]" value="'.$all_s['skill_name'].'" checked /> '.$all_s['skill_name'].'</label><br />';
					} else {
						echo '<label><input type="checkbox" name="have_skills[]" value="'.$all_s['skill_name'].'" /> '.$all_s['skill_name'].'</label><br />';
					}
				}
			} else { // List out all the checkboxes as unchecked if filter is not applied
				while($all_s = mysqli_fetch_assoc($all_skills)) {
					echo '<label><input type="checkbox" name="have_skills[]" value="'.$all_s['skill_name'].'" /> '.$all_s['skill_name'].'</label><br />';
				}
			}
			echo '</td>';
			echo '</tr>';
			echo '</tr>';
			echo '<td style="text-align:center;">';
			echo '<input type="submit" style="width:100px;height:30px;font-size:11pt;text-align:center;margin:5px;padding:0;" value="Apply Filter" name="filter" />';
			echo '</form>';
			echo '</td>';
			echo '</tr>';
			echo '</table><br />';
		} 
	?>
	<form id="student_info" action="" method="POST">

	<table class="entries">
		<thead>
			<tr>
				<th>Name</th>
				<th>Email Address</th>
				<th>Survey?</th>
				<?php if($_SESSION['u_role'] == "Coordinator" OR $_SESSION['u_role'] == "Admin") echo '<th>Project</th>'; ?>
				<?php if($_SESSION['u_role'] == "Coordinator") echo '<th>Client</th>'; ?>
        <?php 
			if($_SESSION['u_role'] == "Client") {
				echo '<th nowrap>Skills <br />(They Have) ';
				if(empty($_GET['have_skills'])) {
					echo '<img id="skills_have_dropdown" src="images/filter_dropdown_button.png" />';
				} else {
					echo '<img id="skills_have_dropdown" src="images/filter_dropdown_filtered_button.png" />';
				}
				
				echo '<div class="drop_down have_skills" style="display:none;">';
				$count=1;
				$form_action_builder = '<form class="post_filter_form" action="students.php';
				foreach($_GET as $key => $value) {
					if($count == 1) {
						$form_action_builder .= '?'.$key.'='.$value;
					} else {
						$form_action_builder .= '&'.$key.'='.$value;
					}
					$count++;
				}
				$form_action_builder .= '" method="POST">';
				echo $form_action_builder;
				echo '<table>';
				echo '<tr style="background-color:white;"><td><strong>Filter by the skills they have</strong></td></tr>';
				$s_arr  = mysqli_query($conn,"SELECT * FROM skills");
				while($s = mysqli_fetch_assoc($s_arr)) {
					echo '<tr style="background-color:white;">';
						echo '<td>';
						if(!empty($_GET['have_skills']) && (strpos(urldecode($_GET['have_skills']),$s['skill_name']) > -1)) {
							echo '<label><input type="checkbox" name="have_skills[]" value="'.$s['skill_name'].'" checked /> '.$s['skill_name'].'</label><br />';
						} else {
							echo '<label><input type="checkbox" name="have_skills[]" value="'.$s['skill_name'].'" /> '.$s['skill_name'].'</label><br />';
						}
						echo '</td>';
					echo '</tr>';
				}
				echo '<tr style="background-color:white;text-align:center;"><td><input type="submit" style="width:100px;height:30px;font-size:11pt;margin:5px;padding:0;" value="Apply Filter" name="have_filter" /></td></tr>';
				echo '</div></table></form></th>';
				echo '<th nowrap>Skills <br />(Want to Learn) '; 
				if(empty($_GET['want_skills'])) {
					echo '<img id="skills_want_dropdown" src="images/filter_dropdown_button.png" />';
				} else {
					echo '<img id="skills_want_dropdown" src="images/filter_dropdown_filtered_button.png" />';
				}
				
				echo '<div class="drop_down want_skills" style="display:none;">';
				$count=1;
				$form_action_builder = '<form class="post_filter_form" action="students.php';
				foreach($_GET as $key => $value) {
					if($count == 1) {
						$form_action_builder .= '?'.$key.'='.$value;
					} else {
						$form_action_builder .= '&'.$key.'='.$value;
					}
					$count++;
				}
				$form_action_builder .= '" method="POST">';
				echo $form_action_builder;
				echo '<table>';
				echo '<tr style="background-color:white;"><td><strong>Filter by the skills they want to learn</strong></td></tr>';
				$s_arr  = mysqli_query($conn,"SELECT * FROM skills");
				while($s = mysqli_fetch_assoc($s_arr)) {
					echo '<tr style="background-color:white;">';
						echo '<td>';
						if(!empty($_GET['want_skills']) && (strpos(urldecode($_GET['want_skills']),$s['skill_name']) > -1)) {
							echo '<label><input type="checkbox" name="want_skills[]" value="'.$s['skill_name'].'" checked /> '.$s['skill_name'].'</label><br />';
						} else {
							echo '<label><input type="checkbox" name="want_skills[]" value="'.$s['skill_name'].'" /> '.$s['skill_name'].'</label><br />';
						}
						echo '</td>';
					echo '</tr>';
				}
				echo '<tr style="background-color:white;text-align:center;"><td><input type="submit" style="width:100px;height:30px;font-size:11pt;margin:5px;padding:0;" value="Apply Filter" name="want_filter" /></td></tr>';
				echo '</div></table></form></th>';
				echo '<th>Availability</th>';
			}
			?>
				<?php if($_SESSION['u_role'] == "Coordinator") {echo '<th>'.wordwrap("D-Clearance?",7,"<br />",true).'</th>';} else if($_SESSION['u_role'] == "Admin") {echo '<th>D-Clearance?</th>';} ?>
				<th># Units</th>
				<?php if($_SESSION['u_role'] == "Client") echo '<th>Marked As?</th><th>Send Offer Letter?</th>'; ?>
				<?php 
					if($_SESSION['u_role'] == "Client") {
						$num_projects = mysqli_query($conn,"SELECT COUNT(*) AS n_projects FROM projects");
						$num_projects = (int)mysqli_fetch_assoc($num_projects)['n_projects'];
						$count = 1;
						while($count <= $num_projects) {
							if($count == 1) {
								echo '<th>'.$count.'st Pref.</th>';
							} else if($count == 2) {
								echo '<th>'.$count.'nd Pref.</th>';
							} else if($count == 3) {
								echo '<th>'.$count.'rd Pref.</th>';
							} else {
								echo '<th>'.$count.'th Pref.</th>';
							}
							$count++;
						}
					} 
				?>
        <?php if($_SESSION['u_role'] == "Coordinator" OR $_SESSION['u_role'] == "Admin") echo '<th>Active/ Withdrawn</th>'; ?>
				<?php if($_SESSION['u_role'] == "Coordinator" OR $_SESSION['u_role'] == "Admin") echo '<th>Delete?</th>'; ?>
			</tr>
			<?php 
				include('auth.php');
				if (isset($_GET['filterBy']) && $_GET['filterBy'] == "dclearance") {
					$students = mysqli_query($conn, "SELECT f_name,l_name,username,survey,n_units,d_clearance,project_enrolled,resume_name_on_server,status FROM login_info WHERE role='Student' AND d_clearance='No'");
				} else if (isset($_GET['filterBy']) && $_GET['filterBy'] == "survey") {
					$students = mysqli_query($conn, "SELECT f_name,l_name,username,survey,n_units,d_clearance,project_enrolled,resume_name_on_server,status FROM login_info WHERE role='Student' AND survey='No'");
				} else if (isset($_GET['filterBy']) && $_GET['filterBy'] == "not_enrolled") {
					$students = mysqli_query($conn, "SELECT f_name,l_name,username,survey,n_units,d_clearance,project_enrolled,resume_name_on_server,status FROM login_info WHERE role='Student' AND (project_enrolled IS NULL OR project_enrolled='')");
				} else if (isset($_GET['filterBy']) && $_GET['filterBy'] == "available_students")  {
					$students = mysqli_query($conn, "SELECT f_name,l_name,username,survey,n_units,d_clearance,project_enrolled,resume_name_on_server,status FROM login_info WHERE role='Student' AND (project_enrolled IS NULL OR project_enrolled='') AND NOT status='Withdrawn'");
				} else if (isset($_GET['filterBy']) && $_GET['filterBy'] == "not_reviewed_list")  {
					$students = mysqli_query($conn, "SELECT li.f_name,li.l_name,li.username,li.survey,li.n_units,li.d_clearance,li.project_enrolled,li.resume_name_on_server,li.status,rs.marked_as FROM login_info li LEFT JOIN (SELECT * FROM reviewed_students WHERE client_email='".$_SESSION['u_name']."') rs ON li.username=rs.student_email WHERE li.role='Student' AND NOT li.status='Withdrawn' AND (li.project_enrolled='' OR li.project_enrolled IS NULL) AND (rs.marked_as IS NULL OR rs.marked_as='' OR rs.marked_as='Unseen')");
				} else if (isset($_GET['filterBy']) && $_GET['filterBy'] == "contacted_list")  {
					$students = mysqli_query($conn, "SELECT li.f_name,li.l_name,li.username,li.survey,li.n_units,li.d_clearance,li.project_enrolled,li.resume_name_on_server,li.status,rs.marked_as FROM login_info li LEFT JOIN reviewed_students rs ON li.username=rs.student_email WHERE li.role='Student' AND rs.marked_as='Contacted'");
				} else if (isset($_GET['filterBy']) && $_GET['filterBy'] == "offer_letter_sent_list")  {
					$students = mysqli_query($conn, "SELECT li.f_name,li.l_name,li.username,li.survey,li.n_units,li.d_clearance,li.project_enrolled,li.resume_name_on_server,li.status,rs.marked_as FROM login_info li LEFT JOIN reviewed_students rs ON li.username=rs.student_email WHERE li.role='Student' AND rs.marked_as='Offer Letter Sent'");
				} else {
					$students = mysqli_query($conn, "SELECT f_name,l_name,username,survey,n_units,d_clearance,project_enrolled,resume_name_on_server,status from login_info WHERE role='Student'");
					if(($_SESSION['u_role'] == "Client") && !empty($_GET['filterBy'])) {
						$cp_query = mysqli_query($conn,"SELECT project_name FROM client_projects WHERE client_email='".$_SESSION['u_name']."'");
						while($cp_row = mysqli_fetch_assoc($cp_query)) {
							if (urldecode($_GET['filterBy']) == "1st_pref_".$cp_row['project_name']) {
								$students = mysqli_query($conn,"SELECT li.f_name,li.l_name,li.username,li.survey,li.n_units,li.d_clearance,li.project_enrolled,li.resume_name_on_server,li.status FROM login_info li LEFT JOIN project_preferences pp ON li.username=pp.email WHERE pp.preference_order='1' AND pp.project_name='".$cp_row['project_name']."' AND li.role='Student' AND (li.project_enrolled IS NULL OR li.project_enrolled='') AND NOT li.status='Withdrawn'");
							} else if (urldecode($_GET['filterBy']) == "2nd_pref_".$cp_row['project_name']) {
								$students = mysqli_query($conn,"SELECT li.f_name,li.l_name,li.username,li.survey,li.n_units,li.d_clearance,li.project_enrolled,li.resume_name_on_server,li.status FROM login_info li LEFT JOIN project_preferences pp ON li.username=pp.email WHERE (pp.preference_order='1' OR pp.preference_order='2') AND pp.project_name='".$cp_row['project_name']."' AND li.role='Student' AND (li.project_enrolled IS NULL OR li.project_enrolled='') AND NOT li.status='Withdrawn'");
							}
						}
					}
				}
				$no_rows = true; // Check whether at least one row is displayed
				while($row = mysqli_fetch_assoc($students)) {
					$skill_have_arr = array();
					$skill_want_arr = array();
					if($_SESSION['u_role'] == "Client") {
						$sk = mysqli_query($conn,"SELECT * FROM student_skills WHERE email='".$row['username']."' AND skill_type='Have Experience'");
						while($sk_row = mysqli_fetch_assoc($sk)) {
							$skill_have_arr[] = $sk_row['skill_name'];
						}
						$sk = mysqli_query($conn,"SELECT * FROM student_skills WHERE email='".$row['username']."' AND skill_type='Want to Learn'");
						while($sk_row = mysqli_fetch_assoc($sk)) {
							$skill_want_arr[] = $sk_row['skill_name'];
						}
					}


					if(!empty($_GET['have_skills'])) {
						$search_skills = explode(",",urldecode($_GET['have_skills']));
						$has_required_skill = !empty(array_intersect($skill_have_arr,$search_skills)); // Student has at least one skill client searches for (using filter).
					} else {
						$has_required_skill = true;
					}
					if(!empty($_GET['want_skills'])) {
						$search_skills = explode(",",urldecode($_GET['want_skills']));
						$want_required_skill = !empty(array_intersect($skill_want_arr,$search_skills)); // Student want to learn at least one skill client searches for (using filter).
					} else {
						$want_required_skill = true;
					}
					if($has_required_skill && $want_required_skill) {
						$no_rows = false;
						echo '<tr>';
							echo '<td>';
							echo $row['f_name'].' '.$row['l_name'];
							echo '</td>';
							echo '<td>';
							echo $row['username'];
							echo '</td>';
							echo '<td nowrap>';
							if ($row['survey'] == "No") {
								echo "Not Filled";
							} else {
								if($_SESSION['u_role'] != "Client") {
									echo '<a href="survey.php?student='.$row['username'].'" target="_blank">View</a>';
								} else {
									echo '<a href="survey.php?student='.$row['username'].'" target="_blank">View Form</a><br />';
									echo '<a href="'.$row['resume_name_on_server'].'" target="_blank">View Resume</a>';
								}
							}
							echo '</td>';
							
							if($_SESSION['u_role'] == "Client") {
								echo '<td>';
								if(!empty($skill_have_arr)) {
									echo implode(", ",$skill_have_arr);
								} else {
									echo "-";
								}
								echo '</td>';
								echo '<td>';
								if(!empty($skill_want_arr)) {
									echo implode(", ",$skill_want_arr);
								} else {
									echo "-";
								}
								echo '</td>';
							}
							
							echo '<td>';
							if (($_SESSION['u_role'] == "Admin")) {		
								$availability = empty($row['project_enrolled'])? '<p class="font_red">Not Enrolled</p>' : $row['project_enrolled'];
								echo $availability;
							} else if ($_SESSION['u_role'] == "Coordinator") {
								if(empty($row['project_enrolled'])) {
									$res = mysqli_query($conn,"SELECT project_name FROM projects p WHERE NOT EXISTS (SELECT cp.project_name FROM client_projects cp WHERE p.project_name = cp.project_name)");
									if(mysqli_num_rows($res) > 0) {
										echo '<select name="enroll_project" class="enroll_project-*'.$row['username'].'" style="padding:5px 10px;margin:25px 5px;border-radius:5px;">';
										echo '<option value="">Not Enrolled</option>';
									} else {
										echo '<p class="font_red">Not Enrolled</p>';
									}
									while($projects_of_coordinators = mysqli_fetch_assoc($res)) {
										echo '<option value="'.$projects_of_coordinators['project_name'].'">'.$projects_of_coordinators['project_name'].'</option>';
									}
									echo '</select>';
									echo '<img src="images/green_check_mark.png" class="enroll_project-*'.$row['username'].'" style="display:none;width:12px;margin:4px 0 0 4px;"/>';
								} else {
									$availability = empty($row['project_enrolled'])? '<p class="font_red">Not Enrolled</p>' : $row['project_enrolled'];
									echo $availability;
								}
							} else if ($_SESSION['u_role'] == "Client") {
								$availability = (empty($row['project_enrolled']) && ($row['status'] != "Withdrawn"))? '<p class="font_bold font_green">Yes</p>' : '<p class="font_bold font_red">No</p>';
								echo $availability;
							}
							echo '</td>';
							if ($_SESSION['u_role'] == "Coordinator") {
								echo '<td>';
								if(!empty($row['project_enrolled'])) {
									$res1 = mysqli_query($conn,"SELECT li.f_name,li.l_name FROM (SELECT * FROM client_projects WHERE project_name='".$row['project_enrolled']."') AS cp LEFT JOIN login_info li ON cp.client_email=li.username WHERE f_name IS NOT NULL AND l_name IS NOT NULL");
									$client_arr = array();
									while($res1_row = mysqli_fetch_assoc($res1)) {
										$client_arr[] = $res1_row['f_name'];
									}
									if(!empty($client_arr)) {
										echo implode(", ",$client_arr);
									} else {
										echo "DR Coordinator";
									}									
								} else {
									echo "-";
								}

								echo '</td>';
							}
							
							if($_SESSION['u_role'] == "Client") {
							echo '<td>';
							if ($row['n_units'] == "1") {
								echo '1 unit';
							} else if ($row['n_units'] == "2") {
								echo '2 units';
							} else if ($row['n_units'] == "3") {
								echo '3 units';
							} else if ($row['n_units'] == "4+") {
								echo '4+ units';
							} else if ($row['n_units'] == "intern") {
								echo 'Unpaid Intern';
							} else {
								echo '-';
							}
							echo '</td>';
							echo '<td>';
								$res = mysqli_query($conn,"SELECT * FROM reviewed_students WHERE client_email='".$_SESSION['u_name']."' AND student_email='".$row['username']."'");
								$disabled = '';
								if((mysqli_num_rows($res) > 0)) {
									$marked_as = mysqli_fetch_assoc($res)['marked_as'];
									$marked_as = empty($marked_as)? 'Unseen' : $marked_as;
								} else if(mysqli_num_rows($res) < 1) {
									$marked_as = 'Unseen';
								}
								if($marked_as == "Offer Letter Sent") {
									$disabled = 'disabled';
								}
								echo '<select name="mark" class="mark-*'.$row['username'].'" '.$disabled.'>';
									if($marked_as == "Unseen") {
										echo '<option value="unseen" selected>Unseen</option>';
										echo '<option value="seen">Seen</option>';
										echo '<option value="contacted">Contacted</option>';
									} else if ($marked_as == "Seen") {
										echo '<option value="unseen">Unseen</option>';
										echo '<option value="seen" selected>Seen</option>';
										echo '<option value="contacted">Contacted</option>';
									} else if ($marked_as == "Contacted") {
										echo '<option value="unseen">Unseen</option>';
										echo '<option value="seen">Seen</option>';
										echo '<option value="contacted" selected>Contacted</option>';
									} else if ($marked_as == "Offer Letter Sent") {
										echo '<option value="unseen">Unseen</option>';
										echo '<option value="seen">Seen</option>';
										echo '<option value="contacted">Contacted</option>';
										echo '<option value="offer_letter_sent" selected>Offer Letter Sent</option>';
									} else {
										echo '<option value="unseen" selected>Unseen</option>';
										echo '<option value="seen">Seen</option>';
										echo '<option value="contacted">Contacted</option>';
									}	
										
								echo '</select>';
								echo '<img src="images/green_check_mark.png" class="mark-*'.$row['username'].'" style="display:none;width:12px;margin:4px 0 0 4px;"/>';
							echo '</td>';

								if ($marked_as == "Offer Letter Sent"){
									echo '<td>';
									echo '<img src="images/offer_letter_sent.png" title="Offer letter has been sent to this student!" style="width:30px;filter:grayscale(100%);" />';
									echo '</td>';	
								} else if (empty($row['project_enrolled']) && ($row['status'] != "Withdrawn")) {
									echo '<td>';
									echo '<img src="images/offer_letter.png" id="offer-*'.str_replace(' ','_',$row['f_name']).'-*'.str_replace(' ','_',$row['l_name']).'-*'.$row['username'].'" class="offer" style="width: 30px;cursor: pointer;" />';
									echo '</td>';
								} else if (!empty($row['project_enrolled'])) {
									echo '<td>';
									echo '<img src="images/offer_letter.png" title="Student is already enrolled in a project!" style="width:30px;filter:grayscale(100%);" />';
									echo '</td>';								
								} else {
									echo '<td>';
									echo '<img src="images/offer_letter.png" title="Student has withdrawn from this course!" style="width:30px;filter:grayscale(100%);" />';
									echo '</td>';										
								}
								$pref_num = mysqli_query($conn,"SELECT project_name FROM project_preferences WHERE email='".$row['username']."'");
								if(mysqli_num_rows($pref_num) < 1) {
									$num_projects = mysqli_query($conn,"SELECT COUNT(*) AS n_projects FROM projects");
									$num_projects = (int)mysqli_fetch_assoc($num_projects)['n_projects'];
									$count = 1;
									while($count <= $num_projects) {
										echo '<td>-</td>';
										$count++;
									}
								} else {
									while($pref_row = mysqli_fetch_assoc($pref_num)) {
										echo '<td>';
										if(!empty($pref_row['project_name'])) {
											echo $pref_row['project_name'];
										} else {
											echo '-';
										}
										echo '</td>';
									}
								}

							}
							if ($_SESSION['u_role'] == "Coordinator" OR $_SESSION['u_role'] == "Admin") {
								echo '<td>';
								echo '<label class="switch">';
									if ($row['d_clearance'] == "Yes") {
										echo '<input type="checkbox" id="'.$row['username'].'" checked>';
									} else {
										echo '<input type="checkbox" id="'.$row['username'].'">';
									}
									echo '<span class="slider round"></span>';
								echo '</label>';
								echo '</td>';
								echo '<td>';
								echo '<select name="units" class="units-*'.$row['username'].'" style="padding:5px 10px;margin:25px 5px;border-radius:5px;" >';
									if($row['n_units'] == "1") {
										echo '<option value="select">Select Units</option>';
										echo '<option value="1" selected>1 unit</option>';
										echo '<option value="2">2 units</option>';
										echo '<option value="3">3 units</option>';
										echo '<option value="4+">4+ units</option>';
										echo '<option value="intern">Unpaid Intern</option>';
									} else if($row['n_units'] == "2") {
										echo '<option value="select">Select Units</option>';
										echo '<option value="1">1 unit</option>';
										echo '<option value="2" selected>2 units</option>';
										echo '<option value="3">3 units</option>';
										echo '<option value="4+">4+ units</option>';
										echo '<option value="intern">Unpaid Intern</option>';
									} else if($row['n_units'] == "3") {
										echo '<option value="select">Select Units</option>';
										echo '<option value="1">1 unit</option>';
										echo '<option value="2">2 units</option>';
										echo '<option value="3" selected>3 units</option>';
										echo '<option value="4+">4+ units</option>';
										echo '<option value="intern">Unpaid Intern</option>';
									} else if($row['n_units'] == "4+") {
										echo '<option value="select">Select Units</option>';
										echo '<option value="1">1 unit</option>';
										echo '<option value="2">2 units</option>';
										echo '<option value="3">3 units</option>';
										echo '<option value="4+" selected>4+ units</option>';
										echo '<option value="intern">Unpaid Intern</option>';
									} else if($row['n_units'] == "intern") {
										echo '<option value="select">Select Units</option>';
										echo '<option value="1">1 unit</option>';
										echo '<option value="2">2 units</option>';
										echo '<option value="3">3 units</option>';
										echo '<option value="4+">4+ units</option>';
										echo '<option value="intern" selected>Unpaid Intern</option>';
									} else {
										echo '<option value="select">Select Units</option>';
										echo '<option value="1">1 unit</option>';
										echo '<option value="2">2 units</option>';
										echo '<option value="3">3 units</option>';
										echo '<option value="4+">4+ units</option>';
										echo '<option value="intern">Unpaid Intern</option>';
									}
								echo '</select>';
								echo '<img src="images/green_check_mark.png" class="units-*'.$row['username'].'" style="display:none;width:15px;margin:14px 0 0 4px;"/>';
								echo '</td>';
								echo '<td>';
								echo '<select name="active" class="active-*'.$row['username'].'" style="padding:5px 10px;margin:25px 5px;border-radius:5px;" >';
									if($row['status'] == "Withdrawn") {
										echo '<option value="active">Active</option>';
										echo '<option value="withdrawn" selected>Withdrawn</option>';
									} else {
										echo '<option value="active" selected>Active</option>';
										echo '<option value="withdrawn">Withdrawn</option>';
									}	
								echo '</select>';
								echo '<img src="images/green_check_mark.png" class="active-*'.$row['username'].'" style="display:none;width:15px;margin:14px 0 0 4px;"/>';
								echo '</td>';

								echo '<td>';
								echo '<img src="images/delete_cross_mark.png" id="delete-'.$row['username'].'" class="delete" style="width:30px;cursor:pointer;" />';
								echo '</td>';
							}
						echo '</tr>';						
					}

				}
				if ($no_rows) {
					echo '<tr>';
						echo '<td colspan="30">';
						echo 'No results to show.';
						echo '</td>';
					echo '</tr>';
				}
			?>
		</thead>
	</table>
	</form>
	<br />
	<?php 
	if ($_SESSION['u_role'] == "Coordinator") {
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
				echo '<td colspan="30">';
				echo 'No results to show.';
				echo '</td>';
			echo '</tr>';
		}

	echo '</thead>
	</table>';
	}
			?>
</div><br />
</div>
<script>
<?php 
	echo 'var role ="'.$_SESSION['u_role'].'";';
	if(!empty($_GET['have_skills'])) {
		echo 'var show_filter = true;';
	} else {
		echo 'var show_filter = false;';
	}
?>
if (role != "Coordinator") {
	$(".coords").remove();
} 
if (role != "Client") {
	$(".client").remove();
} 
if (role != "Admin") {
	$(".admin").remove();
} 
if ((role != "Admin") && role != "Coordinator") {
	$(".coords_and_admin").remove();
}
if(show_filter) {
	$(".filter_form").show();
}
$(".filter_toggle").click(function(e){
	$(".filter_form").toggle();
	if($(".arrow").html() == "⯆") {
		$(".arrow").html("⯅");
	} else {
		$(".arrow").html("⯆");
	}
});

$("#post_filter_form").submit(function(e){
	$.ajax({
		url: "students.php",
		type: "POST",
		data: { "filter": true },
		success: function(response){
			
		},
		error: function(){
		}
	});
});
$("#popup").hide();
$(document).ready(function(){
	$("#skills_have_dropdown").css("cursor","pointer");
	$("#skills_have_dropdown").click(function(e){
		$(".have_skills").toggle();
		if($(".have_skills").is(":visible")) {
			$(".want_skills").hide();
		}
	});
	$("#skills_want_dropdown").css("cursor","pointer");
	$("#skills_want_dropdown").click(function(e){
		$(".want_skills").toggle();
		if($(".want_skills").is(":visible")) {
			$(".have_skills").hide();
		}
	});
	$("input[type=checkbox]").click(function(event){
		var s_id = event.target.id;
		$.ajax({
			url: "update_info.php",
			type: "POST",
			data: { "name": s_id },
			success: function(response){
			},
			error: function(){
			}
		});
	});
	$(".delete").click(function(event){
		if (confirm('Are you sure you want to delete this student?')) {
			var s_id = event.target.id;
			$.ajax({
				url: "update_info.php",
				type: "POST",
				data: { "del_student": s_id },
				success: function(response){
				},
				error: function(){
				}
			});
			window.location.replace("students.php");
		} else {
			return false;
		}
	});
	$(".offer").click(function(event){
		var id_arr = $(this).attr("id").replace('_',' ').split("-*");
		var f_name = id_arr[1];
		var l_name = id_arr[2];
		var email = id_arr[3];
		$("input[id=student_name]").val(f_name+" "+l_name);
		$("input[id=email]").val(email);
		$("#popup").show();
	});
	$("input[name=send_offer_letter]").click(function(event){
		if (confirm('Are you sure you want to send offer letter to this student?')) {
			$("#popup").hide();
			var s_id = event.target.id;
			var email = $("input[id=email]").val();
			$.ajax({
				url: "students.php",
				type: "POST",
				data: { "send_offer_letter": s_id, "email": email},
				success: function(response){
					if(response == "Offer letter sent!") {
						$("#popup").hide();
						window.location.replace("students.php");
					} else {
						$("#error_txt").text(response);
						$("#error_txt").show();
					}
				}
			});
			window.location.replace("students.php");
		} else {
			return false;
		}
	});
	$("#close_popup").on("click",function(){
		$("#popup").toggle();
	});
	$("a").click(function(e) {
		location.reload();
	});
	$("select[name=mark]").change(function(e) {
		var student_email = $(this).attr("class").replace('mark-*','');
		var mark_value = $("."+$.escapeSelector($(this).attr("class"))+" option:selected").text();
		$.ajax({
			url: "students.php",
			type: "POST",
			data: { "mark_change": mark_value, "student_email": student_email},
			success: function(response){
				
			}
			
		});
		$("img[class="+$.escapeSelector($(this).attr("class"))+"]").show();
	});
	$("select[name=enroll_project]").change(function(e) {
		var student_email = $(this).attr("class").replace('enroll_project-*','');
		var project_value = $("."+$.escapeSelector($(this).attr("class"))+" option:selected").val();
		$.ajax({
			url: "students.php",
			type: "POST",
			data: { "enroll_project": project_value, "student_email": student_email},
			success: function(response){
				
			}
			
		});
		$("img[class="+$.escapeSelector($(this).attr("class"))+"]").show();
	});
	$("select[name=active]").change(function(e) {
		var student_email = $(this).attr("class").replace('active-*','');
		var active_value = $("."+$.escapeSelector($(this).attr("class"))+" option:selected").val();
		$.ajax({
			url: "students.php",
			type: "POST",
			data: { "active_change": active_value, "student_email": student_email},
			success: function(response){
				
			}
			
		});
		$("img[class="+$.escapeSelector($(this).attr("class"))+"]").show();
	});
	$("select[name=units]").change(function(e) {
		var student_email = $(this).attr("class").replace('units-*','');
		var units_value = $("."+$.escapeSelector($(this).attr("class"))+" option:selected").val();
		$.ajax({
			url: "students.php",
			type: "POST",
			data: { "units_change": units_value, "student_email": student_email},
			success: function(response){

			}
			
		});
		$("img[class="+$.escapeSelector($(this).attr("class"))+"]").show();
	});
	$("#clear_list").click(function(e) {
		if (confirm('Are you sure you want to clear the list of students who changed the # of units? This action cannot be undone.')) {
			$.ajax({
				url: "students.php",
				type: "POST",
				data: { "clear_list": true },
				success: function(response){

				}
			});
			window.location.replace("students.php");
		} else {
			return false;
		}
	});
	$("#download_changed_units_file").click(function(e) {
		$.ajax({
			url: "download_csv.php",
			type: "POST",
			data: { "download_changed_units_file": true },
			success: function(response){
				if (response.substring(0,5) != "false") {
					window.location.replace("download_csv.php");
				} else {
					alert("Download failed! There are no students in this list.");
				}
			}
		});
	});
	$("#download_dclearance_file").click(function(e) {
		$.ajax({
			url: "download_no_dclearance_list.php",
			type: "POST",
			data: { "download_dclearance_file": true },
			success: function(response){
				console.log(response);
				if (response.substring(0,5) != "false") {
					window.location.replace("download_no_dclearance_list.php");
				} else {
					alert("Download failed! All students (USC students and those who are not enrolled as 'Unpaid Intern') have D-Clearance.");
				}
			}
		});
	});
});
function updateFilter(value) {
	if (value == "dclearance") {
		if((window.location.href.indexOf("have_skills=") == -1) || (window.location.href.indexOf("?") == -1)) {
			window.location.replace("students.php?filterBy=dclearance");
		} else {
			window.location.replace(window.location.href+"&filterBy=dclearance");
		}
		$("option[value=dclearance]").attr('selected', true);
	} else if (value == "survey") {
		if((window.location.href.indexOf("have_skills=") == -1) || (window.location.href.indexOf("?") == -1)) {
			window.location.replace("students.php?filterBy=survey");
		} else {
			window.location.replace(window.location.href+"&filterBy=survey");
		}
		
		$("option[value=survey]").attr('selected', true);
	} else if (value == "not_enrolled") {
		if((window.location.href.indexOf("have_skills=") == -1) || (window.location.href.indexOf("?") == -1)) {
			window.location.replace("students.php?filterBy=not_enrolled");
		} else {
			window.location.replace(window.location.href+"&filterBy=not_enrolled");
		}
		$("option[value=not_enrolled]").attr('selected', true);
	} else if (value == "available_students") {
		if((window.location.href.indexOf("have_skills=") == -1) || (window.location.href.indexOf("?") == -1)) {
			window.location.replace("students.php?filterBy=available_students");
		} else {
			window.location.replace(window.location.href+"&filterBy=available_students");
		}
		$("option[value=available_students]").attr('selected', true);
	} else if (value == "not_reviewed_list") {
		if((window.location.href.indexOf("have_skills=") == -1) || (window.location.href.indexOf("?") == -1)) {
			window.location.replace("students.php?filterBy=not_reviewed_list");
		} else {
			window.location.replace(window.location.href+"&filterBy=not_reviewed_list");
		}
		$("option[value=not_reviewed_list]").attr('selected', true);
	} else if (value == "contacted_list") {
		if((window.location.href.indexOf("have_skills=") == -1) || (window.location.href.indexOf("?") == -1)) {
			window.location.replace("students.php?filterBy=contacted_list");
		} else {
			window.location.replace(window.location.href+"&filterBy=contacted_list");
		}
		$("option[value=contacted_list]").attr('selected', true);
	} else if (value == "offer_letter_sent_list") {
		if((window.location.href.indexOf("have_skills=") == -1) || (window.location.href.indexOf("?") == -1)) {
			window.location.replace("students.php?filterBy=offer_letter_sent_list");
		} else {
			window.location.replace(window.location.href+"&filterBy=offer_letter_sent_list");
		}
		$("option[value=contacted_list]").attr('selected', true);
	} else if (value.startsWith("1st_pref")) {
		if((window.location.href.indexOf("have_skills=") == -1) || (window.location.href.indexOf("?") == -1)) {
			window.location.replace("students.php?filterBy="+encodeURI(value));
		} else {
			window.location.replace(window.location.href+"&filterBy="+encodeURI(value));
		}
		$("option[value="+value+"]").attr('selected', true);
	} else if (value.startsWith("2nd_pref")) {
		if((window.location.href.indexOf("have_skills=") == -1) || (window.location.href.indexOf("?") == -1)) {
			window.location.replace("students.php?filterBy="+encodeURI(value));
		} else {
			window.location.replace(window.location.href+"&filterBy="+encodeURI(value));
		}
		$("option[value="+value+"]").attr('selected', true);
	} else {
		if((window.location.href.indexOf("have_skills=") == -1) || (window.location.href.indexOf("?") == -1)) {
			window.location.replace("students.php");
		} else {
			window.location.replace(window.location.href+"&filterBy=all");
		}
		$("option[value=all]").attr('selected', true);		
	}
}
</script>
<?php
	include('footer.php');
	mysqli_close($conn);
?>