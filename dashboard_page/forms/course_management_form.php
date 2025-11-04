<?php
include_once('../../config.php');

// Set content type for JSON response
header('Content-Type: application/json');

// Default response
$response = ['response' => 'error'];

// Check if action is set
if (!isset($_POST['action'])) {
    echo json_encode($response);
    exit;
}

$action = $_POST['action'];

try {
    if ($action == 'register-new-program') {
        $program_name = trim($_POST['program_name']);
        
        // Validate input
        if (empty($program_name)) {
            $response['message'] = 'Program name is required';
            echo json_encode($response);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO `programs` (program_name) VALUES (:program_name)");
        
        if ($stmt->execute([':program_name' => $program_name])) {
            $response['response'] = 'success';
            $response['message'] = 'Program added successfully';
        }

    } elseif ($action == 'register-new-department') {
        $department_name = trim($_POST['department_name']);
        $department_code = strtoupper(trim($_POST['department_code']));
        
        // Validate inputs
        if (empty($department_name) || empty($department_code)) {
            $response['message'] = 'Department name and code are required';
            echo json_encode($response);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO `department` (department_name, department_code) VALUES (:department_name, :department_code)");
        
        if ($stmt->execute([
            ':department_name' => $department_name,
            ':department_code' => $department_code
        ])) {
            $response['response'] = 'success';
            $response['message'] = 'Department added successfully';
        }

    } elseif ($action == 'register-new-course') {
        $course_program = trim($_POST['course_program']);
        $course_name = trim($_POST['course_name']);
        $course_code = strtoupper(trim($_POST['course_code']));
        $course_department = trim($_POST['course_department']);
        $course_batches = trim($_POST['course_batches']);
        $course_semester = trim($_POST['course_semester']);
        $course_students = trim($_POST['course_students']);
        
        // Validate all required fields
        if (empty($course_program) || empty($course_name) || empty($course_code) || 
            empty($course_department) || empty($course_batches) || empty($course_semester)) {
            $response['message'] = 'All fields are required';
            echo json_encode($response);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO `courses` 
            (program_name, course_name, course_code, department_code, course_semester, course_seats, course_batch) 
            VALUES 
            (:program_name, :course_name, :course_code, :department_code, :course_semester, :course_seats, :course_batch)");
        
        if ($stmt->execute([
            ':program_name' => $course_program,
            ':course_name' => $course_name,
            ':course_code' => $course_code,
            ':department_code' => $course_department,
            ':course_semester' => $course_semester,
            ':course_seats' => $course_students,
            ':course_batch' => $course_batches
        ])) {
            $response['response'] = 'success';
            $response['message'] = 'Course added successfully';
        }

    } else {
        $response['message'] = 'Invalid action';
    }

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>