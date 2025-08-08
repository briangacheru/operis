<?php
include "db.php";
// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    try {
        switch ($_POST['action']) {
            case 'update_status':
                if (!isset($_POST['id']) || !isset($_POST['status'])) {
                    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
                    exit;
                }

                $taskId = intval($_POST['id']);
                $status = $_POST['status'];

                // Validate status
                $validStatuses = ['pending', 'in_progress', 'completed'];
                if (!in_array($status, $validStatuses)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid status']);
                    exit;
                }

                $stmt = $con->prepare("UPDATE tbltodos SET status = ?, completed_at = ?, updated_at = NOW() WHERE id = ?");
                $completed_at = $status === 'completed' ? date('Y-m-d H:i:s') : null;
                $stmt->bind_param("ssi", $status, $completed_at, $taskId);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Database update failed']);
                }
                exit;

            case 'bulk_action':
                if (!isset($_POST['ids']) || !isset($_POST['bulk_type'])) {
                    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
                    exit;
                }

                $ids = $_POST['ids'];
                $action_type = $_POST['bulk_type'];

                if (empty($ids) || !is_array($ids)) {
                    echo json_encode(['success' => false, 'message' => 'No tasks selected']);
                    exit;
                }

                // Sanitize IDs
                $ids = array_map('intval', $ids);
                $ids = array_filter($ids, function($id) { return $id > 0; });

                if (empty($ids)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid task IDs']);
                    exit;
                }

                if ($action_type === 'delete') {
                    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
                    $stmt = $con->prepare("DELETE FROM tbltodos WHERE id IN ($placeholders)");
                    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
                } elseif ($action_type === 'complete') {
                    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
                    $stmt = $con->prepare("UPDATE tbltodos SET status = 'completed', completed_at = NOW(), updated_at = NOW() WHERE id IN ($placeholders)");
                    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid bulk action type']);
                    exit;
                }

                if ($stmt->execute()) {
                    $affected = $stmt->affected_rows;
                    echo json_encode(['success' => true, 'message' => "Successfully processed $affected task(s)"]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Database operation failed']);
                }
                exit;

            default:
                echo json_encode(['success' => false, 'message' => 'Unknown action']);
                exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        exit;
    }
}

// Handle export - Updated version
if (isset($_GET['export'])) {
    $format = $_GET['export'];
    $exportType = $_GET['type'] ?? 'all';
    $includeSubtasks = isset($_GET['subtasks']) && $_GET['subtasks'] === '1';
    $includeAttachments = isset($_GET['attachments']) && $_GET['attachments'] === '1';

    if ($format === 'csv') {
        $filename = 'todos_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Prepare CSV headers
        $headers = ['ID', 'Title', 'Description', 'Priority', 'Status', 'Category', 'Due Date', 'Created At', 'Completed At'];

        if ($includeSubtasks) {
            $headers[] = 'Subtasks Count';
            $headers[] = 'Completed Subtasks';
            $headers[] = 'Subtasks Progress %';
        }

        if ($includeAttachments) {
            $headers[] = 'Attachments Count';
        }

        fputcsv($output, $headers);

        // Build query based on export type
        if ($exportType === 'filtered') {
            // Use the same filtering logic as the main query
            $where_conditions = [];
            $params = [];
            $param_types = "";

            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
                $search_term = "%" . $_GET['search'] . "%";
                $params[] = $search_term;
                $params[] = $search_term;
                $param_types .= "ss";
            }

            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $where_conditions[] = "status = ?";
                $params[] = $_GET['status'];
                $param_types .= "s";
            }

            if (isset($_GET['priority']) && !empty($_GET['priority'])) {
                $where_conditions[] = "priority = ?";
                $params[] = $_GET['priority'];
                $param_types .= "s";
            }

            if (isset($_GET['category']) && !empty($_GET['category'])) {
                $where_conditions[] = "category_id = ?";
                $params[] = $_GET['category'];
                $param_types .= "i";
            }

            if (isset($_GET['due_filter'])) {
                switch ($_GET['due_filter']) {
                    case 'overdue':
                        $where_conditions[] = "due_date < CURDATE() AND status != 'completed'";
                        break;
                    case 'today':
                        $where_conditions[] = "due_date = CURDATE()";
                        break;
                    case 'week':
                        $where_conditions[] = "due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
                        break;
                }
            }

            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
        } else {
            $where_clause = "";
            $params = [];
            $param_types = "";
        }

        // Build the export query
        $sql = "
            SELECT t.*, c.name as category_name";

        if ($includeSubtasks) {
            $sql .= ",
                (SELECT COUNT(*) FROM subtasks WHERE todo_id = t.id) as subtask_count,
                (SELECT COUNT(*) FROM subtasks WHERE todo_id = t.id AND completed = 1) as completed_subtasks";
        }

        if ($includeAttachments) {
            $sql .= ",
                (SELECT COUNT(*) FROM task_attachments WHERE todo_id = t.id) as attachment_count";
        }

        $sql .= "
            FROM tbltodos t 
            LEFT JOIN categories c ON t.category_id = c.id 
            $where_clause
            ORDER BY t.created_at DESC
        ";

        $stmt = $con->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($param_types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $csvRow = [
                $row['id'],
                $row['title'],
                $row['description'],
                $row['priority'],
                $row['status'],
                $row['category_name'] ?? 'No Category',
                $row['due_date'],
                $row['created_at'],
                $row['completed_at']
            ];

            if ($includeSubtasks) {
                $subtaskCount = $row['subtask_count'] ?? 0;
                $completedSubtasks = $row['completed_subtasks'] ?? 0;
                $progress = $subtaskCount > 0 ? round(($completedSubtasks / $subtaskCount) * 100, 1) : 0;

                $csvRow[] = $subtaskCount;
                $csvRow[] = $completedSubtasks;
                $csvRow[] = $progress . '%';
            }

            if ($includeAttachments) {
                $csvRow[] = $row['attachment_count'] ?? 0;
            }

            fputcsv($output, $csvRow);
        }

        fclose($output);
        exit;
    }
}
?>
<?php include "head.php"; ?>
<title>iTasker | Todo List</title>
<?php include "navi.php"; ?>

