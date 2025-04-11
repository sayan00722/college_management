<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'Admin') header('Location: admin/dashboard.php');
    elseif ($_SESSION['role'] == 'Professor') header('Location: professor/dashboard.php');
    elseif ($_SESSION['role'] == 'Student') header('Location: student/dashboard.php');
    exit;
}

include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM User WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['role'] = $user['Role'];
        if ($user['Role'] == 'Admin') header('Location: admin/dashboard.php');
        elseif ($user['Role'] == 'Professor') header('Location: professor/dashboard.php');
        elseif ($user['Role'] == 'Student') header('Location: student/dashboard.php');
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center">Login</h2>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>