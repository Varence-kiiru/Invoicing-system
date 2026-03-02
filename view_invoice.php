<?php
require_once 'config/config.php';

$id = $_GET['id'];
$invoice = $conn->query("SELECT i.*, c.* 
                        FROM invoices i 
                        JOIN clients c ON i.client_id = c.id 
                        WHERE i.id = $id")->fetch_assoc();

$items = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $id");
$settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc() ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $invoice['invoice_type']; ?> Invoice #<?php echo $invoice['invoice_number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .invoice-header { margin-bottom: 30px; }
        .invoice-details { margin: 20px 0; }
        .company-logo { max-height: 100px; }
        @media print {
            .no-print { display: none; }
            .card { border: none !important; }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-body">
                <div class="invoice-header">
                    <div class="row">
                        <div class="col-md-6">
                            <?php if($settings['logo_path'] ?? false): ?>
                                <img src="<?php echo $settings['logo_path']; ?>" class="company-logo">
                            <?php endif; ?>
                            <h2><?php echo $settings['company_name'] ?? 'Company Name'; ?></h2>
                            <p><?php echo nl2br($settings['company_address'] ?? ''); ?></p>
                            <p>Phone: <?php echo $settings['company_phone'] ?? ''; ?></p>
                            <p>Email: <?php echo $settings['company_email'] ?? ''; ?></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h1><?php echo strtoupper($invoice['type'] ?? 'STANDARD'); ?> INVOICE</h1>
                            <h3>#<?php echo $invoice['invoice_number']; ?></h3>
                            <p>Issue Date: <?php echo date('d/m/Y', strtotime($invoice['issue_date'])); ?></p>
                            <p>Due Date: <?php echo date('d/m/Y', strtotime($invoice['due_date'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="invoice-details">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Bill To:</h4>
                            <h5><?php echo $invoice['name']; ?></h5>
                            <p><?php echo nl2br($invoice['address']); ?></p>
                            <p>Phone: <?php echo $invoice['phone']; ?></p>
                            <p>Email: <?php echo $invoice['email']; ?></p>
                        </div>
                    </div>
                </div>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = $items->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $item['description']; ?></td>
                            <td class="text-end"><?php echo $item['quantity']; ?></td>
                            <td class="text-end"><?php echo number_format($item['unit_price'], 2); ?></td>
                            <td class="text-end"><?php echo number_format($item['total'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end">Subtotal:</td>
                            <td class="text-end"><?php echo number_format($invoice['subtotal'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end">Tax (<?php echo $settings['tax_rate'] ?? 0; ?>%):</td>
                            <td class="text-end"><?php echo number_format($invoice['tax_amount'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong><?php echo number_format($invoice['total_amount'], 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="mt-4">
                    <h5>Payment Terms</h5>
                    <p>Please pay within <?php echo round((strtotime($invoice['due_date']) - strtotime($invoice['issue_date'])) / (60*60*24)); ?> days</p>
                </div>

                <div class="no-print mt-4">
                    <button class="btn btn-primary" onclick="window.print()">Print Invoice</button>
                    <a href="invoices.php" class="btn btn-secondary">Back to Invoices</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
