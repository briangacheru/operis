<?php
include('check-login.php');

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['writer-id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // SQL to update the overdraft record
    $sql = "UPDATE tblwriters SET name = ?, email = ?, phone = ? WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssi", $name, $email, $phone, $id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Writer updated successfully';
    } else {
        $response['message'] = 'Error updating record: ' . $stmt->error;
    }
    $stmt->close();
}

echo json_encode($response);
?>

<script>
    document.getElementById('writer-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('update-writer', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                let alertDiv = document.getElementById('user-modal-alert');
                if (data.success) {
                    alertDiv.classList.remove('d-none', 'alert-danger');
                    alertDiv.classList.add('alert-success');
                    alertDiv.textContent = data.message;
                    // Refresh the table data
                    document.getElementById('table-simple-pagination-body').load(' #table-simple-pagination-body > *');
                    // Hide the modal after 5 seconds
                    setTimeout(function() {
                        $('#user-edit-modal').modal('hide');
                    }, 5000);
                } else {
                    alertDiv.classList.remove('d-none', 'alert-success');
                    alertDiv.classList.add('alert-danger');
                    alertDiv.textContent = data.message;
                }
            })
            .catch(error => {
                let alertDiv = document.getElementById('user-modal-alert');
                alertDiv.classList.remove('d-none', 'alert-success');
                alertDiv.classList.add('alert-danger');
                alertDiv.textContent = 'An error occurred: ' + error.message;
            });
    });
</script>
