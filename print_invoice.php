<?php
require_once 'config/config.php';
require_once 'vendor/autoload.php'; // For TCPDF

$id = $_GET['id'];
$invoice = $conn->query("SELECT i.*, c.* 
                        FROM invoices i 
                        JOIN clients c ON i.client_id = c.id 
                        WHERE i.id = $id")->fetch_assoc();

$items = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $id");
$settings = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc() ?? [];

// Create PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($settings['company_name']);
$pdf->SetTitle(($invoice['type'] ?? 'standard') . ' Invoice #' . $invoice['invoice_number']);

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 10);

// Company details
if($settings['logo_path'] ?? false) {
    $pdf->Image($settings['logo_path'], 15, 15, 50);
    $pdf->SetXY(15, 40);
} else {
    $pdf->SetXY(15, 15);
}
$pdf->Cell(0, 5, $settings['company_name'] ?? 'Company Name', 0, 1);
$pdf->MultiCell(0, 5, $settings['company_address'] ?? '', 0, 'L');
$pdf->Cell(0, 5, 'Phone: ' . ($settings['company_phone'] ?? ''), 0, 1);
$pdf->Cell(0, 5, 'Email: ' . ($settings['company_email'] ?? ''), 0, 1);

// Invoice details
$pdf->SetXY(120, 15);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->Cell(0, 10, strtoupper($invoice['type'] ?? 'STANDARD') . ' INVOICE', 0, 1, 'R');
$pdf->SetFont('helvetica', '', 10);
$pdf->SetXY(120, 25);
$pdf->Cell(0, 5, '#' . $invoice['invoice_number'], 0, 1, 'R');
$pdf->SetXY(120, 30);
$pdf->Cell(0, 5, 'Issue Date: ' . date('d/m/Y', strtotime($invoice['issue_date'])), 0, 1, 'R');
$pdf->Cell(0, 5, 'Due Date: ' . date('d/m/Y', strtotime($invoice['due_date'])), 0, 1, 'R');

// Client details
$pdf->SetXY(15, 80);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 5, 'Bill To:', 0, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, $invoice['name'], 0, 1);
$pdf->MultiCell(0, 5, $invoice['address'], 0, 'L');
$pdf->Cell(0, 5, 'Phone: ' . $invoice['phone'], 0, 1);
$pdf->Cell(0, 5, 'Email: ' . $invoice['email'], 0, 1);

// Items table
$pdf->SetXY(15, 130);
$pdf->SetFillColor(240, 240, 240);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(80, 7, 'Description', 1, 0, 'L', true);
$pdf->Cell(30, 7, 'Quantity', 1, 0, 'R', true);
$pdf->Cell(30, 7, 'Unit Price', 1, 0, 'R', true);
$pdf->Cell(30, 7, 'Total', 1, 1, 'R', true);

$pdf->SetFont('helvetica', '', 10);
while($item = $items->fetch_assoc()) {
    $pdf->Cell(80, 7, $item['description'], 1);
    $pdf->Cell(30, 7, $item['quantity'], 1, 0, 'R');
    $pdf->Cell(30, 7, number_format($item['unit_price'], 2), 1, 0, 'R');
    $pdf->Cell(30, 7, number_format($item['total'], 2), 1, 1, 'R');
}

// Totals
$pdf->Cell(140, 7, 'Subtotal:', 1, 0, 'R');
$pdf->Cell(30, 7, number_format($invoice['subtotal'], 2), 1, 1, 'R');
$pdf->Cell(140, 7, 'Tax (' . ($settings['tax_rate'] ?? 0) . '%):', 1, 0, 'R');
$pdf->Cell(30, 7, number_format($invoice['tax_amount'], 2), 1, 1, 'R');
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(140, 7, 'Total:', 1, 0, 'R');
$pdf->Cell(30, 7, number_format($invoice['total_amount'], 2), 1, 1, 'R');

// Output PDF
$pdf->Output('Invoice_' . $invoice['invoice_number'] . '.pdf', 'I');
