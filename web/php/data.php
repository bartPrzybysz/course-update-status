<?php
//
// A collection of functions for interacting with database
//

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


/**
 * Calculate the progress of a course, return a percentage
 * 
 * @param array     $row                mysql->fetch_assoc() return value. Must contain column "completed"
 * @param array     $manually_complete  array of row ids that are marked completed
 * 
 * @return int      percentage of the course that is already completed (rounded to the nearest integer)
 */
function calculate_progress($row, $manually_complete) {
    
    if (in_array($row['id'], $manually_complete)) {
        return 100;
    }

    // Total tasks on todo list.
    // UPDATE THIS VALUE WHEN MAKING CHANGES TO LIST 
    $total_tasks = 46;

    // modify total tasks count based on conditional tasks
    if (!$row['has_examity']) {
        $total_tasks -= 1;
    }

    if (!$row['has_surveys']) {
        $total_tasks -= 1;
    }

    if (!$row['has_wiki']) {
        $total_tasks -= 2;
    }

    if (!$row['has_groups']) {
        $total_tasks -= 5;
    }

    if ($row['has_icons']) {
        $total_tasks -= 1;
    } else {
        $total_tasks -= 2;
    }

    if (!$row['has_update_file']) {
        $total_tasks -= 1;
    }

    if (!$row['has_ally']) {
        $total_tasks -= 1;
    }

    // progress = completed / total
    return (int)(($row['completed'] / $total_tasks) * 100);
}


/**
 * Get list of courses for specifiec year-term-session
 * 
 * @param int       $year    year represented by a 4 digit number
 * @param string    $term    'SUMMER', 'FALL', or 'SPRING'
 * @param string    $session 'OL', 'OL-1', 'OL-2' or 'HY'
 * 
 * @return array   array of course arrays. Each course array has entries 'id', 'name' and 'progress'
 */
function get_courses($year, $term, $session) {
    global $con;

    // Get array of courses that have been manually completed
    $manually_complete = array();

    $sql = "SELECT course_id ".
           "FROM checks ".
           "LEFT JOIN courses ON courses.id = checks.course_id ".
           "WHERE task_id=0 AND year=$year AND term=\"$term\" AND session=\"$session\"";
    $result = $con->query($sql);

    while ($row = $result->fetch_assoc()) {
        array_push($manually_complete, $row["course_id"]);
    }

    // 3 arrays for each category of course
    $unstarted_list = $completed_list = $inprogress_list = array();

    // query for all courses in year-term-session and number of checks they have
    $sql = "SELECT courses.*, COUNT(DISTINCT checks.course_id, checks.task_id) AS \"completed\" ".
           "FROM courses ".
           "LEFT JOIN checks ON checks.course_id = courses.id ".
           "WHERE year=$year AND term=\"$term\" AND session=\"$session\" ".
           "GROUP BY courses.id";
    $result = $con->query($sql);

    // iterate over database rows
    while ($row = $result->fetch_assoc()) {
        //construct course array
        $course = [
            "id"        => $row["id"],
            "name"      => $row["course"]."-".$row["session"].($row['section'] ? "-" : "").$row["section"],
            "progress"  => calculate_progress($row, $manually_complete)
        ];

        //place it in proper category
        if ($course["progress"] == 0) {
            array_push($unstarted_list, $course);
        } elseif ($course["progress"] == 100) {
            array_push($completed_list, $course);
        } else {
            array_push($inprogress_list, $course);
        }
    }

    // return all courses in specific order: unstarted at frong, inprogress in middle and completed last
    $course_list = array_merge($unstarted_list, $inprogress_list, $completed_list);
    return $course_list;
}


/**
 * Get name of course to be displayed
 * in following format: THE-101-OL (SUMMER 2019)
 * 
 * @param int       $id - database id of course
 * 
 * @return string   course name as string
 */
