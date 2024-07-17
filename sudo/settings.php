<?php include "head.php";?>
<title>iTasker | Settings</title>
<?php include "navi.php";

$message = ''; // Initialize an empty message variable
$error_message = ''; // Initialize an empty error message variable

if (isset($_POST['upload_cover_image'])) {
    $adminid = $_SESSION['odmsaid'];

    // Handle file upload
    if (isset($_FILES['cover-image']) && $_FILES['cover-image']['error'] == 0) {
        $target_dir = "../profileimages/";
        $file_name = basename($_FILES["cover-image"]["name"]);
        $target_file = $target_dir . $file_name;

        // Check if file is an actual image
        $check = getimagesize($_FILES["cover-image"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["cover-image"]["tmp_name"], $target_file)) {
                // Update the cover image in the database
                $sql = "UPDATE tbladmin SET coverImage=:coverImage WHERE email=:aid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':coverImage', $file_name, PDO::PARAM_STR); // Use only the file name
                $query->bindParam(':aid', $adminid, PDO::PARAM_STR);
                $query->execute();
                $message = 'Cover image has been updated successfully.';
            } else {
                $error_message = 'Failed to upload cover image.';
            }
        } else {
            $error_message = 'File is not an image.';
        }
    } else {
        $error_message = 'No file was uploaded or there was an upload error.';
    }
}

if (isset($_POST['upload_image'])) {
    $adminid = $_SESSION['odmsaid'];

    // Handle file upload
    if (isset($_FILES['profile-image']) && $_FILES['profile-image']['error'] == 0) {
        $target_dir = "../profileimages/";
        $file_name = basename($_FILES["profile-image"]["name"]);
        $target_file = $target_dir . $file_name;

        // Check if file is an actual image
        $check = getimagesize($_FILES["profile-image"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["profile-image"]["tmp_name"], $target_file)) {
                // Update the profile image in the database
                $sql = "UPDATE tbladmin SET Photo=:photo WHERE email=:aid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':photo', $file_name, PDO::PARAM_STR); // Use only the file name
                $query->bindParam(':aid', $adminid, PDO::PARAM_STR);
                $query->execute();
                $message = 'Profile image has been updated successfully.';
            } else {
                $error_message = 'Failed to upload profile image.';
            }
        } else {
            $error_message = 'File is not an image.';
        }
    } else {
        $error_message = 'No file was uploaded or there was an upload error.';
    }
}


if (isset($_POST['submit'])) {
    $adminid = $_SESSION['odmsaid'];
    $AName = $_POST['username'];
    $fName = $_POST['firstname'];
    $lName = $_POST['lastname'];
    $mobno = $_POST['mobilenumber'];
    $email = $_POST['email'];

    // Handle file upload
    if (isset($_FILES['profile-image']) && $_FILES['profile-image']['error'] == 0) {
        $target_dir = "../profileimages/";
        $target_file = $target_dir . basename($_FILES["profile-image"]["name"]);
        if (move_uploaded_file($_FILES["profile-image"]["tmp_name"], $target_file)) {
            // Update the profile image in the database
            $sql = "UPDATE tbladmin SET Photo=:photo WHERE email=:aid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':photo', $target_file, PDO::PARAM_STR);
            $query->bindParam(':aid', $adminid, PDO::PARAM_STR);
            $query->execute();
        } else {
            $error_message = 'Failed to upload profile image.';
        }
    }

    if (empty($error_message)) {
        $sql = "UPDATE tbladmin SET username=:username, FirstName=:firstname, LastName=:lastname, MobileNumber=:mobilenumber, email=:email WHERE email=:aid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':username', $AName, PDO::PARAM_STR);
        $query->bindParam(':firstname', $fName, PDO::PARAM_STR);
        $query->bindParam(':lastname', $lName, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':mobilenumber', $mobno, PDO::PARAM_STR);
        $query->bindParam(':aid', $adminid, PDO::PARAM_STR);

        if ($query->execute()) {
            $message = 'Profile has been updated successfully.';
        } else {
            $error_message = 'Failed to update profile.';
        }
    }
}

