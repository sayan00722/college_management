<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}
include '../config/db.php';

// Handle course assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_course'])) {
    $professor_id = $_POST['professor_id'];
    $course_ids = $_POST['course_ids'] ?? [];

    foreach ($course_ids as $course_id) {
        $check = $pdo->prepare("SELECT * FROM Class WHERE CourseID = ? AND ProfessorID = ?");
        $check->execute([$course_id, $professor_id]);
        if ($check->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO Class (CourseID, ProfessorID, Semester, Room) VALUES (?, ?, 'Spring 2025', 'TBD')");
            $stmt->execute([$course_id, $professor_id]);
        }
    }
}

// Handle course unassignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unassign_course'])) {
    $professor_id = $_POST['professor_id'];
    $course_id = $_POST['course_id'];

    $stmt = $pdo->prepare("DELETE FROM Class WHERE CourseID = ? AND ProfessorID = ?");
    $stmt->execute([$course_id, $professor_id]);
}

// Handle department change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_department'])) {
    $professor_id = $_POST['professor_id'];
    $new_department = $_POST['new_department'];

    $stmt = $pdo->prepare("UPDATE Professor SET DepartmentID = ? WHERE ProfessorID = ?");
    $stmt->execute([$new_department, $professor_id]);
}

// Fetch all departments
$departments = $pdo->query("SELECT * FROM Department")->fetchAll(PDO::FETCH_ASSOC);

// Fetch professors and their departments
$professors = $pdo->query("
    SELECT p.*, d.DepartmentName 
    FROM Professor p 
    LEFT JOIN Department d ON p.DepartmentID = d.DepartmentID
")->fetchAll();

include '../includes/header.php';
?>

<h2 class="mb-4">Manage Professors</h2>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Department</th>
            <th>Assigned Courses</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($professors as $prof): ?>
            <?php
            $assigned = $pdo->prepare("
                SELECT c.CourseID, c.CourseName 
                FROM Class cl
                JOIN Course c ON cl.CourseID = c.CourseID
                WHERE cl.ProfessorID = ?
            ");
            $assigned->execute([$prof['ProfessorID']]);
            $assigned_courses = $assigned->fetchAll();
            ?>
            <tr>
                <td><?= htmlspecialchars($prof['Name']) ?></td>
                <td>
                    <form method="post" class="d-flex align-items-center">
                        <input type="hidden" name="professor_id" value="<?= $prof['ProfessorID'] ?>">
                        <select name="new_department" class="form-select form-select-sm me-2" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['DepartmentID'] ?>" <?= $dept['DepartmentID'] == $prof['DepartmentID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['DepartmentName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="update_department" class="btn btn-sm btn-outline-primary">Change</button>
                    </form>
                </td>
                <td>
                    <?php if ($assigned_courses): ?>
                        <ul class="mb-0">
                            <?php foreach ($assigned_courses as $ac): ?>
                                <li>
                                    <?= htmlspecialchars($ac['CourseName']) ?>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="professor_id" value="<?= $prof['ProfessorID'] ?>">
                                        <input type="hidden" name="course_id" value="<?= $ac['CourseID'] ?>">
                                        <button type="submit" name="unassign_course" class="btn btn-sm btn-danger btn-circle ms-2" title="Unassign">
                                            &times;
                                        </button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <em>None</em>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignCourseModal<?= $prof['ProfessorID'] ?>">
                        <i class="fas fa-plus"></i> Assign Courses
                    </button>
                </td>
            </tr>

            <!-- Assign Course Modal -->
            <div class="modal fade" id="assignCourseModal<?= $prof['ProfessorID'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-white">
                        <form method="post">
                            <div class="modal-header">
                                <h5 class="modal-title">Assign Courses to <?= htmlspecialchars($prof['Name']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="professor_id" value="<?= $prof['ProfessorID'] ?>">
                                <input type="hidden" name="assign_course" value="1">
                                <div class="mb-3">
                                    <label class="form-label">Select Courses</label>
                                    <?php
                                    $courses = $pdo->prepare("SELECT * FROM Course WHERE DepartmentID = ?");
                                    $courses->execute([$prof['DepartmentID']]);
                                    $dept_courses = $courses->fetchAll();

                                    if ($dept_courses):
                                        foreach ($dept_courses as $course):
                                            $is_assigned = in_array($course['CourseID'], array_column($assigned_courses, 'CourseID'));
                                            if (!$is_assigned):
                                    ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="course_ids[]" value="<?= $course['CourseID'] ?>" id="course<?= $course['CourseID'] ?>">
                                            <label class="form-check-label" for="course<?= $course['CourseID'] ?>">
                                                <?= htmlspecialchars($course['CourseName']) ?>
                                            </label>
                                        </div>
                                    <?php
                                            endif;
                                        endforeach;
                                    else:
                                        echo "<p class='text-muted'>No courses available.</p>";
                                    endif;
                                    ?>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Assign Selected</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