<?php
// Fetch categories
$categories_stmt = $con->prepare("SELECT * FROM categories ORDER BY name");
$categories_stmt->execute();
$categories = $categories_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Build query with filters
$where_conditions = [];
$params = [];
$param_types = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
    $search_term = "%" . $_GET['search'] . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= "ss";
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where_conditions[] = "status = ?";
    $params[] = $_GET['status'];
    $param_types .= "s";
}

if (isset($_GET['priority']) && !empty($_GET['priority'])) {
    $where_conditions[] = "priority = ?";
    $params[] = $_GET['priority'];
    $param_types .= "s";
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where_conditions[] = "category_id = ?";
    $params[] = $_GET['category'];
    $param_types .= "i";
}

if (isset($_GET['due_filter'])) {
    switch ($_GET['due_filter']) {
        case 'overdue':
            $where_conditions[] = "due_date < CURDATE() AND status != 'completed'";
            break;
        case 'today':
            $where_conditions[] = "due_date = CURDATE()";
            break;
        case 'week':
            $where_conditions[] = "due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
            break;
    }
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Fetch todos with filters
$sql = "
    SELECT t.*, c.name as category_name, c.color as category_color,
           (SELECT COUNT(*) FROM subtasks WHERE todo_id = t.id) as subtask_count,
           (SELECT COUNT(*) FROM subtasks WHERE todo_id = t.id AND completed = 1) as completed_subtasks
    FROM tbltodos t 
    LEFT JOIN categories c ON t.category_id = c.id 
    $where_clause
    ORDER BY 
        CASE t.priority 
            WHEN 'high' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low' THEN 3 
        END,
        t.due_date ASC,
        t.created_at DESC
";

$stmt = $con->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$todos = $result->fetch_all(MYSQLI_ASSOC);

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN due_date < CURDATE() AND status != 'completed' THEN 1 ELSE 0 END) as overdue,
        SUM(CASE WHEN due_date = CURDATE() THEN 1 ELSE 0 END) as due_today
    FROM tbltodos
";
$stats_result = $con->query($stats_query);
$stats = $stats_result->fetch_assoc();

if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']);
}
?>


