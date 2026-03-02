// Calculate quotation total
function calculateQuotationTotal() {
    let taxableSubtotal = 0;
    let nonTaxableSubtotal = 0;
    
    $('.item-total').each(function() {
        let itemTotal = parseFloat($(this).val() || 0);
        let isTaxExempt = $(this).closest('tr').find('.tax-exempt').is(':checked');
        
        if(isTaxExempt) {
            nonTaxableSubtotal += itemTotal;
        } else {
            taxableSubtotal += itemTotal;
        }
    });
    
    let subtotal = taxableSubtotal + nonTaxableSubtotal;
    let taxRate = parseFloat($('#tax_rate').val() || 0);
    let taxAmount = taxableSubtotal * (taxRate / 100);
    let total = subtotal + taxAmount;

    $('input[name="taxable_subtotal"]').val(taxableSubtotal.toFixed(2));
    $('input[name="non_taxable_subtotal"]').val(nonTaxableSubtotal.toFixed(2));
    $('input[name="subtotal"]').val(subtotal.toFixed(2));
    $('input[name="tax_amount"]').val(taxAmount.toFixed(2));
    $('input[name="total_amount"]').val(total.toFixed(2));
}

$(document).ready(function() {
    // Initialize DataTable
    const quotationTable = $('#quotationsTable').DataTable({
        order: [[2, 'desc']],
        responsive: true
    });

    // Calculate item total
    function calculateItemTotal() {
        let quantity = $(this).closest('tr').find('.quantity').val() || 0;
        let price = $(this).closest('tr').find('.price').val() || 0;
        let total = quantity * price;
        $(this).closest('tr').find('.item-total').val(total.toFixed(2));
        calculateQuotationTotal();
    }

    // Add new item row
    $('#addItem').click(function() {
        let newRow = $('#itemsTable tbody tr:first').clone();
        newRow.find('input').val('');
        $('#itemsTable tbody').append(newRow);
    });

    // Remove item row
    $(document).on('click', '.remove-item', function() {
        if($('#itemsTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateQuotationTotal();
        }
    });

    // Calculate totals when inputs change
    $(document).on('input', '.quantity, .price, #tax_rate', calculateItemTotal);
    $(document).on('change', '.tax-exempt', calculateQuotationTotal);

    // Generate quotation number
    function generateQuotationNumber() {
        $.ajax({
            url: 'ajax/get_next_quotation_number.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                $('input[name="quotation_number"]').val(response.number);
            }
        });
    }
    
    // Show modal and generate quotation number
    $('#quotationsTable').on('click', 'button', function(e) {
        if($(this).text().includes('New Quotation') || $(e.target).closest('button').length) {
            // Only generate for new quotations, not edits
            if(!$('#quotation_id').val()) {
                generateQuotationNumber();
            }
        }
    });
    
    // Generate quotation number when modal is shown for new quotation
    $('#quotationModal').on('show.bs.modal', function() {
        if(!$('#quotation_id').val()) {
            generateQuotationNumber();
        }
    });

    // Save quotation
    $('#saveQuotation').click(function() {
        // Ensure quotation number is populated
        if(!$('input[name="quotation_number"]').val()) {
            let xhr = $.ajax({
                url: 'ajax/get_next_quotation_number.php',
                method: 'GET',
                dataType: 'json',
                async: false
            });
            xhr.done(function(response) {
                $('input[name="quotation_number"]').val(response.number);
            });
        }
        
        $.ajax({
            url: 'ajax/save_quotation.php',
            method: 'POST',
            data: $('#quotationForm').serialize(),
            success: function(response) {
                if(response.success) {
                    $('#quotationModal').modal('hide');
                    location.reload();
                } else {
                    alert(response.message || 'Error saving quotation');
                }
            }
        });
    });
});

// View quotation
function viewQuotation(id) {
    window.location.href = `view_quotation.php?id=${id}`;
}

// Edit quotation
function editQuotation(id) {
    $.ajax({
        url: 'ajax/get_quotation.php',
        method: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            populateQuotationForm(response.data);
            $('#quotationModal').modal('show');
        }
    });
}

// Convert to invoice
function convertToInvoice(id) {
    if(confirm('Convert this quotation to invoice?')) {
        $.ajax({
            url: 'ajax/convert_to_invoice.php',
            method: 'POST',
            data: { id: id },
            success: function(response) {
                if(response.success) {
                    window.location.href = 'invoices.php';
                }
            }
        });
    }
}

// Delete quotation
function deleteQuotation(id) {
    if(confirm('Are you sure you want to delete this quotation?')) {
        $.ajax({
            url: 'ajax/delete_quotation.php',
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

// Populate quotation form for editing
function populateQuotationForm(data) {
    $('#quotation_id').val(data.id);
    $('input[name="quotation_number"]').val(data.quotation_number);
    $('select[name="client_id"]').val(data.client_id);
    $('input[name="issue_date"]').val(data.issue_date);
    $('input[name="valid_until"]').val(data.valid_until);
    $('#tax_rate').val(parseFloat(data.tax_rate || 0).toFixed(2));
    
    // Update modal title
    $('#modalTitle').text('Edit Quotation');
    
    // Clear existing items
    $('#itemsTable tbody').empty();
    
    // Add quotation items
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
        let emptyRow = `
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
        $('#itemsTable tbody').append(emptyRow);
    }
    
    // Recalculate totals
    $('input[name="subtotal"]').val(parseFloat(data.subtotal).toFixed(2));
    $('input[name="tax_amount"]').val(parseFloat(data.tax_amount).toFixed(2));
    $('input[name="total_amount"]').val(parseFloat(data.total_amount).toFixed(2));
    $('input[name="taxable_subtotal"]').val(parseFloat(data.subtotal).toFixed(2));
    $('input[name="non_taxable_subtotal"]').val('0.00');
}
