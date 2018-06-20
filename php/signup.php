<?php
    require_once('db_credentials.php');

    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if(mysqli_connect_errno()) {
        $msg = 'Error connecting to database: ' . mysqli_connect_error() . '[' . mysqli_connect_errno() . ']';
        exit($msg);
    }

    if(isset($db)) {
        mysqli_close($db);
    }

    $data = "Connection successful. \n";
    $data .= "User created with login details: \n";
    $data .= "Name: " . $name . "\n";
    $data .= 'Email: ' . $email . "\n";
    $data .= 'Password: ' . $password;

    echo $data;
?>
