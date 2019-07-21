#
# Migrate data from the old course_update_status table to new tables
#

import mysql.connector

conn = mysql.connector.connect(user='', 
                               password='', 
                               host='',
                               database='')
cursor = conn.cursor()

courses = list()
checks = list()

cursor.execute("SELECT * FROM course_update_status")

course_id = 1
for row in cursor:
    course = row[1:6] + row[8:15] + (row[-1],)
    courses.append(course)

    if row[6]:
        check = (course_id, 0, row[6], row[7])
        checks.append(check)
    
    col = 15

    for task_id in range(1, 45):
        if row[col]:
            check = (course_id, task_id, row[col], row[col+1])
            checks.append(check)
        
        col += 2

    course_id += 1

insert_courses_sql = "INSERT INTO courses(course, section, year, term, session, has_sandbox, has_examity, has_wiki, has_groups, has_icons, has_update_file, has_ally, notes) " + \
                     "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"

cursor.executemany(insert_courses_sql, courses)

insert_checks_sql = "INSERT INTO checks(course_id, task_id, name, date)" +\
                    "VALUES (%s, %s, %s, %s)"

cursor.executemany(insert_checks_sql, checks)

conn.commit()
cursor.close()
conn.close()
