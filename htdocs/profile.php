<?php 
	session_start();
	include('header.php');
?>

<?php
	if ($_SESSION['u_role'] == "Coordinator") {
		//header("Location: index.php");
		
		
		// query for students under client
		$res = mysqli_query($conn,"SELECT s.f_name as sfname,s.l_name as slname,s.project_enrolled, c.f_name as cfname, c.l_name as clname
		FROM login_info s , client_projects p, login_info c
		WHERE 
		(s.project_enrolled = p.project_name) and
		p.client_email=c.username order by s.project_enrolled");
		// query for students under DR
		$res1 = mysqli_query($conn,"SELECT s.f_name as sfname,s.l_name as slname,s.project_enrolled
		FROM login_info s
		WHERE 
		s.project_enrolled is not null and s.role='Student' and s.project_enrolled not in (select p.project_name from client_projects p) order by s.project_enrolled");
		
		
		
		
		
		
		echo '<div class="main-content">'; 
		echo '<h1>Home</h1>'; 
		
		echo '<table  class="entries">';
		echo '<tr>';
		echo '<th>Student</th> <th>Project</th> <th>Client</th>';
		echo '</tr>';
		
		//students under DR
		while($row = mysqli_fetch_assoc($res1)){
			echo '<tr>';
			echo '<td>'.$row['sfname'].' '.$row['slname'].'</td>';
			echo '<td>'.$row['project_enrolled'].'</td>';
			echo '<td>DR Coordinator</td>';
			
			
			
			echo '</tr>';
		}
		//students under client
		while($row = mysqli_fetch_assoc($res)){
			echo '<tr>';
			echo '<td>'.$row['sfname'].' '.$row['slname'].'</td>';
			echo '<td>'.$row['project_enrolled'].'</td>';
			echo '<td>'.$row['cfname'].' '.$row['clname'].'</td>';
			
			
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';
	}

?>



<?php
	include('footer.php');
?>
