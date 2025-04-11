<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professor') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

// Get professor ID
$stmt = $pdo->prepare("SELECT ProfessorID FROM Professor WHERE UserID = ?");
$stmt->execute([$_SESSION['user_id']]);
$professorID = $stmt->fetchColumn();

// Get professor's classes
$stmt = $pdo->prepare("SELECT c.ClassID, co.CourseName, c.Semester 
                       FROM Class c 
                       JOIN Course co ON c.CourseID = co.CourseID 
                       WHERE c.ProfessorID = ?");
$stmt->execute([$professorID]);
$classes = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2 class="mb-4">Professor Dashboard</h2>

<!-- Classes taught -->
<div class="mb-4">
    <h5>Your Classes</h5>
    <ul class="list-group">
        <?php foreach ($classes as $class): ?>
            <li class="list-group-item">
                <?php echo htmlspecialchars($class['CourseName']) . " — " . htmlspecialchars($class['Semester']); ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<!-- Action Buttons -->
<h5 class="mt-5">Manage</h5>
<ul class="list-group">
    <li class="list-group-item">
        <a href="manage_attendance.php?class_id=<?php echo $class['ClassID']; ?>" class="text-decoration-none">
            <i class="fas fa-user-check me-2"></i>Mark Attendance
        </a>
    </li>
    <li class="list-group-item">
        <a href="manage_assignments.php" class="text-decoration-none">
            <i class="fas fa-tasks me-2"></i>Manage Assignments
        </a>
    </li>
    <li class="list-group-item">
        <a href="create_exam.php" class="text-decoration-none">
            <i class="fas fa-calendar-plus me-2"></i>Schedule Exams
        </a>
    </li>
    <li class="list-group-item">
        <a href="manage_notices.php" class="text-decoration-none">
            <i class="fas fa-bell me-2"></i>Manage Notices
        </a>
    </li>
    <li class="list-group-item">
        <a href="enter_results.php" class="text-decoration-none">
            <i class="fas fa-clipboard me-2"></i>Enter Results
        </a>
    </li>
    <li class="list-group-item">
        <a href="view_timetable.php" class="text-decoration-none">
            <i class="fas fa-clock me-2"></i>View Timetable
        </a>
    </li>
</ul>

<?php include '../includes/footer.php'; ?>
