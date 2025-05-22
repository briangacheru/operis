<?php
include "check-login.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $projectID = $_POST['projectID'];
    $projectName = $_POST['projectName'];
    $projectDescription = $_POST['projectDescription'];
    $projectAmount = $_POST['projectAmount'];
    $projectStatus = $_POST['projectStatus'];
    $projectPeriod = $_POST['projectPeriod'];
    $is_achieved = $_POST['is_achieved'];

    $query = "UPDATE tblprojects
              SET projectName = '$projectName', projectDescription = '$projectDescription', projectAmount = '$projectAmount', projectStatus = '$projectStatus', projectPeriod = '$projectPeriod', is_achieved = '$is_achieved'
              WHERE projectID = $projectID";

    if (mysqli_query($con, $query)) {
        $_SESSION['alert'] = '<div class="alert alert-success border-0 d-flex align-items-center"><p class="mb-0 flex-1">' . htmlspecialchars($projectName) . ' project editted successfully!</p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    } else {
        $_SESSION['alert'] = '<div class="alert alert-danger border-0 d-flex align-items-center"><p class="mb-0 flex-1">Error: ' . mysqli_error($con) . '</p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
    header("Location: projects");
    exit();
}
?>
