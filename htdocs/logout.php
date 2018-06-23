<?php 
	session_start();
	//unset($_SESSION['u_name']);
	$_SESSION = array();
	session_destroy();
	header('Location: index.php');
?>