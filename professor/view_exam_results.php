<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professor') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['course_id'])) {
    echo "Course ID not provided.";
    exit;
}

$courseID = $_GET['course_id'];
include '../config/db.php';

// Fetch exams for this course
$stmt = $pdo->prepare("SELECT * FROM Exam WHERE CourseID = ?");
$stmt->execute([$courseID]);
$exams = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2 class="mb-4">Exam Results</h2>

<?php if ($exams): ?>
    <?php foreach ($exams as $exam): ?>
        <div class="card mb-4 shadow">
            <div class="card-header bg-secondary text-white">
                <?php echo htmlspecialchars($exam['ExamTitle']) . " (" . $exam['ExamDate'] . ")"; ?>
            </div>
            <div class="card-body">

                <?php
                // Get results for this exam
                $stmt = $pdo->prepare("SELECT r.StudentID, s.Name, r.ObtainedMarks
                                       FROM Result r
                                       JOIN Student s ON r.StudentID = s.StudentID
                                       WHERE r.ExamID = ?");
                $stmt->execute([$exam['ExamID']]);
                $results = $stmt->fetchAll();

                // Statistics
                $marks = array_column($results, 'ObtainedMarks');
                $average = $marks ? round(array_sum($marks) / count($marks), 2) : 0;
                $highest = $marks ? max($marks) : 0;
                $lowest = $marks ? min($marks) : 0;
                ?>

                <p><strong>Average:</strong> <?php echo $average; ?> /
                   <strong>Highest:</strong> <?php echo $highest; ?> /
                   <strong>Lowest:</strong> <?php echo $lowest; ?></p>

                <?php if ($results): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Marks Obtained</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                                        <td><?php echo $row['ObtainedMarks']; ?></td>
                                        <td>
                                            <?php
                                            $mark = $row['ObtainedMarks'];
                                            if ($mark >= 90) echo 'A+';
                                            elseif ($mark >= 80) echo 'A';
                                            elseif ($mark >= 70) echo 'B';
                                            elseif ($mark >= 60) echo 'C';
                                            elseif ($mark >= 50) echo 'D';
                                            else echo 'F';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No results available for this exam yet.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No exams scheduled for this course.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
