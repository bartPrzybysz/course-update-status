<?php 
/*
    Set notes property in courses table

    Expects the following POST values:
        notes -- the notes to be stored
        course -- database course id
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

$sql = "UPDATE courses SET notes=\"".$con->real_escape_string($_POST['notes'])."\" WHERE id=".$_POST['course'];
$con->query($sql);
?>