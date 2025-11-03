<?php
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


include_once('config.php'); // PDO connection
// Example: $_SESSION['UserAuthData'] = ['role'=>'admin','department'=>'CS'];
$UserAuthData = $_SESSION['UserAuthData'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Course Management</title>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
<link href="css/page.css" rel="stylesheet" type="text/css">
<link href="css/docs.css" rel="stylesheet" type="text/css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
<style>
.hidden { display: none; }
</style>
</head>
<body>

<div class="container">
<h2>Course Management</h2>
<p>&nbsp;</p>

<!-- Tabs -->
<div class="mb-3">
    <button type="button" class="btn btn-success" id="ProgramsButton" onclick="CourseManagementTab('programs')">Programs</button>
    <button type="button" class="btn btn-primary" id="DepartmentsButton" onclick="CourseManagementTab('departments')">Departments</button>
    <button type="button" class="btn btn-primary" id="CourseButton" onclick="CourseManagementTab('course')">Course Overview</button>
</div>

<!-- Programs -->
<div id="ProgramsWindow">
    <h4>Programs</h4>
    <table class="table table-bordered text-center" id="ProgramsFetch"></table>
</div>

<!-- Departments -->
<div id="DepartmentsWindow" class="hidden">
    <h4>Departments</h4>
    <table class="table table-bordered text-center" id="DepartmentFetch"></table>
</div>

<!-- Courses -->
<div id="CourseWindow" class="hidden">
    <h4>Course Overview</h4>
    <table class="table table-bordered text-center" id="CourseFetch"></table>
</div>

<!-- Add Program Modal -->
<div class="modal fade" id="RegisterNewProgram" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h4 class="modal-title">Register New Program</h4></div>
      <div class="modal-body">
        <form id="RegisterNewProgram-Form">
          <div class="form-group">
            <input type="text" id="register-new-program-name" class="form-control" placeholder="Enter Program Name">
          </div>
        </form>
        <div class="alert alert-warning hidden" id="RegisterNewProgram-warning">Please enter a valid Program name.</div>
        <div class="alert alert-success hidden" id="RegisterNewProgram-success">Program added successfully.</div>
        <div class="alert alert-danger hidden" id="RegisterNewProgram-error">Something went wrong. Contact support.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal" onclick="RegisterNewProgramClose();">Close</button>
        <button type="button" class="btn btn-primary" onclick="RegisterNewProgram();">Register</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Department Modal -->
<div class="modal fade" id="RegisterNewDepartment" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h4 class="modal-title">Register New Department</h4></div>
      <div class="modal-body">
        <form id="RegisterNewDepartment-Form">
          <div class="form-group">
            <input type="text" id="register-new-department-name" class="form-control" placeholder="Department Name">
          </div>
          <div class="form-group">
            <input type="text" id="register-new-department-code" class="form-control" placeholder="Department Code">
          </div>
        </form>
        <div class="alert alert-warning hidden" id="RegisterNewDepartment-warning">Please enter valid values.</div>
        <div class="alert alert-success hidden" id="RegisterNewDepartment-success">Department added successfully.</div>
        <div class="alert alert-danger hidden" id="RegisterNewDepartment-error">Something went wrong. Contact support.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal" onclick="RegisterNewDepartmentClose();">Close</button>
        <button type="button" class="btn btn-primary" onclick="RegisterNewDepartment();">Register</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="RegisterNewCourse" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h4 class="modal-title">Register New Course</h4></div>
      <div class="modal-body">
        <form id="RegisterNewCourse-Form">
          <div class="form-group">
            <select id="register-new-course-program" class="form-control">
              <option value="null">Select a Program</option>
              <?php
              $stmt = $conn->query("SELECT * FROM programs ORDER BY id DESC");
              while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                  echo '<option value="'.htmlspecialchars($row['program_name']).'">'.htmlspecialchars($row['program_name']).'</option>';
              }
              ?>
            </select>
          </div>
          <div class="form-group"><input type="text" id="register-new-course-name" class="form-control" placeholder="Course Name"></div>
          <div class="form-group"><input type="text" id="register-new-course-code" class="form-control" placeholder="Course Code"></div>
          <div class="form-group">
            <select id="register-new-course-department" class="form-control">
              <option value="null">Offered by Department</option>
              <?php
              if($UserAuthData['role']=='admin') $stmtDept=$conn->query("SELECT * FROM department ORDER BY id DESC");
              else $stmtDept=$conn->prepare("SELECT * FROM department WHERE department_code=? ORDER BY id DESC");
              if($UserAuthData['role']!='admin') $stmtDept->execute([$UserAuthData['department']]);
              while($row=$stmtDept->fetch(PDO::FETCH_ASSOC)){
                  echo '<option value="'.htmlspecialchars($row['department_code']).'">'.htmlspecialchars($row['department_name']).'</option>';
              }
              ?>
            </select>
          </div>
          <div class="form-group"><input type="number" id="register-new-course-batches" class="form-control" placeholder="Number of Batches"></div>
          <div class="form-group"><input type="number" id="register-new-course-semester" class="form-control" placeholder="Number of Semesters"></div>
          <div class="form-group"><input type="number" id="register-new-course-students" class="form-control" placeholder="Number of Students"></div>
        </form>
        <div class="alert alert-warning hidden" id="RegisterNewCourse-warning">Please enter valid values.</div>
        <div class="alert alert-success hidden" id="RegisterNewCourse-success">Course added successfully.</div>
        <div class="alert alert-danger hidden" id="RegisterNewCourse-error">Something went wrong. Contact support.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal" onclick="RegisterNewCourseClose();">Close</button>
        <button type="button" class="btn btn-primary" onclick="RegisterNewCourse();">Register</button>
      </div>
    </div>
  </div>
