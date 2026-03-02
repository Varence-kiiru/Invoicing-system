<div class="modal fade" id="invoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Create New Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="invoiceForm">
                    <!-- Hidden Fields -->
                    <input type="hidden" name="invoice_id" id="invoice_id">
                    <input type="hidden" name="invoice_type" id="invoice_type">
                    
                    <!-- Invoice Details -->
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

                    <!-- Dates -->
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

                    <!-- Invoice Items -->
                    <div class="items-section">
                        <h5>Invoice Items</h5>
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Tax Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="description[]" class="form-control" required></td>
                                    <td><input type="number" name="quantity[]" class="form-control quantity" required></td>
                                    <td><input type="number" step="0.01" name="unit_price[]" class="form-control price" required></td>
                                    <td><input type="number" step="0.01" name="total[]" class="form-control item-total" readonly></td>
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" name="tax_exempt[]" class="form-check-input tax-exempt">
                                            <label class="form-check-label">Tax Exempt</label>
                                        </div>
                                    </td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-success" id="addItem">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>

                    <!-- Totals Section -->
                    <div class="row mt-3">
                        <div class="col-md-6 offset-md-6">
                            <table class="table">
                                <tr>
                                    <td>Taxable Subtotal:</td>
                                    <td><input type="number" step="0.01" name="taxable_subtotal" class="form-control" readonly></td>
                                </tr>
                                <tr>
                                    <td>Non-Taxable Subtotal:</td>
                                    <td><input type="number" step="0.01" name="non_taxable_subtotal" class="form-control" readonly></td>
                                </tr>
                                <tr>
                                    <td>Subtotal:</td>
                                    <td><input type="number" step="0.01" name="subtotal" class="form-control" readonly></td>
                                </tr>
                                <tr>
                                    <td>Tax Rate:</td>
                                    <td><input type="number" step="0.01" id="tax_rate" name="tax_rate" value="<?php echo $settings['tax_rate'] ?? 0; ?>" class="form-control"></td>
                                </tr>
                                <tr>
                                    <td>Tax Amount:</td>
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
