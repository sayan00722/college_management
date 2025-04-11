<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../login.php');
    exit;
}

include '../config/db.php';

// Handle department actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_department'])) {
        $departmentName = $_POST['department_name'];
        $stmt = $pdo->prepare("INSERT INTO Department (DepartmentName) VALUES (?)");
        $stmt->execute([$departmentName]);
    } elseif (isset($_POST['edit_department'])) {
        $departmentID = $_POST['department_id'];
        $departmentName = $_POST['department_name'];
        $stmt = $pdo->prepare("UPDATE Department SET DepartmentName = ? WHERE DepartmentID = ?");
        $stmt->execute([$departmentName, $departmentID]);
    } elseif (isset($_POST['delete_department'])) {
        $departmentID = $_POST['department_id'];
        $stmt = $pdo->prepare("DELETE FROM Department WHERE DepartmentID = ?");
        $stmt->execute([$departmentID]);
    } elseif (isset($_POST['add_course'])) {
        $departmentID = $_POST['department_id'];
        $courseName = $_POST['course_name'];
        $credits = $_POST['credits'];
        $stmt = $pdo->prepare("INSERT INTO Course (CourseName, DepartmentID, Credits) VALUES (?, ?, ?)");
        $stmt->execute([$courseName, $departmentID, $credits]);
    }
}

// Fetch departments with course and professor counts
$departments = $pdo->query("
    SELECT d.*, 
        (SELECT COUNT(*) FROM Course c WHERE c.DepartmentID = d.DepartmentID) AS course_count,
        (SELECT COUNT(*) FROM Professor p WHERE p.DepartmentID = d.DepartmentID) AS professor_count
    FROM Department d
")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<h2 class="mb-4">Manage Departments</h2>

<h4>Add Department</h4>
<form method="post" class="mb-4">
    <div class="input-group">
        <input type="text" class="form-control" name="department_name" placeholder="Department Name" required>
        <button type="submit" name="add_department" class="btn btn-success"><i class="fas fa-plus"></i> Add</button>
    </div>
</form>

<h4>Departments List</h4>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Courses</th>
            <th>Professors</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($departments as $dept): ?>
            <tr>
                <td><?php echo $dept['DepartmentID']; ?></td>
                <td><?php echo htmlspecialchars($dept['DepartmentName']); ?></td>
                <td><?php echo $dept['course_count']; ?></td>
                <td><?php echo $dept['professor_count']; ?></td>
                <td>
                    <!-- Edit Button -->
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $dept['DepartmentID']; ?>">
                        <i class="fas fa-edit"></i> Edit
                    </button>

                    <!-- Add Course Button -->
                    <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#addCourseModal<?php echo $dept['DepartmentID']; ?>">
                        <i class="fas fa-book"></i> Add Course
                    </button>

                    <!-- Delete Form -->
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="department_id" value="<?php echo $dept['DepartmentID']; ?>">
                        <button type="submit" name="delete_department" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?php echo $dept['DepartmentID']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-white">
                        <form method="post">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Department</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="department_id" value="<?php echo $dept['DepartmentID']; ?>">
                                <div class="mb-3">
                                    <label>Department Name</label>
                                    <input type="text" class="form-control" name="department_name" value="<?php echo htmlspecialchars($dept['DepartmentName']); ?>" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="edit_department" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add Course Modal -->
            <div class="modal fade" id="addCourseModal<?php echo $dept['DepartmentID']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-white">
                        <form method="post">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Course to <?php echo htmlspecialchars($dept['DepartmentName']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="department_id" value="<?php echo $dept['DepartmentID']; ?>">
                                <div class="mb-3">
                                    <label>Course Name</label>
                                    <input type="text" class="form-control" name="course_name" required>
                                </div>
                                <div class="mb-3">
                                    <label>Credits</label>
                                    <input type="number" class="form-control" name="credits" min="1" max="10" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="add_course" class="btn btn-success">Add Course</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
