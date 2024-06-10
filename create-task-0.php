<?php
include "header.php";

?>

    <div id="alert-container"></div>

    <div class="card shadow-none border mb-3">
        <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(assets/img/illustrations/corner-6.png);">
        </div>
        <!--/.bg-holder-->

        <div class="card-header z-1">
            <div class="row flex-between-center gx-0">
                <div class="col-lg-auto d-flex align-items-center">
                    <h4 class="mb-0 text-primary fw-bold">Create <span class="text-info fw-medium">New Task</span></h4>
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
                    <form class="needs-validation" novalidate="novalidate" id="taskForm" method="post" action="submit-task.php" enctype="multipart/form-data">
                        <div class="card mb-3">
                            <div class="card-header bg-body-tertiary">
                                <h6 class="mb-0">Basic information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row gx-2">
                                    <div class="col-12 mb-3">
                                        <label class="form-label" for="manufacturar-name">Topic:</label>
                                        <input class="form-control" name="topic" type="text" required="required" />
                                        <div class="invalid-feedback">This field is required</div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="import-status">Subject: </label>
                                        <input class="form-control" name="subject" type="text" required="required" />
                                        <div class="invalid-feedback">This field is required</div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="origin-country">Account: </label>
                                        <input class="form-control" name="account" type="text" required="required" />
                                        <div class="invalid-feedback">This field is required</div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="product-summary">Pages: </label>
                                        <input class="form-control"  type="number" name="pages" id="pages" min="0" step="0.01" required="required"/>
                                        <div class="invalid-feedback">This field is required</div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="cpp">CPP: </label>
                                        <select class="form-select" id="cpp" name="cpp" required="required">
                                            <option value="375">375</option>
                                            <option value="350">350 </option>
                                            <option value="200">200 </option>
                                            <option value="400">400</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="dateTimepickerVal">Due Date & Time:</label>
                                        <input class="form-control datetimepicker" name="due_date" type="text" required="required" placeholder="YYYY-mm.dd H:i" data-options='{"enableTime":true,"dateFormat":"Y-m-d H:i","disableMobile":true,"allowInput":true, "minDate": "today"}' />
                                        <div class="invalid-feedback">This field is required</div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="cpp">Confirmed: </label>
                                        <select class="form-select" name="is_confirmed">
                                            <option selected value="0">Yes</option>
                                            <option value="1">No</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="cpp">Select writer: </label>
                                        <select class="form-select" name="writer" id="writerSelect" required="required">
                                            <option selected disabled value="">Select Writer</option>
                                            <?php
                                            // Assuming $con is your database connection
                                            $query = mysqli_query($con, "SELECT id, username, email FROM tblwriters WHERE is_deleted = 0");
                                            while ($row = mysqli_fetch_assoc($query)) {
                                                echo "<option value='" . $row['username'] . "|" . $row['email'] . "'>" . $row['username'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                        <div id="writerError" class="invalid-feedback">Please select a writer.</div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="product-summary">Writer email: </label>
                                        <input class="form-control" type="email" name="email" value="" id="email" required="required"  readonly/>
                                        <div class="invalid-feedback">This field is required</div>
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
                                            <textarea class="tinymce" data-tinymce="data-tinymce" name="description" id="description" required="required"></textarea>
<!--                                            <textarea name="description" id="summernote" class="summernote form-control" required="required"></textarea>-->
                                            <div class="invalid-feedback">This field is required</div>
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
                                    <input name="taskfiles" id="fileInput" type="file" multiple="multiple" accept="*/*" style="display: none;"/>
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
                                        <button class="btn btn-primary" name="save" type="submit" role="button">Create Task </button>
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
    tinymce.init({
        selector: '#description',  // change this value according to your HTML
        resize: 'both'
    });
</script>

    <script>
        document.getElementById('writerSelect').addEventListener('change', function() {
            var selectedOption = this.value.split('|'); // Split the value by the delimiter to get [name, email]
            if (selectedOption.length === 2) { // Make sure both name and email are present
                var email = selectedOption[1]; // Get the email part
                document.getElementById('email').value = email; // Update the email input field
            } else {
                document.getElementById('email').value = ''; // Clear the email input if not a valid selection
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
            const url = 'upload.php'; // URL to the PHP file handling uploads
            const formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'upload');

            const li = document.createElement('li');
            li.textContent = file.name + ' - Uploading...';
            li.style.color = 'yellow'; // Set text color to yellow during upload

            const removeBtn = document.createElement('button'); // Create a remove button
            removeBtn.textContent = 'Remove';
            removeBtn.classList.add('btn', 'btn-outline-warning', 'btn-sm', 'ms-2'); // Add some styling classes
            removeBtn.onclick = function() {
                // Function to remove the list item
                li.parentNode.removeChild(li);
                // Optionally, remove the filePath from uploadedFilePaths if needed
            };

            li.appendChild(removeBtn); // Append the button to the li element
            document.getElementById('fileNamesList').appendChild(li);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                });

                const data = await response.json();
                if (data.status === 'success') {
                    li.textContent = file.name + ' - Upload complete!';
                    li.style.color = 'green'; // Set text color to green on success
                    li.appendChild(removeBtn); // Ensure the remove button stays
                    uploadedFilePaths.push(data.filePath); // Store uploaded file path
                } else {
                    li.textContent = file.name + ' - Upload failed: ' + data.message;
                    li.style.color = 'red'; // Set text color to red on failure
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                li.textContent = file.name + ' - Upload error.';
                li.style.color = 'red'; // Set text color to red on failure
                throw error;
            }
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
                const response = await fetch('submit-task.php', {
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
                        displayBootstrapAlert(`Failed to submit the form: ${data.message}`, 'danger');
                    }
                } else {
                    // The HTTP request failed for some reason
                    console.error("Failed to submit form. HTTP status: " + response.status);
                    displayBootstrapAlert('Failed to submit form. Please try again.', 'warning');
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

            // Optionally, automatically remove the alert after a delay
            //setTimeout(() => {
              //  alertContainer.innerHTML = ''; // Clear the alert
           // }, 5000); // Alert will be cleared after 5 seconds
        }
    });
</script>

<?php
include "footer.php";
?>

