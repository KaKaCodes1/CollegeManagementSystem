<?php
session_start();
include_once('../../config.php');

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if user is authenticated
if (!isset($_SESSION['UserAuthData'])) {
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit;
}

$UserAuthData = $_SESSION['UserAuthData'];
// If session data is serialized, unserialize it
if (is_string($UserAuthData)) {
    $UserAuthData = unserialize($UserAuthData);
}

$action = $_POST['action'] ?? '';

if ($action == 'fetch_student_data') {
    $department = $_POST['department'] ?? '';
    $program = $_POST['program'] ?? '';
    $course_code = $_POST['course_code'] ?? '';
    $batch = $_POST['batch'] ?? '';
    $yoa = $_POST['yoa'] ?? '';

    // Validate required fields
    if (empty($department) || empty($program) || empty($course_code) || empty($batch) || empty($yoa)) {
        echo '<div class="alert alert-danger">Missing required parameters</div>';
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student List</title>
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">
        <link href="css/page.css" rel="stylesheet" type="text/css">
        <link href="css/docs.css" rel="stylesheet" type="text/css">
    </head>

    <body>
        <div class="container mt-4">
            <?php
            if ($UserAuthData['role'] == 'admin' || ($UserAuthData['role'] == 'hod' && $UserAuthData['department'] == $department)) {
                echo "<a href='sample/student_data_sample.csv'><button type='button' class='btn btn-success'>Download Sample Student Data Sheet (CSV File)</button></a> ";
                echo "<button type='button' class='btn btn-success' id='Student-Data-CSV' data-toggle='modal' data-target='#UploadCSV'>Upload Student Data (CSV File)</button></a> ";
                echo "<a href='#'><button type='button' class='btn btn-success' id='Student-Data-Manual'>Manual Insertion</button></a> ";
            }
            ?>
            <p>&nbsp;</p>
            <table class="table table-bordered text-center" id="Student-List-Fetch">
            </table>

            <!-- Pop up Upload CSV-->
            <div class="container-fluid">
                <div class="row-fluid">
                    <div class="col-lg-12">
                        <div class="modal fade" id="UploadCSV" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                 <form method="post" action="dashboard_page/forms/upload_student_data.php" target="new" enctype="multipart/form-data" class="form-horizontal">
                                    <!-- CSRF Token INSIDE the form -->
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title">Upload Student CSV File</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="Department Code" class="col-sm-3 control-label">Department
                                                    Code</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="Student-Data-Department"
                                                        id="Student-Data-Department"
                                                        value="<?php echo htmlspecialchars($department); ?>"
                                                        readonly="readonly">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="Program" class="col-sm-3 control-label">Program</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="Student-Data-Program"
                                                        id="Student-Data-Program"
                                                        value="<?php echo htmlspecialchars($program); ?>"
                                                        readonly="readonly">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="Course" class="col-sm-3 control-label">Course</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="Student-Data-Course"
                                                        id="Student-Data-Course"
                                                        value="<?php echo htmlspecialchars($course_code); ?>"
                                                        readonly="readonly">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="Batch" class="col-sm-3 control-label">Batch</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="Student-Data-Batch"
                                                        id="Student-Data-Batch"
                                                        value="<?php echo htmlspecialchars($batch); ?>" readonly="readonly">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="Year of Admission" class="col-sm-3 control-label">Year of
                                                    Admission</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" name="Student-Data-YOA"
                                                        id="Student-Data-YOA" value="<?php echo htmlspecialchars($yoa); ?>"
                                                        readonly="readonly">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="CSV File" class="col-sm-3 control-label">CSV File</label>
                                                <div class="col-sm-9">
                                                    <input type="file" name="Student-Data-CSV" id="Student-Data-CSV"
                                                        required="required" accept=".csv,.txt">
                                                </div>
                                            </div>
                                            <p>&nbsp;</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary" id="UploadCSV-Submit">Upload
                                                Student Data</button>
                                        </div>
                                    </div>
                                </form>
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

            //Student List Fetch
            function fetch_student_data() {
                $("#Student-List-Fetch").html('');
                var program = "<?php echo htmlspecialchars($program, ENT_QUOTES); ?>";
                var course = "<?php echo htmlspecialchars($course_code, ENT_QUOTES); ?>";
                var batch = "<?php echo htmlspecialchars($batch, ENT_QUOTES); ?>";
                var yoa = "<?php echo htmlspecialchars($yoa, ENT_QUOTES); ?>";
                var department = "<?php echo htmlspecialchars($department, ENT_QUOTES); ?>";
                var course_name = "<?php echo htmlspecialchars($_POST['course'] ?? '', ENT_QUOTES); ?>";

                $.ajax({
                    type: "POST",
                    url: `${BASE_URL}/fetch_student.php`,
                    data: {
                        "department": department,
                        "program": program,
                        "course_code": course,
                        "course": course_name,
                        "batch": batch,
                        "yoa": yoa,
                        "action": "fetch_student"
                    },
                    dataType: "html",
                    success: function (data) {
                        $("#Student-List-Fetch").html(data);
                    },
                    error: function (xhr, status, error) {
                        console.error("Error fetching student data:", error);
                        $("#Student-List-Fetch").html('<tr><td colspan="8" class="text-danger">Error loading student data</td></tr>');
                    }
                });
            }

            // Load student data on page load
            fetch_student_data();

            // Initialize on page load
            $(document).ready(function () {
                console.log("Student List page loaded");
                console.log("Base URL:", BASE_URL);
            });
        </script>
    </body>

    </html>
    <?php
} else {
    echo '<div class="alert alert-danger">Invalid action</div>';
}
?>