</div>

</div> <!-- container -->

<script>
// ----------------- TAB SWITCHING -----------------
function CourseManagementTab(tab) {
    const windows = ['ProgramsWindow', 'DepartmentsWindow', 'CourseWindow'];
    const buttons = ['ProgramsButton', 'DepartmentsButton', 'CourseButton'];

    windows.forEach(w => document.getElementById(w).classList.add('hidden'));
    buttons.forEach(b => {
        const btn = document.getElementById(b);
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
    });

    if (tab === 'programs') {
        document.getElementById('ProgramsWindow').classList.remove('hidden');
        document.getElementById('ProgramsButton').classList.remove('btn-primary');
        document.getElementById('ProgramsButton').classList.add('btn-success');
    } else if (tab === 'departments') {
        document.getElementById('DepartmentsWindow').classList.remove('hidden');
        document.getElementById('DepartmentsButton').classList.remove('btn-primary');
        document.getElementById('DepartmentsButton').classList.add('btn-success');
    } else if (tab === 'course') {
        document.getElementById('CourseWindow').classList.remove('hidden');
        document.getElementById('CourseButton').classList.remove('btn-primary');
        document.getElementById('CourseButton').classList.add('btn-success');
    }
}

// ----------------- FETCH DATA -----------------
function RealTimeData(action) {
    $.post('dashboard_page/realtime_data.php', { action: action }, function(data) {
        if (action === 'fetch-program') $('#ProgramsFetch').html(data);
        else if (action === 'fetch-department') $('#DepartmentFetch').html(data);
        else if (action === 'fetch-course') $('#CourseFetch').html(data);
        else if (action === 'fetch-academic-data') $('#AcademicDataFetch').html(data);
        else if (action === 'fetch-subject-management') $('#SubjectManagementFetch').html(data);
        else if (action === 'fetch-subject-allotment') $('#SubjectAllotmentFetch').html(data);
    });
}

// ----------------- FORM SUBMISSION -----------------
function submitForm(modalId, formData) {
    const warning = `#${modalId}-warning`;
    const success = `#${modalId}-success`;
    const error = `#${modalId}-error`;

    $(warning).addClass('hidden');
    $(success).addClass('hidden');
    $(error).addClass('hidden');

    $.post('forms/course_management_form.php', formData, function(res) {
        if (res.response === 'success') {
            $(success).removeClass('hidden');
            // Auto-refresh the table for this form
            const refreshAction = 'fetch-' + formData.action.split('-')[2];
            RealTimeData(refreshAction);
        } else {
            $(error).removeClass('hidden');
        }
    }, 'json').fail(function() {
        $(error).removeClass('hidden');
    });
}

// ----------------- MODAL FORM FUNCTIONS -----------------
function RegisterNewProgram() {
    const val = $('#register-new-program-name').val();
    if (val.length < 2) return $('#RegisterNewProgram-warning').removeClass('hidden');
    submitForm('RegisterNewProgram', { program_name: val, action: 'register-new-program' });
}

function RegisterNewDepartment() {
    const name = $('#register-new-department-name').val();
    const code = $('#register-new-department-code').val();
    if (name.length < 2 || code.length < 1) return $('#RegisterNewDepartment-warning').removeClass('hidden');
    submitForm('RegisterNewDepartment', { department_name: name, department_code: code, action: 'register-new-department' });
}

function RegisterNewCourse() {
    const program = $('#register-new-course-program').val();
    const name = $('#register-new-course-name').val();
    const code = $('#register-new-course-code').val();
    const dept = $('#register-new-course-department').val();
    const batches = $('#register-new-course-batches').val();
    const sem = $('#register-new-course-semester').val();
    const students = $('#register-new-course-students').val();

    if (program === 'null' || name.length < 2 || code.length < 2 || dept === 'null' || batches < 1 || sem < 1 || students < 1)
        return $('#RegisterNewCourse-warning').removeClass('hidden');

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

// ----------------- RESET MODALS -----------------
function RegisterNewProgramClose() {
    $('#register-new-program-name').val('');
    $('#RegisterNewProgram-warning,#RegisterNewProgram-success,#RegisterNewProgram-error').addClass('hidden');
}
function RegisterNewDepartmentClose() {
    $('#register-new-department-name,#register-new-department-code').val('');
    $('#RegisterNewDepartment-warning,#RegisterNewDepartment-success,#RegisterNewDepartment-error').addClass('hidden');
}
function RegisterNewCourseClose() {
    $('#register-new-course-program').val('null');
    $('#register-new-course-name,#register-new-course-code,#register-new-course-department,#register-new-course-batches,#register-new-course-semester,#register-new-course-students').val('');
    $('#RegisterNewCourse-warning,#RegisterNewCourse-success,#RegisterNewCourse-error').addClass('hidden');
}

// ----------------- INITIAL TABLE LOAD -----------------
$(document).ready(function() {
    RealTimeData('fetch-program');
    RealTimeData('fetch-department');
    RealTimeData('fetch-course');
});
</script>


</body>
</html>
