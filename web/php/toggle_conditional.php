<?php 
/*
    Set conditional properties in the courses table

    Expects the following POST values:
        condition -- name of condition (ie "has_examity")
        course -- database course id
        value -- 1 or 0
*/

// Database connection
$config = include('config.php');
$con = new mysqli($config["host"], $config["user"], $config["password"], $config["database"]);
if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}
if (!$con->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $con->error);
    exit();
}

// Update value of conditional in courses table
$sql = "UPDATE courses SET ".$_POST['condition'].'='.$_POST['value']." WHERE id=".$_POST['course'];
$con->query($sql);
?>