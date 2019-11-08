<!DOCTYPE html>
<html>

<head>
    <title>Manager Dashboard</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/dashboard.css">

    <?php require_once("php/data.php");

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
            case 10: case 11: case 12: 
                $display_year = strval(intval($display_year) + 1);
            case 1:
                $display_term = "SPRING";
                break;
            default:
                $display_term = "FALL";
                break;
        }
    } else {
        $display_term = $_GET["term"];
    }
    ?>

    <script src="js/dashboard.js"></script>
    <script>
        var sectionCount = <?php echo json_encode(get_section_count($display_year, $display_term)); ?>;
        var courseCount = <?php echo json_encode(get_course_count($display_year, $display_term)); ?>;

        urlVars.year = <?php echo $display_year; ?>;
        urlVars.term = "<?php echo $display_term; ?>";
    </script>
</head>

<body onload="populateTable(sectionCount);">
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
            </div>
        </div>
        <div class="col-4 align-self-center">
            <h4>Admin Dashboard</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <table class="table table-bordered table-hover mt-3 ml-3 text-center">
                <tr>
                    <th colspan="5">
                        <h3>Semester Status</h3>
                        <div class="btn-group">
                            <button id="show_all" type="button" class="btn btn-success" onclick="showAll(sectionCount);">
                                All Sections
                            </button>
                            <button id="show_distinct" type="button" class="btn" onclick="showDistinct(courseCount);">
                                Distinct Courses
                            </button>
                        </div>
                        <br>
                        <div class="progress mt-4" style="border: 1px solid lightgray;">
                            <div id="complete-bar" class="progress-bar bg-success" role="progressbar"></div>
                            <div id="inprogress-bar" class="progress-bar bg-warning" role="progressbar"></div>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th></th>
                    <th scope="col">Unstarted</th>
                    <th scope="col">In Progress</th>
                    <th socpe="col">Completed</th>
                    <th scope="col">Total</th>
                </tr>
                <tr>
                    <th scope="row">OL</th>
                    <td row="OL" col="unstarted"></td>
                    <td row="OL" col="inprogress"></td>
                    <td row="OL" col="completed"></td>
                    <td row="OL" col="total"></td>
                </tr>
                <tr>
                    <th scope="row">OL-1</th>
                    <td row="OL1" col="unstarted"></td>
                    <td row="OL1" col="inprogress"></td>
                    <td row="OL1" col="completed"></td>
                    <td row="OL1" col="total"></td>
                </tr>
                <tr>
                    <th scope="row">OL-2</th>
                    <td row="OL2" col="unstarted"></td>
                    <td row="OL2" col="inprogress"></td>
                    <td row="OL2" col="completed"></td>
                    <td row="OL2" col="total"></td>
                </tr>
                <tr>
                    <th scope="row">HY</th>
                    <td row="HY" col="unstarted"></td>
                    <td row="HY" col="inprogress"></td>
                    <td row="HY" col="completed"></td>
                    <td row="HY" col="total"></td>
                </tr>
                <tr>
                    <th scope="row">Total</th>
                    <td row="total" col="unstarted"></td>
                    <td row="total" col="inprogress"></td>
                    <td row="total" col="completed"></td>
                    <td row="total" col="total"></td>
                </tr>
            </table>
        </div>

        <div class="col-6">
            <nav class="nav nav-tabs justify-content-center">
                <a id="examity_tab" class="nav-link active" href="javascript:showExamity();">Examity Courses</a>
                <a id="ally_tab" class="nav-link" href="javascript:showAlly();">Ally Courses</a>
                <a id="sandbox_tab" class="nav-link" href="javascript:showSandbox();">Sandbox Courses</a>
            </nav>
            <div class="toggle-div active" id="examity_courses">
                <div class="list-group mt-3 pr-4">
                    <?php
                    $courses = get_courses_by_condition($display_year, $display_term, 'has_examity');

                    // generate html for list of courses
                    foreach ($courses as $crs) {
                        $url = "course_update_status.php?id=".$crs['id']."&year=$display_year&term=$display_term";
                        echo "<a href='$url' target='_blank' class='list-group-item list-group-item-action'>". 
                             $crs['name']."</a>";
                    }
                    ?>
                </div>
            </div>
            <div class="toggle-div" id="ally_courses">
                <div class="list-group mt-3 pr-4">
                    <?php
                    $courses = get_courses_by_condition($display_year, $display_term, 'has_ally');

                    foreach ($courses as $crs) {
                        $url = "course_update_status.php?id=".$crs['id']."&year=$display_year&term=$display_term";
                        echo "<a href='$url' target='_blank' class='list-group-item list-group-item-action'>". 
                                $crs['name']."</a>";
                    }
                    ?>
                </div>
            </div>
            <div class="toggle-div" id="sandbox_courses">
                <div class="list-group mt-3 pr-4">
                    <?php
                    $courses = get_courses_by_condition($display_year, $display_term, 'has_sandbox');

                    foreach ($courses as $crs) {
                        $url = "course_update_status.php?id=".$crs['id']."&year=$display_year&term=$display_term";
                        echo "<a href='$url' target='_blank' class='list-group-item list-group-item-action'>". 
                             $crs['name']."</a>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-3"></div>
        <div class="col-6">
            <form action="php/upload.php" target="_blank" method="POST" enctype="multipart/form-data">
                <h3 class="text-center mt-5">Upload Courses</h3>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <input type="submit" id="submit" name="import" value="Upload" class="btn btn-primary input-group-text">
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file" name="file" accept=".csv"
                            onchange="setFileLabel(this.value);">
                        <label class="custom-file-label" for="file" id="file_label">Choose File</label>
                    </div>
                </div>
            </form>
            <div class="text-center mt-3 mb-5">
                <h6>Upload format</h6>
                <img src="img/SampleImport.PNG" alt="Sample Input in Excel" class="rounded">
            </div>
        </div>
    </div>
</body>


</html>
