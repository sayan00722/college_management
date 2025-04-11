<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

// Fetch all notices
$notices = $pdo->query("SELECT n.NoticeID, n.Title, n.Date, p.Name AS ProfessorName 
                        FROM Notice n 
                        JOIN Professor p ON n.ProfessorID = p.ProfessorID")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>Manage Notices (Admin)</h2>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Title</th>
            <th>Professor</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($notices as $notice): ?>
            <tr>
                <td><?php echo htmlspecialchars($notice['Title']); ?></td>
                <td><?php echo htmlspecialchars($notice['ProfessorName']); ?></td>
                <td><?php echo $notice['Date']; ?></td>
                <td>
                    <a href="view_notice.php?id=<?php echo $notice['NoticeID']; ?>" class="btn btn-sm btn-info">View</a>
                    <a href="delete_notice.php?id=<?php echo $notice['NoticeID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>