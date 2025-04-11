<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Professor') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

// Get professor ID
$stmt = $pdo->prepare("SELECT ProfessorID FROM Professor WHERE UserID = ?");
$stmt->execute([$_SESSION['user_id']]);
$professorID = $stmt->fetchColumn();

// Fetch all exams created by this professor (through courses assigned)
$stmt = $pdo->prepare("
    SELECT e.ExamID, e.Title, e.ExamDate, e.TotalMarks, co.CourseName, co.CourseID
    FROM Exam e
    JOIN Course co ON e.CourseID = co.CourseID
    JOIN Class c ON c.CourseID = co.CourseID
    WHERE c.ProfessorID = ?
    GROUP BY e.ExamID
");
$stmt->execute([$professorID]);
$exams = $stmt->fetchAll();

if (isset($_POST['submit_marks'])) {
    $examID = $_POST['exam_id'];
    foreach ($_POST['marks'] as $studentID => $marks) {
        // Check if result exists
        $stmt = $pdo->prepare("SELECT ResultID FROM Result WHERE ExamID = ? AND StudentID = ?");
        $stmt->execute([$examID, $studentID]);
        if ($stmt->rowCount() > 0) {
            // Update
            $update = $pdo->prepare("UPDATE Result SET MarksObtained = ? WHERE ExamID = ? AND StudentID = ?");
            $update->execute([$marks, $examID, $studentID]);
        } else {
            // Insert
            $insert = $pdo->prepare("INSERT INTO Result (ExamID, StudentID, MarksObtained) VALUES (?, ?, ?)");
            $insert->execute([$examID, $studentID, $marks]);
        }
    }
    $success = "Marks submitted successfully.";
}
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">Enter Results for Exams</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php foreach ($exams as $exam): ?>
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <strong><?php echo htmlspecialchars($exam['Title']); ?></strong><br>
            <?php echo htmlspecialchars($exam['CourseName']) . " — " . htmlspecialchars($exam['ExamDate']); ?>
        </div>
        <div class="card-body">
            <form method="post">
                <input type="hidden" name="exam_id" value="<?php echo $exam['ExamID']; ?>">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Marks Obtained</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT s.StudentID, s.Name, r.MarksObtained
                            FROM Enrollment e
                            JOIN Student s ON e.StudentID = s.StudentID
                            LEFT JOIN Result r ON r.StudentID = s.StudentID AND r.ExamID = ?
                            WHERE e.CourseID = ?
                        ");
                        $stmt->execute([$exam['ExamID'], $exam['CourseID']]);
                        $students = $stmt->fetchAll();
                        $totalObtained = 0;

                        foreach ($students as $student):
                            $totalObtained += $student['MarksObtained'] ?? 0;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['Name']); ?></td>
                                <td>
                                    <input type="number" name="marks[<?php echo $student['StudentID']; ?>]" class="form-control"
                                           value="<?php echo htmlspecialchars($student['MarksObtained'] ?? 0); ?>" min="0"
                                           max="<?php echo $exam['TotalMarks']; ?>" required>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><strong>Total Marks:</strong> <?php echo $exam['TotalMarks']; ?></p>
                <p><strong>Total Obtained (All Students):</strong> <?php echo $totalObtained; ?></p>
                <button type="submit" name="submit_marks" class="btn btn-success">Submit Marks</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>
