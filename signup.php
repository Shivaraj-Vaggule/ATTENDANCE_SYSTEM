<?php
include("config/db.php");

if (isset($_POST['signup'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $role = $_POST['role'];

    $sql = "INSERT INTO users(name,email,password,role)
VALUES('$name','$email','$password','$role')";

    if ($conn->query($sql)) {
        echo "<script>alert('Signup Successful');window.location='login.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Signup</title>

    <style>
        body {
            font-family: Arial;
            background: linear-gradient(120deg, #2980b9, #6dd5fa);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 350px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #2980b9;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #1f6391;
        }

        .link {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <div class="form-box">

        <h2>Create Account</h2>

        <form method="POST">

            <input type="text" name="name" placeholder="Full Name" required>

            <input type="email" name="email" placeholder="Email" required>

            <input type="password" name="password" placeholder="Password" required>

            <select name="role" required>
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
            </select>

            <button name="signup">Signup</button>

            <div class="link">
                Already have account?
                <a href="login.php">Login</a>
            </div>

        </form>

    </div>

</body>

</html>