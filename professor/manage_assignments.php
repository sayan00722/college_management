<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professor') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

// Get ProfessorID
$professorID = $pdo->query("SELECT ProfessorID FROM Professor WHERE UserID = " . $_SESSION['user_id'])->fetchColumn();

// Fetch classes taught by professor
$stmt = $pdo->prepare("SELECT c.ClassID, co.CourseName 
                       FROM Class c 
                       JOIN Course co ON c.CourseID = co.CourseID 
                       WHERE c.ProfessorID = ?");
$stmt->execute([$professorID]);
$classes = $stmt->fetchAll();

// Create assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_assignment'])) {
    $classID = $_POST['class_id'];
    $title = $_POST['title'];
    $dueDate = $_POST['due_date'];
    $stmt = $pdo->prepare("INSERT INTO Assignment (ClassID, Title, DueDate) VALUES (?, ?, ?)");
    $stmt->execute([$classID, $title, $dueDate]);
    $success = "Assignment created successfully!";
}

// Grade a submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grade_submission'])) {
    $submissionID = $_POST['submission_id'];
    $marks = $_POST['marks'];
    $stmt = $pdo->prepare("UPDATE Submission SET Marks = ? WHERE SubmissionID = ?");
    $stmt->execute([$marks, $submissionID]);
    $success = "Marks updated successfully!";
}

// Fetch assignments
$assignments = $pdo->prepare("SELECT a.AssignmentID, a.Title, a.DueDate, c.CourseName 
                              FROM Assignment a 
                              JOIN Class cl ON a.ClassID = cl.ClassID 
                              JOIN Course c ON cl.CourseID = c.CourseID 
                              WHERE cl.ProfessorID = ?");
$assignments->execute([$professorID]);
$assignments = $assignments->fetchAll();

// Prepare submission fetch statement (with corrected column name)
$submissionsStmt = $pdo->prepare("SELECT s.SubmissionID, s.AssignmentID, s.SubmissionDate, s.File, s.Marks, st.Name AS StudentName 
                                  FROM Submission s
                                  JOIN Student st ON s.StudentID = st.StudentID
                                  WHERE s.AssignmentID = ?");
?>

<?php include '../includes/header.php'; ?>

<h2>Manage Assignments</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<h4>Create Assignment</h4>
<form method="post">
    <div class="mb-3">
        <label for="class_id" class="form-label">Class</label>
        <select class="form-select" id="class_id" name="class_id" required>
            <option value="">Select Class</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?php echo $class['ClassID']; ?>"><?php echo htmlspecialchars($class['CourseName']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" required>
    </div>
    <div class="mb-3">
        <label for="due_date" class="form-label">Due Date</label>
        <input type="date" class="form-control" id="due_date" name="due_date" required>
    </div>
    <button type="submit" name="create_assignment" class="btn btn-primary">Create Assignment</button>
</form>

<h4 class="mt-4">Your Assignments</h4>
<?php foreach ($assignments as $assignment): ?>
    <div class="card my-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><?php echo htmlspecialchars($assignment['CourseName']) . " - " . htmlspecialchars($assignment['Title']); ?></strong>
            <small>Due: <?php echo $assignment['DueDate']; ?></small>
        </div>
        <div class="card-body">
            <h5>Submissions</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Submitted On</th>
                        <th>File</th>
                        <th>Marks</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $submissionsStmt->execute([$assignment['AssignmentID']]);
                    $submissions = $submissionsStmt->fetchAll();
                    if (count($submissions) > 0):
                        foreach ($submissions as $submission): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($submission['StudentName']); ?></td>
                                <td><?php echo $submission['SubmissionDate']; ?></td>
                                <td><a href="../uploads/assignments/<?php echo basename($submission['File']); ?>" target="_blank">Download</a></td>
                                <td>
                                    <form method="post" class="d-flex">
                                        <input type="hidden" name="submission_id" value="<?php echo $submission['SubmissionID']; ?>">
                                        <input type="number" name="marks" class="form-control me-2" value="<?php echo $submission['Marks']; ?>" min="0" max="100" required>
                                        <button type="submit" name="grade_submission" class="btn btn-success btn-sm">Save</button>
                                    </form>
                                </td>
                                <td>
                                    <?php
                                    if (is_numeric($submission['Marks'])) {
                                        $m = $submission['Marks'];
                                        if ($m >= 90) echo "A";
                                        elseif ($m >= 80) echo "B";
                                        elseif ($m >= 70) echo "C";
                                        elseif ($m >= 60) echo "D";
                                        else echo "F";
                                    } else {
                                        echo "Not Graded";
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr><td colspan="5" class="text-center">No submissions yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>
