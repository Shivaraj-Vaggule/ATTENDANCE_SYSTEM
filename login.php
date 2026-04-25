<?php
session_start();
include("config/db.php");

$error = "";

/* STUDENT LOGIN */
if (isset($_POST['login_student'])) {

    $reg = trim($_POST['student_register']);
    $password = trim($_POST['student_password']);

    $stmt = $conn->prepare("SELECT * FROM students WHERE register_number=?");
    $stmt->bind_param("s", $reg);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $student = $result->fetch_assoc();

        // ✅ ALWAYS verify hashed password
        if (password_verify($password, $student['password'])) {

            $_SESSION['student_id'] = $student['id'];
            $_SESSION['name'] = $student['student_name'];
            $_SESSION['role'] = "student";

            // 🔥 FIRST LOGIN CHECK (better way)
            if ($student['password'] === password_hash("123456", PASSWORD_DEFAULT)) {
                $_SESSION['force_change'] = true;
                header("Location: student/change_password.php");
            } else {
                header("Location: student/dashboard.php");
            }

            exit();
        } else {
            $error = "Incorrect Password!";
        }
    } else {
        $error = "Student not found!";
    }
}


/* FORGOT PASSWORD - STUDENT */
if (isset($_POST['forgot_student'])) {

    $reg = trim($_POST['fp_register']);
    $new = $_POST['fp_new_password'];
    $confirm = $_POST['fp_confirm_password'];

    if ($new !== $confirm) {
        $error = "Passwords do not match!";
    } elseif (strlen($new) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {

        $stmt = $conn->prepare("SELECT id FROM students WHERE register_number=?");
        $stmt->bind_param("s", $reg);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $hash = password_hash($new, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE students SET password=?, is_default=0 WHERE register_number=?");
            $update->bind_param("ss", $hash, $reg);
            $update->execute();

            $error = "Password reset successful! Login now.";
        } else {
            $error = "Student not found!";
        }
    }
}
/* TEACHER LOGIN */
if (isset($_POST['login_teacher'])) {
    $email = trim($_POST['teacher_email']);
    $password = trim($_POST['teacher_password']);

    $stmt = $conn->prepare("SELECT * FROM teachers WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $teacher = $result->fetch_assoc();

        if (password_verify($password, $teacher['password'])) {
            $_SESSION['teacher_id'] = $teacher['id'];
            $_SESSION['name'] = $teacher['name'];
            $_SESSION['role'] = "teacher";
            header("Location: teacher/dashboard.php");
            exit();
        } else {
            $error = "Incorrect Password!";
        }
    } else {
        $error = "Teacher not found!";
    }
}

/* FORGOT PASSWORD - TEACHER */
if (isset($_POST['forgot_teacher'])) {

    $email = trim($_POST['fp_teacher_email']);
    $new = $_POST['fp_teacher_new_password'];
    $confirm = $_POST['fp_teacher_confirm_password'];

    if ($new !== $confirm) {
        $error = "Passwords do not match!";
    } elseif (strlen($new) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {

        $stmt = $conn->prepare("SELECT id FROM teachers WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $hash = password_hash($new, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE teachers SET password=? WHERE email=?");
            $update->bind_param("ss", $hash, $email);
            $update->execute();

            $error = "Teacher password reset successful!";
        } else {
            $error = "Teacher not found!";
        }
    }
}

/* ADMIN LOGIN */
if (isset($_POST['login_admin'])) {
    $email = trim($_POST['admin_email']);
    $password = trim($_POST['admin_password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND role='admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['name'] = $admin['name'];
            $_SESSION['role'] = "admin";
            header("Location: admin/dashboard.php");
            exit();
        } else {
            $error = "Incorrect Password!";
        }
    } else {
        $error = "Admin not found!";
    }
}

/* FORGOT PASSWORD - ADMIN */
if (isset($_POST['forgot_admin'])) {

    $email = trim($_POST['fp_admin_email']);
    $new = $_POST['fp_admin_new_password'];
    $confirm = $_POST['fp_admin_confirm_password'];

    if ($new !== $confirm) {
        $error = "Passwords do not match!";
    } elseif (strlen($new) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {

        $stmt = $conn->prepare("SELECT id FROM users WHERE email=? AND role='admin'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $hash = password_hash($new, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE users SET password=? WHERE email=? AND role='admin'");
            $update->bind_param("ss", $hash, $email);
            $update->execute();

            $error = "Admin password reset successful!";
        } else {
            $error = "Admin not found!";
        }
    }
}
/* STUDENT SIGNUP */
if (isset($_POST['signup_student'])) {
    $name = trim($_POST['student_name']);
    $register = trim($_POST['student_register']);
    $rawPassword = $_POST['student_password'];

    // 🔐 Strong password validation
    if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/", $rawPassword)) {
        $error = "Password must be 6+ chars with letters & numbers!";
    } else {
        $password = password_hash($rawPassword, PASSWORD_DEFAULT);

        $check = $conn->prepare("SELECT id FROM students WHERE register_number=?");
        $check->bind_param("s", $register);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = "Register number already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO students(student_name, register_number, password) VALUES(?,?,?)");
            $stmt->bind_param("sss", $name, $register, $password);
            $stmt->execute();

            $error = "Student account created!";
        }
    }
}

