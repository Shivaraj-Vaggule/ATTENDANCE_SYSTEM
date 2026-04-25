<!-- AJAX for semesters -->

<?php
include("../config/db.php");

if(isset($_POST['department_id'])){
    $department_id = $_POST['department_id'];
    $semesters = $conn->query("SELECT * FROM semesters WHERE department_id='$department_id' ORDER BY semester_name ASC");
    echo "<option value=''>Select Semester</option>";
    while($row = $semesters->fetch_assoc()){
        echo "<option value='".$row['id']."'>".$row['semester_name']."</option>";
    }
}
?>