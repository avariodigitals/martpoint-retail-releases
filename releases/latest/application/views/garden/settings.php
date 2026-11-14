<form class="garden-panel" method="post" action="<?= base_url('garden/save_settings'); ?>">
<?php $this->load->view('garden/form_token'); ?>
<div class="garden-panel-heading"><div><span class="garden-kicker">TAILOR YOUR WORKSPACE</span><h2>How does your business operate?</h2></div></div>
<p class="garden-muted garden-panel-intro">Choose the activities you run. Select individual modes, or Combined Operations for all four. Your plan and staff permissions continue to apply.</p>
<div class="garden-mode-cards">
<?php $modeCopy=[
'retail'=>['fa-shopping-bag','Sell décor, pots, accessories, tools and other merchandise through your catalogue and point of sale.','Product sales · Accessories · POS'],
'nursery'=>['fa-pagelines','Manage living plants, care details, received batches and plant losses alongside shared inventory.','Living inventory · Plant care · Stock losses'],
'landscaping'=>['fa-map-o','Take enquiries through inspection, quotations and project delivery. Link deposits, materials and expenses to the work.','Enquiries · Quotations · Projects'],
'maintenance'=>['fa-calendar-check-o','Plan one-time and recurring service visits, assign your team and record completed work and materials used.','Recurring visits · Team assignments · Service work'],
'combined'=>['fa-th-large','Run retail, nursery, landscaping and maintenance together, with one shared catalogue, customer base and inventory.','All four operating modes'],
]; foreach ($garden_modes as $key=>$mode): $copy=$modeCopy[$key]; ?>
<label class="garden-mode-card <?= $key==='combined' ? 'garden-mode-combined' : ''; ?>"><input type="checkbox" name="modes[]" value="<?= $key; ?>" <?= in_array($key,$garden_profile['operating_modes'],true) ? 'checked' : ''; ?>>
<span class="garden-mode-icon"><i class="fa <?= $copy[0]; ?>" aria-hidden="true"></i></span><span class="garden-mode-copy"><strong><?= htmlspecialchars($mode['label']); ?></strong><span><?= htmlspecialchars($copy[1]); ?></span><small><?= htmlspecialchars($copy[2]); ?></small></span></label>
<?php endforeach; ?></div>
<button class="garden-button">Save operating modes</button></form>