/* ADMIN SIGNUP */
if (isset($_POST['signup_admin'])) {
    $name = trim($_POST['admin_name']);
    $email = trim($_POST['admin_email']);
    $password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email=? AND role='admin'");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = "Admin email already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,'admin')");
        $stmt->bind_param("sss", $name, $email, $password);
        $stmt->execute();
        $error = "Admin account created! You can now login.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMS | Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {

            background: linear-gradient(135deg, #4e73df, #1cc88a);
            height: 100vh;
        }


        /* Overlay */
        .overlay {
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: rgba(0, 0, 0, 0.7);
        }

        /* NAVBAR */
        .navbar {
            text-align: center;
            padding: 20px 60px;
            color: white;
            background: rgba(116, 93, 93, 0.2);
        }

        .navbar h1 span {
            color: #00f2fe;
        }

        /* MAIN CONTAINER */
        
        .container {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        /* RIGHT SIDE */
        .right {
            width: 450px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-top-right-radius: 30px;
            border-bottom-left-radius: 30px;
            color: white;
        }

        /* ROLE BUTTONS */
        .roles {
            display: flex;
            margin-bottom: 20px;

        }

        .roles button {
            flex: 1;
            padding: 10px;
            margin: 5px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transition: 0.3s;
        }

        .roles button.active {
            background: #00f2fe;
            color: #000;
            font-weight: bold;
        }

        /* FORM */
        .form {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .form.active {
            display: block;
        }

        .form h3 {
            color: white;
            margin-bottom: 15px;
            margin-left: 10px;
            text-align: center;
        }

        /* INPUT */
        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: none;
            background: none;
            border-bottom: 1px solid #ccc;
            outline: none;
            color: white;
        }

        input::placeholder {
            color: #ddd;
        }

        /* BUTTON */
        .btn {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background: linear-gradient(135deg, #00f2fe, #4facfe);
            color: black;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        /* TOGGLE */
        .toggle {
            text-align: center;
            margin-top: 12px;
            color: #ccc;
            cursor: pointer;
            font-size: 14px;
        }

        .toggle:hover {
            color: white;
        }

        /* ERROR */
        .error-msg {
            color: #f47777;
            text-align: center;
            margin-bottom: 10px;
        }

        /* ANIMATIONS */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideRight {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }

            to {
                opacity: 1;
            }
        }
/* ================= MOBILE RESPONSIVE ================= */
@media (max-width: 768px) {

    body {
        height: auto;
    }

    .overlay {
        padding-bottom: 20px;
    }

    .navbar {
        padding: 15px;
        font-size: 14px;
    }

    .navbar h1 {
        font-size: 28px;
    }

    .container {
        padding: 10px;
    }

    .right {
        width: 480px;
        padding: 40px 30px;
    }


    /* Inputs */
    input {
        font-size: 18px;
        padding: 9px;
    }

    /* Buttons */
    .btn {
        padding: 12px;
        font-size: 20px;
    }

    /* Text */
    .form h3 {
        font-size: 22px;
    }

    .toggle {
        font-size: 18px;
    }

    .error-msg {
        font-size: 17px;
    }
}

/* EXTRA SMALL DEVICES */
@media (max-width: 560px) {

    .navbar h1 {
        font-size: 16px;
    }

    .right {
        width: 450px;
        padding: 35px 30px;
    }

     /* Inputs */
    input {
        font-size: 22px;
        padding: 9px;
    }

    /* Buttons */
    .btn {
        padding: 12px;
        font-size: 24px;
    }

    /* Text */
    .form h3 {
        font-size: 27px;
    }

    .toggle {
        font-size: 20px;
    }

    .error-msg {
        font-size: 22px;
    }
}
        
    </style>
</head>

<body>

    <div class="overlay">

        <!-- NAV -->
        <div class="navbar">
            <h1><span>STUDENT</span> ATTENDANCE MANAGEMENT SYSTEM</h1>
        </div>

        <div class="container">

            <div class="right">

                <?php if ($error) echo "<div class='error-msg'>$error</div>"; ?>

                <div class="roles">
                    <button class="role-btn active" onclick="switchRole('student')">Student</button>
                    <button class="role-btn" onclick="switchRole('teacher')">Teacher</button>
                    <button class="role-btn" onclick="switchRole('admin')">Admin</button>
                </div>

                <!-- STUDENT LOGIN -->
                <form class="form active login student" method="POST">
                    <h3>Student Login</h3>
                    <input type="text" name="student_register" placeholder="Register Number" required>
                    <input type="password" name="student_password" placeholder="Password" required>
                    <div class="toggle" onclick="openForgot('student')">Forgot Password?</div>
                    <button class="btn" type="submit" name="login_student">Login</button>
                </form>
                
                <!-- forget password - student  -->
                <form class="form forgot student" method="POST">
                    <h3>Reset Password</h3>
                    <input type="text" name="fp_register" placeholder="Register Number" required>
                    <input type="password" name="fp_new_password" placeholder="New Password" required>
                    <input type="password" name="fp_confirm_password" placeholder="Confirm Password" required>
                    <button class="btn" name="forgot_student">Reset Password</button>
                    <div class="toggle" onclick="backToLogin()">Back to Login</div>
                </form>

                <!-- TEACHER LOGIN -->
                <form class="form login teacher" method="POST">
                    <h3>Teacher Login</h3>
                    <input type="email" name="teacher_email" placeholder="Email" required>
                    <input type="password" name="teacher_password" placeholder="Password" required>
                    <div class="toggle" onclick="openForgot('teacher')">Forgot Password?</div>
                    <button class="btn" type="submit" name="login_teacher">Login</button>
                </form>

                <!-- forget password - teacher  -->
                <form class="form forgot teacher" method="POST">
                    <h3>Reset Password</h3>

                    <input type="email" name="fp_teacher_email" placeholder="Email" required>
                    <input type="password" name="fp_teacher_new_password" placeholder="New Password" required>
                    <input type="password" name="fp_teacher_confirm_password" placeholder="Confirm Password" required>

                    <button class="btn" name="forgot_teacher">Reset Password</button>

                    <div class="toggle" onclick="backToLogin()">Back to Login</div>
                </form>

                <!-- ADMIN LOGIN -->
                <form class="form login admin" method="POST">
                    <h3>Admin Login</h3>
                    <input type="email" name="admin_email" placeholder="Email" required>
                    <input type="password" name="admin_password" placeholder="Password" required>
                    <div class="toggle" onclick="openForgot('admin')">Forgot Password?</div>
                    <button class="btn" type="submit" name="login_admin">Login</button>
                    <div class="toggle" onclick="toggleForm('admin')">Create Account</div>
                </form>

                <!-- forget password - admin  -->
                <form class="form forgot admin" method="POST">
                    <h3>Reset Password</h3>
                    <input type="email" name="fp_admin_email" placeholder="Email" required>
                    <input type="password" name="fp_admin_new_password" placeholder="New Password" required>
                    <input type="password" name="fp_admin_confirm_password" placeholder="Confirm Password" required>

                    <button class="btn" name="forgot_admin">Reset Password</button>

                    <div class="toggle" onclick="backToLogin()">Back to Login</div>
                </form>

                <!-- ADMIN SIGNUP -->
                <form class="form signup admin" method="POST">
                    <h3>Admin Signup</h3>
                    <input type="text" name="admin_name" placeholder="Username" required>
                    <input type="email" name="admin_email" placeholder="Email" required>
                    <input type="password" name="admin_password" placeholder="Password" required>
                    <button class="btn" type="submit" name="signup_admin">Sign Up</button>
                    <div class="toggle" onclick="toggleForm('admin')">Already have account?</div>
                </form>



            </div>
        </div>

    </div>
    </div>

    <script>
        let currentRole = "student";

        function switchRole(role) {
            currentRole = role;
            document.querySelectorAll(".role-btn").forEach(btn => btn.classList.remove("active"));
            event.target.classList.add("active");
            document.querySelectorAll(".form").forEach(f => f.classList.remove("active"));
            document.querySelector(".login." + role).classList.add("active");
        }

        function toggleForm(role) {
            let login = document.querySelector(".login." + role);
            let signup = document.querySelector(".signup." + role);
            login.classList.toggle("active");
            signup.classList.toggle("active");
        }

        // Password strength hint
        document.querySelectorAll("input[type='password']").forEach(input => {
            input.addEventListener("input", function() {

                const value = this.value;

                const hasLength = value.length >= 6;
                const hasLower = /[a-z]/.test(value);
                const hasUpper = /[A-Z]/.test(value);
                const hasNumber = /[0-9]/.test(value);
                const hasSpecial = /[^a-zA-Z0-9]/.test(value);

                // Strength logic
                if (hasLength && hasLower && hasUpper && hasNumber && hasSpecial) {
                    this.style.borderBottom = "2px solid #1cc88a"; // Strong (green)
                } else if (hasLength && (hasLower || hasUpper) && hasNumber) {
                    this.style.borderBottom = "2px solid #f6c23e"; // Medium (yellow)
                } else {
                    this.style.borderBottom = "2px solid #e74a3b"; // Weak (red)
                }

            });
        });

       function openForgot(role) {
    document.querySelectorAll(".form").forEach(f => f.classList.remove("active"));
    document.querySelector(".forgot." + role).classList.add("active");
}

function backToLogin() {
    document.querySelectorAll(".form").forEach(f => f.classList.remove("active"));
    document.querySelector(".login." + currentRole).classList.add("active");
}
    </script>

</body>

</html>