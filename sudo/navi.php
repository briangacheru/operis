

<!-- ===============================================-->
<!--    Favicons-->
<!-- ===============================================-->
<link rel="apple-touch-icon" sizes="180x180" href="../assets/img/favicons/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/img/favicons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/img/favicons/favicon-16x16.png">
<link rel="shortcut icon" type="image/x-icon" href="../assets/img/favicons/favicon.ico">
<link rel="manifest" href="../assets/img/favicons/manifest.json">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css" />
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
<meta name="msapplication-TileImage" content="../assets/img/favicons/mstile-150x150.png">
<meta name="theme-color" content="#ffffff">
<script src="../vendors/jquery/jquery.min.js"></script>
<script src="../assets/js/config.js"></script>
<script src="../vendors/simplebar/simplebar.min.js"></script>
<script src="../assets/js/adminsessiontimeout.js"></script>
<script src="../assets/js/admin-task-notification.js"></script>



<!-- ===============================================-->
<!--    Stylesheets-->
<!-- ===============================================-->
<link href="../vendors/glightbox/glightbox.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&amp;display=swap" rel="stylesheet">
<link href="../vendors/simplebar/simplebar.min.css" rel="stylesheet">
<link href="../assets/css/theme-rtl.css" rel="stylesheet" id="style-rtl">
<link href="../assets/css/theme.css" rel="stylesheet" id="style-default">
<link href="../assets/css/user-rtl.css" rel="stylesheet" id="user-style-rtl">
<link href="../assets/css/user.css" rel="stylesheet" id="user-style-default">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
<link href="../vendors/datatables.net-bs5/dataTables.bootstrap5.min.css" rel='stylesheet' />
<link href="../vendors/select2/select2.min.css" rel='stylesheet'/>
<script src="../vendors/tinymce/tinymce.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<link href="../vendors/select2-bootstrap-5-theme/select2-bootstrap-5-theme.min.css" rel='stylesheet' />
<link href="../vendors/flatpickr/flatpickr.min.css" rel="stylesheet" />
<link href="../vendors/dropzone/dropzone.css" rel="stylesheet" />
<link href='../vendors/choices/choices.min.css' rel='stylesheet'/>
<script src='../vendors/choices/choices.min.js'></script>
<style>
    .drop-area {
        border: 2px dashed #ccc;
        border-radius: 20px;
        width: auto;
        padding: 20px;
        text-align: center;
        cursor: pointer;
    }
    .drop-area:hover {
        border-color: #333;
    }
    .drop-area.highlight {
        background-color: #f0f0f0; /* Light grey background */
        border: 2px dashed #ff9900; /* Dashed border in warning color */
    }

    .grain-overlay {
        background: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><defs><pattern id='grain' width='10' height='10' patternUnits='userSpaceOnUse'><circle cx='5' cy='5' r='1' fill='white' opacity='0.05'/></pattern></defs><rect width='100' height='100' fill='url(%23grain)'/></svg>");
        opacity: 1;
    }

    @keyframes onlinePulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.2);
            opacity: 0.7;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
    /* Fix for message hover actions visibility */
    .comment-bubble:hover .hover-actions {
        opacity: 1 !important;
        transition: opacity 0.3s ease;
    }

    .hover-actions {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    /* Alternative: Make actions always visible on mobile/touch devices */
    @media (hover: none) and (pointer: coarse) {
        .hover-actions {
            opacity: 0.7 !important;
        }
    }

    /* Ensure the hover works on the entire message bubble */
    .comment-bubble {
        transition: all 0.3s ease;
    }

    .comment-bubble:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }

    /* Tooltip styling */
    .tooltip-inner {
        background-color: rgba(0, 0, 0, 0.9) !important;
        color: white !important;
        padding: 8px 12px !important;
        border-radius: 6px !important;
        font-size: 12px !important;
        max-width: 200px !important;
    }

    .tooltip .tooltip-arrow {
        border-top-color: rgba(0, 0, 0, 0.9) !important;
    }

    .file-list ul {
        list-style-type: none;
        padding: 0;
    }

    .file-list li {
        margin: 10px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .file-list button {
        margin-left: 20px;

    }

    .blurred-text {
        filter: blur(5px);
        transition: filter 0.3s ease-in-out;
    }
    .visible-text {
        filter: none;
    }
    .blur-financial-data {
        filter: blur(5px);
        transition: filter 0.3s ease-in-out;
    }

    .visible-financial-data {
        filter: none;
    }
    .event-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
        vertical-align: middle;
    }
    .highlighted-link {
        color: #007bff;
        text-decoration: underline;
        font-weight: bold;
    }

    .highlighted-link:hover {
        color: #0056b3;
    }

    .badge-subtle-primary .event-dot {
        background-color: #007bff; /* Example color for Income */
    }

    .badge-subtle-danger .event-dot {
        background-color: #dc3545; /* Example color for Expense */
    }

    .badge-subtle-success .event-dot {
        background-color: #28a745; /* Example color for Savings */
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .table-active {
        border-left: 4px solid #28a745 !important; /* Yellow left border */
    }
    .unviewed-task {
        border-left: 4px solid #f1c40f !important; /* Warning yellow left border */
        position: relative;
    }
    .account-card {
        transition: transform 0.2s;
    }
    .account-card:hover {
        transform: translateY(-5px);
    }
    .balance-positive {
        color: #28a745;
    }
    .balance-negative {
        color: #dc3545;
    }
    .chart-container {
        position: relative;
        height: 400px;
    }
    .table-responsive {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .progress-bar-custom {
        background: linear-gradient(45deg, #007bff, #0056b3);
    }
    .loading {
        display: none;
    }
    .loading.show {
        display: block;
    }
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }
    .growth-updated {
        animation: growthUpdate 0.3s ease-in-out;
    }
    .summary-cards-row {
        display: flex !important;
        flex-wrap: wrap;
    }

    .summary-cards-row .col-md-3 {
        flex: 0 0 25%;
        max-width: 25%;
        padding: 0 15px;
        margin-bottom: 15px;
    }

    @media (max-width: 768px) {
        .summary-cards-row .col-md-3 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @media (max-width: 576px) {
        .summary-cards-row .col-md-3 {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }

    .summary-card {
        height: 100%;
        min-height: 120px;
    }

    .toast {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
        min-width: 300px;
    }

    .toast-body {
        border-radius: 0.5rem;
        font-size: 0.9rem;
    }

    .urgent-reminder-toast {
        min-width: 400px;
        max-width: 500px;
        border: 2px solid;
        animation: urgentPulse 2s infinite;
    }

    .urgent-reminder-toast .toast-header.bg-danger {
        border-color: #dc3545;
    }

    .urgent-reminder-toast .toast-header.bg-warning {
        border-color: #ffc107;
    }

    .urgent-reminder-toast .toast-header.bg-info {
        border-color: #0dcaf0;
    }

    @keyframes urgentPulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
        }
        50% {
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }
    }

    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1055;
        max-height: 80vh;
        overflow-y: auto;
    }

    .urgent-reminder-toast .btn-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    .reminder-card {
        transition: all 0.3s ease;
    }
    .reminder-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .priority-high { border-left: 4px solid #dc3545; }
    .priority-medium { border-left: 4px solid #ffc107; }
    .priority-low { border-left: 4px solid #28a745; }
    .completed { opacity: 0.6; }
    .sidebar {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .main-content {
        background-color: #f8f9fa;
        min-height: 100vh;
    }
    .stat-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border-radius: 12px !important;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }

    .stat-card .card-body {
        border-radius: 12px;
    }

    .stat-card i {
        transition: transform 0.3s ease;
    }

    .stat-card:hover i {
        transform: scale(1.1);
    }
    .priority-high {
        border-left: 4px solid #dc3545 !important;
    }

    .priority-medium {
        border-left: 4px solid #ffc107 !important;
    }

    .priority-low {
        border-left: 4px solid #28a745 !important;
    }

    .status-completed {
        opacity: 0.7;
    }

    .status-completed .text-700 {
        text-decoration: line-through;
    }

    /* Active state styling */
    .stat-card.border-primary {
        border-width: 2px !important;
    }

    .stat-card.border-info {
        border-width: 2px !important;
    }

    .stat-card.border-danger {
        border-width: 2px !important;
    }

    .stat-card.border-success {
        border-width: 2px !important;
    }

    .stat-card.border-warning {
        border-width: 2px !important;
    }

    .stat-card.border-secondary {
        border-width: 2px !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .stat-card .card-body {
            padding: 1rem !important;
        }

        .stat-card i {
            font-size: 1.5rem !important;
        }

        .stat-card h4 {
            font-size: 1.25rem !important;
        }
    }

    .reminder-type-tab {
        border-radius: 8px !important;
        font-weight: 500;
        padding: 10px 16px;
    }

    .reminder-type-tab.active {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: white !important;
    }

    .hover-shadow-lg {
        transition: all 0.3s ease;
    }

    .hover-shadow-lg:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .card-title {
        line-height: 1.3;
    }

     .fc-event-badge {
         position: absolute;
         top: -5px;
         right: -5px;
         font-size: 0.6rem;
         min-width: 16px;
         height: 16px;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         font-weight: bold;
         z-index: 10;
         border: 2px solid white;
     }

    .fc-event-badge.today {
        background-color: #0dcaf0;
        color: white;
    }

    .fc-event-badge.upcoming {
        background-color: #0d6efd;
        color: white;
    }

    .fc-event-badge.overdue {
        background-color: #dc3545;
        color: white;
    }

    .fc-event-badge.completed {
        background-color: #198754;
        color: white;
    }

    .fc-event-badge.dismissed {
        background-color: #ffc107;
        color: #000;
    }

    .fc-event-main {
        position: relative;
    }

    .event-content {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 2px 6px !important;
        line-height: 1.2;
    }

    @media (max-width: 768px) {
        .fc-event {
            font-size: 0.65rem !important;
        }

        .event-content {
            padding: 1px 4px !important;
        }
    }

    @media (max-width: 576px) {
        .fc-event {
            font-size: 0.6rem !important;
        }
    }
    /* Make calendar container responsive */
    #appCalendar {
        width: 100%;
        max-width: 100%;
    }

    /* Responsive calendar adjustments */
    .fc {
        width: 100% !important;
    }

    .fc-view-harness {
        width: 100% !important;
    }

    .fc-daygrid-body {
        width: 100% !important;
    }

    .fc-scrollgrid {
        width: 100% !important;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .fc-header-toolbar {
            flex-direction: column;
            gap: 10px;
        }

        .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }

        .fc-button-group .fc-button {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        .fc-daygrid-day-number {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        .fc-col-header-cell {
            font-size: 0.7rem;
        }

        .fc-daygrid-day-number {
            font-size: 0.7rem;
        }

        .fc-button-group .fc-button {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }
    }

</style>

<script>
    var isRTL = JSON.parse(localStorage.getItem('isRTL'));
    if (isRTL) {
        var linkDefault = document.getElementById('style-default');
        var userLinkDefault = document.getElementById('user-style-default');
        linkDefault.setAttribute('disabled', true);
        userLinkDefault.setAttribute('disabled', true);
        document.querySelector('html').setAttribute('dir', 'rtl');
    } else {
        var linkRTL = document.getElementById('style-rtl');
        var userLinkRTL = document.getElementById('user-style-rtl');
        linkRTL.setAttribute('disabled', true);
        userLinkRTL.setAttribute('disabled', true);
    }
</script>

</head>
<body>

<!-- ===============================================-->
<!--    Main Content-->
<!-- ===============================================-->

<main class="main" id="top">
    <div class="container" data-layout="container">
        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>
        <nav class="navbar navbar-light navbar-vertical navbar-expand-xl">
            <script>
                var navbarStyle = localStorage.getItem("navbarStyle");
                if (navbarStyle && navbarStyle !== 'transparent') {
                    document.querySelector('.navbar-vertical').classList.add(`navbar-${navbarStyle}`);
                }
            </script>
            <div class="d-flex align-items-center">
                <div class="toggle-icon-wrapper">

                    <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-toggle="tooltip" data-bs-placement="top" title="Toggle Navigation"><span class="navbar-toggle-icon"><span class="toggle-line"></span></span></button>

                </div><a class="navbar-brand" href="index">
                    <div class="d-flex align-items-center py-3"><img class="me-2" src="../assets/img/icons/spot-illustrations/itasker.png" alt="" width="40" /><span class="font-sans-serif text-primary">i<span class="text-info">Tasker</span></span>
                    </div>
                </a>
            </div>

            <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
                <div class="navbar-vertical-content scrollbar">
                    <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">
                        <!-- parent pages--><a class="nav-link" href="index" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-home "></span></span><span class="nav-link-text ps-1">Dashboard</span>
                            </div>
                        </a>
                        <?php
                        $aid=$_SESSION['odmsaid'];
                        $sql="SELECT * from  tbladmin where email=:aid";
                        $query = $dbh -> prepare($sql);
                        $query->bindParam(':aid',$aid,PDO::PARAM_STR);
                        $query->execute();
                        $results=$query->fetchAll(PDO::FETCH_OBJ);
                        $cnt=1;
                        if($query->rowCount() > 0)
                        {
                        foreach($results as $row)
                        {
                        if($row->AdminName=="Admin"  )
                        {
                        ?>
                        <li class="nav-item">
                            <!-- label-->
                            <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                <div class="col-auto navbar-vertical-label">TASKS
                                </div>
                                <div class="col ps-0">
                                    <hr class="mb-0 navbar-vertical-divider" />
                                </div>
                            </div>
                            <!-- parent pages--><a class="nav-link" href="create-task" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-plus-circle"></span></span><span class="nav-link-text ps-1">Create Task</span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="all-tasks" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-database"></span></span><span class="nav-link-text ps-1">All Tasks</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-success"><?php
                                        // Query to count tasks where is_deleted = 0
                                        $query = "SELECT COUNT(*) as taskCount FROM tbltasks";
                                        $result = mysqli_query($con, $query);
                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);
                                            $count = $row['taskCount'];
                                            // Check if count is greater than 0
                                            if ($count > 0) {
                                                echo $count; // Display the count
                                            } else {
                                                echo "0"; // Display "No Data" if count is 0
                                            }
                                        }
                                        ?></span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="draft-tasks" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-edit"></span></span><span class="nav-link-text ps-1">Drafts</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-danger"><?php
                                        // Query to count tasks where is_deleted = 0
                                        $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND (writer = 'Draft' OR status = 'Draft')";
                                        $result = mysqli_query($con, $query);
                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);
                                            $count = $row['taskCount'];
                                            // Check if count is greater than 0
                                            if ($count > 0) {
                                                echo $count; // Display the count
                                            } else {
                                                echo "0"; // Display "No Data" if count is 0
                                            }
                                        }
                                        ?></span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="unconfirmed" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-question-circle"></span></span><span class="nav-link-text ps-1">Unconfirmed</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-primary"><?php
                                        // Query to count tasks where is_deleted = 0
                                        $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND is_confirmed = 1";
                                        $result = mysqli_query($con, $query);
                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);
                                            $count = $row['taskCount'];
                                            // Check if count is greater than 0
                                            if ($count > 0) {
                                                echo $count; // Display the count
                                            } else {
                                                echo "0"; // Display "No Data" if count is 0
                                            }
                                        }
                                        ?></span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="tasks-in-progress" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-spinner"></span></span><span class="nav-link-text ps-1">In Progress</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-warning"><?php
                                        // Query to count tasks where is_deleted = 0
                                        $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND status = 'In Progress'";
                                        $result = mysqli_query($con, $query);
                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);
                                            $count = $row['taskCount'];
                                            // Check if count is greater than 0
                                            if ($count > 0) {
                                                echo $count; // Display the count
                                            } else {
                                                echo "0"; // Display "No Data" if count is 0
                                            }
                                        }
                                        ?></span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="tasks-in-revision" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-flag"></span></span><span class="nav-link-text ps-1">In Revision</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-warning"><?php
                                        // Query to count tasks where is_deleted = 0
                                        $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND status = 'In Revision'";
                                        $result = mysqli_query($con, $query);
                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);
                                            $count = $row['taskCount'];
                                            // Check if count is greater than 0
                                            if ($count > 0) {
                                                echo $count; // Display the count
                                            } else {
                                                echo "0"; // Display "No Data" if count is 0
                                            }
                                        }
                                        ?></span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="submitted-tasks" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-check"></span></span><span class="nav-link-text ps-1">Submitted</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-info"><?php
                                        // Query to count tasks where is_deleted = 0
                                        $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND status = 'Submitted'";
                                        $result = mysqli_query($con, $query);
                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);
                                            $count = $row['taskCount'];
                                            // Check if count is greater than 0
                                            if ($count > 0) {
                                                echo $count; // Display the count
                                            } else {
                                                echo "0"; // Display "No Data" if count is 0
                                            }
                                        }
                                        ?></span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="completed-tasks" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-check-double"></span></span><span class="nav-link-text ps-1">Completed</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-success"><?php
                                        // Query to count tasks where is_deleted = 0
                                        $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND status = 'Completed' AND is_archived = 0";
                                        $result = mysqli_query($con, $query);
                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);
                                            $count = $row['taskCount'];
                                            // Check if count is greater than 0
                                            if ($count > 0) {
                                                echo $count; // Display the count
                                            } else {
                                                echo "0"; // Display "No Data" if count is 0
                                            }
                                        }
                                        ?></span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="cancelled-tasks" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-trash"></span></span><span class="nav-link-text ps-1">Cancelled</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-danger"><?php
                                        // Query to count tasks where is_deleted = 0
                                        $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 1";
                                        $result = mysqli_query($con, $query);
                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);
                                            $count = $row['taskCount'];
                                            // Check if count is greater than 0
                                            if ($count > 0) {
                                                echo $count; // Display the count
                                            } else {
                                                echo "0"; // Display "No Data" if count is 0
                                            }
                                        }
                                        ?></span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="archived-tasks" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa fa-archive"></span></span><span class="nav-link-text ps-1">Archived</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-secondary"><?php
                                        // Query to count tasks where is_deleted = 0
                                        $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND is_archived = 1";
                                        $result = mysqli_query($con, $query);
                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);
                                            $count = $row['taskCount'];
                                            // Check if count is greater than 0
                                            if ($count > 0) {
                                                echo $count; // Display the count
                                            } else {
                                                echo "0"; // Display "No Data" if count is 0
                                            }
                                        }
                                        ?></span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="favorited-tasks" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fa fa-heart"></span></span><span class="nav-link-text ps-1">Favorite</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-light"><?php
                                        // Query to count tasks where is_deleted = 0
                                        $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND is_favorite = 1";
                                        $result = mysqli_query($con, $query);
                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);
                                            $count = $row['taskCount'];
                                            // Check if count is greater than 0
                                            if ($count > 0) {
                                                echo $count; // Display the count
                                            } else {
                                                echo "0"; // Display "No Data" if count is 0
                                            }
                                        }
                                        ?></span>
                                </div>
                            </a>

                            <!-- label-->
                            <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                <div class="col-auto navbar-vertical-label">APPS
                                </div>
                                <div class="col ps-0">
                                    <hr class="mb-0 navbar-vertical-divider" />
                                </div>
                            </div>

                            <!-- parent pages--><a class='nav-link' href='reminders' role='button'>
                                <div class='d-flex align-items-center'><span class='nav-link-icon'><span class='fas fa-stopwatch-20'></span></span>
                                    <span class='nav-link-text ps-1'>Reminders</span>
                                    <span class='badge rounded-pill ms-2 badge-subtle-success'>New</span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="calendar" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-calendar"></span></span><span class="nav-link-text ps-1">Calendar</span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="chat" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-comments"></span></span><span class="nav-link-text ps-1">Chat</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-primary"><?php echo $unreadMessagesCount ?></span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="todo" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-clipboard-list"></span></span><span class="nav-link-text ps-1">To-Do List</span>
                                </div>
                            </a>
                            <!-- label-->
                            <a class="nav-link dropdown-indicator collapsed" href="#budget" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="budget">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-wallet"></span></span><span class="nav-link-text ps-1">iFinance</span>
                                </div>
                            </a>
                            <ul class="nav collapse" id="budget" style="">
                                <li class="nav-item"><a class="nav-link" href="budget">
                                        <div class="d-flex align-items-center"><span class="nav-link-text ps-1">iBudget</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="transactions">
                                        <div class="d-flex align-items-center"><span class="nav-link-text ps-1">iTransactions</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="analytics">
                                        <div class="d-flex align-items-center"><span class="nav-link-text ps-1">iChart</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="t-calendar">
                                        <div class="d-flex align-items-center"><span class="nav-link-text ps-1">iCalendar</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="saving-goals">
                                        <div class="d-flex align-items-center"><span class="nav-link-text ps-1">iGoals</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="projects.php">
                                        <div class="d-flex align-items-center"><span class="nav-link-text ps-1">iProjects</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                            <!-- label-->
                            <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                <div class="col-auto navbar-vertical-label">PAYMENT
                                </div>
                                <div class="col ps-0">
                                    <hr class="mb-0 navbar-vertical-divider" />
                                </div>
                            </div>
                            <!-- parent pages--><a class="nav-link" href="unpaid-tasks" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fab fa-creative-commons-nc"></span></span><span class="nav-link-text ps-1">Unpaid</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-warning"><?php
                                        // Query to count tasks where is_deleted = 0
                                        $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND is_paid = 0 AND status = 'Completed'";
                                        $result = mysqli_query($con, $query);
                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);
                                            $count = $row['taskCount'];
                                            // Check if count is greater than 0
                                            if ($count > 0) {
                                                echo $count; // Display the count
                                            } else {
                                                echo "0"; // Display "No Data" if count is 0
                                            }
                                        }
                                        ?></span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="paid-tasks" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="far fa-credit-card"></span></span><span class="nav-link-text ps-1">Paid</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-success"><?php
                                        // Query to count tasks where is_deleted = 0
                                        $query = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND status = 'Completed' AND is_paid = 1";
                                        $result = mysqli_query($con, $query);
                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);
                                            $count = $row['taskCount'];
                                            // Check if count is greater than 0
                                            if ($count > 0) {
                                                echo $count; // Display the count
                                            } else {
                                                echo "0"; // Display "No Data" if count is 0
                                            }
                                        }
                                        ?></span>
                                </div>
                            </a>

                            <!-- label-->
                            <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                <div class="col-auto navbar-vertical-label">OVERDRAFTS
                                </div>
                                <div class="col ps-0">
                                    <hr class="mb-0 navbar-vertical-divider" />
                                </div>
                            </div>
                            <!-- parent pages--><a class="nav-link" href="overdraft" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-donate"></span></span><span class="nav-link-text ps-1">Overdrafts</span>
                                </div>
                            </a>
                            <!-- parent pages--><a class="nav-link" href="settled-overdrafts" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-stamp"></span></span><span class="nav-link-text ps-1">Settled</span>
                                </div>
                            </a>
                            <!-- label-->
                            <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                                <div class="col-auto navbar-vertical-label">MANAGEMENT
                                </div>
                                <div class="col ps-0">
                                    <hr class="mb-0 navbar-vertical-divider" />
                                </div>
                            </div>
                            <!-- parent pages--><a class="nav-link" href="usermanagement" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-users"></span></span><span class="nav-link-text ps-1">Writers</span>
                                    <span class="badge rounded-pill ms-2 badge-subtle-secondary">New</span>
                                </div>
                            </a>

                            <?php
                            }
                            }
                            } ?>

                            <!-- parent pages--><a class="nav-link" href="profile" role="button">
                                <div class="d-flex align-items-center"><span class="nav-link-icon"><span class="fas fa-wrench"></span></span><span class="nav-link-text ps-1">Profile</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>



        <div class="content">
            <nav class="navbar navbar-light navbar-glass navbar-top navbar-expand">

                <button class="btn navbar-toggler-humburger-icon navbar-toggler me-1 me-sm-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse" aria-controls="navbarVerticalCollapse" aria-expanded="false" aria-label="Toggle Navigation"><span class="navbar-toggle-icon"><span class="toggle-line"></span></span></button>
                <a class="navbar-brand me-1 me-sm-3" href="index">
                    <div class="d-flex align-items-center"><img class="me-2" src="../assets/img/icons/spot-illustrations/itasker.png" alt="" width="40" /><span class="font-sans-serif text-primary">i</span><span class="font-sans-serif text-info">Tasker</span>
                    </div>
                </a>
                <ul class="navbar-nav align-items-center d-none d-lg-block">
                    <li class="nav-item">
                        <div class="search-box" data-list='{"valueNames":["title"]}'>
                            <form class="position-relative" data-bs-toggle="search" data-bs-display="static">
                                <input class="form-control search-input fuzzy-search" type="search" placeholder="Search..." aria-label="Search" />
                                <span class="fas fa-search search-box-icon"></span>

                            </form>
                            <div class="btn-close-falcon-container position-absolute end-0 top-50 translate-middle shadow-none" data-bs-dismiss="search">
                                <button class="btn btn-link btn-close-falcon p-0" aria-label="Close"></button>
                            </div>
                        </div>
                    </li>
                </ul>
                <ul class="navbar-nav navbar-nav-icons ms-auto flex-row align-items-center">
                    <li class="nav-item ps-2 pe-0">
                        <div class="dropdown theme-control-dropdown"><a class="nav-link d-flex align-items-center dropdown-toggle fa-icon-wait fs-9 pe-1 py-0" href="#" role="button" id="themeSwitchDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="fas fa-sun fs-7" data-fa-transform="shrink-2" data-theme-dropdown-toggle-icon="light"></span><span class="fas fa-moon fs-7" data-fa-transform="shrink-3" data-theme-dropdown-toggle-icon="dark"></span><span class="fas fa-adjust fs-7" data-fa-transform="shrink-2" data-theme-dropdown-toggle-icon="auto"></span></a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-caret border py-0 mt-3" aria-labelledby="themeSwitchDropdown">
                                <div class="bg-white dark__bg-1000 rounded-2 py-2">
                                    <button class="dropdown-item d-flex align-items-center gap-2" type="button" value="light" data-theme-control="theme"><span class="fas fa-sun"></span>Light<span class="fas fa-check dropdown-check-icon ms-auto text-600"></span></button>
                                    <button class="dropdown-item d-flex align-items-center gap-2" type="button" value="dark" data-theme-control="theme"><span class="fas fa-moon" data-fa-transform=""></span>Dark<span class="fas fa-check dropdown-check-icon ms-auto text-600"></span></button>
                                    <button class="dropdown-item d-flex align-items-center gap-2" type="button" value="auto" data-theme-control="theme"><span class="fas fa-adjust" data-fa-transform=""></span>Auto<span class="fas fa-check dropdown-check-icon ms-auto text-600"></span></button>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class='nav-item dropdown'>
                        <?php
                        // Query to count new submitted tasks (not yet acknowledged)
                        $newTasksCountQuery = mysqli_query($con, "SELECT COUNT(*) AS new_task_count FROM tbltasks WHERE is_deleted = 0 AND status = 'Submitted' AND admin_acknowledged = 0");
                        $newTasksCountResult = mysqli_fetch_assoc($newTasksCountQuery);
                        $newTasksCount = $newTasksCountResult['new_task_count'];

                        // Query to count late tasks
                        $lateTasksCountQuery = mysqli_query($con, "SELECT COUNT(*) AS late_task_count FROM tbltasks WHERE is_deleted = 0 AND status = 'In Progress' AND due_date < NOW()");
                        $lateTasksCountResult = mysqli_fetch_assoc($lateTasksCountQuery);
                        $lateTasksCount = $lateTasksCountResult['late_task_count'];

                        // Total task notifications
                        $totalTaskNotifications = $newTasksCount + $lateTasksCount;

                        // Query to fetch new submitted tasks details (priority: submitted first)
                        $newTasksQuery = mysqli_query($con, "SELECT *, 'submitted' as notification_type FROM tbltasks WHERE is_deleted = 0 AND status = 'Submitted' AND admin_acknowledged = 0 ORDER BY submitted_on DESC LIMIT 3");
                        $newTasks = [];
                        while ($task = mysqli_fetch_assoc($newTasksQuery)) {
                            $newTasks[] = $task;
                        }

                        // Query to fetch late tasks details
                        $lateTasksQuery = mysqli_query($con, "SELECT *, 'late' as notification_type FROM tbltasks WHERE is_deleted = 0 AND status = 'In Progress' AND due_date < NOW() ORDER BY due_date ASC LIMIT 3");
                        $lateTasks = [];
                        while ($task = mysqli_fetch_assoc($lateTasksQuery)) {
                            // Calculate overdue time
                            $dueDate = new DateTime($task['due_date']);
                            $now = new DateTime();
                            $interval = $now->diff($dueDate);
                            $task['time_overdue'] = $interval->format('%a days %h hours');
                            $lateTasks[] = $task;
                        }

                        // Check admin permissions
                        $aid = $_SESSION['odmsaid'];
                        $sql = 'SELECT * FROM tbladmin WHERE email=:aid';
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':aid', $aid, PDO::PARAM_STR);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        $isAdmin = ($query->rowCount() > 0 && $results[0]->AdminName == 'Admin');
                        ?>

                        <?php if ($isAdmin): ?>
                            <a class="nav-link notification-indicator notification-indicator-warning px-0 fa-icon-wait"
                               id="navbarDropdownTasks" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                               aria-expanded="false">
                                <span class="fas fa-tasks" data-fa-transform="shrink-6" style="font-size: 33px;"></span>
                                <?php if ($totalTaskNotifications > 0): ?>
                                    <span class="notification-indicator-number"><?php echo $totalTaskNotifications; ?></span>
                                <?php endif; ?>
                            </a>

                            <div class="dropdown-menu dropdown-caret dropdown-caret dropdown-menu-end dropdown-menu-card dropdown-menu-notification dropdown-caret-bg"
                                 aria-labelledby="navbarDropdownTasks">
                                <div class="card card-notification shadow-none">
                                    <div class="card-header">
                                        <div class="row justify-content-between align-items-center">
                                            <div class="col-auto">
                                                <h6 class="card-header-title mb-0 text-warning">Task Notifications</h6>
                                            </div>
                                            <?php if ($newTasksCount > 0): ?>
                                                <div class="col-auto ps-0 ps-sm-3">
                                                    <a class="card-link fw-normal" href="#"
                                                       onclick="markAllSubmittedAsRead()">Mark all as read</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="scrollbar-overlay" style="max-height:19rem">
                                        <div class="list-group list-group-flush fw-normal fs-10">

                                            <?php if ($newTasksCount > 0): ?>
                                                <div class="list-group-title border-bottom text-success">
                                                    <span class="fas fa-check-circle me-2"></span>You
                                                    have <?php echo $newTasksCount; ?> new submitted tasks.
                                                </div>
                                                <?php foreach ($newTasks as $key => $task): ?>
                                                    <div class="list-group-item">
                                                        <?php $encodedId = base64_encode($task['id']); ?>
                                                        <a class="notification notification-flush notification-unread"
                                                           href="view-task?task_id=<?php echo htmlspecialchars($encodedId); ?>"
                                                           onclick="handleTaskClick(event, <?php echo $task['id']; ?>, '<?php echo htmlspecialchars($encodedId); ?>')">
                                                            <div class="notification-avatar">
                                                                <div class="avatar avatar-2xl me-3">
                                                                    <span class="material-icons text-success fs-3">assignment_turned_in</span>
                                                                </div>
                                                            </div>
                                                            <div class="notification-body">
                                                                <p class="mb-1 text-success">
                                                                    <strong>Task <?php echo $task['id']; ?>:</strong>
                                                                    <?php echo htmlspecialchars(substr($task['topic'], 0, 35)) . (strlen($task['topic']) > 35 ? '...' : ''); ?>
                                                                </p>
                                                                <span class='notification-time text-muted'
                                                                      title="<?php echo date('M j, Y g:i A', strtotime($task['submitted_on'])); ?>">
                                                                    <span class='me-2' role='img' aria-label='Time'>📤</span>
                                                                    Submitted: <?php echo timeSubAgo($task['submitted_on']); ?>
                                                                </span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <?php if ($key >= 2) break; ?>
                                                <?php endforeach; ?>
                                            <?php endif; ?>

                                            <?php if ($lateTasksCount > 0): ?>
                                                <div class="list-group-title border-bottom text-danger">
                                                    <span class="fas fa-exclamation-triangle me-2"></span>You
                                                    have <?php echo $lateTasksCount; ?> overdue tasks.
                                                </div>
                                                <?php foreach ($lateTasks as $key => $task): ?>
                                                    <div class="list-group-item">
                                                        <?php $encodedId = base64_encode($task['id']); ?>
                                                        <a class="notification notification-flush notification-unread"
                                                           href="view-task?task_id=<?php echo htmlspecialchars($encodedId); ?>">
                                                            <div class="notification-avatar">
                                                                <div class="avatar avatar-2xl me-3">
                                                                    <span class="material-icons text-danger fs-3">dangerous</span>
                                                                </div>
                                                            </div>
                                                            <div class="notification-body">
                                                                <p class="mb-1 text-danger">
                                                                    <strong>Task <?php echo $task['id']; ?>:</strong>
                                                                    <?php echo htmlspecialchars(substr($task['topic'], 0, 35)) . (strlen($task['topic']) > 35 ? '...' : ''); ?>
                                                                </p>
                                                                <span class="notification-time text-danger">
                                            <span class="me-2" role="img" aria-label="Time">⏰</span>
                                            Overdue by <?php echo $task['time_overdue']; ?>
                                        </span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <?php if ($key >= 2) break; ?>
                                                <?php endforeach; ?>
                                            <?php endif; ?>

                                            <?php if ($totalTaskNotifications == 0): ?>
                                                <div class="list-group-item text-center text-muted py-4">
                                                    <span class="fas fa-check-circle me-2 text-success"></span>
                                                    <p class="mb-0">All tasks are up to date!</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center border-top">
                                        <a class="card-link d-block" href="submitted-tasks">View all tasks</a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link notification-indicator notification-indicator-info px-0 fa-icon-wait" id="navbarDropdownNotification" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-hide-on-body-scroll="data-hide-on-body-scroll">
                            <span class="fas fa-comment" data-fa-transform="shrink-6" style="font-size: 33px;"></span>

                            <span class="notification-indicator-number"><?php echo $unreadMessagesCount; ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-caret dropdown-caret dropdown-menu-end dropdown-menu-card dropdown-menu-notification dropdown-caret-bg" aria-labelledby="navbarDropdownNotification">
                            <div class="card card-notification shadow-none">
                                <div class="card-header">
                                    <div class="row justify-content-between align-items-center">
                                        <div class="col-auto">
                                            <h6 class="card-header-title mb-0 text-primary">Messages</h6>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $sql = "SELECT * FROM tbladmin WHERE email='$aid'";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $results = $query->fetchAll(PDO::FETCH_OBJ);
                                $cnt = 1;

                                if ($query->rowCount() > 0) {
                                foreach ($results as $row) {
                                if ($row->AdminName == "Admin") {
                                ?>
                                <div class="scrollbar-overlay" style="max-height:19rem">
                                    <div class="list-group list-group-flush fw-normal fs-10">
                                        <div class="list-group-title border-bottom text-info">You have <?php echo $unreadMessagesCount; ?> unread messages.</div>
                                        <?php
                                        // Display each unread message
                                        foreach ($unreadMessages as $key => $message) {
                                            // Fetch sender details from tblwriters
                                            $senderID = $message['sender_id'];
                                            $senderQuery = mysqli_query($con, "SELECT username, Photo FROM tblwriters WHERE id = '$senderID'");
                                            $senderResult = mysqli_fetch_assoc($senderQuery);

                                            $receivedDate = new DateTime($message['timestamp']);
                                            $now = new DateTime();
                                            $interval = $now->diff($receivedDate);
                                            $unreadMessages[$key]['time_received'] = $interval->format('%a days %h hours %i minutes');
                                            if ($key >= 9) break; // Limit to only 10 messages
                                        }
                                        ?>
                                        <?php foreach ($unreadMessages as $key => $message): ?>
                                            <div class="list-group-item">
                                                <?php
                                                $encodedId = base64_encode($message['id']);
                                                $senderName = $senderResult['username'];
                                                $senderPhoto = $senderResult['Photo'];
                                                ?>
                                                <a class="notification notification-flush notification-unread" href="chat?message_id=<?php echo htmlspecialchars($encodedId); ?>">
                                                    <div class="notification-avatar">
                                                        <div class="avatar avatar-2xl me-3">
                                                            <div class="avatar-name rounded-circle"><img src="../profileimages/<?php echo $senderPhoto; ?>" alt="<?php echo $senderName; ?>" class="rounded-circle"></div>
                                                        </div>
                                                    </div>
                                                    <div class="notification-body">
                                                        <p class="mb-1"><strong><?php echo $senderName; ?></strong>: <?php echo $message['message']; ?></p>
                                                        <span class="notification-time"><span class="me-2" role="img" aria-label="Emoji">💬</span><?php echo $message['time_received']; ?></span>
                                                    </div>
                                                </a>
                                            </div>
                                            <?php if ($key >= 9) break; // Display only up to 10 messages ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="card-footer text-center border-top"><a class="card-link d-block" href="chat">View all</a></div>
                            </div>
                        </div>
                    </li>
                    <?php
                    }
                    }
                    } ?>



                    <li class='nav-item dropdown'>
                        <?php
                        $aid = $_SESSION['odmsaid'];

                        // Query to count unread writer comments for admin
                        $unreadCommentsCountQuery = mysqli_query($con, "
                            SELECT COUNT(*) AS unread_comments_count 
                            FROM tbl_task_comments 
                            WHERE user_type = 'writer' 
                            AND is_read = 0
                        ");
                        $unreadCommentsCountResult = mysqli_fetch_assoc($unreadCommentsCountQuery);
                        $unreadCommentsCount = $unreadCommentsCountResult['unread_comments_count'];

                        // Query to fetch unread comments details (limit to recent ones)
                        $unreadCommentsQuery = mysqli_query($con, "
                            SELECT tc.*, t.topic, t.id as task_id, t.email as writer_email
                            FROM tbl_task_comments tc 
                            JOIN tbltasks t ON tc.task_id = t.id 
                            WHERE tc.user_type = 'writer' 
                            AND tc.is_read = 0 
                            ORDER BY tc.created_at DESC 
                            LIMIT 5
                        ");

                        $unreadComments = [];
                        while ($comment = mysqli_fetch_assoc($unreadCommentsQuery)) {
                            // Calculate time ago
                            $commentTime = new DateTime($comment['created_at']);
                            $now = new DateTime();
                            $interval = $now->diff($commentTime);

                            if ($interval->y > 0) {
                                $timeAgo = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
                            } elseif ($interval->m > 0) {
                                $timeAgo = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
                            } elseif ($interval->d > 0) {
                                $timeAgo = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
                            } elseif ($interval->h > 0) {
                                $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                            } elseif ($interval->i > 0) {
                                $timeAgo = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
                            } else {
                                $timeAgo = 'Just now';
                            }

                            $comment['time_ago'] = $timeAgo;
                            $unreadComments[] = $comment;
                        }

                        // Check admin permissions
                        $sql = 'SELECT * FROM tbladmin WHERE email=:aid';
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':aid', $aid, PDO::PARAM_STR);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        $isAdmin = ($query->rowCount() > 0 && $results[0]->AdminName == 'Admin');
                        ?>

                        <?php if ($isAdmin): ?>
                            <a class="nav-link notification-indicator notification-indicator-info px-0 fa-icon-wait"
                               id="navbarDropdownComments" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                               aria-expanded="false">
                                <span class="fas fa-bell" data-fa-transform="shrink-6"
                                      style="font-size: 33px;"></span>
                                <?php if ($unreadCommentsCount > 0): ?>
                                    <span class="notification-indicator-number"><?php echo $unreadCommentsCount; ?></span>
                                <?php endif; ?>
                            </a>

                            <div class="dropdown-menu dropdown-caret dropdown-caret dropdown-menu-end dropdown-menu-card dropdown-menu-notification dropdown-caret-bg"
                                 aria-labelledby="navbarDropdownComments">
                                <div class="card card-notification shadow-none">
                                    <div class="card-header">
                                        <div class="row justify-content-between align-items-center">
                                            <div class="col-auto">
                                                <h6 class="card-header-title mb-0 text-info">New Comments</h6>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="scrollbar-overlay" style="max-height:19rem">
                                        <div class="list-group list-group-flush fw-normal fs-10">
                                            <?php if ($unreadCommentsCount > 0): ?>
                                                <div class="list-group-title border-bottom text-info">
                                                    You have <?php echo $unreadCommentsCount; ?> unread comments from
                                                    writers.
                                                </div>
                                                <?php foreach ($unreadComments as $key => $comment): ?>
                                                    <div class="list-group-item">
                                                        <?php $encodedTaskId = base64_encode($comment['task_id']); ?>
                                                        <a class="notification notification-flush notification-unread"
                                                           href="view-task?task_id=<?php echo htmlspecialchars($encodedTaskId); ?>">
                                                            <div class="notification-avatar">
                                                                <div class="avatar avatar-2xl me-3">
                                                                    <span class="material-icons text-info fs-3">chat_bubble</span>
                                                                </div>
                                                            </div>
                                                            <div class="notification-body">
                                                                <p class="mb-1 text-info">
                                                                    <strong>Task <?php echo $comment['task_id']; ?>
                                                                        :</strong>
                                                                    <?php echo htmlspecialchars(substr($comment['topic'], 0, 30)) . (strlen($comment['topic']) > 30 ? '...' : ''); ?>
                                                                </p>
                                                                <p class="mb-1 fs-11">
                                                                    <strong><?php echo htmlspecialchars($comment['username']); ?>
                                                                        :</strong>
                                                                    <?php
                                                                    $unescaped_comment = stripcslashes($comment['comment']);
                                                                    echo htmlspecialchars(substr($unescaped_comment, 0, 45)) . (strlen($unescaped_comment) > 45 ? '...' : '');
                                                                    ?>
                                                                </p>
                                                                <span class="notification-time text-muted">
                                            <span class="me-2" role="img" aria-label="Time">💬</span>
                                            <?php echo $comment['time_ago']; ?>
                                        </span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <?php if ($key >= 3) break; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="list-group-item text-center text-muted py-4">
                                                    <span class="fas fa-check-circle me-2 text-info"></span>
                                                    <p class="mb-0">No new comments from writers</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center border-top">
                                        <a class="card-link d-block" href="all-comments.php">View all comments</a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </li>

                    <li class="nav-item dropdown"><a class="nav-link pe-0 ps-2" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php
                            $aid=$_SESSION['odmsaid'];
                            $sql="SELECT * from  tbladmin where email=:aid";
                            $query = $dbh -> prepare($sql);
                            $query->bindParam(':aid',$aid,PDO::PARAM_STR);
                            $query->execute();
                            $results=$query->fetchAll(PDO::FETCH_OBJ);
                            $cnt=1;
                            if($query->rowCount() > 0)
                            {
                            foreach($results as $row)
                            {
                            ?>
                            <div class="avatar avatar-xl status-online">
                                <?php
                                if($row->Photo=="avatar.png")
                                {
                                    ?>
                                    <img class="rounded-circle" src="../assets/img/team/avatar.png" alt="" />
                                    <?php
                                } else {
                                    ?>
                                    <img class="rounded-circle" src="../profileimages/<?php  echo $row->Photo;?>" alt="">
                                    <?php
                                } ?>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-caret dropdown-caret dropdown-menu-end py-0" aria-labelledby="navbarDropdownUser">
                            <div class="bg-white dark__bg-1000 rounded-2 py-2">
                                <a class="dropdown-item fw-bold text-warning" href="#"><span><?php  echo $row->FirstName;?> <?php  echo $row->LastName;?></span></a>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="profile">Profile &amp; account</a>
                                <a class="dropdown-item" href="settings">Settings</a>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="changelog">Version</a>
                                <a class="dropdown-item" href="logs">Logs</a>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout?logout=1">Logout</a>
                            </div>
                        </div>
                    </li>
                </ul>
            </nav>
            <?php
            }
            } ?>

            <?php
            $query = mysqli_query($con, "SELECT * FROM tblsettings WHERE id = 3");

            if ($query && mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_assoc($query);
            $currentNotification = $row['description'];

            if (isset($row['regStatus']) && $row['regStatus'] == 1) {
            ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Notification!</strong> <?php echo htmlspecialchars($currentNotification, ENT_QUOTES, 'UTF-8'); ?>
                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php
            }
            } else {
                echo '<div class="alert alert-danger" role="alert">Error fetching notification settings.</div>';
            }
            ?>

            <!-- Alert for Unconfirmed Tasks -->
            <?php
            $query_unconfirmed = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND is_confirmed = 1";
            $result_unconfirmed = mysqli_query($con, $query_unconfirmed);
            if ($result_unconfirmed) {
                $row_unconfirmed = mysqli_fetch_assoc($result_unconfirmed);
                $count_unconfirmed = $row_unconfirmed['taskCount'];
                if ($count_unconfirmed > 0) {
                    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        You have ' . $count_unconfirmed . ' unconfirmed tasks! 
                        <a href="unconfirmed" class="alert-link">View Tasks</a>
                        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                }
            }
            ?>

            <!-- Alert for Tasks in Revision -->
            <?php
            // Total tasks in revision
            $query_revision = "SELECT COUNT(*) as taskCount FROM tbltasks WHERE is_deleted = 0 AND status = 'In Revision'";
            $result_revision = mysqli_query($con, $query_revision);
            $count_revision = 0;
            if ($result_revision) {
                $row_revision = mysqli_fetch_assoc($result_revision);
                $count_revision = $row_revision['taskCount'];
            }

            // Tasks in revision not yet viewed (acknowledged = 0)
            $query_unviewed = "SELECT COUNT(*) as unviewedCount FROM tbltasks WHERE is_deleted = 0 AND status = 'In Revision' AND acknowledged = 0";
            $result_unviewed = mysqli_query($con, $query_unviewed);
            $count_unviewed = 0;
            if ($result_unviewed) {
                $row_unviewed = mysqli_fetch_assoc($result_unviewed);
                $count_unviewed = $row_unviewed['unviewedCount'];
            }

            if ($count_revision > 0) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                echo 'You have ' . $count_revision . ' tasks in revision';
                if ($count_unviewed > 0) {
                    echo ' (' . $count_unviewed . ' not yet viewed)';
                }
                echo '! <a href="tasks-in-revision" class="alert-link">View Tasks</a>
                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button> 
                    </div>';
            }
            ?>
