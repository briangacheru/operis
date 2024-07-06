<?php
include "header.php";

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

if ($row = mysqli_fetch_array($result)) {
    $id = base64_encode($row["id"]);
    $taskTopic = $row["topic"];
    $taskSubject = $row["subject"];
    $taskAccount = $row["account"];
    $taskCreatedOn = $row["create_date"];
    $taskStatus = $row["status"];
    $taskIsPaid = $row["is_paid"];
    $taskDescription = $row["description"];
    $taskWriter = $row["writer"];
    $taskWriterEmail = $row["email"];
    $taskDueDate = $row["due_date"];
    $taskCPP = $row["cpp"];
    $taskPages = $row["pages"];
    $existingFiles = $row['task_files']; // Assuming this contains comma-separated file paths
    $submittedFiles = $row['submitted_files'];
    $taskSubmitTime = $row['submitted_on'];
    $submittedOn = $row['submitted_on'];
}

// Determine badge based on task status
$statusBadge = '';
switch ($row["status"]) {
    case 'In Progress':
        $statusBadge = '<div class="badge rounded-pill badge-subtle-warning fs-11">In progress<span class="fas fa-stream ms-1" data-fa-transform="shrink-2"></span></div>';
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
// Correctly retrieve is_paid status from the row
$is_paid = $row['is_paid']; // Assuming 'is_paid' is the column name in your database

// Determine badge based on payment status
$statusBadgeClass = ($is_paid == 1) ? 'bg-success' : 'bg-black';
$statusBadgeText = ($is_paid == 1) ? 'Paid' : 'Unpaid';
$statusBadgePay = "<span class='badge $statusBadgeClass'>$statusBadgeText</span>";

$is_confirmed = $row['is_confirmed']; // Assuming 'is_paid' is the column name in your database
$confirmationClass = ($is_confirmed == 0) ? 'bg-light' : 'bg-primary';
$confirmationText = ($is_confirmed == 0) ? 'Confirmed' : 'Unconfirmed';
$confirmation = "<span class='badge $confirmationClass'>$confirmationText</span>";

?>

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
                            <h6 class="mb-1 text-primary"></h6>
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
                                    $due_date = new DateTime($row['due_date']);
                                    $currentDateTime = new DateTime(); // Assuming you've already got this
                                    $interval = $currentDateTime->diff($due_date);
                                    $isLate = ($due_date < $currentDateTime) ? true : false;

                                    // Calculate total hours and minutes
                                    $totalHours = ($interval->days * 24) + $interval->h;
                                    $totalMinutes = $interval->i;

                                    // Format the difference as a string, and choose color based on whether it's late
                                    if ($isLate) {
                                        $timeDiff = "<span style='color: red; font-weight: bold;'> Past Due by: $totalHours hrs $totalMinutes min </span>";
                                    } else {
                                        $timeDiff = "<span style='color: green; font-weight: bold;'>Time Remaining: $totalHours hrs $totalMinutes min </span>";
                                    }
                                    ?>
                                    <?php if ($taskStatus !='Completed'): ?>
                                        <p class="text-danger fs-9 fw-semi-bold"><span class="far fa-clock text-white me-1"></span><?php echo $timeDiff; ?></p>
                                    <?php elseif ($taskIsPaid = 1): ?>
                                        <?php echo $statusBadgePay; ?>
                                        <?php if ($is_paid == 1):
                                            $paidOn = $row['paid_on'];
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
                    <ul class="list-unstyled d-flex flex-wrap gap-3 fs-9 fw-semi-bold text-300 mt-3 mb-0">
                        <li><span class="fas fa-user-graduate text-white me-1"> </span><?php  echo $taskWriter;?></li>
                        <li><span class="fas fa-user text-white me-1"> </span><?php  echo $taskAccount;?></li>
                        <li><span class="fas fa-file text-white me-1"> </span><?php  echo $taskPages;?> Pages</li>
                        <li><span class="fas fa-credit-card text-white me-1"> </span>Ksh. <?php  echo $taskCPP;?> Per page</li>
                    </ul>
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
                                // Adjust the image path as necessary
                                $thumbnailPath = "assets/img/icons/docs.png"; // Placeholder path for the thumbnail
                                ?>
                                <div class="d-flex mb-3 hover-actions-trigger align-items-center">
                                    <div class="file-thumbnail"><img class="border h-100 w-100 object-fit-cover rounded-2" src="<?php echo $thumbnailPath; ?>" alt="" /></div>
                                    <div class="ms-3 flex-shrink-1 flex-grow-1">
                                        <h6 class="mb-1"><a class="stretched-link text-900 fw-semi-bold" href="<?php echo $fileUrl; ?>" target="_blank"><?php echo $fileName; ?></a></h6>
                                        <div class="fs-10"><span class="fw-semi-bold">Uploaded on</span><span class="fw-medium text-600 ms-2"><?php echo $formattedDate; ?></span></div>
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
                                // Adjust the image path as necessary
                                $thumbnailPath = "assets/img/icons/docs.png"; // Placeholder path for the thumbnail
                                ?>
                                <div class="d-flex mb-3 hover-actions-trigger align-items-center">
                                    <div class="file-thumbnail"><img class="border h-100 w-100 object-fit-cover rounded-2" src="<?php echo $thumbnailPath; ?>" alt="" /></div>
                                    <div class="ms-3 flex-shrink-1 flex-grow-1">
                                        <h6 class="mb-1"><a class="stretched-link text-900 fw-semi-bold" href="<?php echo $fileUrl; ?>" target="_blank"><?php echo $fileName; ?></a></h6>
                                        <div class="fs-10"><span class="fw-semi-bold">Submitted on</span><span class="fw-medium text-600 ms-2"><?php echo $formattedDate; ?></span></div>
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

    <form class="needs-validation" novalidate="novalidate" id="taskForm" method="post" action="submission_upload.php?task_id=<?php echo $encodedId; ?>" enctype="multipart/form-data">
        <div class="card mb-3" id="filesSubmission">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0">Submit file(s)</h6>
            </div>
            <div class="card-body">
                <div id="dropArea" class="drop-area">
                    <p>Drag and drop your files here or click to select files</p>
                    <input name="taskfiles[]" id="fileInput" type="file" multiple="multiple" accept="*/*" style="display: none;" />
                    <button class="btn btn-outline-info me-1 mb-1" type="button" id="selectFilesButton">Select Files</button>
                </div>
                <div id="fileList" class="file-list btn-outline-info">
                    <ul id="fileNamesList"></ul>
                </div>
                <div id="progressContainer"></div>
                <input type="hidden" name="uploadedFiles" id="uploadedFiles" value="">
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <div class="row justify-content-between align-items-center">
                    <div class="col-md">
                        <h5 class="mb-2 mb-md-0">You're almost done!</h5>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-link text-secondary p-0 me-3 fw-medium" type="button" id="discardButton" role="button">Discard</button>
                        <button class="btn btn-primary" name="save" type="submit" role="button" id="submitTaskButton">
                            <span id="buttonText">Submit Task</span>
                            <span id="loadingSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash === '#filesSubmission') {
                document.querySelector('#filesSubmission').scrollIntoView({ behavior: 'smooth' });
            }
            const dropArea = document.getElementById('dropArea');
            const fileInput = document.getElementById('fileInput');
            const fileNamesList = document.getElementById('fileNamesList');
            const selectFilesButton = document.getElementById('selectFilesButton');
            const progressContainer = document.getElementById('progressContainer');
            const submitButton = document.getElementById('submitTaskButton');
            const buttonText = document.getElementById('buttonText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            let uploadedFiles = [];

            // Handle click event to open file dialog
            selectFilesButton.addEventListener('click', function() {
                fileInput.click();
            });

            // Handle file input change event
            fileInput.addEventListener('change', handleFiles);

            // Handle drag over event
            dropArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropArea.classList.add('dragover');
            });

            // Handle drag leave event
            dropArea.addEventListener('dragleave', function() {
                dropArea.classList.remove('dragover');
            });

            // Handle drop event
            dropArea.addEventListener('drop', function(e) {
                e.preventDefault();
                dropArea.classList.remove('dragover');
                const newFiles = e.dataTransfer.files;
                addFiles(newFiles);
            });

            function handleFiles() {
                const newFiles = fileInput.files;
                addFiles(newFiles);
            }

            function addFiles(newFiles) {
                for (const file of newFiles) {
                    const listItem = document.createElement('li');
                    listItem.textContent = file.name;
                    const deleteButton = document.createElement('button');
                    deleteButton.textContent = 'Delete';
                    deleteButton.classList.add('btn', 'btn-danger', 'btn-sm', 'ms-2');
                    deleteButton.addEventListener('click', function() {
                        listItem.remove();
                        // Remove the file from the uploadedFiles list
                        const index = uploadedFiles.indexOf(file);
                        if (index > -1) {
                            uploadedFiles.splice(index, 1);
                        }
                    });
                    listItem.appendChild(deleteButton);
                    fileNamesList.appendChild(listItem);
                    uploadedFiles.push(file);
                }
            }

            submitButton.addEventListener('click', function(e) {
                e.preventDefault();
                buttonText.classList.add('d-none');
                loadingSpinner.classList.remove('d-none');
                uploadFiles(uploadedFiles);
            });

            function uploadFiles(files) {
                progressContainer.innerHTML = '';
                const formData = new FormData();

                for (const file of files) {
                    formData.append('taskfiles[]', file);
                }

                const xhr = new XMLHttpRequest();
                let uploadSuccess = true;

                xhr.upload.addEventListener('progress', function(e) {
                    const percent = e.lengthComputable ? (e.loaded / e.total) * 100 : 0;
                    let progressBar = document.querySelector(`#progress-${file.name}`);
                    if (!progressBar) {
                        progressBar = document.createElement('div');
                        progressBar.classList.add('progress-bar');
                        progressBar.id = `progress-${file.name}`;
                        progressContainer.appendChild(progressBar);
                    }
                    progressBar.style.width = percent + '%';
                    progressBar.textContent = Math.floor(percent) + '%';
                });

                xhr.addEventListener('load', function() {
                    if (xhr.status === 200) {
                        for (const file of files) {
                            const messageContainer = document.createElement('div');
                            messageContainer.classList.add('text-success', 'mb-2');
                            messageContainer.innerHTML = `
                        <div>
                            <span class='fas fa-check-circle me-2'></span>${file.name} uploaded successfully!
                        </div>
                    `;
                            progressContainer.appendChild(messageContainer);
                        }
                        // Check if all files were uploaded successfully
                        if (uploadSuccess) {
                            // Redirect to view-task.php after a brief delay
                            setTimeout(function() {
                                window.location.href = 'view-task.php?task_id=<?php echo $encodedId; ?>&message=' + encodeURIComponent('Task submitted and emailed successfully!');
                            }, 15000); // 15-second delay for user to see the message
                        }
                    } else {
                        const messageContainer = document.createElement('div');
                        messageContainer.classList.add('text-danger', 'mb-2');
                        messageContainer.innerHTML = `
                    <div>
                        <span class='fas fa-exclamation-circle me-2'></span>Error uploading files!
                    </div>
                `;
                        progressContainer.appendChild(messageContainer);
                        uploadSuccess = false;
                        buttonText.classList.remove('d-none');
                        loadingSpinner.classList.add('d-none');
                    }
                });

                xhr.open('POST', 'submission_upload.php?task_id=<?php echo $encodedId; ?>', true);
                xhr.send(formData);
            }
        });
    </script>

<?php
include "footer.php";
?>