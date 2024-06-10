/* -------------------------------------------------------------------------- */
/*                              Edit Overdraft modal                          */
/* -------------------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const writer = this.getAttribute('data-writer');
            const amount = this.getAttribute('data-amount');
            const od_date = this.getAttribute('data-date');

            // Now set the data in the modal fields
            document.getElementById('overdraft-id').value = id;
            document.getElementById('modal-auth-name').value = writer;
            document.getElementById('modal-auth-amount').value = amount;
            document.getElementById('modal-auth-date').value = od_date;

            // Clear any previous alert message
            let alertDiv = $('#modal-alert');
            alertDiv.removeClass('d-none alert-success alert-danger').text('');
        });
    });

    $('#overdraft-form').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
    type: 'POST',
    url: 'update-od.php', // Your PHP file to handle the update
    data: $(this).serialize(),
    dataType: 'json',
    success: function (response) {
    let alertDiv = $('#modal-alert');
    if (response.success) {
    alertDiv.removeClass('d-none alert-danger').addClass('alert-success').text(response.message);
    // Refresh the table data
    $('#table-simple-pagination-body').load(' #table-simple-pagination-body > *');
    // Hide the modal after 5 seconds
    setTimeout(function() {
    $('#overdraft-view-modal').modal('hide');
}, 5000);
} else {
    alertDiv.removeClass('d-none alert-success').addClass('alert-danger').text(response.message);
}
},
    error: function (jqXHR, textStatus, errorThrown) {
    let alertDiv = $('#modal-alert');
    alertDiv.removeClass('d-none alert-success').addClass('alert-danger').text('An error occurred: ' + textStatus + ' - ' + errorThrown);
}
});
});
});

/* -------------------------------------------------------------------------- */
/*                              Other user js functions                       */
/* -------------------------------------------------------------------------- */

    function confirmExport() {
        var confirmExport = confirm("Do you want to download the exported CSV file?");
        if (confirmExport) {
            window.location.href = 'export_all_tasks.php';
        }
    }
        function exportWriter() {
            var exportWriter = confirm("Do you want to download the exported CSV file?");
            if (exportWriter) {
                window.location.href = 'export_writers.php';
            }
}
    function exportPaid() {
    var exportPaid = confirm("Do you want to download the exported CSV file?");
    if (exportPaid) {
    window.location.href = 'export_paid_tasks.php';
}
}
    function exportUnpaid() {
    var exportUnpaid = confirm("Do you want to download the exported CSV file?");
    if (exportUnpaid) {
    window.location.href = 'export_unpaid_tasks.php';
}
}
function exportOverdraft() {
    var exportOverdraft = confirm("Do you want to download the exported CSV file?");
    if (exportOverdraft) {
        window.location.href = 'export_overdraft.php';
    }
}
function exportOverdraftAs() {
    var exportOverdraft = confirm("Do you want to download the exported CSV file?");
    if (exportOverdraft) {
        window.location.href = 'export-overdraft.php';
    }
}
    function selectAllTasks(source) {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][data-bulk-select-row="data-bulk-select-row"]');
    for (let checkbox of checkboxes) {
    checkbox.checked = source.checked;
}
}
    function submitForm(action) {
    var form = document.getElementById('tasksForm');
    form.action = action;
    form.submit();
}
    $('#summernote').summernote({
    tabsize: 1,
    height: 200
});

/* -------------------------------------------------------------------------- */
/*                              Fetch totals for overdraft                    */
/* -------------------------------------------------------------------------- */

    function updateFormFields(writerName) {
    $.ajax({
        type: 'GET',
        url: 'get_amount_due.php',
        data: { writer_name: writerName },
        dataType: 'json',
        success: function(response) {
            $('#tasks_total').val(response.totalCompletedTasks);
            $('#overdraft_total').val(response.totalOverdrafts);
            $('#amount_due').val(response.amountDue);
        },
        error: function() {
            alert('Error fetching data.');
        }
    });
}

/* -------------------------------------------------------------------------- */
/*                              Edit Writer  modal                            */
/* -------------------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-writer-id');
            const name = this.getAttribute('data-writer');
            const email = this.getAttribute('data-email');
            const phone = this.getAttribute('data-phone');

            // Now set the data in the modal fields
            document.getElementById('writer-id').value = id;
            document.getElementById('modal-auth-writer').value = name;
            document.getElementById('modal-auth-email').value = email;
            document.getElementById('modal-auth-phone').value = phone;

            // Clear any previous alert message
            let alertDiv = document.getElementById('user-modal-alert');
            alertDiv.classList.add('d-none');
            alertDiv.classList.remove('alert-success', 'alert-danger');
            alertDiv.textContent = '';
        });
    });

    document.getElementById('writer-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('update-writer.php', {
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
});



