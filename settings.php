<?php
require_once 'config/config.php';

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
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4>System Settings</h4>
                    </div>
                    <div class="card-body">
                        <form id="settingsForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label>Company Logo</label>
                                <div class="d-flex align-items-center gap-3">
                                    <?php if($settings['logo_path'] ?? null): ?>
                                        <img src="<?php echo $settings['logo_path']; ?>" height="50">
                                    <?php endif; ?>
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Company Name</label>
                                <input type="text" name="company_name" class="form-control" 
                                       value="<?php echo $settings['company_name'] ?? ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label>Company Address</label>
                                <textarea name="company_address" class="form-control" rows="3" required>
                                    <?php echo $settings['company_address'] ?? ''; ?>
                                </textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label>Company Phone</label>
                                    <input type="tel" name="company_phone" class="form-control" 
                                           value="<?php echo $settings['company_phone'] ?? ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label>Company Email</label>
                                    <input type="email" name="company_email" class="form-control" 
                                           value="<?php echo $settings['company_email'] ?? ''; ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label>Tax Rate (%)</label>
                                <input type="number" name="tax_rate" class="form-control" step="0.01" 
                                       value="<?php echo $settings['tax_rate'] ?? '0'; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label>Invoice Terms & Conditions</label>
                                <textarea name="invoice_terms" class="form-control" rows="4">
                                    <?php echo $settings['invoice_terms'] ?? ''; ?>
                                </textarea>
                            </div>

                            <div class="mb-3">
                                <label>Quotation Terms & Conditions</label>
                                <textarea name="quotation_terms" class="form-control" rows="4">
                                    <?php echo $settings['quotation_terms'] ?? ''; ?>
                                </textarea>
                            </div>

                            <div class="mb-3">
                                <label>Currency Symbol</label>
                                <input type="text" name="currency_symbol" class="form-control" 
                                       value="<?php echo $settings['currency_symbol'] ?? ''; ?>" required>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/settings.js"></script>
</body>
</html>
