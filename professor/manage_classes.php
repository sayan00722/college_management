<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professor') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

// Fetch professor's classes
$stmt = $pdo->prepare("SELECT c.ClassID, co.CourseName 
                       FROM Class c 
                       JOIN Course co ON c.CourseID = co.CourseID 
                       WHERE c.ProfessorID = (SELECT ProfessorID FROM Professor WHERE UserID = ?)");
$stmt->execute([$_SESSION['user_id']]);
$classes = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>Manage Classes</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Class ID</th>
            <th>Course Name</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($classes as $class): ?>
            <tr>
                <td><?php echo $class['ClassID']; ?></td>
                <td><?php echo htmlspecialchars($class['CourseName']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>