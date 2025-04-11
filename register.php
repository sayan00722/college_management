<?php
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $departmentID = $_POST['department_id'];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO User (Email, Password, Role) VALUES (?, ?, 'Student')");
        $stmt->execute([$email, $password]);
        $userID = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO Student (UserID, Name, DepartmentID) VALUES (?, ?, ?)");
        $stmt->execute([$userID, $name, $departmentID]);
        $pdo->commit();
        $success = "Registration successful! Please log in.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Registration failed: " . $e->getMessage();
    }
}

$departments = $pdo->query("SELECT * FROM Department")->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center">Register as Student</h2>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['DepartmentID']; ?>">
                                    <?php echo htmlspecialchars($dept['DepartmentName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>