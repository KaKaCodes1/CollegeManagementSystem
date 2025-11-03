<?php
session_start();
include_once('../../config.php');

// Check if user is authenticated
if (!isset($_SESSION['UserAuthData'])) {
    echo '<tr><td colspan="8" class="text-danger">Unauthorized access</td></tr>';
    exit;
}

$UserAuthData = $_SESSION['UserAuthData'];
// If session data is serialized, unserialize it
if (is_string($UserAuthData)) {
    $UserAuthData = unserialize($UserAuthData);
}

$action = $_POST['action'] ?? '';

if ($action == 'fetch_staffadvisor_data') {
    
    function array_orderby() {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    $department_code = $_POST['department_code'] ?? '';
    
    if (empty($department_code)) {
        echo '<tr><td colspan="8" class="text-danger">Department code is required</td></tr>';
        exit;
    }

    try {
        // Fetch staff advisors data
        $staffAdvisorStmt = $conn->prepare("
            SELECT sa.program_name, sa.batch, sa.year_of_admission, dt.department_name, 
                   ct.course_code, ct.course_name, ct.course_batch, ls.name, ad.current_semester 
            FROM staff_advisors sa 
            JOIN department dt ON sa.department_code = dt.department_code 
            JOIN courses ct ON sa.course_code = ct.course_code 
            JOIN login_staff ls ON sa.staff_id = ls.userid 
            JOIN academic_data ad ON ad.course = sa.program_name AND ad.admission_year = sa.year_of_admission 
            WHERE sa.department_code = :department_code 
            ORDER BY sa.program_name ASC
        ");
        $staffAdvisorStmt->execute([':department_code' => $department_code]);
        $staffAdvisors = $staffAdvisorStmt->fetchAll(PDO::FETCH_ASSOC);

        $DataSet = array(); 
        $DataSets = array();
        $PushedData = array(); 
        array_push($PushedData, 'tbicea'); 
        $unique = ''; 
        $DepartmentName = 0; 
        $count = 0; 
        $batch = 0;

        echo '<tr>
            <td><strong>#</strong></td>
            <td><strong>Program</strong></td>
            <td><strong>Course</strong></td>
            <td><strong>Year of Admission</strong></td>
            <td><strong>Batch</strong></td>
            <td><strong>Current Semester</strong></td>
            <td><strong>Staff Advisor</strong></td>';
        
        if ($UserAuthData['role'] == 'admin' || $UserAuthData['role'] == 'hod') {
            echo '<td><strong>Operation</strong></td>';
        }
        echo '</tr>';

        // Process existing staff advisors
        foreach ($staffAdvisors as $row) {
            if (!empty($PushedData)) {
                $unique = array_search($row['program_name'] . $row['course_name'] . $row['year_of_admission'] . $row['batch'], $PushedData);
            }
            
            if ($unique === false) {
                $DataSet = [
                    'program_name' => $row['program_name'],
                    'course_name' => $row['course_name'],
                    'course_code' => $row['course_code'],
                    'year_of_admission' => $row['year_of_admission'],
                    'batch' => $row['batch'],
                    'current_semester' => $row['current_semester'],
                    'name' => $row['name'],
                    'course_batches' => $row['course_batch']
                ];
                $DepartmentName = $row['course_name'];
                array_push($DataSets, $DataSet);
                array_push($PushedData, $row['program_name'] . $row['course_name'] . $row['year_of_admission'] . $row['batch']);
            }
        }

        // Fetch courses and academic data for unassigned staff advisors
        $coursesStmt = $conn->prepare("SELECT * FROM `courses` WHERE department_code = :department_code ORDER BY program_name ASC");
        $coursesStmt->execute([':department_code' => $department_code]);
        $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($courses as $row1) {
            $program = $row1['program_name'];
            
            $academicDataStmt = $conn->prepare("SELECT * FROM `academic_data` WHERE course = :program AND current_semester != '0'");
            $academicDataStmt->execute([':program' => $program]);
            $academicData = $academicDataStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($academicData as $row2) {
                $DataSet = [
                    'program_name' => $program,
                    'course_name' => $row1['course_name'],
                    'course_code' => $row1['course_code'],
                    'year_of_admission' => $row2['admission_year'],
                    'current_semester' => $row2['current_semester'],
                    'name' => '',
                    'course_batches' => $row1['course_batch']
                ];
                
                if ($row1['course_batch'] > 0) {
                    for ($i = 1; $i <= $row1['course_batch']; $i++) {
                        $DataSet['batch'] = $i;
                        if (!empty($PushedData)) {
                            $unique = array_search($program . $row1['course_name'] . $row2['admission_year'] . $DataSet['batch'], $PushedData);
                        }
                        if ($unique === false) {
                            array_push($DataSets, $DataSet);
                            array_push($PushedData, $program . $row1['course_name'] . $row2['admission_year'] . $DataSet['batch']);
                        }
                    }
                }
            }
        }

        // Sort the datasets
        $DataSets = array_orderby($DataSets, 'program_name', SORT_ASC, 'course_name', SORT_ASC);

        // Output the data
        foreach ($DataSets as $Data) {
            $count++;
            echo '<tr>
                <td>' . $count . '</td>
                <td>' . htmlspecialchars($Data['program_name']) . '</td>
                <td>' . htmlspecialchars($Data['course_name']) . '</td>
                <td>' . htmlspecialchars($Data['year_of_admission']) . '</td>
                <td>' . htmlspecialchars($Data['batch']) . '</td>
                <td>' . htmlspecialchars($Data['current_semester']) . '</td>
                <td>' . htmlspecialchars($Data['name']) . '</td>';
            
            if ($UserAuthData['role'] == 'admin' || ($UserAuthData['role'] == 'hod' && $UserAuthData['department'] == $department_code)) {
                $Function_Parameter = "'" . htmlspecialchars($Data['program_name'], ENT_QUOTES) . "','" . 
                                    htmlspecialchars($department_code, ENT_QUOTES) . "','" . 
                                    htmlspecialchars($Data['course_code'], ENT_QUOTES) . "','" . 
                                    htmlspecialchars($Data['batch'], ENT_QUOTES) . "','" . 
                                    htmlspecialchars($Data['year_of_admission'], ENT_QUOTES) . "'";
                
                echo '<td>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#AllotStaffAdvisor" onclick="pushFormData(' . $Function_Parameter . ');">
                        Allot Staff Advisor
                    </button>
                </td>';
            } else if ($UserAuthData['role'] == 'hod' && $UserAuthData['department'] != $department_code) {
                echo '<td>No Permission</td>';
            }
            
            echo '</tr>';
        }

    } catch (PDOException $e) {
        error_log("Database error in fetch_staffadvisor_data: " . $e->getMessage());
        echo '<tr><td colspan="8" class="text-danger">Error fetching staff advisor data</td></tr>';
    } catch (Exception $e) {
        error_log("General error in fetch_staffadvisor_data: " . $e->getMessage());
        echo '<tr><td colspan="8" class="text-danger">System error occurred</td></tr>';
    }
} else {
    echo '<tr><td colspan="8" class="text-danger">Invalid action</td></tr>';
}
?>