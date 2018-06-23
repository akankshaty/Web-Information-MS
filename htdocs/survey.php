<?php 
	session_start();
	include('header.php');
	include('auth.php');
	if(!isset($_SESSION['u_name'])) {
		header('Location: index.php');
		exit();
	}
	$res = mysqli_query($conn,"SELECT setting_value FROM settings_option WHERE setting_name ='Filling Survey Form Deadline'");
	if (mysqli_num_rows($res) > 0) { // Deadline for filling the survey form is set
		$unformatted_deadline = mysqli_fetch_assoc($res)['setting_value'];
		$deadline = date("l jS, F Y h:i:s A",$unformatted_deadline);
		$deadline_passed = ((time() - $unformatted_deadline) > 0)? true : false;
	} else { // Deadline for filling the survey form is not set
		$deadline = "No due date set by DR instructor.";
		$deadline_passed = false;
	}
	$admin_or_coordinator_or_client = false; // Variable is true if the user logged-in is Admin, Coordinator or Client
	$admin_or_coordinator = false; // Variable is true if the user logged-in is Admin or Coordinator

	
	if ($_SESSION['u_role'] == "Admin" OR $_SESSION['u_role'] == "Coordinator") {
		$admin_or_coordinator = true; // Variable to allow access to Admin and Coordinator only
		$admin_or_coordinator_or_client = true; // Variable to allow access to Admin, Coordinator and Client
	} else if ($_SESSION['u_role'] == "Client") {
		$admin_or_coordinator_or_client = true; // Variable to allow access to Admin, Coordinator and Client
	}
	if (isset($_GET['student']) && $admin_or_coordinator_or_client) {
		$username = mysqli_real_escape_string($conn,$_GET['student']);
		$res = mysqli_query($conn, "SELECT * FROM login_info WHERE username='".$username."' AND role='Student'");
		if (mysqli_num_rows($res) > 0) { // User exists
			$row = mysqli_fetch_assoc($res);
			$rs = mysqli_query($conn,"SELECT * FROM reviewed_students WHERE client_email='".$_SESSION['u_name']."' AND student_email='".$row['username']."'");
			$mark_value = mysqli_fetch_assoc($rs)['marked_as'];
			if((mysqli_num_rows($rs) > 0) && $mark_value != "Contacted" && $mark_value != "Offer Letter Sent") {
				// Set the "Marked As" section as "Seen" for this student if the user is not already contacted or been sent offer letter by client
				mysqli_query($conn,"UPDATE reviewed_students SET marked_as='Seen' WHERE client_email='".$_SESSION['u_name']."' AND student_email='".$row['username']."'");				
			} else if (mysqli_num_rows($rs) < 1) { // The user has never been reviewed by the client
				mysqli_query($conn,"INSERT INTO reviewed_students (client_email,student_email,marked_as) VALUES ('".$_SESSION['u_name']."','".$row['username']."','Seen')");
			}

			$f_name = ucwords(strtolower($row['f_name'])); // First Name
			$l_name = ucwords(strtolower($row['l_name'])); // Last Name
			$current_student = ($row['current_student'] == "Yes")? true : false; // Current Student?
			$s_id = $row['s_id']; // Student ID
			$email = $row['username'];
			$grad_student = ($row['student_level'] == "Graduate Student")? true : false;
			$d_clearance = ($row['d_clearance'] == "Yes")? true : false;
			$n_units = $row['n_units'];
			$remote = ($row['remote'] == "Yes")? true : false;
		} else {
			header("Location: index.php");
		}
	} else if (isset($_GET['student'])) {
		// Prevent students from viewing other student's survey form
		header("Location: survey.php");
	} else if (!isset($_GET['student']) && $admin_or_coordinator_or_client) { 
		// Redirect Admin/Coordinator/Client when there is no URL params when accessing survey.php page
		header("Location: index.php");
	} else {
		$res = mysqli_query($conn, "SELECT * FROM login_info WHERE username='".$_SESSION['u_name']."' AND role='Student'");
		$row = mysqli_fetch_assoc($res);
		$f_name = ucwords(strtolower($row['f_name'])); // First Name
		$l_name = ucwords(strtolower($row['l_name'])); // Last Name
		$current_student = ($row['current_student'] == "Yes")? true : false; // Current Student?
		$s_id = $row['s_id'];
		$email = $row['username'];
		$grad_student = ($row['student_level'] == "Graduate Student")? true : false;
		$d_clearance = ($row['d_clearance'] == "Yes")? true : false;		
		$n_units = $row['n_units'];
		$remote = ($row['remote'] == "Yes")? true : false;
	}
	if (isset($_POST['submit'])) {
		$res = mysqli_query($conn,"SELECT n_units FROM login_info WHERE username='".$_SESSION['u_name']."'");
		$units = mysqli_fetch_assoc($res)['n_units'];
		if (!empty($units) && ($units != $_POST['n_units'])) { // Send email to admin in case a student changes the registered number of units
			$res = mysqli_query($conn,"SELECT * FROM login_info WHERE role='Admin'");
			$row = mysqli_fetch_assoc($res);
			$admin_email = $row['username']; // Get the email address of the admin
			$admin_f_name = $row['f_name'];
			$admin_l_name = $row['l_name'];
			// Send email to admin regarding change in the number of registered units.
			$to = $admin_email;

			// Subject
			$subject = 'CSCI 590 DR Course - Change in a student\'s registered # of units';

			// Message
			$message = '
			<html>
			<head>
			</head>
			<body>
				<p>Dear '.$admin_f_name.' '.$admin_l_name.',<br />This message is to notify you that the student '.$f_name.' '.$l_name.' has changed the number of units registered for this course. <br />The email address of the student is as follows: '.$email.'</p><br />
			</body>
			</html>
			';

			// To send HTML mail, the Content-type header must be set
			$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-type: text/html; charset=iso-8859-1';
			$headers[] = 'From: <no-reply@'.$url.'>'; // Format of the variable ("From: <no-reply@example.com>")
			// Mail it to admin
			mail($to, $subject, $message, implode("\r\n", $headers));
		}
		// Update the user's survey form info on login_info db table
		mysqli_query($conn,"UPDATE login_info SET n_units='".$_POST['n_units']."', d_clearance='".$_POST['d_clearance']."', remote='".$_POST['rem_student']."' WHERE username='".$_SESSION['u_name']."'");

		// Add the skills in which the student has experience
		$skill_exp = isset($_POST['skill_exp'])? $_POST['skill_exp'] : NULL;
		if (!empty($skill_exp)) { // One or more skills are checked in checkbox
			$res = mysqli_query($conn,"SELECT * FROM student_skills WHERE email='".$_SESSION['u_name']."' AND skill_type='Have Experience'");
			if (mysqli_num_rows($res) > 0) { // Delete all skills the user has saved previously before adding the new skills to the database
				mysqli_query($conn,"DELETE FROM student_skills WHERE email='".$_SESSION['u_name']."'");
			}
			$n_checked = count($skill_exp);
			for($i=0;$i<$n_checked;$i++) {
				// Add student's skill one by one
				mysqli_query($conn,"INSERT INTO student_skills (email,skill_type,skill_name) VALUES ('".$_SESSION['u_name']."','Have Experience','".$skill_exp[$i]."')");
			}
		}
		// Add the skills the student wants to learn
		$skill_wanted = isset($_POST['skill_wanted'])? $_POST['skill_wanted'] : NULL;
		if (!empty($skill_wanted)) { // One or more skills are checked in checkbox
			$res = mysqli_query($conn,"SELECT * FROM student_skills WHERE email='".$_SESSION['u_name']."'");
			if (mysqli_num_rows($res) > 0) { // Delete all skills the user has saved previously before adding the new skills to the database
				mysqli_query($conn,"DELETE FROM student_skills WHERE email='".$_SESSION['u_name']."' AND skill_type='Want to Learn'");
			}
			$n_checked = count($skill_wanted);
			for($i=0;$i<$n_checked;$i++) {
				// Add the skill the student wants to learn from this course row by row
				mysqli_query($conn,"INSERT INTO student_skills (email,skill_type,skill_name) VALUES ('".$_SESSION['u_name']."','Want to Learn','".$skill_wanted[$i]."')");
			}
		}
		// Add the role preferences of student
		
		$res = mysqli_query($conn,"SELECT role_name FROM project_roles");
		$num_roles = mysqli_num_rows($res);
		$res = mysqli_query($conn,"SELECT * FROM role_preferences WHERE email='".$_SESSION['u_name']."'");
		if (mysqli_num_rows($res) > 0) { // Delete all role preferences the user has saved previously before adding the new preferences to the database
			mysqli_query($conn,"DELETE FROM role_preferences WHERE email='".$_SESSION['u_name']."'");
		}
		for($i=1;$i<=$num_roles;$i++) {
			// Add the role preferences of the student row by row
			$role_pref = $_POST['role_pref'.$i];
			$values_arr = explode("-*",$role_pref);
			$role_name = $values_arr[0];
			$preference = $values_arr[1];
			mysqli_query($conn,"INSERT INTO role_preferences (email,preference_order,role_name) VALUES ('".$_SESSION['u_name']."','".$preference."','".$role_name."')");
		}

		// Add the project preferences of student
		
		$res = mysqli_query($conn,"SELECT project_name FROM projects");
		$num_projects = mysqli_num_rows($res);
		$res = mysqli_query($conn,"SELECT * FROM project_preferences WHERE email='".$_SESSION['u_name']."'");
		if (mysqli_num_rows($res) > 0) { // Delete all role preferences the user has saved previously before adding the new preferences to the database
			mysqli_query($conn,"DELETE FROM project_preferences WHERE email='".$_SESSION['u_name']."'");
		}
		for($i=1;$i<=$num_projects;$i++) {
			// Add the project preferences of the student row by row
			$project_pref = $_POST['proj_pref'.$i];
			$values_arr = explode("-*",$project_pref);
			$project_name = $values_arr[0];
			$preference = $values_arr[1];
			mysqli_query($conn,"INSERT INTO project_preferences (email,preference_order,project_name) VALUES ('".$_SESSION['u_name']."','".$preference."','".$project_name."')");
		}
		echo '<script>$(document).ready(function(){$("#last").after("<p id=\"success_txt\">Survey Submission Successful!</p>");});</script>';
		mysqli_query($conn,"UPDATE login_info SET survey='Yes' WHERE username='".$_SESSION['u_name']."'");
	}
	// Check and update whether the resume is uploaded
	if (isset($_POST['update'])) {
		$res = mysqli_query($conn,"SELECT * FROM login_info WHERE username='".$email."'");
		$_SESSION['no_resume'] = empty(mysqli_fetch_assoc($res)['resume_name_on_server'])? 'true' : 'false'; // Check if user has uploaded resume.
		if ($_SESSION['no_resume'] == 'false') {
			echo 'false';
		} else {
			echo 'true';
		}
		
	}
