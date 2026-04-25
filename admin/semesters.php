<?php
session_start();
include("../config/db.php");

/* DASHBOARD LINK */
$dashboard_link = "../login.php";

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == "admin") $dashboard_link = "../admin/dashboard.php";
    if ($_SESSION['role'] == "teacher") $dashboard_link = "../teacher/dashboard.php";
}

/* ADD SEMESTER */
if (isset($_POST['add'])) {

    $dept = $_POST['department_id'];
    $sem = $_POST['semester_name'];

    $check = $conn->query("SELECT * FROM semesters 
        WHERE department_id='$dept' AND semester_name='$sem'");

    if ($check->num_rows > 0) {
        $_SESSION['message'] = "Semester already exists";
        $_SESSION['msg_type'] = "error";
    } else {
        $conn->query("INSERT INTO semesters(department_id,semester_name)
            VALUES('$dept','$sem')");
        $_SESSION['message'] = "Semester added successfully";
        $_SESSION['msg_type'] = "success";
    }

    header("Location: semesters.php");
    exit();
}

/* DELETE */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM semesters WHERE id=$id");

    $_SESSION['message'] = "Semester deleted successfully";
    $_SESSION['msg_type'] = "success";

    header("Location: semesters.php");
    exit();
}

$departments = $conn->query("SELECT * FROM departments");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Semester Management</title>
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
            /* font-family: Arial, Helvetica, sans-serif; */
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            flex-wrap: wrap;
        }

        .btn {
            background: #3498db;
            color: white;
            padding: 8px 14px;
            border-radius: 5px;
            text-decoration: none;
        }

        .container {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
        }

        /* FORM CENTER */
        .form-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .form-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
        }

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 10px;
            border: none;
            background: #27ae60;
            color: white;
            border-radius: 6px;
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

        /* GRID LAYOUT */
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
            background: #34495e;
            color: white;
            padding: 12px;
            font-size: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #ecf0f1;
            padding: 10px;
            text-align: center;
        }

        td {
            text-align: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f1f5ff;
        }

        /* DELETE BUTTON */
        .delete-btn {
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
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

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>

</head>

<body>

    <div class="header">
        <h2>Semester Management</h2>
        <a href="<?php echo $dashboard_link; ?>" class="btn">Dashboard</a>
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

        <!-- FORM -->
        <div class="form-wrapper">
            <div class="form-card">
                <form method="POST">

                    <label>Department</label>
                    <select name="department_id" required>
                        <option value="">Select Department</option>
                        <?php while ($d = $departments->fetch_assoc()) { ?>
                            <option value="<?= $d['id']; ?>">
                                <?= $d['department_name']; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <label>Semester</label>
                    <select name="semester_name" required>
                        <option value="">Select Semester</option>
                        <?php for ($i = 1; $i <= 8; $i++) { ?>
                            <option value="Semester <?= $i ?>">Semester <?= $i ?></option>
                        <?php } ?>
                    </select>

                    <button name="add">Add Semester</button>
                </form>
            </div>
        </div>

        <!-- GRID START -->
        <div class="grid-container">

            <?php
            $deptList = $conn->query("SELECT * FROM departments");

            while ($dept = $deptList->fetch_assoc()) {

                $semesters = $conn->query("SELECT * FROM semesters 
                WHERE department_id='{$dept['id']}'");

                if ($semesters->num_rows > 0) {
            ?>

                    <div class="table-card">
                        <h3>Department :- <?= $dept['department_name']; ?></h3>

                        <table>
                            <tr>
                                <th>Semester</th>
                                <th>Action</th>
                            </tr>

                            <?php while ($row = $semesters->fetch_assoc()) { ?>
                                <tr>
                                    <td><?= $row['semester_name']; ?></td>
                                    <td>
                                        <a class="delete-btn"
                                            href="?delete=<?= $row['id']; ?>"
                                            onclick="return confirm('Delete this semester?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>

                        </table>
                    </div>

            <?php }
            } ?>

        </div>
        <!-- GRID END -->

    </div>

    <script>
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