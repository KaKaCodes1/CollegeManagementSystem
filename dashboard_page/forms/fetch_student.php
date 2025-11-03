<?php
session_start();
include_once('../../config.php');

// Check if user is authenticated
if (!isset($_SESSION['UserAuthData'])) {
    echo '<tr><td colspan="10" class="text-danger">Unauthorized access</td></tr>';
    exit;
}

$UserAuthData = $_SESSION['UserAuthData'];
// If session data is serialized, unserialize it
if (is_string($UserAuthData)) {
    $UserAuthData = unserialize($UserAuthData);
}

$action = $_POST['action'] ?? '';

if ($action == 'fetch_student') {
    $department = $_POST['department'] ?? '';
    $program = $_POST['program'] ?? '';
    $course_code = $_POST['course_code'] ?? '';
    $batch = $_POST['batch'] ?? '';
    $yoa = $_POST['yoa'] ?? '';
    
    // Validate required fields
    if (empty($department) || empty($program) || empty($course_code) || empty($batch) || empty($yoa)) {
        echo '<tr><td colspan="10" class="text-danger">Missing required parameters</td></tr>';
        exit;
    }

    $table = 'stud_' . $yoa . '_main';
    
    try {
        // Check if table exists
        $tableCheck = $conn->prepare("SHOW TABLES LIKE :table");
        $tableCheck->execute([':table' => $table]);
        
        if ($tableCheck->rowCount() == 0) {
            echo '<tr><td colspan="10" class="text-warning">No student data found for the selected year of admission</td></tr>';
            exit;
        }

        // Build query based on batch selection
        if ($batch == 'all') {
            $sql = "SELECT * FROM `$table` WHERE department = :department AND program = :program AND branch = :course_code AND yearOfAddmission = :yoa ORDER BY name ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':department' => $department,
                ':program' => $program,
                ':course_code' => $course_code,
                ':yoa' => $yoa
            ]);
        } else {
            $sql = "SELECT * FROM `$table` WHERE department = :department AND program = :program AND branch = :course_code AND yearOfAddmission = :yoa AND batch = :batch ORDER BY name ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':department' => $department,
                ':program' => $program,
                ':course_code' => $course_code,
                ':yoa' => $yoa,
                ':batch' => $batch
            ]);
        }

        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = 0;
        
        echo "<tr>
            <td><strong>#</strong></td>
            <td><strong>Program</strong></td>
            <td><strong>Department</strong></td>
            <td><strong>Branch</strong></td>
            <td><strong>Admission Number</strong></td>
            <td><strong>Name</strong></td>
            <td><strong>Sex</strong></td>
            <td><strong>Roll Number</strong></td>
            <td><strong>Register Number</strong></td>";
        
        // Fixed variable reference from $department_code to $department
        if ($UserAuthData['role'] == 'admin' || ($UserAuthData['role'] == 'hod' && $UserAuthData['department'] == $department)) {
            echo "<td><strong>Operation</strong></td>";
        }
        echo "</tr>";

        if (empty($students)) {
            echo '<tr><td colspan="10" class="text-warning">No students found for the selected criteria</td></tr>';
        } else {
            foreach ($students as $row) {
                $count++;
                echo "<tr>";
                echo "<td>" . htmlspecialchars($count) . "</td>";
                echo "<td>" . htmlspecialchars($row['program']) . "</td>";
                echo "<td>" . htmlspecialchars($row['department']) . "</td>";
                echo "<td>" . htmlspecialchars($row['branch']) . "</td>";
                echo "<td>" . htmlspecialchars($row['admno']) . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['sex']) . "</td>";
                echo "<td>" . htmlspecialchars($row['rollNo']) . "</td>";
                echo "<td>" . htmlspecialchars($row['regno']) . "</td>";
                
                // Fixed variable reference from $department_code to $department
                if ($UserAuthData['role'] == 'admin' || ($UserAuthData['role'] == 'hod' && $UserAuthData['department'] == $department)) {
                    echo "<td>
                        <button type='button' class='btn btn-danger delete-student' 
                                data-student-id='" . htmlspecialchars($row['id']) . "'
                                data-student-name='" . htmlspecialchars($row['name']) . "'
                                data-year='" . htmlspecialchars($yoa) . "'>
                            Delete
                        </button>
                    </td>";
                }
                echo "</tr>";
            }
        }

    } catch (PDOException $e) {
        error_log("Database error in fetch_student: " . $e->getMessage());
        echo '<tr><td colspan="10" class="text-danger">Error fetching student data: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
    } catch (Exception $e) {
        error_log("General error in fetch_student: " . $e->getMessage());
        echo '<tr><td colspan="10" class="text-danger">System error occurred</td></tr>';
    }
} else {
    echo '<tr><td colspan="10" class="text-danger">Invalid action</td></tr>';
}
?>