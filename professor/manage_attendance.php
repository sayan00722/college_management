<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professor') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

// Get professor's Class list
$stmt = $pdo->prepare("SELECT c.ClassID, co.CourseName 
                       FROM Class c 
                       JOIN Course co ON c.CourseID = co.CourseID 
                       WHERE c.ProfessorID = (SELECT ProfessorID FROM Professor WHERE UserID = ?)");
$stmt->execute([$_SESSION['user_id']]);
$classes = $stmt->fetchAll();

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_attendance'])) {
    $classID = $_POST['class_id'];
    $date = $_POST['date'];
    $statuses = $_POST['attendance_status'];

    foreach ($statuses as $studentID => $status) {
        $stmt = $pdo->prepare("INSERT INTO Attendance (ClassID, StudentID, Date, Status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$classID, $studentID, $date, $status]);
    }

    $success = "Attendance marked successfully!";
}

// Get students if a class is selected
$students = [];
$selectedClassID = $_POST['class_id'] ?? '';
if ($selectedClassID) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.StudentID, s.Name
        FROM Student s
        JOIN Enrollment e ON s.StudentID = e.StudentID
        JOIN Class c ON c.CourseID = e.CourseID
        WHERE c.ClassID = ?
    ");
    $stmt->execute([$selectedClassID]);
    $students = $stmt->fetchAll();
}
?>

<?php include '../includes/header.php'; ?>

<h2>Mark Attendance</h2>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="post" class="card p-4 shadow-sm">
    <div class="mb-3">
        <label for="class_id" class="form-label">Select Class</label>
        <select class="form-select" name="class_id" id="class_id" required onchange="this.form.submit()">
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?= $class['ClassID'] ?>" <?= ($selectedClassID == $class['ClassID']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($class['CourseName']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if (!empty($students)): ?>
        <div class="mb-3">
            <label for="date" class="form-label">Select Date</label>
            <input type="date" name="date" id="date" class="form-control" required>
        </div>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['Name']) ?></td>
                        <td>
                            <select name="attendance_status[<?= $student['StudentID'] ?>]" class="form-select">
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" name="mark_attendance" class="btn btn-primary">Submit Attendance</button>
    <?php endif; ?>
</form>

<?php include '../includes/footer.php'; ?>
