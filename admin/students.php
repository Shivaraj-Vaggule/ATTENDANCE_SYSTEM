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

/* ADD STUDENT */
if (isset($_POST['add'])) {

    $name = trim($_POST['student_name']);
    $reg = trim($_POST['register_number']);
    $email = trim($_POST['email']);
    $dept = $_POST['department_id'];
    $sem = $_POST['semester_id'];

    $photoName = "";

    if (!empty($_FILES['photo']['name'])) {
        $photoName = time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "../uploads/" . $photoName);
    }

    /* DUPLICATE CHECK */
    $check = $conn->query("SELECT * FROM students WHERE register_number='$reg' OR email='$email'");

    if ($check->num_rows > 0) {
        $_SESSION['message'] = "Register number or Email already exists";
        $_SESSION['msg_type'] = "error";
    } else {

        $conn->query("INSERT INTO students(student_name,register_number,email,photo,department_id,semester_id)
VALUES('$name','$reg','$email','$photoName','$dept','$sem')");

        $_SESSION['message'] = "Student added successfully";
        $_SESSION['msg_type'] = "success";
    }

    header("Location: students.php");
    exit();
}


/* DELETE STUDENT */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $conn->query("DELETE FROM students WHERE id='$id'");

    $_SESSION['message'] = "Student deleted successfully";
    $_SESSION['msg_type'] = "success";

    header("Location: students.php");
    exit();
}


/* FETCH DEPARTMENTS */
$departments = $conn->query("SELECT * FROM departments ORDER BY department_name ASC");


/* VIEW STUDENTS */
$students = null;
$header_text = "";

