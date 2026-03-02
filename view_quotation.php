<?php
require_once 'config/config.php';

$id = $_GET['id'];
$quotation = $conn->query("SELECT q.*, c.* 
                          FROM quotations q 
                          JOIN clients c ON q.client_id = c.id 
                          WHERE q.id = $id")->fetch_assoc();

$items = $conn->query("SELECT * FROM quotation_items WHERE quotation_id = $id");
$settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc() ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation #<?php echo $quotation['quotation_number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .quotation-header { margin-bottom: 30px; }
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
                <div class="quotation-header">
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
                            <h1>QUOTATION</h1>
                            <h3>#<?php echo $quotation['quotation_number']; ?></h3>
                            <p>Issue Date: <?php echo date('d/m/Y', strtotime($quotation['issue_date'])); ?></p>
                            <p>Valid Until: <?php echo date('d/m/Y', strtotime($quotation['valid_until'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="client-details mb-4">
                    <h4>To:</h4>
                    <h5><?php echo $quotation['name']; ?></h5>
                    <p><?php echo nl2br($quotation['address']); ?></p>
                    <p>Phone: <?php echo $quotation['phone']; ?></p>
                    <p>Email: <?php echo $quotation['email']; ?></p>
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
                            <td class="text-end"><?php echo number_format($quotation['subtotal'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end">Tax (<?php echo $settings['tax_rate'] ?? 0; ?>%):</td>
                            <td class="text-end"><?php echo number_format($quotation['tax_amount'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong><?php echo number_format($quotation['total_amount'], 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="terms-conditions mt-4">
                    <h5>Terms and Conditions</h5>
                    <p><?php echo nl2br($settings['quotation_terms'] ?? ''); ?></p>
                </div>

                <div class="no-print mt-4">
                    <button class="btn btn-primary" onclick="window.print()">Print Quotation</button>
                    <a href="quotations.php" class="btn btn-secondary">Back to Quotations</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
