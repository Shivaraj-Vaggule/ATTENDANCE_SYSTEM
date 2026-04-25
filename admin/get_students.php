<!-- AJAX for students -->

<?php
include("../config/db.php");

if(isset($_POST['department_id']) && isset($_POST['semester_id'])){
    $department_id = $_POST['department_id'];
    $semester_id = $_POST['semester_id'];
    $students = $conn->query("SELECT * FROM students WHERE department_id='$department_id' AND semester_id='$semester_id' ORDER BY student_name ASC");
    echo "<option value=''>Select Student</option>";
    while($row = $students->fetch_assoc()){
        echo "<option value='".$row['id']."'>".$row['student_name']."</option>";
    }
}
?>