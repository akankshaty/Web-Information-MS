<?php
	session_start();
	include('auth.php');
	if (isset($_SESSION['u_name'])) {
		header('Location: profile.php'); // Redirect to user profile page when user is logged-in already
	}
	if (!isset($_GET['access']) && !isset($_GET['registration'])) {
		header('Location: index.php'); // Redirect to home page if URL does not have access code
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
	<?php 
		$access_token_valid = false;
		if (isset($_GET['access'])) {
			$res = mysqli_query($conn,"SELECT * FROM client_invitations WHERE access_token='".$_GET['access']."'");
			$row = mysqli_fetch_assoc($res);
			$f_name = $row['f_name'];
			$l_name = $row['l_name'];
			$email = $row['email'];
			if (!isset($_GET['registration'])) {
				if (mysqli_num_rows($res) > 0) {
					$access_token_valid = true;
					echo '<form id="survey_form" action="" method="POST">
						<h1>DR Course Client Signup Form</h1>
						<table>
							<tr>
								<td><div><p>First Name <span class="font_red">*</span></p><input type="text" name="f_name" disabled value="'.$f_name.'" placeholder="First Name" /></div></td>
								<td><div><p>Last Name <span class="font_red">*</span></p><input type="text" name="l_name" disabled value="'.$l_name.'" placeholder="Last Name" /><div></td>
							</tr>
						</table>
						<div><p>Email Address <span class="font_red">*</span></p><input type="text" name="s_email" disabled value="'.$email.'" placeholder="Email Address" /></div>
						<p>Username <span class="font_red">*</span></p><input type="text" name="u_name" disabled  value="'.$email.'" />

						<table id="last">
							<tr>
								<td><div><p>Password <span class="font_red">*</span></p><input type="password" name="password" placeholder="Password" /></div></td>
								<td><div><p>Re-type Password <span class="font_red">*</span></p><input type="password" name="retype_password" placeholder="Re-type Password" /></div></td>
							</tr>
						</table>

						<input type="submit" name="signup" value="Sign Up" /><br /><br />
						</form>
						</div>
					</div>';
				} else if (!isset($_GET['registration'])){
					echo '<div class="verified" style="padding-bottom:5px;" ><p id ="error_txt"><img src="images/red_cross_mark.png" />Invalid access token</p></div>';
				}				
			} else {
				$res = mysqli_query($conn,"SELECT * FROM client_invitations WHERE access_token='".$_GET['access']."'");
				$row = mysqli_fetch_assoc($res);
				if (mysqli_num_rows($res) > 0) {
					$access_token_valid = true;
				}
			}
		}
	?>
	<?php 
		$_SESSION['form_f_name'] = $f_name; // Give global access inside this page
		$_SESSION['form_l_name'] = $l_name; // Give global access inside this page
		$_SESSION['form_email'] = $email; // Give global access inside this page
		 // Give global access inside this page
		if (isset($_POST['signup'])) {
			$f_name = mysqli_real_escape_string($conn, trim(ucwords(strtolower($f_name)))); // First name
			$l_name = mysqli_real_escape_string($conn, trim(ucwords(strtolower($l_name)))); // Last name
			$email = mysqli_real_escape_string($conn, trim($email)); // Email address
			$u_name = $email; // Set username = email address
			$password = mysqli_real_escape_string($conn, $_POST['password']); // Password
			$hash_password = password_hash($password,PASSWORD_BCRYPT); //Hash Password
			$_SESSION['form_password'] = $hash_password;
			$sql = "SELECT * FROM login_info WHERE username='".$username."'";
			$user_check = mysqli_query($conn,$sql);
			if ($user_check && mysqli_num_rows($user_check) > 0) { // Username already exist
				echo '<script>$(document).ready(function(){$("#last").after("<p id="error_txt">Username already exist. You have probably signed-up before.</p>");$("#error_txt").show();});</script>';
			} else { // Username does not exist, proceed to client registration
				$res = mysqli_query($conn, "INSERT INTO login_info (f_name,l_name,username,password,verified_email,role) VALUES ('".$f_name."','".$l_name."','".$u_name."','".$hash_password."','Yes','Client')");
				if ($res) {
					header("Location: ?access=".$_GET['access']."&registration=success");
				} else {
					header("Location: ?access=".$_GET['access']."&registration=failed");
				}
			}
		}
		if (isset($_GET['registration']) && isset($_GET['access']) && $access_token_valid) {
			if($_GET['registration'] == "success") {
				echo '<form id="survey_form" action="" method="POST">
					<h1>DR Course Client Signup Form</h1>
					<table>
						<tr>
							<td><div><p>First Name <span class="font_red">*</span></p><input type="text" name="f_name" disabled value="'.$_SESSION['form_f_name'].'" placeholder="First Name" /></div></td>
							<td><div><p>Last Name <span class="font_red">*</span></p><input type="text" name="l_name" disabled value="'.$_SESSION['form_l_name'].'" placeholder="Last Name" /><div></td>
						</tr>
					</table>
					<div><p>Email Address <span class="font_red">*</span></p><input type="text" name="s_email" disabled value="'.$_SESSION['form_email'].'" placeholder="Email Address" /></div>
					<p>Username <span class="font_red">*</span></p><input type="text" name="u_name" disabled value="'.$_SESSION['form_email'].'" />

					<table id="last">
						<tr>
							<td><div><p>Password <span class="font_red">*</span></p><input type="password" name="password" value="'.$_SESSION['form_password'].'" placeholder="Password" /></div></td>
							<td><div><p>Re-type Password <span class="font_red">*</span></p><input type="password" name="retype_password" value="'.$_SESSION['form_password'].'" placeholder="Re-type Password" /></div></td>
						</tr>
					</table>
					<p id="success_txt">Registration Successful!</p>
					<input type="submit" name="signup" value="Sign Up" /><br /><br />
					</form>
					</div>
				</div>';
				mysqli_query($conn, "DELETE FROM client_invitations WHERE email='".$email."'"); // Delete client from client_invitations table
				$_SESSION = array();
			} else if ($_GET['registration'] == "failed") {
				echo '<form id="survey_form" action="" method="POST">
					<h1>DR Course Client Signup Form</h1>
					<table>
						<tr>
							<td><div><p>First Name <span class="font_red">*</span></p><input type="text" name="f_name" disabled value="'.$_SESSION['form_f_name'].'" placeholder="First Name" /></div></td>
							<td><div><p>Last Name <span class="font_red">*</span></p><input type="text" name="l_name" disabled value="'.$_SESSION['form_l_name'].'" placeholder="Last Name" /><div></td>
						</tr>
					</table>
					<div><p>Email Address <span class="font_red">*</span></p><input type="text" name="s_email" disabled value="'.$_SESSION['form_email'].'" placeholder="Email Address" /></div>
					<p>Username <span class="font_red">*</span></p><input type="text" name="u_name" disabled value="'.$_SESSION['form_email'].'" />

					<table id="last">
						<tr>
							<td><div><p>Password <span class="font_red">*</span></p><input type="password" name="password" value="'.$_SESSION['form_password'].'" placeholder="Password" /></div></td>
							<td><div><p>Re-type Password <span class="font_red">*</span></p><input type="password" name="retype_password" value="'.$_SESSION['form_password'].'" placeholder="Re-type Password" /></div></td>
						</tr>
					</table>
					<p id="error_txt">Registration Failed! Please try again later.</p>
					<input type="submit" name="signup" value="Sign Up" /><br /><br />
					</form>
					</div>
				</div>';
				$_SESSION = array();
			}
		} else if (!$access_token_valid OR !isset($_GET['access'])) {
			header("Location: index.php");
		}
	?>
</body>
<script>
$(document).ready(function(){
	$("input[name=password]").focus();
	$("#survey_form").submit(function(e) {
		$("#success_txt").hide();
		$("#error_txt").hide();
		if (!$("input[name=password]").val() || !$("input[name=retype_password]").val()) {
			// Check if all fields are entered
			$("#last").after('<p id="error_txt"><span class="font_red">* </span>Please enter all required fields.</p>');
			$("#error_txt").show();
			return false;
		}

		if ($("input[name=password]").val() != $("input[name=retype_password]").val()) {
			$("#last").after('<p id="error_txt">Password mis-match. Please retype the password correctly.</p>');
			$("#error_txt").show();
			return false;
		}
		if ($("input[name=password]").val().length < 8) {
			$("#last").after('<p id="error_txt">Password should have at least 8 characters.</p>');
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
