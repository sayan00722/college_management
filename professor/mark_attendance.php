<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professor') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

$classID = $_GET['class_id'] ?? null;
if (!$classID) {
    die("Class ID is required.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    foreach ($_POST['attendance'] as $studentID => $status) {
        $stmt = $pdo->prepare("INSERT INTO Attendance (StudentID, ClassID, Date, Status)
                               VALUES (?, ?, ?, ?)
                               ON DUPLICATE KEY UPDATE Status = VALUES(Status)");
        $stmt->execute([$studentID, $classID, $date, $status]);
    }
    $message = "Attendance marked successfully.";
}

// Fetch enrolled students
$stmt = $pdo->prepare("SELECT s.StudentID, s.Name FROM Enrollment e
                       JOIN Student s ON e.StudentID = s.StudentID
                       WHERE e.CourseID = (SELECT CourseID FROM Class WHERE ClassID = ?)");
$stmt->execute([$classID]);
$students = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>Mark Attendance</h2>

<?php if (isset($message)): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<form method="POST">
    <div class="mb-3">
        <label for="date" class="form-label">Select Date:</label>
        <input type="date" id="date" name="date" class="form-control" required>
    </div>

    <?php foreach ($students as $student): ?>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="attendance[<?php echo $student['StudentID']; ?>]" value="Present" id="check_<?php echo $student['StudentID']; ?>" checked>
            <label class="form-check-label" for="check_<?php echo $student['StudentID']; ?>">
                <?php echo htmlspecialchars($student['Name']); ?> (Present)
            </label>
        </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-primary mt-3">Submit Attendance</button>
</form>

<?php include '../includes/footer.php'; ?>
