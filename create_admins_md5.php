<?php
/**
 * create_admins_md5.php
 * Create admin records in cea_sms.login_admin using MD5 (testing only).
 *
 * - Edit DB credentials below.
 * - Edit the $admins array to add the admin(s) you want to create.
 * - Run once, then remove or protect this script.
 */

// ---------- CONFIG ----------
$dbHost   = '127.0.0.1';
$dbName   = 'cea_sms';
$dbUser   = 'root';
$dbPass   = '';      // change to your DB password
$dsn      = "mysql:host=$dbHost;dbname=$dbName;charset=utf8";

$use_md5 = true; // <<-- MD5 enabled for testing
// ---------- END CONFIG ----------

// Admins to create (plaintext password will be md5()'d)
$admins = [
    [
        'name'  => 'Ajeesh S',
        'email' => 'ajeesh@ajeesh.com',
        'phone' => '1234567890',
        'password' => 'DemoPass123!', // will be md5()'d
        'role'  => 'admin',
    ],
    [
        'name'  => 'Tittu Varghese',
        'email' => 'tittuhpd@gmail.com',
        'phone' => '1234567890',
        'password' => 'AnotherDemo123!',
        'role'  => 'admin',
    ],
];

// ---------- helpers ----------
function generate_userid($prefix = '') {
    $u = $prefix . bin2hex(random_bytes(8)) . substr(uniqid(), -6);
    return substr($u, 0, 50);
}
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ---------- main ----------
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

$insertSql = "INSERT INTO login_admin
    (userid, name, email, phone, password, role, login_log)
    VALUES (:userid, :name, :email, :phone, :password, :role, :login_log)";
$insertStmt = $pdo->prepare($insertSql);

foreach ($admins as $a) {
    $name = trim($a['name'] ?? '');
    $email = trim($a['email'] ?? '');
    $phone = trim($a['phone'] ?? '');
    $plain = (string)($a['password'] ?? '');
    $role = trim($a['role'] ?? 'admin');
    $login_log = '';

    if ($name === '' || $email === '' || $plain === '') {
        echo "Skipping: name, email and password are required.\n";
        continue;
    }
    if (!is_valid_email($email)) {
        echo "Skipping '{$name}': invalid email '{$email}'.\n";
        continue;
    }

    // MD5 chosen for testing
    if ($use_md5) {
        $pass_to_store = md5($plain);
    } else {
        $pass_to_store = password_hash($plain, PASSWORD_DEFAULT);
    }

    // generate unique userid
    $attempt = 0; $maxAttempts = 8; $userid = null;
    while ($attempt++ < $maxAttempts) {
        $candidate = generate_userid('');
        $chk = $pdo->prepare("SELECT 1 FROM login_admin WHERE userid = :u LIMIT 1");
        $chk->execute([':u' => $candidate]);
        if ($chk->fetch() === false) { $userid = $candidate; break; }
    }
    if ($userid === null) {
        echo "Failed to generate unique userid for {$name}. Skipping.\n";
        continue;
    }

    try {
        $insertStmt->execute([
            ':userid'    => $userid,
            ':name'      => $name,
            ':email'     => $email,
            ':phone'     => $phone,
            ':password'  => $pass_to_store,
            ':role'      => $role,
            ':login_log' => $login_log,
        ]);
        $newId = $pdo->lastInsertId();
        echo "Inserted admin '{$name}' (id={$newId}, userid={$userid}).\n";
    } catch (PDOException $ex) {
        echo "DB error inserting '{$name}': " . $ex->getMessage() . "\n";
    }
}

echo "Done.\n";