if (isset($_POST['view'])) {

    $dept = $_POST['view_department'];
    $sem = $_POST['view_semester'];

    $students = $conn->query("
SELECT students.*, departments.department_name, semesters.semester_name
FROM students
JOIN departments ON students.department_id=departments.id
JOIN semesters ON students.semester_id=semesters.id
WHERE students.department_id='$dept' AND students.semester_id='$sem'
ORDER BY students.student_name ASC
");

    $rowHeader = $students->fetch_assoc();
    if ($rowHeader) {
        $header_text = $rowHeader['department_name'] . " - " . $rowHeader['semester_name'];
        $students->data_seek(0);
    }
}

?>

<html>

<head>

    <title>Students Management</title>

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


        .card-container {
            padding: 50px;
            display: flex;
            gap: 50px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            flex: 1;
            min-width: 300px;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .add {
            background: #27ae60;
            color: white;
        }

        .view {
            background: #3498db;
            color: white;
        }

        .delete {
            background: #e74c3c;
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        th {
            background: #2c3e50;
            color: white;
        }

        .student-photo {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
        }
        /* ================= MOBILE RESPONSIVE ================= */

@media (max-width: 992px) {
    .card-container {
        padding: 30px 60px;
        gap: 40px;
    }

    .card {
        flex: 1 1 100%;
    }
}

@media (max-width: 768px) {

    /* Header */
    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .header h2 {
        font-size: 20px;
    }

    .header a {
        padding: 6px 10px;
        font-size: 14px;
    }

    /* Cards */
    .card-container {
        padding: 15px;
        gap: 15px;
    }

    .card {
        padding: 15px;
    }

    /* Inputs */
    input, select {
        font-size: 14px;
        padding: 10px;
    }

    button {
        width: 100%;
        padding: 10px;
        font-size: 14px;
    }

    /* Table Responsive */
    .table-card {
        padding: 10px;
        overflow-x: auto;
    }

    table {
        min-width: 600px;
    }

    th, td {
        font-size: 13px;
        padding: 8px;
    }

    .student-photo {
        width: 35px;
        height: 35px;
    }
}

@media (max-width: 480px) {

    .header h2 {
        font-size: 18px;
    }

    .card h3 {
        font-size: 16px;
    }

    th, td {
        font-size: 12px;
    }

    button {
        font-size: 13px;
    }
}
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>

<body>

    <div class="header">
        <h2>Students Management</h2>
        <a href="<?php echo $dashboard_link; ?>">Home</a>
    </div>


    <?php
    if (isset($_SESSION['message'])) {
        echo "<div id='flash-msg' class='{$_SESSION['msg_type']}'>{$_SESSION['message']}</div>";
        unset($_SESSION['message']);
        unset($_SESSION['msg_type']);
    }
    ?>


    <div class="card-container">


        <!-- ADD STUDENT -->

        <div class="card">

            <h3>Add Student</h3>

            <form method="POST" enctype="multipart/form-data">

                <label>Register Number</label>
                <input type="text" name="register_number" required>

                <label>Student Name</label>
                <input type="text" name="student_name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Student Photo</label>
                <input type="file" name="photo">

                <label>Department</label>
                <select name="department_id" id="department_id" required>

                    <option value="">Select Department</option>

                    <?php while ($d = $departments->fetch_assoc()) { ?>

                        <option value="<?php echo $d['id']; ?>">
                            <?php echo $d['department_name']; ?>
                        </option>

                    <?php } ?>

                </select>

                <label>Semester</label>

                <select name="semester_id" id="semester_id" required>
                    <option value="">Select Semester</option>
                </select>

                <button class="add" name="add">Add Student</button>

            </form>

        </div>



        <!-- VIEW STUDENTS -->

        <div class="card">

            <h3>View Students</h3>

            <form method="POST">

                <label>Department</label>

                <select name="view_department" id="view_department" required>

                    <option value="">Select Department</option>

                    <?php
                    $dept2 = $conn->query("SELECT * FROM departments");
                    while ($d = $dept2->fetch_assoc()) {
                    ?>

                        <option value="<?php echo $d['id']; ?>">
                            <?php echo $d['department_name']; ?>
                        </option>

                    <?php } ?>

                </select>

                <label>Semester</label>

                <select name="view_semester" id="view_semester" required>
                    <option value="">Select Semester</option>
                </select>

                <button class="view" name="view">View Students</button>

            </form>

        </div>

    </div>



    <?php if ($students) { ?>

        <div class="table-card">

            <h3><?php echo $header_text; ?></h3>

            <table>

                <tr>
                    <th>Photo</th>
                    <th>Register No</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>

                <?php while ($row = $students->fetch_assoc()) { ?>

                    <tr>
                        <td>
                            <?php if ($row['photo']) { ?>
                                <img src="../uploads/<?php echo $row['photo']; ?>" class="student-photo">
                            <?php } ?>
                        </td>

                        <td><?php echo $row['register_number']; ?></td>
                        <td><?php echo $row['student_name']; ?></td>
                        <td><?php echo $row['email']; ?></td>

                        <td>

                            <a href="?delete=<?php echo $row['id']; ?>"
                                onclick="return confirm('Are you sure you want to delete this student?');">

                                <button class="delete">Delete</button>

                            </a>

                        </td>

                    </tr>

                <?php } ?>

            </table>

        </div>

    <?php } ?>


    <script>
        /* LOAD SEMESTERS FOR ADD FORM */

        $("#department_id").change(function() {

            var dept_id = $(this).val();

            $.ajax({
                type: "POST",
                url: "get_semesters.php",
                data: {
                    department_id: dept_id
                },
                success: function(data) {
                    $("#semester_id").html(data);
                }
            });

        });


        /* LOAD SEMESTERS FOR VIEW FORM */

        $("#view_department").change(function() {

            var dept_id = $(this).val();

            $.ajax({
                type: "POST",
                url: "get_semesters.php",
                data: {
                    department_id: dept_id
                },
                success: function(data) {
                    $("#view_semester").html(data);
                }
            });

        });


        /* AUTO HIDE FLASH MESSAGE */

        setTimeout(function() {

            var msg = document.getElementById("flash-msg");

            if (msg) {
                msg.style.opacity = "0";
                setTimeout(() => {
                    msg.remove();
                }, 500);
            }

        }, 4000);
    </script>

</body>

</html>