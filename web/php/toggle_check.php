<?php require_once("data.php");
/*
    Add or remove check from checks table in database

    Expects the following POST values:
        action -- ADD or REM
        course -- database course id
        task -- database task id
        name -- name of person who checked (only needed when adding)
        date -- date completed in ISO format (only needed when adding)
    
    Echo the inner html for the progress bar of the course for instant update
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

if (empty($_POST['action'])) {
    die();
}

// Add or remove check from checks table
$sql = "";
if ($_POST['action'] == 'ADD') {
    $sql = "INSERT INTO checks(course_id, task_id, name, date) VALUES ". 
           "(".$_POST['course'].", ".$_POST['task'].", '".$_POST['name']."', '".$_POST['date']."')";
} else {
    $sql = "DELETE FROM checks WHERE course_id=".$_POST['course']." AND task_id=".$_POST['task'];
}

$con->query($sql);

// Get array of courses that have been manually completed
$manually_complete = array();

$sql = "SELECT course_id ".
       "FROM checks ".
       "LEFT JOIN courses ON courses.id = checks.course_id ".
       "WHERE task_id=0 AND course_id=".$_POST['course'];
$result = $con->query($sql);

while ($row = $result->fetch_assoc()) {
    array_push($manually_complete, $row["course_id"]);
}

// get inormation on selected course
$sql = "SELECT courses.*, COUNT(DISTINCT checks.course_id, checks.task_id) AS \"completed\" ".
"FROM courses ".
"LEFT JOIN checks ON checks.course_id = courses.id ".
"WHERE courses.id=".$_POST['course']." ".
"GROUP BY courses.id";
$result = $con->query($sql);
$row = $result->fetch_assoc();

// calculate progress of selected course
$progress = calculate_progress($row, $manually_complete);

// generate updated html for progress bar of selected course
$color = ($progress == 100 ? "bg-success" : "bg-warning");
echo "<div class='progress-bar $color' role='progressbar' style='width: $progress%'></div>";
?>