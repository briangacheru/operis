<?php include "head.php";

if (isset($_GET['projectID'])) {
    $encodedId = $_GET['projectID'];
    $projectID = base64_decode($encodedId);
} else {
    $_SESSION['alert'] ='<div class="alert alert-warning border-0 d-flex align-items-center" role="alert">
                                        <div class="bg-warning me-3 icon-item"><span class="fas fa-exclamation-circle text-white fs-6"></span></div>
                                        <p class="mb-0 flex-1">Invalid Project ID!</p>
                                        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>';
}
?>
    <title>Project projects</title>
<?php include "navi.php";
$status = "OK";
$msg = "";
?>
    <div class="card shadow-none border mb-3">
        <div class="card-header d-flex flex-between-center bg-body-tertiary py-2">
            <h3 class="mb-0 text-primary bg">Project Details</h3>

        </div>
    </div>

<?php
if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']);
    //echo '<meta http-equiv="refresh" content="10;url=' . htmlspecialchars($_SERVER['PHP_SELF']) . '">';
}
?>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row justify-content-between align-items-center">
                <div class="col">
                    <?php
                    $queryProject = mysqli_query($con, " SELECT * FROM tblprojects WHERE projectID = '$projectID'");
                    if ($rowProject = mysqli_fetch_array($queryProject)) {
                        $projectID = $rowProject['projectID'];
                        $projectName = $rowProject['projectName'];
                        $projectDescription = $rowProject['projectDescription'];
                        $projectStatus = $rowProject['projectStatus'];
                        $projectPeriod = $rowProject['projectPeriod'];
                        $projectAmount = $rowProject['projectAmount'];
                        $createdAt = date("F j, Y", strtotime($rowProject['created_at']));
                        $isAchieved = $rowProject['is_achieved'];
                        ?>
                    <div class="d-flex">
                        <div class="calendar mt-3"><span class="fas fa-fas fa-gem text-primary fs-4"> </span></div>
                        <div class="flex-1 fs-10">
                            <h4 ><?php echo htmlspecialchars($projectName); ?></h4>
                            <span class="fs-9 text-warning fw-semi-bold">Ksh. <?php echo number_format($projectAmount, 2); ?></span>
                            <span class="ms-1 text-400" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Projected capital" data-bs-original-title="Projected capital">
                                <span class="far fa-question-circle" data-fa-transform="shrink-1"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-xxl-6">
            <div class="row g-3">
                <div class="col-12">
                    <div class="card h-100 font-sans-serif">
                        <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-3.png);">
                        </div>
                        <div class="card-header bg-body-tertiary d-flex flex-between-center py-2">
                            <h6 class="mb-0">More Project Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 h-100">
                                <div class="col-sm-6 col-lg-12">
                                    <table class="table table-borderless fw-medium font-sans-serif fs-10 mb-2">
                                        <tbody>
                                        <tr>
                                            <td class="p-1" style="width: 35%;">Description:</td>
                                            <td class="p-1 fs-10 text-600"><?php echo htmlspecialchars($projectDescription); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="p-1" style="width: 35%;">Status:</td>
                                            <td class="p-1 text-600"><span class="fs-10 badge rounded-pill <?php echo $rowProject["projectStatus"] == 0 ? 'badge-subtle-warning' : 'badge-subtle-success'; ?>">
                                                    <?php echo $rowProject["projectStatus"] == 0 ? "Active" : "Inactive"; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="p-1" style="width: 35%;">Created:</td>
                                            <td class="p-1 text-600"><?php echo htmlspecialchars($createdAt); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="p-1" style="width: 35%;">Achieved?:</td>
                                            <td class="p-1 text-600">
                                                <span class="fs-11 badge rounded-pill <?php echo $rowProject["is_achieved"] == 0 ? 'badge-subtle-secondary' : 'badge-subtle-success'; ?>"><?php echo $rowProject["is_achieved"] == 0 ? "Not Achieved Yet" : "Achieved & Completed"; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php
                            } else {
                                echo "<p>No project details found for the given project ID.</p>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card font-sans-serif">
                        <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-1.png);">
                        </div>
                        <div class="card-header pb-0">
                            <h6 class="mb-0">Spendings</h6>
                        </div>
                            <?php
                            $queryProject = mysqli_query($con, " SELECT p.projectID, p.projectName, p.projectAmount, p.projectStatus, p.projectDescription, p.is_achieved, COALESCE(SUM(b.amount), 0) AS currentProjectAmount
                    FROM tblprojects p LEFT JOIN tblbudget b  ON p.projectName = b.subcategory AND b.category = 'Expense' WHERE p.projectID = '$projectID'
                    GROUP BY p.projectID, p.projectName, p.projectAmount, p.projectStatus, p.projectDescription, p.is_achieved");
                            if ($row = mysqli_fetch_array($queryProject)) {
                                $projectID = $row['projectID'];
                                $currentProjectAmount = $row['currentProjectAmount'];
                                $progress = ($currentProjectAmount / $projectAmount) * 100;
                                $projectBalance = ($projectAmount - $currentProjectAmount);
                                ?>
                        <div class="card-body">
                            <div class="row flex-between-center">
                                <div class="col d-md-flex d-lg-block flex-between-center">
                                    <h4 class="text-700 lh-1 mb-1">Ksh <?php echo number_format($currentProjectAmount, 2); ?></h4>
                                    <?php
                                    if ($currentProjectAmount > $projectAmount) {
                                        // Loss condition
                                        $badgeClass = "badge-subtle-danger";
                                        $textClass = "text-danger";
                                        $status = "Overdraft";
                                        $balanceText = "Ksh " . number_format(abs($projectBalance), 2);
                                    } else {
                                        // Profit condition
                                        $badgeClass = "badge-subtle-success";
                                        $textClass = "text-success";
                                        $status = "Credit";
                                        $balanceText = "Ksh " . number_format($projectBalance, 2);
                                    }
                                    ?>

                                    <span class="badge rounded-pill <?php echo $badgeClass; ?> fs-11 align-bottom">
                                        <span><?php echo $balanceText; ?> (<?php echo $status; ?>)</span>
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <h4 class="fs-6 fw-normal <?php echo $textClass; ?>"><?php echo $progress ?>%</h4>
                                </div>
                            </div>
                            <?php
                            } else {
                                echo '<h6 class="text-700 lh-1 mb-1">No project details found for the given project ID.</h6>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card font-sans-serif">
                        <div class="bg-holder bg-card" style="background-image:url(../assets/img/icons/spot-illustrations/corner-4.png);">
                        </div>
                        <div class="card-header pb-0">
                            <h6 class="mb-0">Income</h6>
                        </div>
                        <?php
                        $queryProject = mysqli_query($con, " SELECT p.projectID, p.projectName, p.projectAmount, p.projectStatus, p.projectDescription, p.is_achieved, COALESCE(SUM(b.amount), 0) AS currentProjectIncome
                                FROM tblprojects p LEFT JOIN tblbudget b  ON p.projectName = b.subcategory AND b.category = 'Income' WHERE p.projectID = '$projectID'
                                GROUP BY p.projectID, p.projectName, p.projectAmount, p.projectStatus, p.projectDescription, p.is_achieved");
                        if ($row = mysqli_fetch_array($queryProject)) {
                        $projectID = $row['projectID'];
                        $currentProjectIncome = $row['currentProjectIncome'];
                        $progress = ($currentProjectIncome / $currentProjectAmount) * 100;
                        $projectBalance = ($currentProjectIncome - $currentProjectAmount);
                        ?>
                        <div class="card-body">
                            <div class="row flex-between-center">
                                <div class="col d-md-flex d-lg-block flex-between-center">
                                    <h4 class="text-700 lh-1 mb-1">Ksh <?php echo number_format($currentProjectIncome, 2); ?></h4>
                                    <?php
                                    if ($currentProjectIncome <= $currentProjectAmount) {
                                    // Loss condition
                                    $badgeClass = "badge-subtle-danger";
                                    $textClass = "text-danger";
                                    $status = "Loss";
                                    $balanceText = "Ksh " . number_format(abs($projectBalance), 2);
                                    } else {
                                    // Profit condition
                                    $badgeClass = "badge-subtle-success";
                                    $textClass = "text-success";
                                    $status = "Profit";
                                    $balanceText = "Ksh " . number_format($projectBalance, 2);
                                    }
                                    ?>

                                    <span class="badge rounded-pill <?php echo $badgeClass; ?> fs-11 align-bottom">
                                        <span><?php echo $balanceText; ?> (<?php echo $status; ?>)</span>
                                    </span>
                                </div>
                                <div class="col-auto">
                                    <h4 class="fs-6 fw-normal <?php echo $textClass; ?>"><?php echo $progress ?>%</h4>
                                </div>
                            </div>
                            <?php
                            } else {
                                echo '<h6 class="text-700 lh-1 mb-1">No project details found for the given project ID.</h6>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-6">
            <div class="card h-100 font-sans-serif">
                <div class="card-header bg-body-tertiary d-flex flex-between-center py-2">
                    <h6 class="mb-0">Sectional Overview</h6>
                </div>
                <div class="card-body p-0 d-flex flex-column justify-content-between">
                    <div id="expenseChart" style="width: 100%; height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-xxl-12">
            <div class="card overflow-hidden">
                <div class="card-header d-flex flex-between-center bg-body-tertiary py-2">
                    <h5 class="mb-0 text-primary bg">Project Transactions</h5>
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
                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Category</th>
                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Description</th>
                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Amount</th>
                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Wallet</th>
                            <th class="text-900 sort pe-1 align-middle white-space-nowrap">Date</th>
                        </tr>
                        </thead>
                        <tbody class="list" id="table-simple-pagination-body">
                        <?php
                        $query = mysqli_query($con,"SELECT b.budgetID, b.category, b.amount, b.description, b.tag, b.expenseDate
                            FROM tblprojects p INNER JOIN tblbudget b ON p.projectName = b.subcategory WHERE p.projectName = '$projectName'
                            ORDER BY b.budgetID DESC");

                        $cnt=1;
                        while($row=mysqli_fetch_array($query)){
                            ?>
                            <tr class="hover-actions-trigger btn-reveal-trigger hover-bg-100">
                                <td class="align-middle" style="width: 28px;">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="simple-pagination-item-<?php echo $cnt; ?>" data-bulk-select-row="data-bulk-select-row" value="<?php echo $row['budgetID']; ?>" name="budgetIds[]"/>
                                    </div>
                                </td>
                                <td class="align-middle text-start product">
                                    <div class="d-flex align-items-center position-relative">
                                        <div class="flex-1">
                                            <h6 class="mb-0 fw-semi-bold text-nowrap"> <?php echo $row["category"]; ?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle text-start">
                                    <div class="d-flex align-items-center position-relative">
                                        <div class="flex-1">
                                            <h6 class="mb-0 fw-semi-bold text-nowrap"><?php echo $row["description"]; ?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle text-start">
                                    <div class="d-flex align-items-center position-relative">
                                        <div class="flex-1">
                                            <h6 class="mb-0 fw-semi-bold text-nowrap"><?php echo number_format($row["amount"], 2); ?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle text-start amount">
                                    <div class="flex-1">
                                        <h6 class="mb-0 fw-semi-bold text-nowrap"><?php echo $row["tag"]; ?></h6>
                                    </div>
                                </td>
                                <td class="align-middle text-start amount">
                                    <h6 class="mb-0 fw-semi-bold mb-0 text-500"><?php echo date("M j, Y", strtotime($row["expenseDate"])); ?></h6>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.0/echarts.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var expenseChart = echarts.init(document.getElementById('expenseChart'));

            var option = {
                backgroundColor: '#121e2d', // Optional dark background for a modern look
                tooltip: {
                    trigger: 'item',
                    formatter: '{a} <br/>{b}: {c} ({d}%)',
                    backgroundColor: 'rgba(50, 50, 50, 0.8)', // Semi-transparent dark tooltip
                    textStyle: {
                        color: '#ffffff', // White tooltip text
                        fontSize: 14,
                        fontFamily: 'Poppins',
                    }
                },
                legend: {
                    top: '5%',
                    left: 'center',
                    textStyle: {
                        fontSize: 14,
                        fontWeight: 'bold',
                        color: '#ffffff',
                        fontFamily: 'Poppins',
                    },
                    itemWidth: 14,
                    itemHeight: 14,
                    itemGap: 10
                },
                series: [
                    {
                        name: 'Expense',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        center: ['50%', '55%'],
                        data: [
                            <?php
                            // Fetch distinct descriptions and calculate their totals
                            $query = mysqli_query($con, "SELECT b.description, b.category, SUM(b.amount) AS totalAmount 
                        FROM tblprojects p 
                        INNER JOIN tblbudget b ON p.projectName = b.subcategory 
                        WHERE p.projectID = '$projectID' AND b.category = 'Expense'
                        GROUP BY b.description
                        ORDER BY b.description DESC");

                            while ($row = mysqli_fetch_assoc($query)) {
                                echo "{ value: " . $row['totalAmount'] . ", name: '" . $row['description'] . "' },";
                            }
                            ?>
                        ],
                        label: {
                            show: true,
                            formatter: '{b}\n{d}%', // Show name and percentage on slices
                            fontSize: 14,
                            fontFamily: 'Poppins',
                            color: '#ffffff'
                        },
                        labelLine: {
                            length: 15,
                            length2: 10,
                            lineStyle: {
                                color: '#ffffff',
                                width: 1
                            }
                        },
                        itemStyle: {
                            borderColor: '#2c343c',
                            borderWidth: 6,
                        },
                        emphasis: {
                            itemStyle: {
                                shadowBlur: 20, // Larger shadow for emphasis
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.8)',
                                borderColor: '#ffffff', // Highlight border on hover
                                borderWidth: 3
                            }
                        },
                        color: [
                            '#C70039','#FF5733', '#33FF57', '#3357FF', '#FFC300', '#FFFACD', '#900C3F', '#DAF7A6', '#581845','#4A90E2', '#50E3C2', '#F8E71C', '#F5A623', '#D0011B', '#8B572A', '#417505', '#B8E986'
                        ]
                    }
                ]
            };

            expenseChart.setOption(option);

            // Make the chart responsive
            window.addEventListener('resize', function () {
                expenseChart.resize();
            });
        });
    </script>



<?php
include "footer.php";
?>