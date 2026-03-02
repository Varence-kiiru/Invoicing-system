// Calculate invoice total including tax
function calculateInvoiceTotal() {
    let taxableSubtotal = 0;
    let nonTaxableSubtotal = 0;

    // Calculate totals for each row
    $('#itemsTable tbody tr').each(function() {
        let row = $(this);
        let total = parseFloat(row.find('.item-total').val()) || 0;
        let isTaxExempt = row.find('.tax-exempt').is(':checked');

        if (isTaxExempt) {
            nonTaxableSubtotal += total;
        } else {
            taxableSubtotal += total;
        }
    });
    let subtotal = taxableSubtotal + nonTaxableSubtotal;
    let taxRate = parseFloat($('#tax_rate').val() || 0);
    let taxAmount = taxableSubtotal * (taxRate / 100);
    let total = subtotal + taxAmount;

    // Update form fields
    $('input[name="taxable_subtotal"]').val(taxableSubtotal.toFixed(2));
    $('input[name="non_taxable_subtotal"]').val(nonTaxableSubtotal.toFixed(2));
    $('input[name="subtotal"]').val(subtotal.toFixed(2));
    $('input[name="tax_amount"]').val(taxAmount.toFixed(2));
    $('input[name="total_amount"]').val(total.toFixed(2));
}

$(document).ready(function() {
    // Initialize DataTable
    const invoiceTable = $('#invoicesTable').DataTable({
        order: [[2, 'desc']],
        responsive: true
    });

    // Set default dates on modal open
    $('#invoiceModal').on('show.bs.modal', function() {
        if(!$('#invoice_id').val()) {
            let today = new Date().toISOString().split('T')[0];
            $('input[name="issue_date"]').val(today);
            
            let dueDate = new Date();
            dueDate.setDate(dueDate.getDate() + 30);
            $('input[name="due_date"]').val(dueDate.toISOString().split('T')[0]);
        }
    });

    // Calculate item total when quantity or price changes
    $(document).on('input', '.quantity, .price', function() {
        let row = $(this).closest('tr');
        let quantity = parseFloat(row.find('.quantity').val()) || 0;
        let price = parseFloat(row.find('.price').val()) || 0;
        let total = quantity * price;
        row.find('.item-total').val(total.toFixed(2));
        calculateInvoiceTotal();
    });

    // Add this to ensure calculations work on new rows
    $('#addItem').click(function() {
        let newRow = $(getItemRowTemplate());
        $('#itemsTable tbody').append(newRow);
    });

    // Remove item row
    $(document).on('click', '.remove-item', function() {
        if($('#itemsTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateInvoiceTotal();
        }
    });

    // Calculate totals when inputs change
    $(document).on('input change', '.quantity, .price, .tax-exempt, #tax_rate', calculateInvoiceTotal);

    // Add tax exempt checkbox to item row
    function getItemRowTemplate() {
        return `
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
        `;
    }

    // Save invoice
    $('#saveInvoice').click(function() {
        // Ensure invoice number is populated
        if(!$('input[name="invoice_number"]').val()) {
            let invoiceType = $('input[name="invoice_type"]').val() || 'standard';
            let xhr = $.ajax({
                url: 'ajax/get_next_invoice_number.php',
                method: 'POST',
                data: { type: invoiceType },
                dataType: 'json',
                async: false
            });
            xhr.done(function(response) {
                let prefix = invoiceType === 'standard' ? 'INV' : 'PRO';
                let year = new Date().getFullYear();
                let number = `${prefix}-${year}-${response.next_number.padStart(4, '0')}`;
                $('input[name="invoice_number"]').val(number);
            });
        }
        
        let formData = $('#invoiceForm').serialize();
        $.ajax({
            url: 'ajax/save_invoice.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $('#invoiceModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Error saving invoice');
                }
            }
        });
    });
});

// Create new invoice
function createInvoice(type) {
    $('#invoiceForm')[0].reset();
    $('input[name="invoice_type"]').val(type);
    $('#modalTitle').text('Create New ' + type.charAt(0).toUpperCase() + type.slice(1) + ' Invoice');
    generateInvoiceNumber(type);
}

