<?php
session_start();
include("../config/db.php");

/* DASHBOARD LINK BASED ON ROLE */

$dashboard_link = "../login.php";

if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] == "admin") {
        $dashboard_link = "../admin/dashboard.php";
    }

    if ($_SESSION['role'] == "teacher") {
        $dashboard_link = "../teacher/dashboard.php";
    }
}

$departments = $conn->query("SELECT * FROM departments");

if (isset($_POST['load_students'])) {

    $dept = $_POST['department_id'];
    $sem = $_POST['semester_id'];
    $subject = $_POST['subject_id'];
    $date = $_POST['date'];

    $students = $conn->query("
        SELECT id, register_number, student_name
        FROM students
        WHERE department_id='$dept'
        AND semester_id='$sem'
        ORDER BY student_name
    ");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Mark Attendance</title>

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
            padding: 25px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h2 {
            margin: 0;
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

        /* FORM CARD */
        .card {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* TABLE CARD */
        .table-card {
            margin-top: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        /* INPUTS */
        select,
        input {
            width: 100%;
            padding: 10px;
            margin: 6px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        /* BUTTON */
        button {
            margin-top: 15px;
            padding: 12px;
            width: 100%;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #219150;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        th {
            background: #2c3e50;
            color: white;
        }

        tr:hover {
            background: #f9f9f9;
        }
    </style>

</head>

<body>

    <div class="header">
        <h2>Mark Attendance</h2>
        <div>
            <a href="attendance_report.php">Daily Report</a>
            <a href="<?php echo $dashboard_link; ?>">Home</a>
        </div>
    </div>

    <!-- FORM -->
    <div class="main">
        <div class="card">

            <form method="POST">

                <label>Date</label>
                <input type="date" name="date" required>

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

                <button name="load_students">Load Students</button>

            </form>

        </div>
    </div>

    <!-- TABLE -->
    <?php if (isset($students)) { ?>

        <div class="main">
            <div class="table-card">

                <h3 style="margin-bottom:15px;">Student Attendance List</h3>

                <form method="POST" action="save_attendance.php">

                    <input type="hidden" name="date" value="<?php echo $date; ?>">
                    <input type="hidden" name="subject_id" value="<?php echo $subject; ?>">

                    <table>

                        <tr>
                            <th>ID</th>
                            <th>Reg No</th>
                            <th>Student Name</th>
                            <th>Present</th>
                            <th>Absent</th>
                        </tr>

                        <?php while ($row = $students->fetch_assoc()) { ?>

                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['register_number']; ?></td>
                                <td><?php echo $row['student_name']; ?></td>

                                <td>
                                    <input type="radio" name="status[<?php echo $row['id']; ?>]" value="Present" checked>
                                </td>

                                <td>
                                    <input type="radio" name="status[<?php echo $row['id']; ?>]" value="Absent">
                                </td>
                            </tr>

                        <?php } ?>

                    </table>

                    <button>Save Attendance</button>

                </form>

            </div>
        </div>

    <?php } ?>

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