<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Professor') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

// Create notice
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_notice'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $professorID = $pdo->query("SELECT ProfessorID FROM Professor WHERE UserID = " . $_SESSION['user_id'])->fetchColumn();
    $date = date('Y-m-d');
    $stmt = $pdo->prepare("INSERT INTO Notice (Title, Content, ProfessorID, Date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $content, $professorID, $date]);
    $success = "Notice created successfully!";
}

// Fetch professor's notices
$notices = $pdo->prepare("SELECT * FROM Notice WHERE ProfessorID = (SELECT ProfessorID FROM Professor WHERE UserID = ?)");
$notices->execute([$_SESSION['user_id']]);
$notices = $notices->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2>Manage Notices</h2>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<h4>Create Notice</h4>
<form method="post">
    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" required>
    </div>
    <div class="mb-3">
        <label for="content" class="form-label">Content</label>
        <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
    </div>
    <button type="submit" name="create_notice" class="btn btn-primary">Create Notice</button>
</form>

<h4 class="mt-4">Your Notices</h4>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Title</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($notices as $notice): ?>
            <tr>
                <td><?php echo htmlspecialchars($notice['Title']); ?></td>
                <td><?php echo $notice['Date']; ?></td>
                <td>
                    <a href="edit_notice.php?id=<?php echo $notice['NoticeID']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="delete_notice.php?id=<?php echo $notice['NoticeID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>