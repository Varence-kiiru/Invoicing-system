<?php
require_once 'config/config.php';

// Initialize invoices query with client information
$invoicesQuery = $conn->query("
    SELECT i.*, c.name as client_name 
    FROM invoices i 
    JOIN clients c ON i.client_id = c.id 
    ORDER BY i.issue_date DESC
");

$invoices = [];

// Store results in array if query successful
if ($invoicesQuery) {
    while ($row = $invoicesQuery->fetch_assoc()) {
        $invoices[] = $row;
    }
}

// Get clients for the dropdown
$clientsQuery = $conn->query("SELECT * FROM clients ORDER BY name");
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
    <title>Invoice Management - <?php echo $settings['company_name'] ?? 'Olivian Group'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Invoice Management</h2>
            <div>
                <button class="btn btn-primary" onclick="createInvoice('standard')" data-bs-toggle="modal" data-bs-target="#invoiceModal">
                    <i class="fas fa-plus"></i> New Standard Invoice
                </button>
                <button class="btn btn-info" onclick="createInvoice('proforma')" data-bs-toggle="modal" data-bs-target="#invoiceModal">
                    <i class="fas fa-plus"></i> New Proforma Invoice
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="invoicesTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Client</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($invoices as $invoice): ?>
                        <tr>
                            <td><?php echo $invoice['invoice_number']; ?></td>
                            <td><?php echo $invoice['client_name']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($invoice['issue_date'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($invoice['due_date'])); ?></td>
                            <td><?php echo number_format($invoice['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php echo getStatusColor($invoice['status']); ?>">
                                    <?php echo ucfirst($invoice['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="viewInvoice(<?php echo $invoice['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-info" onclick="editInvoice(<?php echo $invoice['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-success" onclick="markAsPaid(<?php echo $invoice['id']; ?>)">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteInvoice(<?php echo $invoice['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Invoice Modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create New Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="invoiceForm">
                        <input type="hidden" name="invoice_id" id="invoice_id">
                        <input type="hidden" name="invoice_type" value="standard">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Invoice Number</label>
                                <input type="text" name="invoice_number" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label>Client</label>
                                <select name="client_id" class="form-control" required>
                                    <option value="">Select Client</option>
                                    <?php foreach($clients as $client): ?>
                                        <option value="<?php echo $client['id']; ?>"><?php echo $client['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Issue Date</label>
                                <input type="date" name="issue_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>Due Date</label>
                                <input type="date" name="due_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="items-section">
                            <h5>Invoice Items</h5>
                            <table class="table table-bordered" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" name="description[]" class="form-control" required></td>
                                        <td><input type="number" name="quantity[]" class="form-control quantity" required></td>
                                        <td><input type="number" step="0.01" name="unit_price[]" class="form-control price" required></td>
                                        <td><input type="number" step="0.01" name="total[]" class="form-control item-total" readonly></td>
                                        <td><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-success" id="addItem">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6 offset-md-6">
                                <table class="table">
                                    <tr>
                                        <td>Subtotal:</td>
                                        <td><input type="number" step="0.01" name="subtotal" class="form-control" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Tax (<?php echo $settings['tax_rate'] ?? 0; ?>%):</td>
                                        <td><input type="number" step="0.01" name="tax_amount" class="form-control" readonly></td>
                                    </tr>
                                    <tr>
                                        <td>Total:</td>
                                        <td><input type="number" step="0.01" name="total_amount" class="form-control" readonly></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveInvoice">Save Invoice</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/invoices.js"></script>
</body>
</html>

<?php
function getStatusColor($status) {
    switch($status) {
        case 'paid':
            return 'success';
        case 'pending':
            return 'warning';
        case 'overdue':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>
