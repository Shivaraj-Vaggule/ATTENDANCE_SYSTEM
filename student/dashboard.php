<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

/* =========================
   STUDENT INFO
========================= */
$stmt = $conn->prepare("
    SELECT s.*, d.department_name, sem.semester_name 
    FROM students s
    LEFT JOIN departments d ON s.department_id = d.id
    LEFT JOIN semesters sem ON s.semester_id = sem.id
    WHERE s.id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

/* =========================
   ATTENDANCE STATS
========================= */
$stmt = $conn->prepare("SELECT COUNT(*) as t FROM attendance WHERE student_id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['t'];

$stmt = $conn->prepare("SELECT COUNT(*) as p FROM attendance WHERE student_id=? AND status='Present'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$present = $stmt->get_result()->fetch_assoc()['p'];

$stmt = $conn->prepare("SELECT COUNT(*) as a FROM attendance WHERE student_id=? AND status='Absent'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$absent = $stmt->get_result()->fetch_assoc()['a'];

$percentage = $total > 0 ? round(($present / $total) * 100) : 0;
$warning = ($percentage < 75) ? "⚠ Attendance below 75%!" : "";

/* =========================
   RECENT ATTENDANCE
========================= */
$stmt = $conn->prepare("SELECT * FROM attendance WHERE student_id=? ORDER BY date DESC LIMIT 10");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$records = $stmt->get_result();

/* =========================
   SUBJECT-WISE
========================= */
$subject_data = [];

$stmt = $conn->prepare("
    SELECT 
        sub.subject_name,
        COUNT(a.id) as total,
        SUM(a.status='Present') as present,
        SUM(a.status='Absent') as absent,
        ROUND((SUM(a.status='Present') / NULLIF(COUNT(a.id),0)) * 100) as percentage
    FROM attendance a
    JOIN subjects sub ON a.subject_id = sub.id
    WHERE a.student_id = ?
    GROUP BY a.subject_id
");

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$subject_labels = [];
$subject_percentages = [];

while ($row = $result->fetch_assoc()) {
    $subject_data[] = $row;
    $subject_labels[] = $row['subject_name'];
    $subject_percentages[] = $row['percentage'];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
            overflow-x: hidden;
        }

        .container {
            display: flex;
        }

        /* Mobile Toggle */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1200;
            background: #2c3e50;
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 10px;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
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

        .sidebar a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .std-logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .std-logo i {
            font-size: 55px;
            margin-bottom: 10px;
        }

        .main {
            margin-left: 240px;
            padding: 25px;
            width: calc(100% - 240px);
            transition: all 0.3s ease;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px 25px;
            margin-bottom: 20px;
            border-radius: 12px;
            border-bottom: 5px solid #e74c3c;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .welcome h1 {
            font-size: 24px;
        }

        .photo,
        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }

        .photo {
            object-fit: cover;
            border: 2px solid #4e73df;
        }

        .avatar {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .profile,
        .subject-table,
        .subject-graph {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #ddd;
        }

        .profile p {
            margin: 10px 0;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            color: #777;
            font-size: 16px;
        }

        .card p {
            font-size: 32px;
            font-weight: 600;
            margin-top: 10px;
        }

        .total {
            border-left: 7px solid #3498db;
        }

        .present {
            border-left: 7px solid #2ecc71;
        }

        .absent {
            border-left: 7px solid #e74c3c;
        }

        .percent {
            border-left: 7px solid #9b59b6;
        }

        .alert {
            color: white;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .animate-alert {
            animation: slideFade 1s linear infinite alternate;
        }

        @keyframes slideFade {
            0% {
                background: linear-gradient(135deg, #e9a909, #ff4b2b, #ff527b);
            }

            50% {
                background: linear-gradient(135deg, #ff4b2b, #e9a909, #ff416c);
            }

            100% {
                background: linear-gradient(135deg, #ff527b, #ff4b2b, #e9a909);
            }
        }

        h2 {
            margin-bottom: 15px;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ccc;
        }

        th {
            background: #2c3e50;
            color: white;
        }

        .present-text {
            color: green;
            font-weight: 600;
        }

        .absent-text {
            color: red;
            font-weight: 600;
        }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
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



        /* Mobile Responsive */
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .sidebar {
                left: -260px;
                width: 260px;
            }

            .sidebar.active {
                left: 0;
            }

            .overlay.active {
                display: block;
            }

            .main {
                margin-left: 0;
                width: 100%;
                padding: 80px 50px 20px;
            }

            .header {
                padding: 15px;
            }

            .welcome h1 {
                font-size: 20px;
            }

            .cards {
                grid-template-columns: 1fr;
            }

            table {
                min-width: 500px;
            }

            .footer {
                font-size: 13px;
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .welcome h1 {
                font-size: 18px;
            }

            .photo,
            .avatar {
                width: 45px;
                height: 45px;
            }

            .card p {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

    <button class="menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <div class="container">
        <div class="sidebar" id="sidebar">
            <div class="std-logo">
                <i class="fas fa-user-graduate"></i>
                <h2>Student Panel</h2>
            </div>

            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="view_attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a>
            <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="main">
            <div class="header">
                <div class="welcome">
                    <h1>Welcome <?php echo htmlspecialchars($student['student_name']); ?></h1>
                </div>

                <div class="profile-photo">
                    <?php if (!empty($student['photo']) && file_exists("../uploads/" . $student['photo'])) { ?>
                        <img src="../uploads/<?php echo htmlspecialchars($student['photo']); ?>" class="photo">
                    <?php } else { ?>
                        <div class="avatar">
                            <?php echo strtoupper(substr($student['student_name'], 0, 1)); ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="profile">
                <p><b>Register No :</b> <?php echo htmlspecialchars($student['register_number']); ?></p>
                <p><b>Department :</b> <?php echo htmlspecialchars($student['department_name']); ?></p>
                <p><b>Semester :</b> <?php echo htmlspecialchars($student['semester_name']); ?></p>
            </div>

            <div class="cards">
                <div class="card total">
                    <h3>Total Classes</h3>
                    <p><?php echo $total; ?></p>
                </div>
                <div class="card present">
                    <h3>Present</h3>
                    <p><?php echo $present; ?></p>
                </div>
                <div class="card absent">
                    <h3>Absent</h3>
                    <p><?php echo $absent; ?></p>
                </div>
                <div class="card percent">
                    <h3>Percentage</h3>
                    <p><?php echo $percentage; ?>%</p>
                </div>
            </div>

            <?php if ($warning) { ?>
                <div class="alert animate-alert"><?php echo $warning; ?></div>
            <?php } ?>

            <div class="subject-table">
                <h2>Subject-wise Attendance</h2>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th>Subject</th>
                            <th>Total</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>%</th>
                        </tr>
                        <?php foreach ($subject_data as $row) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                <td><?php echo $row['total']; ?></td>
                                <td><?php echo $row['present']; ?></td>
                                <td><?php echo $row['absent']; ?></td>
                                <td style="color:<?php echo ($row['percentage'] < 75) ? 'red' : 'green'; ?>; font-weight:600;">
                                    <?php echo $row['percentage']; ?>%
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>

            <div class="subject-graph">
                <h2>Subject Graph</h2>
                <canvas id="subjectChart"></canvas>
            </div>

            <div class="subject-table">
                <h2>Recent Attendance</h2>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                        <?php while ($row = $records->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['date']; ?></td>
                                <td class="<?php echo ($row['status'] == 'Present') ? 'present-text' : 'absent-text'; ?>">
                                    <?php echo $row['status']; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
            <footer class="footer">
                <p>&copy; <?php echo date('Y'); ?> Student Attendance Management System. All Rights Reserved.</p>
            </footer>
        </div>
    </div>



    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }

        const ctx2 = document.getElementById('subjectChart').getContext('2d');

        function getGradient(ctx, chartArea) {
            const g = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
            g.addColorStop(0, '#7a6fa0');
            g.addColorStop(1, 'rgba(76, 94, 141, 0.1)');
            return g;
        }

        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($subject_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($subject_percentages); ?>,
                    barThickness: 25,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: '#eee'
                        }
                    }
                }
            },
            plugins: [{
                beforeDatasetsDraw(chart) {
                    const {
                        ctx,
                        chartArea
                    } = chart;
                    if (!chartArea) return;
                    chart.data.datasets[0].backgroundColor = getGradient(ctx, chartArea);
                }
            }]
        });
    </script>

</body>

</html>