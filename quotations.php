<?php
require_once 'config/config.php';

// Get all quotations with client information
$quotations = $conn->query("SELECT q.*, c.name as client_name 
                           FROM quotations q 
                           JOIN clients c ON q.client_id = c.id 
                           ORDER BY q.issue_date DESC");

// Get clients for the new quotation form
$clientsQuery = $conn->query("SELECT id, name FROM clients ORDER BY name");
$clients = [];
if ($clientsQuery) {
    while ($row = $clientsQuery->fetch_assoc()) {
        $clients[] = $row;
    }
}

// Get company settings
$settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc() ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title - <?php echo $settings['company_name'] ?? 'Olivian Group'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .action-buttons .btn { margin: 0 2px; }
        .status-pending { background-color: #fff3cd; }
        .status-accepted { background-color: #d1e7dd; }
        .status-rejected { background-color: #f8d7da; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Quotations</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quotationModal" onclick="resetQuotationForm()">
                <i class="fas fa-plus"></i> New Quotation
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="quotationsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Quotation #</th>
                            <th>Client</th>
                            <th>Issue Date</th>
                            <th>Valid Until</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($quotation = $quotations->fetch_assoc()): ?>
                        <tr class="status-<?php echo strtolower($quotation['status']); ?>">
                            <td><?php echo $quotation['quotation_number']; ?></td>
                            <td><?php echo $quotation['client_name']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($quotation['issue_date'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($quotation['valid_until'])); ?></td>
                            <td><?php echo number_format($quotation['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php echo getStatusBadgeClass($quotation['status']); ?>">
                                    <?php echo ucfirst($quotation['status']); ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-info" onclick="viewQuotation(<?php echo $quotation['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="editQuotation(<?php echo $quotation['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-success" onclick="convertToInvoice(<?php echo $quotation['id']; ?>)">
                                    <i class="fas fa-file-invoice"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteQuotation(<?php echo $quotation['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quotation Modal -->
    <?php include 'templates/quotation_modal.php'; ?>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        function resetQuotationForm() {
            $('#quotationForm')[0].reset();
            $('#quotation_id').val('');
            $('input[name="quotation_number"]').val('');
        }
    </script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/quotation.js"></script>
</body>
</html>
<?php
function getStatusBadgeClass($status) {
    switch($status) {
        case 'accepted': return 'success';
        case 'rejected': return 'danger';
        case 'pending': return 'warning';
        default: return 'secondary';
    }
}
?>
