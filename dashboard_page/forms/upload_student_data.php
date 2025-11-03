<?php
session_start();
include_once('../../config.php');

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check authentication
if (!isset($_SESSION['UserAuthData'])) {
    die("<div class='alert alert-danger'>Error: Unauthorized access</div>");
}

// Check CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("<div class='alert alert-danger'>Error: CSRF token validation failed</div>");
}

// Get POST data
$department = $_POST['Student-Data-Department'] ?? '';
$program = $_POST['Student-Data-Program'] ?? '';
$course_code = $_POST['Student-Data-Course'] ?? '';
$batch = $_POST['Student-Data-Batch'] ?? '';
$yoa = $_POST['Student-Data-YOA'] ?? '';

// Validate required fields
if (empty($department) || empty($program) || empty($course_code) || empty($batch) || empty($yoa)) {
    die("<div class='alert alert-danger'>Error: Missing required parameters</div>");
}

$table = 'stud_' . $yoa . '_main';

// Function to check login_student table structure
function getLoginStudentColumns($conn) {
    $columns = [];
    try {
        $stmt = $conn->query("DESCRIBE login_student");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'];
        }
        return $columns;
    } catch (PDOException $e) {
        error_log("Error getting login_student structure: " . $e->getMessage());
        return ['userid', 'admission_number', 'name', 'email', 'password', 'department', 'year_of_admission']; // default columns
    }
}

