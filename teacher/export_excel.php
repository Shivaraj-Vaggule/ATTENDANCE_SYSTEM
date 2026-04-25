<?php
include("../config/db.php");

/* VALIDATE REQUEST */
if (!isset($_POST['department']) || !isset($_POST['semester'])) {
    die("Invalid Request!");
}

$dept = intval($_POST['department']);
$sem  = intval($_POST['semester']);

/* FETCH NAMES */
$d = $conn->query("SELECT department_name FROM departments WHERE id=$dept");
$dept_data = $d->fetch_assoc();
$dept_name = $dept_data ? $dept_data['department_name'] : 'Unknown';

$s = $conn->query("SELECT semester_name FROM semesters WHERE id=$sem");
$sem_data = $s->fetch_assoc();
$sem_name = $sem_data ? $sem_data['semester_name'] : 'Unknown';

/* HEADERS */
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=attendance_report.xls");

/* PRINT TITLE */
echo "Department : $dept_name\n";
echo "Semester   : $sem_name\n\n";

/* FETCH SUBJECTS */
$subjects = [];
$subQuery = $conn->query("
    SELECT id, subject_name 
    FROM subjects
    WHERE department_id=$dept AND semester_id=$sem
");

while ($sub = $subQuery->fetch_assoc()) {
    $subjects[$sub['id']] = $sub['subject_name'];
}

/* TABLE HEADER */
echo "Reg No\tName\t";
foreach ($subjects as $name) {
    echo $name . "\t";
}
echo "\n";

/* FETCH STUDENTS */
$students = $conn->query("
    SELECT id, register_number, student_name 
    FROM students
    WHERE department_id=$dept AND semester_id=$sem
    ORDER BY student_name
");

/* LOOP STUDENTS */
while ($stu = $students->fetch_assoc()) {

    echo $stu['register_number'] . "\t";
    echo $stu['student_name'] . "\t";

    foreach ($subjects as $sid => $name) {

        /* GET ATTENDANCE */
        $q = $conn->query("
            SELECT 
                SUM(status='Present') as present,
                COUNT(*) as total
            FROM attendance
            WHERE student_id={$stu['id']}
            AND subject_id=$sid
        ");

        $r = $q->fetch_assoc();

        $percent = 0;
        if ($r && $r['total'] > 0) {
            $percent = ($r['present'] / $r['total']) * 100;
        }

        echo round($percent) . "%\t";
    }

    echo "\n";
}
?>