<?php
include_once('../../config.php');

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action == 'add_new_college_admin') {
    function random_string($length) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));
        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        return $key;
    }

    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $designation = $_POST['designation'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($designation)) {
        echo json_encode(['response' => 'error', 'message' => 'All fields are required']);
        exit;
    }

    $cleaned_phone = preg_replace('/\D/', '', $phone);
    if (strlen($cleaned_phone) !== 10) {
        echo json_encode(['response' => 'error', 'message' => 'Phone number must be 10 digits']);
        exit;
    }

    $password = strtolower(str_replace(' ', '', $name)) . $cleaned_phone;
    $password2 = $password;
    $password_hashed = md5($password);
    $userid = rand(123, 1795) . strtolower(preg_replace('/\s+/', '', $name)) . random_string(5);

    if ($designation != 'admin') {
        $table = 'login_college_admin';
    } else {
        $table = 'login_admin';
    }

    try {
        // For non-admin roles, check if designation already exists
        if ($designation != 'admin') {
            $checkStmt = $conn->prepare("SELECT * FROM `$table` WHERE role = :designation");
            $checkStmt->execute([':designation' => $designation]);
            $principalCheckCount = $checkStmt->rowCount();

            if ($principalCheckCount > 0) {
                // Update existing record - ALWAYS include login_log
                $updateStmt = $conn->prepare("UPDATE `$table` SET userid = :userid, name = :name, email = :email, phone = :phone, password = :password, login_log = :login_log WHERE role = :designation");
                $result = $updateStmt->execute([
                    ':userid' => $userid,
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $cleaned_phone,
                    ':password' => $password_hashed,
                    ':login_log' => '[]',
                    ':designation' => $designation
                ]);

                if ($result) {
                    echo json_encode(['response' => 'success', 'pass' => $password2, 'userid' => $userid]);
                } else {
                    throw new Exception('Failed to update record');
                }
            } else {
                // Insert new record - ALWAYS include login_log
                $insertStmt = $conn->prepare("INSERT INTO `$table` (userid, name, email, phone, password, role, login_log) VALUES (:userid, :name, :email, :phone, :password, :designation, :login_log)");
                $result = $insertStmt->execute([
                    ':userid' => $userid,
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $cleaned_phone,
                    ':password' => $password_hashed,
                    ':designation' => $designation,
                    ':login_log' => '[]'
                ]);

                if ($result) {
                    echo json_encode(['response' => 'success', 'pass' => $password2, 'userid' => $userid]);
                } else {
                    throw new Exception('Failed to insert record');
                }
            }
        } else {
            // For admin role - ALWAYS include login_log
            $insertStmt = $conn->prepare("INSERT INTO `$table` (userid, name, email, phone, password, role, login_log) VALUES (:userid, :name, :email, :phone, :password, :designation, :login_log)");
            $result = $insertStmt->execute([
                ':userid' => $userid,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $cleaned_phone,
                ':password' => $password_hashed,
                ':designation' => $designation,
                ':login_log' => '[]'
            ]);

            if ($result) {
                echo json_encode(['response' => 'success', 'pass' => $password2, 'userid' => $userid]);
            } else {
                throw new Exception('Failed to insert admin record');
            }
        }

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['response' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['response' => 'error', 'message' => 'Invalid action']);
}
?>