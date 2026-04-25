<?php
session_start();
include("../config/db.php");

/* ADD ASSIGNMENT */
if (isset($_POST['assign'])) {

    $teacher_id = $_POST['teacher_id'];
    $department_id = $_POST['department_id'];
    $semester_id = $_POST['semester_id'];
    $subject_id = $_POST['subject_id'];

    $check = $conn->query("SELECT * FROM teacher_subjects
    WHERE teacher_id='$teacher_id'
    AND department_id='$department_id'
    AND semester_id='$semester_id'
    AND subject_id='$subject_id'");

    if ($check->num_rows > 0) {
        $_SESSION['message'] = "Already assigned";
        $_SESSION['msg_type'] = "error";
    } else {
        $conn->query("INSERT INTO teacher_subjects
        (teacher_id,department_id,semester_id,subject_id)
        VALUES('$teacher_id','$department_id','$semester_id','$subject_id')");

        $_SESSION['message'] = "Teacher assigned successfully";
        $_SESSION['msg_type'] = "success";
    }

    header("Location: assign_teacher.php");
    exit();
}

/* DELETE */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $conn->query("DELETE FROM teacher_subjects WHERE id='$id'");

    $_SESSION['message'] = "Assignment deleted";
    $_SESSION['msg_type'] = "success";

    header("Location: assign_teacher.php");
    exit();
}

/* FETCH */
$teachers = $conn->query("SELECT * FROM teachers ORDER BY name ASC");
$departments = $conn->query("SELECT * FROM departments ORDER BY department_name ASC");

$assignments = $conn->query("
SELECT ts.*,t.name as teacher_name,
d.department_name,
sem.semester_name,
s.subject_name
FROM teacher_subjects ts
JOIN teachers t ON ts.teacher_id=t.id
JOIN departments d ON ts.department_id=d.id
JOIN semesters sem ON ts.semester_id=sem.id
JOIN subjects s ON ts.subject_id=s.id
ORDER BY ts.id DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Assign Teacher</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        /* Google Font */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');


        /* RESET */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f4f6f9;
        }

        .container {
            padding: 20px 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #34495e;
            color: white;
            padding: 18px 25px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .btn {
            background: #3498db;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
        }

        .form-container {
            display: flex;
            justify-content: center;
        }

        .form-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            width: 450px;
            margin-bottom: 25px;
        }

        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .add {
            background: #27ae60;
            color: white;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .table-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        /* TABLE WRAPPER (IMPORTANT) */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        /* MAKE TABLE MIN WIDTH */
        table {
            min-width: 600px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #2c3e50;
            color: white;
        }

        /* DELETE */
        .delete-btn {
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
        }

        .delete-btn:hover {
            background: #c0392b;
        }


        /* MOBILE CARD VIEW */
        @media(max-width: 600px) {

            .container {
                padding: 20px 25px;
            }
        }

        @media(max-width: 560px) {
            table {
                min-width: 400px;
            }

            table thead {
                display: none;
            }

            table,
            table tbody,
            table tr,
            table td {
                display: block;
                width: 100%;
            }

            table tr {
                background: white;
                margin-bottom: 12px;
                border-radius: 10px;
                padding: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            }

            table td {
                text-align: right;
                padding-left: 10%;
                position: relative;
                border-bottom: none;
            }

            table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                font-weight: bold;
                text-align: left;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>

    <div class="header">
        <h2>Assign Teacher</h2>
        <a href="dashboard.php" class="btn">Dashboard</a>
    </div>

    <div class="container">

        <?php if (isset($_SESSION['message'])) { ?>
            <div id="flash-msg" class="<?php echo $_SESSION['msg_type']; ?>">
                <?php echo $_SESSION['message']; ?>
            </div>
        <?php
            unset($_SESSION['message']);
            unset($_SESSION['msg_type']);
        } ?>

        <div class="main-grid">

            <!-- FORM -->
            <div class="form-container">

                <div class="form-card">
                    <form method="POST">

                        <label>Teacher</label>
                        <select name="teacher_id" required>
                            <option value="">Select Teacher</option>
                            <?php while ($t = $teachers->fetch_assoc()) { ?>
                                <option value="<?= $t['id']; ?>"><?= $t['name']; ?></option>
                            <?php } ?>
                        </select>

                        <label>Department</label>
                        <select name="department_id" id="department_id" required>
                            <option value="">Select Department</option>
                            <?php while ($d = $departments->fetch_assoc()) { ?>
                                <option value="<?= $d['id']; ?>"><?= $d['department_name']; ?></option>
                            <?php } ?>
                        </select>

                        <label>Semester</label>
                        <select name="semester_id" id="semester_id" required>
                            <option value="">Select Semester</option>
                        </select>

                        <label>Subject</label>
                        <select name="subject_id" id="subject_id" required>
                            <option value="">Select Subject</option>
                        </select>

                        <button class="add" name="assign">Assign Teacher</button>
                    </form>
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-card">
                <div class="table-wrapper">
                    <table>

                        <thead>
                            <tr>
                                <th>Teacher</th>
                                <th>Department</th>
                                <th>Semester</th>
                                <th>Subject</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($row = $assignments->fetch_assoc()) { ?>
                                <tr>
                                    <td data-label="Teacher"><?= $row['teacher_name']; ?></td>
                                    <td data-label="Department"><?= $row['department_name']; ?></td>
                                    <td data-label="Semester"><?= $row['semester_name']; ?></td>
                                    <td data-label="Subject"><?= $row['subject_name']; ?></td>
                                    <td data-label="Action">
                                        <a class="delete-btn"
                                            href="?delete=<?= $row['id']; ?>"
                                            onclick="return confirm('Delete?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>

                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        /* LOAD SEMESTERS */
        $("#department_id").change(function() {
            var dept_id = $(this).val();

            $.post("get_semesters.php", {
                department_id: dept_id
            }, function(data) {
                $("#semester_id").html(data);
                $("#subject_id").html('<option value="">Select Subject</option>');
            });
        });

        /* LOAD SUBJECTS */
        $("#semester_id").change(function() {
            var sem_id = $(this).val();

            $.post("get_subjects.php", {
                semester_id: sem_id
            }, function(data) {
                $("#subject_id").html(data);
            });
        });

        /* AUTO HIDE MESSAGE */
        setTimeout(function() {
            var msg = document.getElementById('flash-msg');
            if (msg) {
                msg.style.opacity = "0";
                setTimeout(() => msg.remove(), 500);
            }
        }, 2000);
    </script>

</body>

</html>