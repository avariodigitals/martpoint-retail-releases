<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
$CI =& get_instance();
$can_quote     = $CI->permissions('print_quote');
$can_artwork   = $CI->permissions('print_artwork');
$can_design    = $CI->permissions('print_design');
$can_authorize = $CI->permissions('print_authorize');
$can_production= $CI->permissions('print_production');
$can_edit      = $CI->permissions('print_jobs_edit');
$can_adjust    = !empty($can_adjust_quote);
$prereqs_ok = !empty($job) && $prereqs['ok'];
?>
<div class="mp-section print-job-page">
    <div class="mp-page-head">
        <h2><?= $job ? 'Print Job ' . htmlspecialchars($job->job_code) : 'New Print Job' ?></h2>
        <div class="mp-page-sub"><?= $job && $job->customer_name ? htmlspecialchars($job->customer_name) : 'Capture a print job — specification, artwork and pricing in one pass.' ?></div>
        <a href="<?= base_url('printing') ?>" class="btn btn-default btn-sm">← Print Dashboard</a>
    </div>

<?php if (empty($job)): ?>
    <!-- NEW JOB -->
    <form id="print-job-form" method="post" action="<?= base_url('printing/job_save') ?>">
      <input type="hidden" name="is_draft" id="is_draft" value="0">
      <div class="pj-card"><div class="pj-card-head"><span class="pj-step">1</span> Customer &amp; Job</div>
        <div class="pj-card-body"><div class="pj-grid">
          <div class="pj-field"><label>Client <span class="req">*</span></label><select name="customer_id" class="form-control" required><option value="">Select client</option><?php foreach ($customers as $c): ?><option value="<?= $c->id ?>"><?= htmlspecialchars($c->customer_name) ?></option><?php endforeach; ?></select></div>
          <div class="pj-field"><label>Job title</label><input type="text" name="title" class="form-control" placeholder="e.g. Storefront banner + staff tees"></div>
          <div class="pj-field"><label>Due date</label><input type="date" name="due_date" class="form-control"></div>
          <div class="pj-field"><label>Priority</label><select name="priority" class="form-control"><option value="normal">Normal</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
        </div></div>
      </div>
      <div class="pj-card"><div class="pj-card-head"><span class="pj-step">2</span> Print Items <span class="pj-hint">Category selection changes the specification fields</span></div>
        <div class="pj-card-body"><div id="items-wrap"></div><button type="button" class="btn btn-default btn-sm" id="add-item"><i class="fa fa-plus"></i> Add print item</button></div>
      </div>
      <div class="pj-card"><div class="pj-card-head"><span class="pj-step">3</span> Artwork &amp; Design</div>
        <div class="pj-card-body"><div class="pj-grid">
          <div class="pj-field"><label>Artwork required?</label><select name="artwork_required" class="form-control"><option value="0">No</option><option value="1">Yes — customer supplies design</option></select></div>
          <div class="pj-field"><label>Design reference</label><input type="text" name="design_ref" class="form-control" placeholder="Design / file reference"></div>
        </div></div>
      </div>
      <div class="pj-card"><div class="pj-card-head"><span class="pj-step">4</span> Pricing &amp; Payment Terms <span class="pj-hint">Total is derived from items + charges</span></div>
        <div class="pj-card-body"><div id="charges-wrap"></div><button type="button" class="btn btn-default btn-sm" id="add-charge"><i class="fa fa-plus"></i> Add charge (delivery, installation…)</button>
          <div class="pj-total"><span>Quotation total</span><strong id="quote-total"><?= $CI->currency() ?>0.00</strong></div></div>
      </div>
      <div class="pj-card"><div class="pj-card-head"><span class="pj-step">5</span> Fulfilment</div>
        <div class="pj-card-body pj-grid">
          <div class="pj-field"><label>Delivery / collection</label><select name="fulfilment_method" class="form-control"><option value="collection">Customer collection</option><option value="delivery">Delivery</option></select></div>
          <div class="pj-field"><label>Delivery address / notes</label><input type="text" name="fulfilment_note" class="form-control" placeholder="Optional"></div>
        </div>
      </div>
      <div class="pj-card"><div class="pj-card-head"><span class="pj-step">6</span> Referral Attribution <span class="pj-hint">Optional</span></div>
        <div class="pj-card-body pj-grid">
          <div class="pj-field"><label>Referral rate (%)</label><input type="number" step="0.01" name="referral_rate" class="form-control" placeholder="e.g. 5"></div>
          <div class="pj-field"><label>Referral source</label><input type="text" name="referral_source" class="form-control" placeholder="Partner / source reference"></div>
        </div>
      </div>
      <div class="pj-actions"><button type="button" class="btn btn-default" id="save-draft">Save as Draft</button><button type="button" class="btn btn-primary" id="save-job">Create Job</button></div>
    </form>
    <script>
    (function(){
      var SCHEMAS = <?= json_encode($schema_map) ?>;
      var CATS = <?= json_encode(array_map(function($c){ return ['id'=>$c->id,'name'=>$c->name]; }, $categories)) ?>;
      var CURRENCY = <?= json_encode($CI->currency()) ?>;
      function catOptions(){ return CATS.map(function(c){ return '<option value="'+c.id+'">'+c.name+'</option>'; }).join(''); }
      function specField(f){
        var req = (f.required && !f.optional) ? ' <span class="req">*</span>' : '';
        var ph = f.placeholder ? ' placeholder="'+f.placeholder+'"' : '';
        if (f.type === 'select') return '<div class="pj-field"><label>'+f.label+req+'</label><select class="form-control" data-key="'+f.key+'"><option value="">—</option>'+(f.options||[]).map(function(o){return '<option value="'+o+'">'+o+'</option>';}).join('')+'</select></div>';
        if (f.type === 'sizes') { var s=['S','M','L','XL','XXL']; return '<div class="pj-field pj-full"><label>'+f.label+req+'</label><div class="pj-sizes">'+s.map(function(x){return '<div class="pj-size"><span>'+x+'</span><input type="number" min="0" class="form-control" data-size="'+x+'" placeholder="0"></div>';}).join('')+'</div></div>'; }
        if (f.type === 'positions') { var p=['Front','Back','Left sleeve','Right sleeve']; return '<div class="pj-field pj-full"><label>'+f.label+req+'</label><div class="pj-chks">'+p.map(function(x){return '<label class="pj-chk"><input type="checkbox" data-position="'+x+'"> '+x+'</label>';}).join('')+'</div></div>'; }
        if (f.type === 'number') return '<div class="pj-field"><label>'+f.label+req+'</label><input type="number" step="any" class="form-control" data-key="'+f.key+'"'+ph+'></div>';
        return '<div class="pj-field"><label>'+f.label+req+'</label><input type="text" class="form-control" data-key="'+f.key+'"'+ph+'></div>';
      }
      function renderSpecs(c){ var s=SCHEMAS[c.querySelector('.pj-cat').value]||[]; c.querySelector('.pj-specs').innerHTML=s.map(specField).join(''); }
      function addItem(){
        var c=document.createElement('div'); c.className='pj-item';
        c.innerHTML='<div class="pj-item-head"><select class="form-control pj-cat">'+catOptions()+'</select><input type="number" step="0.001" class="form-control pj-qty" placeholder="Qty"><input type="number" step="0.01" class="form-control pj-price" placeholder="Unit price"><button type="button" class="btn btn-xs btn-danger pj-rm"><i class="fa fa-times"></i></button></div><input type="text" class="form-control pj-desc" placeholder="Item description" style="margin:8px 0;"><div class="pj-specs pj-grid"></div>';
        document.getElementById('items-wrap').appendChild(c);
        c.querySelector('.pj-cat').addEventListener('change',function(){renderSpecs(c);});
        c.querySelector('.pj-rm').addEventListener('click',function(){c.remove();recalc();});
        c.querySelector('.pj-qty').addEventListener('input',recalc);
        c.querySelector('.pj-price').addEventListener('input',recalc);
        renderSpecs(c); recalc();
      }
      function collectItem(c){
        var spec={}; c.querySelectorAll('[data-key]').forEach(function(el){spec[el.getAttribute('data-key')]=el.value;});
        var sz={},h=false; c.querySelectorAll('[data-size]').forEach(function(el){if(el.value!==''){sz[el.getAttribute('data-size')]=el.value;h=true;}}); if(h)spec.size_breakdown=sz;
        var po=[]; c.querySelectorAll('[data-position]').forEach(function(el){if(el.checked)po.push(el.getAttribute('data-position'));}); if(po.length)spec.print_positions=po;
        return {category_id:c.querySelector('.pj-cat').value,description:c.querySelector('.pj-desc').value,qty:c.querySelector('.pj-qty').value,unit_price:c.querySelector('.pj-price').value,spec:spec};
      }
      function recalc(){ var t=0; document.querySelectorAll('.pj-item').forEach(function(c){t+=(parseFloat(c.querySelector('.pj-qty').value)||0)*(parseFloat(c.querySelector('.pj-price').value)||0);}); document.querySelectorAll('.pj-charge-amt').forEach(function(el){t+=parseFloat(el.value)||0;}); document.getElementById('quote-total').textContent=CURRENCY+t.toFixed(2); }
      function addCharge(){ var r=document.createElement('div'); r.className='pj-charge'; r.innerHTML='<input type="text" class="form-control pj-charge-label" placeholder="Charge label"><input type="number" step="0.01" class="form-control pj-charge-amt" placeholder="Amount"><button type="button" class="btn btn-xs btn-danger pj-rmc"><i class="fa fa-times"></i></button>'; document.getElementById('charges-wrap').appendChild(r); r.querySelector('.pj-charge-amt').addEventListener('input',recalc); r.querySelector('.pj-rmc').addEventListener('click',function(){r.remove();recalc();}); }
      document.getElementById('add-item').addEventListener('click',addItem);
      document.getElementById('add-charge').addEventListener('click',addCharge);
      addItem();
      function submit(draft){
        document.getElementById('is_draft').value = draft?'1':'0';
        var form=document.getElementById('print-job-form');
        form.querySelectorAll('input[name="line_json[]"]').forEach(function(el){el.remove();});
        var items=[]; document.querySelectorAll('.pj-item').forEach(function(c){items.push(collectItem(c));});
        if(!items.length){alert('Add at least one print item.');return;}
        items.forEach(function(it){var x=document.createElement('input');x.type='hidden';x.name='line_json[]';x.value=JSON.stringify(it);form.appendChild(x);});
        fetch(form.getAttribute('action'),{method:'POST',body:new FormData(form),headers:{'X-Requested-With':'XMLHttpRequest'}}).then(function(r){return r.json();}).then(function(d){if(d.success){window.location='<?= base_url('printing/job') ?>/'+d.id;}else{alert(d.message||'Error');}}).catch(function(){alert('Request failed');});
      }
      document.getElementById('save-draft').addEventListener('click',function(){submit(1);});
      document.getElementById('save-job').addEventListener('click',function(){submit(0);});
    })();
    </script>

