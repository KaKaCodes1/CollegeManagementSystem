<?php
include_once('../../config.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set JSON header
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action == 'allot-hod') {
    // Get POST data
    $hod = $_POST['hod'] ?? '';
    $department = $_POST['department'] ?? '';

    // Validate required fields
    if (empty($hod) || empty($department)) {
        echo json_encode(['response' => 'error', 'message' => 'HOD and department are required']);
        exit;
    }

    try {
        // Start transaction for atomic operations
        $conn->beginTransaction();

        // First, set all staff in the department to 'staff' role (remove existing HOD)
        $updateStmt = $conn->prepare("UPDATE `login_staff` SET role = 'staff' WHERE role = 'hod' AND department = :department");
        $updateStmt->execute([':department' => $department]);

        // Then, set the selected staff to 'hod' role
        $hodStmt = $conn->prepare("UPDATE `login_staff` SET role = 'hod' WHERE userid = :hod AND department = :department");
        $result = $hodStmt->execute([
            ':hod' => $hod,
            ':department' => $department
        ]);

        // Check if the update was successful
        if ($result && $hodStmt->rowCount() > 0) {
            $conn->commit();
            echo json_encode([
                'response' => 'success', 
                'message' => 'HOD allotted successfully'
            ]);
        } else {
            $conn->rollBack();
            echo json_encode([
                'response' => 'error', 
                'message' => 'Failed to allot HOD. Staff member may not exist in the specified department.'
            ]);
        }

    } catch (PDOException $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        error_log("Database error in allot_hod_form: " . $e->getMessage());
        echo json_encode([
            'response' => 'error', 
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        error_log("General error in allot_hod_form: " . $e->getMessage());
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