<?php
require_once('config.php');
require_once('functions.php');
require_once('db.php');

// Include FPDF library
require('fpdf/fpdf.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = getPDO();

// Get search term if any
$search = $_GET['search'] ?? '';

// Query to get participants
$query = "SELECT p.name, p.email, p.phone, p.department, p.college, e.title as event_name
          FROM participants p 
          LEFT JOIN events e ON p.event_id = e.id";

$params = [];
if (!empty($search)) {
    $query .= " WHERE p.name LIKE :search OR p.email LIKE :search OR p.phone LIKE :search OR p.college LIKE :search OR p.department LIKE :search";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY p.registration_date DESC";

$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create PDF instance with landscape orientation
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 15); // Set bottom margin to 15mm

// Set document information
$pdf->SetCreator('LEO Club ACGCET');
$pdf->SetAuthor('LEO Club ACGCET');
$pdf->SetTitle('Participants List');
$pdf->SetSubject('Participants Data Export');

// Title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'LEO CLUB OF ACGCET - PARTICIPANTS LIST', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Generated on: ' . date('F j, Y, g:i a'), 0, 1, 'C');
$pdf->Ln(8);

// Calculate column widths based on content
$col_widths = [
    'name' => 50,
    'department' => 40,
    'phone' => 30,
    'email' => 60,
    'college' => 50,
    'event' => 40
];

// Adjust column widths if content is too long
foreach ($participants as $participant) {
    $col_widths['name'] = max($col_widths['name'], $pdf->GetStringWidth($participant['name']) + 6);
    $col_widths['department'] = max($col_widths['department'], $pdf->GetStringWidth($participant['department'] ?? 'N/A') + 6);
    $col_widths['email'] = max($col_widths['email'], $pdf->GetStringWidth($participant['email']) + 6);
    $col_widths['college'] = max($col_widths['college'], $pdf->GetStringWidth($participant['college']) + 6);
}

// Ensure total width doesn't exceed page width (297mm - margins)
$total_width = array_sum($col_widths);
$max_width = 277; // 297mm - 10mm left margin - 10mm right margin
if ($total_width > $max_width) {
    $ratio = $max_width / $total_width;
    foreach ($col_widths as $key => $width) {
        $col_widths[$key] = $width * $ratio;
    }
}

// Table header
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(241, 196, 15); // LEO Club yellow color
$pdf->SetTextColor(0, 0, 0); // Black text
$pdf->Cell($col_widths['name'], 8, 'Name', 1, 0, 'C', true);
$pdf->Cell($col_widths['department'], 8, 'Department', 1, 0, 'C', true);
$pdf->Cell($col_widths['phone'], 8, 'Phone', 1, 0, 'C', true);
$pdf->Cell($col_widths['email'], 8, 'Email', 1, 0, 'C', true);
$pdf->Cell($col_widths['college'], 8, 'College', 1, 0, 'C', true);
$pdf->Cell($col_widths['event'], 8, 'Event', 1, 1, 'C', true);

// Table data
$pdf->SetFont('Arial', '', 9);
$fill = false;
foreach ($participants as $participant) {
    // Alternate row color for better readability
    $pdf->SetFillColor($fill ? 240: 255, 255, 255);
    $fill = !$fill;
    
    // Name (with multi-cell if too long)
    $pdf->Cell($col_widths['name'], 6, substr($participant['name'], 0, 30), 1, 0, 'L', true);
    
    // Department
    $pdf->Cell($col_widths['department'], 6, substr($participant['department'] ?? 'N/A', 0, 25), 1, 0, 'L', true);
    
    // Phone
    $pdf->Cell($col_widths['phone'], 6, $participant['phone'], 1, 0, 'C', true);
    
    // Email (with multi-cell if too long)
    $pdf->Cell($col_widths['email'], 6, substr($participant['email'], 0, 35), 1, 0, 'L', true);
    
    // College
    $pdf->Cell($col_widths['college'], 6, substr($participant['college'], 0, 30), 1, 0, 'L', true);
    
    // Event
    $pdf->Cell($col_widths['event'], 6, substr($participant['event_name'] ?? 'N/A', 0, 25), 1, 1, 'L', true);
}

// Add a footer with page numbers and copyright
$pdf->SetY(-15);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 10, 'Page ' . $pdf->PageNo() . ' of {nb}', 0, 0, 'C');
$pdf->SetY(-12);
$pdf->Cell(0, 10, '© ' . date('Y') . ' LEO Club of ACGCET - Confidential', 0, 0, 'C');

// Output the PDF
$filename = 'participants_export_' . date('Ymd_His') . '.pdf';
$pdf->Output('D', $filename); // D for download
exit();