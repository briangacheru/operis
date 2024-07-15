<?php
include "header.php";

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
$taskTopic = $taskSubject = $taskAccount = $taskStatus = $taskConfirmation = $taskIsPaid = $taskDescription = $taskWriter = $taskDueDate  = $taskCPP = $taskDuplicate = $taskPages = $existingFiles = '';

// Retrieve the task data from the database
$sql2 = "SELECT * FROM tbltasks WHERE id='$taskId'";
$result = mysqli_query($con, $sql2);

if ($row = mysqli_fetch_array($result)) {
    $id = base64_encode($row["id"]);
    $taskTopic = $row["topic"];
    $taskSubject = $row["subject"];
    $taskAccount = $row["account"];
    $taskStatus = $row["status"];
    $taskConfirmation = $row["is_confirmed"];
    $taskIsPaid = $row["is_paid"];
    $taskDescription = $row["description"];
    $taskWriter = $row["writer"];
    $taskDueDate = $row["due_date"];
    $taskCPP = $row["cpp"];
    $taskPages = $row["pages"];
    $existingFiles = $row['task_files']; // Assuming this contains comma-separated file paths
    $taskCreatedOn = $row["create_date"];
    $taskDuplicate = $row["is_duplicate"];

}

?>

<div id="alert-container"></div>

<div class="card shadow-none border mb-3">
    <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(../assets/img/illustrations/corner-6.png);">
    </div>
    <!--/.bg-holder-->

    <div class="card-header z-1">
        <div class="row flex-between-center gx-0">
            <div class="col-lg-auto d-flex align-items-center">
                <h4 class="mb-0 text-primary fw-bold">Edit <span class="text-info fw-medium">Task #<?php  echo $taskId;?></span></h4>
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

