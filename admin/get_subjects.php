<!-- AJAX for subjects -->

<?php
include("../config/db.php");

if(isset($_POST['semester_id'])){
    $semester_id = $_POST['semester_id'];
    $subjects = $conn->query("SELECT * FROM subjects WHERE semester_id='$semester_id' ORDER BY subject_name ASC");
    echo "<option value=''>Select Subject</option>";
    while($row = $subjects->fetch_assoc()){
        echo "<option value='".$row['id']."'>".$row['subject_name']."</option>";
    }
}
?>