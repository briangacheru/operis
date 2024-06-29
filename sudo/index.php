<?php
include "header.php";
?>
<?php
$aid = $_SESSION['odmsaid'];
$sql = "SELECT * FROM tbladmin WHERE email=:aid";
$query = $dbh->prepare($sql);
$query->bindParam(':aid', $aid, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
$cnt = 1;

if ($query->rowCount() > 0) {
foreach ($results as $row) {
if ($row->AdminName == "Admin") {
?>
<div class="row  g-3 mb-3">
    <div class="col">
        <div class="card h-lg-100 overflow-hidden">
            <div class="card-body p-0">
                <div class="card bg-transparent-50 overflow-hidden">
                    <div class="card-header position-relative">
                        <div class="bg-holder d-none d-md-block bg-card z-1" style="background-image:url(../assets/img/illustrations/tasking.png);background-size:230px;background-position:right bottom;z-index:-1;">
                        </div>
                        <!--/.bg-holder-->

                        <div class="position-relative z-2">
                            <div>
                                <?php
                                // Get the current hour in 24-hour format
                                $currentHour = date('G');
                                // Initialize greeting variable
                                $greeting = '';
                                // Determine the part of the day and set the appropriate greeting
                                if ($currentHour < 12) {
                                    $greeting = 'Good Morning';
                                } elseif ($currentHour < 18) {
                                    $greeting = 'Good Afternoon';
                                } else {
                                    $greeting = 'Good Evening';
                                }
                                ?>
                                <h3 class="text-primary mb-1"><?php echo $greeting; ?>, <span class="text-info"><?php echo $row->username; ?>!</span></h3>
                                <p>Here’s what happening with your tasks today </p>
                            </div>
                            <div class="d-flex py-3">
                                <div class="pe-3">
                                    <p class="text-600 fs-10 fw-medium">Tasks due Today</p>
                                    <?php
                                    $todayTasks = "";
                                    // Added condition to filter tasks posted today
                                    $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND DATE(due_date) = CURDATE()";
                                    $result = mysqli_query($con, $query);
                                    if ($result) {
                                        $row = mysqli_fetch_assoc($result);
                                        $count = $row['taskCount'];
                                        if ($count > 0) {
                                            $todayTasks = $count; // Set the count to output variable
                                        } else {
                                            $todayTasks = "0"; // Set "0" if count is 0
                                        }
                                    } else {
                                        $todayTasks = "No data"; // Set "No Data" if query fails
                                    }
                                    ?>
                                    <h4 class="text-800 mb-0"><?php echo $todayTasks; ?></h4>
                                </div>
                                <div class="ps-3">
                                    <p class="text-600 fs-10">Unpaid tasks total amount </p>
                                    <?php
                                    // Query to sum CPP*pages for completed, unpaid tasks
                                    $query1 = mysqli_query($con, "SELECT SUM(CPP*pages) AS total FROM tbltasks WHERE is_deleted = 0 AND is_paid = 0 AND status = 'Completed'");
                                    $result1 = mysqli_fetch_assoc($query1);
                                    $totalCompletedTasks = (float) $result1['total']; // Cast to float to ensure arithmetic operation

                                    // Query to sum amount from tbloverdrafts
                                    $query2 = mysqli_query($con, "SELECT SUM(amount) AS total FROM tbloverdrafts WHERE is_settled = 0 AND is_deleted = 0");
                                    $result2 = mysqli_fetch_assoc($query2);
                                    $totalOverdrafts = (float) $result2['total']; // Cast to float to ensure arithmetic operation

                                    // Calculate amount due by subtracting total completed task costs from total overdrafts
                                    $amount_due = $totalCompletedTasks - $totalOverdrafts;
                                    ?>
                                    <h4 class="text-800 mb-0"><?php echo number_format($amount_due, 2, '.', ','); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="mb-0 list-unstyled list-group font-sans-serif">
                            <?php
                            $allDeclined = "";
                            $query = "SELECT COUNT(*) as taskDeclined FROM tbltasks WHERE is_deleted = 0 AND (writer = 'Draft' OR status = 'Draft') AND is_confirmed = 2";
                            $result = mysqli_query($con, $query);
                            if ($result) {
                                $row = mysqli_fetch_assoc($result);
                                $count = $row['taskDeclined'];
                                if ($count > 0) {
                                    $allDeclined = $count; // Set the count to output variable
                                } else {
                                    $allDeclined = "0"; // Set "0" if count is 0
                                }
                            } else {
                                $allDeclined = "No data"; // Set "No Data" if query fails
                            }
                            ?>
                            <?php if ($allDeclined >= 1): ?>
                                <li class="list-group-item mb-0 rounded-0 py-3 px-x1 list-group-item-danger border-x-0 border-top-0">
                                    <div class="row flex-between-center">
                                        <div class="col">
                                            <div class="d-flex">
                                                <div class="fas fa-circle mt-1 fs-11"></div>
                                                <p class="fs-10 ps-2 mb-0"><strong><?php echo $allDeclined; ?> tasks</strong> are declined</p>
                                            </div>
                                        </div>
                                        <div class="col-auto d-flex align-items-center">
                                            <a class="fs-10 fw-medium text-warning-emphasis" href="drafts.php">
                                                View declined tasks<i class="fas fa-chevron-right ms-1 fs-11"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            <?php endif; ?>

                            <?php if ($lateTasksCount >= 1): ?>
                            <li class="list-group-item mb-0 rounded-0 py-3 px-x1 list-group-item-warning border-x-0 border-top-0">
                                <div class="row flex-between-center">
                                    <div class="col">
                                        <div class="d-flex">
                                            <div class="fas fa-circle mt-1 fs-11"></div>
                                            <p class="fs-10 ps-2 mb-0"><strong><?php echo $lateTasksCount; ?> tasks</strong> are late</p>
                                        </div>
                                    </div>
                                    <div class="col-auto d-flex align-items-center"><a class="fs-10 fw-medium text-warning-emphasis" href="tasks-in-progress.php">View late tasks<i class="fas fa-chevron-right ms-1 fs-11"></i></a></div>
                                </div>
                            </li>
                            <?php endif; ?>
                            <?php
                            $allUnpaid = "";
                            $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND is_paid = 0 AND status = 'Completed'";
                            $result = mysqli_query($con, $query);
                            if ($result) {
                                $row = mysqli_fetch_assoc($result);
                                $count = $row['taskCount'];
                                if ($count > 0) {
                                    $allUnpaid = $count; // Set the count to output variable
                                } else {
                                    $allUnpaid = "0"; // Set "0" if count is 0
                                }
                            } else {
                                $allUnpaid = "No data"; // Set "No Data" if query fails
                            }
                            ?>
                            <?php if ($allUnpaid >= 1): ?>
                            <li class="list-group-item mb-0 rounded-0 py-3 px-x1 list-group-item-info text-700 border-x-0 border-top-0">
                                <div class="row flex-between-center">
                                    <div class="col">
                                        <div class="d-flex">
                                            <div class="fas fa-circle mt-1 fs-11 text-primary"></div>
                                            <p class="fs-10 ps-2 mb-0"><strong><?php echo $allUnpaid ?> tasks</strong> are unpaid</p>
                                        </div>
                                    </div>
                                    <div class="col-auto d-flex align-items-center"><a class="fs-10 fw-medium" href="unpaid-tasks.php">View payments<i class="fas fa-chevron-right ms-1 fs-11"></i></a></div>
                                </div>
                            </li>
                            <?php endif; ?>
                            <?php
                            $allSubmitted = "";
                            $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND status = 'Submitted'";
                            $result = mysqli_query($con, $query);
                            if ($result) {
                                $row = mysqli_fetch_assoc($result);
                                $count = $row['taskCount'];
                                if ($count > 0) {
                                    $allSubmitted = $count; // Set the count to output variable
                                } else {
                                    $allSubmitted = "0"; // Set "0" if count is 0
                                }
                            } else {
                                $allSubmitted = "No data"; // Set "No Data" if query fails
                            }
                            ?>
                            <?php if ($allSubmitted >= 1): ?>
                            <li class="list-group-item mb-0 rounded-0 py-3 px-x1 list-group-item-success text-700  border-0">
                                <div class="row flex-between-center">
                                    <div class="col">
                                        <div class="d-flex">
                                            <div class="fas fa-circle mt-1 fs-11 text-success"></div>
                                            <p class="fs-10 ps-2 mb-0"><strong><?php echo $allSubmitted?> tasks</strong> need to be completed</p>
                                        </div>
                                    </div>
                                    <div class="col-auto d-flex align-items-center"><a class="fs-10 fw-medium text-success-emphasis" href="submitted-tasks.php">View submitted tasks<i class="fas fa-chevron-right ms-1 fs-11"></i></a></div>
                                </div>
                            </li>
                            <?php endif; ?>
                            <?php
                            // Fetch the current registration status
                            $query = "SELECT regStatus FROM tblsettings WHERE id = 1"; // writer registration
                            $result = mysqli_query($con, $query);
                            $currentStatus = mysqli_fetch_assoc($result)['regStatus'];
                            $currentStatusText = $currentStatus == 1 ? 'OPEN' : 'CLOSED';
                            $badgeClass = $currentStatus == 1 ? 'badge-subtle-success' : 'badge-subtle-danger';
                            ?>

                            <!-- Display the div if regStatus is 0 -->
                            <?php if ($currentStatus == 0): ?>
                                <li class="list-group-item mb-0 rounded-0 py-3 px-x1 list-group-item-danger border-x-0 border-top-0">
                                    <div class="row flex-between-center">
                                        <div class="col">
                                            <div class="d-flex">
                                                <div class="fas fa-circle mt-1 fs-11"></div>
                                                <p class="fs-10 ps-2 mb-0">Writer registration <strong>is CLOSED</strong></p>
                                            </div>
                                        </div>
                                        <div class="col-auto d-flex align-items-center">
                                            <a class="fs-10 fw-medium text-warning-emphasis" href="settings.php">
                                                Open registration<i class="fas fa-chevron-right ms-1 fs-11"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            <?php endif; ?>
                            <?php
                            // Fetch the current registration status
                            $query = "SELECT regStatus FROM tblsettings WHERE id = 2"; // admin registration
                            $result = mysqli_query($con, $query);
                            $currentStatus = mysqli_fetch_assoc($result)['regStatus'];
                            $currentStatusText = $currentStatus == 1 ? 'OPEN' : 'CLOSED';
                            $badgeClass = $currentStatus == 1 ? 'badge-subtle-success' : 'badge-subtle-danger';
                            ?>

                            <!-- Display the div if regStatus is 0 -->
                            <?php if ($currentStatus == 0): ?>
                                <li class="list-group-item mb-0 rounded-0 py-3 px-x1 list-group-item-danger border-x-0 border-top-0">
                                    <div class="row flex-between-center">
                                        <div class="col">
                                            <div class="d-flex">
                                                <div class="fas fa-circle mt-1 fs-11"></div>
                                                <p class="fs-10 ps-2 mb-0">Admin registration <strong>is CLOSED</strong></p>
                                            </div>
                                        </div>
                                        <div class="col-auto d-flex align-items-center">
                                            <a class="fs-10 fw-medium text-warning-emphasis" href="settings.php">
                                                Open registration<i class="fas fa-chevron-right ms-1 fs-11"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class=" col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-1.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $allTasks = "";
                $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0";
                $result = mysqli_query($con, $query);
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    $count = $row['taskCount'];
                    if ($count > 0) {
                        $allTasks = $count; // Set the count to output variable
                    } else {
                        $allTasks = "0"; // Set "0" if count is 0
                    }
                } else {
                    $allTasks = "No data"; // Set "No Data" if query fails
                }
                ?>
                <h6>All Tasks</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-warning" data-countup='{"endValue":<?php echo $allTasks; ?>,"decimalPlaces":0}'>0</div><a class="fw-semi-bold fs-10 text-nowrap text-warning" href="all-tasks.php">See tasks<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
    <div class=" col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-2.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $allDrafts = "";
                $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND (writer = 'Draft' OR status = 'Draft')";
                $result = mysqli_query($con, $query);
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    $count = $row['taskCount'];
                    if ($count > 0) {
                        $allDrafts = $count; // Set the count to output variable
                    } else {
                        $allDrafts = "0"; // Set "0" if count is 0
                    }
                } else {
                    $allDrafts = "No data"; // Set "No Data" if query fails
                }
                ?>
                <h6>Draft Tasks</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-info" data-countup='{"endValue":<?php echo $allDrafts; ?>,"decimalPlaces":0}'>0</div><a class="fw-semi-bold fs-10 text-nowrap text-info" href="draft-tasks.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-3.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $allUnconfirmed = "";
                $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND status = 'Unconfirmed'";
                $result = mysqli_query($con, $query);
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    $count = $row['taskCount'];
                    if ($count > 0) {
                        $allUnconfirmed = $count; // Set the count to output variable
                    } else {
                        $allUnconfirmed = "0"; // Set "0" if count is 0
                    }
                } else {
                    $allUnconfirmed = "No data"; // Set "No Data" if query fails
                }
                ?>
                <h6>Unconfirmed Tasks</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary" data-countup='{"endValue":<?php echo $allUnconfirmed; ?>}'>0</div><a class="fw-semi-bold fs-10 text-nowrap" href="unconfirmed.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class=" col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-1.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $allPaid = "";
                $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND is_paid = 1";
                $result = mysqli_query($con, $query);
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    $count = $row['taskCount'];
                    if ($count > 0) {
                        $allPaid = $count; // Set the count to output variable
                    } else {
                        $allPaid = "0"; // Set "0" if count is 0
                    }
                } else {
                    $allPaid = "No data"; // Set "No Data" if query fails
                }
                ?>
                <h6>Paid Tasks</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-warning" data-countup='{"endValue":<?php echo $allPaid; ?>}'>0</div><a class="fw-semi-bold fs-10 text-nowrap text-warning" href="paid-tasks.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
    <div class=" col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-2.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $allUnpaid = "";
                $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND is_paid = 0 AND status = 'Completed'";
                $result = mysqli_query($con, $query);
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    $count = $row['taskCount'];
                    if ($count > 0) {
                        $allUnpaid = $count; // Set the count to output variable
                    } else {
                        $allUnpaid = "0"; // Set "0" if count is 0
                    }
                } else {
                    $allUnpaid = "No data"; // Set "No Data" if query fails
                }
                ?>
                <h6>Unpaid Tasks</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-info" data-countup='{"endValue":<?php echo $allUnpaid; ?>}'>0</div><a class="fw-semi-bold fs-10 text-nowrap text-info" href="unpaid-tasks.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-3.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $allCancelled = "";
                $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 1";
                $result = mysqli_query($con, $query);
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    $count = $row['taskCount'];
                    if ($count > 0) {
                        $allCancelled = $count; // Set the count to output variable
                    } else {
                        $allCancelled = "0"; // Set "0" if count is 0
                    }
                } else {
                    $allCancelled = "No data"; // Set "No Data" if query fails
                }
                ?>
                <h6>Cancelled Tasks</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary" data-countup='{"endValue":<?php echo $allCancelled; ?>}'>0</div><a class="fw-semi-bold fs-10 text-nowrap text-primary" href="cancelled-tasks.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class=" col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-1.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $allProgress = "";
                $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND status = 'In Progress'";
                $result = mysqli_query($con, $query);
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    $count = $row['taskCount'];
                    if ($count > 0) {
                        $allProgress = $count; // Set the count to output variable
                    } else {
                        $allProgress = "0"; // Set "0" if count is 0
                    }
                } else {
                    $allProgress = "No data"; // Set "No Data" if query fails
                }
                ?>
                <h6>Tasks in progress</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-warning" data-countup='{"endValue":<?php echo $allProgress; ?>}'>0</div><a class="fw-semi-bold fs-10 text-nowrap text-warning" href="tasks-in-progress.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
    <div class=" col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-2.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $allSubmitted = "";
                $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND status = 'Submitted'";
                $result = mysqli_query($con, $query);
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    $count = $row['taskCount'];
                    if ($count > 0) {
                        $allSubmitted = $count; // Set the count to output variable
                    } else {
                        $allSubmitted = "0"; // Set "0" if count is 0
                    }
                } else {
                    $allSubmitted = "No data"; // Set "No Data" if query fails
                }
                ?>
                <h6>Submitted Tasks</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-info" data-countup='{"endValue":<?php echo $allSubmitted; ?>}'>0</div><a class="fw-semi-bold fs-10 text-nowrap text-info" href="submitted-tasks.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-3.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $allCompleted = "";
                $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND status = 'Completed'";
                $result = mysqli_query($con, $query);
                if ($result) {
                    $row = mysqli_fetch_assoc($result);
                    $count = $row['taskCount'];
                    if ($count > 0) {
                        $allCompleted = $count; // Set the count to output variable
                    } else {
                        $allCompleted = "0"; // Set "0" if count is 0
                    }
                } else {
                    $allCompleted = "No data"; // Set "No Data" if query fails
                }
                ?>
                <h6>Completed Tasks</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary" data-countup='{"endValue":<?php echo $allCompleted; ?>}'>0</div><a class="fw-semi-bold fs-10 text-nowrap text-primary" href="completed-tasks.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class=" col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-1.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $totalPaidFormatted = "No data"; // Default message if the query fails
                $totalPaidRaw = 0; // Raw total for JavaScript
                $query = mysqli_query($con, "SELECT SUM(CPP*pages) AS total FROM tbltasks WHERE is_deleted = 0 AND is_paid = 1");
                if ($query) {
                    $row = mysqli_fetch_array($query);
                    if ($row && $row['total'] !== null) {
                        $totalPaidRaw = $row['total']; // Keep the raw total
                        $totalPaidFormatted = 'Ksh. ' . number_format($row['total'], 2);
                    } else {
                        $totalPaidFormatted = 'Ksh. 0.00';
                    }
                } else {
                    $totalPaidFormatted = "Error: " . mysqli_error($con);
                }
                ?>
                <h6>Total Paid Amount</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-warning" data-countup='{"endValue":<?php echo $totalPaidRaw; ?>,"decimalPlaces":2,"prefix":"Ksh. "}'>0</div>
                <a class="fw-semi-bold fs-10 text-nowrap text-warning" href="paid-tasks.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
    <div class=" col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-2.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $totalUnPaidFormatted = "No data"; // Default message if the query fails
                $totalUnPaidRaw = 0; // Raw total for JavaScript
                $query = mysqli_query($con, "select sum(CPP*pages) as total  from tbltasks  WHERE is_deleted = 0 AND is_paid = 0 AND status = 'Completed'");
                if ($query) {
                    $row = mysqli_fetch_array($query);
                    if ($row && $row['total'] !== null) {
                        $totalUnPaidRaw = $row['total']; // Keep the raw total
                        $totalUnPaidFormatted = 'Ksh. ' . number_format($row['total'], 2);
                    } else {
                        $totalUnPaidFormatted = 'Ksh. 0.00';
                    }
                } else {
                    $totalUnPaidFormatted = "Error: " . mysqli_error($con);
                }
                ?>
                <h6>Total Unpaid Amount</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-info" data-countup='{"endValue":<?php echo $totalUnPaidRaw; ?>,"decimalPlaces":2,"prefix":"Ksh. "}'>0</div>
                <a class="fw-semi-bold fs-10 text-nowrap text-info" href="unpaid-tasks.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-3.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                // Query to sum CPP*pages for completed, unpaid tasks
                $query1 = mysqli_query($con, "SELECT SUM(CPP*pages) AS total FROM tbltasks WHERE is_deleted = 0 AND is_paid = 0 AND status = 'Completed'");
                $result1 = mysqli_fetch_assoc($query1);
                $totalCompletedTasks = (float) $result1['total']; // Cast to float to ensure arithmetic operation

                // Query to sum amount from tbloverdrafts
                $query2 = mysqli_query($con, "SELECT SUM(amount) AS total FROM tbloverdrafts WHERE is_settled = 0 AND is_deleted = 0");
                $result2 = mysqli_fetch_assoc($query2);
                $totalOverdrafts = (float) $result2['total']; // Cast to float to ensure arithmetic operation

                // Calculate amount due by subtracting total completed task costs from total overdrafts
                $amount_due = $totalCompletedTasks - $totalOverdrafts;
                ?>
                <h6>Total Amount Due</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary" data-countup='{"endValue":<?php echo $amount_due; ?>,"decimalPlaces":2,"prefix":"Ksh. "}'>0</div><a class="fw-semi-bold fs-10 text-nowrap text-primary" href="completed-tasks.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class=" col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-1.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $sql ="SELECT id from tblwriters where is_verified=1 AND is_deleted = 0";
                $query = $dbh -> prepare($sql);
                $query->execute();
                $results=$query->fetchAll(PDO::FETCH_OBJ);
                $totalusersquery=$query->rowCount();
                ?>
                <h6>Verified Users</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-warning" data-countup='{"endValue":<?php echo htmlentities($totalusersquery);?>}'>0</div>
                <a class="fw-semi-bold fs-10 text-nowrap text-warning" href="usermanagement.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
    <div class=" col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-2.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $totalOverDraftsFormatted = "No data"; // Default message if the query fails
                $totalOverDraftsRaw = 0; // Raw total for JavaScript
                $query = mysqli_query($con, "select sum(amount) as totalDraft  from tbloverdrafts  WHERE is_settled = 0");
                if ($query) {
                    $row = mysqli_fetch_array($query);
                    if ($row && $row['totalDraft'] !== null) {
                        $totalOverDraftsRaw = $row['totalDraft']; // Keep the raw total
                        $totalOverDraftsFormatted = 'Ksh. ' . number_format($row['totalDraft'], 2);
                    } else {
                        $totalOverDraftsFormatted = 'Ksh. 0.00';
                    }
                } else {
                    $totalOverDraftsFormatted = "Error: " . mysqli_error($con);
                }
                ?>
                <h6>Total Total Overdraft Amount</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-info" data-countup='{"endValue":<?php echo $totalOverDraftsRaw; ?>,"decimalPlaces":2,"prefix":"Ksh. "}'>0</div>
                <a class="fw-semi-bold fs-10 text-nowrap text-info" href="overdraft.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card overflow-hidden" style="min-width: 12rem">
            <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-3.png);">
            </div>
            <!--/.bg-holder-->

            <div class="card-body position-relative">
                <?php
                $sql ="SELECT id from tblwriters where is_deleted = 0";
                $query = $dbh -> prepare($sql);
                $query->execute();
                $results=$query->fetchAll(PDO::FETCH_OBJ);
                $totalusersquery=$query->rowCount();
                ?>
                <h6>Total Writers</h6>
                <div class="display-4 fs-5 mb-2 fw-normal font-sans-serif text-primary" data-countup='{"endValue":<?php echo htmlentities($totalusersquery);?>}'>0</div>
                <a class="fw-semi-bold fs-10 text-nowrap text-primary" href="usermanagement.php">See all<span class="fas fa-angle-right ms-1" data-fa-transform="down-1"></span></a>
            </div>
        </div>
    </div>
</div>
    <?php
    // AdminName is "Admin", do something for admin
} else {
    echo '
<div class="row-cols-lg-12">
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <h4 class="alert-heading">Notification</h4>
        <p>Your account needs to be verified first</p>
            <hr>
            <p class="mb-0">Update your <a href="profile.php">Profile</a> in the mean time.</p>
    </div>
</div>';}}}?>

<?php
include "footer.php";
?>




