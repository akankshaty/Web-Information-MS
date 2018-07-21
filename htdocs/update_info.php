<?php 
	session_start();
	include('auth.php');
	if ($_SESSION['u_role'] != "Coordinator" AND $_SESSION['u_role'] != "Admin" AND $_SESSION['u_role'] != "Student") {
		header("Location: index.php");
	}
	if (isset($_POST['name'])) {

		$url = parse_url((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_HOST); // Parses the domain of the DR Website
		$curr_path = dirname((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");	

		$res = mysqli_query($conn,"SELECT f_name,d_clearance FROM login_info WHERE username='".$_POST['name']."'"); // Select the d-clearance status of the student.
		$row = mysqli_fetch_assoc($res);
		if($row['d_clearance'] == "Yes") { // Set D-Clearance to "No"
			mysqli_query($conn,"UPDATE login_info SET d_clearance='No' WHERE username='".$_POST['name']."'");
			
			// Email for notifying student regarding D-Clearance status update.
			$to = $_POST['name'];

			// Subject
			$subject = 'DR course D-Clearance notification';

			// Message
			$message = '
			<html>
			<head>
			</head>
			<body>
				<p>Dear '.$row['f_name'].',<br /><br />This message is to notify you that the status regarding your D-Clearance for DR course have been modified by either the Admin or the DR Coordinator. Please login to the CSCI 590 DR course web portal to view the update on your survey page.<br /><br />
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
			
		} else { // Set D-Clearance to "Yes"
			mysqli_query($conn,"UPDATE login_info SET d_clearance='Yes' WHERE username='".$_POST['name']."'");
			
			// Email for notifying student regarding D-Clearance status update.
			$to = $_POST['name'];

			// Subject
			$subject = 'DR course D-Clearance notification';

			// Message
			$message = '
			<html>
			<head>
			</head>
			<body>
				<p>Dear '.$row['f_name'].',<br /><br />This message is to notify you that D-Clearance for DR course have been issued to you by either the Admin or the DR Coordinator. Please login to the CSCI 590 DR course web portal to view the update on your survey page.<br /><br />
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
			
		}
	} 
	if (isset($_POST['del_student'])) {

		$url = parse_url((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_HOST); // Parses the domain of the DR Website
		$curr_path = dirname((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
		
		$username = explode("-",$_POST['del_student'])[1]; // Parse the username (email address) from POST variable. POST variable format is "delete-[email address]"
		$res = mysqli_query($conn,"SELECT f_name FROM login_info WHERE username='".$username."'"); // Select the d-clearance status of the student.
		$row = mysqli_fetch_assoc($res);		
		mysqli_query($conn, "DELETE FROM login_info WHERE username='".$username."'");
		mysqli_query($conn, "DELETE FROM changed_units WHERE username='".$username."'");
		mysqli_query($conn, "DELETE FROM project_preferences WHERE email='".$username."'");
		mysqli_query($conn, "DELETE FROM offer_letter_requests WHERE student_email='".$username."'");
		mysqli_query($conn, "DELETE FROM password_requests WHERE email='".$username."'");
		mysqli_query($conn, "DELETE FROM reviewed_students WHERE student_email='".$username."'");
		mysqli_query($conn, "DELETE FROM role_preferences WHERE email='".$username."'");
		mysqli_query($conn, "DELETE FROM student_skills WHERE email='".$username."'");
		mysqli_query($conn, "DELETE FROM vacancy_applications WHERE student_email='".$username."'");
		
			// Email for notifying deletion of account.
			$to = $username;

			// Subject
			$subject = 'DR course website account deletion notification';

			// Message
			$message = '
			<html>
			<head>
			</head>
			<body>
				<p>Dear '.$row['f_name'].',<br /><br />This message is to notify you that your DR course website account has been deleted either by one of the DR Coordinators or by the Admin. You will no longer have access to the DR course website.<br /><br />
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
		
	}
	if (isset($_POST['del_client'])) {
		
		$url = parse_url((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_HOST); // Parses the domain of the DR Website
		$curr_path = dirname((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
		
		$username = explode("-*",$_POST['del_client'])[1]; // Parse the username (email address) from POST variable. POST variable format is "delete-*[email address]"
		$res = mysqli_query($conn,"SELECT f_name FROM login_info WHERE username='".$username."'"); // Select the d-clearance status of the student.
		$row = mysqli_fetch_assoc($res);		
		mysqli_query($conn, "DELETE FROM login_info WHERE username='".$username."'");
		mysqli_query($conn, "DELETE FROM client_projects WHERE client_email='".$username."'");
		mysqli_query($conn, "DELETE FROM password_requests WHERE email='".$username."'");
		mysqli_query($conn, "DELETE FROM reviewed_students WHERE client_email='".$username."'");
		
			// Email for notifying deletion of account.
			$to = $username;

			// Subject
			$subject = 'DR course website account deletion notification';

			// Message
			$message = '
			<html>
			<head>
			</head>
			<body>
				<p>Dear '.$row['f_name'].',<br /><br />This message is to notify you that your DR course website account has been deleted either by one of the DR Coordinators or by the Admin. You will no longer have access to the DR course website.<br /><br />
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

	}
	if (isset($_POST['del_coordinator'])) {
		
		$url = parse_url((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_HOST); // Parses the domain of the DR Website
		$curr_path = dirname((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
		
		$username = explode("-*",$_POST['del_coordinator'])[1]; // Parse the username (email address) from POST variable. POST variable format is "delete-*[email address]"
		$res = mysqli_query($conn,"SELECT f_name FROM login_info WHERE username='".$username."'"); // Select the d-clearance status of the student.
		$row = mysqli_fetch_assoc($res);		
		mysqli_query($conn, "DELETE FROM login_info WHERE username='".$username."'");

			// Email for notifying deletion of account.
			$to = $username;

			// Subject
			$subject = 'DR course website account deletion notification';

			// Message
			$message = '
			<html>
			<head>
			</head>
			<body>
				<p>Dear '.$row['f_name'].',<br /><br />This message is to notify you that your DR course website account has been deleted by the Admin. You will no longer have access to the course website. You will no longer have access to the DR course website.<br /><br />
				Thank you! <br />
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

	}
	if (isset($_POST['apply_vacancy'])) {
		// Parse the role name and project name of vacancy from POST variable. POST variable format is "apply-*[project name]-*[role name]"
		$project_name = explode("-*",str_replace('_',' ',$_POST['apply_vacancy']))[1]; // Remove underscores and replace with a space
		$role_name = explode("-*",str_replace('_',' ',$_POST['apply_vacancy']))[2]; // Remove underscores and replace with a space
		mysqli_query($conn, "INSERT INTO vacancy_applications (project_name,role_name,student_email) VALUES ('".$project_name."','".$role_name."','".$_SESSION['u_name']."')");
	}
	if (isset($_POST['close_vacancy'])) {
		// Parse the role name and project name of vacancy from POST variable. POST variable format is "close-*[project name]-*[role name]"
		$project_name = explode("-*",str_replace('_',' ',$_POST['close_vacancy']))[1];
		$role_name = explode("-*",str_replace('_',' ',$_POST['close_vacancy']))[2]; 
		mysqli_query($conn, "UPDATE vacancies SET closed='Yes' WHERE project_name='".$project_name."' AND role_name='".$role_name."'");
	}
	if(isset($_POST['accept_offer'])) {
			// Student accepted an offer. Update the values in the offer_letter_requests table
			$student_email = explode("-*",$_POST['accept_offer'])[0];
			$project_name = explode("-*",$_POST['accept_offer'])[1];
			
			mysqli_query($conn,"DELETE FROM vacancy_applications WHERE student_email='".$_POST['s_email']."'");
			mysqli_query($conn,"UPDATE offer_letter_requests SET status='Accepted' WHERE student_email='".$student_email."' AND project_name='".$project_name."'");
			mysqli_query($conn,"UPDATE offer_letter_requests SET status='Declined' WHERE student_email='".$student_email."' AND status='Pending'");
			mysqli_query($conn,"UPDATE login_info SET project_enrolled='".$project_name."' WHERE username='".$student_email."'");

	}
	if(isset($_POST['decline_offer'])) {
			// Update the values in the offer_letter_requests table
			$student_email = explode("-*",$_POST['decline_offer'])[0];
			$project_name = explode("-*",$_POST['decline_offer'])[1];
			
			mysqli_query($conn,"UPDATE offer_letter_requests SET status='Declined' WHERE student_email='".$student_email."' AND project_name='".$project_name."'");

	}		
	mysqli_close($conn);
?>