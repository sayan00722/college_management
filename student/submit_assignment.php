<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

// Get StudentID
$studentID = $pdo->prepare("SELECT StudentID FROM Student WHERE UserID = ?");
$studentID->execute([$_SESSION['user_id']]);
$studentID = $studentID->fetchColumn();

// Fetch student's assignments along with submission status
$stmt = $pdo->prepare("
    SELECT 
        a.AssignmentID, 
        a.Title, 
        a.DueDate, 
        c.CourseName,
        s.SubmissionID,
        s.File AS SubmittedFile
    FROM Assignment a 
    JOIN Class cl ON a.ClassID = cl.ClassID 
    JOIN Course c ON cl.CourseID = c.CourseID
    JOIN Enrollment e ON c.CourseID = e.CourseID 
    LEFT JOIN Submission s ON s.AssignmentID = a.AssignmentID AND s.StudentID = ?
    WHERE e.StudentID = ?
");
$stmt->execute([$studentID, $studentID]);
$assignments = $stmt->fetchAll();

// Handle submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_assignment'])) {
    $assignmentID = $_POST['assignment_id'];

    // Check if already submitted
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM Submission WHERE AssignmentID = ? AND StudentID = ?");
    $checkStmt->execute([$assignmentID, $studentID]);
    if ($checkStmt->fetchColumn() > 0) {
        $error = "You have already submitted this assignment.";
    } else {
        $submissionDate = date('Y-m-d H:i:s');

        // File upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $uploadDir = '../uploads/assignments/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = basename($_FILES['file']['name']);
            $targetFile = $uploadDir . time() . '_' . $filename;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                $relativePath = str_replace('../', '', $targetFile); // Save relative path
                $stmt = $pdo->prepare("INSERT INTO Submission (AssignmentID, StudentID, SubmissionDate, File) VALUES (?, ?, ?, ?)");
                $stmt->execute([$assignmentID, $studentID, $submissionDate, $relativePath]);
                $success = "Assignment submitted successfully!";
                header("Location: submit_assignment.php"); // Refresh to reflect submitted status
                exit;
            } else {
                $error = "Failed to upload file.";
            }
        } else {
            $error = "File is required.";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2>Submit Assignment</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php elseif (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<h4>Available Assignments</h4>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Course</th>
            <th>Title</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($assignments as $assignment): ?>
            <tr>
                <td><?php echo htmlspecialchars($assignment['CourseName']); ?></td>
                <td><?php echo htmlspecialchars($assignment['Title']); ?></td>
                <td><?php echo htmlspecialchars($assignment['DueDate']); ?></td>
                <td>
                    <?php if ($assignment['SubmissionID']): ?>
                        <span class="badge bg-success">Submitted</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Pending</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!$assignment['SubmissionID']): ?>
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="assignment_id" value="<?php echo $assignment['AssignmentID']; ?>">
                            <input type="file" name="file" required>
                            <button type="submit" name="submit_assignment" class="btn btn-sm btn-primary">Submit</button>
                        </form>
                    <?php else: ?>
                        <a href="../<?php echo htmlspecialchars($assignment['SubmittedFile']); ?>" class="btn btn-sm btn-secondary" target="_blank">View Submission</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