<div class="card mb-3">
    <div class="card-header">
        <div class="row flex-between-end">
            <div class="col-auto align-self-center">
                <h5 class="mb-0" data-anchor="data-anchor">Task Details</h5>
            </div>
        </div>
    </div>
    <div class="card-body bg-body-tertiary">
        <div class="tab-content">
            <div class="tab-pane preview-tab-pane active" >
                <form class="needs-validation" novalidate="novalidate" id="taskForm" method="post" action="update-task.php" enctype="multipart/form-data">
                    <div class="card mb-3">
                        <div class="card-header bg-body-tertiary">
                            <h6 class="mb-0">Basic information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row gx-2">
                                <div class="col-12 mb-3">
                                    <label class="form-label" for="manufacturar-name">Topic:</label>
                                    <input type="hidden" name="taskId" value="<?php echo htmlspecialchars($taskId); ?>">
                                    <input class="form-control" name="topic" type="text" value="<?php  echo $taskTopic;?>" required="required" />
                                    <div class="invalid-feedback">This field is required</div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label" for="import-status">Subject: </label>
                                    <input class="form-control" name="subject" type="text" value="<?php  echo $taskSubject;?>" required="required" />
                                    <div class="invalid-feedback">This field is required</div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label" for="origin-country">Account: </label>
                                    <input class="form-control" name="account" type="text" value="<?php  echo $taskAccount;?>" required="required" />
                                    <div class="invalid-feedback">This field is required</div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label" for="product-summary">Pages: </label>
                                    <input class="form-control"  type="number" name="pages" id="pages" min="0" step="0.01" value="<?php  echo $taskPages;?>" required="required"/>
                                    <div class="invalid-feedback">This field is required</div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label" for="cpp">CPP: </label>
                                    <select class="form-select" id="cpp" name="cpp" required="required">
                                        <option value="375" <?php echo ($taskCPP == '375') ? 'selected' : ''; ?>>375</option>
                                        <option value="350" <?php echo ($taskCPP == '350') ? 'selected' : ''; ?>>350</option>
                                        <option value="200" <?php echo ($taskCPP == '200') ? 'selected' : ''; ?>>200</option>
                                        <option value="400" <?php echo ($taskCPP == '400') ? 'selected' : ''; ?>>400</option>
                                    </select>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label" for="dateTimepickerVal">Due Date & Time:</label>
                                    <input class="form-control datetimepicker" name="due_date" type="text" required="required"  value="<?php echo htmlspecialchars($taskDueDate, ENT_QUOTES); ?>" placeholder="YYYY-mm.dd H:i" data-options='{"enableTime":true,"dateFormat":"Y-m-d H:i","disableMobile":true,"allowInput":true, "minDate": "today"}' />
                                    <div class="invalid-feedback">This field is required</div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label" for="cpp">Confirmed: </label>
                                    <select class="form-select" id="is_confirmed" name="is_confirmed" required="required">
                                        <option value="0" <?php echo ($taskConfirmation == '0') ? 'selected' : ''; ?>>Yes</option>
                                        <option value="1" <?php echo ($taskConfirmation == '1') ? 'selected' : ''; ?>>No</option>
                                    </select>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label" for="cpp">Select writer: </label>
                                    <select class="form-select" name="writer" id="writerSelect" required="required">
                                        <option disabled value="">Select Writer</option>
                                        <?php
                                        // Assuming $con is your database connection
                                        $query = mysqli_query($con, "SELECT id, username, email FROM tblwriters WHERE is_deleted = 0");
                                        while ($writer = mysqli_fetch_assoc($query)) {
                                            // Check if this writer is the current selection
                                            $selected = ($writer['username'] == $taskWriter) ? 'selected' : '';
                                            echo "<option value=\"{$writer['username']}\" data-email=\"{$writer['email']}\" {$selected}>{$writer['username']}</option>";                                        }
                                        ?>
                                    </select>
                                    <div id="writerError" class="invalid-feedback">Please select a writer.</div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label" for="product-summary">Writer email: </label>
                                    <input class="form-control" type="email" name="email" value="" id="email" required="required"  readonly/>
                                    <div class="invalid-feedback">This field is required</div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label" for="product-summary">Status:</label>
                                    <select class="form-select" id="status" name="status" required="required">
                                        <option value="Draft" <?php echo ($taskStatus == 'Draft') ? 'selected' : ''; ?>>Draft</option>
                                        <option value="In Progress" <?php echo ($taskStatus == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="Submitted" <?php echo ($taskStatus == 'Submitted') ? 'selected' : ''; ?>>Submitted</option>
                                    </select>
                                    <div class="invalid-feedback">This field is required</div>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label" for="cpp">Send email</label>
                                    <select class="form-select" id="sendEmail" name="sendEmail" required="required">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header bg-body-tertiary">
                            <h6 class="mb-0">Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row gx-2">
                                <div class="col-12 mb-3">
                                    <label class="form-label" for="task-description">Task description:</label>
                                    <div class="create-product-description-textarea">
                                        <textarea class="tinymce d-none" data-tinymce="data-tinymce" name="description" id="description" required="required"><?php echo htmlspecialchars($taskDescription); ?></textarea>
                                        <div class="invalid-feedback">This field is required</div>
                                    </div>
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
                                        <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(../assets/img/icons/spot-illustrations/corner-2.png);">
                                        </div>
                                        <!--/.bg-holder-->
                                        <?php
                                        // Display Task Files section
                                        if (!empty($existingFiles)) {
                                            // Assuming $submittedFiles contains comma-separated file paths
                                            $filePaths = explode(',', $existingFiles);
                                            foreach ($filePaths as $filePath) {
                                                $fileName = basename($filePath); // Extracts the filename from the path
                                                $fileUrl = "../taskfiles/" . $filePath; // Constructs the full URL to the file
                                                $formattedDate = date("d M Y, g:i A", strtotime($taskCreatedOn)); // Format 'submitted_on' date
                                                $fileSize = formatSizeUnits(filesize("../taskfiles/" . $filePath)); // Get file size
                                                // Adjust the image path as necessary
                                                $thumbnailPath = "../assets/img/icons/docs.png"; // Placeholder path for the thumbnail
                                                ?>
                                                <div class="d-flex mb-3 hover-actions-trigger align-items-center">
                                                    <div class="file-thumbnail"><img class="border h-100 w-100 object-fit-cover rounded-2" src="<?php echo $thumbnailPath; ?>" alt="" /></div>
                                                    <div class="ms-3 flex-shrink-1 flex-grow-1">
                                                        <h6 class="mb-1"><a class="stretched-link text-900 fw-semi-bold" href="<?php echo $fileUrl; ?>" target="_blank"><?php echo $fileName; ?></a></h6>
                                                        <div class="fs-10"><span class="fw-semi-bold"><?php echo $fileSize; ?></span><span class="fw-medium text-600 ms-2"><?php echo $formattedDate; ?></span></div>
                                                        <input type="hidden" name="existingFiles[]" value="<?php echo htmlspecialchars($filePath); ?>">
                                                        <!-- Add or adjust action buttons as necessary -->
                                                        <div class="hover-actions end-0 top-50 translate-middle-y">
                                                            <a class="btn btn-tertiary border-300 btn-sm me-1 text-600" data-bs-toggle="tooltip" data-bs-placement="top" title="Download" href="<?php echo $fileUrl; ?>" download="<?php echo $fileName; ?>"><img src="../assets/img/icons/cloud-download.svg" alt="" width="15" /></a>
                                                            <button class="btn btn-tertiary border-300 btn-sm me-1 text-600 shadow-none delete-btn" type="button" data-file-path="<?php echo htmlspecialchars($filePath); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove"><img src="../assets/img/icons/delete.svg" alt="" width="15" /></button>
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
                    <div class="card mb-3">
                        <div class="card-header bg-body-tertiary">
                            <h6 class="mb-0">Add task file(s)</h6>
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
                                    <h5 class="mb-2 mb-md-0">You're almost done!</h5>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-link text-secondary p-0 me-3 fw-medium" type="button" id="discardButton" role="button">Discard</button>
                                    <button class="btn btn-primary" name="save" type="submit" role="button" id="updateTaskButton">
                                        <span id="buttonText">Update Task</span>
                                        <span id="loadingSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // When the DOM is fully loaded
        const writerSelect = document.getElementById('writerSelect'); // Get the select element
        const emailInput = document.getElementById('email'); // Get the email input field

        writerSelect.addEventListener('change', function() {
            // When the writer selection changes
            const selectedOption = writerSelect.options[writerSelect.selectedIndex]; // Get the selected option
            const email = selectedOption.getAttribute('data-email'); // Get the data-email attribute
            emailInput.value = email; // Update the email input field
        });

        // Trigger the change event on page load if a writer is selected (for edit scenarios)
        if (writerSelect.selectedIndex > 0) {
            writerSelect.dispatchEvent(new Event('change'));
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        const discardButton = document.getElementById('discardButton');
        const form = document.getElementById('taskForm');

        discardButton.addEventListener('click', function() {
            form.reset();
            // Optionally, scroll to the top if you want to reset the view as well
            window.scrollTo(0, 0);
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('taskForm'); // Ensure you have the correct form ID
        const writerSelect = document.getElementById('writerSelect');
        const writerError = document.getElementById('writerError');

        // Validate the writerSelect on form submit
        form.addEventListener('submit', function(e) {
            if (writerSelect.value === "") {
                e.preventDefault(); // Prevent form submission
                writerError.style.display = 'block'; // Show the error message
            } else {
                writerError.style.display = 'none'; // Hide the error message if a writer is selected
            }
        });

        // Optionally: Hide the error message when a valid option is selected
        writerSelect.addEventListener('change', function() {
            if (writerSelect.value === "") {
                writerError.style.display = 'block';
            } else {
                writerError.style.display = 'none';
            }
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropArea = document.getElementById('dropArea');
        const form = document.getElementById('taskForm');
        let uploadedFilePaths = []; // To store paths of successfully uploaded files

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        // Highlight drop area when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        // Handle dropped files
        dropArea.addEventListener('drop', handleDrop, false);

        // Add change event listener to file input for direct file selection
        document.getElementById('fileInput').addEventListener('change', function(e) {
            handleFiles(e.target.files);
        });

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
            var dt = e.dataTransfer;
            var files = dt.files;

            handleFiles(files);
        }

        function handleFiles(files) {
            files = [...files]; // Convert files to an array
            files.forEach(file => uploadFile(file));
        }

        async function uploadFile(file) {
            const url = 'upload_update.php'; // Ensure this path is correct
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
            removeBtn.onclick = function () {
                li.parentNode.removeChild(li);
                const index = uploadedFilePaths.findIndex(f => f.fileName === file.name);
                if (index > -1) {
                    const filePath = uploadedFilePaths[index].filePath;
                    deleteFileFromServer(filePath);
                    uploadedFilePaths.splice(index, 1);
                    updateUploadedFilesInput();
                }
            };

            li.appendChild(removeBtn);
            document.getElementById('fileNamesList').appendChild(li);

            try {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', url, true);

                xhr.upload.addEventListener('progress', function (e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressBar.value = percentComplete;
                        li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB) - Uploading: ${percentComplete.toFixed(2)}%`;
                        li.appendChild(progressBar);
                        li.appendChild(removeBtn);
                    }
                });

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            const filePath = response.filePath;
                            li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB) - Upload complete!`;
                            li.style.color = 'green';
                            li.appendChild(removeBtn);
                            uploadedFilePaths.push({ fileName: file.name, filePath: filePath });
                            updateUploadedFilesInput();
                        } else {
                            li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB) - Upload failed: ${response.message}`;
                            li.style.color = 'red';
                        }
                    } else {
                        li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB) - Upload error.`;
                        li.style.color = 'red';
                    }
                };

                xhr.send(formData);
            } catch (error) {
                console.error('Error:', error);
                li.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB) - Upload error.`;
                li.style.color = 'red';
            }
        }


        async function deleteFileFromServer(filePath) {
            const url = 'delete_file.php'; // URL to the PHP file handling deletions
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
                } else {
                    console.log('File deleted successfully');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function updateUploadedFilesInput() {
            document.getElementById('uploadedFiles').value = JSON.stringify(uploadedFilePaths); // Update hidden input value
        }

        form.addEventListener('submit', async function(e) {
            e.preventDefault(); // Prevent the default form submission
            // Example validation check
            if (!form.checkValidity()) {
                // Display an error message or highlight the invalid fields
                displayBootstrapAlert('Please fill in all required fields.', 'danger');
                return; // Stop the function if validation fails
            }
            document.getElementById('uploadedFiles').value = JSON.stringify(uploadedFilePaths); // Set hidden input value
            handleSubmit();
        });

        async function handleSubmit() {
            const formData = new FormData(form);
            formData.append('action', 'submitForm'); // Append the action field here

            try {
                const response = await fetch('update-task.php', {
                    method: 'POST',
                    body: formData,
                });

                if (response.ok) {
                    const data = await response.json(); // Assuming the response from your PHP script is JSON
                    if (data.status === 'success') {
                        // Display a success alert
                        //displayBootstrapAlert('Task created successfully.', 'success');
                        // Optionally, redirect or clear the form here
                        //window.location.href = `view-task.php?task_id=${data.task_id}`;
                        const message = encodeURIComponent(data.message);
                        window.location.href = `view-task.php?task_id=${data.task_id}&message=${message}`;
                    } else if (data.status === 'error') {
                        // Display an error alert with the message from the PHP script
                        displayBootstrapAlert(`Failed to update the form: ${data.message}`, 'danger');
                    }
                } else {
                    // The HTTP request failed for some reason
                    console.error("Failed to submit form. HTTP status: " + response.status);
                    displayBootstrapAlert('Failed to update form. Please try again.', 'warning');
                }
            } catch (error) {
                console.error("Error during form submission:", error);
                displayBootstrapAlert(`An error occurred while submitting the form: ${error.message}`, 'danger');
            }
        }

        function displayBootstrapAlert(message, type) {
            const alertContainer = document.getElementById('alert-container');
            const alertHTML = `
        <div class="alert alert-${type} border-0 d-flex align-items-center" role="alert">
            <!--<div class="bg-success me-3 icon-item"><span class="fas fa-check-circle text-white fs-6"></span></div>-->
                <p class="mb-0 flex-1">${message}</p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
            alertContainer.innerHTML = alertHTML;
            // Scroll the alert container into view
            alertContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileContainer = document.querySelector('.card-body');

        fileContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-btn')) {
                e.preventDefault();
                const filePath = e.target.getAttribute('data-file-path');
                // Call the function to delete the file
                deleteFile(filePath, e.target.closest('.d-flex'));
            }
        });
    });
    function deleteFile(filePath, elementToRemove) {
        if (confirm('Are you sure you want to delete this file?')) {
            fetch('delete-file.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'filePath=' + encodeURIComponent(filePath)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // If the file was successfully deleted, remove the element
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
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const updateTaskButton = document.getElementById('updateTaskButton');
        const buttonText = document.getElementById('buttonText');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const taskForm = document.getElementById('taskForm');

        taskForm.addEventListener('submit', function() {
            // Show the spinner and hide the button text
            buttonText.classList.add('d-none');
            loadingSpinner.classList.remove('d-none');

            // Disable the button to prevent multiple submissions
            updateTaskButton.disabled = true;
        });
    });
</script>

<?php
include "footer.php";
?>

