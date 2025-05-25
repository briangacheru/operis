<?php
include "head.php";

$taskId = ''; // Initialize the $taskId variable

if (isset($_GET['task_id'])) {
    $encodedId = $_GET['task_id'];
    $taskId = base64_decode($encodedId);
} else {
    $_SESSION['alert'] ='<div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                                        <div class="bg-warning me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                        <p class="mb-0 flex-1">Invalid task ID!</p>
                                        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
}

// Define variables for task data
$taskTopic = $taskSubject = $taskAccount = $taskCreatedOn = $taskStatus = $taskIsPaid = $taskDescription = $taskWriter = $taskWriterEmail = $taskDueDate = $taskCPP = $taskPages = $existingFiles = $taskSubmitTime = $submittedOn =  '';

// Retrieve the task data from the database
$sql2 = "SELECT * FROM tbltasks WHERE id='$taskId'";
$result = mysqli_query($con, $sql2);

if ($rowTask = mysqli_fetch_array($result)) {
    $id = base64_encode($rowTask["id"]);
    $taskTopic = $rowTask["topic"];
    $taskSubject = $rowTask["subject"];
    $taskAccount = $rowTask["account"];
    $taskCreatedOn = $rowTask["create_date"];
    $taskStatus = $rowTask["status"];
    $taskIsPaid = $rowTask["is_paid"];
    $taskDescription = $rowTask["description"];
    $taskWriter = $rowTask["writer"];
    $taskWriterEmail = $rowTask["email"];
    $taskDueDate = $rowTask["due_date"];
    $taskCPP = $rowTask["cpp"];
    $taskPages = $rowTask["pages"];
    $existingFiles = $rowTask['task_files']; // Assuming this contains comma-separated file paths
    $submittedFiles = $rowTask['submitted_files'];
    $taskSubmitTime = $rowTask['submitted_on'];
    $submittedOn = $rowTask['submitted_on'];
}
$due_date = new DateTime($rowTask['due_date']);
$currentDateTime = new DateTime(); // Assuming you've already got this
$interval = $currentDateTime->diff($due_date);
$isLate = ($due_date < $currentDateTime) ? true : false;
// Determine badge based on task status
$statusBadge = '';
switch ($rowTask["status"]) {
    case 'In Progress':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-warning fs-11">In progress<span class="fas fa-stream ms-1" data-fa-transform="shrink-2"></span></div>';
        break;
    case 'In Revision':
        $statusBadge = '<span class="badge badge rounded-pill badge-subtle-warning">In Revision<span class="ms-1 fas fa-flag" data-fa-transform="shrink-2"></span></span>';
        break;
    case 'Cancelled':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-danger fs-11">Cancelled<span class="fas fa-ban ms-1" data-fa-transform="shrink-2"></span></div>';
        break;
    case 'Draft':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-danger fs-11">Draft<span class="fas fa-edit ms-1" data-fa-transform="shrink-2"></span></div>';
        break;
    case 'Unconfirmed':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-primary fs-11">Unconfirmed<span class="fas fa-question ms-1" data-fa-transform="shrink-2"></span></div>';
        break;
    case 'Submitted':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-info fs-11">Submitted<span class="fas fa-file ms-1" data-fa-transform="shrink-2"></span></div>';
        break;
    case 'Completed':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-success fs-11">Completed<span class="fas fa-check ms-1" data-fa-transform="shrink-2"></span></div>';
        break;
}
if ($isLate && $rowTask["status"] === 'In Progress') {
    $statusBadge .= ' <span class="badge badge rounded-pill badge-subtle-danger">Late<span class="ms-1 fa fa-exclamation-triangle" data-fa-transform="shrink-2"></span></span>';
}
// Correctly retrieve is_paid status from the row
$is_paid = $rowTask['is_paid']; // Assuming 'is_paid' is the column name in your database

// Determine badge based on payment status
$statusBadgeClass = ($is_paid == 1) ? 'bg-success' : 'bg-warning';
$statusBadgeText = ($is_paid == 1) ? 'Paid' : 'Unpaid';
$statusBadgePay = "<span class='badge $statusBadgeClass'>$statusBadgeText</span>";