?>
<div class="survey">
	<form id="survey_form" action="" method="POST" enctype="multipart/form-data">
		<div class="survey_deadline"><h1>DR Course Survey Form</h1>
		<div class="font_bold">Deadline to fill or change the survey:</div> <span class="font_red"><?php echo $deadline;?></span></div>
		<table>
			<tr>
				<td><div><p>First Name <span class="font_red">*</span></p><input type="text" name="f_name" value="<?php echo $f_name; ?>" placeholder="First Name" disabled /></div></td>
				<td><div><p>Last Name <span class="font_red">*</span></p><input type="text" name="l_name" value="<?php echo $l_name; ?>" placeholder="Last Name" disabled /><div></td>
			</tr>
		</table>

		<p>Are you a current USC Student? <span class="font_red">*</span></p><br /><input type="radio" id="yes" name="curr_student" disabled <?php if($current_student) {echo 'checked';} ?> />Yes<br />
		<input type="radio" id="no" name="curr_student" disabled <?php if(!$current_student) {echo 'checked';} ?> />No<br /><br />
		<div class = "usc_students_only">
			<table>
				<tr>
					<td><div><p>USC Student ID (XXXX-XX-XXXX) <span class="font_red">*</span></p><input type="text" name="s_id" disabled placeholder="USC Student ID" value="<?php echo $s_id; ?>" /></div></td>
					<td><div><p>Student USC Email Address <span class="font_red">*</span></p><input type="text" name="s_usc_email" disabled placeholder="example@usc.edu" value="<?php echo $email; ?>" /></div></td>
				</tr>
			</table>
				<p>Are you a graduate or undergraduate student? <span class="font_red">*</span></p><br /><input type="radio" id="grad" name="student_level" disabled value="Graduate Student" <?php if($grad_student) {echo 'checked';} ?> />Graduate Student<br />
				<input type="radio" id="undergrad" name="student_level" disabled value="Undergraduate Student" <?php if(!$grad_student) {echo 'checked';} ?> />Undergraduate Student<br /><br />
				<p>Have you received D-Clearance for this course? <span class="font_red">*</span></p><br /><input type="radio" id="d_yes" name="d_clearance" <?php if($deadline_passed) {echo 'disabled';} ?> value="Yes" <?php if($d_clearance) {echo 'checked';} ?> />Yes<br />
				<input type="radio" id="d_no" name="d_clearance" <?php if($deadline_passed) {echo 'disabled';} ?> value="No" <?php if(!$d_clearance) {echo 'checked';} ?> />No<br /><br />
		</div>
		<div id="other_students_only"><p>Email Address <span class="font_red">*</span></p><input type="text" name="s_email" disabled placeholder="Email Address" value="<?php echo $email; ?>" /></div>
		<p>Number of Units <span class="font_red">*</span></p>
		Each unit equals to 5 hours of work per week (for graduate students)<br /><br />
		<select id="units" name="n_units" <?php if($deadline_passed) {echo 'disabled';} ?> >
			<option value="select">Select Units</option>
			<option class="usc_students_only" <?php if($n_units == "1") {echo 'selected';} ?> value="1">1 unit</option>
			<option class="usc_students_only" <?php if($n_units == "2") {echo 'selected';} ?> value="2">2 units</option>
			<option class="usc_students_only" <?php if($n_units == "3") {echo 'selected';} ?> value="3">3 units</option>
			<option class="usc_students_only" <?php if($n_units == "4+") {echo 'selected';} ?> value="4+">4+ units (requires permission from client and coordinators)</option>
			<option value="intern" <?php if(!$current_student OR ($n_units == "intern")) {echo 'selected';} ?> >Unpaid Intern</option>
		</select><br /><br />
		<p>Are you a remote student? <span class="font_red">*</span></p>
		i.e. Live more than 25 miles from USC Main Campus<br /><br />
		<input type="radio" name="rem_student" <?php if($deadline_passed) {echo 'disabled';} ?> value="Yes" <?php if($remote) {echo 'checked';} ?> />Yes<br />
		<input type="radio" name="rem_student" <?php if($deadline_passed) {echo 'disabled';} ?> value="No" <?php if(!$remote) {echo 'checked';} ?> />No<br />
		<?php 
			
			// Skills (with experience) Section
			if ($_SESSION['u_role'] == 'Student') {
				$res = mysqli_query($conn, "SELECT skill_name FROM student_skills WHERE email='".$_SESSION['u_name']."' AND skill_type='Have Experience'");
			} else {
				$res = mysqli_query($conn, "SELECT skill_name FROM student_skills WHERE email='".$_GET['student']."' AND skill_type='Have Experience'");
			}
			
			$skill_exp_arr = array();
			while($row = mysqli_fetch_assoc($res)) {
				$skill_exp_arr[] = $row['skill_name'];
			}
			$res = mysqli_query($conn,"SELECT skill_name FROM skills");
			if (mysqli_num_rows($res) > 0) {
				echo '<br /><p>List of Skills you have</p>';
				echo 'Click skills you have at least one year experience<br /><br />';
			}
			while($row = mysqli_fetch_assoc($res)) {
				$check_it = in_array($row['skill_name'], $skill_exp_arr)? 'checked' : '';
				if($deadline_passed) {
					echo '<input type="checkbox" name="skill_exp[]" value="'.$row['skill_name'].'" '.$check_it.' disabled /> '.$row['skill_name'].'<br />';
				} else {
					echo '<input type="checkbox" name="skill_exp[]" value="'.$row['skill_name'].'" '.$check_it.' /> '.$row['skill_name'].'<br />';
				}
			}
			
			// Skills (want to learn in this course) Section

			if ($_SESSION['u_role'] == 'Student') {			
				$res = mysqli_query($conn, "SELECT skill_name FROM student_skills WHERE email='".$_SESSION['u_name']."' AND skill_type='Want to Learn'");
			} else {
				$res = mysqli_query($conn, "SELECT skill_name FROM student_skills WHERE email='".$_GET['student']."' AND skill_type='Want to Learn'");
			}
			$skill_wanted_arr = array();
			while($row = mysqli_fetch_assoc($res)) {
				$skill_wanted_arr[] = $row['skill_name'];
			}
			$res = mysqli_query($conn,"SELECT skill_name FROM skills");
			if (mysqli_num_rows($res) > 0) {
				echo '<br /><p>List of Skills you want to learn</p>';
				echo 'Click skills you aim to learn from this course<br /><br />';
			}
			while($row = mysqli_fetch_assoc($res)) {
				$check_it = in_array($row['skill_name'], $skill_wanted_arr)? 'checked' : '';
				if($deadline_passed) {
					echo '<input type="checkbox" name="skill_wanted[]" value="'.$row['skill_name'].'" '.$check_it.' disabled /> '.$row['skill_name'].'<br />';
				} else {
					echo '<input type="checkbox" name="skill_wanted[]" value="'.$row['skill_name'].'" '.$check_it.' /> '.$row['skill_name'].'<br />';
				}
			}
			
			// Roles Section
			
			if ($_SESSION['u_role'] == 'Student') {				
				$res = mysqli_query($conn,"SELECT rp.preference_order,pr.role_name FROM project_roles pr LEFT JOIN role_preferences rp ON pr.role_name = rp.role_name WHERE rp.email='".$_SESSION['u_name']."' ORDER BY pr.role_name ASC");
			} else {
				$res = mysqli_query($conn,"SELECT rp.preference_order,pr.role_name FROM project_roles pr LEFT JOIN role_preferences rp ON pr.role_name = rp.role_name WHERE rp.email='".$_GET['student']."' ORDER BY pr.role_name ASC");				
			}
			$num_res = mysqli_query($conn,"SELECT * FROM project_roles");
			$num_roles = mysqli_num_rows($num_res);
			if ($num_roles > 0) { // If there at least one role is listed, if not then the roles section won't appear on survey
					echo '<br /><p>Roles <span class="font_red">*</span></p>';
					echo 'Please click in order of preference (1 most preferred - '.$num_roles.' least preferred)<br /><br />';

				echo '<table class="entries">';
					echo '<tr>';
					echo '<th>Role</th>';
					for($i=1;$i<=$num_roles;$i++) {
						echo '<th>'.$i.'</th>';
					}
					echo '</tr>';
				if (mysqli_num_rows($res) > 0) { // If survey has already been filled by student
					$count = 1;
					while($row = mysqli_fetch_assoc($res)) {
						$pref = $row['preference_order'];
						if($deadline_passed) {
							echo '<tr>';
							echo '<td>'.$row['role_name'].'</td>';
							for($i=1;$i<=$num_roles;$i++) {
								$check_it = ($pref == strval($i)? 'checked' : '');
								echo '<td><input type="radio" id=u'.$count.$i.' class="role_name'.$count.'" name="role_pref'.$i.'" value="'.$row['role_name'].'-*'.$i.'" '.$check_it.' disabled /></td>';
							}
							echo '</tr>';
						} else {
							echo '<tr>';
							echo '<td>'.$row['role_name'].'</td>';
							for($i=1;$i<=$num_roles;$i++) {
								$check_it = ($pref == strval($i)? 'checked' : '');
								echo '<td><input type="radio" id=u'.$count.$i.' class="role_name'.$count.'" name="role_pref'.$i.'" value="'.$row['role_name'].'-*'.$i.'" '.$check_it.' /></td>';
							}
							echo '</tr>';
						}
						$count++;	
					}
				} else { // The student is filling the survey for the first time
					$empty_fields = mysqli_query($conn,"SELECT * FROM project_roles");
					$count = 1;
					while($row = mysqli_fetch_assoc($empty_fields)) {
						if($deadline_passed) {
							echo '<tr>';
							echo '<td>'.$row['role_name'].'</td>';
							for($i=1;$i<=$num_roles;$i++) {
								echo '<td><input type="radio" id=u'.$count.$i.' class="role_name'.$count.'" name="role_pref'.$i.'" value="'.$row['role_name'].'-*'.$i.'" disabled /></td>';
							}
							echo '</tr>';
						} else {
							echo '<tr>';
							echo '<td>'.$row['role_name'].'</td>';
							for($i=1;$i<=$num_roles;$i++) {
								echo '<td><input type="radio" id=u'.$count.$i.' class="role_name'.$count.'" name="role_pref'.$i.'" value="'.$row['role_name'].'-*'.$i.'" /></td>';
							}
							echo '</tr>';
						}
						$count++;							
					}

				}
				echo '</table>';
			}
			// Projects Section
			
			if ($_SESSION['u_role'] == 'Student') {				
				$res = mysqli_query($conn,"SELECT pp.preference_order,p.project_name FROM projects p LEFT JOIN project_preferences pp ON p.project_name = pp.project_name WHERE pp.email='".$_SESSION['u_name']."' ORDER BY p.project_name ASC");
			} else {
				$res = mysqli_query($conn,"SELECT pp.preference_order,p.project_name FROM projects p LEFT JOIN project_preferences pp ON p.project_name = pp.project_name WHERE pp.email='".$_GET['student']."' ORDER BY p.project_name ASC");
			}
			$num_res = mysqli_query($conn,"SELECT * FROM projects");
			$num_projects = mysqli_num_rows($num_res);
			if ($num_projects > 0) { // If there at least one project is listed, if not then the project section won't appear on survey
					echo '<br /><p>Projects <span class="font_red">*</span></p>';
					echo 'Please click in order of preference (1 most preferred - '.$num_projects.' least preferred)<br /><br />';
				
				echo '<table class="entries">';
					echo '<tr>';
					echo '<th>Project Name</th>';
					for($i=1;$i<=$num_projects;$i++) {
						echo '<th>'.$i.'</th>';
					}
					echo '</tr>';
				if (mysqli_num_rows($res) > 0) {
					$count = 1;
					while($row = mysqli_fetch_assoc($res)) {
						$pref = $row['preference_order'];
						if($deadline_passed) {
							echo '<tr>';
							echo '<td>'.$row['project_name'].'</td>';
							for($i=1;$i<=$num_projects;$i++) {
								$check_it = ($pref == strval($i)? 'checked' : '');
								echo '<td><input type="radio" id=u'.$count.'_'.$i.' class="project_name'.$count.'" name="proj_pref'.$i.'" value="'.$row['project_name'].'-*'.$i.'" '.$check_it.' disabled /></td>';
							}
							echo '</tr>';
						} else {
							echo '<tr>';
							echo '<td>'.$row['project_name'].'</td>';
							$pref = $row['preference_order'];
							for($i=1;$i<=$num_projects;$i++) {
								$check_it = ($pref == strval($i)? 'checked' : '');
								echo '<td><input type="radio" id=u'.$count.'_'.$i.' class="project_name'.$count.'" name="proj_pref'.$i.'" value="'.$row['project_name'].'-*'.$i.'" '.$check_it.' /></td>';
							}
							echo '</tr>';
						}
						$count++;
					}					
				} else {
					$empty_fields = mysqli_query($conn,"SELECT * FROM projects");
					$count = 1;
					while($row = mysqli_fetch_assoc($empty_fields)) {
						if($deadline_passed) {
							echo '<tr>';
							echo '<td>'.$row['project_name'].'</td>';
							for($i=1;$i<=$num_projects;$i++) {
								echo '<td><input type="radio" id=u'.$count.'_'.$i.' class="project_name'.$count.'" name="proj_pref'.$i.'" value="'.$row['project_name'].'-*'.$i.'" disabled /></td>';
							}
							echo '</tr>';
						} else {
							echo '<tr>';
							echo '<td>'.$row['project_name'].'</td>';
							for($i=1;$i<=$num_projects;$i++) {
								echo '<td><input type="radio" id=u'.$count.'_'.$i.' class="project_name'.$count.'" name="proj_pref'.$i.'" value="'.$row['project_name'].'-*'.$i.'" /></td>';
							}
							echo '</tr>';
						}
						$count++;
					}
				}

				echo '</table>';
			}
		?>
		<br />
		
		<div id="last"><p>Resume Upload <span class="font_red">*</span></p>Select file to upload (only .pdf files are allowed): <br />
		<?php 
		if (isset($_GET['student']) && $admin_or_coordinator_or_client) {
			echo '<iframe src="resume_upload_iframe.php?student='.$_GET['student'].'" style="border:none;min-width:600px;min-height:160px;"></iframe>';			
		} else {
			echo '<iframe src="resume_upload_iframe.php" style="border:none;min-width:600px;min-height:160px;"></iframe>';
		}

		?>
		</div>
		<p id="error_txt" style="display:none;"><span class="font_red">* </span>Please enter all required fields.</p>
		<p id="success_txt" style="display:none;">Survey Submission Successful!</p>		
		<input style="margin-top:0;" type="submit" name="submit" <?php if($deadline_passed || $_SESSION['u_role'] != "Student") {echo 'disabled';} ?> value="Submit" />
	</form>
