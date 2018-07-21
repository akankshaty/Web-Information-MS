<?php 
	session_start();
	include('header.php');
	include('auth.php');
?>
<?php
	if (isset($_GET['access'])) {
		$all_query_ok = true; // Set to false if one of the command fails
		$res = mysqli_query($conn, "SELECT * FROM email_verifications WHERE access_token='".$_GET['access']."'");
		if (mysqli_num_rows($res) > 0) { // Check if access token is valid
			$row = mysqli_fetch_assoc($res);
			$get_user = mysqli_query($conn, "SELECT * FROM login_info WHERE username='".$row['email']."'");
			
			$row = mysqli_fetch_assoc($get_user);
			$f_name = ucwords(strtolower($row['f_name']));
			$l_name = ucwords(strtolower($row['l_name']));
			$username = $row['username'];
			$password = $row['password'];
			$role = $row['role'];
			mysqli_autocommit($conn, FALSE); // Disable auto-commit. 
			mysqli_query($conn,"UPDATE login_info SET verified_email='Yes' WHERE username='".$username."'")? NULL : $all_query_ok = false;
			mysqli_query($conn,"DELETE FROM email_verifications WHERE email='".$row['username']."'")? NULL : $all_query_ok = false;
			$all_query_ok? mysqli_commit($conn) : mysqli_rollback($conn); // Rollback if one of the two commands fail
			mysqli_autocommit($conn, TRUE); // Re-enable auto-commit. 
			if ($all_query_ok) {
				echo '<div class="verified"><div id="success_txt"><img src="images/green_check_mark.png" />Your email has been verified! Don\'t forget to fill the survey!</div></div>';
				$_SESSION['f_name'] = $f_name;
				$_SESSION['l_name'] = $l_name;
				$_SESSION['u_name'] = $username;
				$_SESSION['u_pwd'] = $password;
				$_SESSION['u_role'] = $role;
				echo '<form action="survey.php" method="POST" ><input style="margin-left: 46%;" type="submit" name="fill_survey" value="Fill Survey" /></form>';
			} else {
				echo '<div class="verified"><div id="error_txt">Your email could not be verified! Please try again later.</div></div>';
			}
			
		} else {
			echo '<div class="verified"><div id="error_txt"><img src="images/red_cross_mark.png" />Invalid access token ID! Heading back to homepage...</div></div>';
			echo "<meta http-equiv='refresh' content='5;url=index.php'>";
		}
		
		
	} else {
		header("Location: index.php");
	}

?>
<?php
	include('footer.php');
	mysqli_close($conn);
?>