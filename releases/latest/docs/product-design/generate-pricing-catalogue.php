<?php
/**
 * Generates the customer-facing MartPoint Retail Pricing Catalogue HTML
 * from the approved pricing source: application/config/martpoint_pricing.php
 * Usage: php docs/product-design/generate-pricing-catalogue.php > docs/product-design/MartPoint-Pricing-Catalogue.html
 */

$pricing = include __DIR__ . '/../../application/config/martpoint_pricing.php';
$plans = $pricing['plans'];
$version = $pricing['pricing_version'];
$effective = DateTime::createFromFormat('Y-m-d', $pricing['effective_date']);
$effective_label = $effective ? $effective->format('j F Y') : date('j F Y');
$generated_label = date('j F Y');

function naira($n) {
    return '&#x20A6;' . number_format((int)$n, 0);
}
function num($n) {
    return number_format((int)$n, 0);
}
function storage_gb($mb) {
    $gb = $mb / 1024;
    return ($gb == (int)$gb ? (int)$gb : $gb) . ' GB';
}
function footer($page) {
    return '<div class="footer-strip"><span>MartPoint Retail Intelligence Platform</span><span>Page ' . $page . '</span></div>';
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MartPoint Retail Pricing Catalogue</title>
  <link rel="icon" href="favicon.webp" type="image/webp">
  <style>
    :root {
      --mp-primary: #0057FF;
      --mp-primary-dark: #0044CC;
      --mp-ink: #0F172A;
      --mp-muted: #64748B;
      --mp-border: #E2E8F0;
      --mp-bg: #F8FAFC;
      --mp-sky: #EAF1FF;
    }
    * { box-sizing: border-box; }
    html, body {
      margin: 0; padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background: #E8E6E1; color: var(--mp-ink); line-height: 1.45; -webkit-font-smoothing: antialiased;
    }
    @page { size: A4; margin: 0; }
    .document { max-width: 210mm; margin: 0 auto; }
    .page {
      width: 210mm; height: 297mm; margin: 0 auto 12px; padding: 12mm 14mm;
      background: #fff; position: relative; overflow: hidden; display: flex; flex-direction: column;
      page-break-after: always; break-after: page;
      -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .page:last-child { margin-bottom: 0; page-break-after: auto; break-after: auto; }
    @media print { body { background: #fff; } .page { margin: 0; box-shadow: none; } }
    h1, h2, h3 { margin: 0 0 0.3em; font-weight: 800; line-height: 1.12; letter-spacing: -0.02em; color: var(--mp-ink); }
    h1 { font-size: 2.3rem; }
    h2 { font-size: 1.6rem; }
    h3 { font-size: 0.95rem; }
    p { margin: 0 0 0.55em; line-height: 1.5; color: #334155; }
    .label { display: inline-block; font-size: 0.66rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.09em; color: var(--mp-primary); margin-bottom: 6px; }
    .label.light { color: rgba(255,255,255,0.85); }
    .lead { font-size: 0.95rem; line-height: 1.5; color: #334155; }
    .small { font-size: 0.78rem; line-height: 1.42; }
    .muted { color: var(--mp-muted); }
    .grid { display: grid; gap: 12px; }
    .cols-2 { grid-template-columns: repeat(2, 1fr); }
    .cols-3 { grid-template-columns: repeat(3, 1fr); }
    .cols-4 { grid-template-columns: repeat(4, 1fr); }
    .spacer { flex: 1; }
    .fill { flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 24px; }
    .mb-1 { margin-bottom: 6px; }
    .mb-2 { margin-bottom: 12px; }
    .mb-3 { margin-bottom: 18px; }
    .footer-strip { margin-top: auto; padding-top: 10px; border-top: 1px solid var(--mp-border); font-size: 0.74rem; color: var(--mp-muted); display: flex; justify-content: space-between; align-items: center; }
    .page-cover, .page-cta { background: linear-gradient(135deg, var(--mp-primary) 0%, var(--mp-primary-dark) 100%); color: #fff; }
    .page-cover .footer-strip, .page-cta .footer-strip { border-top-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.75); }
    .page-cover h1, .page-cta h1 { color: #fff; }
    .page-cover p, .page-cta p { color: rgba(255,255,255,0.88); }
    .logo { height: 38px; width: auto; }
    .logo-light { filter: brightness(0) invert(1); }
    .pill { display: inline-flex; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; padding: 6px 14px; border-radius: 999px; background: rgba(255,255,255,0.14); color: #fff; border: 1px solid rgba(255,255,255,0.25); }
    .pill-dark { background: var(--mp-primary); color: #fff; border: none; }
    .btn { display: inline-flex; font-size: 0.85rem; font-weight: 700; padding: 12px 24px; border-radius: 999px; background: #fff; color: var(--mp-primary); margin-top: 14px; }
    .btn-outline { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.4); }
    .card { background: var(--mp-bg); border: 1px solid var(--mp-border); border-radius: 10px; padding: 14px 18px; break-inside: avoid; page-break-inside: avoid; }
    .card-white { background: #fff; border: 1px solid var(--mp-border); border-radius: 10px; padding: 14px 18px; break-inside: avoid; page-break-inside: avoid; }
    .note { background: var(--mp-sky); border-left: 4px solid var(--mp-primary); border-radius: 0 10px 10px 0; padding: 12px 16px; margin: 12px 0; break-inside: avoid; page-break-inside: avoid; }
    .note p { margin: 0; }
    .plan-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 10px; padding: 14px 16px; break-inside: avoid; page-break-inside: avoid; position: relative; }
    .plan-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px; border-radius: 10px 10px 0 0; background: var(--accent, var(--mp-primary)); }
    .plan-card h3 { font-size: 0.85rem; color: var(--accent, var(--mp-primary)); text-transform: uppercase; letter-spacing: 0.06em; margin: 4px 0 4px; }
    .plan-card .price { font-size: 1.5rem; font-weight: 800; color: var(--mp-ink); line-height: 1.05; margin-bottom: 4px; }
    .plan-card .per { font-size: 0.74rem; color: var(--mp-muted); margin-bottom: 8px; }
    .plan-card .quota { display: flex; justify-content: space-between; font-size: 0.76rem; color: #334155; padding: 4px 0; border-top: 1px solid var(--mp-border); }
    .plan-card .quota strong { font-weight: 700; }
    table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
    th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid var(--mp-border); vertical-align: top; }
    th { font-size: 0.74rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mp-ink); background: #F1F5F9; }
    th.num, td.num { text-align: right; font-weight: 700; }
    tr:last-child td { border-bottom: none; }
    .quota-table thead th { color: #fff; }
    .quota-table thead th:first-child { color: var(--mp-muted); }
    .bullet-list { list-style: none; padding: 0; margin: 0; }
    .bullet-list li { display: flex; gap: 10px; align-items: flex-start; font-size: 0.85rem; color: #334155; margin-bottom: 12px; }
    .bullet-list li:last-child { margin-bottom: 0; }
    .bullet-list li span { flex: 1; }
    .dot { width: 7px; height: 7px; border-radius: 50%; margin-top: 5px; flex-shrink: 0; }
    .dot-blue { background: var(--mp-primary); }
    .bullet-list li .check { color: var(--mp-primary); font-weight: 700; flex: 0 0 auto; }
    .flow-line { display: flex; align-items: center; gap: 10px; margin: 12px 0 14px; }
    .flow-step { flex: 1; text-align: center; }
    .flow-step strong { display: block; font-size: 0.82rem; color: var(--mp-ink); }
    .flow-step span { font-size: 0.68rem; color: var(--mp-muted); }
    .flow-arrow { font-size: 1.05rem; color: var(--mp-primary); font-weight: 300; }
    .capability-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; }
    .capability { font-size: 0.74rem; color: #334155; display: flex; align-items: flex-start; gap: 6px; }
    .feature { background: #fff; border: 1px solid var(--mp-border); border-radius: 10px; padding: 12px 16px; break-inside: avoid; page-break-inside: avoid; display: flex; flex-direction: column; justify-content: center; }
    .feature h3 { font-size: 0.85rem; margin-bottom: 4px; }
    .feature p { font-size: 0.76rem; color: var(--mp-muted); margin: 0; line-height: 1.4; }
    .industry-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .industry { display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #334155; break-inside: avoid; page-break-inside: avoid; }
    .industry-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
    .contact-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 16px; }
    .contact-card { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); border-radius: 10px; padding: 12px 14px; color: #fff; break-inside: avoid; page-break-inside: avoid; }
    .contact-card strong { display: block; font-size: 0.68rem; color: rgba(255,255,255,0.75); margin-bottom: 4px; }
    .contact-card span { font-size: 0.8rem; font-weight: 700; }
    .qr-wrap { display: flex; align-items: center; gap: 14px; margin-top: 16px; }
    .qr-wrap img { width: 84px; height: 84px; border-radius: 8px; background: #fff; padding: 4px; }
    .qr-text { color: rgba(255,255,255,0.85); font-size: 0.76rem; line-height: 1.4; }
    .qr-text strong { color: #fff; display: block; font-size: 0.85rem; margin-bottom: 3px; }
    .closing { font-size: 0.85rem; color: rgba(255,255,255,0.8); margin-top: 12px; }
    .exclusions { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 12px; margin-top: 8px; }
    .exclusions div { font-size: 0.76rem; color: #334155; display: flex; gap: 7px; }
    .exclusions div::before { content: '\00D7'; color: #DC2626; font-weight: 700; }
    @media screen and (max-width: 900px) {
      .document { padding: 10px; }
      .page { width: 100%; height: auto; min-height: auto; margin-bottom: 20px; }
      .cols-2, .cols-3, .cols-4, .capability-grid, .contact-grid, .exclusions { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
<div class="document">

  <!-- Page 1: Cover -->
  <section class="page page-cover">
    <div style="display:flex; align-items:center; justify-content:space-between;">
      <img src="logo.png" alt="MartPoint" class="logo logo-light">
      <span class="pill">Version <?= $version ?></span>
    </div>
    <div class="fill">
      <div style="max-width:88%;">
        <span class="label light">Customer Pricing Catalogue</span>
        <h1 style="font-size:2.9rem; margin-bottom:16px;">MartPoint Retail Pricing Catalogue</h1>
        <p class="lead" style="font-size:1.05rem; max-width:92%; margin-bottom:24px;">Annual software plans, implementation and optional services for growing retail and service businesses.</p>
        <div style="display:flex; gap:10px; margin-bottom:28px; flex-wrap:wrap;">
          <span class="pill">Annual plans</span>
          <span class="pill">Online Store included</span>
          <span class="pill">Built for growing businesses</span>
        </div>
        <p class="small" style="color:rgba(255,255,255,0.75); max-width:92%;">All prices are stated in Nigerian Naira. Applicable taxes and third-party charges are excluded unless expressly stated in an issued quotation.</p>
      </div>
    </div>
    <p class="small" style="color:rgba(255,255,255,0.85); margin-bottom:14px;"><strong>Effective date:</strong> <?= $effective_label ?></p>
    <?= footer('Cover') ?>
  </section>

  <!-- Page 2: Plan overview -->
  <section class="page">
    <span class="label">Retail Cloud Plans</span>
    <h2 class="mb-1">Annual licence plans</h2>
    <p class="lead mb-2">Four annual plans for growing retail and service businesses. All plans include access to the standard customer-facing Online Store.</p>
    <div class="grid cols-2" style="flex:1; grid-auto-rows:1fr; gap:14px;">
      <?php foreach ($plans as $key => $p): ?>
      <div class="plan-card" style="--accent: <?= $p['accent'] ?>;">
        <h3><?= htmlspecialchars($p['name']) ?></h3>
        <div class="price"><?= naira($p['annual_price']) ?></div>
        <div class="per">per year, per business account</div>
        <div class="quota"><span>Branches</span><strong><?= num($p['branch_limit']) ?></strong></div>
        <div class="quota"><span>Users</span><strong><?= num($p['user_limit']) ?></strong></div>
        <div class="quota"><span>Main products</span><strong><?= num($p['product_limit']) ?></strong></div>
        <div class="quota"><span>Product variations</span><strong><?= num($p['variation_limit']) ?></strong></div>
        <div class="quota"><span>Online products</span><strong><?= num($p['online_product_limit']) ?></strong></div>
        <div class="quota"><span>Custom domain</span><strong>Optional</strong></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?= footer('2') ?>
  </section>

  <!-- Page 3: Quota comparison -->
  <section class="page" style="background:#FAFBFF;">
    <span class="label">Detailed Quota Comparison</span>
    <h2 class="mb-1">What each plan includes</h2>
    <p class="lead mb-2">All limits apply to the entire subscribed business account.</p>
    <div class="fill">
    <table class="quota-table">
      <thead>
        <tr>
          <th>Limit</th>
          <?php foreach ($plans as $p): ?>
          <th class="num" style="background: <?= $p['accent'] ?>;"><?= htmlspecialchars($p['name']) ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <tr><td>Annual licence</td><?php foreach ($plans as $p): ?><td class="num"><?= naira($p['annual_price']) ?></td><?php endforeach; ?></tr>
        <tr><td>Branches</td><?php foreach ($plans as $p): ?><td class="num"><?= num($p['branch_limit']) ?></td><?php endforeach; ?></tr>
        <tr><td>Users</td><?php foreach ($plans as $p): ?><td class="num"><?= num($p['user_limit']) ?></td><?php endforeach; ?></tr>
        <tr><td>Main products</td><?php foreach ($plans as $p): ?><td class="num"><?= num($p['product_limit']) ?></td><?php endforeach; ?></tr>
        <tr><td>Product variations</td><?php foreach ($plans as $p): ?><td class="num"><?= num($p['variation_limit']) ?></td><?php endforeach; ?></tr>
        <tr><td>Products publishable online</td><?php foreach ($plans as $p): ?><td class="num"><?= num($p['online_product_limit']) ?></td><?php endforeach; ?></tr>
        <tr><td>Services</td><?php foreach ($plans as $p): ?><td class="num"><?= num($p['service_limit']) ?></td><?php endforeach; ?></tr>
        <tr><td>Media storage</td><?php foreach ($plans as $p): ?><td class="num"><?= storage_gb($p['media_storage_limit_mb']) ?></td><?php endforeach; ?></tr>
        <tr><td>Custom domain</td><?php foreach ($plans as $p): ?><td class="num">Optional</td><?php endforeach; ?></tr>
      </tbody>
    </table>
    <p class="small muted" style="margin:0;">The primary differences between the plans are capacity: branches, users, catalogue size, storage and online-product allowance. A custom domain is available on every plan at an additional cost.</p>
    </div>
    <?= footer('3') ?>
  </section>

  <!-- Page 4: Features included -->
  <section class="page">
    <span class="label">Included in Every Plan</span>
    <h2 class="mb-1">What every Retail Cloud plan includes</h2>
    <p class="lead mb-2">Core capabilities are available across all plans.</p>
    <div class="grid cols-2" style="flex:1; grid-auto-rows:1fr; gap:10px;">
      <div class="feature"><h3>Point of sale and sales management</h3><p>Fast checkout and sales tracking.</p></div>
      <div class="feature"><h3>Inventory and stock control</h3><p>Track stock, transfers and purchases.</p></div>
      <div class="feature"><h3>Product variations</h3><p>Manage size, colour and option combinations.</p></div>
      <div class="feature"><h3>Customer records</h3><p>History, contact and follow-up information.</p></div>
      <div class="feature"><h3>Supplier records</h3><p>Supplier contacts and purchase history.</p></div>
      <div class="feature"><h3>Purchases</h3><p>Record stock receipts and supplier invoices.</p></div>
      <div class="feature"><h3>Expenses</h3><p>Track operating costs and outflows.</p></div>
      <div class="feature"><h3>Business reporting</h3><p>Clear reports for daily decisions.</p></div>
      <div class="feature"><h3>User and role management</h3><p>Staff accounts with controlled access.</p></div>
      <div class="feature"><h3>Online Store</h3><p>A standard customer-facing storefront.</p></div>
      <div class="feature"><h3>Standard software updates</h3><p>Regular improvements included.</p></div>
      <div class="feature"><h3>Standard remote software support</h3><p>Help with the software during business hours.</p></div>
    </div>
    <?= footer('4') ?>
  </section>

  <!-- Page 5: Products and variations -->
  <section class="page" style="background:#FAFBFF;">
    <span class="label">Understanding Limits</span>
    <h2 class="mb-1">Products and product variations</h2>
    <p class="lead mb-2">A main product is one top-level catalogue item. For example, “Classic Polo Shirt” counts as one product. Its individual size and colour combinations count as product variations. A shirt available in five sizes and eight colours uses one main product and 40 product variations.</p>
    <div class="fill">
      <div class="note" style="margin:0;">
        <p class="small"><strong>A simple product without variations</strong> uses one main-product allowance and one sellable inventory record.</p>
      </div>
      <div class="card-white">
        <p class="small" style="margin:0;"><strong>Example:</strong> Classic Polo Shirt &rarr; 5 sizes &times; 8 colours = <strong>1 main product + 40 product variations</strong>.</p>
      </div>
      <div class="card">
        <h3>Capacity, not features</h3>
        <p class="small" style="margin:0;">The primary differences between plans are capacity: branches, users, catalogue size, storage and online-product allowance.</p>
      </div>
    </div>
    <?= footer('5') ?>
  </section>

  <!-- Page 6: Online Store -->
  <section class="page">
    <span class="label">Online Store</span>
    <h2 class="mb-1">Included with every Retail Cloud plan</h2>
    <p class="lead mb-2">One standard Online Store is included with every active MartPoint Retail Cloud plan, subject to the product-publishing allowance of the selected plan.</p>
    <div class="fill">
      <ul class="bullet-list">
        <li><span class="check">&#10003;</span><span>The customer supplies and uploads their complete product catalogue.</span></li>
        <li><span class="check">&#10003;</span><span>MartPoint may create up to 20 sample products strictly for setup testing, demonstration and launch verification.</span></li>
        <li><span class="check">&#10003;</span><span>MartPoint does not provide full product upload for any client as part of the annual licence.</span></li>
        <li><span class="check">&#10003;</span><span>Catalogue preparation, product photography, bulk data cleanup and manual product entry are separately quoted services.</span></li>
        <li><span class="check">&#10003;</span><span>Payment gateway transaction charges are charged separately by the selected payment provider.</span></li>
        <li><span class="check">&#10003;</span><span>Custom storefront design is not included in the annual licence.</span></li>
        <li><span class="check">&#10003;</span><span>A custom domain is not automatically included merely because an Online Store is included. Custom-domain registration, renewal and technical configuration are separately quoted where requested.</span></li>
        <li><span class="check">&#10003;</span><span>There is no advertised visitor limit, but reasonable security, bandwidth, rate-limiting and fair-use controls apply.</span></li>
      </ul>
      <div class="note" style="margin:0;">
        <p class="small"><strong>Custom domains</strong> are available as an optional service and are not part of the standard plan quota.</p>
      </div>
    </div>
    <?= footer('6') ?>
  </section>

  <!-- Page 7: Implementation -->
  <section class="page" style="background:#FAFBFF;">
    <span class="label">Implementation &amp; Onboarding</span>
    <h2 class="mb-1">Every deployment is assessed before implementation</h2>
    <p class="lead mb-2">The final implementation fee depends on the number of branches, data condition, configuration requirements, migration work, training format, location and launch support required. MartPoint will confirm the scope, timeline and fee in writing before implementation begins.</p>
    <div class="fill">
      <table>
        <thead><tr><th>Implementation package</th><th class="num">Starting price</th><th>Typical scope</th></tr></thead>
        <tbody>
          <?php foreach ($pricing['implementation'] as $item): ?>
          <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td class="num"><?= $item['price'] ?></td>
            <td class="small"><?= htmlspecialchars($item['scope']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="note" style="margin:0;">
        <p class="small"><strong>Implementation is quoted after assessment.</strong> The starting prices above are not a promise that every deployment will be completed at the starting amount.</p>
      </div>
      <div>
        <p class="small" style="margin-bottom:10px;"><strong>Implementation does not include unless expressly stated in the quotation:</strong></p>
        <div class="exclusions" style="margin-top:0;">
          <div><span>Full product upload</span></div>
          <div><span>Catalogue writing or cleanup</span></div>
          <div><span>Product photography</span></div>
          <div><span>Image sourcing or editing</span></div>
          <div><span>Custom development</span></div>
          <div><span>Hardware supply</span></div>
          <div><span>Travel and accommodation</span></div>
          <div><span>Custom storefront design</span></div>
          <div><span>Marketing</span></div>
          <div><span>Ongoing store management</span></div>
          <div><span>Third-party subscription charges</span></div>
        </div>
      </div>
    </div>
    <?= footer('7') ?>
  </section>

  <!-- Page 8: Capacity add-ons -->
  <section class="page">
    <span class="label">Capacity Add-ons</span>
    <h2 class="mb-1">Need more capacity?</h2>
    <p class="lead mb-2">Add extra branches, users, products, variations, services or storage to your plan.</p>
    <div class="fill">
      <table>
        <thead><tr><th>Capacity add-on</th><th class="num">Annual price</th></tr></thead>
        <tbody>
          <?php foreach ($pricing['capacity_addons'] as $a): ?>
          <tr><td><?= htmlspecialchars($a['label']) ?></td><td class="num"><?= $a['price'] ? naira($a['price']) . ' ' . $a['unit'] : $a['unit'] ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="note" style="margin:0;">
        <p class="small"><strong>Co-terminus billing.</strong> Capacity add-ons run with the customer’s main subscription and renew on the same date. Where an add-on is purchased during an active subscription term, MartPoint may calculate a prorated charge covering the remaining subscription period.</p>
      </div>
      <p class="small muted" style="margin:0;">Product and product-variation increases are tracked separately. Purchasing additional products does not create additional product-variation capacity.</p>
    </div>
    <?= footer('8') ?>
  </section>

  <!-- Page 9: Optional services -->
  <section class="page" style="background:#FAFBFF;">
    <span class="label">Optional Services</span>
    <h2 class="mb-1">Optional professional services</h2>
    <p class="lead mb-2">Additional services are available and quoted separately.</p>
    <div class="fill">
      <table>
        <thead><tr><th>Service</th><th class="num">Price</th></tr></thead>
        <tbody>
          <?php foreach ($pricing['optional_services'] as $s): ?>
          <tr><td><?= htmlspecialchars($s['name']) ?></td><td class="num"><?= $s['price'] ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="note" style="margin:0;">
        <p class="small"><strong>Approval required.</strong> Optional services begin only after the customer approves the written scope and invoice. Onsite work is not included in the annual licence.</p>
      </div>
    </div>
    <?= footer('9') ?>
  </section>

  <!-- Page 10: Offline & ERP -->
  <section class="page">
    <span class="label">Other Deployment Options</span>
    <h2 class="mb-1">Offline/Local Deployment</h2>
    <p class="lead mb-2">MartPoint is also available as a locally installed licence.</p>
    <div class="fill">
      <div>
        <table>
          <thead><tr><th>Item</th><th class="num">Price</th></tr></thead>
          <tbody>
            <?php foreach ($pricing['offline'] as $o): ?>
            <tr><td><?= htmlspecialchars($o['name']) ?></td><td class="num"><?= $o['price'] ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <p class="small" style="margin-top:12px; margin-bottom:0;">The licence is billed annually and must be renewed to remain active. Installation, migration, training, customisation, travel and optional annual maintenance are priced separately according to the approved scope.</p>
      </div>
      <div class="card">
        <h3>MartPoint ERP</h3>
        <p class="small">MartPoint ERP licence starts from <?= naira($pricing['erp']['license_from']) ?> per year. The final licence and implementation price depends on the required modules, users, branches, workflows, migration, integrations, deployment model and support scope.</p>
        <p class="small" style="margin-top:10px; margin-bottom:0;">ERP deployments require an assessment and a formal quotation. The quotation will clearly state the licence term and renewal schedule, the modules included, implementation fees, renewal obligations and support coverage.</p>
      </div>
    </div>
    <?= footer('10') ?>
  </section>

  <!-- Page 11: Billing & support -->
  <section class="page" style="background:#FAFBFF;">
    <span class="label">Billing, Support &amp; Commercial Notes</span>
    <h2 class="mb-1">Commercial terms</h2>
    <div class="fill">
    <ul class="bullet-list" style="margin-bottom:24px;">
      <li><span class="check">&#10003;</span><span>Retail Cloud licences are billed annually.</span></li>
      <li><span class="check">&#10003;</span><span>Payment is required before licence activation or renewal.</span></li>
      <li><span class="check">&#10003;</span><span>Implementation begins after the required payment and approval of scope.</span></li>
      <li><span class="check">&#10003;</span><span>Upgrades can be added during an active term.</span></li>
      <li><span class="check">&#10003;</span><span>Downgrades take effect at renewal unless MartPoint approves otherwise.</span></li>
      <li><span class="check">&#10003;</span><span>Capacity add-ons co-terminate with the main subscription.</span></li>
      <li><span class="check">&#10003;</span><span>The issued quotation and invoice define the final scope and amount payable.</span></li>
      <li><span class="check">&#10003;</span><span>Third-party charges are not included unless specifically listed.</span></li>
      <li><span class="check">&#10003;</span><span>Applicable taxes are additional unless the quotation says they are included.</span></li>
      <li><span class="check">&#10003;</span><span>No refund or cancellation promise is made in this catalogue.</span></li>
      <li><span class="check">&#10003;</span><span>Custom work and implementation timelines begin after all required information, access and payments have been received.</span></li>
    </ul>
    <div class="note">
      <p class="small"><strong>Support.</strong> Standard remote software support is included during MartPoint's published business hours: Monday to Friday, 9:00 a.m. to 5:00 p.m. WAT, excluding public holidays.</p>
      <p class="small" style="margin-top:5px;">Your MartPoint subscription includes access to the software, usage documentation, updates applicable to your plan, and technical support for the MartPoint application.</p>
      <p class="small" style="margin-top:5px;">Computers, tablets, barcode scanners, receipt printers, networking equipment, internet service, electricity, hardware installation, data entry, onsite setup, and onsite training are not included in the standard MartPoint subscription unless they are specifically stated in your quotation or service agreement.</p>
      <p class="small" style="margin-top:5px;">Where required, MartPoint can recommend, supply, configure, or arrange compatible hardware and implementation services at an additional cost. These services may be delivered directly by MartPoint or through an approved MartPoint Implementation Partner.</p>
      <p class="small" style="margin-top:5px;">Hardware supplied through MartPoint or an approved partner will be covered according to the applicable manufacturer or supplier warranty. MartPoint is not responsible for faults involving equipment, internet connectivity, electricity, or third-party services that were not supplied or configured by MartPoint or an approved implementation partner.</p>
      <p class="small" style="margin-top:5px;">Our team will provide guidance on compatible equipment, installation requirements, and setup best practices to help you operate MartPoint successfully.</p>
    </div>
    </div>
    <?= footer('11') ?>
  </section>

  <!-- Page 12: Contact -->
  <section class="page page-cta">
    <div style="display:flex; align-items:center; justify-content:space-between;">
      <img src="logo.png" alt="MartPoint" class="logo logo-light">
      <span class="pill">Pricing Catalogue v<?= $version ?></span>
    </div>
    <div style="margin-top:40px;">
      <h1 style="font-size:2.4rem; margin-bottom:14px;">Choose the plan that fits your business.</h1>
      <p class="lead" style="font-size:1rem; max-width:85%;">Start with an annual Retail Cloud plan and add capacity, implementation or services as you grow.</p>
      <div style="display:flex; gap:12px; margin-top:18px; flex-wrap:wrap;">
        <span class="btn" style="margin-top:0;">Request a Demo</span>
        <span class="btn btn-outline" style="margin-top:0;">Speak to an Adviser</span>
        <span class="btn btn-outline" style="margin-top:0;">Get a Custom Quote</span>
      </div>
    </div>
    <div class="spacer"></div>
    <div class="contact-grid">
      <div class="contact-card"><strong>Website</strong><span>martpoint.com.ng</span></div>
      <div class="contact-card"><strong>WhatsApp</strong><span>0803 602 8069</span></div>
      <div class="contact-card"><strong>Email</strong><span>sales@martpoint.com.ng</span></div>
      <div class="contact-card"><strong>Social</strong><span>@usemartpoint</span></div>
    </div>
    <div class="qr-wrap">
      <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPAAAADwAQMAAAAEm3vRAAAABlBMVEX///8AAABVwtN+AAAACXBIWXMAAA7EAAAOxAGVKw4bAAABYElEQVRYhe2YMXLEMAhF8bhQqSP4KD5afDQfRUdw6cITwge0K2esTXpBoU14W/3hswDR5/hii6L/lHzhM3vye1y8qzprScwONsqnJlPgLkZGMM2Q8aCJec/nBJ0Di2oX8bZoiQX+NzaH6hu4sSBUo3xNHYcGbjFbeK0dc9UZMS5+xyz93DE9ROAGm6i0MouoLuR6SJXxvpwDY5VI/saXbKSSVEKVBe5j1dEG0fevIKuobuARMQoLGfKRSt5t1fLTfh74E+aijYuyCskyO8C042L4rapWLci3xhX4ESNsq8HsYA7Fu7Qr8nhYJVLt6khFqVowcAfrMC6mVHGt1rRx6aIzKn4F5nMuWFvgR3sDd7ApiK2Ga7u3Fdnb/ajYtPMzVFGlqmopcBc3Z6g2kApcMQ4qOlKZhIH/wKyncbzrIeD3yXM07BZM1+umacV2d2jgG3ZDmqiLHeyebnuBG/w5fgD055LMGKLypwAAAABJRU5ErkJggg==" alt="Scan for pricing">
      <div class="qr-text"><strong>Scan to view pricing</strong>Point your phone camera at this code to open the MartPoint pricing page.</div>
    </div>
    <div class="spacer"></div>
    <div class="closing"><strong>MartPoint</strong> &mdash; Retail Intelligence Platform</div>
    <?= footer('12') ?>
  </section>

</div>
</body>
</html>
