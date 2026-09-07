<?php
/**
 * MartPoint Retail — Plan Comparison PDF Generator
 *
 * Reads docs/plans/martpoint-plans.html and generates a PDF using Dompdf.
 * The HTML file is the single source of truth — edit it directly.
 *
 * Usage:  php docs/plans/generate_pdf.php
 * Output: docs/plans/MartPoint-Plans.pdf
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ── Read the editable HTML ──
$htmlPath = __DIR__ . '/martpoint-plans.html';
$html = file_get_contents($htmlPath);
if(!$html){
    die("Cannot read {$htmlPath}\n");
}

// ── Inject the date ──
$date = date('F j, Y');
$html = str_replace('__DATE__', $date, $html);

// ── Embed the logo as base64 so Dompdf can render it ──
$logoPath = __DIR__ . '/martpoint-logo.png';
if(file_exists($logoPath)){
    $logoB64 = base64_encode(file_get_contents($logoPath));
    $html = str_replace('src="martpoint-logo.png"', 'src="data:image/png;base64,' . $logoB64 . '"', $html);
}

// ── Generate PDF ──
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');
$options->set('dpi', 150);

// Suppress deprecation warnings from older Dompdf on PHP 8.4
error_reporting(E_ALL & ~E_DEPRECATED);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$outputPath = __DIR__ . '/MartPoint-Plans.pdf';
file_put_contents($outputPath, $dompdf->output());

echo "PDF generated: {$outputPath}\n";
echo "Size: " . round(filesize($outputPath) / 1024, 1) . " KB\n";
