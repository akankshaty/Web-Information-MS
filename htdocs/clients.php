<?php 
	session_start();
	include('header.php');
	include('auth.php');
	
	if ($_SESSION['u_role'] != "Coordinator" AND $_SESSION['u_role'] != "Admin") {
		header("Location: index.php");
	}
	
	if(isset($_POST['add_client'])) {
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
				<p>Dear '.$f_name.' '.$l_name.',<br />This message is to notify you that you are invited to join the Direct Research project as a client.
				Please click the link below to sign-up.</p><br />
				<a href="'.$url.'/client_signup.php?access='.$random_string.'"></a>
			</body>
			</html>
			';

			// To send HTML mail, the Content-type header must be set
			$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-type: text/html; charset=iso-8859-1';
			$headers[] = 'From: '.$_SESSION['f_name'].' '.$_SESSION['l_name'].' <'.$_SESSION['u_name'].'>'; // Format of the variable ("From: First-Name Last-Name <example@example.com>")
			// Mail it to client
			mail($to, $subject, $message, implode("\r\n", $headers));
		}
	}
?>
<div class="main-content">
	<h1>Clients (<a id="toggle_client_form" href="javascript:void(0)">Add Client?</a>)</h1>
	<div class="survey" style="width: 300px; overflow: auto;text-align: center;">
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
	<p>List of all client invitations which has been sent. But the client hasn't signed up yet to this website.</p>
	<p>The access code URL sent to the client is of the form: http://<?php echo $url; ?>/client_signup.php?access=[ACCESS CODE]</p>
	<p><span class="font_bold">Extra Tip:</span> Email the access code directly to the client in case the client is not receiving the invitation email automatically.</p>
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
						echo implode(", ",$p_array);
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
	<p>List of all the clients signed up in this website.</p>
	<table class="entries">
		<thead>
			<tr>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email Address</th>
				<th>Projects Managed</th>
				<th>Delete?</th>
			</tr>
			<?php 
				$clients = mysqli_query($conn, "SELECT f_name,l_name,username FROM login_info WHERE role='Client'");
				
				while($row = mysqli_fetch_assoc($clients)) { // Prints out list of all clients in db
					$client_projects = mysqli_query($conn, "SELECT client_email,project_name FROM client_projects WHERE client_email='".$row['username']."'");
					$project_list = "";
					while($row_projects = mysqli_fetch_assoc($client_projects)) {
						$project_list .= $row_projects['project_name'].", ";
					}
					$project_list = rtrim(trim($project_list),',');
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
						if (mysqli_num_rows($client_projects) > 0) {
							echo '<td>';
							echo $project_list;
							echo '</td>';
						} else {
							echo '<td>';
							echo "No projects.";
							echo '</td>';							
						}
						if ($_SESSION['u_role'] == "Coordinator") {
							echo '<td>';
							echo '<img src="images/delete_cross_mark.png" id="delete-*'.$row['username'].'" class="delete" style="width: 30px;cursor: pointer;" />';
							echo '</td>';
						}

					echo '</tr>';
				}
				if (mysqli_num_rows($clients) < 1) { // If no clients are registered in this website
					echo '<tr>';
						echo '<td colspan="10">';
						echo 'No clients signed up in this website.';
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
	$("#toggle_client_form").on("click", function() {
		$(".survey").toggle();
	});
	$("#new_client").submit(function(e) {
		if (!$("input[name=f_name]").val() || !$("input[name=l_name]").val() || !$("input[name=c_email]").val()) {
			$("#error_txt").text("Please enter all required fields.");
			$(".error").show();
			return false;
		} 
	});
	$(".delete").click(function(event){
		if (confirm('Are you sure you want to delete this client?')) {
			var s_id = event.target.id;
			$.ajax({
				url: "update_info.php",
				type: "POST",
				data: { "del_client": s_id },
				success: function(response){
					window.location.replace("clients.php");
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