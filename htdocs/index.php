<?php
	session_start();
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
<link rel="stylesheet" type="text/css" href="dr.css" />
<title>DR Profile</title>
</head>
<body>
	<div class="top_heading">
		<img id="usc_logo" src="images/usc_logo.png" />
	</div>
	<div class="container">
		<div class="login">
			<div id = "login_logo">
			<img src="images/viterbi_logo.png" />
			</div>
			<?php
			if (isset($_GET['forgot_password']) && $_GET['forgot_password'] == "true") {
				echo '<form id="forgot_password_form" action="" method="POST">
						<h2>Forgot Password</h2>
						<input type="text" name="email" placeholder="Email Address" class="email" /><br /><br />
						<div class="error"><p id="error_txt"></p></div>
						<div class="email_sent"><div id="success_txt"><img src="images/green_check_mark.png" />Email sent.</div></div>
						<input type="submit" name="submit" value="Submit" />
						<p><a href="index.php">Back to Sign-in</a></p>
					</form>';
			}
			else {
				$typed_u_name = isset($_POST['username']) ? 'value="'.$_POST['username'].'"' : "";
				echo '<form id="signin_form" action="" method="POST">
						<input type="text" name="username" placeholder="Username" '.$typed_u_name.' class="username" /><br /><br />
						<input type="password" name="password" placeholder="Password" class="password" /><br /><br />
						<div class="error"><p id="error_txt"></p></div>
						<input type="submit" name="signin" value="Sign In" />
						<p id="forgot_password"><a href="?forgot_password=true">Forgot your password?</a></p>
						<p><a href="student_signup.php">Student Sign-up</a></p>
					</form>';
			}
			?>
		</div>
	</div>
	<?php 
		include('auth.php');
		if (isset($_POST['signin']) && isset($_POST['username'])) {
			$username = mysqli_real_escape_string($conn, $_POST['username']);
			$password = mysqli_real_escape_string($conn, $_POST['password']);
			//$result = mysqli_query($conn,"SELECT * FROM login_info WHERE username='".$username."' AND password=".$password);
			$user_check = mysqli_query($conn,"SELECT * FROM login_info WHERE username='".$username."'");
			$row = mysqli_fetch_assoc($user_check);
			$verified_email = ($row['verified_email'] == "Yes")? true : false;
			$verified_password = password_verify($password,$row['password']);
			if ($verified_email && $verified_password && (mysqli_num_rows($user_check) > 0)) { // Login successful
				
				$_SESSION['f_name'] = ucwords(strtolower($row['f_name']));
				$_SESSION['l_name'] = ucwords(strtolower($row['l_name']));
				$_SESSION['u_name'] = $username;
				$_SESSION['u_pwd'] = $password;
				$_SESSION['u_role'] = $row['role'];
				header("Location: profile.php");
				exit();
			} else if ($verified_email && mysqli_num_rows($user_check) > 0 && !$verified_password) { // Username exist but password is incorrect
				echo '<script>$(document).ready(function(){$("#error_txt").text("Wrong username(email) and/or password.");$(".error").show();});</script>';
			} else if (!$verified_email && mysqli_num_rows($user_check) > 0) { // Email not verified and use exist
				echo '<script>$(document).ready(function(){$("#error_txt").text("Please verify your email to sign-in.");$(".error").show();});</script>';
			} else { // Username does not exist
				echo '<script>$(document).ready(function(){$("#error_txt").text("Username(email) does not exist.");$(".error").show();});</script>';
			}
		}
		if (isset($_POST['submit'])) {
			$email = mysqli_real_escape_string($conn, $_POST['email']);
			$result = mysqli_query($conn,"SELECT * FROM login_info WHERE username='".$email."'");
			$row = mysqli_fetch_assoc($result);
			$f_name = $row['f_name'];
			$l_name = $row['l_name'];
			if (mysqli_num_rows($result) > 0) {
				$string_not_unique = true;
				do { // Loop until the created random string is unique (random string which is not found in database already)
					$random_string = "";
					$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
					for($i=0;$i<=6;$i++) {
						$random_string.=substr($chars,rand(0,strlen($chars)),1); // Generating a random string
					}
					$res = mysqli_query($conn,"SELECT * FROM password_requests WHERE access_token='".$random_string."'");
					if (mysqli_num_rows($res) < 1) { // Generated string is unique
						$string_not_unique = false; 
					}
				} while($string_not_unique);
				// Insert the values into password_requests table
				mysqli_query($conn,"INSERT INTO password_requests (email,access_token) VALUES ('".$email."','".$random_string."')");
				$url = parse_url((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_HOST); // Parses the domain of the DR Website
				$curr_path = dirname((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
				// Email for resetting password
				$to = $email;

				// Subject
				$subject = 'Reset Password CSCI 590 website';

				// Message
				$message = '
				<html>
				<head>
				</head>
				<body>
					<p>Dear '.$f_name.',<br />A password request for this email address was made from the system. Please follow the link below to reset the password. <br /><strong>Password Reset Link: </strong><a href="'.$curr_path.'/reset_password.php?access='.$random_string.'">'.$curr_path.'/reset_password.php?access='.$random_string.'</a></p><br />
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
				echo '<script>$(document).ready(function(){$(".email_sent").show();});</script>';
			} else {
				echo '<script>$(document).ready(function(){$("#error_txt").text("Email does not exist.");$(".error").show();});</script>';
			}
		}
		
		
		mysqli_close($conn);
	?>
</body>
<script>
$(".email_sent").hide();
$(".error").hide();
$(document).ready(function(){
	$("input[name=username]").focus();
	$("input[name=email]").focus();
	$("#signin_form").submit(function(e) {
		if (!$("input[name=username]").val() && !$("input[name=password]").val()) {
			$("#error_txt").text("Please enter a username(email) and password.");
			$(".error").show();
			return false;
		} else if (!$("input[name=username]").val()) {
			$("#error_txt").text("Please enter a username(email).");
			$(".error").show();
			return false;
		} else if (!$("input[name=password]").val()) {
			$("#error_txt").text("Please enter a password.");
			$(".error").show();
			return false;
		} else if (!($("input[name=username]").val().indexOf('@') > -1) && $("input[name=username]").val() != "admin" && $("input[name=username]").val() != "test" && $("input[name=username]").val() != "vipin") {
			$("#error_txt").text("Username should be an email address.");
			$(".error").show();
			return false;
		}
	});
	$("#forgot_password").on("click", function(e) {
		$(".error").hide();
	$("#back_to_signin").on("click", function(e) {
		$("#signin_form").show();
		$("#forgot_password_form").hide();
	});
	});
	
});
</script>
</html>
