<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare("
    SELECT s.*, d.department_name, sem.semester_name 
    FROM students s
    LEFT JOIN departments d ON s.department_id=d.id
    LEFT JOIN semesters sem ON s.semester_id=sem.id
    WHERE s.id=?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

$msg = "";

if (isset($_POST['upload_photo'])) {
    $file = $_FILES['photo']['name'];
    $tmp = $_FILES['photo']['tmp_name'];

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if (in_array($ext, $allowed)) {
        $folder = "../uploads/" . time() . '_' . $file;
        move_uploaded_file($tmp, $folder);
        $conn->query("UPDATE students SET photo='" . basename($folder) . "' WHERE id='$student_id'");
        $msg = "Profile Photo Updated!";
        $student['photo'] = basename($folder);
    } else {
        $msg = "Invalid file type!";
    }
}

if (isset($_POST['change_password'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $conn->query("UPDATE students SET password='$password' WHERE id='$student_id'");
    $msg = "Password Updated Successfully!";
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Student Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #eef2f7, #f8fafc);
            color: #2c3e50;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .menu-btn {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            padding: 0;
            z-index: 1100;
            background: #2c3e50;
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 240px;
            height: 100vh;
            background: #2c3e50;
            color: white;
            padding: 20px;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .std-logo {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .std-logo i {
            font-size: 54px;
            margin-bottom: 10px;
        }

        .std-logo h2 {
            margin: 10px 0 0;
            font-size: 24px;
            font-weight: 600;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 12px 15px;
            margin: 8px 0;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: #3e5872;
        }


        .main {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 0 30px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
        }

        .success {
            background: linear-gradient(135deg, #d4edda, #c3f0d0);
            color: #155724;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 5px solid #28a745;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .profile-photo {
            text-align: center;
        }

        .photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3498db;
            box-shadow: 0 10px 25px rgba(52, 152, 219, 0.3);
            transition: 0.3s;
            margin-bottom: 20px;
        }

        .photo:hover {
            transform: scale(1.05);
        }

        input[type="file"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #dcdde1;
            border-radius: 10px;
            margin: 10px 0;
            font-family: inherit;
        }

        button {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            transition: 0.3s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.3);
        }

        .password-box button {
            background: linear-gradient(135deg, #27ae60, #219150);
        }

        .password-box button:hover {
            box-shadow: 0 8px 20px rgba(39, 174, 96, 0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #edf2f7;
        }

        td:first-child {
            font-weight: 600;
            width: 35%;
            color: #34495e;
        }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 999;
        }

        .overlay.active {
            display: block;
        }

        @media (max-width: 768px) {
            .menu-btn {
                display: block;
            }

            .sidebar {
                left: -260px;
            }

            .sidebar.active {
                left: 0;
            }

            .main {
                margin-left: 0;
                width: 100%;
                padding: 80px 15px 20px;
            }

            .page-title {
                font-size: 26px;
            }

            .card {
                margin: 1.5em;
                padding: 20px;
                border-radius: 16px;
            }

            .photo {
                width: 120px;
                height: 120px;
            }

            table,
            tbody,
            tr,
            td {
                display: block;
                width: 100%;
            }

            tr {
                margin-bottom: 10px;
                border-bottom: 1px solid #eee;
            }

            td {
                text-align: left;
                padding: 10px 0;
                border: none;
            }

            td:first-child {
                width: 100%;
                color: #7f8c8d;
                padding-bottom: 4px;
            }
        }
    </style>
</head>

<body>
    <button class="menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <div class="container">
        <div class="sidebar" id="sidebar">
            <div class="std-logo">
                <i class="fas fa-user-graduate"></i>
                <h2>Student Panel</h2>
            </div>

            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="view_attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a>
            <a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="main">
            <h1 class="page-title">My Profile</h1>

            <?php if ($msg != "") { ?>
                <div class="success"><?php echo $msg; ?></div>
            <?php } ?>

            <div class="card profile-photo">
                <?php if (!empty($student['photo'])) { ?>
                    <img src="../uploads/<?php echo $student['photo']; ?>" class="photo">
                <?php } else { ?>
                    <img src="../uploads/default.png" class="photo">
                <?php } ?>

                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="photo" required>
                    <button type="submit" name="upload_photo">Upload Photo</button>
                </form>
            </div>

            <div class="card profile-card">
                <table>
                    <tr>
                        <td>Name</td>
                        <td><?php echo $student['student_name']; ?></td>
                    </tr>
                    <tr>
                        <td>Register No</td>
                        <td><?php echo $student['register_number']; ?></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td><?php echo $student['email']; ?></td>
                    </tr>
                    <tr>
                        <td>Department</td>
                        <td><?php echo $student['department_name']; ?></td>
                    </tr>
                    <tr>
                        <td>Semester</td>
                        <td><?php echo $student['semester_name']; ?></td>
                    </tr>
                </table>
            </div>

            <div class="card password-box">
                <h2>Change Password</h2>
                <form method="POST">
                    <input type="password" name="password" placeholder="New Password" required>
                    <button type="submit" name="change_password">Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }
    </script>
</body>

</html>