function get_course_name($id) {
    global $con;

    if ($id == 0) {
        return "No Course Selected";
    }

    $sql = "SELECT course, session, section, term, year FROM courses WHERE id=$id";
    $result = $con->query($sql);
    $row = $result->fetch_assoc();

    $name = $row['course'].'-'.$row['session'].($row['section'] ? '-'.$row['section'] : '');
    $name .= ' ('.$row['term'].' '.$row['year'].')';

    return $name;
}


/**
 * Generate html for a checkbox and its labels. Htmo is echoed
 * 
 * @param int   $task    database task id
 * @param int   $course  database id of course
 * 
 * @return void
 */
function make_check($task, $course) {
    global $con;

    // Get task text
    $sql = "SELECT text FROM tasks WHERE id=$task";
    $result = $con->query($sql);
    $row = $result->fetch_assoc();
    $text = $row['text'];

    // Get name and date of task completetion
    $sql = "SELECT name, date FROM checks WHERE task_id=$task AND course_id=$course";
    $result = $con->query($sql);
    $name = "";
    $date = "";
    if ($row = $result->fetch_assoc()) {
        $name = $row['name'];
        $date = date("m/d/Y", strtotime($row['date']));
    }

    $two_digit_index = (string)$task;
    if ($task < 10) {
        $two_digit_index = "0" . $two_digit_index;
    }

    // only print the word 'by' if a name is specified for task completion
    $by = (strlen($name) > 0 ? " by " : "");

    $html = "<input type='checkbox' id='check_$two_digit_index' style='margin-left: -18px;' ".
            "onchange='toggleCheck(this.id);'".($row ? " checked" : "").">\n".
            "<label for='check_$two_digit_index' class='font-italic ".($row ? "d-inline" : "d-none").
            "' style='color: rgb(153, 134, 67);'>\n". 
            "Completed$by<span class='name-span'>$name</span> on <span class='date-span'>$date</span>\n". 
            "</label>\n".
            "<label for='check_$two_digit_index' class='".($row ? "d-block" : "d-inline")."'>\n". 
            "$text\n". 
            "</label>";
    
    echo $html;
}


/**
 * Generate html of a radio button for conditional checklists
 * The heml is echoed
 * 
 * @param string    $condition      Name of the condition (ie "has_examity")
 * @param string    $value          Either "yes" or "no"
 * @param int       $course         Database id of course
 * 
 * @return void
 */
function make_conditional($condition, $value, $course) {
    global $con;

    $sql = "SELECT $condition AS 'cond' FROM courses WHERE id=$course;";
    $result = $con->query($sql);
    $row = $result->fetch_assoc();
    $cond = $row['cond'];

    if ($cond == 1 & $value == "yes") {
        $check = " checked";
    } else if ($cond == 0 & $value == "no") {
        $check = " checked";
    } else {
        $check = "";
    }

    $onclick = ($value == "yes" ? "toggleYes(this.name);" : "toggleNo(this.name);");
    $label = ($value == "yes" ? "Yes" : "No");

    $html = "<input type='radio' name='$condition' value='$value' onclick='$onclick'$check> ". 
            "<label>$label</label>";
    
    echo $html;
}


/**
 * Echo the text of course notes
 * 
 * @param int   $course     Database id of course
 * 
 * @return void
 */
function get_notes($course) {
    global $con;

    $sql = "SELECT notes FROM courses WHERE id=$course;";
    $result = $con->query($sql);
    $row = $result->fetch_assoc();
    $notes = $row['notes'];

    echo $notes;
}


/**
 * Get number of course sections in term by their session and status
 * 
 * @param int       $year   the year as a 4 digit number
 * @param string    $term   "FALL", "SPRING" or "SUMMER"
 * 
 * @return array two dimensional associative array. 
 *               Outer index ["OL", "OL1", "OL2", "HY", "total"]. 
 *               Inner index ["unstarted", "inprogress", "completed", "total"]
 */
