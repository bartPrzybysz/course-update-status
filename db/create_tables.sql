/*

    Create the tables needed for course_update_status checklist

*/


CREATE TABLE courses (
    id              INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    course          VARCHAR(16) COLLATE utf8_unicode_ci NOT NULL,
    section         VARCHAR(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
    year            YEAR(4) NOT NULL,
    term            ENUM('FALL','SPRING','SUMMER') COLLATE utf8_unicode_ci NOT NULL,
    session         ENUM('OL','OL-1','OL-2','HY') COLLATE utf8_unicode_ci NOT NULL,
    has_sandbox     TINYINT(1) NOT NULL DEFAULT '0', 
    has_examity     TINYINT(1) NOT NULL DEFAULT '0', 
    has_surveys     TINYINT(1) NOT NULL DEFAULT '0',
    has_wiki        TINYINT(1) NOT NULL DEFAULT '0', 
    has_groups      TINYINT(1) NOT NULL DEFAULT '0',
    has_icons       TINYINT(1) NOT NULL DEFAULT '1', 
    has_update_file TINYINT(1) NOT NULL DEFAULT '0', 
    has_ally        TINYINT(1) NOT NULL DEFAULT '0', 
    notes           VARCHAR(2048) COLLATE utf8_unicode_ci DEFAULT NULL,

    UNIQUE KEY unique_course (course, section, year, term, session)
) AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;





CREATE TABLE tasks (
     id     INT(11) NOT NULL PRIMARY KEY,
     text   VARCHAR(2048) COLLATE utf8_unicode_ci NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO tasks VALUES
(0, "This course is ready. (Force Green Light)"),
(1, "Copy most recent course offering (by the course creator) into sandbox (Need Admin account)<br>(Course Management &rarr; Packages and Utilities &rarr; Copy Course &rarr; Copy Course Materials into a New Course &rarr; Destination Course ID should be SND-Course#-Term)"),
(2, "Update Syllabus (If this is a Grad Theology or Grad Cat course, you may be able to skip this step. Check syllabus to make sure it’s the new generic format)"),
(3, "Update Course Calendar (These are housed in the main content collection &rarr; Institution Content &rarr; Course Calendars &rarr; Click down arrow next to calendar name and then click on “Edit in blackboard” You will need Admin Privileges)"),
(4, "Keep in mind Spring Break, Thanksgiving Break, Etc. and move assignments & due dates accordingly"),
(5, "Check that \“print\” works on course calendar and reference the icon code cheat sheet for instructions to fix if it does not."),
(6, "Adaptive release content based on checking off that they registered in review status. (Require this approximately 2 weeks in advance)"),
(7, "Creating Professor AND any Teaching Professor or Teaching Assistant Information is showing. (Faculty information can be found in content collection as HTML objects and should be added the same way we do calendars)"),
(8, "Check that all items are \“available\”"),
(9, "Ensure that individual items do not have specific dates. For Example: should say Monday of Week 4 instead of February 4th"),
(10, "Do links work?"),
(11, "Do tool links open in this course? (look at course title in upper left hand corner)"),
(12, "Do links open in a new tab when linking to outside resources?"),
(13, "Are Gradebook Items (such as Journals, Discussion Boards, etc.) uniquely titled?"),
(14, "Should say Post on Week 1 Discussion Board (or title of the discussion board) instead of just \“Post on the Discussion Board\” every week so that date management isn’t confusing."),
(15, "Are all links blue? (If not run BBbot on the course) (note: links to publisher resources cannot always be turned blue)"),
(44, "Make sure that all file names include their filetype extension (.pdf, .docx, .ppt)"),
(16, "Ensure forums are copied"),
(17, "Check to see if starter posts copied over"),
(18, "Delete any previous student posts that were copied over"),
(19, "Unlock Discussion boards if they say \“locked\” under published"),
(20, "Update Dates in Date Management (Course Management &rarr; Course Tools &rarr; Date Management &rarr; Use Term info &rarr; Run)"),
(21, "Compare Date Management with the Course Calendar above to ensure dates match up."),
(22, "Ensure that adaptive release dates “make sense” with due dates (I.E. due dates are within the adaptive release)"),
(23, "Is the professor using the Wiki as a sign-up sheet? If so, go to previous semester and copy the html of the sign-up sheet, create wiki page on the current course and paste into the html."),
(24, "Tell Liz if a course has Wiki sign-ups"),
(25, "Delete extra weighted total column (click down arrow beside column &rarr; edit column information &rarr; see if there is information under the weighting, if there is leave this column and delete the other \“Weighted total\” column)"),
(26, "Delete extra total column"),
(27, "Ensure that weighted total aligns with the syllabus"),
(28, "Ensure that columns appear like previous semester"),
(29, "Does the Grading Schema match the syllabus (Manage &rarr; Grading Schemas &rarr; Ones without checkboxes next to them are in use)"),
(30, "Delete extra ones that aren’t in use (if they have a check box next to them they’re not being used)"),
(31, "Make groups be self-enroll groups and link to them in start here"),
(32, "Adaptive release content based on group enrollment (only course information and faculty information viewable)"),
(33, "Update links to groups within the weeks to be to these new groups"),
(34, "Alert Liz to email professor to tell them the difference"),
(35, "If Professor is utilizing groups for Discussion Boards, Update links in weeks directly to the group discussion board."),
(36, "Is this a Grad CAT or THE Course? (Remove Icons using BBbot and go to \“no\”)"),
(37, "All other courses, ensure that Checklist icon is removed (Run BBbot if not)"),
(38, "Go to Teaching Style &rarr; Default Content View and select \“Text Only\”"),
(39, "Create or update File in Content collection called \“e-learning update\” that lists anything weird in the course that needs updated semester to semester."),
(40, "Fix issues Ally finds (When time permits) (Liz or Kathy will have to \“turn on\” Ally in the sandbox for you)"),
(41, "Delete former send e-mail and replace with the new send e-mail. (New send e-mail is generally at the bottom of the list)"),
(42, "Reorganize the left side to be how the instructor had it (if they customized)"),
(43, "Update any Teaching Professor or Teaching Assistant Information"),
(45, "Remove duplicate custom smartviews"),
(46, "Make sure they are not date specific. If they are, email instructor.");





CREATE TABLE checks (
    course_id   INT(11) NOT NULL,
    task_id     INT(11) NOT NULL,
    name        VARCHAR(32) COLLATE utf8_unicode_ci DEFAULT "",
    date        DATE NOT NULL,

    PRIMARY KEY (course_id, task_id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (task_id) REFERENCES tasks(id),
    UNIQUE KEY unique_check (course_id, task_id)
) AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;