// Generate invoice number
function generateInvoiceNumber(type) {
    $.ajax({
        url: 'ajax/get_next_invoice_number.php',
        method: 'POST',
        data: { type: type },
        success: function(response) {
            let prefix = type === 'standard' ? 'INV' : 'PRO';
            let year = new Date().getFullYear();
            let number = `${prefix}-${year}-${response.next_number.padStart(4, '0')}`;
            $('input[name="invoice_number"]').val(number);
        }
    });
}

// View invoice
function viewInvoice(id) {
    window.location.href = `view_invoice.php?id=${id}`;
}

// Edit invoice
function editInvoice(id) {
    $.ajax({
        url: 'ajax/get_invoice.php',
        method: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if(response.success && response.data) {
                populateInvoiceForm(response.data);
                $('#invoiceModal').modal('show');
            } else {
                alert('Error loading invoice');
            }
        }
    });
}

// Mark invoice as paid
function markAsPaid(id) {
    if(confirm('Mark this invoice as paid?')) {
        $.ajax({
            url: 'ajax/mark_paid.php',
            method: 'POST',
            data: { id: id },
            success: function(response) {
                if(response.success) {
                    location.reload();
                }
            }
        });
    }
}

// Delete invoice
function deleteInvoice(id) {
    if(confirm('Are you sure you want to delete this invoice?')) {
        $.ajax({
            url: 'ajax/delete_invoice.php',
            method: 'POST',
            data: { id: id },
            success: function(response) {
                if(response.success) {
                    location.reload();
                }
            }
        });
    }
}

// Populate form for editing
function populateInvoiceForm(data) {
    $('#invoice_id').val(data.id);
    $('input[name="invoice_number"]').val(data.invoice_number);
    $('input[name="invoice_type"]').val(data.type);
    $('select[name="client_id"]').val(data.client_id);
    $('input[name="issue_date"]').val(data.issue_date);
    $('input[name="due_date"]').val(data.due_date);
    $('#tax_rate').val(parseFloat(data.tax_rate || 0).toFixed(2));
    
    // Update modal title
    $('#modalTitle').text('Edit ' + (data.type ? data.type.charAt(0).toUpperCase() + data.type.slice(1) : 'Standard') + ' Invoice');
    
    // Clear existing items
    $('#itemsTable tbody').empty();
    
    // Add invoice items
    if(data.items && data.items.length > 0) {
        data.items.forEach(function(item) {
            let row = `
                <tr>
                    <td><input type="text" name="description[]" class="form-control" value="${item.description}" required></td>
                    <td><input type="number" name="quantity[]" class="form-control quantity" value="${item.quantity}" required></td>
                    <td><input type="number" step="0.01" name="unit_price[]" class="form-control price" value="${item.unit_price}" required></td>
                    <td><input type="number" step="0.01" name="total[]" class="form-control item-total" value="${item.total}" readonly></td>
                    <td>
                        <div class="form-check">
                            <input type="checkbox" name="tax_exempt[]" class="form-check-input tax-exempt" ${item.tax_exempt ? 'checked' : ''}>
                            <label class="form-check-label">Tax Exempt</label>
                        </div>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button></td>
                </tr>
            `;
            $('#itemsTable tbody').append(row);
        });
    } else {
        // Add single empty row if no items
        let emptyRow = $(getItemRowTemplate());
        $('#itemsTable tbody').append(emptyRow);
    }
    
    // Set the totals from database values
    $('input[name="subtotal"]').val(parseFloat(data.subtotal || 0).toFixed(2));
    $('input[name="tax_amount"]').val(parseFloat(data.tax_amount || 0).toFixed(2));
    $('input[name="total_amount"]').val(parseFloat(data.total_amount || 0).toFixed(2));
    $('input[name="taxable_subtotal"]').val(parseFloat(data.subtotal || 0).toFixed(2));
    $('input[name="non_taxable_subtotal"]').val('0.00');
    
    calculateInvoiceTotal();
}
