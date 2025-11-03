<?php
// ---------------- SESSION & CONFIG ----------------
if(session_status() === PHP_SESSION_NONE) session_start();
include_once('config.php'); // Make sure this file sets $conn as PDO instance
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Academic Management</title>
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/page.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<style>.hidden{display:none;}</style>
</head>
<body>
<div class="container">

<h2>Academic Management</h2>

<!-- Alerts -->
<div class="alert alert-success hidden" id="Update-Semester-Success"><strong>Success!</strong> You have updated semester details.</div>
<div class="alert alert-danger hidden" id="Update-Semester-Failed"><strong>Oops!</strong> Something went wrong.</div>
<div class="alert alert-warning hidden" id="Update-Semester-Warning"><strong>Note!</strong> Please enter a valid input in field <strong><span id="Update-Semester-Error-Part"></span></strong>.</div>

<!-- Update Semester Form -->
<div id="UpdateSemester" class="hidden">
  <h4>Update Semester Details</h4>  
  <form id="UpdateSemester-Form" class="form-horizontal">
    <input type="hidden" id="update-semester-id" readonly>

    <div class="form-group">
      <label class="col-sm-3 control-label">Course</label>
      <div class="col-sm-9">
        <input type="text" id="update-semester-course" class="form-control" readonly>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-3 control-label">Admission Year</label>
      <div class="col-sm-9">
        <input type="text" id="update-semester-admission-year" class="form-control" readonly>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-3 control-label">Current Semester</label>
      <div class="col-sm-9">
        <select id="update-semester-current-semester" class="form-control">
          <option value="null">Current Semester</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-3 control-label">Start Date</label>
      <div class="col-sm-9">
        <input type="date" id="update-semester-start-date" class="form-control">
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-3 control-label">End Date</label>
      <div class="col-sm-9">
        <input type="date" id="update-semester-end-date" class="form-control">
      </div>
    </div>

    <div class="form-group">
      <div class="col-sm-4 col-xs-offset-6">
        <button type="button" class="btn btn-default" onclick="UpdateSemesterClose();">Close</button>
        <button type="button" class="btn btn-primary" onclick="UpdateSemester();">Update</button>
      </div>
    </div>
  </form>
</div>

<!-- Academic Table -->
<table class="table table-bordered text-center" id="AcademicManagementFetch"></table>

<!-- Register New Batch Modal -->
<div class="modal fade" id="RegisterNewBatch" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Register New Batch</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="RegisterNewBatch-Form">
          <div class="form-group">
            <select id="register-new-batch-program" class="form-control">
              <option value="null">Select a course to add new batch</option>
              <?php
              $stmt = $conn->query("SELECT * FROM programs ORDER BY id DESC");
              while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="'.htmlspecialchars($row['program_name']).'">'.htmlspecialchars($row['program_name']).'</option>';
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <input type="text" id="register-new-batch-year" class="form-control" placeholder="Enter Year of Admission">
          </div>
          <div class="form-group">
            <input type="text" id="register-new-batch-scheme" class="form-control" placeholder="Enter Academic Scheme">
          </div>
        </form>
        <div class="alert alert-warning hidden" id="RegisterNewBatch-warning"></div>
        <div class="alert alert-success hidden" id="RegisterNewBatch-success">Batch added successfully.</div>
        <div class="alert alert-danger hidden" id="RegisterNewBatch-error">Something went wrong.</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal" onclick="RegisterNewBatchClose();">Close</button>
        <button class="btn btn-primary" onclick="RegisterNewBatch();">Register</button>
      </div>
    </div>
  </div>
</div>

</div> <!-- container -->

<!-- ==================== JS ==================== -->
<script>
function UpdateSemesterClose() {
  $("#UpdateSemester").addClass('hidden');
}

function UpdateSemester() {
  const id = $("#update-semester-id").val();
  const semester = $("#update-semester-current-semester").val();
  const start = $("#update-semester-start-date").val();
  const end = $("#update-semester-end-date").val();

  if(!start){ $("#Update-Semester-Warning").removeClass('hidden'); $("#Update-Semester-Error-Part").text('Start Date'); return;}
  if(!end){ $("#Update-Semester-Warning").removeClass('hidden'); $("#Update-Semester-Error-Part").text('End Date'); return;}

  $.post('forms/academic_management_form.php',{
    action:'update_semester',
    update_id:id,
    update_semester:semester,
    update_start_date:start,
    update_end_date:end
  },function(data){
    if(data.response=='success'){
      $("#Update-Semester-Success").removeClass('hidden');
      RealTimeData('fetch-academic-data');
    }else{
      $("#Update-Semester-Failed").removeClass('hidden');
    }
  },'json');
}

// ---------------- Register New Batch ----------------
function RegisterNewBatchClose() {
  $("#RegisterNewBatch-Form input, #RegisterNewBatch-Form select").val('');
  $("#RegisterNewBatch-success, #RegisterNewBatch-warning, #RegisterNewBatch-error").addClass('hidden');
}

function RegisterNewBatch() {
  const program=$("#register-new-batch-program").val();
  const year=$("#register-new-batch-year").val();
  const scheme=$("#register-new-batch-scheme").val();

  if(program=='null'){ $("#RegisterNewBatch-warning").text('Select a Program').removeClass('hidden'); return;}
  if(year.length<3||year.length>4){ $("#RegisterNewBatch-warning").text('Invalid Year').removeClass('hidden'); return;}
  if(scheme.length<3||scheme.length>4){ $("#RegisterNewBatch-warning").text('Invalid Scheme').removeClass('hidden'); return;}

  $.post('forms/academic_management_form.php',{
    action:'register-new-batch',
    program_name:program,
    year_of_admission:year,
    acadamic_scheme:scheme
  },function(data){
    if(data.response=='success'){
      $("#RegisterNewBatch-success").removeClass('hidden');
      RealTimeData('fetch-academic-data');
    }else{
      $("#RegisterNewBatch-error").removeClass('hidden');
    }
  },'json');
}

// ---------------- Real Time Table ----------------
function RealTimeData(action){
  $.post('dashboard_page/realtime_data.php',{action:action},function(data){
    $("#AcademicManagementFetch").html(data);
  },'html');
}

// Initial load
$(document).ready(function(){ RealTimeData('fetch-academic-data'); });
</script>
</body>
</html>