<?php else: ?>
    <!-- JOB VIEW -->
    <div class="jh-hero">
      <div class="jh-hero-main">
        <div class="jh-code"><?= htmlspecialchars($job->job_code) ?></div>
        <h3 class="jh-title"><?= htmlspecialchars($job->title ?: 'Print job') ?></h3>
        <div class="jh-meta">
          <span><i class="fa fa-user-o"></i> <?= htmlspecialchars($job->customer_name ?: 'Walk-in') ?></span>
          <span><i class="fa fa-calendar-o"></i> Due <?= $job->due_date ? show_date($job->due_date) : '—' ?></span>
          <span><i class="fa fa-flag-o"></i> <?= htmlspecialchars(ucfirst($job->priority)) ?></span>
        </div>
      </div>
      <div class="jh-hero-money">
        <div><span>Quotation</span><strong><?= $CI->currency() . number_format((float)$job->quote_amount,2) ?></strong></div>
        <div><span>Balance</span><strong><?= $CI->currency() . number_format(max(0,(float)$job->quote_amount - $this->print->net_verified_payments($job->id)),2) ?></strong></div>
      </div>
    </div>

    <div class="jh-steps">
      <?php foreach ($stage_progress as $st): ?>
      <div class="jh-step <?= $st['state'] ?><?= !empty($st['blocked']) ? ' blocked' : '' ?>" title="<?= htmlspecialchars($st['hint']) ?>">
        <span class="jh-dot">
          <?php if ($st['state'] === 'done'): ?><i class="fa fa-check"></i>
          <?php elseif ($st['state'] === 'active'): ?><i class="fa fa-dot-circle-o"></i>
          <?php else: ?><i class="fa fa-circle-o"></i><?php endif; ?>
        </span>
        <span class="jh-step-label"><?= $st['label'] ?></span>
        <?php if ($st['state'] === 'active'): ?><span class="jh-here">current</span><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($stage_progress)): ?>
    <?php
      $active_stage = null;
      foreach ($stage_progress as $sp) { if ($sp['state'] === 'active') { $active_stage = $sp; break; } }
    ?>
    <?php if ($active_stage): ?>
    <div class="jh-next"><i class="fa fa-arrow-circle-right"></i> <strong>Next action:</strong> <?= htmlspecialchars($active_stage['label']) ?> — <?= htmlspecialchars($active_stage['hint']) ?></div>
    <?php endif; ?>
    <?php endif; ?>

    <?php if (!$prereqs_ok): ?>
    <div class="jh-banner warn"><i class="fa fa-lock"></i> Production blocked — missing: <?= implode(', ', $prereqs['missing']) ?></div>
    <?php else: ?>
    <div class="jh-banner ok"><i class="fa fa-check-circle"></i> Ready for production — all prerequisites met.</div>
    <?php endif; ?>

    <div class="jh-cols">
      <div class="jh-col-main">
        <?php foreach ($lines as $ln):
          $spec = $ln->spec_json ? json_decode($ln->spec_json, true) : [];
          $schema = isset($schema_map[$ln->category_id]) ? $schema_map[$ln->category_id] : [];
          $lservices = isset($services_map[$ln->id]) ? $services_map[$ln->id] : [];
          $lplans = isset($plans_map[$ln->id]) ? $plans_map[$ln->id] : [];
        ?>
        <div class="jh-item">
          <div class="jh-item-head">
            <div><span class="jh-tag"><?= htmlspecialchars(ucwords(str_replace('_',' ',$ln->category_key ?? ''))) ?></span> <strong><?= htmlspecialchars($ln->description ?: 'Print item') ?></strong></div>
            <div class="jh-item-qty"><?= (float)$ln->qty ?> × <?= $CI->currency() . number_format((float)$ln->unit_price,2) ?> <strong><?= $CI->currency() . number_format((float)$ln->line_total,2) ?></strong></div>
          </div>
          <div class="jh-chips">
            <?php foreach ($schema as $f):
              $v = isset($spec[$f['key']]) ? $spec[$f['key']] : null;
              if (is_array($v)) $v = implode(', ', array_map(function($k,$x){ return $f['type']==='sizes' ? ($k.': '.$x) : $x; }, array_keys($v), $v));
              if ($v === null || $v === '') continue; ?>
            <span class="jh-chip"><em><?= htmlspecialchars($f['label']) ?></em> <?= htmlspecialchars($v) ?></span>
            <?php endforeach; ?>
          </div>
          <?php if (!empty($lservices)): ?>
          <div class="jh-line-block">
            <div class="jh-line-title">Services</div>
            <?php foreach ($lservices as $sv):
              $stype = isset($service_types[$sv->service_key]) ? $service_types[$sv->service_key] : ['label'=>ucfirst($sv->service_key),'icon'=>'fa-plus'];
              $waived = (int)$sv->charge_waived === 1; ?>
            <div class="jh-svc-row">
              <span class="jh-svc-name"><i class="fa <?= $stype['icon'] ?>"></i> <?= htmlspecialchars($stype['label']) ?><?php if ($sv->design_mode): ?> <em><?= htmlspecialchars(str_replace('_',' ',$sv->design_mode)) ?></em><?php endif; ?></span>
              <span><?php if ($waived): ?><s><?= $CI->currency() . number_format((float)$sv->charge_amount,2) ?></s> <span class="jh-waived">Waived</span><?php if ($sv->waiver_reason): ?><span class="jh-waiver-why"> · <?= htmlspecialchars($sv->waiver_reason) ?></span><?php endif; ?><?php else: ?><strong><?= $CI->currency() . number_format((float)$sv->charge_amount,2) ?></strong><?php endif; ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <?php if (!empty($lplans)): ?>
          <div class="jh-line-block">
            <div class="jh-line-title">Material &amp; operation plan</div>
            <div class="jh-plan-list">
              <?php foreach ($lplans as $p): $u = null; foreach ($units as $uu) { if ($uu->id == $p->plan_unit_id) { $u = $uu->unit_name; break; } } ?>
              <div class="jh-plan-row">
                <span class="jh-plan-kind <?= $p->plan_type ?>"><?= $p->plan_type === 'material' ? 'M' : 'OP' ?></span>
                <span class="jh-plan-name"><?= htmlspecialchars($p->description ?: $p->operation_key) ?></span>
                <span class="jh-plan-qty"><?= (float)$p->plan_qty ?> <?= htmlspecialchars($u ?: '') ?><?= ((int)$p->conversion_ok === 0) ? ' <span class="jh-warn">check unit</span>' : '' ?></span>
                <span class="jh-plan-cost"><?= $CI->currency() . number_format((float)$p->est_total_cost,2) ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
          <?php if ($can_edit): ?>
          <div class="jh-line-actions">
            <button type="button" class="jh-link" onclick="document.getElementById('svc-form-<?= $ln->id ?>').style.display='grid'">+ Service</button>
            <button type="button" class="jh-link" onclick="document.getElementById('plan-form-<?= $ln->id ?>').style.display='grid'">+ Material / operation</button>
          </div>
          <div id="svc-form-<?= $ln->id ?>" class="pj-inline-form" style="display:none;">
            <select class="pj-svc-type form-control"><?php foreach ($service_types as $k=>$t): ?><option value="<?= $k ?>"><?= htmlspecialchars($t['label']) ?></option><?php endforeach; ?></select>
            <input type="number" step="0.01" class="pj-svc-charge-in form-control" placeholder="Customer charge">
            <input type="number" step="0.01" class="pj-svc-cost-in form-control" placeholder="Internal cost">
            <label class="pj-chk"><input type="checkbox" class="pj-svc-waive"> Waive</label>
            <input type="text" class="pj-svc-reason form-control" placeholder="Waiver reason">
            <input type="text" class="pj-svc-mode form-control" placeholder="Mode (new_design/…)">
            <button type="button" class="btn btn-primary btn-xs" onclick="saveService(<?= $job->id ?>, <?= $ln->id ?>, this)">Save</button>
          </div>
          <div id="plan-form-<?= $ln->id ?>" class="pj-inline-form" style="display:none;">
            <select class="pj-plan-type form-control"><option value="material">Material</option><option value="operation">Operation</option></select>
            <select class="pj-plan-item form-control"><option value="">— inventory item —</option><?php foreach ($items as $it): ?><option value="<?= $it->id ?>"><?= htmlspecialchars($it->item_name) ?></option><?php endforeach; ?></select>
            <select class="pj-plan-unit form-control"><option value="">— unit —</option><?php foreach ($units as $uu): ?><option value="<?= $uu->id ?>"><?= htmlspecialchars($uu->unit_name) ?></option><?php endforeach; ?></select>
            <input type="text" class="pj-plan-desc form-control" placeholder="Description / operation">
            <input type="number" step="0.0001" class="pj-plan-qty form-control" placeholder="Qty">
            <input type="number" step="0.0001" class="pj-plan-cost form-control" placeholder="Est. unit cost">
            <input type="number" step="0.001" class="pj-plan-waste form-control" placeholder="Wastage %">
            <button type="button" class="btn btn-primary btn-xs" onclick="savePlan(<?= $job->id ?>, <?= $ln->id ?>, this)">Save</button>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="jh-col-side">
        <div class="jh-side-card">
          <div class="jh-side-head">Quotation</div>
          <div class="jh-side-body">
            <?php $qsum = $quotation_summary; ?>
            <div class="jh-kv"><span>Status</span><strong><?= htmlspecialchars(ucwords(str_replace('_',' ',$job->quotation_status))) ?><?php if ($qsum && (int)$qsum['revision_no'] > 0): ?> <span class="jh-rev">R<?= (int)$qsum['revision_no'] ?></span><?php endif; ?></strong></div>
            <?php if ($qsum): ?>
            <div class="jh-kv"><span>Quotation</span><strong><?= htmlspecialchars($qsum['quotation_code']) ?></strong></div>
            <?php endif; ?>
            <div class="jh-kv"><span>Total</span><strong><?= $CI->currency() . number_format((float)$job->quote_amount,2) ?></strong></div>
            <div class="jh-kv"><span>Tax</span><strong><?= $qsum && !empty($tax_summary['on']) ? htmlspecialchars($tax_summary['label']) : 'Exempt' ?></strong></div>
            <div class="jh-kv"><span>Deposit due</span><strong><?= $CI->currency() . number_format((float)$job->deposit_amount,2) ?></strong></div>

            <?php if ($qsum && !empty($qsum['reaccept_required'])): ?>
            <div class="jh-inline-warn"><i class="fa fa-exclamation-triangle"></i> Revised — customer reacceptance required.</div>
            <?php endif; ?>

            <?php if ($can_quote): ?>
              <?php if (!$qsum || in_array($qsum['sales_status'], [null, ''], true)): ?>
              <div class="jh-field" style="margin:10px 0">
                <label for="quote-customer-id"><strong>Quotation customer <span class="req">*</span></strong></label>
                <select id="quote-customer-id" class="form-control" required>
                  <option value="">Select the customer who will receive this quotation</option>
                  <?php foreach ($customers as $c): ?>
                    <?php $selected_customer = $qsum['customer_id'] ?? ($job->customer_id ?? 0); ?>
                    <option value="<?= (int)$c->id ?>" <?= (int)$selected_customer === (int)$c->id ? 'selected' : '' ?>><?= htmlspecialchars($c->customer_name) ?></option>
                  <?php endforeach; ?>
                </select>
                <?php if (empty($customers)): ?><small class="text-danger">Add a customer under Customers before issuing this quotation.</small><?php else: ?><small class="text-muted">This customer will appear on the quotation and receive its email link.</small><?php endif; ?>
              </div>
              <?php endif; ?>
              <?php if (!$qsum): ?>
              <button class="btn btn-primary btn-sm btn-block" onclick="issuePrintQuotation(false)">Issue Quotation</button>
              <?php elseif (in_array($qsum['sales_status'], [null, ''], true)): ?>
              <div class="jh-btn-row">
                <a class="btn btn-default btn-sm" href="<?= base_url('printing/quote_view/' . $job->id) ?>"><i class="fa fa-eye"></i> View</a>
                <a class="btn btn-default btn-sm" href="<?= base_url('printing/quote_pdf/' . $job->id) ?>" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a>
              </div>
              <button class="btn btn-default btn-sm btn-block" onclick="if(confirm('Revise this quotation? A new revision is created, the customer must re-accept, and any print authorization is invalidated.')){issuePrintQuotation(true)}"><i class="fa fa-refresh"></i> Revise (new revision)</button>
              <?php endif; ?>
            <?php endif; ?>

            <?php if ($can_quote && $qsum && in_array($job->quotation_status, ['issued','draft'], true)): ?>
            <button class="btn btn-success btn-sm btn-block" onclick="printJson('<?= base_url('printing/quote_accept') ?>',{job_id:<?= $job->id ?>})"><i class="fa fa-check"></i> Accept (customer)</button>
            <?php endif; ?>

            <?php if ($can_quote && $qsum && $qsum['accepted_revision'] !== null): ?>
              <?php if (!empty($qsum['converted_sales_id'])): ?>
              <a class="btn btn-default btn-sm btn-block" href="<?= base_url('sales/invoice/' . (int)$qsum['converted_sales_id']) ?>"><i class="fa fa-file-text-o"></i> View Sales Invoice</a>
              <?php else: ?>
              <a class="btn btn-primary btn-sm btn-block" href="<?= base_url('printing/quote_convert/' . $job->id) ?>"><i class="fa fa-exchange"></i> Convert to Invoice</a>
              <?php endif; ?>
            <?php endif; ?>

            <?php
              // Lifecycle: a quotation must not sit "issued" forever.
              $lc = $job->quote_lifecycle_status ?? 'issued';
              $dl = $expiry_days_left;
            ?>
            <?php if ($qsum): ?>
            <div class="jh-lifecycle">
              <div class="jh-kv"><span>Validity</span>
                <strong>
                  <?php if (!empty($qsum['expire_date'])): ?>
                    <?= show_date($qsum['expire_date']) ?>
                    <?php if ($dl !== null && $lc === 'issued'): ?>
                      <span class="jh-days <?= $dl <= 3 ? 'soon' : '' ?>"><?= $dl >= 0 ? $dl . 'd left' : 'past' ?></span>
                    <?php endif; ?>
                  <?php else: ?>
                    No expiry set
                  <?php endif; ?>
                </strong>
              </div>
              <div class="jh-kv"><span>Outcome</span><strong class="jh-lc jh-lc-<?= htmlspecialchars($lc) ?>"><?= ucfirst(htmlspecialchars($lc)) ?></strong></div>
              <?php if (!empty($job->quote_response_reason)): ?>
              <div class="jh-inline-note"><?= htmlspecialchars($job->quote_response_reason) ?></div>
              <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($can_quote && $qsum && $lc === 'issued'): ?>
            <details class="jh-details"><summary>Set / change validity</summary>
              <input type="date" id="qx_date" class="form-control" style="margin:6px 0;"
                     value="<?= !empty($qsum['expire_date']) ? date('Y-m-d', strtotime($qsum['expire_date'])) : date('Y-m-d', strtotime('+30 days')) ?>">
              <button class="btn btn-default btn-sm btn-block"
                onclick="printJson('<?= base_url('printing/quote_expiry') ?>',{job_id:<?= $job->id ?>,expire_date:document.getElementById('qx_date').value})">Save validity date</button>
            </details>
            <?php endif; ?>

            <?php if ($can_quote && $qsum && in_array($lc, ['issued'], true)): ?>
            <details class="jh-details"><summary>Customer declined</summary>
              <input type="text" id="qd_reason" class="form-control" style="margin:6px 0;" placeholder="Reason (required)">
              <button class="btn btn-default btn-sm btn-block"
                onclick="var r=document.getElementById('qd_reason').value; if(!r){alert('A reason is required.');return;} if(confirm('Mark this quotation declined? The job stops waiting for acceptance.')) printJson('<?= base_url('printing/quote_decline') ?>',{job_id:<?= $job->id ?>,reason:r})">Mark declined</button>
            </details>
            <details class="jh-details"><summary>Cancel job</summary>
              <input type="text" id="jc_reason" class="form-control" style="margin:6px 0;" placeholder="Reason (required)">
              <button class="btn btn-danger btn-sm btn-block"
                onclick="var r=document.getElementById('jc_reason').value; if(!r){alert('A reason is required.');return;} if(confirm('Cancel this job entirely? History and payments are preserved.')) printJson('<?= base_url('printing/job_cancel') ?>',{job_id:<?= $job->id ?>,reason:r})">Cancel job</button>
            </details>
            <?php endif; ?>

            <?php if ($can_quote && in_array($lc, ['declined','cancelled','expired'], true)): ?>
            <button class="btn btn-default btn-sm btn-block"
              onclick="printJson('<?= base_url('printing/quote_reopen') ?>',{job_id:<?= $job->id ?>})"><i class="fa fa-undo"></i> Reopen quotation</button>
            <?php endif; ?>

            <?php if ($can_adjust && !$qsum): ?>
            <details class="jh-details"><summary>Adjust quotation</summary>
              <input type="number" step="0.01" id="qa_amount" class="form-control" style="margin:6px 0;" value="<?= (float)$job->quote_amount ?>">
              <input type="text" id="qa_reason" class="form-control" style="margin-bottom:6px;" placeholder="Reason (required)">
              <button class="btn btn-default btn-sm" onclick="var r=document.getElementById('qa_reason').value; if(!r){alert('Reason required');return;} printJson('<?= base_url('printing/quote_adjust') ?>',{job_id:<?= $job->id ?>,amount:document.getElementById('qa_amount').value,reason:r})">Apply adjustment</button>
            </details>
            <?php endif; ?>
          </div>
        </div>
        <div class="jh-side-card">
          <div class="jh-side-head">Tax <span class="jh-hint">controls the quotation</span></div>
          <div class="jh-side-body">
            <div class="jh-kv"><span>Current</span><strong><?= !empty($tax_summary['on']) ? htmlspecialchars($tax_summary['label']) : 'Exempt' ?></strong></div>
            <?php if ($can_quote): ?>
            <form onsubmit="return false;">
              <select class="form-control" id="tax_on" style="margin-bottom:6px;">
                <option value="0"<?= empty($job->tax_on) ? ' selected' : '' ?>>Exempt — no tax</option>
                <option value="1"<?= !empty($job->tax_on) ? ' selected' : '' ?>>Apply tax</option>
              </select>
              <select class="form-control" id="tax_id" style="margin-bottom:6px;">
                <?php foreach ($tax_rates as $tr): ?>
                <option value="<?= (int)$tr->id ?>"<?= (int)$job->tax_id === (int)$tr->id ? ' selected' : '' ?>><?= htmlspecialchars($tr->tax_name) ?> — <?= rtrim(rtrim(number_format((float)$tr->tax, 2, '.', ''), '0'), '.') ?>%</option>
                <?php endforeach; ?>
              </select>
              <select class="form-control" id="tax_type" style="margin-bottom:6px;">
                <option value="Exclusive"<?= ($job->tax_type ?: 'Exclusive') === 'Exclusive' ? ' selected' : '' ?>>Exclusive (added on top)</option>
                <option value="Inclusive"<?= $job->tax_type === 'Inclusive' ? ' selected' : '' ?>>Inclusive (already in price)</option>
              </select>
              <button class="btn btn-default btn-sm btn-block" onclick="printJson('<?= base_url('printing/tax_set') ?>',{job_id:<?= $job->id ?>,tax_on:document.getElementById('tax_on').value,tax_id:document.getElementById('tax_id').value,tax_type:document.getElementById('tax_type').value})">Apply tax setting</button>
            </form>
            <?php endif; ?>
          </div>
        </div>
        <div class="jh-side-card">
          <div class="jh-side-head">Artwork &amp; Authorization</div>
          <div class="jh-side-body">
            <div class="jh-kv"><span>Artwork</span><strong><?= htmlspecialchars($job->artwork_status) ?></strong></div>
            <div class="jh-kv"><span>Design</span><strong><?= htmlspecialchars($job->design_status) ?></strong></div>
            <div class="jh-kv"><span>Authorization</span><strong><?= htmlspecialchars($job->authorization_status) ?></strong></div>
            <?php if ($can_artwork): ?>
            <form id="artwork-form" action="<?= base_url('printing/artwork_save') ?>" method="post" enctype="multipart/form-data" style="margin-top:8px;">
              <input type="hidden" name="job_id" value="<?= $job->id ?>">
              <input type="file" name="artwork_file" class="form-control" style="margin-bottom:6px;">
              <button type="submit" class="btn btn-default btn-sm btn-block">Upload artwork</button>
            </form>
            <?php endif; ?>
            <?php foreach ($artworks as $a): ?>
            <div class="jh-art-row">v<?= $a->version_no ?> · <?= htmlspecialchars($a->file_name) ?> <span class="jh-pill <?= $a->status==='approved'?'ok':'' ?>"><?= $a->status ?></span>
              <?php if ($can_artwork && $a->status !== 'approved'): ?><button class="btn btn-xs btn-success" onclick="printJson('<?= base_url('printing/artwork_approve') ?>',{artwork_id:<?= $a->id ?>})">Approve</button><?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if ($can_design && $job->artwork_status === 'approved' && $job->design_status === 'pending'): ?>
            <button class="btn btn-default btn-sm btn-block" style="margin-top:8px;" onclick="printJson('<?= base_url('printing/design_clear') ?>',{job_id:<?= $job->id ?>,decision:'cleared',reason:'technical check passed'})">Designer — Clear</button>
            <?php endif; ?>
            <?php if ($can_authorize && $job->design_status === 'cleared' && $job->authorization_status === 'none'): ?>
            <select id="approver_id" class="form-control" style="margin-top:8px;"><?php foreach ($users as $u): ?><option value="<?= $u->id ?>"><?= htmlspecialchars($u->username) ?></option><?php endforeach; ?></select>
            <button class="btn btn-primary btn-sm btn-block" style="margin-top:6px;" onclick="printJson('<?= base_url('printing/auth_request') ?>',{job_id:<?= $job->id ?>,approver_id:document.getElementById('approver_id').value})">Request authorization</button>
            <?php endif; ?>
          </div>
        </div>
        <?php if (!empty($cost_estimate)): ?>
        <div class="jh-side-card">
          <div class="jh-side-head">Internal cost <span class="jh-hint">not customer-facing</span></div>
          <div class="jh-side-body">
            <div class="jh-kv"><span>Material</span><strong><?= $CI->currency() . number_format($cost_estimate['material_est'],2) ?></strong></div>
            <div class="jh-kv"><span>Operations</span><strong><?= $CI->currency() . number_format($cost_estimate['operations_est'],2) ?></strong></div>
            <div class="jh-kv"><span>Other (est.)</span><strong><?= $CI->currency() . number_format($cost_estimate['other_est'],2) ?></strong></div>
            <div class="jh-kv"><span>Design / service</span><strong><?= $CI->currency() . number_format($cost_estimate['design_internal_cost'],2) ?></strong></div>
            <?php $gp = (float)$job->quote_amount - $cost_estimate['total_est']; ?>
            <div class="jh-kv total"><span>Estimated profit</span><strong style="color:<?= $gp < 0 ? '#dc2626' : '#059669' ?>;"><?= $CI->currency() . number_format($gp,2) ?></strong></div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
