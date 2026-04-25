<?php
session_start();
include("../config/db.php");

/* DASHBOARD LINK */
$dashboard_link = "../login.php";
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == "admin") $dashboard_link = "../admin/dashboard.php";
    if ($_SESSION['role'] == "teacher") $dashboard_link = "../teacher/dashboard.php";
}

/* DELETE */
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM subjects WHERE id='$delete_id'");
    $_SESSION['message'] = "Subject deleted successfully";
    $_SESSION['msg_type'] = "success";
    header("Location: subjects.php");
    exit();
}

/* ADD */
if (isset($_POST['add_subject'])) {

    $department_id = $_POST['department_id'];
    $semester_id = $_POST['semester_id'];
    $subject_name = trim($_POST['subject_name']);

    if ($semester_id == "" || $department_id == "" || $subject_name == "") {
        $_SESSION['message'] = "Please fill all fields";
        $_SESSION['msg_type'] = "error";
    } else {

        $check = $conn->query("SELECT * FROM subjects 
        WHERE department_id='$department_id' 
        AND semester_id='$semester_id' 
        AND subject_name='$subject_name'");

        if ($check->num_rows > 0) {
            $_SESSION['message'] = "Subject already exists";
            $_SESSION['msg_type'] = "error";
        } else {
            $conn->query("INSERT INTO subjects(department_id, semester_id, subject_name)
            VALUES('$department_id','$semester_id','$subject_name')");

            $_SESSION['message'] = "Subject added successfully";
            $_SESSION['msg_type'] = "success";
        }
    }

    header("Location: subjects.php");
    exit();
}

$departments = $conn->query("SELECT * FROM departments ORDER BY department_name ASC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Subjects Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
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

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            flex-wrap: wrap;
        }

        .header h2 {
            font-size: 20px;
        }

        .header a {
            background: #3498db;
            color: white;
            padding: 8px 14px;
            border-radius: 5px;
            text-decoration: none;
        }

        /* CONTAINER */
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
        }

        /* FORM */
        .form-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .form-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 500px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            border: none;
            background: #27ae60;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }

        /* ALERT */
        .success,
        .error {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .success {
            background: #d4edda;
        }

        .error {
            background: #f8d7da;
        }

        /* GRID */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        /* CARD */
        .table-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
        }

        .table-card h3 {
            text-align: center;
            padding: 12px;
            font-size: 15px;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #041e38a6;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        /* DELETE BUTTON */
        .delete-btn {
            background: #e74c3c;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
        }

        .delete-btn:hover {
            background: #c0392b;
        }

        /* RESPONSIVE */
        @media(max-width: 992px) {
            .grid-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width: 600px) {
            .grid-container {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 10px;
            }

            .header a {
                width: 100%;
                text-align: center;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

    <div class="header">
        <h2>Subjects Management</h2>
        <a href="<?php echo $dashboard_link; ?>">Dashboard</a>
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

        <div class="form-container">
            <div class="form-card">
                <form method="POST">

                    <label>Department</label>
                    <select name="department_id" id="department_id" required>
                        <option value="">Select</option>
                        <?php while ($d = $departments->fetch_assoc()) { ?>
                            <option value="<?= $d['id'] ?>"><?= $d['department_name'] ?></option>
                        <?php } ?>
                    </select>

                    <label>Semester</label>
                    <select name="semester_id" id="semester_id" required>
                        <option value="">Select</option>
                    </select>

                    <label>Subject</label>
                    <input type="text" name="subject_name" required>

                    <button name="add_subject">Add Subject</button>
                </form>
            </div>
        </div>

        <div class="grid-container">

            <?php
            $deptList = $conn->query("SELECT * FROM departments");

            while ($dept = $deptList->fetch_assoc()) {

                $semesters = $conn->query("SELECT * FROM semesters WHERE department_id='{$dept['id']}'");

                while ($sem = $semesters->fetch_assoc()) {

                    $subjects = $conn->query("SELECT * FROM subjects 
                    WHERE department_id='{$dept['id']}' 
                    AND semester_id='{$sem['id']}'");

                    if ($subjects->num_rows > 0) {
            ?>

                        <div class="table-card">
                            <h3>🎓 <?= $dept['department_name'] . " - " . $sem['semester_name']; ?></h3>

                            <table>
                                <tr>
                                    <th>Subject</th>
                                    <th>Action</th>
                                </tr>

                                <?php while ($row = $subjects->fetch_assoc()) { ?>
                                    <tr>
                                        <td>📘 <?= $row['subject_name']; ?></td>
                                        <td>
                                            <a class="delete-btn" href="?delete=<?= $row['id']; ?>" onclick="return confirm('Delete?')">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>

                            </table>
                        </div>

            <?php }
                }
            } ?>

        </div>

    </div>

    <script>
        $("#department_id").change(function() {
            var dept_id = $(this).val();

            if (dept_id) {
                $.ajax({
                    type: 'POST',
                    url: 'get_semesters.php',
                    data: {
                        department_id: dept_id
                    },
                    success: function(html) {
                        $("#semester_id").html(html);
                    }
                });
            }
        });

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