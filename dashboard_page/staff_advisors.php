<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Advisors</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
    <link href="css/page.css" rel="stylesheet" type="text/css">
    <link href="css/docs.css" rel="stylesheet" type="text/css">
    <style>
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Staff Advisors</h2>
        <div class="form-group">
            <form id="SelectDepartment-Form">
                <select name="select-department" id="select-department" class="form-control">
                    <option value="null">Select a Department</option>
                    <?php
                    include_once('../../config.php');
                    $stmt = $conn->prepare("SELECT * FROM `department`");
                    $stmt->execute();
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="'.$row['department_code'].'">'.$row['department_name'].'</option>';
                    }
                    ?>
                </select>
            </form>
        </div>
        <div id="StaffAdvisor-Data-Window" class="hidden">
            <h4>Staff Advisor - <span id="Department-Name"></span></h4>
            <table class="table table-bordered text-center" id="StaffAdvisorData-Fetch">
            </table>
        </div>
    </div>

    <!--Pop Up Box-->
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="col-lg-12">
                <div class="modal fade" id="AllotStaffAdvisor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">Allot Staff Advisor</h4>
                            </div>
                            <div class="modal-body">
                                <div id="Spinner1" class="text-center hidden">
                                    <div class="spinner" style="font-size: 24px;">⏳</div>
                                    <p>Processing...</p>
                                </div>
                                <form id="AllotStaffAdvisor-Form" class="form-horizontal">
                                    <div class="form-group">
                                        <label for="Staff-Program" class="col-sm-3 control-label">Program</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="add-new-staffadvisor-program" id="add-new-staffadvisor-program" class="form-control" placeholder="Program Name" required="required" readonly="readonly">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="Staff-Department" class="col-sm-3 control-label">Department Name</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="add-new-staffadvisor-department" id="add-new-staffadvisor-department" class="form-control" placeholder="Department Name" required="required" readonly="readonly">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="Staff-Course" class="col-sm-3 control-label">Course</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="add-new-staffadvisor-course" id="add-new-staffadvisor-course" class="form-control" placeholder="Course Name" required="required" readonly="readonly">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="Staff-Batch" class="col-sm-3 control-label">Batch</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="add-new-staffadvisor-batch" id="add-new-staffadvisor-batch" class="form-control" placeholder="Batch" required="required" readonly="readonly">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="Staff-YOA" class="col-sm-3 control-label">Year of Admission</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="add-new-staffadvisor-yoa" id="add-new-staffadvisor-yoa" class="form-control" placeholder="Batch" required="required" readonly="readonly">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="Staff-StaffName" class="col-sm-3 control-label">Staffs</label>
                                        <div class="col-sm-9">
                                            <select name="add-new-staffadvisor-staff" id="add-new-staffadvisor-staff" class="form-control">
                                                <option value="null" selected="selected">Select a faculty</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                                <div class="alert alert-warning hidden" id="AllotStaffAdvisor-warning"> Please enter a valid <span id="AllotStaffAdvisor-Error-Part"></span>.</div>
                                <div class="alert alert-success hidden" id="AllotStaffAdvisor-success"> You have successfully alloted a staff advisor.</div>
                                <div class="alert alert-danger hidden" id="AllotStaffAdvisor-error"><strong>Oops.!</strong> Something went wrong. Please contact us via support section.</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="CloseAllotStaffAdvisor();">Close</button>
                                <button type="button" class="btn btn-primary" id="AllotStaffAdvisor-FormButton" onclick="AllotStaffAdvisor();">Allot</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
    
    <script>
    // Base URL configuration
    const BASE_URL = 'dashboard_page/forms';

    //Change Department
    $( "#select-department" ).change(function () {
        var str = $( "#select-department option:selected" ).text();
        var str2 = $( "#select-department option:selected" ).val();
        $( "#select-department option:selected" ).each(function() {
        });
        if(str2!='null') {
            $("#StaffAdvisor-Data-Window").removeClass('hidden');
            fetch_staff_data();
        } else {
            $("#StaffAdvisor-Data-Window").addClass('hidden');
        }
        $("#Department-Name").html(str);
    });

    //Function Fetch Staff Data
    function fetch_staff_data() {
        $("#StaffAdvisorData-Fetch").html('');
        $.ajax({
            type: "POST",
            url: `${BASE_URL}/fetch_staff_advisor.php`,
            data: {
                "department_code": $("#select-department").val(),
                "action": "fetch_staffadvisor_data"
            },
            dataType: "html",
            success: function (data) {
                $( "#StaffAdvisorData-Fetch").html(data);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching staff advisor data:", error);
                $( "#StaffAdvisorData-Fetch").html('<tr><td colspan="6" class="text-danger">Error loading data</td></tr>');
            }
        });
    }

    //Function pushFormData
    function pushFormData(a,b,c,d,e) {
        $("#add-new-staffadvisor-program").val(a);
        $("#add-new-staffadvisor-department").val(b);
        $("#add-new-staffadvisor-course").val(c);
        $("#add-new-staffadvisor-batch").val(d);
        $("#add-new-staffadvisor-yoa").val(e);
        fetch_staff_list(b);
    }

    //Fetch Staff Data JSON
    function fetch_staff_list(f) {
        $.ajax({
            type: "POST",
            url: `${BASE_URL}/fetch.php`,
            data: {
                "department_code": f,
                "action": "fetch_staff_data"
            },
            dataType: "json",
            success: function (data) {
                $("#add-new-staffadvisor-staff").html('');
                $("#add-new-staffadvisor-staff").html('<option value="null">Select a faculty</option>');
                $.each(data, function(k, v) {
                    $("#add-new-staffadvisor-staff").append('<option value="'+v.code+'">'+v.name+'</option>');
                });
            },
            error: function(xhr, status, error) {
                console.error("Error fetching staff list:", error);
                $("#add-new-staffadvisor-staff").html('<option value="null">Error loading staff</option>');
            }
        });
    }

    function AllotStaffAdvisor() {
        var program = $("#add-new-staffadvisor-program").val();
        var department = $("#add-new-staffadvisor-department").val();
        var course = $("#add-new-staffadvisor-course").val();
        var batch = $("#add-new-staffadvisor-batch").val();
        var yoa = $("#add-new-staffadvisor-yoa").val();
        var staff = $("#add-new-staffadvisor-staff").val();
        
        if(staff == 'null') {
            $("#AllotStaffAdvisor-warning").removeClass('hidden');
            $("#AllotStaffAdvisor-Error-Part").html('Staff');
        } else {
            $.ajax({ 
                type: "POST",
                url: `${BASE_URL}/allot_staff_advisor_form.php`,
                beforeSend: function() {
                    $('#Spinner1').removeClass("hidden"); 
                    $("#AllotStaffAdvisor-Form").addClass("hidden"); 
                    $("#AllotStaffAdvisor-FormButton").addClass('hidden');
                    $("#AllotStaffAdvisor-warning").addClass('hidden');
                    $("#AllotStaffAdvisor-success").addClass('hidden'); 
                    $("#AllotStaffAdvisor-error").addClass('hidden');
                },
                data: {
                    "program": program,
                    "department": department,
                    "course": course,
                    "batch": batch,
                    "yoa": yoa,
                    "staff": staff,
                    "action": "allot-staff-advisor"
                },
                dataType: "json",
                success: function (data) {
                    if (data.response == "success") {
                        $("#AllotStaffAdvisor-success").removeClass('hidden');
                        $('#Spinner1').addClass("hidden"); 
                        fetch_staff_data();
                    } else {
                        $('#Spinner1').addClass("hidden"); 
                        $("#AllotStaffAdvisor-Form").removeClass("hidden");
                        $("#AllotStaffAdvisor-FormButton").removeClass('hidden');
                        $("#AllotStaffAdvisor-error").removeClass('hidden');
                        
                        // Show specific error message if available
                        if(data.message) {
                            $("#AllotStaffAdvisor-error").html('<strong>Oops!</strong> ' + data.message);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", error);
                    $('#Spinner1').addClass("hidden"); 
                    $("#AllotStaffAdvisor-Form").removeClass("hidden");
                    $("#AllotStaffAdvisor-FormButton").removeClass('hidden');
                    $("#AllotStaffAdvisor-error").removeClass('hidden');
                }
            });
        }
    }

    function CloseAllotStaffAdvisor() {
        $("#AllotStaffAdvisor-Form").removeClass("hidden");
        $("#AllotStaffAdvisor-FormButton").removeClass('hidden');
        $("#AllotStaffAdvisor-warning").addClass('hidden');
        $("#AllotStaffAdvisor-success").addClass('hidden'); 
        $("#AllotStaffAdvisor-error").addClass('hidden');
    }

    // Initialize on page load
    $(document).ready(function() {
        console.log("Staff Advisors page loaded");
        console.log("Base URL:", BASE_URL);
    });
    </script>
</body>
</html>