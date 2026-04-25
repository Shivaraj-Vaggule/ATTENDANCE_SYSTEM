<?php
include("../config/db.php");

$date = $_POST['date'];
$subject = $_POST['subject_id'];

foreach ($_POST['status'] as $student_id => $status) {

    /* CHECK DUPLICATE */

    $check = $conn->query("
SELECT id FROM attendance
WHERE student_id='$student_id'
AND subject_id='$subject'
AND date='$date'
");

    if ($check->num_rows == 0) {

        $conn->query("
INSERT INTO attendance
(student_id,subject_id,date,status)
VALUES('$student_id','$subject','$date','$status')
");
    }
}

echo "<script>
alert('Attendance Saved Successfully');
window.location='mark_attendance.php';
</script>";
