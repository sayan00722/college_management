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

// Get enrolled courses
$stmt = $pdo->prepare("SELECT c.CourseID, c.CourseName, e.Semester, e.Grade 
                       FROM Enrollment e 
                       JOIN Course c ON e.CourseID = c.CourseID 
                       WHERE e.StudentID = ?");
$stmt->execute([$studentID]);
$courses = $stmt->fetchAll();

// Get notices
$noticeQuery = $pdo->query("SELECT n.Title, n.Content, n.Date, p.Name AS ProfessorName 
                            FROM Notice n 
                            JOIN Professor p ON n.ProfessorID = p.ProfessorID 
                            ORDER BY n.Date DESC");
$notices = $noticeQuery->fetchAll();

// Get exams
$examStmt = $pdo->prepare("SELECT e.ExamDate, e.TotalMarks, c.CourseName 
                           FROM Exam e 
                           JOIN Course c ON e.CourseID = c.CourseID 
                           WHERE e.CourseID IN (
                               SELECT CourseID FROM Enrollment WHERE StudentID = ?
                           ) ORDER BY e.ExamDate DESC");
$examStmt->execute([$studentID]);
$exams = $examStmt->fetchAll();

// Seen flags for highlights
if (!isset($_SESSION['seen_notices'])) {
    $_SESSION['seen_notices'] = false;
}
if (!isset($_SESSION['seen_exams'])) {
    $_SESSION['seen_exams'] = false;
}
$_SESSION['seen_notices'] = true;
$_SESSION['seen_exams'] = true;
?>

<?php include '../includes/header.php'; ?>

<h2 class="mb-4">Student Dashboard</h2>

<!-- Enrolled Courses -->
<h4>Your Enrolled Courses</h4>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Course</th>
            <th>Semester</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($courses as $course): ?>
            <tr>
                <td><?= htmlspecialchars($course['CourseName']) ?></td>
                <td><?= htmlspecialchars($course['Semester']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Quick Access Links -->
<ul class="list-group mt-4">
    <li class="list-group-item"><a href="view_timetable.php" class="text-decoration-none"><i class="fas fa-calendar"></i> View Timetable</a></li>
    <li class="list-group-item"><a href="view_attendance.php" class="text-decoration-none"><i class="fas fa-check-circle"></i> View Attendance</a></li>
    <li class="list-group-item"><a href="submit_assignment.php" class="text-decoration-none"><i class="fas fa-upload"></i> Submit Assignment</a></li>
    <li class="list-group-item"><a href="view_notices.php" class="text-decoration-none"><i class="fas fa-bullhorn"></i> View Notices</a></li>
    <li class="list-group-item"><a href="view_results.php" class="text-decoration-none"><i class="fas fa-chart-bar"></i> View Exam Results</a></li>
</ul>


<!-- Upcoming Exams -->
<div class="mt-5">
    <h4 class="<?= $_SESSION['seen_exams'] ? '' : 'bg-info text-white p-2' ?>">Upcoming Exams <?= $_SESSION['seen_exams'] ? '' : '(New)' ?></h4>
    <?php if ($exams): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Exam Date</th>
                    <th>Total Marks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exams as $exam): ?>
                    <tr>
                        <td><?= htmlspecialchars($exam['CourseName']) ?></td>
                        <td><?= htmlspecialchars($exam['ExamDate']) ?></td>
                        <td><?= htmlspecialchars($exam['TotalMarks']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No upcoming exams scheduled.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
