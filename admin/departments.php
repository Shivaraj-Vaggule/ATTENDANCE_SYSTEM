<?php
include("../config/db.php");

if (isset($_POST['add'])) {
    $name = $_POST['department_name'];
    $conn->query("INSERT INTO departments(department_name) VALUES('$name')");
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM departments WHERE id=$id");
}

$result = $conn->query("SELECT * FROM departments");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Departments</title>

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

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #34495e;
            color: white;
            padding: 15px 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .header h2 {
            font-size: 20px;
        }

        .header a {
            text-decoration: none;
            background: #3498db;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            margin-top: 8px;
            transition: 0.3s
        }

        .header a:hover {
            background: #2c7fb6;
        }

        /* CARD */
        .container {
            padding: 60px;
        }

        input {
            width: 400px;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: none;
            outline: 1px solid #34495e;
            outline-offset: 1px;
            transition: 0.3s;

        }

        button {
            padding: 11px;
            border: none;
            cursor: pointer;

        }

        .add {
            background: #27ae60;
            color: white;
            transition: .3s;
        }

        .add:hover {
            background: #288b52;
        }

        .delete {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            transition: .3s;
        }

        .delete:hover {
            background: #dd5243;

        }

        /* TABLE */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            overflow: hidden;
            min-width: 400px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        th {
            background: #2c3e50;
            color: white;
        }

        /* tr:hover {
            background: #f9f9f9;
        } */

        /* MOBILE */
        @media (max-width: 600px) {

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header a {
                width: 100%;
                text-align: center;
            }

            .card button {
                width: 100%;
            }

            button {
                width: 50%;
            }

            th,
            td {
                padding: 8px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Departments</h2>
        <a href="dashboard.php">Dashboard</a>
    </div>

    <div class="container">

        <div class="card">
            <form method="POST">
                <input type="text" name="department_name" placeholder="Enter department name" required>
                <button class="add" name="add">Add Department</button>
            </form>
        </div>

        <div class="table-container">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Department</th>
                    <th>Action</th>
                </tr>

                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['department_name']; ?></td>
                        <td>
                            <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this department?')">
                                <button class="delete">Delete</button>
                            </a>
                        </td>
                    </tr>
                <?php } ?>

            </table>
        </div>
    </div>

</body>

</html>