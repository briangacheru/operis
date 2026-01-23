<?php include 'head.php';?>
    <title>Financial Dashboard</title>
<?php include 'navi.php'; ?>

    <div class='container-fluid'>
        <!-- Header -->
        <div class='card shadow-none border mb-3'>
            <div class='bg-holder bg-card d-none d-md-block'
                 style='background-image:url(../assets/img/illustrations/corner-6.png);'>
            </div>
            <!--/.bg-holder-->

            <div class='card-header z-1'>
                <div class='row flex-between-center gx-0'>
                    <div class='col-lg-auto d-flex align-items-center'>
                        <h4 class='mb-0 text-primary fw-bold'>Account Distribution <span class='text-info fw-medium'>Dashboard</span>
                        </h4>
                    </div>
                    <div class='col-lg-auto pt-3 pt-lg-0'>
                        <form class="$rowTask flex-lg-column flex-xxl-$rowTask gx-3 gy-2 align-items-center align-items-lg-start align-items-xxl-center">
                            <div class='col-auto'>
                            </div>
                            <div class='col-md-auto position-relative'>
                                <h6 class='mb-1 badge rounded-pill badge-subtle-info'><?php echo date('jS F Y'); ?> |
                                    <span id="timeDisplay"></span></h6>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-none border mb-3">
            <div class='col-md-auto mt-3 mb-3 text-end'>
                <button type='button' onclick='refreshData()' class='btn btn-sm btn-outline-success me-2' data-bs-toggle='tooltip' data-bs-placement='top' title='Refresh'>
                    <i class='fas fa-sync-alt' aria-hidden='true'></i>
                    <span class='d-none d-sm-inline-block d-xl-none d-xxl-inline-block ms-1'>Refresh</span> <span class='spinner-border spinner-border-sm loading' role='status'></span>
                </button>
                <button type='button' onclick='showAddAccountModal()' class='btn btn-sm btn-outline-info me-2' data-bs-toggle='tooltip' data-bs-placement='top' title='Add Account'>
                    <i class='fas fa-plus-circle' aria-hidden='true'></i>
                    <span class='d-none d-sm-inline-block d-xl-none d-xxl-inline-block ms-1'>Add Account</span>
                </button>
                <button type='button' onclick='showUpdateBalanceModal()' class='btn btn-sm btn-outline-facebook me-2' data-bs-toggle='tooltip' data-bs-placement='top' title='Update Balances'>
                    <i class='fas fa-hand-holding-usd' aria-hidden='true'></i>
                    <span class='d-none d-sm-inline-block d-xl-none d-xxl-inline-block ms-1'>Update Balances</span>
                </button>
                <button type='button' onclick='showManageTypesModal()' class='btn btn-sm btn-outline-primary me-2' data-bs-toggle='tooltip' data-bs-placement='top' title='Manage Account Types'>
                    <i class='fas fa-cogs' aria-hidden='true'></i>
                    <span class='d-none d-sm-inline-block d-xl-none d-xxl-inline-block ms-1'>Manage Types</span>
                </button>
                <button type='button' onclick='showMonthViewer()' class='btn btn-sm btn-outline-google-plus me-2' data-bs-toggle='tooltip' data-bs-placement='top' title='View by Month'>
                    <i class='far fa-calendar-alt' aria-hidden='true'></i>
                    <span class='d-none d-sm-inline-block d-xl-none d-xxl-inline-block ms-1'>View by Month</span>
                </button>
                <button type='button' onclick='exportData()' class='btn btn-sm btn-outline-secondary me-2' data-bs-toggle='tooltip' data-bs-placement='top' title='Export'>
                    <i class='fas fa-download' aria-hidden='true'></i>
                    <span class='d-none d-sm-inline-block d-xl-none d-xxl-inline-block ms-1'>Export</span>
                </button>

            </div>
        </div>

        <!-- Summary Cards -->
        <div class='row g-3 mb-3'>
            <div class='col-md-6 col-xxl-3'>
                <div class='card account-card h-md-100'>
                    <div class='card-header d-flex flex-between-center pb-0'>
                        <h6 class='mb-0'>Total Balance</h6>
                        <i class='fas fa-wallet fa-2x opacity-75'></i>
                    </div>
                    <div class='card-body pt-2'>
                        <div class='row g-0 h-100 align-items-center'>
                            <div class='col'>
                                <div class='d-flex align-items-center'>
                                    <div>
                                        <h5 class="mb-2" id='totalBalance'>Loading...</h5>
                                        <span class='badge rounded-pill fs-11' id='monthlyGrowth'>Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-md-6 col-xxl-3'>
                <div class='card account-card h-md-100'>
                    <div class='card-header d-flex flex-between-center pb-0'>
                        <h6 class='mb-0'>Accounts</h6>
                        <i class='fas fa-university fa-2x opacity-75'></i>
                    </div>
                    <div class='card-body pt-2'>
                        <div class='row g-0 h-100 align-items-center'>
                            <div class='col'>
                                <div class='d-flex align-items-center'>
                                    <div>
                                        <h5 class='mb-2' id='activeAccounts'>Loading...</h5>
                                        <small class='opacity-75' id='inactiveAccounts'></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-md-6 col-xxl-3'>
                <div class='card account-card h-md-100'>
                    <div class='card-header d-flex flex-between-center pb-0'>
                        <h6 class='mb-0' id='savingsTitle'>Monthly Savings</h6>
                        <i class='fas fa-piggy-bank fa-2x opacity-75'></i>
                    </div>
                    <div class='card-body pt-2'>
                        <div class='row g-0 h-100 align-items-center'>
                            <div class='col'>
                                <div class='d-flex align-items-center'>
                                    <div>
                                        <h5 class="mb-2" id='monthlySavings'>Loading...</h5>
                                        <small class='opacity-75' id='savingsDetails'>Savings, MMF & SACCO</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-md-6 col-xxl-3'>
                <div class='card account-card h-md-100'>
                    <div class='card-header d-flex flex-between-center pb-0'>
                        <h6 class='mb-0'>Latest Data Month</h6>
                        <i class='fas fa-calendar-alt fa-2x opacity-75'></i>
                    </div>
                    <div class='card-body pt-2'>
                        <div class='row g-0 h-100 align-items-center'>
                            <div class='col'>
                                <div class='d-flex align-items-center'>
                                    <div>
                                        <h5 class='mb-2' id='latestDataMonth'>Loading...</h5>
                                        <small class='opacity-75' id='latestDataDetails'>-</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class='row mb-4'>
            <div class='col-lg-6'>
                <div class='card bg-line-chart-gradient'>
                    <div class='card-header bg-body-tertiary'>
                        <h5 class='mb-0'><i class='fas fa-chart-pie me-2'></i>Distribution by Account Type</h5>
                    </div>
                    <div class='card-body'>
                        <div class='chart-container'>
                            <div id='pieChart'></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-lg-6'>
                <div class='card bg-line-chart-gradient'>
                    <div class='card-header bg-body-tertiary'>
                        <h5 class='mb-0'><i class='fas fa-chart-bar me-2'></i>Accounts Balance Comparison</h5>
                    </div>
                    <div class='card-body'>
                        <div class='chart-container'>
                            <div id='barChart'></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='row mb-4'>
            <div class='col-12'>
                <div class='card bg-line-chart-gradient'>
                    <div class='card-header bg-body-tertiary'>
                        <h5 class='mb-0'><i class='fas fa-chart-line me-2'></i>Monthly Growth Trend</h5>
                    </div>
                    <div class='card-body'>
                        <div class='chart-container'>
                            <div id='growthChart'></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class='row'>
            <div class='col-12'>
                <div class='card'>
                    <div class='card-header bg-body-tertiary d-flex justify-content-between align-items-center'>
                        <h5 class='mb-0'><i class='fas fa-table me-2'></i>Accounts Details</h5>
                        <div class='input-group' style='width: 300px;'>
                            <input type='text' class='form-control' placeholder='Search accounts...' id='searchInput'>
                            <button class='btn btn-outline-light' type='button' onclick='searchAccounts()'>
                                <i class='fas fa-search'></i>
                            </button>
                        </div>
                    </div>
                    <div class='card-body p-0'>
                        <div class='table-responsive'>
                            <table class='table table-hover mb-0 table-border fs-10 border-200 overflow-hidden rounded-3' id='accountsTable'>
                                <thead class='bg-body-tertiary'>
                                <tr>
                                    <th scope='col' onclick='sortTable(0)' style='cursor: pointer;'>
                                        Account Name <i class='fas fa-sort'></i>
                                    </th>
                                    <th scope='col' onclick='sortTable(1)' style='cursor: pointer;'>
                                        Type <i class='fas fa-sort'></i>
                                    </th>
                                    <th scope='col' onclick='sortTable(1)' style='cursor: pointer;'>
                                        Bank Name <i class='fas fa-sort'></i>
                                    </th>
                                    <th scope='col' onclick='sortTable(2)' style='cursor: pointer;'>
                                        Balance <i class='fas fa-sort'></i>
                                    </th>
                                    <th scope='col'>Distribution</th>
                                    <th scope='col' onclick='sortTable(4)' style='cursor: pointer;'>
                                        As at <i class='fas fa-sort'></i>
                                    </th>
                                    <th scope='col'>Status</th>
                                    <th scope='col'>Actions</th>
                                </tr>
                                </thead>
                                <tbody id='accountsTableBody'>
                                <tr>
                                    <td colspan='8' class='text-center py-4'>
                                        <div class='spinner-border text-primary' role='status'>
                                            <span class='visually-hidden'>Loading...</span>
                                        </div>
                                        <p class='mt-2'>Loading accounts...</p>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class='card-footer bg-body-tertiary'>
                        <div class='d-flex justify-content-between align-items-center'>
                            <small class='text-muted' id='accountCount'>Loading accounts...</small>
                            <nav>
                                <ul class='pagination pagination-sm mb-0'>
                                    <li class='page-item disabled'><a class='page-link' href='#'>Previous</a></li>
                                    <li class='page-item active'><a class='page-link' href='#'>1</a></li>
                                    <li class='page-item disabled'><a class='page-link' href='#'>Next</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New Account Modal -->
    <div class='modal fade' id='addAccountModal' tabindex='-1'>
        <div class='modal-dialog modal-lg'>
            <div class='modal-content'>
                <div class='modal-header px-5 position-relative modal-shape-header bg-shape'>
                    <div class='position-relative z-1'>
                        <h4 class='mb-0 text-white'>Add New Account</h4>
                    </div>
                    <div data-bs-theme='dark'>
                        <button class='btn-close position-absolute top-0 end-0 mt-2 me-2' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                </div>
                <div class='modal-body'>
                    <form id='addAccountForm'>
                        <div class='row'>
                            <!-- Account Name -->
                            <div class='col-md-6 mb-3'>
                                <label for='accountName' class='form-label'>
                                    <i class='fas fa-tag text-primary'></i> Account Name *
                                </label>
                                <input type='text' class='form-control' id='accountName'
                                       placeholder='e.g., Main Checking Account' required maxlength='100'>
                                <div class='form-text'>Choose a descriptive name for your account</div>
                            </div>

                            <!-- Account Type -->
                            <div class='col-md-6 mb-3'>
                                <label for='accountType' class='form-label'>
                                    <i class='fas fa-list text-primary'></i> Account Type *
                                </label>
                                <select class='form-select' id='accountType' required>
                                    <option value=''>Select Account Type</option>
                                </select>
                                <div class='form-text'>Select the type of account</div>
                            </div>

                            <!-- Initial Balance -->
                            <div class='col-md-6 mb-3'>
                                <label for='initialBalance' class='form-label'>
                                    <i class='fas fa-dollar-sign text-success'></i> Initial Balance *
                                </label>
                                <div class='input-group'>
                                    <span class='input-group-text'>$</span>
                                    <input type='number' step='0.01' class='form-control' id='initialBalance'
                                           placeholder='0.00' required min='0'>
                                </div>
                                <div class='form-text'>Enter the current balance for this account</div>
                            </div>

                            <!-- Bank Name -->
                            <div class='col-md-6 mb-3'>
                                <label for='bankName' class='form-label'>
                                    <i class='fas fa-university text-primary'></i> Bank/Institution
                                </label>
                                <input type='text' class='form-control' id='bankName'
                                       placeholder='e.g., Chase Bank' maxlength='100'>
                                <div class='form-text'>Optional: Name of the bank or financial institution</div>
                            </div>

                            <!-- Account Number -->
                            <div class='col-md-6 mb-3'>
                                <label for='accountNumber' class='form-label'>
                                    <i class='fas fa-hashtag text-primary'></i> Account Number
                                </label>
                                <input type='text' class='form-control' id='accountNumber'
                                       placeholder='****1234' maxlength='50'>
                                <div class='form-text'>Optional: Last 4 digits or masked account number</div>
                            </div>

                            <!-- Interest Rate -->
                            <div class='col-md-6 mb-3'>
                                <label for='interestRate' class='form-label'>
                                    <i class='fas fa-percentage text-success'></i> Interest Rate (%)
                                </label>
                                <div class='input-group'>
                                    <input type='number' step='0.01' class='form-control' id='interestRate'
                                           placeholder='0.00' min='0' max='100'>
                                    <span class='input-group-text'>%</span>
                                </div>
                                <div class='form-text'>Optional: Annual interest rate</div>
                            </div>

                            <!-- Minimum Balance -->
                            <div class='col-md-6 mb-3'>
                                <label for='minimumBalance' class='form-label'>
                                    <i class='fas fa-exclamation-triangle text-warning'></i> Minimum Balance
                                </label>
                                <div class='input-group'>
                                    <span class='input-group-text'>$</span>
                                    <input type='number' step='0.01' class='form-control' id='minimumBalance'
                                           placeholder='0.00' min='0'>
                                </div>
                                <div class='form-text'>Optional: Required minimum balance</div>
                            </div>

                            <!-- Status -->
                            <div class='col-md-6 mb-3'>
                                <label for='accountStatus' class='form-label'>
                                    <i class='fas fa-toggle-on text-success'></i> Status
                                </label>
                                <select class='form-select' id='accountStatus' required>
                                    <option value='Active' selected>Active</option>
                                    <option value='Inactive'>Inactive</option>
                                    <option value='Locked'>Locked</option>
                                    <option value='Low Balance'>Low Balance</option>
                                    <option value='Debt'>Debt</option>
                                </select>
                                <div class='form-text'>Current status of the account</div>
                            </div>

                            <!-- Notes -->
                            <div class='col-12 mb-3'>
                                <label for='accountNotes' class='form-label'>
                                    <i class='fas fa-sticky-note text-info'></i> Notes
                                </label>
                                <textarea class='form-control' id='accountNotes' rows='3'
                                          placeholder='Optional notes about this account...' maxlength='500'></textarea>
                                <div class='form-text'>Optional: Additional information about this account</div>
                            </div>
                        </div>

                        <!-- Summary Card -->
                        <div class='card border-0 mt-3' id='accountSummaryCard' style='display: none;'>
                            <div class='card-body py-2'>
                                <h6 class='card-title mb-2'>
                                    <i class='fas fa-eye text-primary'></i> Account Preview
                                </h6>
                                <div class='row'>
                                    <div class='col-md-6'>
                                        <small class='text-muted'>Account Name:</small>
                                        <div class='fw-bold' id='previewAccountName'>-</div>
                                    </div>
                                    <div class='col-md-6'>
                                        <small class='text-muted'>Initial Balance:</small>
                                        <div class='fw-bold text-success' id='previewBalance'>$0.00</div>
                                    </div>
                                    <div class='col-md-6 mt-2'>
                                        <small class='text-muted'>Account Type:</small>
                                        <div class='fw-bold' id='previewAccountType'>-</div>
                                    </div>
                                    <div class='col-md-6 mt-2'>
                                        <small class='text-muted'>Bank:</small>
                                        <div class='fw-bold' id='previewBank'>-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>
                        <i class='fas fa-times'></i> Cancel
                    </button>
                    <button type='button' class='btn btn-primary' onclick='addAccount()'>
                        <i class='fas fa-save'></i> Create Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Balance Modal -->
    <div class='modal fade' id='updateBalanceModal' tabindex='-1'>
        <div class='modal-dialog modal-xl'>
            <div class='modal-content'>
                <div class='modal-header px-5 position-relative modal-shape-header bg-shape'>
                    <div class='position-relative z-1'>
                        <h4 class='mb-0 text-white'>Update Monthly Balances</h4>
                    </div>
                    <div data-bs-theme='dark'>
                        <button class='btn-close position-absolute top-0 end-0 mt-2 me-2' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                </div>
                <div class='modal-body'>
                    <div class='mb-3'>
                        <label for='updateMonth' class='form-label'>Month/Year</label>
                        <input type='month' class='form-control' id='updateMonth' required>
                    </div>
                    <div id='balanceUpdateList'></div>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                    <button type='button' class='btn btn-primary' onclick='saveBalanceUpdates()'>Save Updates</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Account Types Modal -->
    <div class='modal fade' id='manageTypesModal' tabindex='-1'>
        <div class='modal-dialog modal-lg'>
            <div class='modal-content'>
                <div class='modal-header px-5 position-relative modal-shape-header bg-shape'>
                    <div class='position-relative z-1'>
                        <h4 class='mb-0 text-white'>Manage Account Types</h4>
                    </div>
                    <div data-bs-theme='dark'>
                        <button class='btn-close position-absolute top-0 end-0 mt-2 me-2' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                </div>
                <div class='modal-body'>
                    <div class='row mb-3'>
                        <div class='col-md-6'>
                            <h6>Add New Type</h6>
                            <form id='addTypeForm'>
                                <div class='mb-2'>
                                    <input type='text' class='form-control' id='newTypeName' placeholder='Type Name' required>
                                </div>
                                <div class='mb-2'>
                                    <input type='color' class='form-control' id='newTypeColor' value='#007bff' required>
                                </div>
                                <div class='mb-2'>
                                    <input type='text' class='form-control' id='newTypeIcon' placeholder='Icon Class (e.g., fas fa-wallet)' value='fas fa-wallet'>
                                </div>
                                <button type='button' class='btn btn-success btn-sm' onclick='addAccountType()'>Add Type</button>
                            </form>
                        </div>
                        <div class='col-md-6'>
                            <h6>Existing Types</h6>
                            <div id='existingTypesList'></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Month Balance Viewer Modal -->
    <div class='modal fade' id='monthViewerModal' tabindex='-1'>
        <div class='modal-dialog modal-xl'>
            <div class='modal-content'>
                <div class='modal-header px-5 position-relative modal-shape-header bg-shape'>
                    <div class='position-relative z-1'>
                        <h4 class='mb-0 text-white'>View Balances By Month</h4>
                    </div>
                    <div data-bs-theme='dark'>
                        <button class='btn-close position-absolute top-0 end-0 mt-2 me-2' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                </div>
                <div class='modal-body'>
                    <!-- Month Selection -->
                    <div class='row mb-4'>
                        <div class='col-md-4'>
                            <label for='viewMonth' class='form-label'>Select Month</label>
                            <select class='form-select' id='viewMonth' onchange='loadBalancesByMonth()'>
                                <option value=''>Select Month</option>
                            </select>
                        </div>
                        <div class='col-md-4'>
                            <label class='form-label'>Quick Actions</label>
                            <div class='btn-group d-block'>
                                <button class='btn btn-outline-primary btn-sm' onclick='loadCurrentMonth()'>Current Month</button>
                                <button class='btn btn-outline-secondary btn-sm' onclick='loadPreviousMonth()'>Previous Month</button>
                            </div>
                        </div>
                        <div class='col-md-4'>
                            <label class='form-label'>Compare Months</label>
                            <button class='btn btn-outline-info btn-sm d-block' onclick='showMonthComparison()'>
                                <i class='fas fa-chart-line'></i> Compare Months
                            </button>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class='row mb-4 summary-cards-row' id='monthSummaryCards' style='display: none;'>
                        <div class='col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 mb-3'>
                            <div class='card bg-body-secondary summary-card'>
                                <div class='card-body text-center d-flex flex-column justify-content-center'>
                                    <h6 class='card-title mb-2'>Total Balance</h6>
                                    <h5 class='mb-0' id='monthTotalBalance'>$0</h5>
                                </div>
                            </div>
                        </div>
                        <div class='col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 mb-3'>
                            <div class='card bg-body-secondary summary-card'>
                                <div class='card-body text-center d-flex flex-column justify-content-center'>
                                    <h6 class='card-title mb-2'>Total Growth</h6>
                                    <h5 class='mb-0' id='monthTotalGrowth'>$0</h5>
                                </div>
                            </div>
                        </div>
                        <div class='col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 mb-3'>
                            <div class='card bg-body-secondary summary-card'>
                                <div class='card-body text-center d-flex flex-column justify-content-center'>
                                    <h6 class='card-title mb-2'>Accounts with Data</h6>
                                    <h5 class='mb-0' id='monthAccountsCount'>0</h5>
                                </div>
                            </div>
                        </div>
                        <div class='col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12 mb-3'>
                            <div class='card bg-body-secondary summary-card'>
                                <div class='card-body text-center d-flex flex-column justify-content-center'>
                                    <h6 class='card-title mb-2'>Selected Month</h6>
                                    <h5 class='mb-0' id='selectedMonthDisplay'>-</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Accounts Table -->
                    <div class='table-responsive'>
                        <table class='table table-hover' id='monthBalancesTable' style='display: none;'>
                            <thead class='table-primary'>
                            <tr>
                                <th>Account Name</th>
                                <th>Type</th>
                                <th>Balance</th>
                                <th>Growth Amount</th>
                                <th>Growth %</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id='monthBalancesTableBody'>
                            </tbody>
                        </table>
                    </div>

                    <!-- No Data Message -->
                    <div id='noMonthDataMessage' class='text-center py-4' style='display: none;'>
                        <i class='fas fa-calendar-times fa-3x text-muted mb-3'></i>
                        <h5>No data available</h5>
                        <p class='text-muted'>Select a month to view balance data</p>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                    <button type='button' class='btn btn-primary' onclick='exportMonthData()'>
                        <i class='fas fa-download'></i> Export Month Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Month Comparison Modal -->
    <div class='modal fade' id='monthComparisonModal' tabindex='-1'>
        <div class='modal-dialog modal-lg'>
            <div class='modal-content'>
                <div class='modal-header px-5 position-relative modal-shape-header bg-shape'>
                    <div class='position-relative z-1'>
                        <h4 class='mb-0 text-white'>Compare Monthly Balances</h4>
                    </div>
                    <div data-bs-theme='dark'>
                        <button class='btn-close position-absolute top-0 end-0 mt-2 me-2' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                </div>
                <div class='modal-body'>
                    <div class='row mb-3'>
                        <div class='col-md-6'>
                            <label for='startMonth' class='form-label'>Start Month</label>
                            <select class='form-select' id='startMonth'>
                                <option value=''>Select Start Month</option>
                            </select>
                        </div>
                        <div class='col-md-6'>
                            <label for='endMonth' class='form-label'>End Month</label>
                            <select class='form-select' id='endMonth'>
                                <option value=''>Select End Month</option>
                            </select>
                        </div>
                    </div>
                    <div class='text-center mb-3'>
                        <button class='btn btn-primary' onclick='loadMonthComparison()'>
                            <i class='fas fa-chart-line'></i> Compare
                        </button>
                    </div>
                    <div id='comparisonResults'></div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Account Details Modal -->
    <div class='modal fade' id='viewAccountModal' tabindex='-1'>
        <div class='modal-dialog modal-lg'>
            <div class='modal-content'>
                <div class='modal-header px-5 position-relative modal-shape-header bg-shape'>
                    <div class='position-relative z-1'>
                        <h4 class='mb-0 text-white'>Account Details</h4>
                    </div>
                    <div data-bs-theme='dark'>
                        <button class='btn-close position-absolute top-0 end-0 mt-2 me-2' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                </div>
                <div class='modal-body'>
                    <div class='row'>
                        <!-- Account Information Card -->
                        <div class='col-md-6 mb-3'>
                            <div class='card h-100'>
                                <div class='card-header bg-body-tertiary'>
                                    <h6 class='mb-0'><i class='fas fa-info-circle me-2'></i>Account Information</h6>
                                </div>
                                <div class='card-body'>
                                    <div class='row mb-2'>
                                        <div class='col-5'><strong>Name:</strong></div>
                                        <div class='col-7' id='viewAccountName'>-</div>
                                    </div>
                                    <div class='row mb-2'>
                                        <div class='col-5'><strong>Type:</strong></div>
                                        <div class='col-7' id='viewAccountType'>-</div>
                                    </div>
                                    <div class='row mb-2'>
                                        <div class='col-5'><strong>Status:</strong></div>
                                        <div class='col-7' id='viewAccountStatus'>-</div>
                                    </div>
                                    <div class='row mb-2'>
                                        <div class='col-5'><strong>Created:</strong></div>
                                        <div class='col-7' id='viewAccountCreated'>-</div>
                                    </div>
                                    <div class='row mb-2'>
                                        <div class='col-5'><strong>Last Updated:</strong></div>
                                        <div class='col-7' id='viewAccountUpdated'>-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information Card -->
                        <div class='col-md-6 mb-3'>
                            <div class='card h-100'>
                                <div class='card-header bg-body-tertiary'>
                                    <h6 class='mb-0'><i class='fas fa-dollar-sign me-2'></i>Financial Information</h6>
                                </div>
                                <div class='card-body'>
                                    <div class='row mb-2'>
                                        <div class='col-6'><strong>Current Balance:</strong></div>
                                        <div class='col-6'>
                                            <span class='fw-bold text-success' id='viewCurrentBalance'>$0.00</span>
                                        </div>
                                    </div>
                                    <div class='row mb-2'>
                                        <div class='col-6'><strong>Interest Rate:</strong></div>
                                        <div class='col-6' id='viewInterestRate'>0%</div>
                                    </div>
                                    <div class='row mb-2'>
                                        <div class='col-6'><strong>Minimum Balance:</strong></div>
                                        <div class='col-6' id='viewMinimumBalance'>$0.00</div>
                                    </div>
                                    <div class='row mb-2'>
                                        <div class='col-6'><strong>Growth This Month:</strong></div>
                                        <div class='col-6'>
                                            <span id='viewGrowthAmount' class='fw-bold'>$0.00</span>
                                            <small class='text-muted'>(<span id='viewGrowthPercent'>0%</span>)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Information Card -->
                        <div class='col-md-6 mb-3'>
                            <div class='card h-100'>
                                <div class='card-header bg-body-tertiary'>
                                    <h6 class='mb-0'><i class='fas fa-university me-2'></i>Bank Information</h6>
                                </div>
                                <div class='card-body'>
                                    <div class='row mb-2'>
                                        <div class='col-5'><strong>Bank Name:</strong></div>
                                        <div class='col-7' id='viewBankName'>-</div>
                                    </div>
                                    <div class='row mb-2'>
                                        <div class='col-5'><strong>Account Number:</strong></div>
                                        <div class='col-7' id='viewAccountNumber'>-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Card -->
                        <div class='col-md-6 mb-3'>
                            <div class='card h-100'>
                                <div class='card-header bg-body-tertiary'>
                                    <h6 class='mb-0'><i class='fas fa-sticky-note me-2'></i>Notes</h6>
                                </div>
                                <div class='card-body'>
                                    <div id='viewAccountNotes' class='text-muted'>No notes available</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance History Section -->
                    <div class='card mt-3'>
                        <div class='card-header bg-body-tertiary'>
                            <h6 class='mb-0'><i class='fas fa-history me-2'></i>Recent Balance History</h6>
                        </div>
                        <div class='card-body p-0'>
                            <div class='table-responsive scrollbar'>
                                <table class='table table-sm mb-0 border-200 table-border'>
                                    <thead class='bg-200'>
                                    <tr>
                                        <th>Month</th>
                                        <th>Balance</th>
                                        <th>Growth</th>
                                        <th>Growth %</th>
                                        <th>Notes</th>
                                    </tr>
                                    </thead>
                                    <tbody id='viewBalanceHistory'>
                                    <tr>
                                        <td colspan='5' class='text-center text-muted'>Loading balance history...</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>
                        <i class='fas fa-times'></i> Close
                    </button>
                    <button type='button' class='btn btn-primary' onclick='editAccountFromView()'>
                        <i class='fas fa-edit'></i> Edit Account
                    </button>
                    <button type='button' class='btn btn-success' onclick='viewBalanceHistoryModal()'>
                        <i class='fas fa-chart-line'></i> View Full History
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div class='modal fade' id='editAccountModal' tabindex='-1'>
        <div class='modal-dialog modal-lg'>
            <div class='modal-content'>
                <div class='modal-header px-5 position-relative modal-shape-header bg-shape'>
                    <div class='position-relative z-1'>
                        <h4 class='mb-0 text-white'>Edit Account Details</h4>
                    </div>
                    <div data-bs-theme='dark'>
                        <button class='btn-close position-absolute top-0 end-0 mt-2 me-2' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                </div>
                <div class='modal-body'>
                    <form id='editAccountForm'>
                        <input type='hidden' id='editAccountId'>

                        <div class='row'>
                            <!-- Basic Information -->
                            <div class='col-md-6 mb-3'>
                                <label for='editAccountName' class='form-label'>
                                    <i class='fas fa-tag text-primary'></i> Account Name *
                                </label>
                                <input type='text' class='form-control' id='editAccountName' required maxlength='100'>
                            </div>

                            <div class='col-md-6 mb-3'>
                                <label for='editAccountType' class='form-label'>
                                    <i class='fas fa-list text-primary'></i> Account Type *
                                </label>
                                <select class='form-select' id='editAccountType' required>
                                    <option value=''>Select Account Type</option>
                                </select>
                            </div>

                            <!-- Bank Information -->
                            <div class='col-md-6 mb-3'>
                                <label for='editBankName' class='form-label'>
                                    <i class='fas fa-university text-primary'></i> Bank Name
                                </label>
                                <input type='text' class='form-control' id='editBankName' maxlength='100'>
                            </div>

                            <div class='col-md-6 mb-3'>
                                <label for='editAccountNumber' class='form-label'>
                                    <i class='fas fa-hashtag text-primary'></i> Account Number
                                </label>
                                <input type='text' class='form-control' id='editAccountNumber' maxlength='50'>
                            </div>

                            <!-- Financial Information -->
                            <div class='col-md-6 mb-3'>
                                <label for='editInterestRate' class='form-label'>
                                    <i class='fas fa-percentage text-success'></i> Interest Rate (%)
                                </label>
                                <div class='input-group'>
                                    <input type='number' step='0.01' class='form-control' id='editInterestRate' min='0'
                                           max='100'>
                                    <span class='input-group-text'>%</span>
                                </div>
                            </div>

                            <div class='col-md-6 mb-3'>
                                <label for='editMinimumBalance' class='form-label'>
                                    <i class='fas fa-exclamation-triangle text-warning'></i> Minimum Balance
                                </label>
                                <div class='input-group'>
                                    <span class='input-group-text'>$</span>
                                    <input type='number' step='0.01' class='form-control' id='editMinimumBalance'
                                           min='0'>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class='col-md-6 mb-3'>
                                <label for='editAccountStatus' class='form-label'>
                                    <i class='fas fa-toggle-on text-success'></i> Status
                                </label>
                                <select class='form-select' id='editAccountStatus'>
                                    <option value='Active'>Active</option>
                                    <option value='Inactive'>Inactive</option>
                                    <option value='Locked'>Locked</option>
                                    <option value='Low Balance'>Low Balance</option>
                                    <option value='Debt'>Debt</option>
                                </select>
                            </div>

                            <!-- Notes -->
                            <div class='col-12 mb-3'>
                                <label for='editAccountNotes' class='form-label'>
                                    <i class='fas fa-sticky-note text-info'></i> Notes
                                </label>
                                <textarea class='form-control' id='editAccountNotes' rows='3'
                                          maxlength='500'></textarea>
                            </div>
                        </div>

                        <!-- Current Balance Info (Read-only) -->
                        <div class='card border-0 mt-3'>
                            <div class='card-body py-2'>
                                <h6 class='card-title mb-2'>
                                    <i class='fas fa-info-circle text-info'></i> Current Balance Information
                                </h6>
                                <div class='row'>
                                    <div class='col-md-6'>
                                        <small class='text-muted'>Current Balance:</small>
                                        <div class='fw-bold text-success' id='editCurrentBalanceDisplay'>$0.00</div>
                                    </div>
                                    <div class='col-md-6'>
                                        <small class='text-muted'>Last Balance Update:</small>
                                        <div class='fw-bold' id='editLastBalanceUpdate'>-</div>
                                    </div>
                                </div>
                                <div class='alert alert-info mt-2 mb-0'>
                                    <small><i class='fas fa-info-circle'></i> To update the balance, use the 'Update
                                        Balances' feature from the main dashboard.</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>
                        <i class='fas fa-times'></i> Cancel
                    </button>
                    <button type='button' class='btn btn-danger me-auto' onclick='deleteAccountFromEdit()'>
                        <i class='fas fa-trash'></i> Delete Account
                    </button>
                    <button type='button' class='btn btn-primary' onclick='saveAccountChanges()'>
                        <i class='fas fa-save'></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance History Modal -->
    <div class='modal fade' id='balanceHistoryModal' tabindex='-1'>
        <div class='modal-dialog modal-xl'>
            <div class='modal-content'>
                <div class='modal-header px-5 position-relative modal-shape-header bg-shape'>
                    <div class='position-relative z-1'>
                        <h4 class='mb-0 text-white'>Balance History - <span id='historyAccountName'></span></h4>
                    </div>
                    <div data-bs-theme='dark'>
                        <button class='btn-close position-absolute top-0 end-0 mt-2 me-2' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                </div>
                <div class='modal-body'>
                    <!-- Balance Chart -->
                    <div class='card bg-line-chart-gradient mb-3'>
                        <div class='card-header'>
                            <h6 class='mb-0'><i class='fas fa-chart-area me-2'></i>Balance Trend</h6>
                        </div>
                        <div class='card-body'>
                            <div class='chart-container'>
                                <div id='accountBalanceChart'></div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance History Table -->
                    <div class='card'>
                        <div class='card-header d-flex justify-content-between align-items-center'>
                            <h6 class='mb-0'><i class='fas fa-table me-2'></i>Detailed History</h6>
                            <button class='btn btn-sm btn-outline-primary' onclick='exportBalanceHistory()'>
                                <i class='fas fa-download'></i> Export
                            </button>
                        </div>
                        <div class='card-body p-0'>
                            <div class='table-responsive'>
                                <table class='table table-hover mb-0'>
                                    <thead class='table-dark'>
                                    <tr>
                                        <th>Month/Year</th>
                                        <th>Balance</th>
                                        <th>Growth Amount</th>
                                        <th>Growth %</th>
                                        <th>Notes</th>
                                        <th>Date Added</th>
                                    </tr>
                                    </thead>
                                    <tbody id='fullBalanceHistory'>
                                    <tr>
                                        <td colspan='6' class='text-center'>Loading balance history...</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>
                        <i class='fas fa-times'></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script>
        // Configuration - Update these URLs to match your API endpoints
        const API_BASE_URL = 'accounts_api';

        // Global variables
        let accountsData = [];
        let pieChartInstance, barChartInstance;
        let totalBalance = 0;
        let growthChart;
        let accountTypes = [];
        let currentAccount = null;
        let accountBalanceChart = null;
        let accountBalanceChartInstance;

        // Initialize dashboard on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
        });

        // Load all dashboard data
        async function loadDashboardData() {
            try {
                await Promise.all([
                    loadSummaryData(),
                    loadAccountsData(),
                    loadDistributionData(),
                    loadGrowthData(),
                    loadLatestMonthData()
                ]);
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                showError('Failed to load dashboard data. Please check your API connection.');
            }
        }

        function updateMonthlyGrowthDisplay(data) {
            const growthValue = parseFloat(data.monthly_growth) || 0;
            const growthAmount = parseFloat(data.growth_amount) || 0;
            const growthElement = document.getElementById('monthlyGrowth');

            // Format the display
            const growthSign = growthValue >= 0 ? '+' : '';
            const amountSign = growthAmount >= 0 ? '+' : '';

            // Update content
            growthElement.textContent = `${growthSign}${growthValue.toFixed(1)}% | ${amountSign}${formatCurrency(growthAmount)}`;

            // Reset classes and add base classes
            growthElement.className = 'badge rounded-pill fs-11';

            // Add conditional styling classes
            if (growthValue > 0) {
                growthElement.classList.add('badge-subtle-success');
            } else if (growthValue < 0) {
                growthElement.classList.add('badge-subtle-danger');
            } else {
                growthElement.classList.add('badge-subtle-secondary');
            }

            // Update tooltip
            growthElement.title = `Growth from previous month: ${amountSign}${formatCurrency(growthAmount)} (${growthSign}${growthValue.toFixed(2)}%)`;
        }

        // Load summary data for cards
        async function loadSummaryData() {
            try {
                const response = await fetch(`${API_BASE_URL}?action=summary`);
                const data = await response.json();

                document.getElementById('totalBalance').textContent = formatCurrency(data.total_balance);
                document.getElementById('activeAccounts').textContent = data.active_accounts + ' Active';
                document.getElementById('inactiveAccounts').textContent = data.inactive_accounts + ' Inactive';
                document.getElementById('monthlySavings').textContent = formatCurrency(data.monthly_savings || 0);
                document.getElementById('monthlySavings').className = (data.monthly_savings || 0) >= 0 ? 'mb-2 text-success' : 'mb-2 text-danger';

                // Update the title to show the specific month
                document.getElementById('savingsTitle').textContent = data.savings_month_display === 'No Data' ?
                    'Monthly Savings' :
                    data.savings_month_display + ' Savings';

                // Update the details to show account types
                document.getElementById('savingsDetails').textContent = 'Savings, MMF & Sacco';

                // Use the enhanced monthly growth display
                updateMonthlyGrowthDisplay(data);

                totalBalance = data.total_balance;
            } catch (error) {
                console.error('Error loading summary:', error);
                // Set fallback values on error
                document.getElementById('savingsTitle').textContent = 'Monthly Savings';
                document.getElementById('activeAccounts').textContent = 'Error';
                document.getElementById('inactiveAccounts').textContent = 'Error';
            }
        }

        // Load accounts data for table
        async function loadAccountsData() {
            try {
                const response = await fetch(API_BASE_URL);
                accountsData = await response.json();

                renderAccountsTable(accountsData);
                updateAccountCount(accountsData.length);
            } catch (error) {
                console.error('Error loading accounts:', error);
                document.getElementById('accountsTableBody').innerHTML =
                    '<tr><td colspan="7" class="text-center text-danger">Error loading accounts</td></tr>';
            }
        }

        // Load distribution data for charts
        async function loadDistributionData() {
            try {
                const response = await fetch(`${API_BASE_URL}?action=distribution`);
                const data = await response.json();

                renderCharts(data);
            } catch (error) {
                console.error('Error loading distribution:', error);
            }
        }

        // Add this function to populate account types dropdown
        async function populateAccountTypesDropdown() {
            try {
                const response = await fetch(`${API_BASE_URL}?action=account_types`);
                const types = await response.json();

                const select = document.getElementById('accountType');
                select.innerHTML = '<option value="">Select Type</option>';

                types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.type_name;
                    option.textContent = type.type_name;
                    option.style.color = type.color_code;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading account types:', error);
            }
        }

        // Update the showAddAccountModal function
        async function showAddAccountModal() {
            // Clear form
            document.getElementById('addAccountForm').reset();
            document.getElementById('accountSummaryCard').style.display = 'none';

            // Load account types
            await loadAccountTypesForModal();

            // Add real-time preview functionality
            setupAccountPreview();

            // Show modal
            new bootstrap.Modal(document.getElementById('addAccountModal')).show();
        }
        // Load account types for the modal
        async function loadAccountTypesForModal() {
            try {
                const response = await fetch(`${API_BASE_URL}?action=account_types`);
                const accountTypes = await response.json();

                const select = document.getElementById('accountType');
                select.innerHTML = '<option value="">Select Account Type</option>';

                accountTypes.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.type_name;
                    option.textContent = type.type_name;
                    option.setAttribute('data-color', type.color_code);
                    option.setAttribute('data-icon', type.icon_class);
                    select.appendChild(option);
                });

            } catch (error) {
                console.error('Error loading account types:', error);
                showError('Failed to load account types');
            }
        }
        // Setup real-time account preview
        function setupAccountPreview() {
            const inputs = ['accountName', 'initialBalance', 'accountType', 'bankName'];

            inputs.forEach(inputId => {
                const input = document.getElementById(inputId);
                input.addEventListener('input', updateAccountPreview);
                input.addEventListener('change', updateAccountPreview);
            });
        }
        // Update account preview in real-time
        function updateAccountPreview() {
            const accountName = document.getElementById('accountName').value;
            const initialBalance = parseFloat(document.getElementById('initialBalance').value) || 0;
            const accountType = document.getElementById('accountType').value;
            const bankName = document.getElementById('bankName').value;

            const summaryCard = document.getElementById('accountSummaryCard');

            // Show preview if at least name and balance are provided
            if (accountName || initialBalance > 0) {
                summaryCard.style.display = 'block';

                document.getElementById('previewAccountName').textContent = accountName || 'Unnamed Account';
                document.getElementById('previewBalance').textContent = formatCurrency(initialBalance);
                document.getElementById('previewAccountType').textContent = accountType || 'Not selected';
                document.getElementById('previewBank').textContent = bankName || 'Not specified';
            } else {
                summaryCard.style.display = 'none';
            }
        }

        // Render accounts table
        function renderAccountsTable(accounts) {
            const tbody = document.getElementById('accountsTableBody');

            if (accounts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2"></i><div>No accounts found</div></td></tr>';
                return;
            }

            tbody.innerHTML = accounts.map(account => {
                const percentage = totalBalance > 0 ? (Math.abs(account.balance) / totalBalance * 100).toFixed(1) : 0;
                const balanceClass = account.balance >= 0 ? 'balance-positive' : 'balance-negative';
                const statusBadge = getStatusBadge(account.status);
                const typeBadge = getTypeBadge(account.account_type, account.color_code, account.icon_class);

                return `
                    <tr>
                        <td><strong>${account.account_name}</strong></td>
                        <td>${typeBadge}</td>
                        <td><strong>${account.bank_name}</strong></td>
                        <td class="${balanceClass}"><strong>${formatCurrency(account.balance)}</strong></td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar ${getProgressBarClass(account.account_type)}"
                                     style="width: ${percentage}%">${percentage}%</div>
                            </div>
                        </td>
                        <td>${account.last_balance_update ? formatBalanceDate(account.last_balance_update) : 'No balance data'}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary me-1 mb-2" data-bs-toggle="tooltip" data-bs-placement="top" title="View Account" onclick="viewDetails(${account.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle='tooltip' data-bs-placement='top' title='Edit Account' onclick="editAccount(${account.id})">
                                <i class="fas fa-edit"></i>
                            </button>

                        </td>
                    </tr>
                `;
            }).join('');
        }

        function formatBalanceDate(dateString) {
            if (!dateString) return 'No balance data';

            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                year: 'numeric'
            });
        }

        // Render charts
        function renderCharts(distributionData) {
            const labels = distributionData.map(item => item.account_type);
            const data = distributionData.map(item => parseFloat(item.total_balance));
            const colors = distributionData.map(item => item.color_code || '#007bff');
            const total = data.reduce((sum, val) => sum + val, 0);

            // Destroy existing ApexCharts instances
            if (pieChartInstance) pieChartInstance.destroy();
            if (barChartInstance) barChartInstance.destroy();

            // ==== PIE CHART: Account Type Distribution ====
            const pieOptions = {
                chart: {
                    type: 'donut',
                    height: 350,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    },
                    toolbar: {
                        show: false // we’re using custom buttons
                    }
                },
                series: data,
                labels: labels,
                colors: colors,
                dataLabels: {
                    enabled: true,
                    formatter: (val) => val.toFixed(1) + '%',
                    style: {colors: ['#fff']}
                },
                tooltip: {
                    y: {
                        formatter: (val) => {
                            const percent = (val / total) * 100;
                            return `${formatCurrency(val)} (${percent.toFixed(1)}%)`;
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        colors: '#ffffff'
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: () => formatCurrency(total),
                                    color: '#ffffff'
                                }
                            }
                        }
                    }
                }
            };

            pieChartInstance = new ApexCharts(document.querySelector('#pieChart'), pieOptions);
            pieChartInstance.render();

            // === BAR CHART: Individual Active Accounts ===
            fetch('accounts_api?action=getAccounts')
                .then(response => response.json())
                .then(accounts => {
                    const activeAccounts = accounts
                        .filter(account => account.status !== 'Inactive')
                        .sort((a, b) => b.balance - a.balance);

                    // Map account type to color
                    const colorMap = {};
                    distributionData.forEach(item => {
                        colorMap[item.account_type] = item.color_code || '#007bff';
                    });

                    const barData = activeAccounts.map(acc => ({
                        x: acc.account_name,
                        y: parseFloat(acc.balance),
                        fillColor: colorMap[acc.account_type] || '#007bff'
                    }));

                    const totalBar = barData.reduce((sum, bar) => sum + bar.y, 0);

                    const barOptions = {
                        chart: {
                            type: 'bar',
                            height: 400,
                            toolbar: { show: false },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800,
                                animateGradually: {
                                    enabled: true,
                                    delay: 150
                                },
                                dynamicAnimation: {
                                    enabled: true,
                                    speed: 350
                                }
                            }
                        },
                        series: [{
                            name: 'Balance',
                            data: activeAccounts.map(acc => ({
                                x: acc.account_name,
                                y: parseFloat(acc.balance),
                                fillColor: colorMap[acc.account_type] || '#007bff'
                            }))
                        }],
                        xaxis: {
                            labels: {
                                rotate: -45,
                                style: {colors: '#ffffff'}
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: val => formatCurrency(val),
                                style: {colors: '#ffffff'}
                            },
                            title: {
                                text: 'Balance',
                                style: {color: '#ffffff'}
                            }
                        },
                        plotOptions: {
                            bar: {
                                distributed: true,
                                borderRadius: 4,
                                columnWidth: '60%'
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        tooltip: {
                            y: {
                                formatter: (val) => {
                                    const percent = (val / totalBar) * 100;
                                    return `${formatCurrency(val)} (${percent.toFixed(1)}%)`;
                                }
                            }
                        },
                        grid: {
                            borderColor: 'rgba(255,255,255,0.05)',
                            strokeDashArray: 4,
                            yaxis: {lines: {show: true}},
                            xaxis: {lines: {show: false}}
                        },
                        legend: {show: false}
                    };

                    barChartInstance = new ApexCharts(document.querySelector('#barChart'), barOptions);
                    barChartInstance.render();
                })
                .catch(error => {
                    console.error('Error loading accounts for bar chart:', error);
                });
        }

        // Search accounts
        async function searchAccounts() {
            const searchTerm = document.getElementById('searchInput').value;

            if (searchTerm.trim() === '') {
                renderAccountsTable(accountsData);
                return;
            }

            try {
                const response = await fetch(`${API_BASE_URL}?action=search&q=${encodeURIComponent(searchTerm)}`);
                const filteredAccounts = await response.json();
                renderAccountsTable(filteredAccounts);
                updateAccountCount(filteredAccounts.length, accountsData.length);
            } catch (error) {
                console.error('Error searching accounts:', error);
            }
        }

        // Add new account with initial balance (with debugging)
        async function addAccount() {
            // Get form values
            const accountName = document.getElementById('accountName').value.trim();
            const accountType = document.getElementById('accountType').value;
            const initialBalance = parseFloat(document.getElementById('initialBalance').value) || 0;
            const bankName = document.getElementById('bankName').value.trim();
            const accountNumber = document.getElementById('accountNumber').value.trim();
            const interestRate = parseFloat(document.getElementById('interestRate').value) || 0;
            const minimumBalance = parseFloat(document.getElementById('minimumBalance').value) || 0;
            const status = document.getElementById('accountStatus').value;
            const notes = document.getElementById('accountNotes').value.trim();

            // Validation
            if (!accountName) {
                showError('Account name is required');
                document.getElementById('accountName').focus();
                return;
            }

            if (!accountType) {
                showError('Please select an account type');
                document.getElementById('accountType').focus();
                return;
            }

            if (initialBalance < 0) {
                showError('Initial balance cannot be negative');
                document.getElementById('initialBalance').focus();
                return;
            }

            // Show loading state
            const submitButton = document.querySelector('[onclick="addAccount()"]');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
            submitButton.disabled = true;

            try {
                // Prepare account data
                const accountData = {
                    account_name: accountName,
                    account_type: accountType,
                    balance: initialBalance, // This will be used for initial balance_history entry
                    bank_name: bankName,
                    account_number: accountNumber,
                    status: status,
                    interest_rate: interestRate,
                    minimum_balance: minimumBalance,
                    notes: notes
                };

                const response = await fetch(`${API_BASE_URL}?action=create`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(accountData)
                });

                // Get response text first to see what we're actually receiving
                const responseText = await response.text();

                // Try to parse as JSON
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (jsonError) {
                    showError('Server returned invalid response. Check console for details.');
                    return;
                }

                if (response.ok && result.success) {
                    // Success
                    bootstrap.Modal.getInstance(document.getElementById('addAccountModal')).hide();
                    await loadDashboardData();
                    showSuccess(`Account "${accountName}" created successfully with initial balance of ${formatCurrency(initialBalance)}!`);

                    // Clear form
                    document.getElementById('addAccountForm').reset();
                    document.getElementById('accountSummaryCard').style.display = 'none';
                } else {
                    showError(result.message || 'Failed to create account');
                }

            } catch (error) {
                showError('Failed to create account. Please try again.');
            } finally {
                // Restore button
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            }
        }

        // Enhanced success notification with account details
        function showAccountCreatedSuccess(accountName, initialBalance) {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>
                        <strong>Account Created Successfully!</strong>
                        <br><small>${accountName} • Initial Balance: ${formatCurrency(initialBalance)}</small>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

            // Add to toast container
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }

            toastContainer.appendChild(toast);

            // Show toast
            const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
            bsToast.show();

            // Remove from DOM after hiding
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        // Delete account
        async function deleteAccount(accountId) {
            if (!confirm('Are you sure you want to delete this account?')) {
                return;
            }

            try {
                const response = await fetch(`${API_BASE_URL}?id=${accountId}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (response.ok) {
                    await loadDashboardData(); // Refresh all data
                    showSuccess('Account deleted successfully!');
                } else {
                    showError('Failed to delete account: ' + result.message);
                }
            } catch (error) {
                console.error('Error deleting account:', error);
                showError('Failed to delete account. Please try again.');
            }
        }

        // Refresh all data
        async function refreshData() {
            const refreshBtn = document.querySelector('[onclick="refreshData()"]');
            const spinner = refreshBtn.querySelector('.loading');

            spinner.classList.add('show');
            refreshBtn.disabled = true;

            try {
                await loadDashboardData();
                showSuccess('Data refreshed successfully!');
            } catch (error) {
                showError('Failed to refresh data.');
            } finally {
                spinner.classList.remove('show');
                refreshBtn.disabled = false;
            }
        }

        // Utility functions
        function formatCurrency(amount) {
            const num = parseFloat(amount);
            if (isNaN(num)) return '$0.00';

            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'KSH'
            }).format(num);
        }

        function formatCurrencyValue(amount) {
            const num = parseFloat(amount);
            if (isNaN(num)) return '0.00';

            const sign = num >= 0 ? '+' : '';
            return sign + num.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        }

        function getStatusBadge(status) {
            const badges = {
                'Active': 'bg-success',
                'Inactive': 'bg-secondary',
                'Locked': 'bg-warning text-dark',
                'Low Balance': 'bg-danger',
                'Debt': 'bg-dark'
            };

            const icons = {
                'Active': 'fas fa-check-circle',
                'Inactive': 'fas fa-pause-circle',
                'Locked': 'fas fa-lock',
                'Low Balance': 'fas fa-exclamation-triangle',
                'Debt': 'fas fa-minus-circle'
            };

            const badgeClass = badges[status] || 'bg-secondary';
            const iconClass = icons[status] || 'fas fa-question-circle';

            return `<span class="badge ${badgeClass}"><i class="${iconClass} me-1"></i>${status}</span>`;
        }

        // Replace the getTypeBadge function with this dynamic version:
        function getTypeBadge(type, colorCode = null, iconClass = null) {
            // If colorCode is provided, use it; otherwise fall back to defaults
            const defaultColors = {
                'Checking': '#007bff',
                'Savings': '#28a745',
                'Investment': '#ffc107',
                'CD': '#17a2b8',
                'Digital': '#6c757d',
                'Crypto': '#343a40',
                'Credit': '#dc3545'
            };

            const defaultIcons = {
                'Checking': 'fas fa-university',
                'Savings': 'fas fa-piggy-bank',
                'Investment': 'fas fa-chart-line',
                'CD': 'fas fa-certificate',
                'Digital': 'fas fa-mobile-alt',
                'Crypto': 'fab fa-bitcoin',
                'Credit': 'fas fa-credit-card'
            };

            const color = colorCode || defaultColors[type] || '#6c757d';
            const icon = iconClass || defaultIcons[type] || 'fas fa-wallet';

            return `<span class="badge" style="background-color: ${color}"><i class="${icon} me-1"></i>${type}</span>`;
        }

        function getProgressBarClass(type) {
            const classes = {
                'Checking': 'progress-bar-custom',
                'Savings': 'bg-success',
                'Investment': 'bg-warning',
                'CD': 'bg-info',
                'Digital': 'bg-secondary',
                'Crypto': 'bg-dark',
                'Credit': 'bg-danger'
            };
            return classes[type] || 'bg-primary';
        }

        function updateAccountCount(showing, total = null) {
            const countText = total ? `Showing ${showing} of ${total} accounts` : `Showing ${showing} accounts`;
            document.getElementById('accountCount').textContent = countText;
        }

        // Enhanced notification functions
        function showSuccess(message) {
            // Create a toast notification
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed';
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
            bsToast.show();

            // Remove from DOM after hiding
            toast.addEventListener('hidden.bs.toast', () => {
                document.body.removeChild(toast);
            });
        }

        function showError(message) {
            // Create a toast notification
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-danger border-0 position-fixed';
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-exclamation-circle me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
            bsToast.show();

            // Remove from DOM after hiding
            toast.addEventListener('hidden.bs.toast', () => {
                document.body.removeChild(toast);
            });
        }

        function exportData() {
            // Convert accounts data to CSV
            const csv = convertToCSV(accountsData);
            downloadCSV(csv, 'accounts_export.csv');
        }

        function convertToCSV(data) {
            const headers = ['Account Name', 'Type', 'Balance', 'Bank', 'Status', 'Last Updated'];
            const rows = data.map(account => [
                account.account_name,
                account.account_type,
                account.balance,
                account.bank_name || '',
                account.status,
                account.last_updated
            ]);

            return [headers, ...rows].map(row => row.join(',')).join('\n');
        }

        function downloadCSV(csv, filename) {
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Table sorting (client-side for now)
        let sortDirection = {};
        function sortTable(columnIndex) {
            const direction = sortDirection[columnIndex] === 'asc' ? 'desc' : 'asc';
            sortDirection[columnIndex] = direction;

            const sortedData = [...accountsData].sort((a, b) => {
                let aVal, bVal;

                switch(columnIndex) {
                    case 0: aVal = a.account_name; bVal = b.account_name; break;
                    case 1: aVal = a.account_type; bVal = b.account_type; break;
                    case 2: aVal = parseFloat(a.balance); bVal = parseFloat(b.balance); break;
                    case 4: aVal = new Date(a.last_balance_update); bVal = new Date(b.last_balance_update); break;
                    default: return 0;
                }

                if (direction === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });

            renderAccountsTable(sortedData);
        }

        // Search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchAccounts();
            }
        });

        // Load growth data and render growth chart
        async function loadGrowthData() {
            try {
                const response = await fetch(`${API_BASE_URL}?action=growth`);
                const data = await response.json();
                renderGrowthChart(data);
            } catch (error) {
                console.error('Error loading growth data:', error);
            }
        }

        // Render growth chart
        function renderGrowthChart(growthData) {
            const labels = growthData.map(item => {
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('en-US', {month: 'short', year: 'numeric'});
            }).reverse();

            const balanceData = growthData.map(item => parseFloat(item.total_balance)).reverse();
            const growthData2 = growthData.map(item => parseFloat(item.total_growth_amount || 0)).reverse();

            const options = {
                chart: {
                    type: 'line',
                    height: 400,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            reset: true
                        }
                    },
                    zoom: {
                        enabled: true
                    }
                },
                series: [
                    {
                        name: 'Total Balance',
                        data: balanceData
                    }
                ],
                xaxis: {
                    categories: labels,
                    title: {
                        text: 'Month-Year',
                        style: { color: '#ffffff' }
                    },
                    labels: {
                        style: {
                            colors: '#ffffff'
                        }
                    }
                },
                yaxis: [
                    {
                        title: {
                            text: 'Balance',
                            style: { color: '#ffffff' }
                        },
                        labels: {
                            style: {
                                colors: '#ffffff'
                            },
                            formatter: function (val) {
                                return formatCurrency(val);
                            }
                        }
                    }
                ],
                markers: {
                    size: 4,
                    colors: ['#007bff', '#28a745'],
                    strokeColors: '#fff',
                    strokeWidth: 2,
                    hover: {
                        size: 6
                    }
                },
                colors: ['#0ecd41', '#28a745'],
                stroke: {
                    curve: 'smooth',
                    width: [5, 4]
                },
                stroke: {
                    curve: 'smooth',
                    width: [5, 4]
                },
                grid: {
                    show: true,
                    borderColor: 'rgba(255, 255, 255, 0.05)', // almost invisible
                    strokeDashArray: 4
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function (val) {
                            return formatCurrency(val);
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    offsetY: -10
                }
            };

            if (growthChart) {
                growthChart.updateOptions(options);
            } else {
                growthChart = new ApexCharts(document.querySelector('#growthChart'), options);
                growthChart.render();
            }
        }


        // Show update balance modal
        async function showUpdateBalanceModal() {
            const modal = new bootstrap.Modal(document.getElementById('updateBalanceModal'));

            // Set current month
            const now = new Date();
            const currentMonth = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
            document.getElementById('updateMonth').value = currentMonth;

            // Load accounts for balance update
            await loadAccountsForBalanceUpdate();
            modal.show();
        }

        // Load accounts for balance update (enhanced with real-time updates)
        async function loadAccountsForBalanceUpdate() {
            try {
                const response = await fetch(`${API_BASE_URL}?action=accounts_for_update`);
                const accounts = await response.json();
                const listContainer = document.getElementById('balanceUpdateList');

                if (accounts.length === 0) {
                    listContainer.innerHTML = '<div class="alert alert-info">No accounts found</div>';
                    return;
                }

                listContainer.innerHTML = accounts.map(account => {
                    // Get type badge with icon
                    const typeBadge = getTypeBadge(account.account_type, account.color_code, account.icon_class);

                    return `
            <div class='card mb-3 border-0 shadow-sm'>
                <div class='card-body py-3'>
                    <div class='row align-items-center'>
                        <div class='col-md-3'>
                            <div class='d-flex align-items-center'>
                                <div>
                                    <strong class='d-block'>${account.account_name}</strong>
                                    <div class='text-muted small mb-1'>
                                        Bank: ${account.bank_name || 'No Bank'}
                                    </div>
                                    <div class='mb-1'>
                                        ${typeBadge}
                                    </div>
                                    <small class='text-muted'>Balance: ${formatCurrency(account.current_balance)}</small>
                                    <br><small class='text-muted'>As at: ${account.last_balance_update || 'Never'}</small>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-2'>
                            <label class='form-label text-muted small mb-1'>New Balance</label>
                            <input type='number'
                                   step='1'
                                   class='form-control form-control-sm'
                                   id='balance_${account.id}'
                                   value='${account.current_balance}'
                                   placeholder='Enter new balance'
                                   oninput='calculateGrowthPreview(${account.id}, ${account.current_balance})'
                                   onchange='calculateGrowthPreview(${account.id}, ${account.current_balance})'
                                   onkeyup='calculateGrowthPreview(${account.id}, ${account.current_balance})'>
                        </div>
                        <div class='col-md-2'>
                            <label class='form-label text-muted small mb-1'>
                                Growth Amount
                                <i class='fas fa-calculator text-primary' title='Auto-calculated'></i>
                            </label>
                            <input type='text'
                                   class='form-control form-control-sm text-center fw-bold'
                                   id='growth_${account.id}'
                                   value='+0.00'
                                   readonly
                                   title='Automatically calculated based on balance change'>
                        </div>
                        <div class='col-md-2'>
                            <label class='form-label text-muted small mb-1'>
                                Growth %
                                <i class='fas fa-percentage text-success' title='Auto-calculated'></i>
                            </label>
                            <input type='text'
                                   class='form-control form-control-sm text-center fw-bold'
                                   id='growth_percent_${account.id}'
                                   value='0.00%'
                                   readonly
                                   title='Percentage change from previous balance'>
                        </div>
                        <div class='col-md-2'>
                            <label class='form-label text-muted small mb-1'>Notes</label>
                            <input type='text'
                                   class='form-control form-control-sm'
                                   id='notes_${account.id}'
                                   placeholder='Optional notes'
                                   maxlength='100'>
                        </div>
                        <div class='col-md-1'>
                            <div class='text-center'>
                                <button class='btn btn-outline-primary btn-sm'
                                        onclick='resetBalance(${account.id}, ${account.current_balance})'
                                        title='Reset to current balance'>
                                    <i class='fas fa-undo'></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Growth Summary Row -->
                    <div class='row mt-2'>
                        <div class='col-12'>
                            <div class='alert alert-light py-1 mb-0' id='summary_${account.id}' style='display: none;'>
                                <small class='text-muted'>
                                    <i class='fas fa-info-circle'></i>
                                    <span id='summary_text_${account.id}'></span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `;
                }).join('');

                // Store accounts data for calculations
                window.currentAccountsForUpdate = accounts;

                // Initialize all growth calculations
                accounts.forEach(account => {
                    calculateGrowthPreview(account.id, account.current_balance);
                });

            } catch (error) {
                console.error('Error loading accounts for update:', error);
                showError('Failed to load accounts for balance update');
            }
        }

        // Show bulk update summary
        function showBulkUpdateSummary() {
            if (!window.currentAccountsForUpdate) return;

            let totalGrowth = 0;
            let changedAccounts = 0;

            window.currentAccountsForUpdate.forEach(account => {
                const newBalanceInput = document.getElementById(`balance_${account.id}`);
                const newBalance = parseFloat(newBalanceInput.value) || 0;
                const growth = newBalance - account.current_balance;

                if (growth !== 0) {
                    totalGrowth += growth;
                    changedAccounts++;
                }
            });

            // Update modal footer with summary
            const modalFooter = document.querySelector('#updateBalanceModal .modal-footer');
            const existingSummary = modalFooter.querySelector('.bulk-summary');

            if (existingSummary) {
                existingSummary.remove();
            }

            if (changedAccounts > 0) {
                const summaryDiv = document.createElement('div');
                summaryDiv.className = 'bulk-summary me-auto';
                summaryDiv.innerHTML = `
            <small class="text-muted">
                <i class="fas fa-calculator"></i>
                <strong>${changedAccounts}</strong> accounts changed |
                Total growth: <span class="${totalGrowth >= 0 ? 'text-success' : 'text-danger'}">
                    ${formatCurrency(totalGrowth)}
                </span>
            </small>
        `;

                modalFooter.insertBefore(summaryDiv, modalFooter.firstChild);
            }
        }

        // Calculate growth preview when balance changes
        function calculateGrowthPreview(accountId, currentBalance) {
            const newBalanceInput = document.getElementById(`balance_${accountId}`);
            const growthAmountDisplay = document.getElementById(`growth_${accountId}`);
            const growthPercentDisplay = document.getElementById(`growth_percent_${accountId}`);
            const summaryDiv = document.getElementById(`summary_${accountId}`);
            const summaryText = document.getElementById(`summary_text_${accountId}`);

            const newBalance = parseFloat(newBalanceInput.value) || 0;
            const growthAmount = newBalance - currentBalance;
            const growthPercent = currentBalance > 0 ? ((growthAmount / currentBalance) * 100) : 0;

            // Update the display fields with formatted values
            growthAmountDisplay.value = formatCurrencyValue(growthAmount);
            growthPercentDisplay.value = growthPercent.toFixed(2) + '%';

            // Determine colors and styling
            let colorClass, bgClass, summaryClass, icon, description;

            if (growthAmount > 0) {
                colorClass = 'text-success';
                bgClass = 'bg-success-subtle border-success';
                summaryClass = 'alert-success';
                icon = '📈';
                description = 'Increase';
            } else if (growthAmount < 0) {
                colorClass = 'text-danger';
                bgClass = 'bg-danger-subtle border-danger';
                summaryClass = 'alert-danger';
                icon = '📉';
                description = 'Decrease';
            } else {
                colorClass = 'text-muted';
                bgClass = 'bg-light border-secondary';
                summaryClass = 'alert-light';
                icon = '➖';
                description = 'No change';
            }

            // Apply styling
            growthAmountDisplay.className = `form-control form-control-sm text-center fw-bold ${colorClass} ${bgClass}`;
            growthPercentDisplay.className = `form-control form-control-sm text-center fw-bold ${colorClass} ${bgClass}`;

            // Update summary
            if (growthAmount !== 0) {
                summaryDiv.style.display = 'block';
                summaryDiv.className = `alert ${summaryClass} py-1 mb-0`;
                summaryText.innerHTML = `
            ${icon} <strong>${description}</strong> of ${formatCurrency(Math.abs(growthAmount))}
            (${Math.abs(growthPercent).toFixed(2)}%) from ${formatCurrency(currentBalance)} to ${formatCurrency(newBalance)}
        `;
            } else {
                summaryDiv.style.display = 'none';
            }

            // Update tooltips
            growthAmountDisplay.title = `${description}: ${formatCurrency(Math.abs(growthAmount))}`;
            growthPercentDisplay.title = `${description}: ${Math.abs(growthPercent).toFixed(2)}%`;

            // Update the balance input styling
            if (newBalance !== currentBalance) {
                newBalanceInput.className = `form-control form-control-sm ${colorClass} border-2`;
            } else {
                newBalanceInput.className = 'form-control form-control-sm';
            }
            animateGrowthUpdate(accountId);
            showBulkUpdateSummary();
        }

        // Add animation when growth updates
        function animateGrowthUpdate(accountId) {
            const growthAmount = document.getElementById(`growth_${accountId}`);
            const growthPercent = document.getElementById(`growth_percent_${accountId}`);

            [growthAmount, growthPercent].forEach(element => {
                element.classList.add('growth-updated');
                setTimeout(() => {
                    element.classList.remove('growth-updated');
                }, 300);
            });
        }

        function resetBalance(accountId, originalBalance) {
            const balanceInput = document.getElementById(`balance_${accountId}`);
            const notesInput = document.getElementById(`notes_${accountId}`);

            balanceInput.value = originalBalance;
            notesInput.value = '';

            // Trigger recalculation
            calculateGrowthPreview(accountId, originalBalance);

            // Visual feedback
            balanceInput.focus();
            balanceInput.select();
        }

        // Load latest month data
        async function loadLatestMonthData() {
            try {
                const response = await fetch(`${API_BASE_URL}?action=latest_month`);
                const data = await response.json();

                // Update the latest data month card
                document.getElementById('latestDataMonth').textContent = data.formatted_month;

                // Update details with actionable insights
                const detailsElement = document.getElementById('latestDataDetails');
                if (data.accounts_with_data > 0) {
                    const needsUpdate = data.total_accounts - data.accounts_with_data;
                    const isStale = isDataStale(data.month_year);

                    if (needsUpdate > 0) {
                        detailsElement.innerHTML = `
                    <i class='fas fa-tasks text-warning me-1'></i>${needsUpdate} need updates<br>
                    <button class='btn btn-sm btn-outline-primary mt-1' onclick='showUpdateModal()'>
                        <i class='fas fa-plus me-1'></i>Update Now
                    </button>
                `;
                    } else if (isStale) {
                        detailsElement.innerHTML = `
                    <i class='fas fa-calendar-plus text-info me-1'></i>Ready for new month<br>
                    <button class='btn btn-sm btn-outline-success mt-1' onclick='startNewMonth()'>
                        <i class='fas fa-forward me-1'></i>Start ${getNextMonth()}
                    </button>
                `;
                    } else {
                        detailsElement.innerHTML = `
                    <i class='fas fa-check-circle text-success me-1'></i>Data complete<br>
                    <i class='fas fa-chart-line me-1'></i>Ready for analysis
                `;
                    }
                } else {
                    detailsElement.innerHTML = `
                <i class='fas fa-plus-circle text-primary me-1'></i>No data yet<br>
                <button class='btn btn-sm btn-outline-primary mt-1' onclick='showUpdateModal()'>
                    <i class='fas fa-plus me-1'></i>Add First Record
                </button>
            `;
                }

                window.latestMonthData = data;

            } catch (error) {
                console.error('Error loading latest month data:', error);
                document.getElementById('latestDataMonth').textContent = 'Error';
                document.getElementById('latestDataDetails').textContent = 'Failed to load';
            }
        }

        function calculateDaysAgo(monthYear) {
            const targetDate = new Date(monthYear + '-01');
            const today = new Date();
            const diffTime = Math.abs(today - targetDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            return diffDays;
        }

        function isCurrentMonthData(monthYear) {
            const currentMonth = new Date().toISOString().slice(0, 7);
            return monthYear === currentMonth;
        }

        function isDataStale(monthYear) {
            const currentMonth = new Date().toISOString().slice(0, 7);
            return monthYear < currentMonth;
        }

        function getNextMonth() {
            const next = new Date();
            next.setMonth(next.getMonth() + 1);
            return next.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
        }

        // Save balance updates
        async function saveBalanceUpdates() {
            const monthYear = document.getElementById('updateMonth').value + '-01';
            const updates = [];

            for (const account of window.currentAccountsForUpdate || []) {
                const newBalanceInput = document.getElementById(`balance_${account.id}`);
                const notesInput = document.getElementById(`notes_${account.id}`);

                const newBalance = parseFloat(newBalanceInput.value) || 0;
                const notes = notesInput.value || '';

                // Only update if balance has changed
                if (newBalance !== account.current_balance) {
                    updates.push({
                        account_id: account.id,
                        balance: newBalance,
                        month_year: monthYear,
                        notes: notes
                    });
                }
            }

            if (updates.length === 0) {
                showError('No balance changes detected');
                return;
            }

            // Show progress
            const saveButton = document.querySelector('[onclick="saveBalanceUpdates()"]');
            const originalText = saveButton.innerHTML;
            saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            saveButton.disabled = true;

            let successCount = 0;
            let errorCount = 0;

            // Save each update
            for (const update of updates) {
                try {
                    const response = await fetch(`${API_BASE_URL}?action=update_balance`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(update)
                    });

                    const result = await response.json();

                    if (response.ok && !result.message.includes('Unable')) {
                        successCount++;
                        console.log(`Updated account ${update.account_id}:`, result);
                    } else {
                        errorCount++;
                        console.error(`Failed to update account ${update.account_id}:`, result.message);
                    }
                } catch (error) {
                    errorCount++;
                    console.error('Error updating balance:', error);
                }
            }

            // Restore button
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;

            // Show results
            if (successCount > 0) {
                bootstrap.Modal.getInstance(document.getElementById('updateBalanceModal')).hide();
                await loadDashboardData();
                showSuccess(`Successfully updated ${successCount} account balances!`);
            }

            if (errorCount > 0) {
                showError(`Failed to update ${errorCount} accounts. Check console for details.`);
            }
        }

        // Show manage types modal
        async function showManageTypesModal() {
            await loadAccountTypes();
            const modal = new bootstrap.Modal(document.getElementById('manageTypesModal'));
            modal.show();
        }

        // Load account types
        async function loadAccountTypes() {
            try {
                const response = await fetch(`${API_BASE_URL}?action=account_types&include_inactive=true`);
                accountTypes = await response.json();
                renderAccountTypesList();
            } catch (error) {
                console.error('Error loading account types:', error);
            }
        }

        // Render account types list
        function renderAccountTypesList() {
            const container = document.getElementById('existingTypesList');

            container.innerHTML = accountTypes.map(type => {
                const isActive = type.is_active == 1;
                const statusClass = isActive ? '' : 'opacity-50';
                const statusBadge = isActive ?
                    '' :
                    '<span class="badge bg-secondary ms-2">Inactive</span>';

                return `
        <div class='d-flex justify-content-between align-items-center mb-2 p-2 border rounded ${statusClass}'>
            <div class="d-flex align-items-center">
                <span class='badge me-2' style='background-color: ${type.color_code}'>
                    <i class="${type.icon_class} me-1"></i>${type.type_name}
                </span>
                ${statusBadge}
            </div>
            <div>
                ${isActive ? `
                    <button class='btn btn-sm btn-outline-primary me-1' onclick='editAccountType(${type.id})' title='Edit'>
                        <i class='fas fa-edit'></i>
                    </button>
                    <button class='btn btn-sm btn-outline-danger' onclick='deleteAccountType(${type.id})' title='Deactivate'>
                        <i class='fas fa-toggle-off'></i>
                    </button>
                ` : `
                    <button class='btn btn-sm btn-outline-success me-1' onclick='reactivateAccountType(${type.id})' title='Reactivate'>
                        <i class='fas fa-toggle-on'></i>
                    </button>
                    <button class='btn btn-sm btn-outline-primary me-1' onclick='editAccountType(${type.id})' title='Edit'>
                        <i class='fas fa-edit'></i>
                    </button>
                `}
            </div>
        </div>
        `;
            }).join('');
        }

        // Add new function to reactivate account types
        async function reactivateAccountType(typeId) {
            if (!confirm('Are you sure you want to reactivate this account type?')) return;

            try {
                const response = await fetch(`${API_BASE_URL}?action=manage_type`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'reactivate', id: typeId })
                });

                const result = await response.json();

                if (result.success) {
                    await loadAccountTypes();
                    await populateAccountTypesDropdown(); // Refresh dropdown
                    showSuccess(result.message);
                } else {
                    showError(result.message);
                }
            } catch (error) {
                console.error('Error reactivating account type:', error);
                showError('Failed to reactivate account type');
            }
        }

        // Add account type
        async function addAccountType() {
            const typeName = document.getElementById('newTypeName').value.trim();
            const colorCode = document.getElementById('newTypeColor').value;
            const iconClass = document.getElementById('newTypeIcon').value.trim();

            if (!typeName) {
                showError('Please enter a type name');
                return;
            }

            const formData = {
                action: 'create',
                type_name: typeName,
                color_code: colorCode,
                icon_class: iconClass || 'fas fa-wallet'
            };

            try {
                const response = await fetch(`${API_BASE_URL}?action=manage_type`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById('addTypeForm').reset();
                    document.getElementById('newTypeColor').value = '#007bff'; // Reset color
                    await loadAccountTypes();
                    showSuccess(result.message);
                } else {
                    showError(result.message);
                }
            } catch (error) {
                console.error('Error adding account type:', error);
                showError('Failed to add account type');
            }
        }

        // Delete account type
        async function deleteAccountType(typeId) {
            if (!confirm('Are you sure you want to deactivate this account type? It will be hidden from new accounts but existing accounts will keep this type.')) return;

            try {
                const response = await fetch(`${API_BASE_URL}?action=manage_type`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', id: typeId })
                });

                const result = await response.json();

                if (result.success) {
                    await loadAccountTypes();
                    await populateAccountTypesDropdown(); // Refresh dropdown
                    showSuccess('Account type deactivated successfully');
                } else {
                    showError(result.message);
                }
            } catch (error) {
                console.error('Error deactivating account type:', error);
                showError('Failed to deactivate account type');
            }
        }

        function calculateGrowthPercentage(accountId, currentBalance) {
            const newBalanceInput = document.getElementById(`balance_${accountId}`);
            const growthAmountInput = document.getElementById(`growth_${accountId}`);

            const newBalance = parseFloat(newBalanceInput.value) || 0;
            const growthAmount = parseFloat(growthAmountInput.value) || 0;

            // Auto-calculate growth amount if balance changed
            if (newBalance !== currentBalance && growthAmount === 0) {
                const calculatedGrowth = newBalance - currentBalance;
                growthAmountInput.value = calculatedGrowth.toFixed(2);
            }
        }

        async function editAccountType(typeId) {
            try {
                // Find the account type data
                const accountType = accountTypes.find(type => type.id == typeId);
                if (!accountType) {
                    showError('Account type not found');
                    return;
                }

                // Show edit modal
                showEditAccountTypeModal(accountType);
            } catch (error) {
                console.error('Error editing account type:', error);
                showError('Failed to open edit form');
            }
        }

        function showEditAccountTypeModal(accountType) {
            const modalHtml = `
    <div class='modal fade' id='editAccountTypeModal' tabindex='-1'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header bg-primary text-white'>
                    <h5 class='modal-title'>
                        <i class='fas fa-edit me-2'></i>
                        Edit Account Type
                    </h5>
                    <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button>
                </div>
                <div class='modal-body'>
                    <form id='editAccountTypeForm'>
                        <input type='hidden' id='editTypeId' value='${accountType.id}'>

                        <!-- Type Name -->
                        <div class='mb-3'>
                            <label for='editTypeName' class='form-label'>Type Name</label>
                            <input type='text' class='form-control' id='editTypeName'
                                   value='${accountType.type_name}' required>
                        </div>

                        <!-- Color Code -->
                        <div class='mb-3'>
                            <label for='editColorCode' class='form-label'>Color</label>
                            <div class='row'>
                                <div class='col-8'>
                                    <input type='color' class='form-control form-control-color'
                                           id='editColorCode' value='${accountType.color_code}' required>
                                </div>
                                <div class='col-4'>
                                    <input type='text' class='form-control' id='editColorCodeText'
                                           value='${accountType.color_code}' placeholder='#000000'>
                                </div>
                            </div>
                        </div>

                        <!-- Icon Class -->
                        <div class='mb-3'>
                            <label for='editIconClass' class='form-label'>Icon Class</label>
                            <div class='input-group'>
                                <input type='text' class='form-control' id='editIconClass'
                                       value='${accountType.icon_class}' placeholder='fas fa-wallet' required>
                                <button type='button' class='btn btn-outline-secondary' onclick='showIconPicker("edit")'>
                                    <i class='fas fa-icons'></i>
                                </button>
                            </div>
                            <small class='text-muted'>Use Font Awesome classes (e.g., fas fa-wallet)</small>
                        </div>

                        <!-- Preview -->
                        <div class='mb-3'>
                            <label class='form-label'>Preview</label>
                            <div class='p-3 border rounded'>
                                <span id='editTypePreview' class='badge' style='background-color: ${accountType.color_code}'>
                                    <i class='${accountType.icon_class} me-1'></i>${accountType.type_name}
                                </span>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class='mb-3'>
                            <div class='form-check'>
                                <input class='form-check-input' type='checkbox' id='editIsActive'
                                       ${accountType.is_active == 1 ? 'checked' : ''}>
                                <label class='form-check-label' for='editIsActive'>
                                    Active (visible in dropdowns)
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                    <button type='button' class='btn btn-primary' onclick='saveEditedAccountType()'>
                        <i class='fas fa-save me-1'></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>`;

            // Remove existing modal if any
            const existingModal = document.getElementById('editAccountTypeModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editAccountTypeModal'));
            modal.show();

            // Setup event listeners for real-time preview
            setupEditTypePreview();
        }

        function setupEditTypePreview() {
            const nameInput = document.getElementById('editTypeName');
            const colorInput = document.getElementById('editColorCode');
            const colorTextInput = document.getElementById('editColorCodeText');
            const iconInput = document.getElementById('editIconClass');
            const preview = document.getElementById('editTypePreview');

            function updatePreview() {
                const name = nameInput.value || 'Type Name';
                const color = colorInput.value;
                const icon = iconInput.value || 'fas fa-wallet';

                preview.style.backgroundColor = color;
                preview.innerHTML = `<i class='${icon} me-1'></i>${name}`;

                // Update text input with color picker value
                colorTextInput.value = color;
            }

            // Sync color picker with text input
            colorInput.addEventListener('input', updatePreview);
            colorTextInput.addEventListener('input', function() {
                if (this.value.match(/^#[0-9A-F]{6}$/i)) {
                    colorInput.value = this.value;
                    updatePreview();
                }
            });

            nameInput.addEventListener('input', updatePreview);
            iconInput.addEventListener('input', updatePreview);
        }

        async function saveEditedAccountType() {
            const formData = {
                action: 'update',
                id: parseInt(document.getElementById('editTypeId').value),
                type_name: document.getElementById('editTypeName').value.trim(),
                color_code: document.getElementById('editColorCode').value,
                icon_class: document.getElementById('editIconClass').value.trim(),
                is_active: document.getElementById('editIsActive').checked ? 1 : 0
            };

            // Validation
            if (!formData.type_name) {
                showError('Please enter a type name');
                return;
            }

            if (!formData.color_code) {
                showError('Please select a color');
                return;
            }

            if (!formData.icon_class) {
                showError('Please enter an icon class');
                return;
            }

            try {
                const response = await fetch(`${API_BASE_URL}?action=manage_type`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('editAccountTypeModal')).hide();

                    // Refresh data
                    await loadAccountTypes();
                    await populateAccountTypesDropdown();

                    showSuccess('Account type updated successfully!');
                } else {
                    showError('Failed to update account type: ' + result.message);
                }
            } catch (error) {
                console.error('Error updating account type:', error);
                showError('Failed to update account type. Please try again.');
            }
        }

        // Enhanced icon picker function
        function showIconPicker(mode = 'add') {
            const iconInput = mode === 'edit' ?
                document.getElementById('editIconClass') :
                document.getElementById('iconClass');

            const commonIcons = [
                'fas fa-wallet', 'fas fa-university', 'fas fa-piggy-bank', 'fas fa-chart-line',
                'fas fa-certificate', 'fas fa-mobile-alt', 'fab fa-bitcoin', 'fas fa-credit-card',
                'fas fa-coins', 'fas fa-dollar-sign', 'fas fa-euro-sign', 'fas fa-pound-sign',
                'fas fa-yen-sign', 'fas fa-money-bill', 'fas fa-money-check', 'fas fa-receipt',
                'fas fa-cash-register', 'fas fa-hand-holding-usd', 'fas fa-donate', 'fas fa-gift',
                'fas fa-shopping-cart', 'fas fa-store', 'fas fa-building', 'fas fa-home',
                'fas fa-car', 'fas fa-plane', 'fas fa-ship', 'fas fa-train'
            ];

            const pickerHtml = `
    <div class='modal fade' id='iconPickerModal' tabindex='-1'>
        <div class='modal-dialog modal-lg'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <h5 class='modal-title'>Choose an Icon</h5>
                    <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                </div>
                <div class='modal-body'>
                    <div class='row g-2'>
                        ${commonIcons.map(icon => `
                            <div class='col-2'>
                                <button type='button' class='btn btn-outline-secondary w-100 icon-option'
                                        data-icon='${icon}' title='${icon}'>
                                    <i class='${icon}'></i>
                                </button>
                            </div>
                        `).join('')}
                    </div>
                    <hr>
                    <div class='mb-3'>
                        <label class='form-label'>Or enter custom icon class:</label>
                        <input type='text' class='form-control' id='customIconInput'
                               placeholder='fas fa-custom-icon'>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                    <button type='button' class='btn btn-primary' onclick='selectCustomIcon("${mode}")'>
                        Use Custom Icon
                    </button>
                </div>
            </div>
        </div>
    </div>`;

            // Remove existing picker if any
            const existingPicker = document.getElementById('iconPickerModal');
            if (existingPicker) {
                existingPicker.remove();
            }

            // Add picker to body
            document.body.insertAdjacentHTML('beforeend', pickerHtml);

            // Show picker
            const pickerModal = new bootstrap.Modal(document.getElementById('iconPickerModal'));
            pickerModal.show();

            // Add click handlers for icon options
            document.querySelectorAll('.icon-option').forEach(button => {
                button.addEventListener('click', function() {
                    const selectedIcon = this.dataset.icon;
                    iconInput.value = selectedIcon;

                    // Update preview if in edit mode
                    if (mode === 'edit') {
                        setupEditTypePreview();
                        document.getElementById('editIconClass').dispatchEvent(new Event('input'));
                    } else {
                        document.getElementById('iconClass').dispatchEvent(new Event('input'));
                    }

                    pickerModal.hide();
                });
            });
        }

        function selectCustomIcon(mode) {
            const customIcon = document.getElementById('customIconInput').value.trim();
            if (customIcon) {
                const iconInput = mode === 'edit' ?
                    document.getElementById('editIconClass') :
                    document.getElementById('iconClass');

                iconInput.value = customIcon;

                // Update preview
                if (mode === 'edit') {
                    document.getElementById('editIconClass').dispatchEvent(new Event('input'));
                } else {
                    document.getElementById('iconClass').dispatchEvent(new Event('input'));
                }

                bootstrap.Modal.getInstance(document.getElementById('iconPickerModal')).hide();
            }
        }


        // Show month viewer modal
        async function showMonthViewer() {
            await loadAvailableMonths();
            new bootstrap.Modal(document.getElementById('monthViewerModal')).show();
        }

        // Load available months
        async function loadAvailableMonths() {
            try {
                const response = await fetch(`${API_BASE_URL}?action=available_months`);
                const months = await response.json();

                const select = document.getElementById('viewMonth');
                const startSelect = document.getElementById('startMonth');
                const endSelect = document.getElementById('endMonth');

                // Clear existing options
                [select, startSelect, endSelect].forEach(sel => {
                    sel.innerHTML = '<option value="">Select Month</option>';
                });

                months.forEach(month => {
                    const option = document.createElement('option');
                    option.value = month;
                    option.textContent = formatMonthDisplay(month);

                    select.appendChild(option.cloneNode(true));
                    startSelect.appendChild(option.cloneNode(true));
                    endSelect.appendChild(option.cloneNode(true));
                });

            } catch (error) {
                console.error('Error loading available months:', error);
            }
        }

        // Load balances by month
        async function loadBalancesByMonth() {
            const selectedMonth = document.getElementById('viewMonth').value;

            if (!selectedMonth) {
                hideMonthData();
                return;
            }

            try {
                const response = await fetch(`${API_BASE_URL}?action=balances_by_month&month=${selectedMonth}`);
                const data = await response.json();

                if (data.error) {
                    showError(data.error);
                    return;
                }

                displayMonthData(data);

            } catch (error) {
                console.error('Error loading month balances:', error);
                showError('Failed to load month data');
            }
        }

        // Display month data
        function displayMonthData(data) {
            // Show summary cards
            document.getElementById('monthSummaryCards').style.display = 'block';
            document.getElementById('monthTotalBalance').textContent = formatCurrency(data.summary.total_balance || 0);
            document.getElementById('monthTotalGrowth').textContent = formatCurrency(data.summary.total_growth || 0);
            document.getElementById('monthAccountsCount').textContent = `${data.summary.accounts_with_data || 0}/${data.summary.total_accounts || 0}`;
            document.getElementById('selectedMonthDisplay').textContent = formatMonthDisplay(data.month);

            // Show table
            document.getElementById('monthBalancesTable').style.display = 'table';
            document.getElementById('noMonthDataMessage').style.display = 'none';

            // Populate table
            const tbody = document.getElementById('monthBalancesTableBody');
            tbody.innerHTML = data.accounts.map(account => {
                const hasData = account.data_status === 'Has data';
                const rowClass = hasData ? '' : 'table-secondary';

                // Safely handle numeric values with fallbacks
                const balance = parseFloat(account.balance) || 0;
                const growthAmount = parseFloat(account.growth_amount) || 0;
                const growthPercentage = parseFloat(account.growth_percentage) || 0;

                return `
            <tr class="${rowClass}">
                <td><strong>${account.account_name || 'Unknown'}</strong></td>
                <td>${getTypeBadge(account.account_type || 'Unknown', account.color_code)}</td>
                <td class="${balance >= 0 ? 'text-success' : 'text-danger'}">
                    <strong>${formatCurrency(balance)}</strong>
                </td>
                <td class="${growthAmount >= 0 ? 'text-success' : 'text-danger'}">
                    ${formatCurrency(growthAmount)}
                </td>
                <td class="${growthPercentage >= 0 ? 'text-success' : 'text-danger'}">
                    ${growthPercentage.toFixed(2)}%
                </td>
                <td>
                    <span class="badge ${hasData ? 'bg-success' : 'bg-secondary'}">
                        ${hasData ? 'Has Data' : 'No Data'}
                    </span>
                </td>
                <td>${account.notes || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary"  onclick="viewAccountHistory(${account.id})" title="View History">
                        <i class="fas fa-history"></i>
                    </button>
                </td>
            </tr>
        `;
            }).join('');
        }

        // Hide month data
        function hideMonthData() {
            document.getElementById('monthSummaryCards').style.display = 'none';
            document.getElementById('monthBalancesTable').style.display = 'none';
            document.getElementById('noMonthDataMessage').style.display = 'block';
        }

        // Load current month
        function loadCurrentMonth() {
            const currentMonth = new Date().toISOString().slice(0, 7) + '-01';
            document.getElementById('viewMonth').value = currentMonth;
            loadBalancesByMonth();
        }

        // Load previous month
        function loadPreviousMonth() {
            const now = new Date();
            now.setMonth(now.getMonth() - 1);
            const previousMonth = now.toISOString().slice(0, 7) + '-01';
            document.getElementById('viewMonth').value = previousMonth;
            loadBalancesByMonth();
        }

        // Show month comparison
        async function showMonthComparison() {
            await loadAvailableMonths();
            new bootstrap.Modal(document.getElementById('monthComparisonModal')).show();
        }

        // Load month comparison
        async function loadMonthComparison() {
            const startMonth = document.getElementById('startMonth').value;
            const endMonth = document.getElementById('endMonth').value;

            if (!startMonth || !endMonth) {
                showError('Please select both start and end months');
                return;
            }

            try {
                const response = await fetch(`${API_BASE_URL}?action=monthly_comparison&start_month=${startMonth}&end_month=${endMonth}`);
                const data = await response.json();

                if (data.error) {
                    showError(data.error);
                    return;
                }

                displayComparisonResults(data, startMonth, endMonth);

            } catch (error) {
                console.error('Error loading comparison:', error);
                showError('Failed to load comparison data');
            }
        }

        // Display comparison results
        function displayComparisonResults(data, startMonth, endMonth) {
            const container = document.getElementById('comparisonResults');

            if (data.length === 0) {
                container.innerHTML = '<div class="alert alert-info">No comparison data available for selected months.</div>';
                return;
            }

            // Safely calculate totals with null checks
            const totalStartBalance = data.reduce((sum, acc) => sum + (parseFloat(acc.start_balance) || 0), 0);
            const totalEndBalance = data.reduce((sum, acc) => sum + (parseFloat(acc.end_balance) || 0), 0);
            const totalChange = totalEndBalance - totalStartBalance;
            const totalChangePercent = totalStartBalance > 0 ? (totalChange / totalStartBalance * 100) : 0;

            container.innerHTML = `
        <div class="alert alert-primary">
            <h6>Overall Summary</h6>
            <div class="row">
                <div class="col-md-3">
                    <strong>Start (${formatMonthDisplay(startMonth)}):</strong><br>
                    ${formatCurrency(totalStartBalance)}
                </div>
                <div class="col-md-3">
                    <strong>End (${formatMonthDisplay(endMonth)}):</strong><br>
                    ${formatCurrency(totalEndBalance)}
                </div>
                <div class="col-md-3">
                    <strong>Change:</strong><br>
                    <span class="${totalChange >= 0 ? 'text-success' : 'text-danger'}">
                        ${formatCurrency(totalChange)}
                    </span>
                </div>
                <div class="col-md-3">
                    <strong>Change %:</strong><br>
                    <span class="${totalChangePercent >= 0 ? 'text-success' : 'text-danger'}">
                        ${totalChangePercent.toFixed(2)}%
                    </span>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Start Balance</th>
                        <th>End Balance</th>
                        <th>Change</th>
                        <th>Change %</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.map(account => {
                const startBalance = parseFloat(account.start_balance) || 0;
                const endBalance = parseFloat(account.end_balance) || 0;
                const balanceChange = parseFloat(account.balance_change) || 0;
                const percentageChange = parseFloat(account.percentage_change) || 0;

                return `
                            <tr>
                                <td><strong>${account.account_name || 'Unknown'}</strong></td>
                                <td>${formatCurrency(startBalance)}</td>
                                <td>${formatCurrency(endBalance)}</td>
                                <td class="${balanceChange >= 0 ? 'text-success' : 'text-danger'}">
                                    ${formatCurrency(balanceChange)}
                                </td>
                                <td class="${percentageChange >= 0 ? 'text-success' : 'text-danger'}">
                                    ${percentageChange.toFixed(2)}%
                                </td>
                            </tr>
                        `;
            }).join('')}
                </tbody>
            </table>
        </div>
    `;
        }

        // Helper functions
        function formatMonthDisplay(monthString) {
            const date = new Date(monthString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
        }

        async function viewAccountHistory(accountId) {
            try {
                // Get account details
                const account = accountsData.find(acc => acc.id == accountId);
                if (!account) {
                    showError('Account not found');
                    return;
                }

                // Fetch balance history for this account
                const response = await fetch(`${API_BASE_URL}?action=balance_history&account_id=${accountId}`);
                const historyData = await response.json();

                // Create and show modal
                showAccountHistoryModal(account, historyData);
            } catch (error) {
                console.error('Error loading account history:', error);
                showError('Failed to load account history');
            }
        }

        function showAccountHistoryModal(account, historyData) {
            // Create modal HTML
            const modalHtml = `
    <div class='modal fade' id='accountHistoryModal' tabindex='-1'>
        <div class='modal-dialog modal-xl'>
            <div class='modal-content'>
                <div class='modal-header px-5 position-relative modal-shape-header bg-shape'>
                    <div class='position-relative z-1'>
                      <h4 class='mb-0 text-white' id='authentication-modal-label'>Balance History - ${account.account_name}</h4>
                    </div>
                    <div data-bs-theme='dark'>
                      <button class='btn-close position-absolute top-0 end-0 mt-2 me-2' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                </div>
                <div class='modal-body'>
                    <!-- Account Summary -->
                    <div class='row mb-4'>
                        <div class='col-md-3'>
                            <div class='card bg-body-secondary'>
                                <div class='card-body text-center'>
                                    <h6>Current Balance</h6>
                                    <h4>${formatCurrency(account.balance)}</h4>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='card bg-body-secondary'>
                                <div class='card-body text-center'>
                                    <h6>Account Type</h6>
                                    <h5>${account.account_type}</h5>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='card bg-body-secondary'>
                                <div class='card-body text-center'>
                                    <h6>Bank</h6>
                                    <h5>${account.bank_name || 'N/A'}</h5>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='card bg-body-secondary'>
                                <div class='card-body text-center'>
                                    <h6>Status</h6>
                                    <h5>${account.status}</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- History Chart -->
                    <div class='card bg-line-chart-gradient mb-4'>
                        <div class='card-header'>
                            <h6 class='mb-0'><i class='fas fa-chart-line me-2'></i>Balance Trend</h6>
                        </div>
                        <div class='card-body'>
                            <div id='accountHistoryChart' ></div>
                        </div>
                    </div>

                    <!-- History Table -->
                    <div class='card'>
                        <div class='card-header d-flex justify-content-between align-items-center'>
                            <h6 class='mb-0'><i class='fas fa-table me-2'></i>Monthly Balance Records</h6>
                        </div>
                        <div class='card-body p-0'>
                            <div class='table-responsive'>
                                <table class='table table-hover mb-0'>
                                    <thead class='table-light'>
                                        <tr>
                                            <th>Month/Year</th>
                                            <th>Balance</th>
                                            <th>Growth Amount</th>
                                            <th>Growth %</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody id='historyTableBody'>
                                        ${renderHistoryTableRows(historyData)}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                    <button type='button' class='btn btn-primary' onclick='exportAccountHistory(${account.id})'>
                        <i class='fas fa-download me-1'></i>Export History
                    </button>
                </div>
            </div>
        </div>
    </div>`;

            // Remove existing modal if any
            const existingModal = document.getElementById('accountHistoryModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('accountHistoryModal'));
            modal.show();

            // Render chart after modal is shown
            setTimeout(() => {
                renderAccountHistoryChart(historyData);
            }, 300);
        }

        function renderHistoryTableRows(historyData) {
            if (!historyData || historyData.length === 0) {
                return '<tr><td colspan="6" class="text-center text-muted py-4">No balance history found</td></tr>';
            }

            return historyData.map(record => {
                const growthClass = record.growth_amount >= 0 ? 'text-success' : 'text-danger';
                const growthIcon = record.growth_amount >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down';

                return `
        <tr>
            <td><strong>${formatMonthYear(record.month_year)}</strong></td>
            <td><strong>${formatCurrency(record.balance)}</strong></td>
            <td class="${growthClass}">
                <i class="${growthIcon} me-1"></i>
                ${formatCurrency(Math.abs(record.growth_amount))}
            </td>
            <td class="${growthClass}">
                <strong>${record.growth_percentage > 0 ? '+' : ''}${record.growth_percentage}%</strong>
            </td>
            <td>${record.notes || '-'}</td>
        </tr>`;
            }).join('');
        }

        function renderAccountHistoryChart(historyData) {
            if (!historyData || historyData.length === 0) return;

            // Sort data by month_year
            const sortedData = historyData.sort((a, b) => new Date(a.month_year) - new Date(b.month_year));

            const labels = sortedData.map(record => formatMonthYear(record.month_year));
            const balanceData = sortedData.map(record => parseFloat(record.balance));
            const growthData = sortedData.map(record => parseFloat(record.growth_amount || 0));

            const options = {
                chart: {
                    type: 'line',
                    height: 350,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            reset: true
                        }
                    },
                    zoom: {
                        enabled: true
                    }
                },
                series: [
                    {
                        name: 'Balance',
                        data: balanceData
                    }
                ],
                xaxis: {
                    categories: labels,
                    title: {
                        text: 'Month-Year',
                        style: { color: '#ffffff' }
                    },
                    labels: {
                        style: {
                            colors: '#ffffff'
                        }
                    }
                },
                yaxis: [
                    {
                        title: {
                            text: 'Balance',
                            style: { color: '#ffffff' }
                        },
                        labels: {
                            style: {
                                colors: '#ffffff'
                            },
                            formatter: function (val) {
                                return formatCurrency(val);
                            }
                        }
                    }
                ],
                markers: {
                    size: 4,
                    colors: ['#06771e', '#28a745'],
                    strokeWidth: 2,
                    hover: {
                        size: 6
                    }
                },
                colors: ['#0ecd41', '#690917'],
                stroke: {
                    width: [5, 4],  // Increase line thickness for both series
                    curve: 'smooth'
                },
                grid: {
                    borderColor: 'rgba(255, 255, 255, 0.05)', // almost invisible white
                    strokeDashArray: 4, // dashed style
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function (val) {
                            return formatCurrency(val);
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    offsetY: -10,
                    labels: {
                        colors: '#ffffff'
                    }
                }
            };

            const accountHistoryChart = new ApexCharts(document.querySelector('#accountHistoryChart'), options);
            accountHistoryChart.render();
        }

        function exportMonthData() {
            const selectedMonth = document.getElementById('viewMonth').value;
            if (!selectedMonth) {
                showError('Please select a month first');
                return;
            }

            // Export functionality
            alert('Export functionality for month: ' + formatMonthDisplay(selectedMonth));
        }

        // Safe number formatting helper
        function safeToFixed(value, decimals = 2) {
            const num = parseFloat(value);
            return isNaN(num) ? '0.00' : num.toFixed(decimals);
        }

        // View account details
        async function viewDetails(accountId) {
            try {
                // Find account in current data
                currentAccount = accountsData.find(acc => acc.id == accountId);
                if (!currentAccount) {
                    showError('Account not found');
                    return;
                }

                // Populate account information
                document.getElementById('viewAccountName').textContent = currentAccount.account_name;
                document.getElementById('viewAccountType').innerHTML = getTypeBadge(currentAccount.account_type, currentAccount.color_code);
                document.getElementById('viewAccountStatus').innerHTML = getStatusBadge(currentAccount.status);
                document.getElementById('viewAccountCreated').textContent = formatDate(currentAccount.created_at);
                document.getElementById('viewAccountUpdated').textContent = formatDate(currentAccount.last_updated);

                // Financial information
                document.getElementById('viewCurrentBalance').textContent = formatCurrency(currentAccount.balance);
                document.getElementById('viewCurrentBalance').className = currentAccount.balance >= 0 ? 'fw-bold text-success' : 'fw-bold text-danger';
                document.getElementById('viewInterestRate').textContent = (currentAccount.interest_rate || 0) + '%';
                document.getElementById('viewMinimumBalance').textContent = formatCurrency(currentAccount.minimum_balance || 0);

                // Growth information (if available)
                const growthAmount = currentAccount.growth_amount || 0;
                const growthPercent = currentAccount.growth_percentage || 0;
                document.getElementById('viewGrowthAmount').textContent = formatCurrency(growthAmount);
                document.getElementById('viewGrowthAmount').className = growthAmount >= 0 ? 'fw-bold text-success' : 'fw-bold text-danger';
                document.getElementById('viewGrowthPercent').textContent = growthPercent.toFixed(2) + '%';

                // Bank information
                document.getElementById('viewBankName').textContent = currentAccount.bank_name || 'Not specified';
                document.getElementById('viewAccountNumber').textContent = currentAccount.account_number || 'Not specified';

                // Notes
                document.getElementById('viewAccountNotes').textContent = currentAccount.notes || 'No notes available';
                document.getElementById('viewAccountNotes').className = currentAccount.notes ? '' : 'text-muted';

                // Load balance history
                await loadAccountBalanceHistory(accountId);

                // Show modal
                new bootstrap.Modal(document.getElementById('viewAccountModal')).show();

            } catch (error) {
                console.error('Error viewing account details:', error);
                showError('Failed to load account details');
            }
        }

        // Load account balance history
        async function loadAccountBalanceHistory(accountId, limit = 5) {
            try {
                const response = await fetch(`${API_BASE_URL}?action=get_balance_history&account_id=${accountId}&limit=${limit}`);
                const history = await response.json();

                const tbody = document.getElementById('viewBalanceHistory');

                if (history.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No balance history available</td></tr>';
                    return;
                }

                tbody.innerHTML = history.map(record => {
                    const growthClass = record.growth_amount >= 0 ? 'text-success' : 'text-danger';
                    const growthIcon = record.growth_amount >= 0 ? '↗️' : '↘️';

                    return `
                <tr>
                    <td>${formatMonthYear(record.month_year)}</td>
                    <td class="fw-bold">${formatCurrency(record.balance)}</td>
                    <td class="${growthClass}">
                        ${growthIcon} ${formatCurrency(Math.abs(record.growth_amount))}
                    </td>
                    <td class="${growthClass}">${record.growth_percentage.toFixed(2)}%</td>
                    <td><small>${record.notes || '-'}</small></td>
                </tr>
            `;
                }).join('');

            } catch (error) {
                console.error('Error loading balance history:', error);
                document.getElementById('viewBalanceHistory').innerHTML =
                    '<tr><td colspan="5" class="text-center text-danger">Error loading balance history</td></tr>';
            }
        }

        // Edit account from view modal
        function editAccountFromView() {
            bootstrap.Modal.getInstance(document.getElementById('viewAccountModal')).hide();
            setTimeout(() => editAccount(currentAccount.id), 300);
        }

        // Edit account details
        async function editAccount(accountId) {
            try {
                // Find account in current data
                currentAccount = accountsData.find(acc => acc.id == accountId);
                if (!currentAccount) {
                    showError('Account not found');
                    return;
                }

                // Load account types for dropdown
                await loadAccountTypesForEdit();

                // Populate form fields
                document.getElementById('editAccountId').value = currentAccount.id;
                document.getElementById('editAccountName').value = currentAccount.account_name;
                document.getElementById('editAccountType').value = currentAccount.account_type;
                document.getElementById('editBankName').value = currentAccount.bank_name || '';
                document.getElementById('editAccountNumber').value = currentAccount.account_number || '';
                document.getElementById('editInterestRate').value = currentAccount.interest_rate || 0;
                document.getElementById('editMinimumBalance').value = currentAccount.minimum_balance || 0;
                document.getElementById('editAccountStatus').value = currentAccount.status;
                document.getElementById('editAccountNotes').value = currentAccount.notes || '';

                // Display current balance info (read-only)
                document.getElementById('editCurrentBalanceDisplay').textContent = formatCurrency(currentAccount.balance);
                document.getElementById('editCurrentBalanceDisplay').className = currentAccount.balance >= 0 ? 'fw-bold text-success' : 'fw-bold text-danger';
                document.getElementById('editLastBalanceUpdate').textContent = currentAccount.last_balance_update ?
                    formatDate(currentAccount.last_balance_update) : 'Never updated';

                // Show modal
                new bootstrap.Modal(document.getElementById('editAccountModal')).show();

            } catch (error) {
                console.error('Error loading account for editing:', error);
                showError('Failed to load account for editing');
            }
        }

        // Load account types for edit modal
        async function loadAccountTypesForEdit() {
            try {
                const response = await fetch(`${API_BASE_URL}?action=account_types`);
                const types = await response.json();

                const select = document.getElementById('editAccountType');
                select.innerHTML = '<option value="">Select Account Type</option>';

                types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.type_name;
                    option.textContent = type.type_name;
                    option.setAttribute('data-color', type.color_code);
                    option.setAttribute('data-icon', type.icon_class);
                    select.appendChild(option);
                });

            } catch (error) {
                console.error('Error loading account types:', error);
                showError('Failed to load account types');
            }
        }

        // Save account changes
        async function saveAccountChanges() {
            try {
                // Get form data
                const formData = {
                    id: document.getElementById('editAccountId').value,
                    account_name: document.getElementById('editAccountName').value.trim(),
                    account_type: document.getElementById('editAccountType').value,
                    bank_name: document.getElementById('editBankName').value.trim(),
                    account_number: document.getElementById('editAccountNumber').value.trim(),
                    interest_rate: parseFloat(document.getElementById('editInterestRate').value) || 0,
                    minimum_balance: parseFloat(document.getElementById('editMinimumBalance').value) || 0,
                    status: document.getElementById('editAccountStatus').value,
                    notes: document.getElementById('editAccountNotes').value.trim()
                };

                // Validation
                if (!formData.account_name) {
                    showError('Account name is required');
                    document.getElementById('editAccountName').focus();
                    return;
                }

                if (!formData.account_type) {
                    showError('Account type is required');
                    document.getElementById('editAccountType').focus();
                    return;
                }

                // Show loading state
                const saveButton = document.querySelector('[onclick="saveAccountChanges()"]');
                const originalText = saveButton.innerHTML;
                saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                saveButton.disabled = true;

                // Send update request
                const response = await fetch(`${API_BASE_URL}?action=update`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Success
                    bootstrap.Modal.getInstance(document.getElementById('editAccountModal')).hide();
                    await loadDashboardData(); // Refresh all data
                    showSuccess('Account updated successfully!');
                } else {
                    showError(result.message || 'Failed to update account');
                }

            } catch (error) {
                console.error('Error updating account:', error);
                showError('Failed to update account. Please try again.');
            } finally {
                // Restore button
                const saveButton = document.querySelector('[onclick="saveAccountChanges()"]');
                if (saveButton) {
                    saveButton.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                    saveButton.disabled = false;
                }
            }
        }

        // Delete account from edit modal
        async function deleteAccountFromEdit() {
            const accountId = document.getElementById('editAccountId').value;
            const accountName = document.getElementById('editAccountName').value;

            if (!confirm(`Are you sure you want to delete "${accountName}"?\n\nThis action cannot be undone and will also delete all balance history for this account.`)) {
                return;
            }

            try {
                const response = await fetch(`${API_BASE_URL}?id=${accountId}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editAccountModal')).hide();
                    await loadDashboardData(); // Refresh all data
                    showSuccess('Account deleted successfully!');
                } else {
                    showError(result.message || 'Failed to delete account');
                }

            } catch (error) {
                console.error('Error deleting account:', error);
                showError('Failed to delete account. Please try again.');
            }
        }

        // View full balance history modal
        async function viewBalanceHistoryModal() {
            if (!currentAccount) return;

            try {
                // Set account name in modal title
                document.getElementById('historyAccountName').textContent = currentAccount.account_name;

                // Load full balance history
                const response = await fetch(`${API_BASE_URL}?action=get_balance_history&account_id=${currentAccount.id}`);
                const history = await response.json();

                // Populate table
                const tbody = document.getElementById('fullBalanceHistory');
                if (history.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No balance history available</td></tr>';
                } else {
                    tbody.innerHTML = history.map(record => {
                        const growthClass = record.growth_amount >= 0 ? 'text-success' : 'text-danger';
                        const growthIcon = record.growth_amount >= 0 ? '↗️' : '↘️';

                        return `
                    <tr>
                        <td class="fw-bold">${formatMonthYear(record.month_year)}</td>
                        <td class="fw-bold">${formatCurrency(record.balance)}</td>
                        <td class="${growthClass}">
                            ${growthIcon} ${formatCurrency(Math.abs(record.growth_amount))}
                        </td>
                        <td class="${growthClass}">${record.growth_percentage.toFixed(2)}%</td>
                        <td><small>${record.notes || '-'}</small></td>
                        <td><small>${formatDate(record.created_at)}</small></td>
                    </tr>
                `;
                    }).join('');
                }

                // Render balance chart
                renderAccountBalanceChart(history);

                // Show modal
                new bootstrap.Modal(document.getElementById('balanceHistoryModal')).show();

            } catch (error) {
                console.error('Error loading balance history:', error);
                showError('Failed to load balance history');
            }
        }

        // Render account balance chart
        function renderAccountBalanceChart(history) {
            if (!history || history.length === 0) return;

            // Destroy existing chart if needed
            if (accountBalanceChartInstance) {
                accountBalanceChartInstance.destroy();
            }

            // Prepare data
            const labels = history.map(record => formatMonthYear(record.month_year)).reverse();
            const balanceData = history.map(record => parseFloat(record.balance)).reverse();

            const options = {
                chart: {
                    type: 'line',
                    height: 350,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            reset: true
                        }
                    },
                    zoom: {
                        enabled: true
                    }
                },
                series: [
                    {
                        name: 'Balance',
                        data: balanceData
                    }
                ],
                xaxis: {
                    categories: labels,
                    title: {
                        text: 'Month-Year',
                        style: { color: '#ffffff' }
                    },
                    labels: {
                        style: {
                            colors: '#ffffff'
                        }
                    }
                },
                yaxis: [
                    {
                        title: {
                            text: 'Balance',
                            style: { color: '#ffffff' }
                        },
                        labels: {
                            style: {
                                colors: '#ffffff'
                            },
                            formatter: function (val) {
                                return formatCurrency(val);
                            }
                        }
                    }
                ],
                markers: {
                    size: 4,
                    colors: ['#06771e', '#28a745'],
                    strokeWidth: 2,
                    hover: {
                        size: 6
                    }
                },
                colors: ['#0ecd41', '#690917'],
                stroke: {
                    width: [5, 4],  // Increase line thickness for both series
                    curve: 'smooth'
                },
                grid: {
                    borderColor: 'rgba(255, 255, 255, 0.05)', // almost invisible white
                    strokeDashArray: 4, // dashed style
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function (val) {
                            return formatCurrency(val);
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    offsetY: -10,
                    labels: {
                        colors: '#ffffff'
                    }
                }
            };

            // Render new chart
            accountBalanceChartInstance = new ApexCharts(document.querySelector('#accountBalanceChart'), options);
            accountBalanceChartInstance.render();
        }

        // Export balance history
        function exportBalanceHistory() {
            if (!currentAccount) return;

            // Get balance history data from table
            const table = document.getElementById('fullBalanceHistory');
            const rows = Array.from(table.querySelectorAll('tr'));

            if (rows.length === 0 || rows[0].cells.length === 1) {
                showError('No data to export');
                return;
            }

            // Create CSV content
            const headers = ['Month/Year', 'Balance', 'Growth Amount', 'Growth %', 'Notes', 'Date Added'];
            const csvContent = [
                headers.join(','),
                ...rows.map(row => {
                    const cells = Array.from(row.cells);
                    return cells.map(cell => `"${cell.textContent.trim()}"`).join(',');
                })
            ].join('\n');

            // Download CSV
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${currentAccount.account_name}_balance_history.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Utility function to format month/year
        function formatMonthYear(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
        }

    </script>

    <script src='https://cdn.jsdelivr.net/npm/apexcharts'></script>
<?php include 'footer.php'; ?>