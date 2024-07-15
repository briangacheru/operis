<?php
include "header.php";

$writerID = isset($_GET['writerID']) ? base64_decode($_GET['writerID']) : null;

if ($writerID) {
    // Fetch writer details
    $sql="SELECT * FROM tblwriters WHERE id=?";
    $query = $dbh->prepare($sql);
    $query->bindParam(1, $writerID, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if ($query->rowCount() > 0) {
        foreach ($results as $row) {
            // Fetch completed tasks count
            $completedQuery = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND status = 'Completed' AND email = ?";
            $completedStmt = $dbh->prepare($completedQuery);
            $completedStmt->execute([$row->email]);
            $completedRow = $completedStmt->fetch(PDO::FETCH_ASSOC);
            $allCompleted = $completedRow['taskCount'] ?? 0;

            // Fetch in-progress tasks count
            $progressQuery = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND status = 'In Progress' AND email = ?";
            $progressStmt = $dbh->prepare($progressQuery);
            $progressStmt->execute([$row->email]);
            $progressRow = $progressStmt->fetch(PDO::FETCH_ASSOC);
            $allProgress = $progressRow['taskCount'] ?? 0;

            // Fetch total paid
            $totalPaidQuery = "SELECT SUM(CPP*pages) AS total FROM tbltasks WHERE is_deleted = 0 AND is_paid = 1 AND email = ?";
            $totalPaidStmt = $dbh->prepare($totalPaidQuery);
            $totalPaidStmt->execute([$row->email]);
            $totalPaidRow = $totalPaidStmt->fetch(PDO::FETCH_ASSOC);
            $totalPaidRaw = $totalPaidRow['total'] ?? 0;
            $totalPaidFormatted = 'Ksh. ' . number_format($totalPaidRaw, 2);

            // Display writer profile
            ?>
            <div class="card mb-3">
                <div class="card-header position-relative min-vh-25 mb-7">
                    <div class="bg-holder rounded-3 rounded-bottom-0" style="background-image:url('../profileimages/<?php echo $row->coverImage ?: '1.jpg'; ?>');"></div>
                    <!--/.bg-holder-->
                    <div class="avatar avatar-5xl avatar-profile">
                        <img class="rounded-circle img-thumbnail shadow-sm" src="../profileimages/<?php echo $row->Photo ?: 'avatar.png'; ?>" width="200" alt="">
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <h4 class="mb-1 text-info">
                                <?php echo $row->FirstName . ' ' . $row->LastName; ?>
                                <span data-bs-toggle="tooltip" data-bs-placement="right" title="<?php echo $row->is_verified ? 'Verified' : 'Unverified'; ?>">
                                    <small class="fa fa-<?php echo $row->is_verified ? 'check-circle text-primary' : 'times-circle text-secondary'; ?>" data-fa-transform="shrink-4 down-2"></small>
                                </span>
                            </h4>
                            <h5 class="fs-9 fw-normal text-primary"><?php echo $row->email; ?></h5>
                            <p class="text-500"><?php echo $row->phone; ?></p>
                            <div class="border-bottom border-dashed my-4 d-lg-none"></div>
                        </div>
                        <div class="col ps-2 ps-lg-5">
                            <div class="d-flex align-items-center mb-2">
                                <span class="fas fa-user-secret fs-8 me-2 text-info" title="Member Since" data-fa-transform="grow-2"></span>
                                <div class="flex-1">
                                    <h6 class="mb-0 text-primary">
                                        <?php echo date("jS F, Y", strtotime($row->created_at)); ?>
                                    </h6>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="fas fa-archive fs-8 me-2 text-info" title="Completed Tasks" data-fa-transform="grow-2"></span>
                                <div class="flex-1">
                                    <h6 class="mb-0 text-primary"><?php echo $allCompleted; ?> Completed tasks</h6>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="fas fa-spinner fs-8 me-2 text-info" title="Tasks Pending" data-fa-transform="grow-2"></span>
                                <div class="flex-1">
                                    <h6 class="mb-0 text-primary"><?php echo $allProgress; ?> Task(s) pending</h6>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="fas fa-wallet fs-8 me-2 text-info" title="Total Disbursed" data-fa-transform="grow-2"></span>
                                <div class="flex-1">
                                    <h6 class="mb-0 text-primary"><?php echo $totalPaidFormatted; ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-0">
                <div class="col-lg-12">
                    <div class="card mb-3">
                        <div class="card-header bg-body-tertiary">
                            <h5 class="mb-0 text-info">Intro</h5>
                        </div>
                        <div class="card-body text-justify">
                            <p class="mb-0 text-primary">Dedicated, passionate, and accomplished Full Stack Developer with 9+ years of progressive experience working as an Independent Contractor for Google and developing and growing my educational social network that helps others learn programming, web design, game development, networking.</p>
                            <div class="collapse show" id="profile-intro">
                                <p class="mt-3 text-primary">I’ve acquired a wide depth of knowledge and expertise in using my technical skills in programming, computer science, software development, and mobile app development to developing solutions to help organizations increase productivity, and accelerate business performance. </p>
                                <p class="text-primary">It’s great that we live in an age where we can share so much with technology but I’m but I’m ready for the next phase of my career, with a healthy balance between the virtual world and a workplace where I help others face-to-face.</p>
                                <p class="mb-0 text-primary">There’s always something new to learn, especially in IT-related fields. People like working with me because I can explain technology to everyone, from staff to executives who need me to tie together the details and the big picture. I can also implement the technologies that successful projects need.</p>
                            </div>
                        </div>
                        <div class="card-footer bg-body-tertiary p-0 border-top">
                            <button class="btn btn-link d-block w-100 btn-intro-collapse" type="button" data-bs-toggle="collapse" data-bs-target="#profile-intro" aria-expanded="true" aria-controls="profile-intro">Show <span class="less">less<span class="fas fa-chevron-up ms-2 fs-11"></span></span><span class="full">full<span class="fas fa-chevron-down ms-2 fs-11"></span></span></button>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="alert alert-danger">Writer not found.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid writer ID.</div>';
}

include "footer.php";
?>
