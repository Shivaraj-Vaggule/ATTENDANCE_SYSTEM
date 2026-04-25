<?php
session_start();
include("../config/db.php");

/* =========================
   CHECK SESSION
========================= */
$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    header("Location: ../login.php");
    exit();
}

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
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Student not found!";
    exit();
}

$student = $result->fetch_assoc();

/* =========================
   SUBJECTS
========================= */
$subjects = $conn->query("
    SELECT * FROM subjects
    WHERE department_id = {$student['department_id']}
    AND semester_id = {$student['semester_id']}
");

/* =========================
   FULL MONTH DATES
========================= */
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

$month = str_pad($month, 2, "0", STR_PAD_LEFT);

$start = "$year-$month-01";
$end = date("Y-m-t", strtotime($start));

$date_array = [];

$period = new DatePeriod(
    new DateTime($start),
    new DateInterval('P1D'),
    (new DateTime($end))->modify('+1 day')
);

foreach ($period as $date) {
    $date_array[] = $date->format("Y-m-d");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Attendance Report</title>

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2c3e50;
            color: white;
            padding: 25px 25px;
            margin-bottom: 30px;
        }

        .header h2 {
            margin: 0;
        }

        .header a {
            text-decoration: none;
            color: white;
            background: #3498db;
            padding: 8px 12px;
            border-radius: 5px;
            margin-left: 10px;
            transition: 0.3s;
        }

        .header a:hover {
            background: #378dc5;
        }

        .container {
            padding: 40px;
        }


        .month-container {
            margin: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;

        }

        .month-container select {
            margin-right: 10px;
            padding: 8px 12px;
        }

        .month-container select:focus {
            border: 2px solid #b890c1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.11);
        }

        .month-container button {
            margin-left: 10px;
            border: none;
            padding: 8.5px 15px;
            background: #b890c1;
            color: white;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .month-container button:hover {
            background: #ac87b4;
        }

        .month-text h2 {
            color: #0f0c0c;
        }

        .table-container {
            width: 100%;
            margin: auto;
            overflow-x: auto;
            border: 1px solid #ccc;
            background: white;
        }

        table {
            border-collapse: collapse;
            min-width: 1200px;
            width: 100%;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
            white-space: nowrap;
        }

        th {
            background: #2c3e50;
            color: white;
            position: sticky;
            top: 0;
            z-index: 3;
        }

        .day-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            line-height: 1.2;
        }

        .day-name {
            font-size: 11px;
            opacity: 0.8;
        }

        .day-date {
            font-size: 14px;
            font-weight: bold;
        }

        /* STATUS COLORS */
        .present {
            background: green;
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
        }

        .absent {
            background: red;
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
        }

        .not {
            background: #ccc;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
        }

        /* FREEZE SUBJECT COLUMN */
        td:first-child,
        th:first-child {
            position: sticky;
            left: 0;
            background: #2c3e50;
            color: white;
            z-index: 5;
        }

        .note {
            color: #dc3545;
        }

        .export-btn {
            padding: 10px;
            background-color: #27ae60;
            margin-top: 20px;
            border: none;
            transition: 0.3s;
        }

        .export-btn:hover {
            background-color: #24a259;
        }

        .summary {
            width: 100%;
            margin: 20px auto;
            background: #d0e1c9;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2><?php echo $student['student_name']; ?></h2>
        <h2>Monthly Attendance</h2>
        <div>

            <a href="../student/dashboard.php">Home</a>
        </div>
    </div>

    <div class="container">
        <div class="month-container">
            <div>
                <form method="GET" style="text-align:center; margin:20px;">

                    <select name="month">
                        <?php for ($m = 1; $m <= 12; $m++) { ?>
                            <option value="<?php echo $m; ?>"
                                <?php if (date('m') == $m) echo "selected"; ?>>
                                <?php echo date("F", mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php } ?>
                    </select>

                    <select name="year">
                        <?php for ($y = 2020; $y <= date('Y'); $y++) { ?>
                            <option value="<?php echo $y; ?>"
                                <?php if (date('Y') == $y) echo "selected"; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <button class="filter-btn" type="submit">Filter</button>
                </form>
            </div>

            <!-- selected month & year -->
            <?php
            $month_name = date("F", mktime(0, 0, 0, $month, 1));
            ?>
            <div class="month-text">

                <h2><?php echo $month_name . " " . $year; ?></h2>
            </div>

        </div>

        <!-- table -->
        <div class="table-container">
            <table>

                <tr>
                    <th>Subject</th>

                    <?php foreach ($date_array as $date) { ?>
                        <th>
                            <div class="day-header">
                                <span class="day-name"><?php echo date("D", strtotime($date)); ?></span>
                                <span class="day-date"><?php echo date("d", strtotime($date)); ?></span>
                            </div>
                        </th>
                    <?php } ?>

                    <th>Total</th>
                    <th>%</th>
                </tr>

                <?php
                $total_present_all = 0;
                $total_days_all = 0;

                while ($sub = $subjects->fetch_assoc()) {

                    $present_count = 0;
                    $total_days = 0;

                    echo "<tr>";
                    echo "<td>" . $sub['subject_name'] . "</td>";

                    foreach ($date_array as $date) {

                        $att = $conn->query("
            SELECT status FROM attendance
            WHERE student_id = '$student_id'
            AND subject_id = '" . $sub['id'] . "'
            AND date = '$date'
        ");

                        if ($att->num_rows > 0) {
                            $row = $att->fetch_assoc();

                            if ($row['status'] == "Present") {
                                echo "<td><span class='present'>P</span></td>";
                                $present_count++;
                            } else {
                                echo "<td><span class='absent'>A</span></td>";
                            }

                            $total_days++;
                        } else {
                            echo "<td><span class='not'>-</span></td>";
                        }
                    }

                    $percentage = ($total_days > 0) ? round(($present_count / $total_days) * 100) : 0;

                    echo "<td>$present_count/$total_days</td>";
                    echo "<td>$percentage%</td>";
                    echo "</tr>";

                    $total_present_all += $present_count;
                    $total_days_all += $total_days;
                }
                ?>

            </table>
        </div>
        <p class="note">Note: In this table Symbols indicates("-" => Not taken attendance) || ("P" => Present) || ("A" => Absent ) </p>

        <form method="POST" action="export_pdf.php" target="_blank">
            <input type="hidden" name="month" value="<?php echo $month; ?>">
            <input type="hidden" name="year" value="<?php echo $year; ?>">
            <button class="export-btn">📄 Export PDF</button>
        </form>

        <div class="summary">
            <b>Summary Of Attendance</b><br><br>

            Present Days: <b><?php echo "$total_present_all / $total_days_all"; ?></b><br>

            Percentage:
            <b>
                <?php echo ($total_days_all > 0) ? round(($total_present_all / $total_days_all) * 100) : 0; ?>%
            </b>
        </div>

    </div>


</body>

</html>