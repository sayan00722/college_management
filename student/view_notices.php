<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

// Get student ID
$stmt = $pdo->prepare("SELECT StudentID FROM Student WHERE UserID = ?");
$stmt->execute([$_SESSION['user_id']]);
$studentID = $stmt->fetchColumn();

// Get notices
$query = $pdo->query("SELECT n.Title, n.Content, n.Date, p.Name AS ProfessorName 
                      FROM Notice n 
                      JOIN Professor p ON n.ProfessorID = p.ProfessorID 
                      ORDER BY n.Date DESC");
$notices = $query->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2 class="mb-4">Class Notices</h2>

<?php if ($notices): ?>
    <ul class="list-group">
        <?php foreach ($notices as $notice): ?>
            <li class="list-group-item">
                <strong><?= htmlspecialchars($notice['Title']) ?></strong><br>
                <?= nl2br(htmlspecialchars($notice['Content'])) ?><br>
                <small>By <?= htmlspecialchars($notice['ProfessorName']) ?> on <?= htmlspecialchars($notice['Date']) ?></small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No notices found.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
