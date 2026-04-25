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

/* ADD TEACHER WITH DUPLICATE PROTECTION */

if (isset($_POST['add_teacher'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    /* CHECK DUPLICATE EMAIL */

    $check_email = $conn->prepare("SELECT id FROM teachers WHERE email=?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result_email = $check_email->get_result();

    /* CHECK DUPLICATE NAME + EMAIL */

    $check_teacher = $conn->prepare("SELECT id FROM teachers WHERE name=? AND email=?");
    $check_teacher->bind_param("ss", $name, $email);
    $check_teacher->execute();
    $result_teacher = $check_teacher->get_result();

    if ($result_email->num_rows > 0) {
        $_SESSION['message'] = "Email already registered!";
        $_SESSION['msg_type'] = "error";
    } elseif ($result_teacher->num_rows > 0) {
        $_SESSION['message'] = "Teacher already exists!";
        $_SESSION['msg_type'] = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO teachers(name,email,password) VALUES(?,?,?)");
        $stmt->bind_param("sss", $name, $email, $password);
        $stmt->execute();

        $_SESSION['message'] = "Teacher added successfully";
        $_SESSION['msg_type'] = "success";
    }

    header("Location: teachers.php");
    exit();
}


/* DELETE TEACHER */

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $conn->query("DELETE FROM teachers WHERE id='$id'");

    $_SESSION['message'] = "Teacher deleted successfully";
    $_SESSION['msg_type'] = "success";

    header("Location: teachers.php");
    exit();
}


/* FETCH TEACHERS */

$teachers = $conn->query("SELECT * FROM teachers ORDER BY id DESC");

?>

<!DOCTYPE html>
<html>

<head>

    <title>Teachers Management</title>

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

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header a {
            text-decoration: none;
            color: white;
            background: #3498db;
            padding: 8px 12px;
            border-radius: 5px;
            margin-left: 10px;
        }
        .dashboard {
            background: #3498db;
            color: white;
        }

        /* .logout {
            background: #e74c3c;
            color: white;
        } */

        .form-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            width: 400px;
            margin-bottom: 25px;
        }

        input {
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

        .delete {
            background: #e74c3c;
            color: white;
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>

</head>

<body>

    <div class="header">
        <h1>Teachers Management</h1>

        <div>
            <a href="<?php echo $dashboard_link; ?>">Home</a>
        </div>
    </div>

    <div class="container">

        <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='{$_SESSION['msg_type']}' id='flash-msg'>{$_SESSION['message']}</div>";

            unset($_SESSION['message']);
            unset($_SESSION['msg_type']);
        }
        ?>


        <div class="form-card">

            <form method="POST">

                <label>Teacher Name</label>
                <input type="text" name="name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <button name="add_teacher">Add Teacher</button>

            </form>

        </div>


        <div class="table-card">

            <h3>Teachers List</h3>

            <table>

                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>

                <?php while ($row = $teachers->fetch_assoc()) { ?>

                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>

                        <td>

                            <a href="teachers.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this teacher?')">

                                <button class="delete">Delete</button>

                            </a>

                        </td>

                    </tr>

                <?php } ?>

            </table>

        </div>


        <script>
            /* AUTO HIDE MESSAGE */

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
    </div>

</body>

</html>