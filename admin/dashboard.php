<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

$totalStudents = $pdo->query("SELECT COUNT(*) FROM Student")->fetchColumn();
$totalProfessors = $pdo->query("SELECT COUNT(*) FROM Professor")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM Course")->fetchColumn();
?>

<?php include '../includes/header.php'; ?>

<h2 class="mb-4">Admin Dashboard</h2>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-users"></i> Total Students</h5>
                <p class="card-text fs-4"><?php echo $totalStudents; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-chalkboard-teacher"></i> Total Professors</h5>
                <p class="card-text fs-4"><?php echo $totalProfessors; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-book"></i> Total Courses</h5>
                <p class="card-text fs-4"><?php echo $totalCourses; ?></p>
            </div>
        </div>
    </div>
</div>
<ul class="list-group mt-4">
    <li class="list-group-item"><a href="manage_departments.php" class="text-decoration-none"><i class="fas fa-building"></i> Manage Departments</a></li>
    <li class="list-group-item"><a href="manage_professors.php" class="text-decoration-none"><i class="fas fa-chalkboard-teacher"></i> Manage Professors</a></li>
    <li class="list-group-item"><a href="manage_students.php" class="text-decoration-none"><i class="fas fa-users"></i> Manage Students</a></li>
    <li class="list-group-item"><a href="manage_courses.php" class="text-decoration-none"><i class="fas fa-book"></i> Manage Courses</a></li>
</ul>

<?php include '../includes/footer.php'; ?>