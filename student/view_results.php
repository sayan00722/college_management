<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

// Get student ID
$stmt = $pdo->prepare("SELECT StudentID FROM Student WHERE UserID = ?");
$stmt->execute([$_SESSION['user_id']]);
$studentID = $stmt->fetchColumn();

// Get student's exam results
$stmt = $pdo->prepare("SELECT r.ExamID, r.MarksObtained, e.TotalMarks, e.ExamDate, c.CourseName 
                       FROM Result r
                       JOIN Exam e ON r.ExamID = e.ExamID
                       JOIN Course c ON e.CourseID = c.CourseID
                       WHERE r.StudentID = ?
                       ORDER BY e.ExamDate DESC");
$stmt->execute([$studentID]);
$results = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2 class="mb-4">Your Exam Results</h2>

<?php if ($results): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Course</th>
                <th>Exam Date</th>
                <th>Obtained Marks</th>
                <th>Total Marks</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $result): 
                $percentage = ($result['MarksObtained'] / $result['TotalMarks']) * 100;
                if ($percentage >= 90) $grade = 'A+';
                elseif ($percentage >= 80) $grade = 'A';
                elseif ($percentage >= 70) $grade = 'B';
                elseif ($percentage >= 60) $grade = 'C';
                elseif ($percentage >= 50) $grade = 'D';
                else $grade = 'F';
            ?>
                <tr>
                    <td><?= htmlspecialchars($result['CourseName']) ?></td>
                    <td><?= htmlspecialchars($result['ExamDate']) ?></td>
                    <td><?= htmlspecialchars($result['MarksObtained']) ?></td>
                    <td><?= htmlspecialchars($result['TotalMarks']) ?></td>
                    <td><strong><?= $grade ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You have no exam results available yet.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
