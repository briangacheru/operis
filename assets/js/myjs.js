
/* -------------------------------------------------------------------------- */
/*                              Other user js functions                       */
/* -------------------------------------------------------------------------- */

    function confirmExport() {
        var confirmExport = confirm("Do you want to download the exported CSV file?");
        if (confirmExport) {
            window.location.href = 'export_all_tasks';
        }
    }
        function exportWriter() {
            var exportWriter = confirm("Do you want to download the exported CSV file?");
            if (exportWriter) {
                window.location.href = 'export_writers';
            }
}
    function exportPaid() {
    var exportPaid = confirm("Do you want to download the exported CSV file?");
    if (exportPaid) {
    window.location.href = 'export_paid_tasks';
}
}
    function exportUnpaid() {
    var exportUnpaid = confirm("Do you want to download the exported CSV file?");
    if (exportUnpaid) {
    window.location.href = 'export_unpaid_tasks';
}
}
function exportOverdraft() {
    var exportOverdraft = confirm("Do you want to download the exported CSV file?");
    if (exportOverdraft) {
        window.location.href = 'export_overdraft';
    }
}
function exportOverdraftAs() {
    var exportOverdraft = confirm("Do you want to download the exported CSV file?");
    if (exportOverdraft) {
        window.location.href = 'export-overdraft';
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

/* -------------------------------------------------------------------------- */
/*                              Fetch totals for overdraft                    */
/* -------------------------------------------------------------------------- */

    function updateFormFields(writerName) {
    $.ajax({
        type: 'GET',
        url: 'get_amount_due',
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
});



