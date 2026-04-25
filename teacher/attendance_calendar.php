<?php
include("../config/db.php");

$query = $conn->query("
SELECT 
attendance.date,
subjects.subject_name,
departments.department_name,
semesters.semester_name,
SUM(attendance.status='Present') as present,
SUM(attendance.status='Absent') as absent
FROM attendance
JOIN subjects ON attendance.subject_id = subjects.id
JOIN students ON attendance.student_id = students.id
JOIN departments ON students.department_id = departments.id
JOIN semesters ON students.semester_id = semesters.id
WHERE attendance.date = CURDATE()
GROUP BY departments.id, semesters.id, subjects.id
ORDER BY departments.department_name, semesters.id
");

$data = [];

while($row = $query->fetch_assoc()){
    $key = $row['department_name']." - ".$row['semester_name'];
    $data[$key][] = $row;
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Today's Attendance</title>

<style>

body{
font-family: Arial;
background:#f4f6f9;
margin:0;
}

.container{
width:95%;
margin:auto;
margin-top:30px;
}

h2{
text-align:center;
color:#2c3e50;
margin-bottom:25px;
}

.card-container{
display:flex;
flex-wrap:wrap;
gap:25px;
justify-content:center;
}

.card{
background:white;
padding:20px;
border-radius:8px;
box-shadow:0 2px 8px rgba(0,0,0,0.1);
width:400px;
}

.card h3{
margin-bottom:5px;
color:#34495e;
}

.card .date{
font-size:14px;
color:#7f8c8d;
margin-bottom:10px;
}

table{
width:100%;
border-collapse:collapse;
font-size:14px;
}

th{
background:#2c3e50;
color:white;
padding:8px;
}

td{
padding:8px;
text-align:center;
border-bottom:1px solid #eee;
}

tr:hover{
background:#f2f2f2;
}

.present{
color:green;
font-weight:bold;
}

.absent{
color:red;
font-weight:bold;
}

</style>

</head>

<body>

<div class="container">

<h2>Today's Attendance</h2>

<div class="card-container">

<?php foreach($data as $title => $rows){ ?>

<div class="card">

<h3><?php echo $title; ?></h3>

<div class="date">
Date : <?php echo date("d M Y"); ?>
</div>

<table>

<tr>
<th>Subject</th>
<th>Present</th>
<th>Absent</th>
</tr>

<?php foreach($rows as $r){ ?>

<tr>

<td><?php echo $r['subject_name']; ?></td>

<td class="present"><?php echo $r['present']; ?></td>

<td class="absent"><?php echo $r['absent']; ?></td>

</tr>

<?php } ?>

</table>

</div>

<?php } ?>

</div>

</div>

</body>

</html>