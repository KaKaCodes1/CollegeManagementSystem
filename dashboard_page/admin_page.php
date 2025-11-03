<?php
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
include_once(__DIR__ . '/../config.php');


// ------------------- USER AUTHENTICATION -------------------
// Ensure the user is logged in
if (!isset($_SESSION['UserAuthData'])) {
    header('Location: ../../logout.php'); // Redirect to logout if not logged in
    exit;
}

$UserAuthData = $_SESSION['UserAuthData'];

// If stored as a serialized string, unserialize it
if (is_string($UserAuthData)) {
    $UserAuthData = unserialize($UserAuthData);
}

// Only allow admin users
if (empty($UserAuthData) || $UserAuthData['role'] !== 'admin') {
    echo "<p>Access denied. Admins only.</p>";
    exit;
}
// ------------------------------------------------------------

?>
<div class="container-fluid">
  <div class="row-fluid" id="InnerPage">
    <!-- Left Sidebar Navigation -->
    <div class="col-lg-2">
      <ul class="nav nav-pills nav-stacked">
        <li><a href="dashboard.php" id="home">Home</a></li>
        <li><a href="dashboard.php?action=course-management" id="course-management">Course Management</a></li>
        <li><a href="dashboard.php?action=academic-management">Academic</a></li>
        <li><a href="dashboard.php?action=subject-management">Subject Management</a></li>
        <li><a href="dashboard.php?action=subject-allotment">Subject Allotment</a></li>
        <li><a href="dashboard.php?action=staff-list">Staffs</a></li>
        <li><a href="dashboard.php?action=user-management">Users</a></li>
        <li><a href="dashboard.php?action=staff-advisors">Staff Advisors</a></li>
        <li><a href="dashboard.php?action=hod">HODs</a></li>
      </ul>
    </div>

    <!-- Main Content Area -->
    <div class="col-lg-8">
<?php
// ------------------- HANDLE SUBPAGES -------------------
if (!isset($_GET['action'])) {
    // No specific action, show Overview
?>
      <h2>Overview</h2>
      <table class="table table-bordered tabcenter">
        <tr>
          <td colspan="2" class="text-center"><strong>Users #</strong></td>
        </tr>
        <tr>
          <td>HODs</td>
          <td>5</td>
        </tr>
        <tr>
          <td>Staffs</td>
          <td>12</td>
        </tr>
        <tr>
          <td>Students</td>
          <td>3520</td>
        </tr>
      </table>

      <table class="table table-bordered tabcenter">
        <tr>
          <td colspan="4" class="text-center"><strong>Recent Logins</strong></td>
        </tr>
        <?php 
        // Include recent logins table if file exists
        $recentLoginsFile = __DIR__ . '/recent_logins.php';
        if (file_exists($recentLoginsFile)) {
            include_once($recentLoginsFile); 
        } else {
            echo "<tr><td colspan='4'>No recent logins available.</td></tr>";
        }
        ?>
      </table>

<?php
} else {
    // Handle subpages based on `action`
    $action = $_GET['action'];

    // Map action keys to subpage files
    $pages = [
        'course-management'   => __DIR__ . '/course_management.php',
        'academic-management' => __DIR__ . '/academic_management.php',
        'subject-management'  => __DIR__ . '/subject_management.php',
        'subject-allotment'   => __DIR__ . '/subject_allotment.php',
        'staff-list'          => __DIR__ . '/staff_management.php',
        'user-management'     => __DIR__ . '/user_management.php',
        'staff-advisors'      => __DIR__ . '/staff_advisors.php',
        'hod'                 => __DIR__ . '/hod.php',
        'student-list'        => __DIR__ . '/student_list.php',
        'feed-back'           => __DIR__ . '/feed_back.php'
    ];

    // Include the subpage if it exists, otherwise show error
    if (isset($pages[$action]) && file_exists($pages[$action])) {
        include_once($pages[$action]);
    } else {
        echo "<p>Page not found: <strong>{$action}</strong></p>";
    }
}
?>
    </div>

    <!-- Right Sidebar Navigation -->
    <div class="col-lg-2">
      <ul class="nav nav-pills nav-stacked text-right">
        <li><a href="dashboard.php?action=student-list">Students List</a></li>
        <li><a href="#">Mark Attendance</a></li>
        <li><a href="#">Attendance Register</a></li>
        <li><a href="#">Enter Marklist</a></li>
        <li><a href="#">Class Internal Marklist</a></li>
        <li><a href="#">Generate Internal</a></li>
        <li><a href="#">Elective</a></li>
        <li><a href="#">Register Elective</a></li>
        <li><a href="dashboard.php?action=feed-back">Feedback</a></li>
      </ul>
    </div>
  </div>
</div>
