<?php 
	session_start();
	include('header.php');
	include('auth.php');
	
	if ($_SESSION['u_role'] != "Admin") {
		header("Location: index.php");
	}
	
	if(isset($_POST['add_coordinator'])) {
		$f_name = mysqli_real_escape_string($conn,$_POST['f_name']);
		$l_name = mysqli_real_escape_string($conn,$_POST['l_name']);
		$c_email = mysqli_real_escape_string($conn,$_POST['c_email']);
		
		$url = parse_url((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_HOST); // Parses the domain of the DR Website
		$curr_path = dirname((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
		
		$res = mysqli_query($conn,"SELECT * FROM login_info WHERE username='".$c_email."'");
		if(mysqli_num_rows($res) < 1) { // Check if user already exist in database, add invitation if not.
			$string_not_unique = true;
			do { // Loop until the created random string is unique (random string which is not found in database already)
				$random_string = "";
				$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
				for($i=0;$i<=6;$i++) {
					$random_string.=substr($chars,rand(0,strlen($chars)),1); // Generating a random string as a form of access code to email users
				}
				$res = mysqli_query($conn,"SELECT * FROM coordinator_invitations WHERE access_token='".$random_string."'");
				if (mysqli_num_rows($res) < 1) { // Generated string is unique
					$string_not_unique = false; 
				}
			} while($string_not_unique);
			// Insert the values into coordinator_invitations table
			mysqli_query($conn,"INSERT INTO coordinator_invitations (f_name,l_name,email,access_token) VALUES ('".$f_name."','".$l_name."','".$c_email."','".$random_string."')");
			
			
			// Email for inviting coordinator to join
			$to = $c_email;

			// Subject
			$subject = 'CSCI 590 DR Course Coordinator Invitation';

			// Message
			$message = '
			<html>
			<head>
			</head>
			<body>
				<p>Dear '.$f_name.',<br /><br />This message is to notify you that you are invited to join the Direct Research course website as a DR Coordinator.
				Please click on the link below to sign-up to the website.</p>
				<a href="'.$curr_path.'/coordinator_signup.php?access='.$random_string.'">'.$curr_path.'/coordinator_signup.php?access='.$random_string.'</a><br /><br />
				Thank you!
			</body>
			</html>
			';

			// To send HTML mail, the Content-type header must be set
			$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-type: text/html; charset=iso-8859-1';
			$headers[] = 'From: DR CSCI-590 <no-reply@'.$url.'>'; // Format of the variable ("From: DR CSCI-590 <no-reply@domain_name.com>")
			// Mail it to coordinator
			mail($to, $subject, $message, implode("\r\n", $headers));
		}
	}
?>
<div class="main-content">
	<h1>Coordinators (<a id="toggle_coordinator_form" href="javascript:void(0)">Add a DR Coordinator?</a>)</h1>
	<div class="survey" style="width: 300px; overflow: auto;text-align: center;">
		<form id="new_coordinator" action="" method="POST">
		<div><p>Coordinator's First Name <span class="font_red">*</span></p><input type="text" name="f_name" placeholder="First Name" /></div>
		<div><p>Coordinator's Last Name <span class="font_red">*</span></p><input type="text" name="l_name" placeholder="Last Name" /></div>
		<div><p>Coordinator's Email Address <span class="font_red">*</span></p><input type="text" name="c_email" placeholder="Email Address" /></div>

		<div class="error"><p id="error_txt"></p></div>
		<input type="submit" value="Add Coordinator" name="add_coordinator" />
		</form>
	</div>
	<p>List of all coordinator invitations which has been sent. But the coordinator hasn't signed up yet to this website.</p>
	<p>The access code URL sent to the coordinator is of the form: http://<?php echo $url; ?>/coordinator_signup.php?access=[ACCESS CODE]</p>
	<p><span class="font_bold">Extra Tip:</span> Email the access code directly to the coordinator in case the coordinator is not receiving the invitation email automatically.</p>
	<table class="entries">
		<thead>
			<tr>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email Address</th>
				<th>Access Code</th>
			</tr>
			<?php 
				// Get a list of all coordinator invitations sent by the Admin
				$coordinators = mysqli_query($conn, "SELECT f_name,l_name,email,access_token FROM coordinator_invitations");
				while($row = mysqli_fetch_assoc($coordinators)) { // Prints out each row from coordinator_invitations db table results one by one
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
						echo $row['access_token'];
						echo '</td>';
					echo '</tr>';
				}
				if (mysqli_num_rows($coordinators) < 1) { // If no coordinator invitations is sent
					echo '<tr>';
						echo '<td colspan="10">';
						echo 'No coordinator invitations sent.';
						echo '</td>';
					echo '</tr>';
				}
			?>
		</thead>
	</table><br />
	<p>List of all the coordinators signed up in this website.</p>
	<table class="entries">
		<thead>
			<tr>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email Address</th>
				<th>Delete?</th>
			</tr>
			<?php 
				$coordinators = mysqli_query($conn, "SELECT f_name,l_name,username FROM login_info WHERE role='Coordinator'");
				
				while($row = mysqli_fetch_assoc($coordinators)) { // Prints out list of all coordinators in db
					echo '<tr>';
						echo '<td>';
						echo $row['f_name'];
						echo '</td>';
						echo '<td>';
						echo $row['l_name'];
						echo '</td>';
						echo '<td>';
						echo $row['username'];
						echo '</td>';
						if ($_SESSION['u_role'] == "Admin") {
							echo '<td>';
							echo '<img src="images/delete_cross_mark.png" id="delete-*'.$row['username'].'" class="delete" style="width: 30px;cursor: pointer;" />';
							echo '</td>';
						}

					echo '</tr>';
				}
				if (mysqli_num_rows($coordinators) < 1) { // If no DR coordinators are registered in this website
					echo '<tr>';
						echo '<td colspan="10">';
						echo 'No DR coordinators signed up in this website.';
						echo '</td>';
					echo '</tr>';
				}
			?>
		</thead>
	</table><br />
</div>
<script>
$(".error").hide();
$(".survey").hide();
$(document).ready(function() {
	$("#toggle_coordinator_form").on("click", function() {
		$(".survey").toggle();
	});
	$("#new_coordinator").submit(function(e) {
		if (!$("input[name=f_name]").val() || !$("input[name=l_name]").val() || !$("input[name=c_email]").val()) {
			$("#error_txt").text("Please enter all required fields.");
			$(".error").show();
			return false;
		} 
	});
	$(".delete").click(function(event){
		if (confirm('Are you sure you want to delete this coordinator?')) {
			var s_id = event.target.id;
			$.ajax({
				url: "update_info.php",
				type: "POST",
				data: { "del_coordinator": s_id },
				success: function(response){
					window.location.replace("coordinators.php");
				},
				error: function(){
				}
			});
		} else {
			return false;
		}
	});
});
</script>

<?php
	include('footer.php');
	mysqli_close($conn);
?>