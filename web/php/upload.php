<?php
/*
    Upload a CSV file to courses database

    Expects a csv file in FILES (uploaded by a POST method form in the origin html)
        The csv file must have the following column headers: course, section, year, term, session and notes

*/

// Database connection
$config = include('config.php');
$con = new mysqli($config["host"], $config["user"], $config["password"], $config["database"]);
if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}
if (!$con->set_charset("utf8")) {
    printf("Error loading character set utf8: %s<br>", $con->error);
    exit();
}

/**
 * Make sure that the file uploaded follows the right format for a database upload
 * 
 * @param string        $file_path         Relative path of uploaded file
 * 
 * @return bool
 */
function verify_upload($file_path) {
     // make sure file exists
     if (!file_exists($file_path)) {
        return False;
    }

    // make sure file is excel or csv
    if(pathinfo($file_path, PATHINFO_EXTENSION) != 'csv') {
        echo "Upload must be a CSV file<br>";
        return False;
    }

    $file = fopen($file_path, "r");

    // make sure the first row has the right headers
    if (fgetcsv($file) != ['course', 'section', 'year', 'term', 'session', 'notes']) {
        echo "Invalid column headers<br>";
        echo "Coulmn headers should be course, section, year, term, session and notes";
        return False;
    }

    // verify each row
    $index = 1;
    while ($row = fgetcsv($file)) {
        $index++;

        // course cannot be null
        if (!$row[0]) {
            echo "Error in row $index:   All rows require a course name<br>";
            return False;
        }
        // year must be a valid year
        if (intval($row[2]) < 2000) {
            echo "Error in row $index:   Invalid year encountered<br>";
            return False;
        }
        // term must be valid
        if (!in_array($row[3], ["FALL", "SUMMER", "SPRING"])) {
            echo "Error in row $index:   Invalid term encountered<br>";
            return False;
        }
        // session must ve valid
        if (!in_array($row[4], ["OL", "OL-1", "OL-2", "HY"])) {
            echo "Error in row $index:   Invalid session encountered<br>";
            return False;
        }
    }
    
    $index--;
    echo "$index rows of data found<br>";
    echo "Note: Duplicate courses will not be uploaded<br>";

    fclose($file);

    return True;
}


/**
 * Upload contents of file to courses table
 * 
 * @param mysqli        $con            A mysqli object that is connected to the target database
 * @param string        $file_path      Relative path of the file to be uploaded
 * 
 * @return void
 */
function db_upload($con, $file_path) {
    $file = fopen($file_path, "r");
    fgetcsv($file);

    $sql = "INSERT IGNORE INTO courses(course, section, year, term, session, notes) VALUES ";
    while ($row = fgetcsv($file)) {
        $course = "'".$row[0]."'";
        $section = ($row[1] ? "'".$row[1]."'" : "NULL");
        $year = $row[2];
        $term = "'".$row[3]."'";
        $session = "'".$row[4]."'";
        $notes = "'".$con->real_escape_string($row[5])."'";

        $sql .= "($course, $section, $year, $term, $session, $notes), ";
    }

    $sql = rtrim($sql, ", ").";";

    fclose($file);

    $con->query($sql);
}

// rename the file with a timestamp
$path = '../uploads/upload_'.date('Y-m-d_H-i-s').'.'.pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

// move the uploaded file from temp to the uploads folder
move_uploaded_file($_FILES['file']['tmp_name'], "$path");

// verify the file and upload it
if (verify_upload($path)) {
    db_upload($con, $path);
    echo "<br>UPOAD SUCCESSFUL";
} else {
    echo "<br>UPLOAD FAILED";
}

?>