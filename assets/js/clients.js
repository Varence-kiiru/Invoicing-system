$(document).ready(function() {
    // Initialize DataTable
    const clientTable = $('#clientsTable').DataTable({
        order: [[0, 'asc']],
        responsive: true
    });

    // Save client
    $('#saveClient').click(function() {
        $.ajax({
            url: 'ajax/save_client.php',
            method: 'POST',
            data: $('#clientForm').serialize(),
            success: function(response) {
                if(response.success) {
                    $('#clientModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Error saving client');
                }
            }
        });
    });
});

// Edit client
function editClient(id) {
    $.ajax({
        url: 'ajax/get_client.php',
        method: 'GET',
        data: { id: id },
        success: function(response) {
            if(response.success) {
                $('#client_id').val(response.data.id);
                $('input[name="name"]').val(response.data.name);
                $('input[name="email"]').val(response.data.email);
                $('input[name="phone"]').val(response.data.phone);
                $('textarea[name="address"]').val(response.data.address);
                $('#clientModal').modal('show');
            }
        }
    });
}

// View client history
function viewClientHistory(id) {
    window.location.href = `client_history.php?id=${id}`;
}

// Delete client
function deleteClient(id) {
    if(confirm('Are you sure you want to delete this client? This will also delete all associated invoices and quotations.')) {
        $.ajax({
            url: 'ajax/delete_client.php',
            method: 'POST',
            data: { id: id },
            success: function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Error deleting client');
                }
            }
        });
    }
}
