<?php
session_start();
include_once('../../config.php');

// Check if user is authenticated
if (!isset($_SESSION['UserAuthData'])) {
	echo '<tr><td colspan="5" class="text-danger">Unauthorized access</td></tr>';
	exit;
}

$UserAuthData = $_SESSION['UserAuthData'];
// If session data is serialized, unserialize it
if (is_string($UserAuthData)) {
	$UserAuthData = unserialize($UserAuthData);
}

$action = $_POST['action'] ?? '';

if ($action == 'fetch_user_data') {
	$type = $_POST['userType'] ?? '';

	// Determine table based on user type
	if ($type == 'administration') {
		$table = 'login_college_admin';
	} else if ($type == 'site-admin') {
		$table = 'login_admin';
	} else {
		echo '<tr><td colspan="5" class="text-danger">Invalid user type</td></tr>';
		exit;
	}

	if ($table) {
		try {
			// Fetch data using PDO
			$stmt = $conn->prepare("SELECT * FROM $table");
			$stmt->execute();
			$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

			// Start building HTML output
			echo '<tr>';
			echo '<td><strong>Name</strong></td><td><strong>E-Mail</strong></td><td><strong>Phone</strong></td><td><strong>Designation</strong></td>';
			if ($UserAuthData['role'] == 'admin') {
				echo '<td><strong>Operation</strong></td>';
			}
			echo '</tr>';

			foreach ($users as $row) {
				echo '<tr>';
				echo '<td>' . htmlspecialchars($row['name']) . '</td>';
				echo '<td>' . htmlspecialchars($row['email']) . '</td>';
				echo '<td>' . htmlspecialchars($row['phone']) . '</td>';
				echo '<td>' . htmlspecialchars(ucfirst($row['role'])) . '</td>';

				if ($UserAuthData['role'] == 'admin') {
					echo "<td><button type='button' class='btn btn-danger delete-user' data-user-type='" . htmlspecialchars($type) . "' data-user-id='" . htmlspecialchars($row['id']) . "' data-user-name='" . htmlspecialchars($row['name']) . "'>Delete</button> </td>";
				}
				echo '</tr>';
			}

			if ($UserAuthData['role'] == 'admin') {
				echo '<tr>
                    <td colspan="5">
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#AddNewCollegeAdmin">
                            Add New Administrator
                        </button>
                    </td>
                </tr>';
			}

		} catch (PDOException $e) {
			error_log("Database error in fetch_user_data: " . $e->getMessage());
			echo '<tr><td colspan="5" class="text-danger">Error fetching user data</td></tr>';
		}
	} else {
		echo '<tr><td colspan="5" class="text-danger">Invalid table specified</td></tr>';
	}
} else {
	echo '<tr><td colspan="5" class="text-danger">Invalid action</td></tr>';
}
?>