$is_confirmed = $rowTask['is_confirmed']; // Assuming 'is_paid' is the column name in your database
$confirmationClass = ($is_confirmed == 0) ? 'bg-light' : 'bg-primary';
$confirmationText = ($is_confirmed == 0) ? 'Confirmed' : 'Unconfirmed';
$confirmation = "<span class='badge $confirmationClass'>$confirmationText</span>";

?>

    <title>Task #<?php  echo $taskId;?> Submission | iTasker</title>
<?php include "navi.php";?>

    <div class="card shadow-none border mb-3">
        <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(assets/img/illustrations/corner-6.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-header z-1">
            <div class="row flex-between-center gx-0">
                <div class="col-lg-auto d-flex align-items-center">
                    <h4 class="mb-0 text-primary fw-bold">Submit <span class="text-info fw-medium">Task</span></h4>
                </div>
                <div class="col-lg-auto pt-3 pt-lg-0">
                    <form class="row flex-lg-column flex-xxl-row gx-3 gy-2 align-items-center align-items-lg-start align-items-xxl-center">
                        <div class="col-auto">
                        </div>
                        <div class="col-md-auto position-relative">
                            <h6 class="mb-1 badge rounded-pill badge-subtle-info"><?php echo date("jS F Y"); ?> | <span id="timeDisplay"></span></h6>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Display Bootstrap Alerts -->
<?php
if (isset($_GET['message'])) {
    // Sanitize the message to remove any HTML tags
    $message = htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8');
    echo "
                <div class='alert alert-success border-0 d-flex align-items-center' role='alert'>
                    <div class='bg-success me-3 icon-item'><span class='fas fa-check-circle text-white fs-6'></span></div>
                        <p class='mb-0 flex-1'>$message</p>
                    <button class='btn-close' type='button' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
                <script>
            // Use JavaScript to hide the alert after 5 seconds
            setTimeout(function() {
                var alertElement = document.querySelector('.alert');
                if (alertElement) {
                    alertElement.classList.add('fade'); // Add Bootstrap's fade class
                    alertElement.addEventListener('transitionend', function() {
                        alertElement.remove();
                    });
                }
            }, 5000); // 5000 milliseconds = 5 seconds
        </script>
                ";
}
?>
<?php
if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']); // Clear the alert message
}
?>

    <div class="card mb-3">
        <div class="bg-holder bg-card" style="background-image:url(assets/img/icons/spot-illustrations/corner-5.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-body position-relative">
            <div class="row g-2 align-items-sm-center">
                <div class="col-auto"><img src="assets/img/icons/connect-circle.png" alt="" height="55" /></div>
                <div class="col">
                    <div class="row align-items-center">
                        <div class="col col-lg-8">
                            <h5 class="mb-sm-0 text-primary fs-7">Task ID: <span class="text-info fw-medium">#<?php  echo $taskId;?></span></h5>
                            <p class="fw-semi-bold fs-10"><span class="me-1">Posted</span><span class="text-info ms-2"><?php  echo date("d M Y, g:i A", strtotime($taskCreatedOn));?></span>
                            </p>
                            <div class="fs-9 mb-3 mb-sm-0 text-primary"><strong class="me-2">Status: </strong><?php  echo $statusBadge;?>
                                <?php if ($is_confirmed == 1): ?>
                                    <?php echo $confirmation;?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden mb-3" data-bs-theme="light">
        <div class="card-body bg-black">
            <div class="bg-holder rounded-3" style="background-image:url(assets/img/illustrations/corner-3.png);">
            </div>
            <!--/.bg-holder-->

            <div class="row">
                <div class="card-body position-relative">
                    <div class="row g-3 align-items-center">
                        <div class="col">
                            <div class="row align-items-center">
                                <div class="col col-sm-12">
                                    <h6 class="fw-semi-bold text-400 fs-9"><span class="fas fa-book text-white me-1"> </span><?php  echo $taskSubject;?></h6>
                                    <h2 class="fw-bold text-white"><?php  echo $taskTopic;?> </h2>
                                    <p class="text-white fw-semi-bold fs-10"><span class="me-1 fs-9">Due</span><span class="text-info ms-2 fs-10"><?php  echo date("d M Y, g:i A", strtotime($taskDueDate));?></span>
                                    </p>
                                    <?php
                                    $due_date = new DateTime($rowTask['due_date']);
                                    $currentDateTime = new DateTime();
                                    $isLate = ($due_date < $currentDateTime);

                                    $remainingSeconds = $isLate ? 0 : $due_date->getTimestamp() - $currentDateTime->getTimestamp();

                                    if ($isLate) {
                                        $timeDiff = "<span id='time-remaining' style='color: red; font-weight: bold;'>Past Due</span>";
                                    } else {
                                        $timeDiff = "<span id='time-remaining' class='fw-bold text-green fs-8'></span>";
                                    }
                                    ?>
                                    <?php if ($taskStatus !='Completed'): ?>
                                        <p class="text-danger fs-9 fw-semi-bold"><span class="far fa-clock text-white me-1"></span><?php echo $timeDiff; ?></p>
                                    <?php elseif ($taskIsPaid = 1): ?>
                                        <?php echo $statusBadgePay; ?>
                                        <?php if ($is_paid == 1):
                                            $paidOn = $rowTask['paid_on'];
                                            $paidDate = date("d M Y, g:i A", strtotime($paidOn));
                                            ?> <span class="text-info ms-2 fs-10"><?php echo $paidDate; ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php $totalCost = $taskPages * $taskCPP;  ?>
                                    <h5 class="fs-9 mt-3 text-white">Ksh. <?php  echo $totalCost;?> </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="text-secondary text-opacity-50" />
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                        <span class="badge rounded-pill badge-subtle-dark border border-300 text-info py-2 px-3">
                            <span class="fas fa-user text-white me-1"> </span><?php  echo $taskWriter;?></span>
                        <span class="badge rounded-pill badge-subtle-dark border border-300 text-info py-2 px-3">
                            <span class="fas fa-file text-white me-1"> </span><?php  echo $taskPages;?> Pages</span>
                        <span class="badge rounded-pill badge-subtle-dark border border-300 text-info py-2 px-3">
                            <span class="fas fa-credit-card text-white me-1"> </span>Ksh. <?php  echo $taskCPP;?> CPP</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row ">
        <div class="col-lg-12 order-1 order-lg-0">
            <div class="card mb-3">
                <div class="card-header bg-body-tertiary">
                    <h5 class="mb-0">Description</h5>
                </div>
                <div class="card-body position-relative">
                    <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(assets/img/icons/spot-illustrations/corner-1.png);">
                    </div>
                    <!--/.bg-holder-->

                    <ul class="list-unstyled position-relative fs-9 p-0 m-0">
                        <li class="mb-2">
                            <!--                            <div class="d-flex"><dd>--><?php //echo $taskDescription; ?><!--</dd></div>-->
                            <div class="d-flex">
                                <dd>
                                    <?php
                                    // Remove `\r\n` from the task description
                                    $taskDescription = str_replace("\r\n", "<br>", $taskDescription);
                                    echo html_entity_decode($taskDescription) ?>
                                </dd>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="col mb-3">
        <div class="row g-3">
            <div class="col-xxl-12">
                <div class="card h-100 h-xxl-auto mt-xxl-3">
                    <div class="card-header d-flex flex-between-center bg-body-tertiary py-2">
                        <h6 class="mb-0">Task Files</h6><!--<a class="py-1 fs-10 font-sans-serif" href="#!">View All</a>-->
                    </div>
                    <div class="card-body position-relative">
                        <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(assets/img/icons/spot-illustrations/corner-2.png);">
                        </div>
                        <!--/.bg-holder-->
                        <?php
                        // Display Task Files section
                        if (!empty($existingFiles)) {
                            // Assuming $submittedFiles contains comma-separated file paths
                            $filePaths = explode(',', $existingFiles);
                            foreach ($filePaths as $filePath) {
                                $fileName = basename($filePath); // Extracts the filename from the path
                                $fileUrl = "taskfiles/" . $filePath; // Constructs the full URL to the file
                                $formattedDate = date("d M Y, g:i A", strtotime($taskCreatedOn)); // Format 'submitted_on' date
                                $taskfileSize = formatSizeUnits(filesize("taskfiles/" . $filePath)); // Get file size
                                // Adjust the image path as necessary
                                $thumbnailPath = "assets/img/icons/docs.png"; // Placeholder path for the thumbnail
                                ?>
                                <div class="d-flex mb-3 hover-actions-trigger align-items-center">
                                    <div class="file-thumbnail"><img class="border h-100 w-100 object-fit-cover rounded-2" src="<?php echo $thumbnailPath; ?>" alt="" /></div>
                                    <div class="ms-3 flex-shrink-1 flex-grow-1">
                                        <h6 class="mb-1"><a class="stretched-link text-900 fw-semi-bold" href="<?php echo $fileUrl; ?>" target="_blank"><?php echo $fileName; ?></a></h6>
                                        <div class="fs-10"><span class="fw-semi-bold"><?php echo $taskfileSize; ?></span><span class="fw-medium text-600 ms-2"><?php echo $formattedDate; ?></span></div>
                                        <!-- Add or adjust action buttons as necessary -->
                                        <div class="hover-actions end-0 top-50 translate-middle-y">
                                            <a class="btn btn-tertiary border-300 btn-sm me-1 text-600" data-bs-toggle="tooltip" data-bs-placement="top" title="Download" href="<?php echo $fileUrl; ?>" download="<?php echo $fileName; ?>"><img src="assets/img/icons/cloud-download.svg" alt="" width="15" /></a>
                                            <!-- Edit button or other actions -->
                                        </div>
                                    </div>
                                </div>
                                <hr class="text-200" />
                                <?php
                            }
                        } else {
                            echo '<div>No files attached.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col mb-3">
        <div class="row g-3">
            <div class="col-xxl-12">
                <div class="card h-100 h-xxl-auto mt-xxl-3" >
                    <div class="card-header d-flex flex-between-center bg-body-tertiary py-2">
                        <h6 class="mb-0">Submitted Files</h6><!--<a class="py-1 fs-10 font-sans-serif" href="#!">View All</a>-->
                    </div>
                    <div class="card-body position-relative">
                        <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(assets/img/icons/spot-illustrations/corner-7.png);">
                        </div>
                        <!--/.bg-holder-->
                        <?php
                        // Display Task Files section
                        if (!empty($submittedFiles)) {
                            // Assuming $submittedFiles contains comma-separated file paths
                            $filePaths = explode(',', $submittedFiles);
                            foreach ($filePaths as $filePath) {
                                $fileName = basename($filePath); // Extracts the filename from the path
                                $fileUrl = "taskfiles/" . $filePath; // Constructs the full URL to the file
                                $formattedDate = date("d M Y, g:i A", strtotime($submittedOn)); // Format 'submitted_on' date
                                $fileSize = formatSizeUnits(filesize("taskfiles/" . $filePath)); // Get file size
                                // Adjust the image path as necessary
                                $thumbnailPath = "assets/img/icons/docs.png"; // Placeholder path for the thumbnail
                                ?>
                                <div class="d-flex mb-3 hover-actions-trigger align-items-center">
                                    <div class="file-thumbnail"><img class="border h-100 w-100 object-fit-cover rounded-2" src="<?php echo $thumbnailPath; ?>" alt="" /></div>
                                    <div class="ms-3 flex-shrink-1 flex-grow-1">
                                        <h6 class="mb-1"><a class="stretched-link text-900 fw-semi-bold" href="<?php echo $fileUrl; ?>" target="_blank"><?php echo $fileName; ?></a></h6>
                                        <div class="fs-10"><span class="fw-semi-bold"><?php echo $fileSize; ?></span><span class="fw-medium text-600 ms-2"><?php echo $formattedDate; ?></span></div>
                                        <!-- Add or adjust action buttons as necessary -->
                                        <div class="hover-actions end-0 top-50 translate-middle-y">
                                            <a class="btn btn-tertiary border-300 btn-sm me-1 text-600" data-bs-toggle="tooltip" data-bs-placement="top" title="Download" href="<?php echo $fileUrl; ?>" download="<?php echo $fileName; ?>"><img src="assets/img/icons/cloud-download.svg" alt="" width="15" /></a>
                                            <!-- Edit button or other actions -->
                                        </div>
                                    </div>
                                </div>
                                <hr class="text-200" />
                                <?php
                            }
                        } else {
                            echo '<div>No files attached.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="alertPlaceholder"></div>
    <form class="needs-validation" novalidate="novalidate" id="taskForm" method="post" action="submission_upload" enctype="multipart/form-data">
        <div class="card mb-3" id="filesSubmission">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0">Submit file(s)</h6>
            </div>
            <div class="card-body">
                <div id="dropArea" class="drop-area">
                    <p>Drag and drop your files here or click to select files</p>
                    <input name="taskfiles[]" id="fileInput" type="file" multiple="multiple" accept="*/*" style="display: none;"/>
                    <button class="btn btn-outline-info me-1 mb-1" type="button" onclick="document.getElementById('fileInput').click()">Select Files</button>
                </div>
                <div id="fileList" class="file-list btn-outline-info">
                    <ul id="fileNamesList"></ul>
                </div>
                <input type="hidden" name="uploadedFiles" id="uploadedFiles" value="">
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <div class="row justify-content-between align-items-center">
                    <div class="col-md">
                        <h5 class="mb-2 mb-md-0" id="statusText">You're almost done!</h5>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-link text-secondary p-0 me-3 fw-medium" type="button" id="discardButton" role="button">Discard</button>
                        <button class="btn btn-primary d-none" name="save" type="submit" role="button" id="submitTaskButton">
                            <span id="buttonText">Submit Task</span>
                            <span id="loadingSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div id="fireworks-container" style="position:fixed;top:0;left:0;width:100vw;height:100vh;pointer-events:none;z-index:9999;display:none;"></div>
        <!-- Hidden fields for additional required data -->
        <input type="hidden" name="taskId" value="<?php  echo $taskId;?>">
        <input type="hidden" name="topic" value="<?php  echo $taskTopic;?>">
        <input type="hidden" name="due" value="<?php  echo $taskDueDate;?>">
        <input type="hidden" name="account" value="<?php  echo $taskAccount;?>">
        <input type="hidden" name="email" value="<?php  echo $taskWriterEmail;?>">
        <input type="hidden" name="sendEmail" value="1">

    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Element references
            const dropArea = document.getElementById('dropArea');
            const form = document.getElementById('taskForm');
            const fileInput = document.getElementById('fileInput');
            const submitTaskButton = document.getElementById('submitTaskButton');
            const buttonText = document.getElementById('buttonText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const alertPlaceholder = document.getElementById('alertPlaceholder');
            const discardButton = document.getElementById('discardButton');
            const fileContainer = document.querySelector('.card-body');
            const statusText = document.getElementById('statusText');

            let uploadedFilePaths = []; // Store paths of successfully uploaded files

            // Initially hide submit button
            submitTaskButton.classList.add('d-none');

            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            // Drag and drop handlers
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });

            // Event Listeners
            dropArea.addEventListener('drop', handleDrop, false);
            fileInput.addEventListener('change', function(e) {
                handleFiles(e.target.files);
            });

            discardButton.addEventListener('click', function() {
                form.reset();
                uploadedFilePaths = [];
                document.getElementById('fileNamesList').innerHTML = '';
                updateUploadedFilesInput();
                toggleSubmitButton();
                window.scrollTo(0, 0);
            });

            // File container click handler for delete buttons
            fileContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('delete-btn')) {
                    e.preventDefault();
                    const filePath = e.target.getAttribute('data-file-path');
                    deleteFile(filePath, e.target.closest('.d-flex'));
                }
            });

            // Form submission handler
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!form.checkValidity() || uploadedFilePaths.length === 0) {
                    displayBootstrapAlert('Please fill in all required fields and upload at least one file.', 'danger');
                    return;
                }

                buttonText.classList.add('d-none');
                loadingSpinner.classList.remove('d-none');
                submitTaskButton.disabled = true;
                statusText.textContent = 'Submitting...';

                document.getElementById('uploadedFiles').value = JSON.stringify(uploadedFilePaths);
                handleSubmit();
            });

            // Utility Functions
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            function highlight(e) {
                dropArea.classList.add('highlight');
            }

            function unhighlight(e) {
                dropArea.classList.remove('highlight');
            }

            function handleDrop(e) {
                handleFiles(e.dataTransfer.files);
            }

            function toggleSubmitButton() {
                if (uploadedFilePaths.length > 0) {
                    submitTaskButton.classList.remove('d-none');
                } else {
                    submitTaskButton.classList.add('d-none');
                }
            }

            function handleFiles(files) {
                [...files].forEach(file => uploadFile(file));
            }

            async function uploadFile(file) {
                const url = 'upload_update';
                const formData = new FormData();
                formData.append('file', file);
                formData.append('action', 'upload');

                const li = document.createElement('li');
                li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB) - Uploading: 0%`;
                li.style.color = '#FFA500';

                const progressBar = document.createElement('progress');
                progressBar.value = 0;
                progressBar.max = 100;
                li.appendChild(progressBar);

                const removeBtn = document.createElement('button');
                removeBtn.textContent = 'Remove';
                removeBtn.classList.add('btn', 'btn-outline-warning', 'btn-sm', 'ms-2');
                removeBtn.onclick = function() {
                    li.parentNode.removeChild(li);
                    const index = uploadedFilePaths.findIndex(f => f.fileName === file.name);
                    if (index > -1) {
                        deleteFileFromServer(uploadedFilePaths[index].filePath);
                        uploadedFilePaths.splice(index, 1);
                        updateUploadedFilesInput();
                        toggleSubmitButton();
                    }
                };

                li.appendChild(removeBtn);
                document.getElementById('fileNamesList').appendChild(li);

                try {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', url, true);

                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            progressBar.value = percentComplete;
                            li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB) - Uploading: ${percentComplete.toFixed(2)}%`;
                            li.appendChild(progressBar);
                            li.appendChild(removeBtn);
                        }
                    });

                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === 'success') {
                                li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB) - Upload complete!`;
                                li.style.color = 'green';
                                li.appendChild(removeBtn);
                                uploadedFilePaths.push({ fileName: file.name, filePath: response.filePath });
                                updateUploadedFilesInput();
                                toggleSubmitButton();
                            } else {
                                li.textContent = `${file.name} - Upload failed: ${response.message}`;
                                li.style.color = 'red';
                            }
                        } else {
                            li.textContent = `${file.name} - Upload error.`;
                            li.style.color = 'red';
                        }
                    };

                    xhr.send(formData);
                } catch (error) {
                    console.error('Error:', error);
                    li.textContent = `${file.name} - Upload error.`;
                    li.style.color = 'red';
                }
            }

            async function deleteFileFromServer(filePath) {
                const url = 'delete_file';
                const formData = new FormData();
                formData.append('filePath', filePath);
                formData.append('action', 'deleteFile');

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData,
                    });
                    const data = await response.json();
                    if (data.status !== 'success') {
                        console.error('Failed to delete file: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }

            function deleteFile(filePath, elementToRemove) {
                if (confirm('Are you sure you want to delete this file?')) {
                    fetch('delete-file', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'filePath=' + encodeURIComponent(filePath)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                elementToRemove.remove();
                            } else {
                                alert('Failed to delete the file. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred. Please try again.');
                        });
                }
            }
            function showFireworks() {
                const container = document.getElementById('fireworks-container');
                container.style.display = 'block';
                // Fireworks burst
                confetti({
                    particleCount: 150,
                    spread: 70,
                    origin: { y: 0.6 }
                });
                setTimeout(() => {
                    container.style.display = 'none';
                }, 500);
            }

            async function handleSubmit() {
                const formData = new FormData(form);
                formData.append('action', 'submitForm');

                try {
                    const response = await fetch('submission_upload', {
                        method: 'POST',
                        body: formData,
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.status === 'success') {
                            showFireworks();
                            setTimeout(() => {
                                const message = encodeURIComponent(data.message);
                                window.location.href = `view-task?task_id=${data.task_id}&message=${message}`;
                            }, 1500);
                        } else {
                            displayBootstrapAlert(`Failed to update the form: ${data.message}`, 'danger');
                        }
                    } else {
                        console.error("Failed to submit form. HTTP status: " + response.status);
                        displayBootstrapAlert('Failed to update form. Please try again.', 'warning');
                    }
                } catch (error) {
                    console.error("Error during form submission:", error);
                    displayBootstrapAlert(`An error occurred while submitting the form: ${error.message}`, 'danger');
                }
            }

            function updateUploadedFilesInput() {
                document.getElementById('uploadedFiles').value = JSON.stringify(uploadedFilePaths);
            }

            function displayBootstrapAlert(message, type) {
                const alertContainer = document.getElementById('alert-container');
                const alertHTML = `
            <div class="alert alert-${type} border-0 d-flex align-items-center" role="alert">
                <p class="mb-0 flex-1">${message}</p>
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
                alertContainer.innerHTML = alertHTML;
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    </script>

<?php
include "footer.php";
?>