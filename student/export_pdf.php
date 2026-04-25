<?php
session_start();
include("../config/db.php");

/* CHECK SESSION */
if (!isset($_SESSION['student_id'])) {
    die("Session expired");
}

$student_id = $_SESSION['student_id'];

$month = $_POST['month'] ?? date('m');
$year = $_POST['year'] ?? date('Y');

$month = str_pad($month, 2, "0", STR_PAD_LEFT);

$start = "$year-$month-01";
$end = date("Y-m-t", strtotime($start));

/* STUDENT */
$student = $conn->query("SELECT * FROM students WHERE id='$student_id'")->fetch_assoc();

/* SUBJECTS */
$subjects = $conn->query("
SELECT * FROM subjects
WHERE department_id = {$student['department_id']}
AND semester_id = {$student['semester_id']}
");

/* DATES */
$dates = [];
$period = new DatePeriod(
    new DateTime($start),
    new DateInterval('P1D'),
    (new DateTime($end))->modify('+1 day')
);

foreach ($period as $d) {
    $dates[] = $d->format("Y-m-d");
}

/* HEADERS */
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=attendance_report.xls");
header("Pragma: no-cache");
header("Expires: 0");

/* STYLE (FOR EXCEL) */
echo "<style>
table { border-collapse: collapse; }
td, th { border:1px solid #000; padding:5px; text-align:center; }
th { background:#4e73df; color:white; font-weight:bold; }
.present { background:#1cc88a; color:white; }
.absent { background:#e74a3b; color:white; }
.not { background:#ddd; }
.title { font-size:25px; font-weight:bold; text-align:center; }
</style>";

$month_name = date("F", mktime(0, 0, 0, $month, 1));

/* TITLE */
echo "<table>";
echo "<tr><td colspan='" . (count($dates) + 3) . "' class='title'>";
echo "Attendance Report - " . $student['student_name'] . " ($month_name $year)";
echo "</td></tr>";

/* HEADER ROW */
echo "<tr>";
echo "<th>Subject</th>";

foreach ($dates as $d) {
    $day = date("D", strtotime($d));
    $date_num = date("d", strtotime($d));

    echo "<th>$day<br>$date_num</th>";
}

echo "<th>Total</th><th>%</th>";
echo "</tr>";

/* DATA */
while ($sub = $subjects->fetch_assoc()) {

    $present = 0;
    $total = 0;

    echo "<tr>";
    echo "<td>" . $sub['subject_name'] . "</td>";

    foreach ($dates as $d) {

        $att = $conn->query("
        SELECT status FROM attendance
        WHERE student_id='$student_id'
        AND subject_id='" . $sub['id'] . "'
        AND date='$d'
        ");

        if ($att->num_rows > 0) {
            $row = $att->fetch_assoc();

            if ($row['status'] == "Present") {
                echo "<td class='present'>P</td>";
                $present++;
            } else {
                echo "<td class='absent'>A</td>";
            }
            $total++;
        } else {
            echo "<td class='not'>-</td>";
        }
    }

    $per = ($total > 0) ? round(($present / $total) * 100) : 0;

    echo "<td style='mso-number-format:\"\\@\"'><b>$present/$total</b></td>";
    echo "<td style='mso-number-format:\"\\@\"'><b>$per%</b></td>";
    echo "</tr>";
}

echo "</table>";

exit();
