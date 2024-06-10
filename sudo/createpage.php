<?php
include "header.php";
include "sidebar.php";
include "nav.php";
?>

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
                    <form class="needs-validation" novalidate="novalidate">
                    <div class="card mb-3">
                        <div class="card-header bg-body-tertiary">
                            <h6 class="mb-0">Basic information</h6>
                        </div>
                        <div class="card-body">
                                <div class="row gx-2">
                                    <div class="col-12 mb-3">
                                        <label class="form-label" for="manufacturar-name">Topic:</label>
                                        <input class="form-control" id="manufacturar-name" type="text" required="required" />
                                        <div class="invalid-feedback">This field is required</div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="import-status">Subject: </label>
                                        <input class="form-control" id="manufacturar-name" type="text" required="required" />
                                        <div class="invalid-feedback">This field is required</div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="origin-country">Account: </label>
                                        <input class="form-control" id="manufacturar-name" type="text" required="required" />
                                        <div class="invalid-feedback">This field is required</div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="product-summary">Pages: </label>
                                        <input class="form-control" id="product-summary" type="text" required="required"/>
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
                                        <input class="form-control datetimepicker" id="dateTimepickerVal" type="text" required="required" placeholder="d/m/y H:i" data-options='{"enableTime":true,"dateFormat":"d/m/y H:i","disableMobile":true,"allowInput":true}' />
                                        <div class="invalid-feedback">This field is required</div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="cpp">Confirmed: </label>
                                        <select class="form-select" id="cpp" name="cpp">
                                            <option value="0">Yes</option>
                                            <option value="1">No </option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="cpp">Select writer: </label>
                                        <select class="form-select" id="cpp" name="cpp" required="required">
                                            <option value="375">Me</option>
                                            <option value="350">You </option>
                                            <option value="200">They </option>
                                            <option value="400">400</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="product-summary">Writer email: </label>
                                        <input class="form-control" id="product-summary" type="text" required="required"/>
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
                                        <textarea class="tinymce d-none" data-tinymce="data-tinymce" name="task-description" id="task-description" required="required"></textarea>
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
                            <div class="dropzone dropzone-multiple p-0" id="dropzoneMultipleFileUpload" data-dropzone="data-dropzone" action="#!">
                                <div class="fallback">
                                    <input name="file" type="file" multiple="multiple" />
                                </div>
                                <div class="dz-message" data-dz-message="data-dz-message"> <img class="me-2" src="assets/img/icons/cloud-upload.svg" width="25" alt="" /><span class="d-none d-lg-inline">Drag your image here<br/>or, </span><span class="btn btn-link p-0 fs-10">Browse</span></div>
                                <div class="dz-preview dz-preview-multiple m-0 d-flex flex-column">
                                    <div class="d-flex media align-items-center mb-3 pb-3 border-bottom btn-reveal-trigger"><img class="dz-image" src="assets/img/icons/cloud-upload.svg" alt="..." data-dz-thumbnail="data-dz-thumbnail" />
                                        <div class="flex-1 d-flex flex-between-center">
                                            <div>
                                                <h6 data-dz-name="data-dz-name"></h6>
                                                <div class="d-flex align-items-center">
                                                    <p class="mb-0 fs-10 text-400 lh-1" data-dz-size="data-dz-size"></p>
                                                    <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress=""></span></div>
                                                </div><span class="fs-11 text-danger" data-dz-errormessage="data-dz-errormessage"></span>
                                            </div>
                                            <div class="dropdown font-sans-serif">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal dropdown-caret-none" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2"><a class="dropdown-item" href="#!" data-dz-remove="data-dz-remove">Remove File</a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="row justify-content-between align-items-center">
                                <div class="col-md">
                                    <h5 class="mb-2 mb-md-0">You're almost done!</h5>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-link text-secondary p-0 me-3 fw-medium" role="button">Discard</button>
                                    <button class="btn btn-primary" role="button">Create Task </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


<?php
include "footer.php";
?>