// Function to create student tables with correct column names
function createStudentTables($conn, $year_suffix) {
    $table_definitions = [
        "stud_{$year_suffix}_main" => "
            CREATE TABLE IF NOT EXISTS `stud_{$year_suffix}_main` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` varchar(100) NOT NULL,
                `department` varchar(50) NOT NULL,
                `branch` varchar(50) NOT NULL,
                `program` varchar(50) NOT NULL,
                `batch` varchar(50) NOT NULL,
                `admno` varchar(50) NOT NULL,
                `rollNo` varchar(10) NOT NULL,
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
                `currentsem` varchar(20) NOT NULL DEFAULT '1',
                `blood_group` varchar(20) NOT NULL,
                `income` varchar(20) NOT NULL,
                `email` varchar(100) NOT NULL,
                `p_email` varchar(100) NOT NULL,
                `name_localG` varchar(100) NOT NULL,
                `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `admno` (`admno`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1
        "
    ];

    foreach ($table_definitions as $table_name => $create_sql) {
        try {
            $conn->exec($create_sql);
            return true;
        } catch (PDOException $e) {
            error_log("Failed to create table {$table_name}: " . $e->getMessage());
            return false;
        }
    }
}

try {
    // First, check if table exists and create if needed
    $checkTable = $conn->query("SHOW TABLES LIKE '$table'");
    if ($checkTable->rowCount() == 0) {
        echo "<div class='alert alert-warning'>Table $table doesn't exist. Creating it now...</div>";
        
        if (!createStudentTables($conn, $yoa)) {
            throw new Exception("Failed to create required table: $table");
        }
        
        echo "<div class='alert alert-success'>Table $table created successfully!</div>";
    }

    // Check login_student table structure
    $loginColumns = getLoginStudentColumns($conn);
    echo "<div class='alert alert-info'>Login table columns: " . implode(', ', $loginColumns) . "</div>";

    // Process file upload
    $baseDir = dirname(__FILE__);
    $FileUploadPath = $baseDir . "/uploads/";

    // Create uploads directory if it doesn't exist
    if (!is_dir($FileUploadPath)) {
        mkdir($FileUploadPath, 0755, true);
    }

    // Check if file was uploaded
    if (!isset($_FILES["Student-Data-CSV"]) || $_FILES["Student-Data-CSV"]["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload failed with error code: " . $_FILES["Student-Data-CSV"]["error"]);
    }

    // Validate file
    $allowedTypes = ['text/csv', 'application/csv', 'text/plain'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedType = finfo_file($finfo, $_FILES["Student-Data-CSV"]["tmp_name"]);
    finfo_close($finfo);
    
    if (!in_array($detectedType, $allowedTypes)) {
        throw new Exception("Invalid file type. Please upload a CSV file.");
    }

    // Move uploaded file
    $fileName = $program . '_' . $department . '_' . time() . '.csv';
    $filePath = $FileUploadPath . $fileName;
    
    if (!move_uploaded_file($_FILES["Student-Data-CSV"]["tmp_name"], $filePath)) {
        throw new Exception("Failed to move uploaded file");
    }

    // Process CSV
    $file = fopen($filePath, "r");
    if (!$file) {
        throw new Exception("Failed to open uploaded file");
    }

    // Read header
    $header = fgetcsv($file);
    $expectedHeader = ['program', 'department', 'branch', 'batch', 'admission_no', 'roll_no', 'register_no', 'name', 'sex', 'address', 'religion', 'cast', 'reserv_group', 'father_name', 'father_occupation', 'year_of_admission', 'date_of_birth', 'father_mobile_number', 'local_guardian_mobile_number', 'blood_group', 'income', 'email', 'parent_email', 'name_of_local_guardian'];
    
    if (!$header) {
        throw new Exception("CSV file is empty or cannot be read");
    }

    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    $rowNumber = 1;

    // Process data rows
    while (($data = fgetcsv($file)) !== FALSE) {
        $rowNumber++;
        
        // Skip empty rows
        if (empty($data) || count(array_filter($data)) === 0) {
            continue;
        }

        // Pad data array to ensure we have enough elements
        $data = array_pad($data, 24, '');

        // Validate required fields
        if (empty(trim($data[4]))) { // admission_no
            $errors[] = "Row $rowNumber: Missing admission number";
            $errorCount++;
            continue;
        }

        // Generate user credentials
        $cleanName = preg_replace('/\s+/', '', $data[7] ?? '');
        $userid = rand(123, 1795) . strtolower($cleanName) . bin2hex(random_bytes(3));
        $password = password_hash($data[4], PASSWORD_DEFAULT);

        try {
            $conn->beginTransaction();

            // Insert into student main table
            $stmt1 = $conn->prepare("INSERT INTO `$table` 
                (user_id, department, branch, program, batch, admno, rollNo, regno, name, sex, address, religion, cast, rsvgroup, fathername, fatheroccupation, yearOfAddmission, dateOfBirth, f_mob, lg_mob, blood_group, income, email, p_email, name_localG, currentsem) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $params1 = [
                $userid,
                trim($data[1] ?? ''),  // department
                trim($data[2] ?? ''),  // branch  
                trim($data[0] ?? ''),  // program
                trim($data[3] ?? ''),  // batch
                trim($data[4] ?? ''),  // admno (admission_no)
                trim($data[5] ?? ''),  // rollNo (roll_no)
                trim($data[6] ?? ''),  // regno (register_no)
                trim($data[7] ?? ''),  // name
                trim($data[8] ?? ''),  // sex
                trim($data[9] ?? ''),  // address
                trim($data[10] ?? ''), // religion
                trim($data[11] ?? ''), // cast
                trim($data[12] ?? ''), // rsvgroup (reserv_group)
                trim($data[13] ?? ''), // fathername (father_name)
                trim($data[14] ?? ''), // fatheroccupation (father_occupation)
                trim($data[15] ?? ''), // yearOfAddmission (year_of_admission)
                trim($data[16] ?? ''), // dateOfBirth (date_of_birth)
                trim($data[17] ?? ''), // f_mob (father_mobile_number)
                trim($data[18] ?? ''), // lg_mob (local_guardian_mobile_number)
                trim($data[19] ?? ''), // blood_group
                trim($data[20] ?? ''), // income
                trim($data[21] ?? ''), // email
                trim($data[22] ?? ''), // p_email (parent_email)
                trim($data[23] ?? ''), // name_localG (name_of_local_guardian)
                '1' // currentsem (default value)
            ];

            if (!$stmt1->execute($params1)) {
                $errorInfo = $stmt1->errorInfo();
                throw new Exception("Failed to insert into student table: " . $errorInfo[2]);
            }

            // Insert into login_student table - with login_log field
            if (in_array('login_log', $loginColumns)) {
                // If login_log column exists, include it in the insert
                $stmt2 = $conn->prepare("INSERT INTO `login_student` 
                    (userid, admission_number, name, email, password, department, year_of_admission, login_log) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                $params2 = [
                    $userid,
                    trim($data[4] ?? ''),  // admission_number (admission_no)
                    trim($data[7] ?? ''),  // name
                    trim($data[21] ?? ''), // email
                    $password,
                    trim($data[1] ?? ''),  // department
                    trim($data[15] ?? ''), // year_of_admission (year_of_admission)
                    'First login' // Default value for login_log
                ];
            } else {
                // If login_log column doesn't exist, use the original query
                $stmt2 = $conn->prepare("INSERT INTO `login_student` 
                    (userid, admission_number, name, email, password, department, year_of_admission) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");

                $params2 = [
                    $userid,
                    trim($data[4] ?? ''),  // admission_number (admission_no)
                    trim($data[7] ?? ''),  // name
                    trim($data[21] ?? ''), // email
                    $password,
                    trim($data[1] ?? ''),  // department
                    trim($data[15] ?? '')  // year_of_admission (year_of_admission)
                ];
            }

            if (!$stmt2->execute($params2)) {
                $errorInfo = $stmt2->errorInfo();
                throw new Exception("Failed to insert into login table: " . $errorInfo[2]);
            }

            $conn->commit();
            $successCount++;
            echo "✓ Inserted Student " . htmlspecialchars($data[4]) . " - " . htmlspecialchars($data[7]) . "<br/>";
            flush();

        } catch (PDOException $e) {
            $conn->rollBack();
            $errorCount++;
            $errorMsg = "Row $rowNumber - " . htmlspecialchars($data[4] ?? 'unknown') . ": " . $e->getMessage();
            $errors[] = $errorMsg;
            error_log("INSERT ERROR: " . $errorMsg);
            echo "✗ Failed: " . htmlspecialchars($data[4] ?? 'unknown') . " - " . $e->getMessage() . "<br/>";
            flush();
        }
    }

    fclose($file);
    
    // Clean up
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Display summary
    echo "<br/><div class='alert alert-success'><strong>Import Summary:</strong><br/>";
    echo "Successfully imported: $successCount students<br/>";
    echo "Failed: $errorCount students</div>";

    if ($errorCount > 0 && !empty($errors)) {
        echo "<div class='alert alert-warning'><strong>First few errors:</strong><br/>";
        foreach (array_slice($errors, 0, 5) as $error) {
            echo htmlspecialchars($error) . "<br/>";
        }
        if (count($errors) > 5) {
            echo "... and " . (count($errors) - 5) . " more errors<br/>";
        }
        echo "</div>";
    }

} catch (Exception $e) {
    if (isset($filePath) && file_exists($filePath)) {
        unlink($filePath);
    }
    echo "<div class='alert alert-danger'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>