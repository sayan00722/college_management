<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['course_name'];
    $credits = $_POST['credits'];
    $dept = $_POST['department_id'];
    $stmt = $pdo->prepare("INSERT INTO Course (CourseName, Credits, DepartmentID) VALUES (?, ?, ?)");
    $stmt->execute([$name, $credits, $dept]);
}

$courses = $pdo->query("SELECT c.*, d.DepartmentName 
                        FROM Course c 
                        LEFT JOIN Department d ON c.DepartmentID = d.DepartmentID")->fetchAll();

$departments = $pdo->query("SELECT * FROM Department")->fetchAll();
include '../includes/header.php';
?>

<h2>Manage Courses</h2>

<form method="POST" class="mb-3">
    <input type="text" name="course_name" placeholder="Course Name" required>
    <input type="number" name="credits" placeholder="Credits" required>
    <select name="department_id" required>
        <?php foreach ($departments as $d): ?>
            <option value="<?= $d['DepartmentID'] ?>"><?= htmlspecialchars($d['DepartmentName']) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-primary">Add Course</button>
</form>

<table class="table table-bordered">
    <thead><tr><th>Name</th><th>Credits</th><th>Department</th></tr></thead>
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

<?php include '../includes/footer.php'; ?>
