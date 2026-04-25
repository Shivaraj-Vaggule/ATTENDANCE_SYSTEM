<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit();
}

// force only first-time users
if (!isset($_SESSION['force_change'])) {
    header("Location: dashboard.php");
    exit();
}

$msg = "";

if (isset($_POST['change_pass'])) {

    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // 🔐 Strong password validation
    if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/", $new)) {
        $msg = "Password must be 6+ chars with letters & numbers!";
    }
    elseif ($new !== $confirm) {
        $msg = "Passwords do not match!";
    }
    else {
        $hash = password_hash($new, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE students SET password=? WHERE id=?");
        $stmt->bind_param("si", $hash, $_SESSION['student_id']);
        $stmt->execute();

        unset($_SESSION['force_change']);

        header("Location: student/dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1f2a40, #4facfe);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            width: 350px;
            padding: 30px;
            border-radius: 15px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            color: white;
            animation: fadeIn 0.5s ease;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .input-group {
            position: relative;
            margin: 15px 0;
        }

        input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            outline: none;
            background: rgba(255,255,255,0.2);
            color: white;
        }

        input::placeholder {
            color: #ddd;
        }

        .toggle-pass {
            position: absolute;
            right: 10px;
            top: 12px;
            cursor: pointer;
        }

        .btn {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            border: none;
            border-radius: 10px;
            background: #00f2fe;
            color: black;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        .msg {
            text-align: center;
            margin-top: 10px;
            color: #ff6b6b;
        }

        @keyframes fadeIn {
            from {opacity:0; transform: translateY(20px);}
            to {opacity:1;}
        }
    </style>
</head>

<body>

<div class="card">
    <h2>Change Password</h2>

    <form method="POST">

        <div class="input-group">
            <input type="password" name="new_password" id="newPass" placeholder="New Password" required>
            <span class="toggle-pass" onclick="togglePass('newPass')">👁</span>
        </div>

        <div class="input-group">
            <input type="password" name="confirm_password" id="confirmPass" placeholder="Confirm Password" required>
            <span class="toggle-pass" onclick="togglePass('confirmPass')">👁</span>
        </div>

        <button class="btn" name="change_pass">Update Password</button>

        <div class="msg"><?php echo $msg; ?></div>

    </form>
</div>

<script>
function togglePass(id){
    let input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}

// Live validation
document.getElementById("confirmPass").addEventListener("input", function(){
    let newPass = document.getElementById("newPass").value;
    if(this.value !== newPass){
        this.style.border = "2px solid red";
    } else {
        this.style.border = "2px solid green";
    }
});
</script>

</body>
</html>