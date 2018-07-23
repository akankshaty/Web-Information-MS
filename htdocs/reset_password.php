<?php 
	session_start();
	include('auth.php');
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

			<?php
				if (isset($_GET['access'])) {
					$all_query_ok = true; // Set to false if one of the command fails
					$res = mysqli_query($conn, "SELECT * FROM password_requests WHERE access_token='".$_GET['access']."'");
					if (mysqli_num_rows($res) < 1) { // Access token is invalid
						echo '<div class="verified"><div id="error_txt"><img src="images/red_cross_mark.png" />Invalid access token ID! Heading back to homepage...</div></div>';
						echo "<meta http-equiv='refresh' content='5;url=index.php'>"; // Go to login page in 5 seconds.
					} else {
						echo '<div class="login">
									<div id = "login_logo">
										<img src="images/viterbi_logo.png" />
									</div>
									<form id="password_reset_form" action="" method="POST">
								<input type="password" name="new_password" placeholder="New password" class="password" /><br /><br />
								<input type="password" name="retype_password" placeholder="Re-type password" class="password" /><br /><br />
								<div class="error"><p id="error_txt"></p></div>
								<input type="submit" name="reset_password" value="Reset Password" /><br /><br />
							</form></div>';
					}
				} else { // User on reset password page without any access token will be sent back to login page immediately
					header("Location: index.php");
				}

			?>
<?php
	if(isset($_POST['reset_password'])) {
		if (isset($_GET['access'])) {
			$all_query_ok = true; // Set to false if one of the command fails
			$res = mysqli_query($conn, "SELECT * FROM password_requests WHERE access_token='".$_GET['access']."'");
			if (mysqli_num_rows($res) > 0) { // Check if access token is valid
				$row = mysqli_fetch_assoc($res);
				$get_user = mysqli_query($conn, "SELECT * FROM login_info WHERE username='".$row['email']."'");
				$row = mysqli_fetch_assoc($get_user);
				
				$username = $row['username'];
				$password = $_POST['new_password'];
				$hash_password = password_hash($password,PASSWORD_BCRYPT);
				
				mysqli_autocommit($conn, FALSE); // Disable auto-commit. 
				mysqli_query($conn,"UPDATE login_info SET password='".$hash_password."' WHERE username='".$username."'")? NULL : $all_query_ok = false;
				mysqli_query($conn,"DELETE FROM password_requests WHERE email='".$row['username']."'")? NULL : $all_query_ok = false;
				$all_query_ok? mysqli_commit($conn) : mysqli_rollback($conn); // Rollback if one of the two commands fail
				mysqli_autocommit($conn, TRUE); // Re-enable auto-commit. 
				if ($all_query_ok) {
					echo '<script>$(".login").hide();</script><div class="verified"><div id="success_txt"><img src="images/green_check_mark.png" />Your password has been successfully reset!</div></div>';
					echo '<form action="index.php" method="POST" ><input style="margin-left: 46%;" type="submit" name="signin" value="Sign In" /></form>';
				} else {
					echo '<script>$(".login").hide();</script><div class="verified"><div id="error_txt"><img src="images/red_cross_mark.png" />Your password could not be reset! Please try again later.</div></div>';
					echo '<form action="index.php" method="POST" ><input style="margin-left: 46%;" type="submit" name="signin" value="Sign In" /></form>';
				}
			}
		}
	}

?>
	</div>
<script>
$(".error").hide();
$(document).ready(function(){
	$("#password_reset_form").submit(function(e) {
		if ($("input[name=new_password]").val() != $("input[name=retype_password]").val()) {
			$("#error_txt").text("Password mis-match. Please retype the password correctly.");
			$(".error").show();
			return false;
		}
		if ($("input[name=new_password]").val().length < 8) {
			$("#error_txt").text("Password should have at least 8 characters.");
			$(".error").show();
			return false;
		}
	});
});
</script>
<?php
	include('footer.php');
	mysqli_close($conn);
?>