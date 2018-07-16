<?php 
	session_start();
	include('auth.php');
	if ($_SESSION['u_role'] != "Coordinator" AND $_SESSION['u_role'] != "Admin" AND $_SESSION['u_role'] != "Client") {
		header("Location: index.php");
	}
	
	// create a file pointer connected to the output stream
	$f = fopen('php://output', 'w');
	$res = mysqli_query($conn, "SELECT f_name,l_name,username,s_id,d_clearance,current_student,role,status FROM login_info WHERE role='Student' AND d_clearance<>'Yes' AND current_student='Yes' AND status='Active'");
	if(mysqli_num_rows($res) > 0) {
		fputcsv($f, array('Name', 'Email Address', 'USC ID'));
		while($row = mysqli_fetch_assoc($res)) {
			$name = $row['f_name']." ".$row['l_name'];
			if (!empty($row['s_id'])) {
				$sid = $row['s_id'];
			} else {
				$sid = "-";
			}
			fputcsv($f, array($name, $row['username'], $sid));
		}

		// output headers so that the file is downloaded rather than displayed
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="student_needs_dclearance.csv"');
		 
		// do not cache the file
		header('Pragma: no-cache');
		header('Expires: 0');			 
		fclose($f);
	} else {
		echo "false"; // Outputs false if there are no rows. The variable is used during AJAX call in students.php.
		return false;
	}

	mysqli_close($conn);
?>