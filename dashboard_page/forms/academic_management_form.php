<?php
include_once('../../config.php');
session_start();

// Check CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['response' => 'error', 'message' => 'CSRF token validation failed']);
    exit;
}

$action = $_POST['action'] ?? '';

// Validate and sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($action == 'register-new-batch') {
    // Validate required fields
    $required_fields = ['program_name', 'year_of_admission', 'acadamic_scheme'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['response' => 'error', 'message' => "Missing required field: $field"]);
            exit;
        }
    }

    $program_name = sanitizeInput($_POST['program_name']);
    $year_of_admission = sanitizeInput($_POST['year_of_admission']);
    $academic_scheme = sanitizeInput($_POST['acadamic_scheme']);
    $current_semester = '1';

    // Validate year format
    if (!preg_match('/^\d{4}$/', $year_of_admission)) {
        echo json_encode(['response' => 'error', 'message' => 'Invalid year format']);
        exit;
    }

    // Start transaction (PDO way)
    $conn->beginTransaction();

    try {
        // Insert academic data (PDO prepared statement)
        $stmt = $conn->prepare("INSERT INTO `academic_data` (course, admission_year, university_scheme, current_semester) VALUES (?, ?, ?, ?)");
        
        if (!$stmt->execute([$program_name, $year_of_admission, $academic_scheme, $current_semester])) {
            throw new Exception("Failed to insert academic data");
        }

        // Validate table name to prevent SQL injection
        $table_suffix = preg_replace('/[^0-9]/', '', $year_of_admission);
        if (empty($table_suffix)) {
            throw new Exception("Invalid year for table creation");
        }

        // Create tables with proper error handling
        $tables_created = createStudentTables($conn, $table_suffix);
        
        if (!$tables_created) {
            throw new Exception("Failed to create one or more tables");
        }

        // Commit transaction (PDO way)
        $conn->commit();
        
        $response = [
            'response' => 'success', 
            'message' => 'Batch registered and tables created successfully'
        ];

    } catch (Exception $e) {
        // Rollback transaction on error (PDO way)
        $conn->rollBack();
        $response = [
            'response' => 'error', 
            'message' => $e->getMessage()
        ];
    }

    echo json_encode($response);

} else if ($action == 'update_semester') {
    // Validate required fields
    $required_fields = ['update_id', 'update_semester', 'update_start_date', 'update_end_date'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['response' => 'error', 'message' => "Missing required field: $field"]);
            exit;
        }
    }

    $update_id = sanitizeInput($_POST['update_id']);
    $update_semester = sanitizeInput($_POST['update_semester']);
    $update_start_date = sanitizeInput($_POST['update_start_date']);
    $update_end_date = sanitizeInput($_POST['update_end_date']);

    // Validate ID is numeric
    if (!is_numeric($update_id)) {
        echo json_encode(['response' => 'error', 'message' => 'Invalid ID format']);
        exit;
    }

    // Validate date format
    if (!validateDate($update_start_date) || !validateDate($update_end_date)) {
        echo json_encode(['response' => 'error', 'message' => 'Invalid date format']);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE `academic_data` SET current_semester = ?, semester_starting_date = ?, semester_ending_date = ? WHERE id = ?");
        
        if ($stmt->execute([$update_semester, $update_start_date, $update_end_date, $update_id])) {
            $response = ['response' => 'success', 'message' => 'Semester updated successfully'];
        } else {
            throw new Exception("Failed to update semester");
        }
        
    } catch (Exception $e) {
        $response = ['response' => 'error', 'message' => $e->getMessage()];
    }

    echo json_encode($response);

} else {
    http_response_code(400);
    echo json_encode(['response' => 'error', 'message' => 'Invalid action']);
}

/**
 * Create student tables for a given year (PDO version)
 */
function createStudentTables($conn, $year_suffix) {
    $table_definitions = [
        "stud_{$year_suffix}_main" => "
            CREATE TABLE IF NOT EXISTS `stud_{$year_suffix}_main` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `department` varchar(50) NOT NULL,
                `branch` varchar(50) NOT NULL,
                `program` varchar(50) NOT NULL,
                `batch` varchar(50) NOT NULL,
                `admno` varchar(50) NOT NULL,
                `rollNo` int(10) NOT NULL,
                `regno` varchar(10) NOT NULL,
                `name` varchar(100) NOT NULL,
                `sex` varchar(20) NOT NULL,
                `address` varchar(500) NOT NULL,
                `religion` varchar(20) NOT NULL,
                `cast` varchar(20) NOT NULL,
                `rsvgroup` varchar(20) NOT NULL,
                `fathername` varchar(50) NOT NULL,
                `fatheroccupation` varchar(50) NOT NULL,
                `yearOfAddmission` varchar(20) NOT NULL,
                `dateOfBirth` varchar(20) NOT NULL,
                `f_mob` varchar(15) NOT NULL,
                `lg_mob` varchar(15) NOT NULL,
                `currentsem` varchar(20) NOT NULL,
                `blood_group` varchar(20) NOT NULL,
                `income` varchar(20) NOT NULL,
                `email` varchar(100) NOT NULL,
                `p_email` varchar(100) NOT NULL,
                `name_localG` varchar(100) NOT NULL,
                `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `admno` (`admno`),
                UNIQUE KEY `email` (`email`),
                INDEX `program_batch` (`program`, `batch`),
                INDEX `department_branch` (`department`, `branch`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1
        ",
        
        "stud_{$year_suffix}_attendance" => "
            CREATE TABLE IF NOT EXISTS `stud_{$year_suffix}_attendance` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `department` varchar(50) NOT NULL,
                `course` varchar(50) NOT NULL,
                `branch` varchar(50) NOT NULL,
                `semester` varchar(10) NOT NULL,
                `batch` varchar(10) NOT NULL DEFAULT '1',
                `date` date NOT NULL,
                `period` varchar(20) NOT NULL,
                `subject` varchar(20) NOT NULL,
                `type` varchar(10) NOT NULL DEFAULT 'TH',
                `duration` int(10) NOT NULL DEFAULT '1',
                `teacher` varchar(10) NOT NULL,
                `from` varchar(10) NOT NULL,
                `to` varchar(10) NOT NULL,
                `absents` varchar(500) NOT NULL,
                `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `date_subject` (`date`, `subject`),
                INDEX `batch_semester` (`batch`, `semester`),
                INDEX `teacher_date` (`teacher`, `date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1
        ",
        
        "stud_{$year_suffix}_data" => "
            CREATE TABLE IF NOT EXISTS `stud_{$year_suffix}_data` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `add_no` varchar(20) NOT NULL,
                `subject_code` varchar(20) NOT NULL,
                `category` varchar(20) NOT NULL,
                `value` varchar(20) NOT NULL,
                `remark` varchar(20) NOT NULL,
                `batch` varchar(10) NOT NULL DEFAULT '1',
                `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `add_no_subject` (`add_no`, `subject_code`),
                INDEX `batch_category` (`batch`, `category`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1
        "
    ];

    foreach ($table_definitions as $table_name => $create_sql) {
        try {
            $conn->exec($create_sql);
        } catch (PDOException $e) {
            error_log("Failed to create table {$table_name}: " . $e->getMessage());
            return false;
        }
    }
    
    return true;
}

/**
 * Validate date format
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>