</div>
<script>
$("#other_students_only").hide();
<?php echo 'var num_roles = '.$num_roles.';'; 
	echo 'var num_projects = '.$num_projects.';'; 
	$res = mysqli_query($conn,"SELECT * FROM login_info WHERE username='".$email."'");
	$row = mysqli_fetch_assoc($res);
	if (empty($row['resume_name_on_server'])) {
		$_SESSION['no_resume'] = 'true';
	} else {
		$_SESSION['no_resume'] = 'false';
	}
	echo 'var no_resume = '.$_SESSION['no_resume'].';';

	?>
$(document).ready(function(){
	if ($("#yes").is(':checked')) { // If current student, show form elements only for current students
		$(".usc_students_only").show();
		$("#other_students_only").hide();	
	} else if ($("#no").is(':checked')) {
		$(".usc_students_only").hide();
		$("#other_students_only").show();
	}
	$("input:radio").on('click', function() { // Checking validity of radio buttons clicked on Roles and projects section of survey
	  var $box = $(this);
	  if ($box.is(":checked")) {
		var group = "input:radio[class='" + $box.attr("class") + "']";
		$(group).prop("checked", false);
		$box.prop("checked", true);
	  } else {
		$box.prop("checked", false);
	  }
	});
	$("#survey_form").submit(function(e) {
		$("#error_txt").hide();
		$("#success_txt").hide();
	   $.ajax({
		  type: "POST",
		  url: "survey.php",
		  data: {"update" : 'true'},
		  success: function(data){
			  if (data == 'true') {
				  $("#error_txt").text("* Please upload your resume!");
				  $("#error_txt").show();
				  no_resume = true;
				  return false;
			  } else {
				  no_resume = false;
			  }
		  }
	   });
		if ($("#units").find('option:selected').val() == "select") {
			// Check if all fields are entered
			$("#success_txt").remove();
			$("#error_txt").remove();
			$("#last").after('<p id="error_txt"><span class="font_red">* </span>Please enter all required fields.</p>');
			return false;
		}
		if (no_resume) {
			// Check if all fields are entered
			$("#success_txt").remove();
			$("#error_txt").remove();
			$("#last").after('<p id="error_txt"><span class="font_red">* </span>Please upload your resume.</p>');
			return false;
		}
		for(var i=1;i<=num_roles;i++) {
			if ($("input[name=role_pref"+i+"]:checked").length == 0) {
				$("#success_txt").remove();
				$("#error_txt").remove();
				$("#last").after('<p id="error_txt"><span class="font_red">* </span>Please rank all the role preferences.</p>');
				return false;
			}
		}
		for(var i=1;i<=num_projects;i++) {
			if ($("input[name=proj_pref"+i+"]:checked").length == 0) {
				$("#success_txt").remove();
				$("#error_txt").remove();
				$("#last").after('<p id="error_txt"><span class="font_red">* </span>Please rank all the project preferences.</p>');
				return false;
			}
		}
	});
});
</script>
<?php
	include('footer.php');
	mysqli_close($conn);
?>