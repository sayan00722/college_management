<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
        <?php
        $dashboardLink = "#";
        if (isset($_SESSION['role'])) {
            switch ($_SESSION['role']) {
                case 'Admin':
                    $dashboardLink = "/college_management/admin/dashboard.php";
                    break;
                case 'Professor':
                    $dashboardLink = "/college_management/professor/dashboard.php";
                    break;
                case 'Student':
                    $dashboardLink = "/college_management/student/dashboard.php";
                    break;
            }
        }
        ?>
            <a class="navbar-brand" href="<?= $dashboardLink ?>">College Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/college_management/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/college_management/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="javascript:history.back()" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back</a>
        <?php endif; ?>
