<?php

ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once('../config.php'); // Adjust path correctly
header('Content-Type: text/html; charset=utf-8');

// ----------------- USER AUTHENTICATION -----------------
if (!isset($_SESSION['UserAuthData'])) {
    http_response_code(403);
    echo "<p>Access denied. Not logged in.</p>";
    exit;
}

$UserAuthData = $_SESSION['UserAuthData'];
if (is_string($UserAuthData)) $UserAuthData = unserialize($UserAuthData);

$role = $UserAuthData['role'] ?? '';
$departmentUser = $UserAuthData['department'] ?? '';

$action = $_POST['action'] ?? '';

// Helper function for HTML escaping
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// ----------------- FETCH PROGRAMS -----------------
if ($action === 'fetch-program') {
    try {
        $stmt = $conn->query("SELECT * FROM `programs` ORDER BY id DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<tr><th>Name</th>";
        if ($role === 'admin') echo "<th>Operation</th>";
        echo "</tr>";

        foreach ($rows as $row) {
            echo "<tr>";
            echo "<td>" . e($row['program_name']) . "</td>";
            if ($role === 'admin') {
                echo "<td><a href='delete.php?p=delete-program&id=" . $row['id'] . "'>
                        <button type='button' class='btn btn-danger'>Delete</button></a></td>";
            }
            echo "</tr>";
        }

        if ($role === 'admin') {
            echo "<tr><td colspan='2'>
                <button type='button' class='btn btn-success' data-toggle='modal' data-target='#RegisterNewProgram'>
                    Register New Program
                </button></td></tr>";
        }
    } catch (PDOException $e) {
        echo "<tr><td colspan='2'>Database error: " . e($e->getMessage()) . "</td></tr>";
    }
}

// ----------------- FETCH DEPARTMENTS -----------------
elseif ($action === 'fetch-department') {
    try {
        $stmt = $conn->query("SELECT * FROM `department` ORDER BY id DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<tr><th>Department Name</th><th>Department Code</th>";
        if ($role === 'admin') echo "<th>Operation</th>";
        echo "</tr>";

        foreach ($rows as $row) {
            echo "<tr>";
            echo "<td>" . e($row['department_name']) . "</td>";
            echo "<td>" . e($row['department_code']) . "</td>";
            if ($role === 'admin') {
                echo "<td><a href='delete.php?p=delete-department&id=" . $row['id'] . "'>
                        <button type='button' class='btn btn-danger'>Delete</button></a></td>";
            }
            echo "</tr>";
        }

        if ($role === 'admin') {
            echo "<tr><td colspan='3'>
                <button type='button' class='btn btn-success' data-toggle='modal' data-target='#RegisterNewDepartment'>
                    Register New Department
                </button></td></tr>";
        }
    } catch (PDOException $e) {
        echo "<tr><td colspan='3'>Database error: " . e($e->getMessage()) . "</td></tr>";
    }
}

// ----------------- FETCH COURSES -----------------
elseif ($action === 'fetch-course') {
    try {
        $stmt = $conn->query("SELECT * FROM `courses` ORDER BY id DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<tr>
                <th>Program</th>
                <th>Course Name</th>
                <th>Batches</th>
                <th>Department</th>
                <th>No. of Semesters</th>
                <th>No. of Students</th>";
        if ($role === 'admin' || $role === 'hod') echo "<th>Operation</th>";
        echo "</tr>";

        foreach ($rows as $row) {
            echo "<tr>";
            echo "<td>" . e($row['program_name']) . "</td>";
            echo "<td>" . e($row['course_name']) . " (" . e($row['course_code']) . ")</td>";
            echo "<td>" . e($row['course_batch']) . "</td>";
            echo "<td>" . e($row['department_code']) . "</td>";
            echo "<td>" . e($row['course_semester']) . "</td>";
            echo "<td>" . e($row['course_seats']) . "</td>";

            if ($role === 'admin') {
                echo "<td><a href='delete.php?p=delete-course&id=" . $row['id'] . "'>
                        <button type='button' class='btn btn-danger'>Delete</button></a></td>";
            } elseif ($role === 'hod') {
                if ($departmentUser === $row['department_code']) {
                    echo "<td><a href='delete.php?p=delete-course&id=" . $row['id'] . "'>
                        <button type='button' class='btn btn-danger'>Delete</button></a></td>";
                } else {
                    echo "<td>No Permission</td>";
                }
            }

            echo "</tr>";
        }

        if ($role === 'admin' || $role === 'hod') {
            echo "<tr><td colspan='7'>
                <button type='button' class='btn btn-success' data-toggle='modal' data-target='#RegisterNewCourse'>
                    Register New Course
                </button></td></tr>";
        }
    } catch (PDOException $e) {
        echo "<tr><td colspan='7'>Database error: " . e($e->getMessage()) . "</td></tr>";
    }
}

// ----------------- FETCH ACADEMIC DATA -----------------
elseif ($action === 'fetch-academic-data') {
    try {
        $stmt = $conn->query("SELECT * FROM `academic_data` ORDER BY admission_year DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<tr>
                <th>Course</th>
                <th>Admission</th>
                <th>Scheme</th>
                <th>Current Semester</th>
                <th>Start Date</th>
                <th>End Date</th>";
        if ($role === 'admin') echo "<th>Operation</th>";
        echo "</tr>";

        foreach ($rows as $row) {
            $current_semester = $row['current_semester'] == '0' ? 'Passout' : $row['current_semester'];

            echo "<tr>
                    <td>" . e($row['course']) . "</td>
                    <td>" . e($row['admission_year']) . "</td>
                    <td>" . e($row['university_scheme']) . "</td>
                    <td>" . e($current_semester) . "</td>
                    <td>" . e($row['semester_starting_date']) . "</td>
                    <td>" . e($row['semester_ending_date']) . "</td>";

            if ($role === 'admin') {
                echo "<td>
                        <a onclick=\"SetSemester('".e($row['id'])."','".e($row['course'])."','".e($row['admission_year'])."','".e($row['current_semester'])."','".e($row['semester_starting_date'])."','".e($row['semester_ending_date'])."');\">
                            <button type='button' class='btn btn-info'>Edit</button>
                        </a>
                        <a href='delete.php?p=delete-acadamic_year&id=".e($row['id'])."'>
                            <button type='button' class='btn btn-danger'>Delete</button>
                        </a>
                    </td>";
            }
            echo "</tr>";
        }

        if ($role === 'admin') {
            echo "<tr><td colspan='7'>
                    <button type='button' class='btn btn-success' data-toggle='modal' data-target='#RegisterNewBatch'>
                        Register New Batch
                    </button></td></tr>";
        }

    } catch (PDOException $e) {
        echo "<tr><td colspan='7'>Database error: " . e($e->getMessage()) . "</td></tr>";
    }
}

// ----------------- FETCH SUBJECT MANAGEMENT -----------------
elseif ($action === 'fetch-subject-management') {
    $program = $_POST['program'] ?? '';
    $course_code = $_POST['course_code'] ?? '';
    $scheme = $_POST['scheme'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $department = $_POST['department'] ?? '';

    try {
        $stmt = $conn->prepare("SELECT * FROM `semester_subject` 
                                WHERE program = :program AND course = :course AND scheme = :scheme AND semester = :semester AND department = :department 
                                ORDER BY subj_code ASC, subj_code_sub ASC");
        $stmt->execute([
            ':program' => $program,
            ':course' => $course_code,
            ':scheme' => $scheme,
            ':semester' => $semester,
            ':department' => $department
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<tr>
                <th>#</th>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Type</th>
                <th>Internal</th>
                <th>External</th>
                <th>Hours</th>";
        if ($role === 'admin' || ($role === 'hod' && $departmentUser === $department)) echo "<th>Operation</th>";
        echo "</tr>";

        $count = 0;
        foreach ($rows as $row) {
            $count++;
            echo "<tr>";
            echo "<td>$count</td>";
            echo "<td>" . e($row['subj_code']) . " " . e($row['subj_code_sub']) . "</td>";
            echo "<td>" . e($row['subj_name']) . "</td>";
            echo "<td>" . e($row['type']) . "</td>";
            echo "<td>" . e($row['in_mark']) . "</td>";
            echo "<td>" . e($row['ex_mark']) . "</td>";
            echo "<td>" . e($row['hours']) . "</td>";

            if ($role === 'admin') {
                echo "<td><a href='delete.php?p=delete-subject&id=".e($row['id'])."'>
                        <button type='button' class='btn btn-danger'>Delete</button></a></td>";
            } elseif ($role === 'hod') {
                echo "<td>";
                if ($departmentUser === $department) {
                    echo "<a href='delete.php?p=delete-subject&id=".e($row['id'])."'>
                            <button type='button' class='btn btn-danger'>Delete</button></a>";
                } else {
                    echo "No Permission";
                }
                echo "</td>";
            }

            echo "</tr>";
        }

        if ($role === 'admin' || ($role === 'hod' && $departmentUser === $department)) {
            echo "<tr><td colspan='8' class='text-center'>
                    <button type='button' class='btn btn-success' data-toggle='modal' data-target='#AddNewSubject'>
                        Add New Subject
                    </button></td></tr>";
        }

    } catch (PDOException $e) {
        echo "<tr><td colspan='8'>Database error: " . e($e->getMessage()) . "</td></tr>";
    }
}

// ----------------- FETCH SUBJECT ALLOTMENT -----------------
elseif ($action === 'fetch-subject-allotment') {
    $program = $_POST['program'] ?? '';
    $course_code = $_POST['course_code'] ?? '';
    $scheme = $_POST['scheme'] ?? '';
    $batch = $_POST['batch'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $department = $_POST['department'] ?? '';

    try {
        $stmt = $conn->prepare("
            SELECT ss.subj_code, ss.subj_code_sub, ss.subj_name, sa.teacher_id, ls.name 
            FROM semester_subject ss
            LEFT JOIN subject_allotment sa ON sa.subject_code = ss.subj_code AND sa.subject_code_sub = ss.subj_code_sub AND sa.batch = :batch
            LEFT JOIN login_staff ls ON ls.userid = sa.teacher_id
            WHERE ss.program = :program AND ss.course = :course AND ss.scheme = :scheme AND ss.semester = :semester AND ss.department = :department
            ORDER BY ss.subj_code ASC, ss.subj_code_sub ASC
        ");
        $stmt->execute([
            ':program' => $program,
            ':course' => $course_code,
            ':scheme' => $scheme,
            ':semester' => $semester,
            ':department' => $department,
            ':batch' => $batch
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<tr>
                <th>#</th>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Teacher</th>";
        if ($role === 'admin' || ($role === 'hod' && $departmentUser === $department)) echo "<th>Operation</th>";
        echo "</tr>";

        $count = 0;
        foreach ($rows as $row) {
            $count++;
            $teacherName = $row['name'] ?? 'No Staff';

            echo "<tr>";
            echo "<td>$count</td>";
            echo "<td>" . e($row['subj_code']) . " " . e($row['subj_code_sub']) . "</td>";
            echo "<td>" . e($row['subj_name']) . "</td>";
            echo "<td>" . e($teacherName) . "</td>";

            if ($role === 'admin' || ($role === 'hod' && $departmentUser === $department)) {
                echo "<td class='text-center'>
                        <button onclick=\"AllotTeacherPopupFill('".e($row['subj_code'])."','".e($row['subj_code_sub'])."','".e($row['subj_name'])."');\" 
                        type='button' class='btn btn-success' data-toggle='modal' data-target='#AllotStaff'>Allot Teacher</button>
                      </td>";
            } else {
                echo "<td>No Permission</td>";
            }

            echo "</tr>";
        }

    } catch (PDOException $e) {
        echo "<tr><td colspan='5'>Database error: " . e($e->getMessage()) . "</td></tr>";
    }
}

// ----------------- INVALID ACTION -----------------
else {
    echo "<tr><td colspan='7'>Invalid action</td></tr>";
}
