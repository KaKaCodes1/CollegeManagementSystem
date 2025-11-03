<?php
session_start();
include_once(__DIR__ . '/config.php'); // config.php must define $conn as PDO

// Function to get client IP address
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Function to get browser information
function getBrowserInfo() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $browser = "Unknown";
    $os = "Unknown";

    // Get Browser
    if (preg_match('/MSIE/i', $user_agent) && !preg_match('/Opera/i', $user_agent)) {
        $browser = 'Internet Explorer';
    } elseif (preg_match('/Firefox/i', $user_agent)) {
        $browser = 'Mozilla Firefox';
    } elseif (preg_match('/Chrome/i', $user_agent)) {
        $browser = 'Google Chrome';
    } elseif (preg_match('/Safari/i', $user_agent)) {
        $browser = 'Apple Safari';
    } elseif (preg_match('/Opera/i', $user_agent)) {
        $browser = 'Opera';
    } elseif (preg_match('/Netscape/i', $user_agent)) {
        $browser = 'Netscape';
    }

    // Get OS
    if (preg_match('/Windows/i', $user_agent)) {
        $os = 'Windows';
    } elseif (preg_match('/Macintosh|Mac OS X/i', $user_agent)) {
        $os = 'Mac OS';
    } elseif (preg_match('/Linux/i', $user_agent)) {
        $os = 'Linux';
    } elseif (preg_match('/Android/i', $user_agent)) {
        $os = 'Android';
    } elseif (preg_match('/iOS|iPhone|iPad/i', $user_agent)) {
        $os = 'iOS';
    }

    return [
        'browser' => $browser,
        'os' => $os
    ];
}

// Function to write login log
function writeLoginLog($conn, $userid, $userrole, $status) {
    try {
        $ip = getClientIP();
        $browserInfo = getBrowserInfo();
        
        $stmt = $conn->prepare("INSERT INTO `log_login` 
            (ip, os, browser, userid, userrole, timestamp) 
            VALUES (:ip, :os, :browser, :userid, :userrole, NOW())");
        
        $stmt->execute([
            ':ip' => $ip,
            ':os' => $browserInfo['os'],
            ':browser' => $browserInfo['browser'],
            ':userid' => $userid,
            ':userrole' => $userrole
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Login log error: " . $e->getMessage());
        return false;
    }
}

if (!isset($_POST['login-submit'])) {
    header('Location: index.php');
    exit;
}

$LoginEmail = trim($_POST['login-email']);
$LoginPassword = md5(trim($_POST['login-password'])); // MD5 for testing
$LoginRole = trim($_POST['login-role']);

$allowedRoles = [
    'admin' => 'login_admin',
    'staff' => 'login_staff',
    'student' => 'login_student'
];

if (!isset($allowedRoles[$LoginRole])) {
    // Log failed login attempt (invalid role)
    writeLoginLog($conn, $LoginEmail, 'invalid_role', 'Failed - Invalid Role');
    header('Location: index.php?login-error=role');
    exit;
}

$LoginTable = $allowedRoles[$LoginRole];

try {
    $stmt = $conn->prepare("SELECT * FROM `$LoginTable` WHERE email = ?");
    $stmt->execute([$LoginEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // User not found - log failed attempt
        writeLoginLog($conn, $LoginEmail, $LoginRole, 'Failed - User Not Found');
        header('Location: index.php?login-error=user');
        exit;
    } elseif ($user['password'] !== $LoginPassword) {
        // Wrong password - log failed attempt
        writeLoginLog($conn, $user['userid'] ?? $LoginEmail, $LoginRole, 'Failed - Wrong Password');
        header('Location: index.php?login-error=password');
        exit;
    } else {
        // Successful login
        $userid = $user['userid'] ?? $user['email'];
        $userrole = $user['role'] ?? $LoginRole;
        
        // Log successful login
        writeLoginLog($conn, $userid, $userrole, 'Success');

        $UserAuthData = [
            'status' => 'valid',
            'email' => $user['email'],
            'userid' => $user['userid'],
            'name' => $user['name'],
            'role' => $user['role']
        ];

        if ($LoginRole == 'staff') $UserAuthData['type'] = $user['type'];
        if ($LoginRole == 'staff' || $LoginRole == 'student') $UserAuthData['department'] = $user['department'];
        if ($LoginRole == 'student') $UserAuthData['admission_number'] = $user['admission_number'];

        $_SESSION['UserAuthData'] = $UserAuthData;

        header('Location: dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    // Database error - log failed attempt
    writeLoginLog($conn, $LoginEmail, $LoginRole, 'Failed - Database Error');
    header('Location: index.php?login-error=database');
    exit;
}
?>