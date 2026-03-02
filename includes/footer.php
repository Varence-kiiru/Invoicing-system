<?php
$settings = $settings ?? ($conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc() ?? []);
$year = date('Y');
?>
<footer class="footer mt-auto py-3 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5><?php echo $settings['company_name'] ?? 'Olivian Group'; ?></h5>
                <p class="mb-0"><?php echo nl2br($settings['company_address'] ?? ''); ?></p>
            </div>
            <div class="col-md-4">
                <h5>Contact Information</h5>
                <p class="mb-1">
                    <i class="fas fa-phone"></i> 
                    <?php echo $settings['company_phone'] ?? ''; ?>
                </p>
                <p class="mb-0">
                    <i class="fas fa-envelope"></i> 
                    <?php echo $settings['company_email'] ?? ''; ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <p class="mb-0">© <?php echo $year; ?> All Rights Reserved</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
