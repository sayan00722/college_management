<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/db.php';

// Get the StudentID using the logged-in user's UserID
$stmt = $pdo->prepare("SELECT StudentID FROM Student WHERE UserID = ?");
$stmt->execute([$_SESSION['user_id']]);
$studentID = $stmt->fetchColumn();

if (!$studentID) {
    echo "<p>Student not found.</p>";
    exit;
}

// Fetch timetable details for enrolled courses
$stmt = $pdo->prepare("
    SELECT t.Day, t.Time, co.CourseName, cl.Room
    FROM Enrollment e
    JOIN Course co ON e.CourseID = co.CourseID
    JOIN Class cl ON cl.CourseID = co.CourseID
    JOIN Timetable t ON t.ClassID = cl.ClassID
    WHERE e.StudentID = ?
    ORDER BY 
        FIELD(t.Day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
        t.Time
");
$stmt->execute([$studentID]);
$timetable = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>Timetable</h2>

<?php if ($timetable): ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Day</th>
                <th>Time</th>
                <th>Course</th>
                <th>Room</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($timetable as $entry): ?>
                <tr>
                    <td><?php echo htmlspecialchars($entry['Day']); ?></td>
                    <td><?php echo htmlspecialchars(date('H:i', strtotime($entry['Time']))); ?></td>
                    <td><?php echo htmlspecialchars($entry['CourseName']); ?></td>
                    <td><?php echo htmlspecialchars($entry['Room']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No timetable available for your enrolled courses.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
