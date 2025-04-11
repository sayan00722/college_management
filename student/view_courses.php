<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';
include '../includes/header.php';

// Fetch student details using UserID to get StudentID
$stmt = $pdo->prepare("SELECT * FROM Student WHERE UserID = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

if (!$student) {
    echo "<p>Student not found.</p>";
    include '../includes/footer.php';
    exit;
}

// Fetch all courses offered in the student's department
$stmt = $pdo->prepare("SELECT c.*, d.DepartmentName 
                       FROM Course c 
                       JOIN Department d ON c.DepartmentID = d.DepartmentID 
                       WHERE c.DepartmentID = ?");
$stmt->execute([$student['DepartmentID']]);
$courses = $stmt->fetchAll();
?>

<h2>Available Courses in Your Department</h2>

<?php if ($courses): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Credits</th>
                <th>Department</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?= htmlspecialchars($course['CourseName']) ?></td>
                    <td><?= $course['Credits'] ?></td>
                    <td><?= htmlspecialchars($course['DepartmentName']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No courses available for your department at the moment.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