if (isset($_POST['update_password'])) {
    $adminid = $_SESSION['odmsaid'];
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Verify old password
    $sql = "SELECT Password FROM tbladmin WHERE email=:aid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':aid', $adminid, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result && password_verify($oldPassword, $result->Password)) {
        if ($newPassword === $confirmPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE tbladmin SET Password=:newpassword WHERE email=:aid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':newpassword', $hashedPassword, PDO::PARAM_STR);
            $query->bindParam(':aid', $adminid, PDO::PARAM_STR);
            if ($query->execute()) {
                $message = 'Password has been updated successfully.';
            } else {
                $error_message = 'Failed to update password.';
            }
        } else {
            $error_message = 'New password and confirm password do not match.';
        }
    } else {
        $error_message = 'Old password is incorrect.';
    }
}


// Check if the form is submitted
if (isset($_POST['submitStatus'])) {
    $newStatus = isset($_POST['newStatus']) ? (int) $_POST['newStatus'] : null;

    // Validate new status
    if ($newStatus !== null && ($newStatus === 0 || $newStatus === 1)) {
        // Update the registration status in the database
        $query = "UPDATE tblsettings SET regStatus = ? WHERE id = 1"; // Assuming there's only one record to update
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "i", $newStatus);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Writer Registration status updated successfully.";
        } else {
            $error_message = "Failed to update registration status.";
        }
    } else {
        $error_message = "Invalid status value.";
    }
}

// Fetch the current registration status
$query = "SELECT regStatus FROM tblsettings WHERE id = 1"; // Assuming there's only one record
$result = mysqli_query($con, $query);
$currentStatus = mysqli_fetch_assoc($result)['regStatus'];
$currentStatusText = $currentStatus == 1 ? 'OPEN' : 'CLOSED';
$badgeClass = $currentStatus == 1 ? 'badge-subtle-success' : 'badge-subtle-danger';

// Check if the form is submitted
if (isset($_POST['adminStatus'])) {
    $newStatus = isset($_POST['newStatus']) ? (int) $_POST['newStatus'] : null;

    // Validate new status
    if ($newStatus !== null && ($newStatus === 0 || $newStatus === 1)) {
        // Update the registration status in the database
        $query = "UPDATE tblsettings SET regStatus = ? WHERE id = 2"; // Assuming there's only one record to update
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "i", $newStatus);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Admin Registration status updated successfully.";
        } else {
            $error_message = "Failed to update registration status.";
        }
    } else {
        $error_message = "Invalid status value.";
    }
}

// Fetch the current registration status
$query1 = "SELECT regStatus FROM tblsettings WHERE id = 2"; // Assuming there's only one record
$result1 = mysqli_query($con, $query1);
$currentStatus1 = mysqli_fetch_assoc($result1)['regStatus'];
$currentStatusText1 = $currentStatus1 == 1 ? 'OPEN' : 'CLOSED';
$badgeClass1 = $currentStatus1 == 1 ? 'badge-subtle-success' : 'badge-subtle-danger';

// Handle form submission for updating notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notificationSubmit'])) {
    $notificationText = $_POST['notificationText'];

    $sql = "UPDATE tblsettings SET description = ?, regStatus = 1 WHERE id = 3";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $notificationText);

    if ($stmt->execute()) {
        $message = "Notification updated successfully.";
    } else {
        $error_message = "Error updating notification: " . $stmt->error;
    }

    $stmt->close();
}

