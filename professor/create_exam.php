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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $classID = $_POST['class_id'];
    $title = $_POST['title'];
    $exam_date = $_POST['exam_date'];
    $total_marks = $_POST['total_marks'];

    // Get CourseID from ClassID
    $stmt = $pdo->prepare("SELECT CourseID FROM Class WHERE ClassID = ?");
    $stmt->execute([$classID]);
    $courseID = $stmt->fetchColumn();

    // Insert exam with title and total marks
    $stmt = $pdo->prepare("INSERT INTO Exam (CourseID, ExamDate, TotalMarks, Title) VALUES (?, ?, ?, ?)");
    $stmt->execute([$courseID, $exam_date, $total_marks, $title]);

    $_SESSION['message'] = "Exam created successfully.";
    header("Location: dashboard.php");
    exit;
}

// Get classes for dropdown
$stmt = $pdo->prepare("SELECT ClassID, Semester, CourseName FROM Class 
                       JOIN Course ON Class.CourseID = Course.CourseID 
                       WHERE ProfessorID = ?");
$stmt->execute([$professorID]);
$classes = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2 class="mb-4">Create Exam</h2>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<form method="post">
    <div class="mb-3">
        <label for="class_id" class="form-label">Select Class</label>
        <select name="class_id" id="class_id" class="form-select" required>
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?= $class['ClassID'] ?>">
                    <?= htmlspecialchars($class['CourseName']) ?> — <?= htmlspecialchars($class['Semester']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="title" class="form-label">Exam Title</label>
        <input type="text" name="title" id="title" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="exam_date" class="form-label">Exam Date</label>
        <input type="date" name="exam_date" id="exam_date" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="total_marks" class="form-label">Total Marks</label>
        <input type="number" name="total_marks" id="total_marks" class="form-control" min="1" max="1000" required>
    </div>

    <button type="submit" class="btn btn-primary">Create Exam</button>
</form>

<?php include '../includes/footer.php'; ?>
