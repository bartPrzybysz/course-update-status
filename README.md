# Checklists for updating courses semester to semester
## Setting Up
#### 1. Upload web folder to server
Can be renamed if desired

#### 2. Initialize database
Run **db/create_tables.sql** in a MySQL database

#### 3. Configure database connection
Open **php/config.php** and fill in the empty strings with your databse credentials

*It is best to not perform this step using ftp*

## Uploading Courses
#### 1. Create a course upload csv file
The file must contain the following headers in the following order:
 - course - Hyphen delimeted course code, can not be empty
 - section - A course section symbol
 - year - The year the class is to take place, can not be empty
 - term - Must be one of the following: "FALL", "SPRING", "SUMMER"
 - session - Must be one of the following: "OL", "OL-1", "OL-2", "HY"
 - notes - Optional notes regardging the class
 
 Example:
 
course | section | year | term | session | notes
------ | ------- | ---- | ---- | ------- | -----
TST-101 |  | 2019 | FALL | OL |
TST-230 | A | 2019 | FALL | OL-1 | This is the larger section
TST-230 | B | 2019 | FALL | OL-1 | This is the smaller section
TST-250 |  | 2020 | SPRING | OL-2 |
TST-306 | L | 2020 | SUMMER | HY | Lab times may change

*Note: If you are using Microsoft Excel to create the csv file, make sure you save as "CSV" not "CSV UTF-8"*

#### 2. Upload csv file
Navigate to the site's **dashboard.php** page, Scroll to the bottom and select your file.

## Creating a new task
#### 1. Add a new task row in the tasks table
*Note: the task id is not autoincremented and must be specified on assignement*
```SQL
INSERT INTO tasks(id, text) VALUES ( 47, ”Description of the new task.”)
```
#### 2. Add task to the checklist page
Open **course_update_status.php**
Add the follwing in the desired location on the page:
```php
<?php make_check(47, $selected_id); ?>
```

#### 3. Adjust progress calculation
Open the file **php/data.php**

In the **calculate_progress** function, locate the variable **$total_tasks**

11.	Add the number of checks being added to this value (this value should reflect the number of tasks listed in checklist)

## Adding a conditional task to the checklist
#### 1. Add a boolean column to courses table
Example:
```SQL
ALTER TABLE courses ADD conditiona_name TINYINT(1) NOT NULL DEFAULT '0' AFTER has_ally
```
#### 2. Add a new task row to the tasks table
```SQL
INSERT INTO tasks(id, text) VALUES ( 47, ”Description of the new task.”)
```

#### 3. Add conditional to checklist page
Open the file **course_update_status.php**
Add the following in the desired location on the page:
```html
<li class="conditional">
  Condition Description<br>
  <?php make_conditional("condition_name", "yes", $selected_id); ?>
  <?php make_conditional("condition_name", "no", $selected_id); ?>
</li>
```
If there are questions that should only appear if the conditional is checked “yes”, add a checklist `<ul>` element under the first conditional. This checklist should have the id of condition name with “_yes” appended to it. For example, has_blog_yes. If there are questions that should only appear if the conditional is checked “no”, place them in a checklist `<ul>` under the second conditional. This checklist should have an id of the condition name with “_no” appended to it: has_blog_no. The code should look like this:
```html
<li class="conditional">
  Condition Description<br>
  <?php make_conditional("condition_name", "yes", $selected_id); ?>
  <ul class=”checklist” id=”condition_name_yes”>
    <li><?php make_check(47, $selected_id); ?></li>
  </ul>
  <?php make_conditional("condition_name", "no", $selected_id); ?>
  <ul class=”checklist” id=”condition_name_no”>
    <li><?php make_check(48, $selected_id); ?></li>
  </ul>
</li>
```
#### 3. Adjust progress calculation
Open the file **php/data.php**

In the **calculate_progress** function, locate the variable **$local_tasks**
Add the number of checks being added to this value (this value should reflect the number of tasks listed in checklist)

Add the following code to adjust the progress bar calculation depending on the conditional
```php
If ($row[‘condition_name’]) {
	$total_tasks -= /*number of tasks in the “no” conditional*/ ;
} else {
	$total_tasks -= /*number of tasks in the “yes” conditional*/ ;
}
```
## Removing a task from the chcklist
#### 1. Remove the task from the checklist page
Open the file **course_update_status**

Locate the task you wish to remove and remove the followint code:

*Note remember the task id for the next step*
```php
<li><?php make_check(task id, $selected_id); ?></li>
```

#### 2. Delete the checks associated with the task
```SQL
DELETE FROM checks WHERE task_id=47 /*replace this number*/
```

#### 3. Delete the task from the tasks table
```SQL
DELETE FROM tasks WHERE id=47 /*replace this number*/
```
