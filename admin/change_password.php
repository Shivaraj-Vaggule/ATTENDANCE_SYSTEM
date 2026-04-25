<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

$msg = "";

if (isset($_POST['change'])) {

    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $user_id = $_SESSION['user_id'];

    // Get current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify old password
    if (!password_verify($old_password, $user['password'])) {
        $msg = "❌ Old password is incorrect!";
    } elseif ($new_password !== $confirm_password) {
        $msg = "❌ New passwords do not match!";
    } else {
        // Hash new password
        $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $update->bind_param("si", $new_hashed, $user_id);
        $update->execute();

        $msg = "✅ Password updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Change Password</title>
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .box {
            width: 350px;
            margin: 80px auto;
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.5s ease;
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #4facfe;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .msg {
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
            animation: slideIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>

</head>

<body>
    <div class="container">

        <div class="main">

        <div class="box">

            <h2>🔐 Change Password</h2>

            <form method="POST">
                <input type="password" name="old_password" placeholder="Old Password" required>
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>

                <button type="submit" name="change">Update Password</button>
            </form>

            <div class="msg"><?php echo $msg; ?></div>

        </div>
    </div>
    </div>

    <script>
        const toggles = document.querySelectorAll('.menu-toggle');

        toggles.forEach(toggle => {
            toggle.addEventListener('click', () => {

                toggle.classList.toggle('active');

                let submenu = toggle.nextElementSibling;
                submenu.classList.toggle('active');

            });
        });
    </script>
    

</body>

</html>