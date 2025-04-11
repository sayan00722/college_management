<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user role
$stmt = $pdo->prepare("SELECT Role FROM User WHERE UserID = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<div class='alert alert-danger'>Invalid user session.</div>";
    exit();
}

$role = $user['Role'];

// Prepare timetable query based on role
if ($role == 'Professor') {
    $stmt = $pdo->prepare("
        SELECT c.CourseID, co.CourseName, t.Day, t.Time, c.Room
        FROM Professor p
        JOIN Class c ON p.ProfessorID = c.ProfessorID
        JOIN Course co ON c.CourseID = co.CourseID
        JOIN Timetable t ON c.ClassID = t.ClassID
        WHERE p.UserID = ?
        ORDER BY FIELD(t.Day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.Time
    ");
} elseif ($role == 'Student') {
    $stmt = $pdo->prepare("
        SELECT co.CourseID, co.CourseName, t.Day, t.Time, c.Room
        FROM Student s
        JOIN Enrollment e ON s.StudentID = e.StudentID
        JOIN Course co ON e.CourseID = co.CourseID
        JOIN Class c ON co.CourseID = c.CourseID
        JOIN Timetable t ON c.ClassID = t.ClassID
        WHERE s.UserID = ?
        ORDER BY FIELD(t.Day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.Time
    ");
} else {
    echo "<div class='alert alert-warning'>Only students and professors can view the timetable.</div>";
    exit();
}

$stmt->execute([$user_id]);
$timetable = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Timetable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container mt-4">
    <h3 class="mb-4">Your Timetable</h3>

    <?php if (count($timetable) > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>Course ID</th>
                    <th>Course Name</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Room</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($timetable as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['CourseID']) ?></td>
                        <td><?= htmlspecialchars($row['CourseName']) ?></td>
                        <td><?= htmlspecialchars($row['Day']) ?></td>
                        <td><?= date("g:i A", strtotime($row['Time'])) ?></td>
                        <td><?= htmlspecialchars($row['Room']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No timetable entries found for your account.</div>
    <?php endif; ?>
</div>
</body>
</html>
