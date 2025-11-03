<?php
session_start();
include_once('header.php');
include_once('config.php');

if (!isset($_SESSION['UserAuthData'])) {
    header('Location: logout.php');
    exit;
}

$UserAuthData = $_SESSION['UserAuthData']; // no unserialize()

if ($UserAuthData['role'] === 'admin') {
    include_once('dashboard_page/admin_page.php');
} else {
    // For students or staff
    ?>
    <div class="container">
        <h3>Welcome <?php echo htmlspecialchars($UserAuthData['name']); ?></h3>
        <p>You do not have admin privileges.</p>
    </div>
    <?php
}

include_once('footer.php');
?>