function get_section_count($year, $term) {
    // get courses for each session
    $ol = get_courses($year, $term, "OL");
    $ol_1 = get_courses($year, $term, "OL-1");
    $ol_2 = get_courses($year, $term, "OL-2");
    $hy = get_courses($year, $term, "HY");

    $section_count = [
        "OL"    => ["unstarted" => 0, "inprogress" => 0, "completed" => 0, "total" => 0],
        "OL1"   => ["unstarted" => 0, "inprogress" => 0, "completed" => 0, "total" => 0],
        "OL2"   => ["unstarted" => 0, "inprogress" => 0, "completed" => 0, "total" => 0],
        "HY"    => ["unstarted" => 0, "inprogress" => 0, "completed" => 0, "total" => 0],
        "total" => ["unstarted" => 0, "inprogress" => 0, "completed" => 0, "total" => 0]
    ];

    // count OL courses
    foreach ($ol as $course) {
        if ($course["progress"] == 100) {
            $section_count["OL"]["completed"]++;
            $section_count["total"]["completed"]++;
        } elseif ($course["progress"] == 0) {
            $section_count["OL"]["unstarted"]++;
            $section_count["total"]["unstarted"]++;
        } else {
            $section_count["OL"]["inprogress"]++;
            $section_count["total"]["inprogress"]++;
        }

        $section_count["OL"]["total"]++;
        $section_count["total"]["total"]++;
    }

    // count OL-1 courses
    foreach ($ol_1 as $course) {
        if ($course["progress"] == 100) {
            $section_count["OL1"]["completed"]++;
            $section_count["total"]["completed"]++;
        } elseif ($course["progress"] == 0) {
            $section_count["OL1"]["unstarted"]++;
            $section_count["total"]["unstarted"]++;
        } else {
            $section_count["OL1"]["inprogress"]++;
            $section_count["total"]["inprogress"]++;
        }

        $section_count["OL1"]["total"]++;
        $section_count["total"]["total"]++;
    }

    // count OL-2 courses
    foreach ($ol_2 as $course) {
        if ($course["progress"] == 100) {
            $section_count["OL2"]["completed"]++;
            $section_count["total"]["completed"]++;
        } elseif ($course["progress"] == 0) {
            $section_count["OL2"]["unstarted"]++;
            $section_count["total"]["unstarted"]++;
        } else {
            $section_count["OL2"]["inprogress"]++;
            $section_count["total"]["inprogress"]++;
        }

        $section_count["OL2"]["total"]++;
        $section_count["total"]["total"]++;
    }

    // count HY courses
    foreach ($hy as $course) {
        if ($course["progress"] == 100) {
            $section_count["HY"]["completed"]++;
            $section_count["total"]["completed"]++;
        } elseif ($course["progress"] == 0) {
            $section_count["HY"]["unstarted"]++;
            $section_count["total"]["unstarted"]++;
        } else {
            $section_count["HY"]["inprogress"]++;
            $section_count["total"]["inprogress"]++;
        }

        $section_count["HY"]["total"]++;
        $section_count["total"]["total"]++;
    }

    return $section_count;
}


/**
 * Internal helper function.
 * Get status and session of each distinct course. If distinct course is offered in multiple sessions, 
 * count the session of its last occurance
 * 
 * @param int           $year               year represented as a four digit number
 * @param string        $term               "FALL", "SPRING" or "SUMMER"
 * @param array         $manually_complete  List of ids that are marked complete
 * 
 * @return array - array of arrays containting a status and a session
 */
