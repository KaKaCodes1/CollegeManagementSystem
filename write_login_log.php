<?php
// Make sure required variables exist
if (!isset($LoginEmail, $LoginRole, $LoginStatus)) {
    die("Missing login variables for logging.");
}

date_default_timezone_set('Africa/Nairobi');

// Collect IP
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

// Collect OS and Browser
$user_agent = $_SERVER['HTTP_USER_AGENT'];

function getOS($user_agent) {
    $os_platform = "Unknown OS";
    $os_array = [
        '/windows nt 10/i' => 'Windows 10',
        '/windows nt 6.3/i' => 'Windows 8.1',
        '/windows nt 6.2/i' => 'Windows 8',
        '/windows nt 6.1/i' => 'Windows 7',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/linux/i' => 'Linux',
        '/ubuntu/i' => 'Ubuntu',
        '/iphone/i' => 'iPhone',
        '/android/i' => 'Android',
    ];
    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $os_platform = $value;
        }
    }
    return $os_platform;
}

function getBrowser($user_agent) {
    $browser = "Unknown Browser";
    $browser_array = [
        '/msie/i' => 'Internet Explorer',
        '/firefox/i' => 'Firefox',
        '/chrome/i' => 'Chrome',
        '/safari/i' => 'Safari',
        '/opera/i' => 'Opera',
        '/mobile/i' => 'Mobile',
    ];
    foreach ($browser_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $browser = $value;
        }
    }
    return $browser;
}

$os = getOS($user_agent);
$browser = getBrowser($user_agent);
$today = date("Y-m-d H:i:s");

// Ensure log folder exists
$logFolder = __DIR__ . '/log';
if (!is_dir($logFolder)) {
    mkdir($logFolder, 0755, true);
}

$LoginLogFile = $logFolder . '/login_log.log';
$fh = fopen($LoginLogFile, 'a') or die("Can't open log file");
$LogData = "$LoginEmail - $LoginRole - $ip - $os - $browser - $today - $LoginStatus\n";
fwrite($fh, $LogData);
fclose($fh);

// Insert to database using PDO if login success
if ($LoginStatus === 'Success' && isset($conn, $UserAuthData)) {
    try {
        $stmt = $conn->prepare("INSERT INTO log_login (ip, os, browser, userid, userrole, timestamp) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ip,
            $os,
            $browser,
            $UserAuthData['userid'],
            $LoginRole,
            $today
        ]);
    } catch (PDOException $e) {
        // Log database errors silently
        error_log("Login log DB error: " . $e->getMessage());
    }
}
?>
