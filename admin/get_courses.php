<?php
include '../config/db.php';

if (isset($_GET['department_id'])) {
    $deptID = $_GET['department_id'];
    $stmt = $pdo->prepare("SELECT CourseID, CourseName FROM Course WHERE DepartmentID = ?");
    $stmt->execute([$deptID]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
