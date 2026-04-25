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

if (isset($_POST['view'])) {

    $dept = $_POST['department'];
    $sem  = $_POST['semester'];

    /* SUBJECTS */
    $subjects = $conn->query("
        SELECT * FROM subjects 
        WHERE department_id='$dept' AND semester_id='$sem'
    ");

    /* STUDENTS */
    $students = $conn->query("
        SELECT * FROM students 
        WHERE department_id='$dept' AND semester_id='$sem'
        ORDER BY student_name
    ");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Excel Report</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
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

        .header h2 {
            margin: 0;
        }

        .header a {
            text-decoration: none;
            color: white;
            background: #3498db;
            padding: 8px 14px;
            border-radius: 6px;
        }

        /* MAIN WRAPPER */
        .container {
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
        button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        /* BUTTON */
        button {
            background: #27ae60;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #219150;
        }

        /* EXPORT BUTTON */
        .export-btn {
            background: #3498db;
            margin-top: 15px;
        }

        .export-btn:hover {
            background: #2c80b4;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        th {
            background: #34495e;
            color: white;
        }

        tr:hover {
            background: #f9f9f9;
        }

        /* TITLE */
        .table-title {
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>Excel Attendance Report</h2>
        <a href="<?php echo $dashboard_link; ?>">Home</a>

    </div>

    <div class="container">

        <!-- FORM -->
        <div class="card">

            <form method="POST">

                <label>Department</label>
                <select name="department" id="department" required>
                    <option value="">Select</option>
                    <?php while ($d = $departments->fetch_assoc()) { ?>
                        <option value="<?php echo $d['id']; ?>">
                            <?php echo $d['department_name']; ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Semester</label>
                <select name="semester" id="semester" required>
                    <option value="">Select</option>
                </select>

                <button name="view">View Report</button>

            </form>

        </div>

        <!-- TABLE -->
        <?php if (isset($students)) { ?>

            <div class="table-card">
                <div class="table-title">Attendance Percentage Report</div>

                <table>
                    <tr>
                        <th>Reg No</th>
                        <th>Name</th>

                        <?php
                        $subArr = [];
                        while ($sub = $subjects->fetch_assoc()) {
                            $subArr[] = $sub;
                            echo "<th>" . $sub['subject_name'] . "</th>";
                        }
                        ?>
                    </tr>

                    <?php while ($stu = $students->fetch_assoc()) { ?>

                        <tr>
                            <td><?php echo $stu['register_number']; ?></td>
                            <td><?php echo $stu['student_name']; ?></td>

                            <?php foreach ($subArr as $sub) {

                                $q = $conn->query("
                        SELECT 
                        SUM(status='Present') as present,
                        COUNT(*) as total
                        FROM attendance
                        WHERE student_id='{$stu['id']}'
                        AND subject_id='{$sub['id']}'
                    ");

                                $r = $q->fetch_assoc();
                                $percent = ($r['total'] > 0) ? ($r['present'] / $r['total']) * 100 : 0;

                                echo "<td>" . round($percent) . "%</td>";
                            } ?>

                        </tr>

                    <?php } ?>

                </table>

                <!-- EXPORT BUTTON -->
                <form method="POST" action="export_excel.php">
                    <input type="hidden" name="department" value="<?php echo $dept; ?>">
                    <input type="hidden" name="semester" value="<?php echo $sem; ?>">
                    <button class="export-btn">Export to Excel</button>
                </form>

            </div>

        <?php } ?>

    </div>

    <!-- AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
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
    </script>

</body>

</html>