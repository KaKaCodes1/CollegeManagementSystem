<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Head Of the Departments</title>
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
        <h2>Head Of the Departments</h2>
        <div id="HOD-Data-Window">
            <table class="table table-bordered text-center" id="HODData-Fetch">
            </table>
        </div>
    </div>

    <!--Pop Up Box-->
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="col-lg-12">
                <div class="modal fade" id="AllotHOD" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">Allot HOD</h4>
                            </div>
                            <div class="modal-body">
                                <div id="Spinner1" class="text-center hidden">
                                    <div class="spinner" style="font-size: 24px;">⏳</div>
                                    <p>Processing...</p>
                                </div>
                                <form id="AllotHOD-Form" class="form-horizontal">
                                    <div class="form-group">
                                        <label for="HOD-Department" class="col-sm-3 control-label">Department Code</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="allot-hod-department" id="allot-hod-department" class="form-control" placeholder="Department Name" required="required" readonly="readonly">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="Staff-StaffName" class="col-sm-3 control-label">Staffs</label>
                                        <div class="col-sm-9">
                                            <select name="allot-hod-staff" id="allot-hod-staff" class="form-control">
                                                <option value="null" selected="selected">Select a faculty</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                                <div class="alert alert-warning hidden" id="AllotHOD-warning"> Please enter a valid <span id="AllotHOD-Error-Part"></span>.</div>
                                <div class="alert alert-success hidden" id="AllotHOD-success"> You have successfully allotted a HOD.</div>
                                <div class="alert alert-danger hidden" id="AllotHOD-error"><strong>Oops!</strong> Something went wrong. Please contact us via support section.</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="CloseAllotHOD();">Close</button>
                                <button type="button" class="btn btn-primary" id="AllotHOD-FormButton" onclick="AllotHOD();">Allot</button>
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

    //Function Fetch HOD Data
    function fetch_hod_data() {
        $("#HODData-Fetch").html('');
        $.ajax({
            type: "POST",
            url: `${BASE_URL}/fetch_hod.php`,
            data: {"action": "fetch_hod_data"},
            dataType: "html",
            success: function (data) {
                $("#HODData-Fetch").html(data);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching HOD data:", error);
                $("#HODData-Fetch").html('<tr><td colspan="4" class="text-danger">Error loading HOD data</td></tr>');
            }
        });
    }

    // Load HOD data on page load
    fetch_hod_data();

    function pushFormData(department_code) {
        $("#allot-hod-department").val(department_code);
        fetch_staff_list(department_code);
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
                $("#allot-hod-staff").html('');
                $("#allot-hod-staff").html('<option value="null">Select a faculty</option>');
                $.each(data, function(k, v) {
                    $("#allot-hod-staff").append('<option value="'+v.code+'">'+v.name+'</option>');
                });
            },
            error: function(xhr, status, error) {
                console.error("Error fetching staff list:", error);
                $("#allot-hod-staff").html('<option value="null">Error loading staff</option>');
            }
        });
    }

    //Allot HOD
    function AllotHOD() {
        var department = $("#allot-hod-department").val();
        var hod = $("#allot-hod-staff").val();
        
        if(hod == 'null') {
            $("#AllotHOD-warning").removeClass('hidden');
            $("#AllotHOD-Error-Part").html('Staff');
        } else {
            $.ajax({ 
                type: "POST",
                url: `${BASE_URL}/allot_hod_form.php`,
                beforeSend: function() {
                    $('#Spinner1').removeClass("hidden"); 
                    $("#AllotHOD-Form").addClass("hidden"); 
                    $("#AllotHOD-FormButton").addClass('hidden');
                    $("#AllotHOD-warning").addClass('hidden');
                    $("#AllotHOD-success").addClass('hidden'); 
                    $("#AllotHOD-error").addClass('hidden');
                },
                data: {
                    "department": department,
                    "hod": hod,
                    "action": "allot-hod"
                },
                dataType: "json",
                success: function (data) {
                    if (data.response == "success") {
                        $("#AllotHOD-success").removeClass('hidden');
                        $('#Spinner1').addClass("hidden"); 
                        fetch_hod_data();
                    } else {
                        $('#Spinner1').addClass("hidden"); 
                        $("#AllotHOD-Form").removeClass("hidden");
                        $("#AllotHOD-FormButton").removeClass('hidden');
                        $("#AllotHOD-error").removeClass('hidden');
                        
                        // Show specific error message if available
                        if(data.message) {
                            $("#AllotHOD-error").html('<strong>Oops!</strong> ' + data.message);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", error);
                    $('#Spinner1').addClass("hidden"); 
                    $("#AllotHOD-Form").removeClass("hidden");
                    $("#AllotHOD-FormButton").removeClass('hidden');
                    $("#AllotHOD-error").removeClass('hidden');
                }
            });
        }
    }

    function CloseAllotHOD() {
        $("#AllotHOD-Form").removeClass("hidden");
        $("#AllotHOD-FormButton").removeClass('hidden');
        $("#AllotHOD-warning").addClass('hidden');
        $("#AllotHOD-success").addClass('hidden'); 
        $("#AllotHOD-error").addClass('hidden');
    }

    // Initialize on page load
    $(document).ready(function() {
        console.log("HOD Management page loaded");
        console.log("Base URL:", BASE_URL);
    });
    </script>
</body>
</html>