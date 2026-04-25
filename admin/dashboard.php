<?php
session_start();
include("../config/db.php");

if ($_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

/* Counts */
$students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$teachers = $conn->query("SELECT COUNT(*) as total FROM teachers")->fetch_assoc()['total'];
$departments = $conn->query("SELECT COUNT(*) as total FROM departments")->fetch_assoc()['total'];
$subjects = $conn->query("SELECT COUNT(*) as total FROM subjects")->fetch_assoc()['total'];

$present_today = $conn->query("
SELECT COUNT(*) as total FROM
(
    SELECT student_id,
    CASE 
        WHEN SUM(status='Absent') > 0 THEN 'Absent'
        ELSE 'Present'
    END as final_status
    FROM attendance
    WHERE date = CURDATE()
    GROUP BY student_id
) t
WHERE final_status='Present'
")->fetch_assoc()['total'];

$absent_today = $conn->query("
SELECT COUNT(*) as total FROM
(
    SELECT student_id,
    CASE 
        WHEN SUM(status='Absent') > 0 THEN 'Absent'
        ELSE 'Present'
    END as final_status
    FROM attendance
    WHERE date = CURDATE()
    GROUP BY student_id
) t
WHERE final_status='Absent'
")->fetch_assoc()['total'];

/* Attendance % */
$attendance = $conn->query("
SELECT ROUND(
    (SUM(CASE WHEN final_status = 'Present' THEN 1 ELSE 0 END) / COUNT(*)) * 100,
    2
) AS percent
FROM (
    SELECT student_id,
           CASE 
               WHEN SUM(status='Absent') > 0 THEN 'Absent'
               ELSE 'Present'
           END AS final_status
    FROM attendance
    WHERE date = CURDATE()
    GROUP BY student_id
) AS daily_attendance
")->fetch_assoc()['percent'];

$attendance = $attendance ?? 0;



// graphs  //
$today = date("Y-m-d");

$query = $conn->query("
SELECT 
    d.department_name,
    sem.semester_name,
    SUM(CASE WHEN student_status='Present' THEN 1 ELSE 0 END) AS present,
    SUM(CASE WHEN student_status='Absent' THEN 1 ELSE 0 END) AS absent
FROM (
    SELECT 
        s.id,
        s.department_id,
        s.semester_id,
        CASE 
            WHEN SUM(a.status='Absent') > 0 THEN 'Absent'
            ELSE 'Present'
        END AS student_status
    FROM students s
    JOIN attendance a 
        ON a.student_id = s.id
        AND a.date = '$today'
    GROUP BY s.id
) AS daily_status
JOIN departments d ON daily_status.department_id = d.id
JOIN semesters sem ON daily_status.semester_id = sem.id
GROUP BY d.id, sem.id
ORDER BY d.department_name, sem.id
");

$data = [];

while ($row = $query->fetch_assoc()) {
    $data[$row['department_name']][] = $row;
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

        /* LAYOUT */
        .container {
            display: flex;
        }

        /* SIDEBAR */
        .sidebar {
            width: 250px;
            background: #1f2a40;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo i {
            font-size: 35px;
            margin-bottom: 5px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar li {
            margin: 5px 0;
        }

        .sidebar a {
            display: block;
            padding: 10px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: #394867;
        }

        .sidebar i {
            margin-right: 10px;
        }

        /* DROPDOWN */
        .menu-toggle {
            padding: 10px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            opacity: 0.8;
            border-top: 1px solid #717171;
        }

        .menu-toggle:hover {
            background: #394867;
            border-radius: 6px;
        }

        .submenu {
            display: none;
            padding-left: 15px;
        }

        .submenu.active {
            display: block;
        }

        .menu-toggle span {
            transition: 0.3s;
        }

        .menu-toggle.active span {
            transform: rotate(180deg);
        }

        /* MAIN */
        .main {
            margin-left: 280px;
            padding: 10px 20px 30px 0;
            width: 100%;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .name {
            padding: 0 10px;
            border-left: 6px solid #4e3f65;
        }

        .date {
            background: white;
            padding: 8px 15px;
            border-radius: 6px;
            border-left: 5px solid #ff9100;
            border-right: 5px solid #ff9100;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }


        /* CARDS */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-6px);
        }

        /* ICON */
        .card i {
            font-size: 20px;
            margin-bottom: 10px;
            color: white;
            background: linear-gradient(135deg, #06d4f8, #9a03ff);
            padding: 8px;
            border-radius: 8px;
        }

        /* TITLE */
        .card h4 {
            color: #555;
            margin-bottom: 5px;
        }

        /* VALUE */
        .card p {
            font-size: 26px;
            font-weight: bold;
            color: #111;
        }

        .student {
            border-left: 6px solid #f1a815;
            background-color: #f4b12a15;
        }

        .present {
            border-left: 6px solid #27ae60;
            background-color: #27ae5f0f;
        }

        .absent {
            border-left: 6px solid #e74c3c;
            background-color: #e74d3c17;
        }

        .percent {
            border-left: 6px solid #7c2b6a;
            background-color: #7c2b6a09;
        }

        .footer {
            margin-top: 30px;
            padding: 18px 20px;
            text-align: center;
            background: #ffffff;
            color: #555;
            border-top: 1px solid #e0e0e0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            border-radius: 12px 12px 0 0;
            font-size: 14px;
            font-weight: 500;
        }

        .footer p {
            margin: 0;
        }

        @media (max-width: 768px) {
            .footer {
                font-size: 13px;
                padding: 15px;
            }
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .cards {
                justify-content: center;
            }

            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 210px;
            }
        }

        /* GRAPH SECTION */

        .modern-graph {
            margin-top: 30px;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .modern-graph h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        /* ROW */

        .graph-row {
            margin-bottom: 20px;
        }

        /* LABEL */

        .label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* BAR */

        .bar {
            width: 100%;
            height: 20px;
            background: #ecf0f1;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        /* FILL */

        .fill {
            height: 100%;
            color: white;
            text-align: right;
            padding-right: 8px;
            font-size: 12px;
            line-height: 20px;
            border-radius: 20px;
            animation: grow 1s ease;
        }

        /* COLORS */

        .present-fill {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
        }

        .absent-fill {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
        }

        /* NO DATA */

        .no-data {
            text-align: center;
            color: red;
            font-weight: bold;
        }

        /* ANIMATION */

        @keyframes grow {
            from {
                width: 0;
            }
        }

        /* MOBILE RESPONSIVE FIX */
        .menu-btn {
            display: none;
            font-size: 22px;
            cursor: pointer;
            background: #1f2a40;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
        }

        /* MOBILE VIEW */
        /* MOBILE RESPONSIVE IMPROVED */
        @media (max-width: 768px) {

            .menu-btn {
                display: block;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1001;
            }

            .sidebar {
                position: fixed;
                left: -260px;
                top: 0;
                width: 250px;
                height: 100%;
                z-index: 1001;
                transition: 0.3s;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            }

            .sidebar.active {
                left: 0;
            }

            /* Overlay background */
            .sidebar::after {
                content: "";
                position: fixed;
                top: 0;
                left: 250px;
                width: 100%;
                height: 100%;
                display: none;
                background: rgba(0, 0, 0, 0.3);
            }

            .sidebar.active::after {
                display: block;

            }

            .main {
                margin-left: 0 !important;
                padding: 70px 15px 20px 15px;
            }

            .name h1 {
                font-size: 20px;
            }

            .date h2 {
                font-size: 14px;
            }

            /* Cards stack */
            .card {
                padding: 15px;
            }

            .card p {
                font-size: 22px;
            }

            /* Graph */
            .modern-graph {
                padding: 15px;
            }

            .label {
                font-size: 14px;
            }

            .fill {
                font-size: 10px;
            }
        }
    </style>

</head>

<body>

    <div class="container">

        <!-- SIDEBAR -->
        <div class="sidebar">

            <div class="logo">

                <i class="fas fa-user-shield"></i>
                <h2>Admin Panel</h2>
            </div>

            <ul>

                <li>
                    <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                </li>

                <!-- Departments -->
                <li class="menu-toggle">
                    <div class="menu-title">
                        <i class="fas fa-building"></i>Departments
                    </div>
                    <span class="arrow"><i class="fas fa-chevron-down"></i></span>
                </li>
                <ul class="submenu">
                    <li><a href="departments.php"><i class="fas fa-plus-circle"></i> Add Department</a></li>
                    <li><a href="semesters.php"><i class="fas fa-calendar-alt"></i> Add Semester</a></li>
                    <li><a href="subjects.php"><i class="fas fa-book"></i> Add Subject</a></li>
                </ul>

                <!-- Teachers -->

                <li class="menu-toggle">
                    <div class="menu-title">
                        <i class="fas fa-chalkboard-teacher"></i>Teachers
                    </div>
                    <span class="arrow"><i class="fas fa-chevron-down"></i></span>
                </li>
                <ul class="submenu">
                    <li><a href="teachers.php"><i class="fas fa-user-plus"></i> Add Teacher</a></li>
                    <li><a href="assign_teacher.php"><i class="fas fa-tasks"></i> Assign Teacher</a></li>
                </ul>

                <!-- Students -->
                <li class="menu-toggle">
                    <div class="menu-title">
                        <i class="fas fa-user-graduate"></i>Students
                    </div>
                    <span class="arrow"><i class="fas fa-chevron-down"></i></span>
                </li>


                <ul class="submenu">
                    <li><a href="students.php"><i class="fas fa-user-plus"></i> Add Students</a></li>
                </ul>

                <!-- Attendance -->

                <li class="menu-toggle">
                    <div class="menu-title">
                        <i class="fas fa-calendar-check"></i>Attendance
                    </div>
                    <span class="arrow"><i class="fas fa-chevron-down"></i></span>
                </li>

                <ul class="submenu">
                    <li><a href="../teacher/attendance_report.php"><i class="fas fa-chart-bar"></i> View Attendance</a></li>
                </ul>

                <!-- Settings -->
                <li class="menu-toggle">
                    <div class="menu-title">
                        <i class="fas fa-cog"></i> Settings
                    </div>
                    <span class="arrow"><i class="fas fa-chevron-down"></i></span>
                </li>

                <ul class="submenu">
                    <li><a href="change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
                    <li><a href="system_settings.php"><i class="fas fa-sliders-h"></i> System Settings</a></li>
                </ul>

                <!-- Logout -->
                <li>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>

            </ul>

        </div>

        <!-- MAIN -->
        <div class="main">

            <!-- HEADER -->
            <div class="header">


                <div class="menu-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </div>

                <div class="name">
                    <h1>Admin Dashboard</h1>
                </div>

                <div class="date">
                    <h2><?php echo date("d M Y"); ?></h2>
                </div>
            </div>

            <!-- CARDS -->
            <div class="cards">

                <div class="card student">
                    <i class="fas fa-user-graduate"></i>
                    <h4>Students</h4>
                    <p><?php echo $students ?></p>
                </div>

                <div class="card present">
                    <i class="fas fa-user-check text-success"></i>
                    <h4>Present Today</h4>
                    <p><?php echo $present_today; ?></p>

                </div>

                <div class="card absent">
                    <i class="fas fa-user-times text-danger"></i>
                    <h4>Absent Today</h4>
                    <p><?php echo $absent_today; ?></p>

                </div>

                <div class="card percent">
                    <i class="fas fa-chart-line"></i>
                    <h4>Attendance %</h4>
                    <p><?php echo $attendance ? $attendance . '%' : '0%' ?></p>
                </div>


            </div>
            <div class="cards">
                <div class="card">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h4>Teachers</h4>
                    <p><?php echo $teachers ?></p>
                </div>

                <div class="card">
                    <i class="fas fa-building"></i>
                    <h4>Departments</h4>
                    <p><?php echo $departments ?></p>
                </div>

                <div class="card">
                    <i class="fas fa-book"></i>
                    <h4>Subjects</h4>
                    <p><?php echo $subjects ?></p>
                </div>

            </div>

            <!-- MODERN ATTENDANCE GRAPH -->

            <div class="modern-graph">

                <h3>📊 Attendance Overview</h3>

                <?php if (empty($data)) { ?>

                    <p class="no-data">No Attendance Marked Today</p>

                <?php } else { ?>

                    <?php foreach ($data as $dept => $rows) { ?>

                        <?php foreach ($rows as $r) {

                            $total = $r['present'] + $r['absent'];

                            $presentPercent = ($total > 0) ? ($r['present'] / $total) * 100 : 0;
                            $absentPercent  = ($total > 0) ? ($r['absent'] / $total) * 100 : 0;

                        ?>

                            <div class="graph-row">

                                <div class="label">
                                    <?php echo $dept . " - " . $r['semester_name']; ?>
                                </div>

                                <!-- PRESENT -->
                                <div class="bar">
                                    <div class="fill present-fill" style="width: <?php echo $presentPercent; ?>%">
                                        <?php echo $r['present']; ?>
                                    </div>
                                </div>

                                <!-- ABSENT -->
                                <div class="bar">
                                    <div class="fill absent-fill" style="width: <?php echo $absentPercent; ?>%">
                                        <?php echo $r['absent']; ?>
                                    </div>
                                </div>

                            </div>

                        <?php } ?>

                    <?php } ?>

                <?php } ?>

            </div>
            <!-- Footer -->
            <footer class="footer">
                <p>&copy; <?php echo date('Y'); ?> Student Attendance Management System. All Rights Reserved.</p>
            </footer>
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

        function toggleSidebar() {
            document.querySelector(".sidebar").classList.toggle("active");
        }
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            const btn = document.querySelector('.menu-btn');

            if (!sidebar.contains(e.target) && !btn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });
    </script>



</body>

</html>