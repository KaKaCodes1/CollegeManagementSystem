<?php
session_start();
if (!isset($_SESSION['student_id']) || $_SESSION['role'] != 'student') {
    header("Location: student_login.php");
    exit();
}

include 'db_connection.php';

$student_id = $_SESSION['student_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">CEA Student Portal</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo $_SESSION['name']; ?></span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card text-white bg-primary">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-user"></i> Profile</h5>
                        <a href="student_profile.php" class="stretched-link text-white text-decoration-none"></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card text-white bg-success">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-calendar-alt"></i> Attendance</h5>
                        <a href="student_attendance.php" class="stretched-link text-white text-decoration-none"></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card text-white bg-info">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-chart-bar"></i> Marks</h5>
                        <a href="student_marks.php" class="stretched-link text-white text-decoration-none"></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card text-white bg-warning">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-book"></i> Subjects</h5>
                        <a href="student_subjects.php" class="stretched-link text-white text-decoration-none"></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Announcements</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6>Semester Exams Schedule</h6>
                            <p class="mb-0">Final semester exams will commence from March 15, 2015.</p>
                            <small class="text-muted">Posted on: February 10, 2015</small>
                        </div>
                        <div class="alert alert-warning">
                            <h6>Fee Payment Reminder</h6>
                            <p class="mb-0">Last date for fee payment is February 28, 2015.</p>
                            <small class="text-muted">Posted on: February 5, 2015</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Info</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get student basic info
                        $stmt = $pdo->prepare("SELECT * FROM stud_2014_main WHERE user_id = ?");
                        $stmt->execute([$student_id]);
                        $student = $stmt->fetch();
                        
                        if ($student) {
                            echo "<p><strong>Roll No:</strong> {$student['rollNo']}</p>";
                            echo "<p><strong>Department:</strong> {$student['department']}</p>";
                            echo "<p><strong>Program:</strong> {$student['program']}</p>";
                            echo "<p><strong>Batch:</strong> {$student['batch']}</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
</body>
</html>