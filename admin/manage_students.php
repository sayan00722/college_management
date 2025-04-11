<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}
include '../config/db.php';

// Handle filters
$filterDept = $_GET['department_id'] ?? '';
$filterCourse = $_GET['course_id'] ?? '';

// Pagination
$limit = 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

// Fetch departments and courses
$departments = $pdo->query("SELECT * FROM Department")->fetchAll(PDO::FETCH_ASSOC);

// Fetch filtered students
$query = "SELECT s.*, d.DepartmentName 
          FROM Student s 
          LEFT JOIN Department d ON s.DepartmentID = d.DepartmentID";
$params = [];
$conditions = [];

if ($filterDept) {
    $conditions[] = "s.DepartmentID = ?";
    $params[] = $filterDept;
}
if ($filterCourse) {
    $query .= " JOIN Enrollment e ON s.StudentID = e.StudentID";
    $conditions[] = "e.CourseID = ?";
    $params[] = $filterCourse;
}
if ($conditions) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$total = $pdo->prepare($query);
$total->execute($params);
$totalStudents = $total->rowCount();

$query .= " LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Fetch courses (if needed)
$courses = $pdo->query("SELECT * FROM Course")->fetchAll(PDO::FETCH_ASSOC);

// Handle add student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash('student123', PASSWORD_DEFAULT);
    $deptID = $_POST['department_id'];
    $courseID = $_POST['course_id'];

    // Insert into User
    $stmt = $pdo->prepare("INSERT INTO User (Email, Password, Role) VALUES (?, ?, 'Student')");
    $stmt->execute([$email, $password]);
    $userID = $pdo->lastInsertId();

    // Insert into Student
    $stmt = $pdo->prepare("INSERT INTO Student (UserID, Name, DepartmentID) VALUES (?, ?, ?)");
    $stmt->execute([$userID, $name, $deptID]);
    $studentID = $pdo->lastInsertId();

    // Enroll in course
    $stmt = $pdo->prepare("INSERT INTO Enrollment (StudentID, CourseID, Semester) VALUES (?, ?, 'Spring 2025')");
    $stmt->execute([$studentID, $courseID]);

    header("Location: manage_students.php");
    exit;
}

// Handle delete student
if (isset($_GET['delete'])) {
    $studentID = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT UserID FROM Student WHERE StudentID = ?");
    $stmt->execute([$studentID]);
    $userID = $stmt->fetchColumn();

    $pdo->prepare("DELETE FROM Enrollment WHERE StudentID = ?")->execute([$studentID]);
    $pdo->prepare("DELETE FROM Student WHERE StudentID = ?")->execute([$studentID]);
    $pdo->prepare("DELETE FROM User WHERE UserID = ?")->execute([$userID]);

    header("Location: manage_students.php");
    exit;
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Manage Students</h2>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <select name="department_id" id="filterDepartment" class="form-select">
                <option value="">Filter by Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['DepartmentID'] ?>" <?= ($filterDept == $dept['DepartmentID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['DepartmentName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <select name="course_id" id="filterCourse" class="form-select">
                <option value="">Filter by Course</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-2">
            <a href="manage_students.php" class="btn btn-secondary w-100">Reset</a>
        </div>
    </form>



    <!-- Student List -->
    <table class="table table-bordered">
        <thead>
            <tr><th>Name</th><th>Department</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= htmlspecialchars($student['Name']) ?></td>
                    <td><?= htmlspecialchars($student['DepartmentName']) ?></td>
                    <td>
                        <a href="?delete=<?= $student['StudentID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete student?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= ceil($totalStudents / $limit); $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&department_id=<?= $filterDept ?>&course_id=<?= $filterCourse ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<!-- JS for dependent dropdown -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const departmentSelect = document.getElementById('departmentSelect');
    const courseSelect = document.getElementById('courseSelect');
    const filterDepartment = document.getElementById('filterDepartment');
    const filterCourse = document.getElementById('filterCourse');

    function loadCourses(deptID, targetSelect, selected = "") {
        targetSelect.innerHTML = '<option>Loading...</option>';
        targetSelect.disabled = true;
        fetch(`get_courses.php?department_id=${deptID}`)
            .then(res => res.json())
            .then(data => {
                targetSelect.innerHTML = '<option value="">Select Course</option>';
                data.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.CourseID;
                    option.textContent = course.CourseName;
                    if (course.CourseID == selected) option.selected = true;
                    targetSelect.appendChild(option);
                });
                targetSelect.disabled = false;
            });
    }

    if (departmentSelect) {
        departmentSelect.addEventListener('change', () => {
            loadCourses(departmentSelect.value, courseSelect);
        });
    }

    if (filterDepartment) {
        filterDepartment.addEventListener('change', () => {
            loadCourses(filterDepartment.value, filterCourse);
        });

        // Load on page load if pre-selected
        <?php if ($filterDept): ?>
        loadCourses(<?= json_encode($filterDept) ?>, filterCourse, <?= json_encode($filterCourse) ?>);
        <?php endif; ?>
    }
});
</script>

<?php include '../includes/footer.php'; ?>
