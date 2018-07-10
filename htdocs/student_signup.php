<?php
	session_start();
	include('auth.php');
	if (isset($_SESSION['u_name'])) {
		header('Location: profile.php'); // Redirect to user profile page when user is logged-in already
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="dr.css" />
<title>DR Profile</title>
</head>
<body>
	<div class="top_heading">
		<img id="usc_logo" src="images/usc_logo.png" />
	</div>
	<div class="container">
		<div class="registration">
			<div id = "login_logo">
			<img src="images/viterbi_logo.png" />
			</div>
		<form id="survey_form" method="POST">
			<h1>DR Course Signup Form</h1>
			<table>
				<tr>
					<td><div><p>First Name <span class="font_red">*</span></p><input type="text" name="f_name" placeholder="First Name" /></div></td>
					<td><div><p>Last Name <span class="font_red">*</span></p><input type="text" name="l_name" placeholder="Last Name" /><div></td>
				</tr>
			</table>
			<p>Are you a current USC Student? <span class="font_red">*</span></p><br /><input type="radio" id="yes" name="curr_student" value="Yes" checked />Yes<br />
			<input type="radio" id="no" name="curr_student" value="No" />No<br /><br />
			<div  class="usc_students_only">
				<table>
					<tr>
						<td><div><p>USC Student ID (XXXX-XX-XXXX) <span class="font_red">*</span></p><input type="text" name="s_id" placeholder="USC Student ID" /></div></td>
						<td><div><p>Student USC Email Address <span class="font_red">*</span></p><input type="text" name="s_usc_email" placeholder="example@usc.edu" /></div></td>
					</tr>
				</table>
				<p>Are you a graduate or undergraduate student? <span class="font_red">*</span></p><br /><input type="radio" id="grad" name="student_level" value="Graduate Student" checked />Graduate Student<br />
				<input type="radio" id="undergrad" name="student_level" value="Undergraduate Student" />Undergraduate Student<br /><br />
				<p>Have you received D-Clearance for this course? <span class="font_red">*</span></p><br /><input type="radio" id="d_yes" name="d_clearance" value="Yes" />Yes<br />
				<input type="radio" id="d_no" name="d_clearance" value="No" checked />No<br /><br />	
			</div>
			<div id="other_students_only"><p>Email Address <span class="font_red">*</span></p><input type="text" name="s_email" placeholder="Email Address" /></div>
			<p>Username <span class="font_red">*</span></p><input type="text" name="u_name" disabled />

			<table>
				<tr>
					<td><div><p>Password <span class="font_red">*</span></p><input type="password" name="password" placeholder="Password" /></div></td>
					<td><div><p>Re-type Password <span class="font_red">*</span></p><input type="password" name="retype_password" placeholder="Re-type Password" /></div></td>
				</tr>
			</table>
			<p id="error_txt"><span class="font_red">* </span>Please enter all required fields.</p>
			<p id="success_txt">Registration Successful! A verification email has been sent to you!</p>

			<input type="submit" name="signup" value="Sign Up" />
			<input type="button" name="login" value="Go back to login" onclick="window.location = 'index.php';"/><br /><br />
		</form>
		</div>
	</div>
	<?php 
		if (isset($_POST['signup'])) {
			$f_name = mysqli_real_escape_string($conn, trim(ucwords(strtolower($_POST['f_name'])))); // First name
			$l_name = mysqli_real_escape_string($conn, trim(ucwords(strtolower($_POST['l_name'])))); // Last name
			if ($_POST['curr_student'] == "Yes") {
				$email = mysqli_real_escape_string($conn, trim($_POST['s_usc_email'])); // Set USC Email address for USC students
			} else {
				$email = mysqli_real_escape_string($conn, trim($_POST['s_email'])); // Set Email address for non-USC students
			}
			$u_name = $email; // Set username = email address
			$password = mysqli_real_escape_string($conn, $_POST['password']); // Password
			$hash_password = password_hash($password,PASSWORD_BCRYPT);
			$s_id = isset($_POST['s_id'])? mysqli_real_escape_string($conn, trim($_POST['s_id'])) : NULL; // USC Student ID
			$curr_student = mysqli_real_escape_string($conn, $_POST['curr_student']);
			$student_level = isset($_POST['student_level'])? mysqli_real_escape_string($conn, $_POST['student_level']) : mysqli_real_escape_string($conn, "Non-USC student");
			$d_clearance = isset($_POST['d_clearance'])? mysqli_real_escape_string($conn, $_POST['d_clearance']) : "No";
			$sql = "SELECT * FROM login_info WHERE username='".$username."'";
			$user_check = mysqli_query($conn,$sql);
			if ($user_check && mysqli_num_rows($user_check) > 0) { // Username already exist
				echo '<script>$(document).ready(function(){$("#error_txt").text("Username already exist. You have probably signed-up before.");$("#error_txt").show();});</script>';
			} else { // Username does not exist, proceed to send email verification
				$res = mysqli_query($conn, "INSERT INTO login_info (f_name,l_name,username,password,s_id,current_student,student_level,survey,d_clearance,status,verified_email,role) VALUES ('".$f_name."','".$l_name."','".$u_name."','".$hash_password."','".$s_id."','".$curr_student."','".$student_level."','No','".$d_clearance."','Active','No','Student')");
				if ($res) {
					echo '<script>$(document).ready(function(){$("#success_txt").text("Registration Successful! A verification email has been sent to you!");$("#success_txt").show();});</script>';
				} else {
					echo '<script>$(document).ready(function(){$("#error_txt").text("Error! Registration Failed! Please try again later.");$("#error_txt").show();});</script>';
				}
				do { // Loop until the created random string is unique (random string which is not found in database already)
					$string_not_unique = true; 
					$random_string = "";
					$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
					for($i=0;$i<=6;$i++) {
						$random_string.=substr($chars,rand(0,strlen($chars)),1); // Generating a random string as a form of access code to verify email
					}
					$res = mysqli_query($conn,"SELECT * FROM email_verifications WHERE access_token='".$random_string."'");
					if (mysqli_num_rows($res) < 1) { // Generated string is unique
						$string_not_unique = false; 
					}
				} while($string_not_unique);
				
				$res = mysqli_query($conn,"INSERT INTO email_verifications (f_name,l_name,email,access_token) VALUES ('".$f_name."','".$l_name."','".$email."','".$random_string."')");
				$url = parse_url((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_HOST); // Parses the domain of the DR Website
				// Email for student's email upon sign-up
				$to = $email;

				// Subject
				$subject = 'CS590 DR Course Email Verification';

				// Message
				$message = '
				<html>
				<head>
				</head>
				<body>
					<p>Dear '.$f_name.' '.$l_name.',<br /><h2>DR course website Email Verification</h2>Please click the link to complete the sign-up process.</p><br />
					<a href="'.$url.'/verify.php?access='.$random_string.'"></a>
				</body>
				</html>
				';

				// To send HTML mail, the Content-type header must be set
				$headers[] = 'MIME-Version: 1.0';
				$headers[] = 'Content-type: text/html; charset=iso-8859-1';
				$headers[] = 'From: '.$f_name.' '.$l_name.' <'.$email.'>'; // Format of the variable ("From: First-Name Last-Name <example@example.com>")
				// Mail it to student
				mail($to, $subject, $message, implode("\r\n", $headers));

				
			}
		}
	?>
</body>
<script>
$("#error_txt").hide();
$("input[name=curr_student]").val('Yes');
$("#success_txt").hide();
$("#other_students_only").hide();
$(document).ready(function(){
	$("input[name=f_name]").focus();
	$("#yes").on("click", function() {
		$("input[name=curr_student]").val('Yes');
		$(".usc_students_only").show();
		$("#other_students_only").hide();
		$("#other_students_only input[name=s_email]").val('');
		$(".usc_students_only #grad").prop('checked', true);
		$(".usc_students_only #d_no").prop('checked', true);
		$("input[name=u_name]").val('');
	});
	$("#no").on("click", function() {
		$("input[name=curr_student]").val('No');
		$(".usc_students_only").hide();
		$(".usc_students_only input[name=s_id]").val('');
		$(".usc_students_only input[name=s_usc_email]").val('');
		$(".usc_students_only input[name=student_level]").prop('checked', false);
		$(".usc_students_only input[name=d_clearance]").prop('checked', false);
		$("#other_students_only").show();
		$("input[name=u_name]").val('');
	});
	$(".usc_students_only input[name=s_usc_email]").on("keyup", function() {
		var value = $(".usc_students_only input[name=s_usc_email]").val();
		$("input[name=u_name]").val(value);
	});
	$("#other_students_only input[name=s_email]").on("keyup", function() {
		var value = $("#other_students_only input[name=s_email]").val();
		$("input[name=u_name]").val(value);
	});
	$("#survey_form").submit(function(e) {
		$("#error_txt").hide();
		$("#success_txt").hide();
		if (!$("input[name=f_name]").val() || !$("input[name=l_name]").val() || (!$("input[name=s_email]").val() && !$("input[name=s_usc_email]").val()) || !$("input[name=curr_student]").val() || !$("input[name=password]").val() || !$("input[name=retype_password]").val()) {
			// Check if all fields are entered
			$("#error_txt").html('<p id="error_txt"><span class="font_red">* </span>Please enter all required fields.</p>');
			$("#error_txt").show();
			return false;
		}
		if (($("input[name=curr_student]").val() == "Yes") && !$("input[name=s_id]").val()) {
			// Check if Student ID is entered for current students
			$("#error_txt").html('<p id="error_txt"><span class="font_red">* </span>Please enter all required fields.</p>');
			$("#error_txt").show();
			return false;
		} 
		if (!$("input[name=s_email]").val() && !($("input[name=s_usc_email]").val().indexOf('@') > -1)) {
			$("#error_txt").text("Invalid email address.");
			$("#error_txt").show();
			return false;
		} 
		if (!$("input[name=s_usc_email]").val() && !($("input[name=s_email]").val().indexOf('@') > -1)) {
			$("#error_txt").text("Invalid email address.");
			$("#error_txt").show();
			return false;
		}
		if ($("#yes").is(":checked") && !$("input[name=s_usc_email]").val().match(/\usc\.edu$/)) {
			$("#error_txt").text("Invalid USC email address.");
			$("#error_txt").show();
			return false;
		}
		if ($("#yes").is(":checked") && !$("input[name=s_id]").val().match(/^[0-9]{4}-[0-9]{2}-[0-9]{4}$/) && !$("input[name=s_id]").val().match(/^[0-9]{10}$/)) {
			$("#error_txt").text("Invalid USC Student ID. Follow XXXX-XX-XXXX 10 digit format.");
			$("#error_txt").show();
			return false;
		} else if ($("input[name=s_id]").val().match(/^[0-9]{10}$/)) {
			var formatted = $("input[name=s_id]").slice(0,4)+"-"+$("input[name=s_id]").slice(4,6)+"-"+$("input[name=s_id]").slice(6);
			$("input[name=s_id]").val(formatted);
		}
		if ($("input[name=password]").val() != $("input[name=retype_password]").val()) {
			$("#error_txt").text("Password mis-match. Please retype the password correctly.");
			$("#error_txt").show();
			return false;
		}
		if ($("input[name=password]").val().length < 8) {
			$("#error_txt").html("Password should have at least 8 characters.");
			$("#error_txt").show();
			return false;
		}
	});
});
</script>
</html>
<?php 
	mysqli_close($conn);
?>
