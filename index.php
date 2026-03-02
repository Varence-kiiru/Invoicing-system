<?php
require_once 'config/config.php';

// Get quick statistics
$total_invoices = $conn->query("SELECT COUNT(*) as count FROM invoices")->fetch_assoc()['count'];
$total_quotations = $conn->query("SELECT COUNT(*) as count FROM quotations")->fetch_assoc()['count'];
$total_clients = $conn->query("SELECT COUNT(*) as count FROM clients")->fetch_assoc()['count'];
$total_pending = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE status='pending'")->fetch_assoc()['count'];

// Get recent invoices
$recent_invoices = $conn->query("SELECT i.*, c.name as client_name 
                                FROM invoices i 
                                JOIN clients c ON i.client_id = c.id 
                                ORDER BY i.issue_date DESC LIMIT 5");

// Get recent quotations
$recent_quotations = $conn->query("SELECT q.*, c.name as client_name 
                                  FROM quotations q 
                                  JOIN clients c ON q.client_id = c.id 
                                  ORDER BY q.issue_date DESC LIMIT 5");
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
        .stat-card {
            border-radius: 15px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Invoices</h5>
                        <h2><?php echo $total_invoices; ?></h2>
                        <i class="fas fa-file-invoice fa-2x float-end"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Quotations</h5>
                        <h2><?php echo $total_quotations; ?></h2>
                        <i class="fas fa-quote-right fa-2x float-end"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Clients</h5>
                        <h2><?php echo $total_clients; ?></h2>
                        <i class="fas fa-users fa-2x float-end"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Pending Invoices</h5>
                        <h2><?php echo $total_pending; ?></h2>
                        <i class="fas fa-clock fa-2x float-end"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Invoices</h5>
                        <a href="invoices.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Client</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($invoice = $recent_invoices->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $invoice['invoice_number']; ?></td>
                                    <td><?php echo $invoice['client_name']; ?></td>
                                    <td><?php echo number_format($invoice['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $invoice['status'] == 'paid' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($invoice['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Quotations</h5>
                        <a href="quotations.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Quotation #</th>
                                    <th>Client</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($quotation = $recent_quotations->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $quotation['quotation_number']; ?></td>
                                    <td><?php echo $quotation['client_name']; ?></td>
                                    <td><?php echo number_format($quotation['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $quotation['status'] == 'accepted' ? 'success' : 'info'; ?>">
                                            <?php echo ucfirst($quotation['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
