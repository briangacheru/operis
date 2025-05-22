<?php include "head.php";?>
    <title>Project projects</title>
<?php include "navi.php";
$status = "OK";
$msg = "";
?>
    <div class="card shadow-none border mb-3">
        <div class="card-header d-flex flex-between-center bg-body-tertiary py-2">
            <h3 class="mb-0 text-primary bg">Active Projects</h3>
            <div>
                <button class="btn btn-outline-primary btn-sm me-2" type="button" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                    <span class="fas fa-plus fs-11"></span>
                    <span class="d-none d-sm-inline-block ms-1 align-middle" title="Add a new project">Add Project</span>
                </button>
            </div>
        </div>
    </div>

<?php
if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']);
    //echo '<meta http-equiv="refresh" content="10;url=' . htmlspecialchars($_SERVER['PHP_SELF']) . '">';
}
?>

    <div class="row g-3 mb-3">
        <div class="col-xxl-12">
            <div class="row g-3 h-100">
                <?php
                $sql = "
                    SELECT p.projectID, p.projectName, p.projectAmount, p.projectStatus, p.projectDescription, p.is_achieved, p.projectPeriod, COALESCE(SUM(b.amount), 0) AS currentProjectAmount
                    FROM tblprojects p LEFT JOIN tblbudget b  ON p.projectName = b.subcategory WHERE p.is_achieved = 0 AND p.is_deleted = 0
                    GROUP BY p.projectID, p.projectName, p.projectAmount, p.projectStatus, p.projectDescription, p.is_achieved, p.projectPeriod";
                $result = $con->query($sql);

                // Check if data exists
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $projectID = $row['projectID'];
                        $projectName = $row['projectName'];
                        $projectAmount = $row['projectAmount'];
                        $projectStatus = $row['projectStatus'];
                        $projectDate = $row['projectPeriod'];
                        $projectDescription = $row['projectDescription'];
                        $currentProjectAmount = $row['currentProjectAmount'];
                        $progress = ($currentProjectAmount / $projectAmount) * 100;
                        $projectBalance = ($projectAmount - $currentProjectAmount);

                        $currentDate = new DateTime();
                        $projectDateTime = new DateTime($projectDate);
                        $interval = $currentDate->diff($projectDateTime);
                        // Format the difference into months, weeks, and days
                        $projectPeriodParts = [];
                        if ($interval->m > 0) {
                            $projectPeriodParts[] = $interval->m . " month" . ($interval->m > 1 ? "s" : "");
                        }
                        if ($interval->d >= 7) {
                            $weeks = intdiv($interval->d, 7);
                            $projectPeriodParts[] = $weeks . " week" . ($weeks > 1 ? "s" : "");
                            $interval->d %= 7; // Remaining days after weeks
                        }
                        if ($interval->d > 0) {
                            $projectPeriodParts[] = $interval->d . " day" . ($interval->d > 1 ? "s" : "");
                        }

                        // Join the parts into a readable format
                        $projectPeriod = implode(" ", $projectPeriodParts);

                        echo '<div class="col-12">';
                        echo '<div class="card font-sans-serif h-100">';
                        echo '<div class="card-header pb-0">';
                        echo "<h5 class='mb-2'>$projectName</h5>";
                        echo '</div>';
                        echo '<div class="card-body pt-0">';
                        echo '<div class="row align-items-end h-100 mb-n1">';
                        echo '<div class="col-6 col-sm-5 pe-md-0 pe-lg-3">';
                        echo '<div class="row g-0">';
                        echo '<div class="col-7"><h6 class="text-600">Projected:</h6></div>';
                        echo "<div class='col-5'><h6 class='text-800'>Ksh " . number_format($projectAmount, 0) . "</h6></div>";
                        echo '</div>';
                        echo '<div class="row g-0 mb-2">';
                        echo '<div class="col-7"><h6 class="mb-0 text-600">Spent:</h6></div>';
                        echo "<div class='col-5'><h6 class='mb-0 text-800'>Ksh " . number_format($currentProjectAmount, 0) . "</h6></div>";
                        echo '</div>';
                        echo '<div class="row g-0 mb-2">';
                        echo '<div class="col-7"><h6 class="mb-0 text-600">Remaining:</h6></div>';
                        echo "<div class='col-5'><h6 class='mb-0 text-800'>Ksh " . number_format($projectBalance, 0) . "</h6></div>";
                        echo '</div>';
                        echo '<div class="row g-0">';
                        echo '<div class="col-7"><h6 class="mb-0 text-600">T-minus:</h6></div>';
                        echo "<div class='col-5'><h6 class='mb-0 text-800'>$projectPeriod</h6></div>";
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="col-6 col-sm-7">';
                        echo '<div class="row g-0 mb-2">';
                        echo '<div class="col-5"><h6 class="mb-0 text-600">Description:</h6></div>';
                        echo "<div class='col-7'><h6 class='mb-0 text-800'>$projectDescription</h6></div>";
                        echo '</div>';
                        echo '<div class="row g-0 mb-2">';
                        echo '<div class="col-5"><h6 class="mb-0 text-600">Progress:</h6></div>';
                        echo "<div class='col-7'><h6 class='mb-0 text-800'>" . round($progress, 2) . "%</h6></div>";
                        echo '</div>';
                        echo '<div class="progress">';
                        echo '<div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: ' . round($progress, 2) . '%;" aria-valuenow="' . round($progress, 2) . '" aria-valuemin="0" aria-valuemax="100">' . round($progress, 2) . '%</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<h6 class="text-center mb-0 text-600">No active projects yet.</h6>';
                }
                ?>

            </div>
        </div>

    </div>
    <div class="row g-3 mb-3">
        <div class="col-xxl-12">
            <div class="card overflow-hidden">
                <div class="card-header d-flex flex-between-center bg-body-tertiary py-2">
                    <h5 class="mb-0 text-primary bg">All Projects List</h5>
                </div>
                <div class="card-body px-0 pt-0" id="transaction-table">
                    <table class="table table-sm mb-0 overflow-hidden data-table fs-10"  data-datatables="data-datatables">
                        <thead class="bg-200">
                        <tr>
                            <th class="text-900 no-sort white-space-nowrap">
                                <div class="form-check mb-0 d-flex align-items-center">
                                    <input class="form-check-input" id="checkbox-select-all" type="checkbox" onclick="selectAllTasks(this)" data-bulk-select='{"body":"table-simple-pagination-body","actions":"table-simple-pagination-actions","replacedElement":"table-simple-pagination-replace-element"}' />
                                </div>
                            </th>
                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Project</th>
                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Projected Capital(Ksh)</th>
                            <th class="text-900 sort pe-1 text-center white-space-nowrap">Status</th>
                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Progress</th>
                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Due date</th>
                            <th class="text-900 no-sort pe-1 align-middle data-table-row-action"></th>
                        </tr>
                        </thead>
                        <tbody class="list" id="table-simple-pagination-body">
                        <?php
                        $query = mysqli_query($con,"SELECT p.projectID, p.projectName, p.projectDescription, p.projectStatus, p.projectPeriod, p.created_at, p.projectAmount, p.is_deleted, p.is_achieved, 
                        COALESCE(SUM(b.amount), 0) AS currentProjectAmount FROM tblprojects p LEFT JOIN tblbudget b ON p.projectName = b.subcategory 
                        WHERE p.is_deleted = 0 GROUP BY p.projectID ORDER BY p.projectID DESC");

                        $cnt=1;
                        while($row=mysqli_fetch_array($query)){
                            $projectID = $row['projectID'];
                            $projectName = $row['projectName'];
                            $projectDescription = $row['projectDescription'];
                            $projectAmount = $row['projectAmount'];
                            $projectPeriod = $row['projectPeriod'];
                            $currentProjectAmount = $row['currentProjectAmount'];
                            $progress = $projectAmount > 0 ? ($currentProjectAmount / $projectAmount) * 100 : 0;
                            $encodedId = base64_encode($row["projectID"]);
                            ?>
                            <tr class="hover-actions-trigger btn-reveal-trigger hover-bg-100">
                                <td class="align-middle" style="width: 28px;">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="simple-pagination-item-<?php echo $cnt; ?>" data-bulk-select-row="data-bulk-select-row" value="<?php echo $row['projectID']; ?>" name="projectIds[]"/>
                                    </div>
                                </td>
                                <td class="align-middle text-start product">
                                    <div class="d-flex align-items-center position-relative">
                                        <div class="flex-1">
                                            <h6 class="mb-0 fw-semi-bold text-nowrap">
                                                <a class="text-900 stretched-link" href="project-details?projectID=<?php echo $encodedId; ?>">
                                                    <?php echo $row["projectName"];?></a></h6>
                                            <p class="fw-semi-bold mb-0 text-500">
                                                <?php echo $row["projectDescription"];?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle text-start amount">
                                    <div class="d-flex align-items-center position-relative">
                                        <div class="flex-1">
                                            <h6 class="mb-0 fw-semi-bold text-nowrap"> <?php echo $row["projectAmount"]; ?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle text-start">
                                    <div class="d-flex align-items-center position-relative">
                                        <div class="flex-1">
                                            <h6 class="mb-0 fw-semi-bold text-nowrap">
                                                <span class="w-100 fs-10 badge <?php echo $row["projectStatus"] == 0 ? 'badge-subtle-warning' : 'badge-subtle-success'; ?>">
                                                    <?php echo $row["projectStatus"] == 0 ? "Active" : "Inactive"; ?>
                                                </span>
                                            </h6>
                                            <p class="fw-semi-bold mb-0 text-500">
                                                <span class="w-100 badge <?php echo $row["is_achieved"] == 0 ? 'badge-subtle-secondary' : 'badge-subtle-success'; ?>"><?php echo $row["is_achieved"] == 0 ? "Not Achieved Yet" : "Achieved & Completed"; ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle text-start amount">
                                    <div class="flex-1">
                                        <h6 class="mb-0 fw-semi-bold text-nowrap"> <?php echo round($progress, 2); ?>%</h6>
                                    </div>
                                </td>
                                <td class="align-middle text-start amount">
                                    <h6 class="mb-0 fw-semi-bold mb-0 text-500"><?php $originalDate = $row["projectPeriod"];
                                        $formattedDate = date("M j, Y", strtotime($originalDate));
                                        echo $formattedDate;?>
                                    </h6>
                                </td>
                                <td class="align-middle white-space-nowrap text-end position-relative">
                                    <div class="hover-actions bg-100">
                                        <button class="btn btn-outline-danger icon-item rounded-3 me-2 fs-11 icon-item-sm" data-bs-toggle="modal" data-bs-target="#editProjectModal" title="Edit this project"
                                                onclick="populateEditModal(<?php echo $row['projectID']; ?>, '<?php echo $row['projectName']; ?>', '<?php echo $row['projectDescription']; ?>', <?php echo $row['projectAmount']; ?>, <?php echo $row['projectStatus']; ?>, <?php echo $row['is_achieved']; ?>,'<?php echo $row['projectPeriod']; ?>')">
                                            <span class="fas fa-edit"></span>
                                        </button>
                                        <button class="btn icon-item rounded-3 me-2 fs-11 icon-item-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteProjectModal" title="Delete this project"
                                                onclick="populateDeleteModal(<?php echo $row['projectID']; ?>)">
                                            <span class="fas fa-trash"></span>
                                        </button>
                                    </div>
                                    <div class="dropdown font-sans-serif btn-reveal-trigger">
                                        <button class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal-sm transition-none" type="button" id="crm-recent-leads-4" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span class="fas fa-chevron-left fs-11"></span></button>
                                    </div>
                                </td>
                            </tr>
                            <?php
                            $cnt=$cnt+1;
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Project Modal -->
    <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <form id="addProjectForm" method="POST" action="add_project">
                    <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                        <div class="position-relative z-1">
                            <h4 class="mb-0 text-white" id="addProjectModalLabel">Add New Project</h4>
                        </div>
                        <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="addProjectName" class="form-label">Project Name</label>
                            <input type="text" class="form-control" id="addProjectName" name="projectName" required>
                        </div>
                        <div class="mb-3">
                            <label for="addProjectDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="addProjectDescription" name="projectDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="addProjectAmount" class="form-label">Amount (Ksh)</label>
                            <input type="number" class="form-control" id="addProjectAmount" name="projectAmount" required>
                        </div>
                        <div class="mb-3">
                            <label for="addProjectPeriod" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="addProjectPeriod" name="projectPeriod" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><span class="fas fa-save"></span> Add Project</button>
                    </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Edit Project Modal -->
    <div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editProjectForm" method="POST" action="edit_project">
                    <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                        <div class="position-relative z-1">
                            <h4 class="mb-0 text-white" id="editProjectModalLabel">Edit Project project</h4>
                        </div>
                        <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editProjectID" name="projectID">
                        <div class="mb-3">
                            <label for="editProjectName" class="form-label">Project Name</label>
                            <input type="text" class="form-control" id="editProjectName" name="projectName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editProjectDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editProjectDescription" name="projectDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editProjectAmount" class="form-label">Amount</label>
                            <input type="number" class="form-control" id="editProjectAmount" name="projectAmount" required>
                        </div>
                        <div class="mb-3">
                            <label for="editProjectStatus" class="form-label">Status</label>
                            <select class="form-control" id="editProjectStatus" name="projectStatus">
                                <option value="0">Active</option>
                                <option value="1">Completed</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editProjectPeriod" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="editProjectPeriod" name="projectPeriod" required>
                        </div>
                        <div class="mb-3">
                            <label for="editProjectAchievement" class="form-label">Achieved?</label>
                            <select class="form-control" id="editProjectAchievement" name="is_achieved">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><span class="fas fa-edit"></span> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Project Modal -->
    <div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-labelledby="deleteProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="deleteProjectForm" method="POST" action="delete_project">
                <div class="modal-content">
                    <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                        <div class="position-relative z-1">
                            <h4 class="mb-0 text-white" id="deleteProjectModalLabel">Delete Project</h4>
                        </div>
                        <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="deleteProjectID" name="projectID">
                        <p>Are you sure you want to delete this project?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger"><span class="fas fa-trash"></span> Delete project</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script type="text/javascript">
        function populateEditModal(projectID, projectName, projectDescription, projectAmount, projectStatus, is_achieved, projectPeriod) {
            document.getElementById('editProjectID').value = projectID;
            document.getElementById('editProjectName').value = projectName;
            document.getElementById('editProjectDescription').value = projectDescription;
            document.getElementById('editProjectAmount').value = projectAmount;
            document.getElementById('editProjectStatus').value = projectStatus;
            document.getElementById('editProjectAchievement').value = is_achieved;
            document.getElementById('editProjectPeriod').value = projectPeriod;
        }

        function populateDeleteModal(projectID) {
            document.getElementById('deleteProjectID').value = projectID;
        }

        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

<?php
include "footer.php";
?>