<!-- Main Header Card -->
<div class="card shadow-none border mb-3">
    <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(../assets/img/illustrations/corner-6.png);"></div>
    <div class="card-header z-1">
        <div class="row flex-between-center gx-0">
            <div class="col-lg-auto d-flex align-items-center">
                <h4 class="mb-0 text-primary fw-bold">To Do <span class="text-info fw-medium">List</span></h4>
            </div>
            <div class="col-lg-auto pt-3 pt-lg-0">
                <div class="d-flex align-items-center">
                    <div class="mx-3">
                        <button class="btn btn-outline-info btn-sm"  href="?export=csv" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                    </div>
                    <div>
                        <h6 class="mb-1 badge rounded-pill badge-subtle-info me-3"><?php echo date("jS F Y"); ?> | <span id="timeDisplay"></span></h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Dashboard -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body text-center">
                <h3 class="mb-1"><?php echo $stats['total']; ?></h3>
                <small>Total Tasks</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stats-card bg-success text-white">
            <div class="card-body text-center">
                <h3 class="mb-1"><?php echo $stats['completed']; ?></h3>
                <small>Completed</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body text-center">
                <h3 class="mb-1"><?php echo $stats['in_progress']; ?></h3>
                <small>In Progress</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stats-card bg-info text-white">
            <div class="card-body text-center">
                <h3 class="mb-1"><?php echo $stats['pending']; ?></h3>
                <small>Pending</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stats-card bg-danger text-white">
            <div class="card-body text-center">
                <h3 class="mb-1"><?php echo $stats['overdue']; ?></h3>
                <small>Overdue</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stats-card bg-secondary text-white">
            <div class="card-body text-center">
                <h3 class="mb-1"><?php echo $stats['due_today']; ?></h3>
                <small>Due Today</small>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="Search tasks..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-control" name="status">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo ($_GET['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-control" name="priority">
                    <option value="">All Priorities</option>
                    <option value="high" <?php echo ($_GET['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="medium" <?php echo ($_GET['priority'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="low" <?php echo ($_GET['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-control" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo ($_GET['category'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-control" name="due_filter">
                    <option value="">All Due Dates</option>
                    <option value="overdue" <?php echo ($_GET['due_filter'] ?? '') === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                    <option value="today" <?php echo ($_GET['due_filter'] ?? '') === 'today' ? 'selected' : ''; ?>>Due Today</option>
                    <option value="week" <?php echo ($_GET['due_filter'] ?? '') === 'week' ? 'selected' : ''; ?>>Due This Week</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-outline-primary w-100"><span class="fas fa-filter"></span></button>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
<div class="bulk-actions mb-3">
    <div class="card">
        <div class="card-body py-2">
            <div class="d-flex align-items-center">
                <span class="me-3"><span id="selectedCount">0</span> selected</span>
                <button class="btn btn-success btn-sm me-2" onclick="bulkAction('complete')">
                    <i class="fas fa-check"></i> Mark Complete
                </button>
                <button class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
                <button class="btn btn-secondary btn-sm ms-auto" onclick="clearSelection()">Clear Selection</button>
            </div>
        </div>
    </div>
</div>

<!-- Main Todo List -->
<div class="row g-3 mb-5">
    <div class="col">
        <div class="card h-100">
            <div class="card-header d-flex flex-between-center bg-body-tertiary">
                <div class="d-flex align-items-center">
                    <input type="checkbox" id="selectAll" class="form-check-input me-2">
                    <label for="selectAll" class="form-check-label">Select All</label>
                </div>
                <div>
                    <button class="btn btn-falcon-info btn-sm me-2 mt-2" data-bs-toggle="modal" data-bs-target="#categoryModal">
                        <i class="fas fa-tags"></i> Manage Categories
                    </button>
                    <button class="btn btn-falcon-info btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                        <i class="fas fa-plus"></i> Add Task
                    </button>
                </div>
            </div>
            <div class="card-body p-0 mb-3">
                <?php if (!empty($todos)): ?>
                    <?php foreach ($todos as $todo):
                        $isOverdue = $todo['due_date'] && $todo['due_date'] < date('Y-m-d') && $todo['status'] !== 'completed';
                        $isDueToday = $todo['due_date'] === date('Y-m-d');
                        $rowClass = $isOverdue ? 'overdue' : ($isDueToday ? 'due-today' : '');
                        $statusClass = $todo['status'] === 'completed' ? 'status-completed' : '';
                        ?>
                        <div class="todo-list-item border-top border-200 priority-<?php echo $todo['priority']; ?> <?php echo $rowClass; ?> <?php echo $statusClass; ?>" data-id="<?php echo $todo['id']; ?>">
                            <div class="row align-items-center py-3 px-3">

                                <!-- Left Column: Checkbox + Task Content -->
                                <div class="col-md-7 col-lg-8">
                                    <div class="d-flex align-items-start">
                                        <input type="checkbox" class="form-check-input me-3 mt-1 bulk-select" value="<?php echo $todo['id']; ?>">

                                        <div class="task-content flex-grow-1">
                                            <!-- Task Title and Badges Row -->
                                            <div class="d-flex align-items-center flex-wrap mb-1">
                                                <span class="text-700 fw-bold me-2"><?php echo htmlspecialchars($todo['title']); ?></span>

                                                <!-- Priority Badge -->
                                                <span class="badge badge-subtle-<?php echo $todo['priority'] === 'high' ? 'danger' : ($todo['priority'] === 'medium' ? 'warning' : 'success'); ?> me-2 mb-1">
                            <?php echo ucfirst($todo['priority']); ?>
                        </span>

                                                <!-- Category Badge -->
                                                <?php if ($todo['category_name']): ?>
                                                    <span class="badge category-badge me-2 mb-1" style="background-color: <?php echo $todo['category_color']; ?>">
                            <?php echo htmlspecialchars($todo['category_name']); ?>
                        </span>
                                                <?php endif; ?>

                                                <!-- Due Date -->
                                                <?php if ($todo['due_date']): ?>
                                                    <small class="text-<?php echo $isOverdue ? 'danger' : ($isDueToday ? 'warning' : 'muted'); ?> mb-1">
                                                        <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($todo['due_date'])); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Task Description -->
                                            <?php if ($todo['description']): ?>
                                                <div class="text-600 fs-11 mb-2"><?php echo htmlspecialchars($todo['description']); ?></div>
                                            <?php endif; ?>

                                            <!-- Subtasks Progress -->
                                            <?php if ($todo['subtask_count'] > 0): ?>
                                                <div class="d-flex align-items-center">
                                                    <small class="text-muted me-2">
                                                        <i class="fas fa-tasks"></i> <?php echo $todo['completed_subtasks']; ?>/<?php echo $todo['subtask_count']; ?> subtasks
                                                    </small>
                                                    <div class="progress task-progress" style="width: 80px; height: 4px;">
                                                        <div class="progress-bar" style="width: <?php echo $todo['subtask_count'] > 0 ? ($todo['completed_subtasks'] / $todo['subtask_count']) * 100 : 0; ?>%"></div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Middle Column: Status -->
                                <div class="col-md-3 col-lg-2 d-flex mt-2 mb-2">
                                    <select class="form-select form-select-sm px-2" onchange="updateStatus(<?php echo $todo['id']; ?>, this.value)">
                                        <option value="pending" <?php echo $todo['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="in_progress" <?php echo $todo['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="completed" <?php echo $todo['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </div>

                                <!-- Right Column: Action Buttons -->
                                <div class="col-md-2 text-end hover-actions-trigger mb-4">
                                    <div class="hover-actions d-flex justify-content-end">
                                        <button class="btn btn-primary btn-sm me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewTaskModal"
                                                onclick="loadTaskDetails(<?php echo $todo['id']; ?>)"
                                                data-bs-placement="top" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-secondary btn-sm me-1 edit-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editTaskModal"
                                                data-id="<?php echo $todo['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($todo['title']); ?>"
                                                data-bs-placement="top" title="Edit <?php echo htmlspecialchars($todo['title']); ?>"
                                                data-description="<?php echo htmlspecialchars($todo['description']); ?>"
                                                data-priority="<?php echo $todo['priority']; ?>"
                                                data-category="<?php echo $todo['category_id']; ?>"
                                                data-due-date="<?php echo $todo['due_date']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm delete-btn"
                                                data-id="<?php echo $todo['id']; ?>"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Task">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No tasks found. Add your first task to get started!</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                            <i class="fas fa-plus"></i> Add Your First Task
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="add-todo" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-control" id="priority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category_id">
                                <option value="">No Category</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="attachments" class="form-label">Attachments</label>
                    <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                    <small class="text-muted">You can select multiple files</small>
                </div>

                <!-- Subtasks Section -->
                <div class="mb-3">
                    <label class="form-label">Subtasks</label>
                    <div id="subtasksContainer">
                        <!-- Subtasks will be added here -->
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addSubtaskField()">
                        <i class="fas fa-plus"></i> Add Subtask
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="edit-todo" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editTaskId" name="id">

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="editPriority" class="form-label">Priority</label>
                            <select class="form-control" id="editPriority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="editDescription" class="form-label">Description</label>
                    <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="editCategory" class="form-label">Category</label>
                            <select class="form-control" id="editCategory" name="category_id">
                                <option value="">No Category</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="editDueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="editDueDate" name="due_date">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="editAttachments" class="form-label">Add More Attachments</label>
                    <input type="file" class="form-control" id="editAttachments" name="attachments[]" multiple>
                </div>

                <!-- Existing Attachments -->
                <div id="existingAttachments" class="mb-3">
                    <!-- Will be populated via JavaScript -->
                </div>

                <!-- Subtasks Section -->
                <div class="mb-3">
                    <label class="form-label">Subtasks</label>
                    <div id="editSubtasksContainer">
                        <!-- Subtasks will be loaded here -->
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addEditSubtaskField()">
                        <i class="fas fa-plus"></i> Add Subtask
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><span><i class="fas fa-save"></i></span> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- View Task Modal -->
<div class="modal fade" id="viewTaskModal" tabindex="-1" aria-labelledby="viewTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border">
            <div class="modal-header bg-body-primary ps-card pe-5 border-bottom-0">
                <h5 class="modal-title" id="viewTaskTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-card pb-card pt-1 fs-10">

                <!-- Description Section -->
                <div class="d-flex mt-3">
                    <span class="fa-stack ms-n1 me-3">
                        <i class="fas fa-circle fa-stack-2x text-200"></i>
                        <i class="fas fa-align-left fa-stack-1x text-primary"></i>
                    </span>
                    <div class="flex-1">
                        <h6>Description</h6>
                        <p class="mb-0" id="viewTaskDescription">
                            <!-- Description content will be loaded here -->
                        </p>
                    </div>
                </div>

                <!-- Priority & Status Section -->
                <div class="d-flex mt-3">
                    <span class="fa-stack ms-n1 me-3">
                        <i class="fas fa-circle fa-stack-2x text-200"></i>
                        <i class="fas fa-flag fa-stack-1x text-primary"></i>
                    </span>
                    <div class="flex-1">
                        <h6>Priority & Status</h6>
                        <p class="mb-1">
                            <span class="badge me-2" id="viewTaskPriority"></span>
                            <span class="badge" id="viewTaskStatus"></span>
                        </p>
                    </div>
                </div>

                <!-- Due Date Section -->
                <div class="d-flex mt-3">
                    <span class="fa-stack ms-n1 me-3">
                        <i class="fas fa-circle fa-stack-2x text-200"></i>
                        <i class="fas fa-calendar-check fa-stack-1x text-primary"></i>
                    </span>
                    <div class="flex-1">
                        <h6>Due Date</h6>
                        <p class="mb-1" id="viewTaskDueDate">
                            <!-- Due date will be loaded here -->
                        </p>
                    </div>
                </div>

                <!-- Category Section -->
                <div class="d-flex mt-3">
                    <span class="fa-stack ms-n1 me-3">
                        <i class="fas fa-circle fa-stack-2x text-200"></i>
                        <i class="fas fa-tag fa-stack-1x text-primary"></i>
                    </span>
                    <div class="flex-1">
                        <h6>Category</h6>
                        <p class="mb-0" id="viewTaskCategory">
                            <!-- Category will be loaded here -->
                        </p>
                    </div>
                </div>

                <!-- Subtasks Section -->
                <div class="d-flex mt-3">
                    <span class="fa-stack ms-n1 me-3">
                        <i class="fas fa-circle fa-stack-2x text-200"></i>
                        <i class="fas fa-tasks fa-stack-1x text-primary"></i>
                    </span>
                    <div class="flex-1">
                        <h6>Subtasks</h6>
                        <div id="viewTaskSubtasks">
                            <!-- Subtasks will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Attachments Section -->
                <div class="d-flex mt-3">
                    <span class="fa-stack ms-n1 me-3">
                        <i class="fas fa-circle fa-stack-2x text-200"></i>
                        <i class="fas fa-paperclip fa-stack-1x text-primary"></i>
                    </span>
                    <div class="flex-1">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="mb-0 fs-9">Attachments</h6>
                        </div>
                        <div id="viewTaskAttachments">
                            <!-- Attachments will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Timeline Section -->
                <div class="d-flex mt-3">
                    <span class="fa-stack ms-n1 me-3">
                        <i class="fas fa-circle fa-stack-2x text-200"></i>
                        <i class="fas fa-clock fa-stack-1x text-primary"></i>
                    </span>
                    <div class="flex-1">
                        <h6>Timeline</h6>
                        <p class="mb-1">
                            <small class="text-muted">Created: <span id="viewTaskCreated"></span></small><br>
                            <small class="text-muted">Completed: <span id="viewTaskCompleted"></span></small>
                        </p>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="editTaskFromView()">
                    <i class="fas fa-edit me-2"></i>Edit Task
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Category Management Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Manage Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add New Category Form -->
                <form action="manage-categories" method="POST" class="mb-4">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="name" placeholder="Category Name" required>
                        </div>
                        <div class="col-md-4">
                            <input type="color" class="form-control" name="color" value="#007bff">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </form>

                <!-- Existing Categories -->
                <div class="list-group">
                    <?php foreach($categories as $category): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="badge me-2" style="background-color: <?php echo $category['color']; ?>; width: 20px; height: 20px;"></span>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </div>
                            <form action="manage-categories" method="POST" class="d-inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this category?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Confirmation Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="fas fa-download me-2"></i>Export Tasks
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6>Export Details:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Total Tasks: <span id="exportTotalTasks"><?php echo $stats['total']; ?></span></li>
                        <li><i class="fas fa-check text-success me-2"></i>Completed: <span id="exportCompletedTasks"><?php echo $stats['completed']; ?></span></li>
                        <li><i class="fas fa-clock text-warning me-2"></i>Pending: <span id="exportPendingTasks"><?php echo $stats['pending']; ?></span></li>
                        <li><i class="fas fa-play text-info me-2"></i>In Progress: <span id="exportInProgressTasks"><?php echo $stats['in_progress']; ?></span></li>
                    </ul>
                </div>

                <div class="mb-3">
                    <h6>Export Options:</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="exportType" id="exportAll" value="all" checked>
                        <label class="form-check-label" for="exportAll">
                            <strong>All Tasks</strong> - Export all tasks regardless of filters
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="exportType" id="exportFiltered" value="filtered">
                        <label class="form-check-label" for="exportFiltered">
                            <strong>Current View</strong> - Export only tasks currently visible with applied filters
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <h6>Include Additional Data:</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="includeSubtasks" checked>
                        <label class="form-check-label" for="includeSubtasks">
                            Include subtasks information
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="includeAttachments" checked>
                        <label class="form-check-label" for="includeAttachments">
                            Include attachments information
                        </label>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <small>The CSV file will be downloaded automatically and will include task details, categories, due dates, and timestamps.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="confirmExport()">
                    <i class="fas fa-download me-2"></i>Export CSV
                </button>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>

<script>
    // Global variables
    let subtaskCounter = 0;
    let editSubtaskCounter = 0;

    // Time display
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        document.getElementById('timeDisplay').textContent = timeString;
    }
    setInterval(updateTime, 1000);
    updateTime();

    // Auto-save functionality
    let autoSaveTimer;
    function autoSave() {
        const title = document.getElementById('title')?.value || '';
        const description = document.getElementById('description')?.value || '';

        if (title || description) {
            localStorage.setItem('draft_title', title);
            localStorage.setItem('draft_description', description);
        }
    }

    // Load draft on page load
    document.addEventListener('DOMContentLoaded', function() {
        const draftTitle = localStorage.getItem('draft_title');
        const draftDescription = localStorage.getItem('draft_description');

        if (draftTitle && document.getElementById('title')) {
            document.getElementById('title').value = draftTitle;
        }
        if (draftDescription && document.getElementById('description')) {
            document.getElementById('description').value = draftDescription;
        }

        // Clear drafts when form is submitted
        document.querySelector('#addTaskModal form')?.addEventListener('submit', function() {
            localStorage.removeItem('draft_title');
            localStorage.removeItem('draft_description');
        });
    });

    // Enhanced hover effects for better UX
    document.addEventListener('DOMContentLoaded', function() {
        // Show/hide action buttons on hover for desktop
        if (window.innerWidth > 768) {
            document.querySelectorAll('.todo-list-item').forEach(item => {
                const hoverActions = item.querySelector('.hover-actions');

                item.addEventListener('mouseenter', function() {
                    hoverActions.style.opacity = '1';
                });

                item.addEventListener('mouseleave', function() {
                    hoverActions.style.opacity = '0.3';
                });

                // Keep visible when focused
                hoverActions.addEventListener('focusin', function() {
                    this.style.opacity = '1';
                });
            });
        }
    });

    // Auto-save on input
    document.getElementById('title')?.addEventListener('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(autoSave, 1000);
    });

    document.getElementById('description')?.addEventListener('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(autoSave, 1000);
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+N to add new task
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            document.querySelector('[data-bs-target="#addTaskModal"]').click();
        }

        // Escape to close modals
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                bootstrap.Modal.getInstance(openModal).hide();
            }
        }
    });

    // Subtask management
    function addSubtaskField() {
        subtaskCounter++;
        const container = document.getElementById('subtasksContainer');
        const subtaskDiv = document.createElement('div');
        subtaskDiv.className = 'input-group mb-2';
        subtaskDiv.innerHTML = `
        <input type="text" class="form-control" name="subtasks[]" placeholder="Subtask title">
        <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
        container.appendChild(subtaskDiv);
    }

    function addEditSubtaskField() {
        editSubtaskCounter++;
        const container = document.getElementById('editSubtasksContainer');
        const subtaskDiv = document.createElement('div');
        subtaskDiv.className = 'input-group mb-2';
        subtaskDiv.innerHTML = `
        <input type="text" class="form-control" name="new_subtasks[]" placeholder="New subtask title">
        <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
        container.appendChild(subtaskDiv);
    }

    // Status update
    function updateStatus(taskId, status) {
        // Create FormData for proper POST request
        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('id', taskId);
        formData.append('status', status);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
            .then(response => {
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text(); // Get as text first to debug
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        // Show success message briefly
                        showNotification('Status updated successfully!', 'success');
                        // Optional: Update UI without full reload
                        updateTaskRowStatus(taskId, status);
                    } else {
                        showNotification('Failed to update status: ' + (data.message || 'Unknown error'), 'error');
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Response text:', text);
                    showNotification('Server response error. Check console for details.', 'error');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                showNotification('Network error: ' + error.message, 'error');
            });
    }

    // Bulk selection
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.bulk-select');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('bulk-select')) {
            updateBulkActions();
        }
    });

    function updateBulkActions() {
        const selected = document.querySelectorAll('.bulk-select:checked');
        const bulkActions = document.querySelector('.bulk-actions');
        const selectedCount = document.getElementById('selectedCount');

        selectedCount.textContent = selected.length;
        bulkActions.style.display = selected.length > 0 ? 'block' : 'none';

        // Update select all checkbox
        const selectAll = document.getElementById('selectAll');
        const allCheckboxes = document.querySelectorAll('.bulk-select');
        selectAll.checked = selected.length === allCheckboxes.length && allCheckboxes.length > 0;
        selectAll.indeterminate = selected.length > 0 && selected.length < allCheckboxes.length;
    }

    // Fix for bulk action - update the fetch body
    function bulkAction(action) {
        const selected = Array.from(document.querySelectorAll('.bulk-select:checked')).map(cb => cb.value);

        if (selected.length === 0) {
            showNotification('Please select tasks first', 'warning');
            return;
        }

        const actionText = action === 'delete' ? 'delete' : 'mark as complete';
        if (!confirm(`Are you sure you want to ${actionText} ${selected.length} task(s)?`)) {
            return;
        }

        // Create FormData for proper POST request
        const formData = new FormData();
        formData.append('action', 'bulk_action');
        formData.append('bulk_type', action);

        // Add each ID as a separate form field
        selected.forEach(id => {
            formData.append('ids[]', id);
        });

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showNotification(`Successfully ${actionText}d ${selected.length} task(s)!`, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotification('Failed to perform bulk action: ' + (data.message || 'Unknown error'), 'error');
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Response text:', text);
                    showNotification('Server response error. Check console for details.', 'error');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                showNotification('Network error: ' + error.message, 'error');
            });
    }

    function clearSelection() {
        document.querySelectorAll('.bulk-select').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        updateBulkActions();
    }

    // Load task details for view modal
    function loadTaskDetails(taskId) {
        fetch(`get-task-details.php?id=${taskId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const task = data.task;

                    // Set title
                    document.getElementById('viewTaskTitle').textContent = task.title;

                    // Set description
                    document.getElementById('viewTaskDescription').textContent = task.description || 'No description provided';

                    // Set priority and status
                    const priorityBadge = document.getElementById('viewTaskPriority');
                    priorityBadge.textContent = task.priority.toUpperCase();
                    priorityBadge.className = `rounded-pill badge-subtle-${getPriorityColor(task.priority)}`;

                    const statusBadge = document.getElementById('viewTaskStatus');
                    statusBadge.textContent = task.status.replace('_', ' ').toUpperCase();
                    statusBadge.className = `rounded-pill badge-subtle-${getStatusColor(task.status)}`;

                    // Set due date
                    const dueDateElement = document.getElementById('viewTaskDueDate');
                    if (task.due_date) {
                        const dueDate = new Date(task.due_date);
                        const today = new Date();
                        const isOverdue = dueDate < today && task.status !== 'completed';
                        const isDueToday = dueDate.toDateString() === today.toDateString();

                        let dateClass = '';
                        let dateIcon = '';
                        if (isOverdue) {
                            dateClass = 'text-danger';
                            dateIcon = '<i class="fas fa-exclamation-triangle me-1"></i>';
                        } else if (isDueToday) {
                            dateClass = 'text-warning';
                            dateIcon = '<i class="fas fa-clock me-1"></i>';
                        } else {
                            dateClass = 'text-muted';
                            dateIcon = '<i class="fas fa-calendar me-1"></i>';
                        }

                        dueDateElement.innerHTML = `<span class="${dateClass}">${dateIcon}${dueDate.toLocaleDateString('en-US', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        })}</span>`;
                    } else {
                        dueDateElement.innerHTML = '<span class="text-muted">No due date set</span>';
                    }

                    // Set category
                    const categoryElement = document.getElementById('viewTaskCategory');
                    if (task.category_name) {
                        categoryElement.innerHTML = `<span class="badge" style="background-color: ${task.category_color || '#6c757d'}">${task.category_name}</span>`;
                    } else {
                        categoryElement.innerHTML = '<span class="text-muted">No category assigned</span>';
                    }

                    // Load subtasks
                    const subtasksContainer = document.getElementById('viewTaskSubtasks');
                    if (data.subtasks && data.subtasks.length > 0) {
                        const completedCount = data.subtasks.filter(s => s.completed).length;
                        const totalCount = data.subtasks.length;
                        const progressPercent = Math.round((completedCount / totalCount) * 100);

                        let subtasksHTML = `
                    <div class="mb-2">
                        <small class="text-muted">${completedCount}/${totalCount} completed (${progressPercent}%)</small>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar" style="width: ${progressPercent}%"></div>
                        </div>
                    </div>
                `;

                        subtasksHTML += data.subtasks.map(subtask =>
                            `<div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" ${subtask.completed ? 'checked' : ''} disabled>
                        <label class="form-check-label ${subtask.completed ? 'text-decoration-line-through text-muted' : ''}">
                            ${subtask.title}
                        </label>
                    </div>`
                        ).join('');

                        subtasksContainer.innerHTML = subtasksHTML;
                    } else {
                        subtasksContainer.innerHTML = '<p class="text-muted mb-0">No subtasks added</p>';
                    }


                    // Load attachments
                    const attachmentsContainer = document.getElementById('viewTaskAttachments');
                    if (data.attachments && data.attachments.length > 0) {
                        attachmentsContainer.innerHTML = data.attachments.map(attachment =>
                            `<div class="d-flex align-items-center mb-1">
                        <a href="${attachment.file_path}"  class="text-decoration-none me-3" data-gallery="attachment-bg">
                            <div class="bg-attachment">
                                <div class="bg-holder rounded" style="background-image:url(${attachment.file_path});"></div><!--/.bg-holder-->
                            </div>
                        </a>
                        <div class="flex-1 fs-11">
                            <h6 class="mb-1"> <a class="text-decoration-none" href="${attachment.file_path}" data-gallery="attachment-title">${attachment.filename}</a></h6>
                            <p class="mb-0">(${formatFileSize(attachment.file_size || 0)})</p>
                        </div>
                    </div>`
                        ).join('');
                    } else {
                        attachmentsContainer.innerHTML = '<p class="text-muted mb-0">No attachments</p>';
                    }

                    // Set timeline
                    document.getElementById('viewTaskCreated').textContent = formatDateTime(task.created_at);
                    document.getElementById('viewTaskCompleted').textContent = task.completed_at ? formatDateTime(task.completed_at) : 'Not completed yet';

                    // Store task ID for potential editing
                    document.getElementById('viewTaskModal').setAttribute('data-task-id', taskId);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load task details');
            });
    }

    // Helper function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Helper function to format date and time
    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Function to edit task from view modal
    function editTaskFromView() {
        const taskId = document.getElementById('viewTaskModal').getAttribute('data-task-id');
        const taskRow = document.querySelector(`[data-id="${taskId}"]`);

        if (taskRow) {
            // Close view modal
            bootstrap.Modal.getInstance(document.getElementById('viewTaskModal')).hide();

            // Trigger edit modal
            setTimeout(() => {
                const editBtn = taskRow.querySelector('.edit-btn');
                if (editBtn) {
                    editBtn.click();
                }
            }, 300);
        }
    }

    function getStatusColor(status) {
        switch(status) {
            case 'completed': return 'success';
            case 'in_progress': return 'warning';
            case 'pending': return 'secondary';
            default: return 'secondary';
        }
    }

    function getPriorityColor(priority) {
        switch(priority) {
            case 'high': return 'danger';
            case 'medium': return 'warning';
            case 'low': return 'success';
            default: return 'secondary';
        }
    }

    // Edit task modal
    const editTaskModal = document.getElementById('editTaskModal');
    editTaskModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const title = button.getAttribute('data-title');
        const description = button.getAttribute('data-description');
        const priority = button.getAttribute('data-priority');
        const category = button.getAttribute('data-category');
        const dueDate = button.getAttribute('data-due-date');

        document.getElementById('editTaskId').value = id;
        document.getElementById('editTitle').value = title;
        document.getElementById('editDescription').value = description;
        document.getElementById('editPriority').value = priority;
        document.getElementById('editCategory').value = category || '';
        document.getElementById('editDueDate').value = dueDate || '';

        // Load existing subtasks and attachments
        loadEditTaskDetails(id);
    });

    function loadEditTaskDetails(taskId) {
        fetch(`get-task-details.php?id=${taskId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Load existing subtasks
                    const subtasksContainer = document.getElementById('editSubtasksContainer');
                    subtasksContainer.innerHTML = '';

                    if (data.subtasks && data.subtasks.length > 0) {
                        data.subtasks.forEach(subtask => {
                            const subtaskDiv = document.createElement('div');
                            subtaskDiv.className = 'input-group mb-2';
                            subtaskDiv.innerHTML = `
                            <input type="text" class="form-control" name="existing_subtasks[${subtask.id}]" value="${subtask.title}">
                            <div class="input-group-text">
                                <input type="checkbox" name="subtask_completed[${subtask.id}]" ${subtask.completed ? 'checked' : ''}>
                            </div>
                            <button type="button" class="btn btn-outline-danger" onclick="markSubtaskForDeletion(${subtask.id}, this)">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                            subtasksContainer.appendChild(subtaskDiv);
                        });
                    }

                    // Load existing attachments
                    const attachmentsContainer = document.getElementById('existingAttachments');
                    attachmentsContainer.innerHTML = '';

                    if (data.attachments && data.attachments.length > 0) {
                        const attachmentsDiv = document.createElement('div');
                        attachmentsDiv.innerHTML = '<label class="form-label">Existing Attachments</label>';

                        data.attachments.forEach(attachment => {
                            const attachmentDiv = document.createElement('div');
                            attachmentDiv.className = 'd-flex justify-content-between align-items-center border p-2 mb-1';
                            attachmentDiv.innerHTML = `
                            <div>
                                <i class="fas fa-paperclip"></i> ${attachment.filename}
                            </div>
                            <button type="button" class="btn btn-danger btn-sm" onclick="markAttachmentForDeletion(${attachment.id}, this)">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                            attachmentsDiv.appendChild(attachmentDiv);
                        });

                        attachmentsContainer.appendChild(attachmentsDiv);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function markSubtaskForDeletion(subtaskId, button) {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'delete_subtasks[]';
        hiddenInput.value = subtaskId;
        document.getElementById('editTaskModal').querySelector('form').appendChild(hiddenInput);
        button.parentElement.remove();
    }

    function markAttachmentForDeletion(attachmentId, button) {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'delete_attachments[]';
        hiddenInput.value = attachmentId;
        document.getElementById('editTaskModal').querySelector('form').appendChild(hiddenInput);
        button.parentElement.remove();
    }

    // Delete task
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            const taskId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
                window.location.href = `delete-todo?id=${taskId}`;
            }
        });
    });

    // Real-time search (if not using form submission)
    function setupRealTimeSearch() {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            let searchTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    const searchTerm = this.value.toLowerCase();
                    document.querySelectorAll('.todo-list-item').forEach(item => {
                        const title = item.querySelector('.text-700').textContent.toLowerCase();
                        const description = item.querySelector('.text-600').textContent.toLowerCase();
                        const matches = title.includes(searchTerm) || description.includes(searchTerm);
                        item.style.display = matches ? 'flex' : 'none';
                    });
                }, 300);
            });
        }
    }

    // Notification system
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.custom-notification');
        existingNotifications.forEach(notification => notification.remove());

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show custom-notification`;
        notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;

        notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    // Export functionality
    function confirmExport() {
        const exportType = document.querySelector('input[name="exportType"]:checked').value;
        const includeSubtasks = document.getElementById('includeSubtasks').checked;
        const includeAttachments = document.getElementById('includeAttachments').checked;

        // Show loading state
        const exportBtn = document.querySelector('#exportModal .btn-primary');
        const originalText = exportBtn.innerHTML;
        exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Preparing Export...';
        exportBtn.disabled = true;

        // Build export URL with parameters
        let exportUrl = '?export=csv';
        exportUrl += `&type=${exportType}`;
        exportUrl += `&subtasks=${includeSubtasks ? '1' : '0'}`;
        exportUrl += `&attachments=${includeAttachments ? '1' : '0'}`;

        // If exporting filtered results, include current filter parameters
        if (exportType === 'filtered') {
            const urlParams = new URLSearchParams(window.location.search);
            const filterParams = ['search', 'status', 'priority', 'category', 'due_filter'];

            filterParams.forEach(param => {
                if (urlParams.has(param) && urlParams.get(param)) {
                    exportUrl += `&${param}=${encodeURIComponent(urlParams.get(param))}`;
                }
            });
        }

        // Create a temporary link and trigger download
        const link = document.createElement('a');
        link.href = exportUrl;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Show success notification
        setTimeout(() => {
            showNotification('Export started! Your CSV file should download shortly.', 'success');

            // Reset button state
            exportBtn.innerHTML = originalText;
            exportBtn.disabled = false;

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
        }, 1000);
    }

    // Update export statistics when modal is shown
    document.getElementById('exportModal').addEventListener('show.bs.modal', function() {
        // You can update these dynamically if needed
        // For now, they're populated from PHP
    });

    // Optional: Update task row status without full page reload
    function updateTaskRowStatus(taskId, status) {
        const taskRow = document.querySelector(`[data-id="${taskId}"]`);
        if (taskRow) {
            // Update status classes
            taskRow.classList.remove('status-completed');
            if (status === 'completed') {
                taskRow.classList.add('status-completed');
            }

            // Update the dropdown to reflect the change
            const dropdown = taskRow.querySelector('.status-dropdown');
            if (dropdown) {
                dropdown.value = status;
            }
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        updateBulkActions();
        // setupRealTimeSearch(); // Uncomment if you want real-time search instead of form submission
    });
</script>

