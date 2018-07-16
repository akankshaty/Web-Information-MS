<?php 
	session_start();
	include('auth.php');
	if ($_SESSION['u_role'] != "Coordinator" AND $_SESSION['u_role'] != "Admin" AND $_SESSION['u_role'] != "Client") {
		header("Location: index.php");
	}
	
	// create a file pointer connected to the output stream
	$f = fopen('php://output', 'w');
	$res = mysqli_query($conn, "SELECT li.f_name,li.l_name,li.d_clearance,cu.username,cu.from_units,cu.to_units,cu.timestamp FROM changed_units cu LEFT JOIN login_info li ON li.username=cu.username WHERE li.role='Student' AND li.status='Active'");
	if(mysqli_num_rows($res) > 0) {
		fputcsv($f, array('Name', 'Email Address', 'Units Changed From', 'Units Changed To'));
		while($row = mysqli_fetch_assoc($res)) {
			$name = $row['f_name']." ".$row['l_name'];
			if ($row['from_units'] == "1") {
				$from = "1 unit";
			} else if ($row['from_units'] == "2") {
				$from = "2 units";
			} else if ($row['from_units'] == "3") {
				$from = "3 units";
			} else if ($row['from_units'] == "4+") {
				$from = "4+ units";
			} else if ($row['from_units'] == "intern") {
				$from = "Unpaid Intern";
			} else {
				$from = "-";
			}
			if ($row['to_units'] == "1") {
				$to = "1 unit";
			} else if ($row['to_units'] == "2") {
				$to = "2 units";
			} else if ($row['to_units'] == "3") {
				$to = "3 units";
			} else if ($row['to_units'] == "4+") {
				$to = "4+ units";
			} else if ($row['to_units'] == "intern") {
				$to = "Unpaid Intern";
			} else {
				$to = "-";
			}
			fputcsv($f, array($name, $row['username'], $from, $to));
		}

		// output headers so that the file is downloaded rather than displayed
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="student_changed_units.csv"');
		 
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