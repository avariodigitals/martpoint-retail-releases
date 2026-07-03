<!DOCTYPE html>
<html>
<head>
<?php include APPPATH . "views/comman/code_css.php"; ?>
<style>
body { background: #f5f5f5; padding: 20px; }
.card-container {
    width: 324px;
    height: 204px;
    border-radius: 14px;
    position: relative;
    overflow: hidden;
    margin: 0 auto 20px auto;
    background: linear-gradient(135deg, <?=htmlspecialchars($card_color ?? '#fdfbf7');?> 0%, #f5f0e8 100%);
    border: 1.5px solid #d4c8b8;
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
.card-brand {
    position: absolute;
    top: 12px;
    left: 14px;
    font-size: 13px;
    font-weight: 800;
    color: #8b7355;
    opacity: 0.25;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    transform: rotate(-8deg);
    transform-origin: left center;
}
.card-barcode {
    position: absolute;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
}
.card-barcode img {
    height: 38px;
}
.card-type-label {
    font-size: 11px;
    color: #8b7355;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-top: 4px;
    font-weight: 600;
}
.card-body {
    padding: 56px 18px 12px 18px;
    text-align: center;
}
.card-label {
    font-size: 9px;
    color: #9e8e7e;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 2px;
}
.card-value {
    font-size: 32px;
    font-weight: 700;
    color: #3d3229;
    margin-bottom: 2px;
    line-height: 1.1;
}
.card-number {
    font-size: 13px;
    font-family: "Courier New", Courier, monospace;
    color: #6b5b4f;
    letter-spacing: 3px;
    margin-bottom: 6px;
}
.card-name {
    font-size: 13px;
    color: #3d3229;
    font-weight: 600;
    margin-bottom: 2px;
}
.card-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 8px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255,255,255,0.55);
    border-top: 1px solid rgba(0,0,0,0.06);
}
.card-signature {
    font-size: 10px;
    color: #8b7355;
    font-style: italic;
    max-width: 100px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.card-expiry {
    font-size: 10px;
    color: #3d3229;
    font-weight: 700;
    text-align: center;
    flex: 1;
}
.card-watermark {
    position: absolute;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 52px;
    font-weight: 900;
    color: rgba(139,115,85,0.04);
    text-transform: uppercase;
    letter-spacing: 4px;
    pointer-events: none;
    user-select: none;
}
@media print {
    body { background: #fff; padding: 0; }
    .no-print { display: none !important; }
    .card-container {
        box-shadow: none;
        margin: 0;
        page-break-inside: avoid;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
</head>
<body>
<div class="text-center no-print" style="margin-bottom:20px;">
<h3><i class="fa fa-ticket"></i> Gift Card Preview</h3>
<button class="btn btn-primary" onclick="window.print()"><i class="fa fa-print"></i> Print Card</button>
<button class="btn btn-success" onclick="downloadCard('giftCardPrint','gift-card-<?=htmlspecialchars($card->card_number ?? 'export');?>.png')"><i class="fa fa-download"></i> Download PNG</button>
<a href="<?=base_url();?>gift_cards" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back</a>
</div>

<div class="card-container" id="giftCardPrint">
<div class="card-brand"><?=htmlspecialchars($brand_name ?? 'MartPoint');?></div>
<div class="card-barcode">
<img src="https://bwipjs-api.metafloor.com/?bcid=code128&text=<?=urlencode($card->card_number ?? 'GC0000');?>&scale=3&height=8" alt="barcode">
</div>
<div class="card-watermark"><?=htmlspecialchars($brand_name ?? 'MartPoint');?></div>
<div class="card-body">
<div class="card-label">Gift Card Value</div>
<div class="card-value"><?=store_number_format($card->initial_value ?? 0);?></div>
<div class="card-number"><?=htmlspecialchars($card->card_number ?? '');?></div>
<div class="card-name"><?=htmlspecialchars($customer->customer_name ?? 'Gift Card');?></div>
<div class="card-type-label">Gift Card</div>
</div>
<div class="card-footer">
<div class="card-signature"><?=htmlspecialchars($brand_name ?? 'MartPoint');?></div>
<div class="card-expiry"><?=($card->expiry_date ? 'Expires: ' . show_date($card->expiry_date) : 'No Expiry');?></div>
<div style="font-size:9px;color:#8b7355;">ID: <?=str_pad($card->id ?? 0, 4, '0', STR_PAD_LEFT);?></div>
</div>
</div>

<div class="text-center no-print" style="margin-top:30px;color:#888;font-size:12px;">
<p>Card size: 85.6mm x 53.98mm (standard ATM card / ID-1)</p>
<p>Use a thermal card printer or label printer for best results.</p>
</div>
<script src="<?=base_url();?>theme/plugins/tableExporter/libs/html2canvas/html2canvas.min.js"></script>
<script>
function downloadCard(elementId, filename){
    var el = document.getElementById(elementId);
    if(!el) return;
    html2canvas(el, {scale: 2, useCORS: true, backgroundColor: null}).then(function(canvas){
        var link = document.createElement('a');
        link.download = filename;
        link.href = canvas.toDataURL('image/png');
        link.click();
    });
}
</script>
</body>
</html>
