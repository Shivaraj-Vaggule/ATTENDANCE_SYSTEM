<?php
include("../config/db.php");

$departments = $conn->query("SELECT * FROM departments");

if (isset($_POST['view'])) {

    $dept = $_POST['department_id'];
    $sem = $_POST['semester_id'];
    $subject = $_POST['subject_id'];
    $month = $_POST['month'];

    $result = $conn->query("
SELECT students.register_number,
students.student_name,
SUM(status='Present') as present,
SUM(status='Absent') as absent,
COUNT(*) as total
FROM attendance
JOIN students ON attendance.student_id=students.id
WHERE students.department_id='$dept'
AND students.semester_id='$sem'
AND attendance.subject_id='$subject'
AND DATE_FORMAT(attendance.date,'%Y-%m')='$month'
GROUP BY students.id
ORDER BY students.student_name
");
}
?>

<html>

<head>

    <title>Monthly Attendance Report</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
       body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f6f9;
    margin: 0;
}

/* HEADER */
.header {
    background: #2c3e50;
    color: white;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header a {
    text-decoration: none;
    color: white;
    background: #3498db;
    padding: 8px 12px;
    border-radius: 5px;
    margin-left: 10px;
}

/* MAIN WRAPPER */
.main {
    max-width: 1100px;
    margin: 30px auto;
    padding: 0 15px;
}

/* CARD */
.card {
    background: white;
    width: 100%;
    max-width: 500px;
    margin: auto;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* FORM */
label {
    font-weight: bold;
}

select, input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

/* BUTTON */
button {
    width: 100%;
    background: #27ae60;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

button:hover {
    background: #219150;
}

/* TABLE CONTAINER */
.table-container {
    margin-top: 30px;
}

/* TABLE CARD */
.table-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    overflow-x: auto;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    text-align: center;
}

th {
    background: #34495e;
    color: white;
}

/* HOVER EFFECT */
tr:hover {
    background: #f9f9f9;
}
    </style>

</head>

<body>

    <div class="header">

        <h2>Monthly Attendance Report</h2>

        <div>
            <a href="dashboard.php">Home</a>
            <a href="mark_attendance.php">Mark Attendance</a>
            <a href="attendance_report.php">Daily Report</a>
            <a href="export_report.php">Excel Report</a>
        </div>

    </div>


    <div class="main">

        <div class="card">

            <form method="POST">

                <label>Department</label>

                <select name="department_id" id="department" required>

                    <option value="">Select Department</option>

                    <?php while ($d = $departments->fetch_assoc()) { ?>

                        <option value="<?php echo $d['id']; ?>">
                            <?php echo $d['department_name']; ?>
                        </option>

                    <?php } ?>

                </select>


                <label>Semester</label>

                <select name="semester_id" id="semester" required>

                    <option value="">Select Semester</option>

                </select>


                <label>Subject</label>

                <select name="subject_id" id="subject" required>

                    <option value="">Select Subject</option>

                </select>


                <label>Month</label>

                <input type="month" name="month" required>

                <br>

                <button name="view">View Report</button>

            </form>

        </div>
    </div>

    <div class="main table-container">


        <?php if (isset($result)) { ?>

            <div class="table-card">

                <table>

                    <tr>
                        <th>Reg No</th>
                        <th>Student Name</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Total</th>
                        <th>Percentage</th>
                    </tr>

                    <?php while ($row = $result->fetch_assoc()) {

                        $percent = 0;

                        if ($row['total'] > 0) {
                            $percent = ($row['present'] / $row['total']) * 100;
                        }

                    ?>

                        <tr>

                            <td><?php echo $row['register_number']; ?></td>
                            <td><?php echo $row['student_name']; ?></td>
                            <td><?php echo $row['present']; ?></td>
                            <td><?php echo $row['absent']; ?></td>
                            <td><?php echo $row['total']; ?></td>
                            <td><?php echo round($percent, 2); ?>%</td>

                        </tr>

                    <?php } ?>

                </table>

            </div>

        <?php } ?>


    </div>
    <script>
        /* LOAD SEMESTERS */

        $("#department").change(function() {

            var dept = $(this).val();

            $.ajax({

                type: "POST",
                url: "../admin/get_semesters.php",
                data: {
                    department_id: dept
                },
                success: function(data) {

                    $("#semester").html(data);

                }

            });

        });


        /* LOAD SUBJECTS */

        $("#semester").change(function() {

            var sem = $(this).val();
            var dept = $("#department").val();

            $.ajax({

                type: "POST",
                url: "../admin/get_subjects.php",
                data: {
                    semester_id: sem,
                    department_id: dept
                },
                success: function(data) {

                    $("#subject").html(data);

                }

            });

        });
    </script>

</body>

</html>