// Handle form submission for deleting notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notificationDelete'])) {
    $sql = "UPDATE tblsettings SET description = '', regStatus = 0 WHERE id = 3";
    $stmt = $con->prepare($sql);

    if ($stmt->execute()) {
        $message = "Notification deleted successfully.";
    } else {
        $error_message = "Error deleting notification: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch current notification
$query = mysqli_query($con, "SELECT * FROM tblsettings WHERE id = 3");
$row = mysqli_fetch_assoc($query);
$currentNotification = $row['description'];

?>

<div class="row">
    <div class="col-12">
        <?php if ($message): ?>
            <div class="alert alert-success border-0 d-flex align-items-center" role="alert">
                <div class="bg-success me-3 icon-item"><span class="fas fa-check-circle text-white fs-6"></span></div>
                <p class="mb-0 flex-1"><?php echo $message; ?></p>
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger border-0 d-flex align-items-center" role="alert">
                <div class="bg-danger me-3 icon-item"><span class="fas fa-times-circle text-white fs-6"></span></div>
                <p class="mb-0 flex-1"><?php echo $error_message; ?></p>
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="card mb-3 btn-reveal-trigger">
            <div class="card-header position-relative min-vh-25 mb-8">
                <?php
                $adminid = $_SESSION['odmsaid'];
                $sql = "SELECT * from tbladmin where email =:aid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':aid', $adminid, PDO::PARAM_STR);
                $query->execute();
                $results = $query->fetchAll(PDO::FETCH_OBJ);
                if ($query->rowCount() > 0) {
                foreach ($results as $row) {
                ?>
                <div class="cover-image">
                    <?php if ($row->coverImage == "1.jpg") { ?>
                    <div class="bg-holder rounded-3 rounded-bottom-0" style="background-image:url(../profileimages/1.jpg);">
                        <?php } else { ?>
                        <div class="bg-holder rounded-3 rounded-bottom-0" style="background-image:url('../profileimages/<?php echo $row->coverImage; ?>');">
                            <?php } ?>
                        </div>
                        <!--/.bg-holder-->
                        <label class="cover-image-file-input" data-bs-toggle="modal" data-bs-target="#updateCoverModal" style="cursor: pointer;">
                            <span class="fas fa-camera me-2"></span><span>Change cover photo</span>
                        </label>
                    </div>
                    <div class="avatar avatar-5xl avatar-profile shadow-sm img-thumbnail rounded-circle">
                        <div class="h-100 w-100 rounded-circle overflow-hidden position-relative">
                            <?php if ($row->Photo == "avatar.png") { ?>
                                <img src="../assets/img/team/avatar.png" width="200" alt="" data-dz-thumbnail="data-dz-thumbnail">
                            <?php } else { ?>
                                <img src="../profileimages/<?php echo $row->Photo; ?>" width="200" alt="" data-dz-thumbnail="data-dz-thumbnail">
                            <?php } ?>
                            <label class="mb-0 overlay-icon d-flex flex-center" data-bs-toggle="modal" data-bs-target="#updateModal">
                                <span class="bg-holder overlay overlay-0"></span>
                                <span class="z-1 text-white dark__text-white text-center fs-10">
                                <span class="fas fa-camera"></span>
                                <span class="d-block">Update</span>
                            </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cover Image Modal -->
    <div class="modal fade" id="updateCoverModal" tabindex="-1" aria-labelledby="updateCoverModalLabel" aria-hidden="true">
        <div class="modal-dialog mt-6" role="document">
            <div class="modal-content border-0">
                <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                    <div class="position-relative z-1">
                        <h4 class="mb-0 text-white" id="cover-modal-label">Update your Cover Image</h4>
                    </div>
                    <button class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>>
                </div>
                <div class="modal-body py-4 px-5">
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group mb-3">
                            <label for="cover-image">Choose a new cover image</label>
                            <input type="file" class="form-control-file" id="cover-image" name="cover-image" required>
                        </div>
                        <button type="submit" name="upload_cover_image"  class="btn btn-outline-primary w-50">Upload Cover Image</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Avatar Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog mt-6" role="document">
            <div class="modal-content border-0">
                <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                    <div class="position-relative z-1">
                        <h4 class="mb-0 text-white" id="avatar-modal-label">Update your Avatar</h4>
                    </div>
                    <button class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4 px-5">
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group mb-3">
                            <label for="profile-image">Choose a new profile image</label>
                            <input type="file" class="form-control-file" id="profile-image" name="profile-image" required>
                        </div>
                        <button type="submit" name="upload_image" class="btn btn-outline-primary w-50">Upload Avatar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-0">
        <div class="col-lg-8 pe-lg-2">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 text-info">Profile Settings</h5>
                </div>
                <div class="card-body bg-body-tertiary">
                    <form class="row g-3" method="post" enctype="multipart/form-data">
                        <div class="col-lg-6 form-floating">
                            <input class="form-control" id="first-name" type="text" name="firstname" value="<?php echo $row->FirstName; ?>" />
                            <label for="first-name">First Name</label>
                        </div>
                        <div class="col-lg-6 form-floating">
                            <input class="form-control" id="last-name" type="text" name="lastname" value="<?php echo $row->LastName; ?>" />
                            <label for="last-name">Last Name</label>
                        </div>
                        <div class="col-lg-6 form-floating">
                            <input class="form-control" id="email1" type="email" name="email" value="<?php echo $row->email; ?>" />
                            <label for="email1">Email</label>
                        </div>
                        <div class="col-lg-6 form-floating">
                            <input class="form-control" id="email2" type="text" name="mobilenumber" value="<?php echo $row->MobileNumber; ?>" />
                            <label for="email2">Phone</label>
                        </div>
                        <div class="col-lg-6 form-floating">
                            <input class="form-control" id="user-name" type="text" name="username" value="<?php echo $row->username; ?>" />
                            <label for="user-name">Username</label>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-outline-primary w-100" type="submit" name="submit">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php
            if($row->superadmin == 1)
            {
                ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0 text-info">Writer Registration Settings</h5>
                    </div>
                    <div class="card-body bg-body-tertiary">
                        <!-- Display Current Registration Status -->
                        <p>Current Writer Registration Status: <span class="badge fs-10 <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($currentStatusText, ENT_QUOTES, 'UTF-8'); ?></span></p>
                        <!-- Form to Update Registration Status -->
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="newStatus" value="<?php echo $currentStatus == 1 ? 0 : 1; ?>">
                            <button class="btn btn-outline-primary w-100" type="submit" name="submitStatus">
                                <?php echo $currentStatus == 1 ? 'Close Registration' : 'Open Registration'; ?>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0 text-info">Admin Registration Settings</h5>
                    </div>
                    <div class="card-body bg-body-tertiary">
                        <!-- Display Current Registration Status -->
                        <p>Current Admin Registration Status: <span class="badge fs-10 <?php echo $badgeClass1; ?>"><?php echo htmlspecialchars($currentStatusText1, ENT_QUOTES, 'UTF-8'); ?></span></p>
                        <!-- Form to Update Registration Status -->
                        <form method="post" enctype="multipart/form-data">
                            <input type="hidden" name="newStatus" value="<?php echo $currentStatus1 == 1 ? 0 : 1; ?>">
                            <button class="btn btn-outline-primary w-100" type="submit" name="adminStatus">
                                <?php echo $currentStatus1 == 1 ? 'Close Registration' : 'Open Registration'; ?>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0 text-info">Notifications Settings</h5>
                    </div>
                    <div class="card-body bg-body-tertiary">
                        <!-- Form to Update Notification -->
                        <!-- Form to Update Notification -->
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="notificationText" class="form-label">Notification Text</label>
                                <textarea class="form-control" id="notificationText" name="notificationText" rows="3" required><?php echo htmlspecialchars($currentNotification, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <button class="btn btn-outline-primary me-1 mb-1" type="submit" name="notificationSubmit">Update Notification</button>
                            <button class="btn btn-outline-danger me-1 mb-1 float-end" type="submit" name="notificationDelete" onclick="return confirm('Are you sure you want to delete the notification?');">Delete Notification</button>
                        </form>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <?php
            } ?>
        </div>
        <div class="col-lg-4 ps-lg-2">
            <div class="sticky-sidebar">
                <div class="card mb-3 overflow-hidden">
                    <div class="card-header">
                        <h5 class="mb-0 text-info">Change Password</h5>
                    </div>
                    <div class="card-body bg-body-tertiary">
                        <form method="post">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="old-password" type="password" name="old_password" placeholder="Old Password" />
                                <label for="old-password">Old Password</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="new-password" type="password" name="new_password" placeholder="New Password" />
                                <label for="new-password">New Password</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="confirm-password" type="password" name="confirm_password" placeholder="Confirm Password" />
                                <label for="confirm-password">Confirm Password</label>
                            </div>
                            <button class="btn btn-outline-primary d-block w-100" type="submit" name="update_password">Update Password</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 text-info">Danger Zone</h5>
                    </div>
                    <div class="card-body bg-body-tertiary">
                        <h5 class="fs-9">Delete this account</h5>
                        <p class="fs-10">Once you delete an account, there is no going back. Please be certain.</p><a class="btn btn-falcon-danger d-block" href="#!">Deactivate Account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
    }
    ?>
    <?php include "footer.php"; ?>
