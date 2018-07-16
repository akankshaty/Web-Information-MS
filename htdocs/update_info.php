<?php 
	session_start();
	include('auth.php');
	if ($_SESSION['u_role'] != "Coordinator" AND $_SESSION['u_role'] != "Admin") {
		header("Location: index.php");
	}
	if (isset($_POST['name'])) {
		$res = mysqli_query($conn,"SELECT d_clearance FROM login_info WHERE username='".$_POST['name']."'"); // Select the d-clearance status of the student.
		$row = mysqli_fetch_assoc($res);
		if($row['d_clearance'] == "Yes") { // Set D-Clearance to "No"
			mysqli_query($conn,"UPDATE login_info SET d_clearance='No' WHERE username='".$_POST['name']."'");
			// the message
			$msg = "Dear DR Student,\n\nThis message is to notify you that the status regarding your D-Clearance for DR course have been modified. Please login to the DR course portal for more information.";

			// use wordwrap() if lines are longer than 70 characters
			$msg = wordwrap($msg,70);

			// send email
			mail($_POST['name'],"DR course D-Clearance notification",$msg);
		} else { // Set D-Clearance to "Yes"
			mysqli_query($conn,"UPDATE login_info SET d_clearance='Yes' WHERE username='".$_POST['name']."'");
			// the message
			$msg = "Dear DR Student,\n\nThis message is to notify you that D-Clearance for DR course have been issued to you.";

			// use wordwrap() if lines are longer than 70 characters
			$msg = wordwrap($msg,70);

			// send email
			mail($_POST['name'],"DR course D-Clearance notification",$msg);
		}
	} 
	if (isset($_POST['del_student'])) {
		$username = explode("-",$_POST['del_student'])[1]; // Parse the username (email address) from POST variable. POST variable format is "delete-[email address]"
		mysqli_query($conn, "DELETE FROM login_info WHERE username='".$username."'");
		mysqli_query($conn, "DELETE FROM changed_units WHERE username='".$username."'");
		mysqli_query($conn, "DELETE FROM project_preferences WHERE email='".$username."'");
		mysqli_query($conn, "DELETE FROM offer_letter_requests WHERE student_email='".$username."'");
		mysqli_query($conn, "DELETE FROM password_requests WHERE email='".$username."'");
		mysqli_query($conn, "DELETE FROM reviewed_students WHERE student_email='".$username."'");
		mysqli_query($conn, "DELETE FROM role_preferences WHERE email='".$username."'");
		mysqli_query($conn, "DELETE FROM student_skills WHERE email='".$username."'");
		mysqli_query($conn, "DELETE FROM vacancy_applications WHERE student_email='".$username."'");
			// the message
			$msg = "Dear DR Student,\n\nThis message is to notify you that your DR course website account has been deleted by one of the DR Coordinators/Admin. You will no longer have access to the course website.";

			// use wordwrap() if lines are longer than 70 characters
			$msg = wordwrap($msg,70);

			// send email
			mail($_POST['name'],"DR course website account deletion notification",$msg);
	}
	if (isset($_POST['del_client'])) {
		$username = explode("-*",$_POST['del_client'])[1]; // Parse the username (email address) from POST variable. POST variable format is "delete-*[email address]"
		mysqli_query($conn, "DELETE FROM login_info WHERE username='".$username."'");
		mysqli_query($conn, "DELETE FROM client_projects WHERE client_email='".$username."'");
		mysqli_query($conn, "DELETE FROM password_requests WHERE email='".$username."'");
		mysqli_query($conn, "DELETE FROM reviewed_students WHERE client_email='".$username."'");
			// the message
			$msg = "Dear DR Client,\n\nThis message is to notify you that your DR course website account has been deleted by one of the DR Coordinators/Admin. You will no longer have access to the course website.";

			// use wordwrap() if lines are longer than 70 characters
			$msg = wordwrap($msg,70);

			// send email
			mail($_POST['name'],"DR course website account deletion notification",$msg);
	}
	if (isset($_POST['del_coordinator'])) {
		$username = explode("-*",$_POST['del_coordinator'])[1]; // Parse the username (email address) from POST variable. POST variable format is "delete-*[email address]"
		mysqli_query($conn, "DELETE FROM login_info WHERE username='".$username."'");
			// the message
			$msg = "Dear DR Coordinator,\n\nThis message is to notify you that your DR course website account has been deleted by Admin. You will no longer have access to the course website.";

			// use wordwrap() if lines are longer than 70 characters
			$msg = wordwrap($msg,70);

			// send email
			mail($_POST['name'],"DR course website account deletion notification",$msg);
	}
	if (isset($_POST['close_vacancy'])) {
		// Parse the role name and project name of vacancy from POST variable. POST variable format is "close-*[project name]-*[vacancy name]"
		$project_name = explode("-*",str_replace('_',' ',$_POST['close_vacancy']))[1];
		$role_name = explode("-*",str_replace('_',' ',$_POST['close_vacancy']))[2]; 
		mysqli_query($conn, "UPDATE vacancies SET closed='Yes' WHERE project_name='".$project_name."' AND role_name='".$role_name."'");
	}
	if(isset($_POST['accept_offer'])) {
			// Update the values in the offer_letter_requests table
			$student_email = explode("-*",$_POST['accept_offer'])[0];
			$project_name = explode("-*",$_POST['accept_offer'])[1];
			
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