function get_distinct($year, $term, $manually_complete) {
    global $con;

    $distinct_courses = array();
    
    $sql = "SELECT DISTINCT course FROM courses WHERE year=$year AND term='$term'";
    $result = $con->query($sql);
    while ($row = $result->fetch_assoc()) {
        array_push($distinct_courses, $row["course"]);
    }

    $statuses = array();

    foreach ($distinct_courses as $course) {
        $progress = 0;
        $sections = 0;
        $session_str = "";

        // query for all courses in year-term-session and number of checks they have
        $sql = "SELECT courses.*, COUNT(DISTINCT checks.course_id, checks.task_id) AS \"completed\" ".
               "FROM courses ".
               "LEFT JOIN checks ON checks.course_id = courses.id ".
               "WHERE year=$year AND term=\"$term\" AND course='$course' ".
               "GROUP BY courses.id";
        $result = $con->query($sql);

        while ($row = $result->fetch_assoc()) {
            $progress += calculate_progress($row, $manually_complete);
            $sections++;
            $session_str = str_replace("-", "", $row["session"]);
        }

        $status = ["status" => "", "session" => ""];
        $status_str = "";
        if ($progress / $sections >= 99) {
            $status_str = "completed";
        } elseif ($progress / $sections == 0) {
            $status_str = "unstarted";
        } else {
            $status_str = "inprogress";
        }
        
        $status = ["status" => $status_str, "session" => $session_str];

        array_push($statuses, $status);
    }

    return $statuses;
}


/**
 * Get number of distinct courses in term by their session and status
 * 
 * @param int       $year   the year as a 4 digit number
 * @param string    $term   "FALL", "SPRING" or "SUMMER"
 * 
 * @return array two dimensional associative array. 
 *               Outer index ["OL", "OL1", "OL2", "HY", "total"]. 
 *               Inner index ["unstarted", "inprogress", "completed", "total"]
 */
function get_course_count($year, $term) {
    global $con;

    // Get array of courses that have been manually completed
    $manually_complete = array();

    $sql = "SELECT course_id ".
           "FROM checks ".
           "LEFT JOIN courses ON courses.id = checks.course_id ".
           "WHERE task_id=0 AND year=$year AND term=\"$term\"";
    $result = $con->query($sql);

    while ($row = $result->fetch_assoc()) {
        array_push($manually_complete, $row["course_id"]);
    }

    // get courses for each session
    $distinct = get_distinct($year, $term, $manually_complete);

    $course_count = [
        "OL"    => ["unstarted" => 0, "inprogress" => 0, "completed" => 0, "total" => 0],
        "OL1"   => ["unstarted" => 0, "inprogress" => 0, "completed" => 0, "total" => 0],
        "OL2"   => ["unstarted" => 0, "inprogress" => 0, "completed" => 0, "total" => 0],
        "HY"    => ["unstarted" => 0, "inprogress" => 0, "completed" => 0, "total" => 0],
        "total" => ["unstarted" => 0, "inprogress" => 0, "completed" => 0, "total" => 0]
    ];

    // count OL courses
    foreach ($distinct as $course) {
        $course_count[$course["session"]][$course["status"]]++;
        $course_count["total"][$course["status"]]++;
        $course_count[$course["session"]]["total"]++;
        $course_count["total"]["total"]++;
    }

    return $course_count;
}


/**
 * Get list of courses for specifiec term that have condition checked true
 * 
 * @param int       $year       year represented by a 4 digit number
 * @param string    $term       'SUMMER', 'FALL', or 'SPRING'
 * @param string    $condition  the condition (ie "has_examity")
 * 
 * @return array   array of course arrays. Each course array has entries 'id' and 'progress'
 */
function get_courses_by_condition($year, $term, $condition) {
    global $con;

    $course_list = array();

    $sql = "SELECT id, course, session, section, term, year ".
           "FROM courses WHERE year=$year AND term='$term' AND $condition=1";
    $result = $con->query($sql);
    while ($row = $result->fetch_assoc()) {
        $name = $row['course'].'-'.$row['session'].($row['section'] ? '-'.$row['section'] : '');
        $name .= ' ('.$row['term'].' '.$row['year'].')';

        $course = ["id" => $row['id'], "name" => $name];
        array_push($course_list, $course);
    }

    return $course_list;
}
?>