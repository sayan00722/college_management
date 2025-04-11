<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

// Fetch attendance records
$stmt = $pdo->prepare("SELECT a.Date, a.Status, c.CourseName 
                       FROM Attendance a 
                       JOIN Class cl ON a.ClassID = cl.ClassID 
                       JOIN Course c ON cl.CourseID = c.CourseID 
                       WHERE a.StudentID = (SELECT StudentID FROM Student WHERE UserID = ?)");
$stmt->execute([$_SESSION['user_id']]);
$attendance = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>View Attendance</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Course</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($attendance as $record): ?>
            <tr>
                <td><?php echo htmlspecialchars($record['CourseName']); ?></td>
                <td><?php echo $record['Date']; ?></td>
                <td><?php echo $record['Status']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>