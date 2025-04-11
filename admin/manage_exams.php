<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

// Fetch courses
$courses = $pdo->query("SELECT * FROM Course")->fetchAll();

// Create exam
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_exam'])) {
    $courseID = $_POST['course_id'];
    $examDate = $_POST['exam_date'];
    $totalMarks = $_POST['total_marks'];
    $stmt = $pdo->prepare("INSERT INTO Exam (CourseID, ExamDate, TotalMarks) VALUES (?, ?, ?)");
    $stmt->execute([$courseID, $examDate, $totalMarks]);
    $success = "Exam created successfully!";
}

// Fetch exams
$exams = $pdo->query("SELECT e.ExamID, e.ExamDate, e.TotalMarks, c.CourseName 
                      FROM Exam e 
                      JOIN Course c ON e.CourseID = c.CourseID")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>Manage Exams</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<h4>Create Exam</h4>
<form method="post">
    <div class="mb-3">
        <label for="course_id" class="form-label">Course</label>
        <select class="form-select" id="course_id" name="course_id" required>
            <option value="">Select Course</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?php echo $course['CourseID']; ?>"><?php echo htmlspecialchars($course['CourseName']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="exam_date" class="form-label">Exam Date</label>
        <input type="date" class="form-control" id="exam_date" name="exam_date" required>
    </div>
    <div class="mb-3">
        <label for="total_marks" class="form-label">Total Marks</label>
        <input type="number" class="form-control" id="total_marks" name="total_marks" required>
    </div>
    <button type="submit" name="create_exam" class="btn btn-primary">Create Exam</button>
</form>

<h4 class="mt-4">Exams List</h4>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Course</th>
            <th>Exam Date</th>
            <th>Total Marks</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($exams as $exam): ?>
            <tr>
                <td><?php echo htmlspecialchars($exam['CourseName']); ?></td>
                <td><?php echo $exam['ExamDate']; ?></td>
                <td><?php echo $exam['TotalMarks']; ?></td>
                <td>
                    <a href="edit_exam.php?id=<?php echo $exam['ExamID']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="delete_exam.php?id=<?php echo $exam['ExamID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>