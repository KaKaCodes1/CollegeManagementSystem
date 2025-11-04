<?php
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once('config.php'); // PDO connection

// Check if user is logged in
if (!isset($_SESSION['UserAuthData'])) {
    header('Location: login.php');
    exit;
}

$UserAuthData = $_SESSION['UserAuthData'];
$userRole = $UserAuthData['role'] ?? '';
$userDepartment = $UserAuthData['department'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Course Management</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="css/page.css" rel="stylesheet" type="text/css">
    <link href="css/docs.css" rel="stylesheet" type="text/css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    <style>
        .hidden {
            display: none;
        }
        .tab-button {
            margin: 5px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .action-buttons {
            margin-bottom: 20px;
        }
        .alert {
            margin-top: 10px;
        }
        .modal-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <h2><i class="fas fa-book"></i> Course Management System</h2>
        <p class="text-muted">Manage programs, departments, and courses</p>

        <!-- Tabs Navigation -->
        <div class="action-buttons">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-success tab-button" id="ProgramsButton" onclick="CourseManagementTab('programs')">
                    <i class="fas fa-graduation-cap"></i> Programs
                </button>
                <button type="button" class="btn btn-outline-primary tab-button" id="DepartmentsButton" onclick="CourseManagementTab('departments')">
                    <i class="fas fa-building"></i> Departments
                </button>
                <button type="button" class="btn btn-outline-primary tab-button" id="CourseButton" onclick="CourseManagementTab('course')">
                    <i class="fas fa-book-open"></i> Course Overview
                </button>
            </div>
            
            <!-- Quick Action Buttons -->
            <div class="pull-right">
                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#RegisterNewProgram">
                    <i class="fas fa-plus"></i> Add Program
                </button>
                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#RegisterNewDepartment">
                    <i class="fas fa-plus"></i> Add Department
                </button>
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#RegisterNewCourse">
                    <i class="fas fa-plus"></i> Add Course
                </button>
            </div>
        </div>

        <!-- Programs Section -->
        <div id="ProgramsWindow">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-graduation-cap"></i> Programs List</h4>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="ProgramsFetch">
                            <thead class="thead-dark">
                                <tr>
                               
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Departments Section -->
        <div id="DepartmentsWindow" class="hidden">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-building"></i> Departments List</h4>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="DepartmentFetch">
                            <thead class="thead-dark">
                                <tr>
                                 
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Section -->
        <div id="CourseWindow" class="hidden">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><i class="fas fa-book-open"></i> Courses List</h4>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="CourseFetch">
                            <thead class="thead-dark">
                                <tr>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Program Modal -->
    <div class="modal fade" id="RegisterNewProgram" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fas fa-graduation-cap"></i> Register New Program</h4>
                </div>
                <div class="modal-body">
                    <form id="RegisterNewProgram-Form">
                        <div class="form-group">
                            <label for="register-new-program-name">Program Name:</label>
                            <input type="text" id="register-new-program-name" class="form-control" 
                                   placeholder="Enter Program Name" required>
                        </div>
                    </form>
                    <div id="RegisterNewProgram-alerts">
                        <div class="alert alert-warning hidden" id="RegisterNewProgram-warning">
                            <i class="fas fa-exclamation-triangle"></i> Please enter a valid Program name (min 2 characters).
                        </div>
                        <div class="alert alert-success hidden" id="RegisterNewProgram-success">
                            <i class="fas fa-check-circle"></i> Program added successfully.
                        </div>
                        <div class="alert alert-danger hidden" id="RegisterNewProgram-error">
                            <i class="fas fa-times-circle"></i> Something went wrong. Please try again.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="resetProgramModal()">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary" onclick="RegisterNewProgram()">
                        <i class="fas fa-save"></i> Register Program
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Department Modal -->
    <div class="modal fade" id="RegisterNewDepartment" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fas fa-building"></i> Register New Department</h4>
                </div>
                <div class="modal-body">
                    <form id="RegisterNewDepartment-Form">
                        <div class="form-group">
                            <label for="register-new-department-name">Department Name:</label>
                            <input type="text" id="register-new-department-name" class="form-control" 
                                   placeholder="Department Name" required>
                        </div>
                        <div class="form-group">
                            <label for="register-new-department-code">Department Code:</label>
                            <input type="text" id="register-new-department-code" class="form-control" 
                                   placeholder="Department Code (e.g., CSE)" maxlength="10" required>
                            <small class="text-muted">Code will be automatically converted to uppercase</small>
                        </div>
                    </form>
                    <div id="RegisterNewDepartment-alerts">
                        <div class="alert alert-warning hidden" id="RegisterNewDepartment-warning">
                            <i class="fas fa-exclamation-triangle"></i> Please enter valid values for all fields.
                        </div>
                        <div class="alert alert-success hidden" id="RegisterNewDepartment-success">
                            <i class="fas fa-check-circle"></i> Department added successfully.
                        </div>
                        <div class="alert alert-danger hidden" id="RegisterNewDepartment-error">
                            <i class="fas fa-times-circle"></i> Something went wrong. Please try again.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="resetDepartmentModal()">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary" onclick="RegisterNewDepartment()">
                        <i class="fas fa-save"></i> Register Department
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Course Modal -->
    <div class="modal fade" id="RegisterNewCourse" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fas fa-book-open"></i> Register New Course</h4>
                </div>
                <div class="modal-body">
                    <form id="RegisterNewCourse-Form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="register-new-course-program">Program:</label>
                                    <select id="register-new-course-program" class="form-control" required>
                                        <option value="">Select a Program</option>
                                        <?php
                                        $stmt = $conn->query("SELECT * FROM programs ORDER BY program_name ASC");
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . htmlspecialchars($row['program_name']) . '">' . 
                                                 htmlspecialchars($row['program_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="register-new-course-department">Department:</label>
                                    <select id="register-new-course-department" class="form-control" required>
                                        <option value="">Select Department</option>
                                        <?php
                                        if ($userRole == 'admin') {
                                            $stmtDept = $conn->query("SELECT * FROM department ORDER BY department_name ASC");
                                        } else {
                                            $stmtDept = $conn->prepare("SELECT * FROM department WHERE department_code = ? ORDER BY department_name ASC");
                                            $stmtDept->execute([$userDepartment]);
                                        }
                                        while ($row = $stmtDept->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . htmlspecialchars($row['department_code']) . '">' . 
                                                 htmlspecialchars($row['department_name']) . ' (' . htmlspecialchars($row['department_code']) . ')</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="register-new-course-name">Course Name:</label>
                                    <input type="text" id="register-new-course-name" class="form-control" 
                                           placeholder="Course Name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="register-new-course-code">Course Code:</label>
                                    <input type="text" id="register-new-course-code" class="form-control" 
                                           placeholder="Course Code" required>
                                    <small class="text-muted">Code will be automatically converted to uppercase</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="register-new-course-batches">Number of Batches:</label>
                                    <input type="number" id="register-new-course-batches" class="form-control" 
                                           placeholder="Batches" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="register-new-course-semester">Number of Semesters:</label>
                                    <input type="number" id="register-new-course-semester" class="form-control" 
                                           placeholder="Semesters" min="1" max="12" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="register-new-course-students">Number of Students:</label>
                                    <input type="number" id="register-new-course-students" class="form-control" 
                                           placeholder="Students" min="1" required>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div id="RegisterNewCourse-alerts">
                        <div class="alert alert-warning hidden" id="RegisterNewCourse-warning">
                            <i class="fas fa-exclamation-triangle"></i> Please fill all fields with valid values.
                        </div>
                        <div class="alert alert-success hidden" id="RegisterNewCourse-success">
                            <i class="fas fa-check-circle"></i> Course added successfully.
                        </div>
                        <div class="alert alert-danger hidden" id="RegisterNewCourse-error">
                            <i class="fas fa-times-circle"></i> Something went wrong. Please try again.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="resetCourseModal()">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary" onclick="RegisterNewCourse()">
                        <i class="fas fa-save"></i> Register Course
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab Management
        function CourseManagementTab(tab) {
            // Hide all windows
            $('#ProgramsWindow, #DepartmentsWindow, #CourseWindow').addClass('hidden');
            
            // Reset all buttons to outline
            $('#ProgramsButton, #DepartmentsButton, #CourseButton')
                .removeClass('btn-success')
                .addClass('btn-outline-primary');
            
            // Show selected window and activate button
            switch(tab) {
                case 'programs':
                    $('#ProgramsWindow').removeClass('hidden');
                    $('#ProgramsButton').removeClass('btn-outline-primary').addClass('btn-success');
                    break;
                case 'departments':
                    $('#DepartmentsWindow').removeClass('hidden');
                    $('#DepartmentsButton').removeClass('btn-outline-primary').addClass('btn-success');
                    break;
                case 'course':
                    $('#CourseWindow').removeClass('hidden');
                    $('#CourseButton').removeClass('btn-outline-primary').addClass('btn-success');
                    break;
            }
        }

        // Real-time Data Fetching
        function RealTimeData(action) {
            $.post('dashboard_page/realtime_data.php', { action: action }, function(data) {
                switch(action) {
                    case 'fetch-program':
                        $('#ProgramsFetch tbody').html(data);
                        break;
                    case 'fetch-department':
                        $('#DepartmentFetch tbody').html(data);
                        break;
                    case 'fetch-course':
                        $('#CourseFetch tbody').html(data);
                        break;
                }
            }).fail(function() {
                console.error('Failed to fetch data for: ' + action);
            });
        }

        // Form Submission Handler
        function submitForm(modalId, formData) {
            const $modal = $('#' + modalId);
            const $warning = $('#' + modalId + '-warning');
            const $success = $('#' + modalId + '-success');
            const $error = $('#' + modalId + '-error');

            // Hide all alerts
            $warning.addClass('hidden');
            $success.addClass('hidden');
            $error.addClass('hidden');

            $.post('dashboard_page/forms/course_management_form.php', formData, function(response) {
                if (response.response === 'success') {
                    $success.removeClass('hidden');
                    // Refresh relevant data
                    const refreshAction = 'fetch-' + formData.action.split('-')[2];
                    RealTimeData(refreshAction);
                    
                    // Auto-close modal after 2 seconds
                    setTimeout(() => {
                        $modal.modal('hide');
                        resetModal(modalId);
                    }, 2000);
                } else {
                    $error.removeClass('hidden');
                    if (response.message) {
                        $error.html('<i class="fas fa-times-circle"></i> ' + response.message);
                    }
                }
            }, 'json').fail(function() {
                $error.removeClass('hidden');
            });
        }

        // Individual Form Functions
        function RegisterNewProgram() {
            const programName = $('#register-new-program-name').val().trim();
            if (programName.length < 2) {
                $('#RegisterNewProgram-warning').removeClass('hidden');
                return;
            }
            submitForm('RegisterNewProgram', {
                program_name: programName,
                action: 'register-new-program'
            });
        }

        function RegisterNewDepartment() {
            const deptName = $('#register-new-department-name').val().trim();
            const deptCode = $('#register-new-department-code').val().trim();
            
            if (deptName.length < 2 || deptCode.length < 1) {
                $('#RegisterNewDepartment-warning').removeClass('hidden');
                return;
            }
            submitForm('RegisterNewDepartment', {
                department_name: deptName,
                department_code: deptCode,
                action: 'register-new-department'
            });
        }

        function RegisterNewCourse() {
            const program = $('#register-new-course-program').val();
            const name = $('#register-new-course-name').val().trim();
            const code = $('#register-new-course-code').val().trim();
            const dept = $('#register-new-course-department').val();
            const batches = $('#register-new-course-batches').val();
            const sem = $('#register-new-course-semester').val();
            const students = $('#register-new-course-students').val();

            if (!program || name.length < 2 || code.length < 2 || !dept || 
                batches < 1 || sem < 1 || students < 1) {
                $('#RegisterNewCourse-warning').removeClass('hidden');
                return;
            }

            submitForm('RegisterNewCourse', {
                course_program: program,
                course_name: name,
                course_code: code,
                course_department: dept,
                course_batches: batches,
                course_semester: sem,
                course_students: students,
                action: 'register-new-course'
            });
        }

        // Modal Reset Functions
        function resetModal(modalId) {
            $('#' + modalId + ' form')[0].reset();
            $('#' + modalId + ' .alert').addClass('hidden');
        }

        function resetProgramModal() {
            resetModal('RegisterNewProgram');
        }

        function resetDepartmentModal() {
            resetModal('RegisterNewDepartment');
        }

        function resetCourseModal() {
            resetModal('RegisterNewCourse');
        }

        // Auto-uppercase department and course codes
        $('#register-new-department-code, #register-new-course-code').on('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Initialize on document ready
        $(document).ready(function() {
            RealTimeData('fetch-program');
            RealTimeData('fetch-department');
            RealTimeData('fetch-course');
            
            // Reset modals when they are hidden
            $('#RegisterNewProgram, #RegisterNewDepartment, #RegisterNewCourse').on('hidden.bs.modal', function() {
                resetModal($(this).attr('id'));
            });
        });
    </script>
</body>
</html>