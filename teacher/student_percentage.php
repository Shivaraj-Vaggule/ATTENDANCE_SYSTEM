<?php
session_start();
include("../config/db.php");

/* FETCH DEPARTMENTS */
$departments = $conn->query("SELECT * FROM departments");

$students = [];
$subjects = [];
$dept_name = "";
$sem_name = "";

if (isset($_POST['view'])) {
    $dept = $_POST['department'];
    $sem  = $_POST['semester'];

    /* Department Name */
    $d = $conn->query("SELECT department_name FROM departments WHERE id='$dept'");
    $dept_name = $d->fetch_assoc()['department_name'];

    /* Semester Name */
    $s = $conn->query("SELECT semester_name FROM semesters WHERE id='$sem'");
    $sem_name = $s->fetch_assoc()['semester_name'];

    /* Subjects */
    $subQuery = $conn->query("
    SELECT * FROM subjects
    WHERE department_id='$dept' AND semester_id='$sem'
    ");
    while ($row = $subQuery->fetch_assoc()) {
        $subjects[] = $row;
    }

    /* Students */
    $students = $conn->query("
    SELECT * FROM students
    WHERE department_id='$dept' AND semester_id='$sem'
    ORDER BY student_name
    ");
}
?>

<html>

<head>

    <title>Attendance Percentage Report</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            margin: 0;
        }

        .container {
            width: 95%;
            margin: auto;
            margin-top: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        select,
        button {
            padding: 8px;
            margin: 5px;
        }

        button {
            background: #3498db;
            border: none;
            color: white;
            cursor: pointer;
        }

        .export {
            background: #27ae60;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #2c3e50;
            color: white;
        }

        .low {
            color: red;
            font-weight: bold;
        }

        .good {
            color: green;
            font-weight: bold;
        }

        .header {
            background: #ffe600;
            padding: 10px;
            font-size: 18px;
            font-weight: bold;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>

    <div class="container">

        <div class="card">

            <h2>Student Attendance Percentage</h2>

            <form method="POST">

                <select name="department" id="department" required>
                    <option value="">Select Department</option>

                    <?php while ($d = $departments->fetch_assoc()) { ?>

                        <option value="<?php echo $d['id']; ?>">
                            <?php echo $d['department_name']; ?>
                        </option>

                    <?php } ?>

                </select>


                <select name="semester" id="semester" required>
                    <option value="">Select Semester</option>
                </select>

                <button name="view">View Report</button>

                <button class="export" type="submit" name="export" formaction="export_excel.php">
    Export Excel
</button>

            </form>

            <?php if (!empty($subjects)) { ?>

                <div class="header">
                    Dept : <?php echo $dept_name ?> &nbsp;&nbsp;&nbsp;
                    Semester : <?php echo $sem_name ?>
                </div>

                <table>

                    <tr>
                        <th>Reg No</th>
                        <th>Name</th>

                        <?php foreach ($subjects as $sub) { ?>

                            <th><?php echo $sub['subject_name']; ?></th>

                        <?php } ?>

                    </tr>

                    <?php while ($stu = $students->fetch_assoc()) { ?>

                        <tr>

                            <td><?php echo $stu['register_number']; ?></td>
                            <td><?php echo $stu['student_name']; ?></td>

                            <?php
                            foreach ($subjects as $sub) {

                                $sid = $sub['id'];
                                $stuid = $stu['id'];

                                $q = $conn->query("
SELECT 
SUM(status='Present') as present,
COUNT(*) as total
FROM attendance
WHERE student_id='$stuid'
AND subject_id='$sid'
");

                                $r = $q->fetch_assoc();

                                $percent = 0;

                                if ($r['total'] > 0) {
                                    $percent = ($r['present'] / $r['total']) * 100;
                                }

                                $class = ($percent < 75) ? "low" : "good";
                            ?>

                                <td class="<?php echo $class; ?>">
                                    <?php echo round($percent); ?>%
                                </td>

                            <?php } ?>

                        </tr>

                    <?php } ?>

                </table>

            <?php } ?>

        </div>

    </div>

    <script>
        /* LOAD SEMESTERS */

        $("#department").change(function() {

            var dept_id = $(this).val();

            $.ajax({
                type: "POST",
                url: "../admin/get_semesters.php",
                data: {
                    department_id: dept_id
                },
                success: function(data) {
                    $("#semester").html(data);
                }
            });

        });
    </script>

</body>

</html>