<?php
date_default_timezone_set('America/Los_Angeles');
session_start();
include('auth.php');
if(!isset($_SESSION['u_name']) && basename($_SERVER['SCRIPT_FILENAME']) != "verify.php") { // Redirect user to login page if user is not logged-in (except for when user is verifying email (verify.php))
    //header('Location: index.php');
    exit();
}
$url = parse_url((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_HOST); // Parses the domain of the DR Website
?>
<!DOCTYPE html>
<html lang="en" style="overflow:hidden;background-color: #fff;">
<head>
<meta charset="UTF-8">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="dr.css" />
<title>DR Profile</title>
</head>
<body style="overflow:hidden;width:80%;">
<?php 
	$res = mysqli_query($conn,"SELECT setting_value FROM settings_option WHERE setting_name ='Filling Survey Form Deadline'");
	if (mysqli_num_rows($res) > 0) { // Deadline for filling the survey form is set
		$unformatted_deadline = mysqli_fetch_assoc($res)['setting_value'];
		$deadline_passed = ((time() - $unformatted_deadline) > 0)? true : false;
	} else { // Deadline for filling the survey form is not set
		$deadline_passed = false;
	}
	if ($_SESSION['u_role'] == 'Student') {
		$res = mysqli_query($conn,"SELECT * FROM login_info WHERE username='".$_SESSION['u_name']."'");
	} else {
		$res = mysqli_query($conn,"SELECT * FROM login_info WHERE username='".$_GET['student']."'");
	}
	
	$row = mysqli_fetch_assoc($res);
	if (!empty($row['resume_name_on_server'])) {
		echo '<span id="resume_status"><a href="'.$row['resume_name_on_server'].'" target="_blank" ><p style="font-size:11pt;margin: 5px 0; font-style: italic;">'.$row['resume_name_by_user'].'</p></a></span>';
	} else {
		echo '<span id="resume_status"><p style="font-size:11pt;margin: 5px 0; font-style: italic;">No resume on file.</p></span>';
	}
?>
		<form action="" method="post" enctype="multipart/form-data" >
		<?php
		if ($_SESSION['u_role'] == "Student") {
			echo '<input type="file" name="file_to_upload" id="file_to_upload" /><br />';
		}
		?>
<?php
if(isset($_POST['upload'])) {
	$target_dir = "resumes/";
	$file_type = strtolower(pathinfo($target_dir.basename($_FILES["file_to_upload"]["name"]),PATHINFO_EXTENSION));

	do { // Loop until the created random string is unique (random string which is not found in database already)
		$string_not_unique = true; 
		$random_string = "";
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		for($i=0;$i<10;$i++) {
			$random_string.=substr($chars,rand(0,strlen($chars)),1); // Generating a random string to save the file in directory
		}
		$target_file = $target_dir.basename($random_string.'.'.trim($file_type));
		if (!file_exists($target_file)) { // Generated string is unique and no file exist on that name with that exact file extension
			$string_not_unique = false; 
		}
	} while($string_not_unique);
	$upload_ok = true;
	
	// No file chosen
	if(($_FILES["file_to_upload"]["name"] == "") && $upload_ok) {
		echo "<p class='font_red' style='font-size:11pt;font-weight: normal;margin: 5px 0;'>Please choose a file to upload.</p>";
		$upload_ok = false;
	} 
	// Check file size
	if (($_FILES["file_to_upload"]["size"] > 3000000) && $upload_ok) {
		echo "<p class='font_red' style='font-size:11pt;font-weight: normal;margin: 5px 0;'>Upload Failed! Sorry, your file is too large. File size should be less than 3MB.</p>";
		$upload_ok = false;
	}
	// Allow only PDF file formats
	if($file_type != "pdf" && $upload_ok) {
		echo "<p class='font_red' style='font-size:11pt;font-weight: normal;margin: 5px 0;'>Upload Failed! Sorry, only PDF files are allowed.</p>";
		$upload_ok = false;
	}
	// Upload file only if $upload_ok is set to true
	if ($upload_ok) {
		if (move_uploaded_file($_FILES["file_to_upload"]["tmp_name"], $target_file)) {
			$_SESSION['no_resume'] = false;
			$res = mysqli_query($conn,"SELECT * FROM login_info WHERE username='".$_SESSION['u_name']."'");
			$row = mysqli_fetch_assoc($res);
			$file = $row['resume_name_on_server']; // Get the file name of the previous resume version uploaded by user.
			if (!empty($file)) {
				if(file_exists($file)) {
					unlink($file); // Delete the previous file once the new file from server is uploaded.
				}
			}
			mysqli_query($conn,"UPDATE login_info SET resume_name_by_user='".$_FILES["file_to_upload"]["name"]."', resume_name_on_server='".$target_file."' WHERE username='".$_SESSION['u_name']."'");
			echo '<script>$(document).ready(function(){$("#resume_status").html("<a href=\"'.$target_file.'\" target=\"_blank\" ><p style=\"font-size:11pt;margin: 5px 0; font-style: italic;\">'.$_FILES["file_to_upload"]["name"].'</p></a>");});</script>';
			echo "<p class='font_green' style='font-size:11pt;font-weight: normal;margin: 5px 0;'>The file ". basename( $_FILES["file_to_upload"]["name"])." has been uploaded.</p>";
		} else {
			echo "<p class='font_red' style='font-size:11pt;font-weight: normal;margin: 5px 0;'>Upload Failed! Sorry, there was an error uploading your file.</p>";
		}
	}
}
if(isset($_POST['remove'])) {
	$confirm_msg = "Are you sure you want to delete uploaded resume?";

	$script = "<script>";
	$script .= "if(confirm('" . $confirm_msg . "'))";
	$script .= "document.write('".'<p class="font_green" style="font-size:11pt;font-weight: normal;margin: 5px 0;">The file '. " has been removed.</p>" . "');";
	$script .= "else";
	$script .= "document.write('".'<p class="font_red" style="font-size:11pt;font-weight: normal;margin: 5px 0;">The file '. " is retained.</p>" . "');";
	$script .= "</script>";

	echo $script;
}
?>
		<input type="submit" id ="upload_file" style="float: left;width:auto; font-size: 12pt; padding: 5px; margin:10px 0;left:0;" name="upload" <?php if($deadline_passed || $_SESSION['u_role'] != "Student") {echo 'hidden';} ?> value="Upload" />
		<!--<input type="submit" id="remove_file" style="float: left;width:auto; font-size: 12pt; padding: 5px; margin:10px 5px;left:0;" name="remove" <?php //if($deadline_passed || $_SESSION['u_role'] != "Student") {echo 'disabled';} ?> value="Remove" />-->
		</form>
		<!--</div>--><br />
<?php
	include('footer.php');
	mysqli_close($conn);
?>
