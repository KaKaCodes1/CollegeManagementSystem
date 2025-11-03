<?php
// Include the database configuration file
// Assumes $conn is a PDO connection object
include_once('../../config.php'); 

// Default response if nothing succeeds
$response = ['response' => 'error'];

// Exit early if 'action' is not set in POST
if (!isset($_POST['action'])) {
    echo json_encode($response);
    exit;
}

// Get the action from POST
$action = $_POST['action'];

try {
    // ---------- REGISTER NEW PROGRAM ----------
    if ($action == 'register-new-program') {
        // Trim whitespace from input
        $program_name = trim($_POST['program_name']);
        
        // Prepare the SQL statement with a named placeholder
        $stmt = $conn->prepare("INSERT INTO programs (program_name) VALUES (:program_name)");
        
        // Execute the statement with bound parameter
        if ($stmt->execute([':program_name' => $program_name])) {
            $response['response'] = 'success';
        }

    // ---------- REGISTER NEW DEPARTMENT ----------
    } elseif ($action == 'register-new-department') {
        $department_name = trim($_POST['department_name']);
        $department_code = strtoupper(trim($_POST['department_code'])); // force uppercase

        // Prepare the insert statement with named placeholders
        $stmt = $conn->prepare(
            "INSERT INTO department (department_name, department_code) 
             VALUES (:department_name, :department_code)"
        );

        // Execute with bound parameters
        if ($stmt->execute([
            ':department_name' => $department_name,
            ':department_code' => $department_code
        ])) {
            $response['response'] = 'success';
        }

    // ---------- REGISTER NEW COURSE ----------
    } elseif ($action == 'register-new-course') {
        // Trim and sanitize all POST data
        $course_program    = trim($_POST['course_program']);
        $course_name       = trim($_POST['course_name']);
        $course_code       = strtoupper(trim($_POST['course_code'])); // force uppercase
        $course_department = trim($_POST['course_department']);
        $course_batches    = trim($_POST['course_batches']);
        $course_semester   = trim($_POST['course_semester']);
        $course_students   = trim($_POST['course_students']);

        // Prepare the SQL statement with named placeholders
        $stmt = $conn->prepare(
            "INSERT INTO courses 
                (program_name, course_name, course_code, department_code, course_semester, course_seats, course_batch)
             VALUES 
                (:program_name, :course_name, :course_code, :department_code, :course_semester, :course_seats, :course_batch)"
        );

        // Array of parameters to bind to the statement
        $params = [
            ':program_name'    => $course_program,
            ':course_name'     => $course_name,
            ':course_code'     => $course_code,
            ':department_code' => $course_department,
            ':course_semester' => $course_semester,
            ':course_seats'    => $course_students,
            ':course_batch'    => $course_batches
        ];

        // Execute the insert statement
        if ($stmt->execute($params)) {
            $response['response'] = 'success';
        }
    }

} catch (PDOException $e) {
    // Catch any PDO/database errors
    $response['response'] = 'error';
    $response['message'] = $e->getMessage(); // optional, useful for debugging
}


// Return JSON response
echo json_encode($response);


// Fetch Programs
try {
    $stmt = $conn->prepare("SELECT * FROM programs ORDER BY program_name ASC");
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Error fetching programs: " . $e->getMessage() . "</p>";
    $programs = [];
}
?>

<h3>Programs</h3>
<?php if (!empty($programs)): ?>
    <ul>
        <?php foreach ($programs as $p): ?>
            <li><?php echo htmlspecialchars($p['program_name']); ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No programs available.</p>
<?php endif; ?>