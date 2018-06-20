<?php
    require_once('db_credentials.php');

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

    $data = 'Connection successful. Data passed to DB: ';
    $data .= 'Email: ' . $email;
    $data .= 'Password: ' . $password;

    echo $data;
?>
