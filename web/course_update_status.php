<!DOCTYPE html>
<html>

<head>
    <title>Course Update Status</title>
    <link rel="stylesheet" type="text/css" href="css\bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css\course-update.css">

    <?php require_once("php/data.php");
        
    // Get url paramters, if empty, assign defaults
    if (empty($_GET["id"])) {
        $selected_id = 0;
    } else {
        $selected_id = (int)$_GET["id"];
    }
    
    if (empty($_GET["name"])) {
        $name = "";
    } else {
        $name = $_GET["name"];
    }

    if(empty($_GET["year"])){
        $display_year = date("Y");
    } else {
        $display_year = $_GET["year"];
    }

    if(empty($_GET["term"])) {
        $display_term;
        switch (intval(date('m'))) {
            case 2: case 3: case 4: case 5: case 6:
                $display_term = "SUMMER";
                break;
            case 7: case 8: case 9:
                $display_term = "FALL";
                break;
            case 10: case 11: case 12: case 1:
                $display_term = "SPRING";
                break;
            default:
                $display_term = "FALL";
                break;
        }
    } else {
        $display_term = $_GET["term"];
    }

    if(empty($_GET["session"])) {
        $display_session = "OL";
    } else {
        $display_session = $_GET["session"];
    }
    
    $course_list = get_courses($display_year, $display_term, $display_session);

    $course_name = get_course_name($selected_id);
    ?>
    
    <script src = js/course_update.js></script>
    <script>
        // Initialize urlVars object
        urlVars.id = <?php echo $selected_id; ?>;
        urlVars.name = "<?php echo $name; ?>";
        urlVars.year = <?php echo $display_year; ?>;
        urlVars.term = "<?php echo $display_term; ?>";
        urlVars.session = "<?php echo $display_session; ?>";
    </script>
</head>

