<?php
include("../config/db.php");

$total_students = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];

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
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../assets/teacher_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
            background: #2c3e50;
            color: #fff;
            border: none;
            padding: 12px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 998;
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
            .menu-toggle {
                display: block;
            }

            .sidebar {
                position: fixed;
                left: -280px;
                top: 0;
                width: 260px;
                height: 100%;
                z-index: 999;
                transition: left 0.3s ease;
                overflow-y: auto;
            }

            .sidebar.active {
                left: 0;
            }

            .sidebar-overlay.active {
                display: block;
            }

            .main {
                margin-left: 0 !important;
                width: 100%;
                padding-top: 90px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .cards {
                grid-template-columns: 1fr;
            }

            .quick-links {
                display: grid;
                grid-template-columns: 1fr;
                gap: 10px;
                text-align: center;
            }
             .footer {
            font-size: 13px;
            padding: 15px;
        }
        }
    </style>
</head>
<body>

    <button class="menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="container">

        <div class="sidebar" id="sidebar">
            <div class="std-logo">
                <i class="fas fa-user-tie"></i>
                <h2>Teacher Panel</h2>
            </div>

            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a></li>

                <li class="menu-title">Students</li>
                <li><a href="../admin/students.php"><i class="fas fa-user-plus"></i>Add Student</a></li>

                <li class="menu-title">Subjects</li>
                <li><a href="../admin/subjects.php"><i class="fas fa-book"></i>Add Subject</a></li>

                <li class="menu-title">Attendance</li>
                <li><a href="mark_attendance.php"><i class="fas fa-clipboard-check"></i>Mark Attendance</a></li>

                <li class="menu-title">Reports</li>

                <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="topbar">
                <h1>Teacher Dashboard</h1>
                <div class="date">
                    <h2><?php echo date("d M Y"); ?></h2>
                </div>
            </div>

            <div class="cards">
                <div class="card">
                    <h3>Total Students</h3>
                    <p><?php echo $total_students; ?></p>
                </div>

                <div class="card present">
                    <h3>Present Today</h3>
                    <p><?php echo $present_today; ?></p>
                </div>

                <div class="card absent">
                    <h3>Absent Today</h3>
                    <p><?php echo $absent_today; ?></p>
                </div>
            </div>

            <div class="quick-links">
                <a href="mark_attendance.php" class="btn">Mark Attendance</a>
                <a href="attendance_calendar.php" class="btn">Attendance Calender</a>
                <a href="attendance_report.php" class="btn">Attendance Report</a>
                <a href="student_percentage.php" class="btn">Attendance Percentage</a>
            </div>

            <div class="modern-graph">
                <h3>📊 Attendance Overview</h3>

                <?php if (empty($data)) { ?>
                    <p class="no-data">No Attendance Marked Today</p>
                <?php } else { ?>

                    <?php foreach ($data as $dept => $rows) { ?>
                        <?php foreach ($rows as $r) {
                            $total = $r['present'] + $r['absent'];
                            $presentPercent = ($total > 0) ? ($r['present'] / $total) * 100 : 0;
                            $absentPercent = ($total > 0) ? ($r['absent'] / $total) * 100 : 0;
                        ?>

                            <div class="graph-row">
                                <div class="label">
                                    <?php echo $dept . " - " . $r['semester_name']; ?>
                                </div>

                                <div class="bar">
                                    <div class="fill present-fill" style="width: <?php echo $presentPercent; ?>%">
                                        <?php echo $r['present']; ?>
                                    </div>
                                </div>

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
            <footer class="footer">
    <p>&copy; <?php echo date('Y'); ?> Student Attendance Management System. All Rights Reserved.</p>
</footer>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }
    </script>

</body>
</html>