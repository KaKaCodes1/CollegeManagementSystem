<?php
session_start();
include_once('../../config.php');

// Check if user is authenticated
if (!isset($_SESSION['UserAuthData'])) {
    echo '<tr><td colspan="4" class="text-danger">Unauthorized access</td></tr>';
    exit;
}

$UserAuthData = $_SESSION['UserAuthData'];
// If session data is serialized, unserialize it
if (is_string($UserAuthData)) {
    $UserAuthData = unserialize($UserAuthData);
}

$action = $_POST['action'] ?? '';

if ($action == 'fetch_hod_data') {
    try {
        $PushedData = array(); 
        $DataSet = array(); 
        $DataSets = array(); 
        array_push($PushedData, 'tbicea'); 
        $unique = '';

        // Fetch departments with HODs
        $hodStmt = $conn->prepare("
            SELECT dt.department_name, dt.department_code, ls.name 
            FROM department dt 
            JOIN login_staff ls ON ls.department = dt.department_code 
            WHERE ls.role = 'hod' 
            ORDER BY dt.department_name ASC
        ");
        $hodStmt->execute();
        $hods = $hodStmt->fetchAll(PDO::FETCH_ASSOC);

        // Process departments with HODs
        foreach ($hods as $row) {
            if (!empty($PushedData)) {
                $unique = array_search($row['department_code'], $PushedData);
            }
            
            if ($unique === false) {
                $DataSet = [
                    'department_code' => $row['department_code'],
                    'department_name' => $row['department_name'],
                    'name' => $row['name']
                ];
                array_push($DataSets, $DataSet);
                array_push($PushedData, $row['department_code']);
            }
        }

        // Fetch all departments (including those without HODs)
        $deptStmt = $conn->prepare("SELECT * FROM `department` ORDER BY department_name ASC");
        $deptStmt->execute();
        $departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

        // Process all departments
        foreach ($departments as $row) {
            if (!empty($PushedData)) {
                $unique = array_search($row['department_code'], $PushedData);
            }
            
            if ($unique === false) {
                $DataSet = [
                    'department_code' => $row['department_code'],
                    'department_name' => $row['department_name'],
                    'name' => ''
                ];
                array_push($DataSets, $DataSet);
                array_push($PushedData, $row['department_code']);
            }
        }

        $count = 0;
        echo "<tr>
            <td><strong>#</strong></td>
            <td><strong>Department</strong></td>
            <td><strong>Name</strong></td>";
        
        if ($UserAuthData['role'] == 'admin') {
            echo "<td><strong>Operation</strong></td>";
        }
        echo "</tr>";

        // Output the data
        foreach ($DataSets as $Data) {
            $count++;
            echo '<tr>
                <td>' . $count . '</td>
                <td>' . htmlspecialchars($Data['department_name']) . ' (' . htmlspecialchars($Data['department_code']) . ')</td>
                <td>' . htmlspecialchars($Data['name']) . '</td>';
            
            if ($UserAuthData['role'] == 'admin') {
                $departmentCode = htmlspecialchars($Data['department_code'], ENT_QUOTES);
                echo '<td>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#AllotHOD" onclick="pushFormData(\'' . $departmentCode . '\');">
                        Allot HOD
                    </button>
                </td>';
            }
            
            echo '</tr>';
        }

    } catch (PDOException $e) {
        error_log("Database error in fetch_hod_data: " . $e->getMessage());
        echo '<tr><td colspan="4" class="text-danger">Error fetching HOD data</td></tr>';
    } catch (Exception $e) {
        error_log("General error in fetch_hod_data: " . $e->getMessage());
        echo '<tr><td colspan="4" class="text-danger">System error occurred</td></tr>';
    }
} else {
    echo '<tr><td colspan="4" class="text-danger">Invalid action</td></tr>';
}
?>