<body onload="toggleAll();">
    <div class="row sticky-top page-header">
        <div class="col-4 align-self-center">
            <div class="input-group">
                <h5 class="mb-0 ml-3 mr-3">Show: </h5>
                <input type="number" id="year" min="2000" max="2100" step="1" value="<?php echo $display_year; ?>"
                       onchange="urlVars.setYear(this.value);">
                <select id="term" onchange="urlVars.setTerm(this.value);">
                    <option value="FALL" <?php if($display_term == "FALL") echo "selected";?>>Fall</option>
                    <option value="SPRING" <?php if($display_term == "SPRING") echo "selected";?>>Spring</option>
                    <option value="SUMMER" <?php if($display_term == "SUMMER") echo "selected";?>>Summer</option>
                </select>
                <select id="session" onchange="urlVars.setSession(this.value);">
                    <option value="OL" <?php if($display_session == "OL") echo "selected";?>>OL</option>
                    <option value="OL-1" <?php if($display_session == "OL-1") echo "selected";?>>OL-1</option>
                    <option value="OL-2" <?php if($display_session == "OL-2") echo "selected";?>>OL-2</option>
                    <option value="HY" <?php if($display_session == "HY") echo "selected";?>>HY</option>
                </select>
            </div>
        </div>
        <div class="col-4 align-self-center">
            <h4>Course Update Checklist</h4>
        </div>
        <div class="col-4 align-self-center">
            <div class="input-group">
                <h5 class="mb-0 ml-5 mr-3 pl-3">Your Name: </h5>
                <input type="text" id="username" value="<?php echo $name; ?>" onchange="urlVars.setName(this.value);">
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-4">
            <nav class="nav nav-pills flex-column text-center border-right 
                        font-weight-bold course-nav">
                <?php 
                // generate links to all courses in courselist
                foreach($course_list as $course) {
                    $color = ($course["progress"] == 100 ? "bg-success" : "bg-warning");
                    $url = "?id=".$course["id"].
                           "&name=$name&year=$display_year&term=$display_term&session=$display_session";

                    echo "<a class='nav-link".($course["id"] == $selected_id ? " active" : "")."' href='$url'>";
                    echo $course["name"];
                    echo "<div class='progress'>";
                    echo "<div class='progress-bar $color' role='progressbar' style='width: ".$course["progress"]."%'>".
                         "</div>";
                    echo "</div></a>";
                }
                ?>
            </nav>
        </div>

        <div class="col-8 mt-3 pr-5">
            <h2 class="text-center font-weight-bold"><?php echo $course_name; ?></h2>

            <hr>
            <ul class="checklist">
                <li><?php make_check(0, $selected_id); ?></li>
            </ul>
            <hr>
            <ul class="checklist">
                <li><?php make_check(1, $selected_id); ?></li>
                <li class="conditional">
                    Does this course have a sandbox?<br>
                    <?php make_conditional("has_sandbox", "yes", $selected_id); ?>
                    <?php make_conditional("has_sandbox", "no", $selected_id); ?>
                </li>
            </ul>

            <hr>

            <h3 onclick="collapse('list-1');">Course Information</h3>
            <ul class="checklist collapse" id="list-1">
                <li><?php make_check(2, $selected_id); ?></li>
                <li><?php make_check(3, $selected_id); ?></li>
                <li class="ml-4"><?php make_check(4, $selected_id); ?></li>
                <li class="ml-4"><?php make_check(5, $selected_id); ?></li>
                <li class="conditional">
                    Has Examity Information<br>
                    <?php make_conditional("has_examity", "yes", $selected_id); ?>
                    <ul class="checklist" id="has_examity_yes">
                        <li><?php make_check(6, $selected_id); ?></li>
                    </ul>
                    <?php make_conditional("has_examity", "no", $selected_id); ?>
                </li>
            </ul>

            <hr>

            <h3 onclick="collapse('list-2');">Faculty Information</h3>
            <ul class="checklist collapse" id="list-2">
                <li><?php make_check(7, $selected_id); ?></li>
            </ul>

            <hr>

            <h3 onclick="collapse('list-3');">Learning Sessions</h3>
            <ul class="checklist collapse" id="list-3">
                <li><?php make_check(8, $selected_id); ?></li>
                <li><?php make_check(9, $selected_id); ?></li>
                <li><?php make_check(10, $selected_id); ?></li>
                <li class="ml-4"><?php make_check(11, $selected_id); ?></li>
                <li class="ml-4"><?php make_check(12, $selected_id); ?></li>
                <li class="ml-4"><?php make_check(13, $selected_id); ?></li>
                <li class="ml-5"><?php make_check(14, $selected_id); ?></li>
                <li><?php make_check(15, $selected_id); ?></li>
                <li><?php make_check(44, $selected_id); ?></li>
            </ul>

            <hr>

            <h3 onclick="collapse('list-4');">Discussion Board</h3>
            <ul class="checklist collapse" id="list-4">
                <li><?php make_check(16, $selected_id); ?></li>
                <li><?php make_check(17, $selected_id); ?></li>
                <li><?php make_check(18, $selected_id); ?></li>
                <li><?php make_check(19, $selected_id); ?></li>
            </ul>

            <hr>

            <h3 onclick="collapse('list-5');">Course Tools</h3>
            <ul class="checklist collapse" id="list-5">
                <li><h5>Date management</h5></li>
                <li><?php make_check(20, $selected_id); ?></li>
                <li class="ml-4"><?php make_check(21, $selected_id); ?></li>
                <li class="ml-4"><?php make_check(22, $selected_id); ?></li>
                <li><h5><br>Wikis</h5></li>
                <li class="conditional">
                    Are Wikis present?<br>
                    <?php make_conditional("has_wiki", "yes", $selected_id); ?>
                    <ul class="checklist" id="has_wiki_yes">
                        <li><?php make_check(23, $selected_id); ?></li>
                        <li><?php make_check(24, $selected_id); ?></li>
                    </ul>
                    <?php make_conditional("has_wiki", "no", $selected_id); ?>
                </li>
                <li><h5><br>Surveys</h5></li>
                <li class="conditional">
                    Does this course have Surveys?<br>
                    <?php make_conditional("has_surveys", "yes", $selected_id); ?>
                    <ul class="checklist" id="has_surveys_yes">
                        <li><?php make_check(46, $selected_id); ?></li>
                    </ul>
                    <?php make_conditional("has_surveys", "no", $selected_id); ?>
                </li>
            </ul>

            <hr>

            <h3 onclick="collapse('list-6');">Full Grade Center</h3>
            <ul class="checklist collapse" id="list-6">
                <li><?php make_check(25, $selected_id); ?></li>
                <li><?php make_check(26, $selected_id); ?></li>
                <li><?php make_check(27, $selected_id); ?></li>
                <li><?php make_check(28, $selected_id); ?></li>
                <li><?php make_check(29, $selected_id); ?></li>
                <li class="ml-4"><?php make_check(30, $selected_id); ?></li>
                <li><?php make_check(45, $selected_id); ?></li>
            </ul>

            <hr>

            <h3 onclick="collapse('list-7');">Users and Groups</h3>
            <ul class="checklist collapse" id="list-7">
                <li class="conditional">
                    Does this class have users and groups?<br>
                    <?php make_conditional("has_groups", "yes", $selected_id) ?>
                    <ul class="checklist" id="has_groups_yes">
                        <li><?php make_check(31, $selected_id); ?></li>
                        <li class="ml-4"><?php make_check(32, $selected_id); ?></li>
                        <li class="ml-4"><?php make_check(33, $selected_id); ?></li>
                        <li class="ml-4"><?php make_check(34, $selected_id); ?></li>
                        <li class="ml-4"><?php make_check(35, $selected_id); ?></li>
                    </ul>
                    <?php make_conditional("has_groups", "no", $selected_id); ?>
                </li>
            </ul>

            <hr>

            <h3 onclick="collapse('list-8');">Customization</h3>
            <ul class="checklist collapse" id="list-8">
                <li class="conditional">
                    Are there icons in this course?<br>
                    <?php make_conditional("has_icons", "yes", $selected_id); ?>
                    <ul class="checklist" id="has_icons_yes">
                        <li><?php make_check(36, $selected_id); ?></li>
                        <li><?php make_check(37, $selected_id); ?></li>
                    </ul>
                    <?php make_conditional("has_icons", "no", $selected_id) ?>
                    <ul class="checklist" id="has_icons_no">
                        <li><?php make_check(38, $selected_id); ?></li>
                    </ul>
                </li>
                <li class="conditional">
                    Does this course have "special" update requirements?<br>
                    <?php make_conditional("has_update_file", "yes", $selected_id) ?>
                    <ul class="checklist" id="has_update_file_yes">
                        <li><?php make_check(39, $selected_id); ?></li>
                    </ul>
                    <?php make_conditional("has_update_file", "no", $selected_id) ?>
                </li>
            </ul>

            <hr>

            <h3 onclick="collapse('list-9');">Ally</h3>
            <ul class="checklist collapse" id="list-9">
                <li class="conditional">
                    Does this course use Ally?<br>
                    <?php make_conditional("has_ally", "yes", $selected_id); ?>
                    <ul class="checklist" id="has_ally_yes">
                        <li><?php make_check(40, $selected_id); ?></li>
                    </ul>
                    <?php make_conditional("has_ally", "no", $selected_id); ?>
                </li>
            </ul>

            <hr>

            <h3 onclick="collapse('list-10');">When Copying Into a Live Course</h3>
            <ul class="checklist collapse" id="list-10">
                <li><?php make_check(41, $selected_id); ?></li>
                <li><?php make_check(42, $selected_id); ?></li>
                <li><?php make_check(43, $selected_id); ?></li>
            </ul>

            <h6>Notes</h6>
            <textarea rows="8" class="form-control" id="notes" onchange="setNotes(this.value);"><?php get_notes($selected_id); ?></textarea>
        </div>
    </div>
</body>

</html>