<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
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
        <h2>User Management</h2>
        <div class="form-group">
            <form id="SelectUserType-Form">
                <select name="select-usertype" id="select-usertype" class="form-control">
                    <option value="null">Select a user type</option>
                    <option value="administration">College Administration</option>
                    <option value="site-admin">Site Administration</option>
                </select>
            </form>
        </div>
        <div id="User-Data-Window" class="hidden">
            <h4 id="Title"></h4>
            <table class="table table-bordered text-center" id="UserData-Fetch">
            </table>
        </div>
    </div>

    <!--Pop Up Box College Administration-->
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="col-lg-12">
                <div class="modal fade" id="AddNewCollegeAdmin" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">Add New Administrator</h4>
                            </div>
                            <div class="modal-body">
                                <div id="Spinner1" class="text-center hidden">
                                    <div class="spinner" style="font-size: 24px;">⏳</div>
                                    <p>Processing...</p>
                                </div>
                                <form id="AddNewCollegeAdmin-Form" class="form-horizontal">
                                    <div class="form-group">
                                        <label for="Staff-Name" class="col-sm-3 control-label">Name</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="add-new-collegeadmin-name" id="add-new-collegeadmin-name" class="form-control" placeholder="Enter Name Eg. Firstname Lastname" required="required">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="Staff-Email" class="col-sm-3 control-label">E-Mail</label>
                                        <div class="col-sm-9">
                                            <input type="email" name="add-new-collegeadmin-email" id="add-new-collegeadmin-email" class="form-control" placeholder="Enter E-Mail Address Eg. sample@sampledomain.com" required="required">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="Staff-Phone" class="col-sm-3 control-label">Phone Number</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="add-new-collegeadmin-phone" id="add-new-collegeadmin-phone" class="form-control" placeholder="Enter Phone Number Eg. 9876543210" required="required">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="Staff-Designation" class="col-sm-3 control-label">Designation</label>
                                        <div class="col-sm-9">
                                            <select name="add-new-collegeadmin-designation" id="add-new-collegeadmin-designation" class="form-control" required>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                                <div class="alert alert-warning hidden" id="AddNewCollegeAdmin-warning"> Please enter a valid <span id="AddNewCollegeAdmin-Error-Part"></span>.</div>
                                <div class="alert alert-success hidden" id="AddNewCollegeAdmin-success"> You have successfully assigned College Administrator. The temporary password generated for the user is <strong><span id="AddNewCollegeAdmin-password"></span></strong>.</div>
                                <div class="alert alert-danger hidden" id="AddNewCollegeAdmin-error"><strong>Oops.!</strong> Something went wrong. Please contact us via support section.</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="CloseAddNewCollegeAdmin();">Close</button>
                                <button type="button" class="btn btn-primary" id="AddNewCollegeAdmin-FormButton" onclick="AddNewCollegeAdmin();">Add</button>
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

    //Change User Type
    $( "#select-usertype" ).change(function () {
        var str = $( "#select-usertype option:selected" ).text();
        var str2 = $( "#select-usertype option:selected" ).val();
        $( "#select-usertype option:selected" ).each(function() {
        });
        if(str2!='null') {
            $("#User-Data-Window").removeClass('hidden');
            $("#Title").html(str);
            if(str2=='site-admin') {
                $("#add-new-collegeadmin-designation").html("<option value='admin'>Admin</option>");
            } else {
                $("#add-new-collegeadmin-designation").html('<option value="null" selected="selected">Select a Designation</option><option value="principal">Principal</option><option value="manager">Manager</option>');
            }
            fetch_user_data(str2);
        }
    });

    function fetch_user_data(x) {
        $( "#UserData-Fetch").html('');
        $.ajax({
            type: "POST",
            url: `${BASE_URL}/fetch_user.php`,
            data: {
                "userType": x,
                "action": "fetch_user_data"
            },
            dataType: "html",
            success: function (data) {
                $( "#UserData-Fetch").html(data);
            },
            error: function(xhr, status, error) {
                console.error("Error fetching user data:", error);
                $( "#UserData-Fetch").html('<tr><td colspan="5" class="text-danger">Error loading user data</td></tr>');
            }
        });
    }

    function AddNewCollegeAdmin() {
        var name = $("#add-new-collegeadmin-name").val();
        var email = $("#add-new-collegeadmin-email").val();
        var phone = $("#add-new-collegeadmin-phone").val();
        var designation = $("#add-new-collegeadmin-designation").val();
        var email_pattern = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        var phone_pattern = /^([0-9]{10})$/;
        
        if(name == null || name=='' || name.length<2) {
            $("#AddNewCollegeAdmin-warning").removeClass('hidden');
            $("#AddNewCollegeAdmin-Error-Part").html("Name");
        }
        else if(email_pattern.test(email) == false || email=='') {
            $("#AddNewCollegeAdmin-warning").removeClass('hidden');
            $("#AddNewCollegeAdmin-Error-Part").html("E-Mail address");
        }
        else if(phone_pattern.test(phone) == false || phone=='') {
            $("#AddNewCollegeAdmin-warning").removeClass('hidden');
            $("#AddNewCollegeAdmin-Error-Part").html("Phone Number");
        }
        else if(designation=='null') {
            $("#AddNewCollegeAdmin-warning").removeClass('hidden');
            $("#AddNewCollegeAdmin-Error-Part").html("Designation");
        }
        else {
            $("#AddNewCollegeAdmin-warning").addClass('hidden');
            $.ajax({
                type: "POST",
                url: `${BASE_URL}/user_management_form.php`,
                beforeSend: function() {
                    $('#Spinner1').removeClass('hidden'); 
                    $("#AddNewCollegeAdmin-Form").addClass('hidden');
                },
                data: {
                    "name": name,
                    "email": email,
                    "phone": phone,
                    "designation": designation,
                    "action": "add_new_college_admin"
                },
                dataType: "json",
                success: function (data) {
                    if(data.response == 'success') {
                        $('#Spinner1').addClass('hidden');
                        $('#AddNewCollegeAdmin-success').removeClass('hidden');
                        $("#AddNewCollegeAdmin-password").html(data.pass);
                        $("#AddNewCollegeAdmin-FormButton").addClass('hidden');
                        var str2 = $( "#select-usertype option:selected" ).val();
                        fetch_user_data(str2);
                    }
                    else {
                        console.error("Server error:", data);
                        $('#Spinner1').addClass('hidden');
                        $("#AddNewCollegeAdmin-Form").removeClass('hidden');
                        $('#AddNewCollegeAdmin-error').removeClass('hidden');
                        
                        // Show specific error message if available
                        if(data.message) {
                            $('#AddNewCollegeAdmin-error').html('<strong>Oops!</strong> ' + data.message);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", error);
                    $('#Spinner1').addClass('hidden');
                    $("#AddNewCollegeAdmin-Form").removeClass('hidden');
                    $('#AddNewCollegeAdmin-error').removeClass('hidden');
                }
            });
        }
    }

    function CloseAddNewCollegeAdmin() {
        $("#AddNewCollegeAdmin-Form").removeClass('hidden');
        $('#AddNewCollegeAdmin-error').addClass('hidden');
        $('#AddNewCollegeAdmin-warning').addClass('hidden');
        $('#AddNewCollegeAdmin-success').addClass('hidden');
        $("#add-new-collegeadmin-name").val('');
        $("#add-new-collegeadmin-email").val('');
        $("#add-new-collegeadmin-phone").val('');
        $('#Spinner1').addClass('hidden');
        $("#AddNewCollegeAdmin-FormButton").removeClass('hidden');
    }

    // Initialize on page load
    $(document).ready(function() {
        console.log("User Management page loaded");
        console.log("Base URL:", BASE_URL);
    });
    </script>
</body>
</html>