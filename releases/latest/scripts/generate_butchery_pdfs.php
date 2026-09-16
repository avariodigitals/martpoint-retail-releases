<?php
/**
 * Generate PDFs for the Butchery & Frozen Food guides.
 * Uses WeasyPrint when available for full CSS support, otherwise falls back to DOMPDF.
 * Run: php scripts/generate_butchery_pdfs.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function buildPdf($htmlFile, $pdfFile) {
    $baseDir = dirname($htmlFile);

    // Prefer WeasyPrint because it renders the brand CSS (gradients, flex, grid) correctly.
    exec('which weasyprint 2>/dev/null', $whichOut, $whichStatus);
    if ($whichStatus === 0 && !empty($whichOut)) {
        $cmd = 'weasyprint ' . escapeshellarg($htmlFile) . ' ' . escapeshellarg($pdfFile);
        exec($cmd . ' 2>&1', $out, $status);
        if ($status === 0) {
            echo "Created: $pdfFile\n";
            return;
        }
        echo "WeasyPrint failed, falling back to DOMPDF...\n";
    }

    // DOMPDF fallback (best-effort for environments without WeasyPrint)
    $cssFile = $baseDir . '/../martpoint-brand.css';
    $html = file_get_contents($htmlFile);
    if ($html === false) {
        throw new Exception("Could not read $htmlFile");
    }

    $css = file_exists($cssFile) ? file_get_contents($cssFile) : '';

    // Remove external stylesheet links and the Google Font import (DOMPDF cannot fetch remote fonts)
    $html = preg_replace('#<link rel="stylesheet" href="\.\./martpoint-brand\.css">#', '', $html);
    $html = preg_replace('#<link rel="stylesheet" href="https://cdnjs\.cloudflare\.com/ajax/libs/font-awesome/4\.7\.0/css/font-awesome\.min\.css">#', '', $html);
    $css = preg_replace('#@import url\(\'https://fonts\.googleapis\.com[^\)]+\'\);#', '', $css);

    // Resolve CSS variables so dompdf can render the brand colours
    $varMap = [
        '--mp-primary' => '#0057FF',
        '--mp-primary-dark' => '#0044CC',
        '--mp-pay' => '#D97706',
        '--mp-pay-dark' => '#B45309',
        '--mp-bg' => '#F5F4F0',
        '--mp-surface' => '#FFFFFF',
        '--mp-text' => '#292524',
        '--mp-muted' => '#78716C',
        '--mp-border' => '#E7E5E4',
        '--mp-success' => '#059669',
        '--mp-danger' => '#DC2626',
        '--mp-warning' => '#F59E0B',
        '--mp-ink' => '#44403C',
        '--shadow-sm' => '0 1px 2px rgba(41, 37, 36, 0.05)',
        '--shadow' => '0 10px 25px -5px rgba(41, 37, 36, 0.08), 0 4px 10px -4px rgba(41, 37, 36, 0.04)',
        '--radius' => '16px',
        '--radius-sm' => '10px',
        '--space' => '8px',
    ];
    foreach ($varMap as $var => $value) {
        $pattern = '/var\(\s*' . preg_quote($var, '/') . '\s*(?:,\s*[^\)]+)?\)/';
        $css = preg_replace($pattern, $value, $css);
        $html = preg_replace($pattern, $value, $html);
    }

    $dompdfOverrides = '
    .page-cover { background: #0057FF !important; color: #fff !important; }
    .page-cover-ink { background: #0F172A !important; color: #fff !important; }
    .hero-pattern { display: none !important; }
    .page, .cover-inner { display: block !important; }
    .page { padding: 14mm 16mm 22mm !important; position: relative; }
    .cover-inner { position: relative; min-height: 263mm; padding-bottom: 14mm !important; }
    .cover-hero { margin-top: 60mm !important; }
    .spacer { display: none !important; }
    .footer-strip { position: absolute !important; bottom: 0 !important; left: 0 !important; right: 0 !important; width: 100% !important; }
    .grid { display: block !important; }
    .card { width: 48%; float: left; margin: 0 4% 14px 0; page-break-inside: avoid; }
    .card:nth-child(2n) { margin-right: 0; }
    .step { display: block !important; overflow: hidden; margin-bottom: 14px; }
    .step-number { float: left; margin-right: 12px; }
    .step-body { float: left; width: calc(100% - 48px); }
    .tick-list li { display: block !important; position: relative; padding-left: 18px; }
    .tick-list li > i { position: absolute; left: 0; top: 2px; color: #059669; }
    .tip, .tip.warning { background: #F0F9FF; border-left: 4px solid #0057FF; padding: 10px 14px; }
    .code-block { background: #F5F5F4; border: 1px solid #E7E5E4; border-radius: 12px; padding: 14px; font-family: monospace; font-size: 0.76rem; white-space: pre-wrap; }
    ';

    $styleBlock = '<style>' . "\n" . $css . "\n" . $dompdfOverrides . "\n" . '</style>';
    $html = str_replace('</head>', $styleBlock . "\n" . '</head>', $html);

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', false);
    $options->set('chroot', __DIR__ . '/..');
    $options->set('basePath', $baseDir . '/');
    $options->set('defaultFont', 'Helvetica');
    $options->set('dpi', 96);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    file_put_contents($pdfFile, $dompdf->output());
    echo "Created: $pdfFile\n";
}

$root = __DIR__ . '/..';

try {
    buildPdf(
        $root . '/docs/product-design/customer-guide/butchery-frozen-customer-guide.html',
        $root . '/docs/product-design/customer-guide/butchery-frozen-customer-guide.pdf'
    );
    buildPdf(
        $root . '/docs/product-design/technical-guide/butchery-frozen-technical-guide.html',
        $root . '/docs/product-design/technical-guide/butchery-frozen-technical-guide.pdf'
    );
    echo "PDF generation complete.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
