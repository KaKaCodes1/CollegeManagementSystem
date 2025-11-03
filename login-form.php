<?php
session_start();
include_once(__DIR__ . '/config.php'); // config.php must define $conn as PDO

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
    header('Location: index.php?login-error=role');
    exit;
}

$LoginTable = $allowedRoles[$LoginRole];

try {
    $stmt = $conn->prepare("SELECT * FROM `$LoginTable` WHERE email = ?");
    $stmt->execute([$LoginEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $LoginStatus = "Failed";
        include(__DIR__ . '/write_login_log.php');
        header('Location: index.php?login-error=user');
        exit;
    } elseif ($user['password'] !== $LoginPassword) {
        $LoginStatus = "Failed Password";
        include(__DIR__ . '/write_login_log.php');
        header('Location: index.php?login-error=password');
        exit;
    } else {
        $LoginStatus = "Success";

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

        include(__DIR__ . '/write_login_log.php');

        header('Location: dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    $LoginStatus = "Failed DB";
    include(__DIR__ . '/write_login_log.php');
    header('Location: index.php?login-error=database');
    exit;
}
?>