<?php endif; ?>
</div>

<style>
.print-job-page{font-family:'Inter',sans-serif}
.pj-card{background:#fff;border:1px solid #d5e3e8;border-radius:12px;margin-bottom:16px;overflow:hidden}
.pj-card-head{display:flex;align-items:center;gap:10px;padding:13px 18px;background:#f4f8fa;border-bottom:1px solid #e2edf1;font-weight:700;color:#102a35;font-size:14px}
.pj-step{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:#0e7490;color:#fff;font-size:12px;font-weight:800}
.pj-hint{margin-left:auto;font-weight:500;font-size:11.5px;color:#5b6f78}
.pj-card-body{padding:16px 18px}
.pj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px}
.pj-field label{display:block;font-size:12.5px;font-weight:600;color:#102a35;margin-bottom:5px}
.pj-field .req{color:#dc2626}.pj-full{grid-column:1/-1}
.pj-item{border:1px solid #d5e3e8;border-radius:10px;padding:12px;margin-bottom:12px;background:#fbfdfe}
.pj-item-head{display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;align-items:center}
.pj-sizes{display:flex;flex-wrap:wrap;gap:8px}.pj-size{display:flex;align-items:center;gap:5px}.pj-size span{font-size:12px;font-weight:700;color:#5b6f78;min-width:26px}.pj-size input{width:70px}
.pj-chks{display:flex;flex-wrap:wrap;gap:12px}.pj-chk{font-size:12.5px;font-weight:500;color:#102a35}
.pj-charge{display:grid;grid-template-columns:2fr 1fr auto;gap:8px;margin-bottom:8px}
.pj-total{display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:12px;border-top:2px solid #e2edf1;font-size:14px;font-weight:600}
.pj-total strong{font-size:22px;font-family:'Bricolage Grotesque',sans-serif;color:#0b5d73}
.pj-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:8px}
.pj-inline-form{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:8px;background:#f4f8fa;border:1px solid #d5e3e8;border-radius:8px;padding:10px;margin-top:8px}
/* job view */
.jh-hero{display:flex;flex-wrap:wrap;gap:16px;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#0b5d73,#0e7490);border-radius:14px;padding:20px 24px;color:#fff;margin-bottom:16px}
.jh-code{font-family:ui-monospace,monospace;font-size:12px;background:rgba(255,255,255,.16);border-radius:6px;padding:3px 9px;display:inline-block}
.jh-title{font-family:'Bricolage Grotesque',sans-serif;font-size:22px;margin:8px 0 6px;color:#fff}
.jh-meta{display:flex;flex-wrap:wrap;gap:16px;font-size:13px;opacity:.94}.jh-meta i{opacity:.75;margin-right:5px}
.jh-hero-money{display:flex;gap:26px}.jh-hero-money div span{display:block;font-size:11px;opacity:.8;text-transform:uppercase;letter-spacing:.05em}.jh-hero-money div strong{font-size:20px;font-family:'Bricolage Grotesque',sans-serif}
.jh-steps{display:flex;flex-wrap:wrap;gap:6px;background:#fff;border:1px solid #d5e3e8;border-radius:12px;padding:12px 14px;margin-bottom:12px}
.jh-step{display:flex;align-items:center;gap:7px;font-size:12.5px;color:#9db2bb;padding:5px 12px;font-weight:600;border-radius:999px;border:1px solid transparent}
.jh-step.done{color:#065f46;background:#ecfdf5;border-color:#a7f3d0}
.jh-step.active{color:#0b5d73;background:#e0f2f8;border-color:#7dd3e8;box-shadow:0 0 0 2px rgba(14,116,144,.10)}
.jh-step.pending{color:#9db2bb}
.jh-step.blocked{color:#b45309;background:#fffbeb;border-color:#fde68a}
.jh-dot{width:20px;height:20px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;background:#eef2f4;font-size:11px}
.jh-step.done .jh-dot{background:#059669;color:#fff}
.jh-step.active .jh-dot{background:#0e7490;color:#fff}
.jh-here{font-size:9.5px;text-transform:uppercase;letter-spacing:.06em;background:#0e7490;color:#fff;border-radius:999px;padding:1px 7px;font-weight:700}
.jh-next{background:#f0f9ff;border:1px solid #bae6fd;color:#0c4a6e;border-radius:10px;padding:9px 14px;font-size:12.5px;margin-bottom:16px}
.jh-banner{border-radius:10px;padding:11px 16px;font-size:13px;font-weight:600;margin-bottom:16px}
.jh-banner.warn{background:#fef3c7;color:#b45309}.jh-banner.ok{background:#d1fae5;color:#065f46}
.jh-cols{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:16px;align-items:start}
@media(max-width:1024px){.jh-cols{grid-template-columns:1fr}}
.jh-item{background:#fff;border:1px solid #d5e3e8;border-radius:12px;padding:16px 18px;margin-bottom:14px}
.jh-item-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:12px}
.jh-tag{background:#e0f2f8;color:#0b5d73;border-radius:999px;padding:2px 10px;font-size:11px;font-weight:700;margin-right:6px}
.jh-item-head strong{font-size:15px}
.jh-item-qty{font-size:13px;color:#5b6f78}.jh-item-qty strong{font-size:15px;color:#0b5d73;margin-left:6px}
.jh-chips{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:6px}
.jh-chip{background:#f4f8fa;border:1px solid #e2edf1;border-radius:8px;padding:5px 10px;font-size:12.5px;color:#102a35}
.jh-chip em{font-style:normal;color:#7d949d;font-weight:600;margin-right:4px}
.jh-line-block{margin-top:12px;padding-top:12px;border-top:1px dashed #e2edf1}
.jh-line-title{font-size:11px;font-weight:800;color:#0b5d73;text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px}
.jh-svc-row{display:flex;justify-content:space-between;align-items:center;gap:10px;font-size:13.5px;padding:6px 0}
.jh-svc-name{font-weight:600;color:#102a35}.jh-svc-name i{color:#0e7490;width:16px}.jh-svc-name em{font-style:normal;color:#9db2bb;font-size:11px;font-weight:500}
.jh-svc-row s{color:#9db2bb}.jh-waived{background:#fef3c7;color:#b45309;border-radius:999px;padding:1px 8px;font-size:10.5px;font-weight:700}
.jh-waiver-why{font-size:11px;color:#9db2bb}
.jh-plan-list{display:flex;flex-direction:column;gap:4px}
.jh-plan-row{display:grid;grid-template-columns:26px 1fr auto auto;gap:10px;align-items:center;font-size:13px;padding:5px 0}
.jh-plan-kind{width:24px;height:20px;display:inline-flex;align-items:center;justify-content:center;border-radius:5px;font-size:10px;font-weight:800}
.jh-plan-kind.material{background:#e0f2f8;color:#0b5d73}.jh-plan-kind.operation{background:#ede9fe;color:#6d28d9}
.jh-plan-name{color:#102a35}.jh-plan-qty{color:#5b6f78}.jh-plan-cost{font-weight:700;color:#0b5d73}
.jh-warn{background:#fef3c7;color:#b45309;border-radius:4px;padding:1px 6px;font-size:10px;font-weight:700}
.jh-line-actions{display:flex;gap:14px;margin-top:10px}
.jh-link{background:none;border:none;color:#0e7490;font-size:12.5px;font-weight:700;cursor:pointer;padding:0}
.jh-side-card{background:#fff;border:1px solid #d5e3e8;border-radius:12px;margin-bottom:14px;overflow:hidden}
.jh-side-head{display:flex;align-items:center;gap:8px;padding:12px 16px;background:#f4f8fa;border-bottom:1px solid #e2edf1;font-weight:700;font-size:13.5px;color:#102a35}
.jh-rev{background:#0e7490;color:#fff;border-radius:3px;padding:1px 6px;font-size:10.5px;vertical-align:middle}
.jh-btn-row{display:flex;gap:6px;margin-bottom:6px}
.jh-btn-row .btn{flex:1}
.jh-inline-warn{background:#fffbeb;border:1px solid #fde68a;color:#92400e;border-radius:6px;padding:7px 9px;font-size:11.5px;margin-bottom:8px}
.jh-hint{font-weight:500;font-size:11px;color:#7c8f99;text-transform:none;letter-spacing:0}
.jh-lifecycle{margin:8px 0;padding-top:8px;border-top:1px dashed #e2edf1}
.jh-days{display:inline-block;background:#e6f4f8;color:#0e7490;border-radius:999px;padding:1px 7px;font-size:10.5px;font-weight:700;margin-left:5px;vertical-align:middle}
.jh-days.soon{background:#fffbeb;color:#b45309}
.jh-lc{padding:1px 8px;border-radius:999px;font-size:11px}
.jh-lc-issued{background:#eff6ff;color:#1e40af}
.jh-lc-accepted{background:#ecfdf5;color:#065f46}
.jh-lc-declined{background:#fef2f2;color:#b91c1c}
.jh-lc-cancelled{background:#f1f5f9;color:#475569}
.jh-lc-expired{background:#fffbeb;color:#92400e}
.jh-inline-note{font-size:11.5px;color:#64748b;margin-top:5px;font-style:italic}
.jh-side-body{padding:14px 16px}
.jh-kv{display:flex;justify-content:space-between;align-items:center;gap:10px;font-size:13px;padding:6px 0;border-bottom:1px solid #f0f6f8}
.jh-kv:last-child{border-bottom:none}.jh-kv span{color:#5b6f78}.jh-kv strong{color:#102a35;text-align:right}
.jh-kv.total{border-top:1px solid #e2edf1;margin-top:4px;padding-top:10px}.jh-kv.total strong{font-size:16px}
.jh-details{margin-top:10px}.jh-details summary{cursor:pointer;font-size:12.5px;color:#0e7490;font-weight:700}
.jh-art-row{font-size:12.5px;color:#5b6f78;margin-top:8px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.jh-pill{background:#eef2f4;color:#5b6f78;border-radius:999px;padding:1px 8px;font-size:10.5px;font-weight:700}
.jh-pill.ok{background:#d1fae5;color:#065f46}
</style>

<script>
/* CSRF: every action on this screen posts over fetch(), and CodeIgniter
   rejects a POST with no token ("token absent"). The token is therefore sent
   with each request, and the hash the server returns is kept for the next one
   so a refreshed token can never go stale mid-session. */
var MP_CSRF_NAME = '<?= $CI->security->get_csrf_token_name() ?>';
var MP_CSRF_HASH = '<?= $CI->security->get_csrf_hash() ?>';

function issuePrintQuotation(revision){
  var customer = document.getElementById('quote-customer-id');
  if (!customer || !customer.value) { alert('Select the customer for this quotation first.'); if(customer) customer.focus(); return; }
  var data = {job_id:<?= (int)($job->id ?? 0) ?>, customer_id:customer.value};
  if (revision) data.revision_note = 'Revised from job screen';
  printJson('<?= base_url('printing/quote_issue') ?>', data);
}

function printJson(url, data){
  var fd = new FormData();
  for (var k in data) fd.append(k, data[k]);
  fd.append(MP_CSRF_NAME, MP_CSRF_HASH);
  fetch(url, {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){return r.json();})
    .then(function(d){
      if (d && d.csrf_hash) MP_CSRF_HASH = d.csrf_hash;
      if (d.success) { location.reload(); } else { alert(d.message || 'Error'); }
    })
    .catch(function(){ alert('Request failed'); });
}
function saveService(jobId, lineId, btn){
  var f = btn.closest('.pj-inline-form');
  printJson('<?= base_url('printing/service_save') ?>', {
    job_id: jobId, line_id: lineId,
    service_key: f.querySelector('.pj-svc-type').value,
    charge_amount: f.querySelector('.pj-svc-charge-in').value,
    internal_cost: f.querySelector('.pj-svc-cost-in').value,
    charge_waived: f.querySelector('.pj-svc-waive').checked ? 1 : 0,
    waiver_reason: f.querySelector('.pj-svc-reason').value,
    design_mode: f.querySelector('.pj-svc-mode').value
  });
}
function savePlan(jobId, lineId, btn){
  var f = btn.closest('.pj-inline-form');
  printJson('<?= base_url('printing/plan_save') ?>', {
    job_id: jobId, line_id: lineId,
    plan_type: f.querySelector('.pj-plan-type').value,
    item_id: f.querySelector('.pj-plan-item').value,
    plan_unit_id: f.querySelector('.pj-plan-unit').value,
    description: f.querySelector('.pj-plan-desc').value,
    plan_qty: f.querySelector('.pj-plan-qty').value,
    est_unit_cost: f.querySelector('.pj-plan-cost').value,
    wastage_pct: f.querySelector('.pj-plan-waste').value
  });
}
<?php if (!empty($job)): ?>
document.getElementById('artwork-form') && document.getElementById('artwork-form').addEventListener('submit', function(e){
  e.preventDefault();
  fetch(this.getAttribute('action'), {method:'POST', body:new FormData(this), headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){return r.json();})
    .then(function(d){ if(d.success){ location.reload(); } else { alert(d.message || 'Error'); } })
    .catch(function(){ alert('Upload failed'); });
});
<?php endif; ?>
</script>
