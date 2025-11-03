<?php
include_once('../../config.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set JSON header
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action == 'allot-staff-advisor') {
    // Get POST data
    $program = $_POST['program'] ?? '';
    $department = $_POST['department'] ?? '';
    $course = $_POST['course'] ?? '';
    $batch = $_POST['batch'] ?? '';
    $yoa = $_POST['yoa'] ?? '';
    $staff = $_POST['staff'] ?? '';

    // Validate required fields
    if (empty($program) || empty($department) || empty($course) || empty($batch) || empty($yoa) || empty($staff)) {
        echo json_encode(['response' => 'error', 'message' => 'All fields are required']);
        exit;
    }

    try {
        // Check if staff advisor already exists
        $checkStmt = $conn->prepare("SELECT * FROM `staff_advisors` WHERE department_code = :department AND program_name = :program AND course_code = :course AND batch = :batch");
        $checkStmt->execute([
            ':department' => $department,
            ':program' => $program,
            ':course' => $course,
            ':batch' => $batch
        ]);
        $exist = $checkStmt->rowCount();

        if ($exist > 0) {
            // Update existing record
            $updateStmt = $conn->prepare("UPDATE `staff_advisors` SET staff_id = :staff WHERE department_code = :department AND program_name = :program AND course_code = :course AND batch = :batch");
            $result = $updateStmt->execute([
                ':staff' => $staff,
                ':department' => $department,
                ':program' => $program,
                ':course' => $course,
                ':batch' => $batch
            ]);

            if ($result) {
                echo json_encode(['response' => 'success', 'message' => 'Staff advisor updated successfully']);
            } else {
                throw new Exception('Failed to update staff advisor');
            }
        } else {
            // Insert new record
            $insertStmt = $conn->prepare("INSERT INTO `staff_advisors` (department_code, program_name, course_code, batch, staff_id, year_of_admission) VALUES (:department, :program, :course, :batch, :staff, :yoa)");
            $result = $insertStmt->execute([
                ':department' => $department,
                ':program' => $program,
                ':course' => $course,
                ':batch' => $batch,
                ':staff' => $staff,
                ':yoa' => $yoa
            ]);

            if ($result) {
                echo json_encode(['response' => 'success', 'message' => 'Staff advisor added successfully']);
            } else {
                throw new Exception('Failed to insert staff advisor');
            }
        }

    } catch (PDOException $e) {
        error_log("Database error in allot_staff_advisor_form: " . $e->getMessage());
        echo json_encode([
            'response' => 'error', 
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        error_log("General error in allot_staff_advisor_form: " . $e->getMessage());
        echo json_encode([
            'response' => 'error', 
            'message' => 'System error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'response' => 'error', 
        'message' => 'Invalid action'
    ]);
}
?>