<?php include "head.php"; ?>
<title>iTasker | Favorite Tasks</title>
<?php include "navi.php"; ?>

<?php
// Fetch todos from the database
$stmt = $con->prepare("SELECT * FROM tbltodos ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$todos = $result->fetch_all(MYSQLI_ASSOC);

if (isset($_SESSION['alert'])) {
    echo $_SESSION['alert'];
    unset($_SESSION['alert']); // Clear the alert message
}
?>

<div class="card shadow-none border mb-3">
    <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(../assets/img/illustrations/corner-6.png);">
    </div>
    <div class="card-header z-1">
        <div class="row flex-between-center gx-0">
            <div class="col-lg-auto d-flex align-items-center">
                <h4 class="mb-0 text-primary fw-bold">To Do <span class="text-info fw-medium">List</span></h4>
            </div>
            <div class="col-lg-auto pt-3 pt-lg-0">
                <form class="row flex-lg-column flex-xxl-row gx-3 gy-2 align-items-center align-items-lg-start align-items-xxl-center">
                    <div class="col-md-auto position-relative">
                        <h6 class="mb-1 badge rounded-pill badge-subtle-info"><?php echo date("jS F Y"); ?> | <span id="timeDisplay"></span></h6>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col">
        <div class="card h-100">
            <div class="card-header d-flex flex-between-center bg-body-tertiary">
                <h6 class="mb-0"></h6>
                <button class="btn btn-falcon-default btn-sm" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    <i class="fas fa-plus"></i> Add to-do
                </button>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($todos)): ?>
                    <?php foreach ($todos as $todo): ?>
                        <div class="d-flex justify-content-between border-top hover-actions-trigger btn-reveal-trigger px-x1 border-200 todo-list-item">
                            <div class="form-check mb-0 d-flex align-items-center">
                                <input class="form-check-input rounded-circle form-check-line-through p-2 form-check-input-success" type="checkbox" id="todo-<?php echo $todo['id']; ?>" >
                                <label class="form-check-label mb-0 p-3" for="todo-<?php echo $todo['id']; ?>">
                                    <span class="mb-1 text-700 d-block"><?php echo htmlspecialchars($todo['title']); ?></span>
                                    <span class="fs-11 text-600 lh-base font-base fw-normal d-block mb-0"><?php echo htmlspecialchars($todo['description']); ?></span>
                                </label>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="hover-actions">
                                    <button class="btn btn-primary icon-item rounded-3 me-2 fs-11 icon-item-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewTaskModal"
                                            data-id="<?php echo $todo['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($todo['title']); ?>"
                                            data-description="<?php echo htmlspecialchars($todo['description']); ?>"
                                            data-created-at="<?php echo htmlspecialchars($todo['created_at']); ?>">
                                        <span class="fas fa-eye"></span>
                                    </button>
                                    <button class="btn btn-secondary icon-item rounded-3 me-2 fs-11 icon-item-sm edit-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editTaskModal"
                                            data-id="<?php echo $todo['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($todo['title']); ?>"
                                            data-description="<?php echo htmlspecialchars($todo['description']); ?>">
                                        <span class="fas fa-edit"></span>
                                    </button>
                                    <button class="btn btn-danger icon-item rounded-3 me-2 fs-11 icon-item-sm delete-btn"
                                            data-id="<?php echo $todo['id']; ?>">
                                        <span class="fas fa-trash"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center mt-3">No tasks found. Add your first task!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add To-Do Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="add-todo" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Add Task</button>
            </div>
        </form>
    </div>
</div>

<!-- View To-Do Modal -->
<div class="modal fade" id="viewTaskModal" tabindex="-1" aria-labelledby="viewTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px">
        <div class="modal-content position-relative">
            <div class="modal-header">
                <h5 class="modal-title" id="viewTaskModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="d-flex"><span class="fa-stack ms-n1 me-3 mt-3"><i class="fa-inverse fa-stack-1x text-primary fas fa-tag" data-fa-transform="shrink-2"></i></span>
                    <div class="flex-1">
                        <div class="d-flex mt-3"><span class="badge me-1 py-2 badge-subtle-info"><span id="viewTaskDescription"></span></span>
                        </div>
                        <span class="text-break fs-10"><span id="viewTaskCreatedAt"></span></span>
                        <hr class="my-2">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit To-Do Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="edit-todo" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editTaskId" name="id"> <!-- Hidden input to store the task ID -->
                <div class="mb-3">
                    <label for="editTitle" class="form-label">Title</label>
                    <input type="text" class="form-control" id="editTitle" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="editDescription" class="form-label">Description</label>
                    <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>


<?php include "footer.php"; ?>

<script>
    const viewTaskModal = document.getElementById('viewTaskModal');
    viewTaskModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Button that triggered the modal
        const title = button.getAttribute('data-title');
        const description = button.getAttribute('data-description');
        const createdAt = button.getAttribute('data-created-at');

        // Update the modal's content
        const modalTitle = viewTaskModal.querySelector('.modal-title');
        const modalDescription = document.getElementById('viewTaskDescription');
        const modalCreatedAt = document.getElementById('viewTaskCreatedAt');

        modalTitle.textContent = title;
        modalDescription.textContent = description;
        modalCreatedAt.textContent = createdAt;
    });

    // Handle delete button clicks
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            const taskId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this to-do?')) {
                window.location.href = `delete-todo?id=${taskId}`;
            }
        });
    });
</script>
<script>
    const editTaskModal = document.getElementById('editTaskModal');
    editTaskModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Button that triggered the modal
        const id = button.getAttribute('data-id');
        const title = button.getAttribute('data-title');
        const description = button.getAttribute('data-description');

        // Populate the modal fields with the current task data
        const modalTaskId = document.getElementById('editTaskId');
        const modalTitle = document.getElementById('editTitle');
        const modalDescription = document.getElementById('editDescription');

        modalTaskId.value = id;
        modalTitle.value = title;
        modalDescription.value = description;
    });
</script>

