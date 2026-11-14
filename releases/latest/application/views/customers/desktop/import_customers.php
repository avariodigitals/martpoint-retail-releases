<?php $this->load->view('customers/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Import <?= mp_label('customer'); ?>s from a CSV file — upload, map columns, preview, then run.</div>
  </div>
</div>

<!-- STEP 1: Upload -->
<div class="mp-card-form box" id="step-upload">
  <div class="mp-card-head"><h3>1. Choose CSV File</h3></div>
  <div class="mp-card-body">
    <form class="form-horizontal" id="import-form" enctype="multipart/form-data" method="POST">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" id="csrf_field" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
      <input type="hidden" name="store_id" id="store_id" value="<?= htmlspecialchars(get_current_store_id()); ?>">
      <div class="mp-form-grid">
        <div class="mp-form-group full">
          <label for="import_file"><?= $this->lang->line('import_customers'); ?> <span class="text-danger">*</span></label>
          <input type="file" class="mp-form-control" id="import_file" name="import_file" accept=".csv" style="padding:9px 14px;">
          <span id="import_file_msg" style="display:block;" class="text-muted">CSV format only, up to 10 MB. The first row may be a header.</span>
        </div>
      </div>
      <div class="mp-form-actions" style="margin-top:24px;">
        <button type="button" id="btn-upload" class="mp-btn-primary"><i class="fa fa-upload"></i> Upload &amp; Map Columns</button>
        <a href="<?= base_url('import/download/customers'); ?>" class="mp-btn-secondary"><i class="fa fa-file-excel-o"></i> <?= $this->lang->line('download_example_format'); ?></a>
      </div>
    </form>
  </div>
</div>

<!-- STEP 2: Mapping (populated after upload) -->
<div class="mp-card box" id="step-map" style="display:none;">
  <div class="mp-card-head"><h3>2. Map Columns &amp; Duplicate Policy</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid" id="map-grid"></div>
    <div class="mp-form-grid" style="margin-top:18px;">
      <div class="mp-form-group">
        <label>First row is a header</label>
        <select id="has_header" class="mp-form-control">
          <option value="1" selected>Yes — skip first row</option>
          <option value="0">No — import every row</option>
        </select>
      </div>
      <div class="mp-form-group">
        <label>When a customer already exists</label>
        <select id="dup_policy" class="mp-form-control">
          <option value="skip" selected>Skip the row (keep existing record)</option>
          <option value="update">Update the existing customer</option>
          <option value="reject">Reject the row as an error</option>
        </select>
        <span class="mp-form-hint">Duplicates are matched on mobile number or email.</span>
      </div>
    </div>
    <div class="mp-form-actions" style="margin-top:20px;">
      <button type="button" id="btn-preview" class="mp-btn-primary"><i class="fa fa-eye"></i> Preview Import</button>
    </div>
  </div>
</div>

<!-- STEP 3: Preview result -->
<div class="mp-card box" id="step-preview" style="display:none;">
  <div class="mp-card-head"><h3>3. Preview</h3></div>
  <div class="mp-card-body">
    <div class="mp-kpi-grid">
      <div class="mp-kpi-card summary"><div class="mp-kpi-label">Total rows</div><div class="mp-kpi-value" id="pv-total">0</div></div>
      <div class="mp-kpi-card sales"><div class="mp-kpi-label">Ready</div><div class="mp-kpi-value" id="pv-valid">0</div></div>
      <div class="mp-kpi-card stock"><div class="mp-kpi-label">Duplicates</div><div class="mp-kpi-value" id="pv-dup">0</div></div>
      <div class="mp-kpi-card debt"><div class="mp-kpi-label">Errors</div><div class="mp-kpi-value" id="pv-err">0</div></div>
    </div>
    <div id="pv-errors-list" style="margin-top:14px;"></div>
    <div class="mp-form-actions" style="margin-top:20px;">
      <button type="button" id="btn-run" class="mp-btn-primary"><i class="fa fa-play"></i> Run Import</button>
      <button type="button" id="btn-remap" class="mp-btn-secondary">Back to Mapping</button>
    </div>
  </div>
</div>

<!-- STEP 4: Progress / result -->
<div class="mp-card box" id="step-run" style="display:none;">
  <div class="mp-card-head"><h3>4. Import Progress</h3></div>
  <div class="mp-card-body">
    <div class="progress" style="height:22px;background:#eef2f7;border-radius:6px;overflow:hidden;">
      <div id="run-bar" class="progress-bar progress-bar-success" style="height:100%;width:0%;line-height:22px;color:#fff;text-align:center;transition:width .3s;">0%</div>
    </div>
    <div id="run-summary" style="margin-top:14px;"></div>
    <div class="mp-form-actions" style="margin-top:16px;">
      <a id="run-errors-link" href="#" class="mp-btn-secondary" style="display:none;"><i class="fa fa-download"></i> Download error report (CSV)</a>
      <a href="<?= base_url('customers'); ?>" id="run-done-link" class="mp-btn-primary" style="display:none;">Go to Customers</a>
      <button type="button" id="btn-resume" class="mp-btn-primary" style="display:none;"><i class="fa fa-repeat"></i> Resume Import</button>
    </div>
  </div>
</div>

<!-- Audit history -->
<div class="mp-card box" style="margin-top:24px;">
  <div class="mp-card-head"><h3><i class="fa fa-history"></i> Import History</h3></div>
  <div class="mp-card-body" style="padding:0;">
    <table class="table mp-static-table">
      <thead><tr>
        <th>#</th><th>Date</th><th>File</th><th>By</th><th>Rows</th>
        <th>Imported</th><th>Updated</th><th>Skipped/Dup</th><th>Errors</th><th>Status</th><th></th>
      </tr></thead>
      <tbody>
      <?php if(empty($import_history)): ?>
        <tr><td colspan="11" class="text-center text-muted" style="padding:24px;">No imports yet.</td></tr>
      <?php else: foreach($import_history as $b): ?>
        <tr>
          <td><?= (int)$b->id; ?></td>
          <td><?= htmlspecialchars($b->created_at); ?></td>
          <td><?= htmlspecialchars($b->filename); ?></td>
          <td><?= htmlspecialchars($b->created_by); ?></td>
          <td><?= (int)$b->total_rows; ?></td>
          <td><?= (int)$b->ok_rows; ?></td>
          <td><?= (int)$b->updated_rows; ?></td>
          <td><?= (int)$b->dup_rows; ?></td>
          <td><?= (int)$b->error_rows; ?></td>
          <td><span class="mp-pill <?= $b->status==='completed'?'success':($b->status==='failed'?'danger':'muted'); ?>"><?= htmlspecialchars($b->status); ?></span></td>
          <td>
            <?php if((int)$b->error_rows + (int)$b->dup_rows > 0): ?>
              <a class="mp-qa-btn blue" style="padding:4px 10px;" href="<?= base_url('import/import_customers_errors/'.$b->id); ?>" title="Download row report"><i class="fa fa-download"></i></a>
            <?php endif; ?>
            <?php if(in_array($b->status,array('failed','processing'))): ?>
              <button type="button" class="mp-qa-btn orange resume-batch" data-batch="<?= (int)$b->id; ?>" title="Resume"><i class="fa fa-repeat"></i></button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="mp-card box" style="margin-top:24px;">
  <div class="mp-card-head"><h3><?= $this->lang->line('import_instructions'); ?></h3></div>
  <div class="mp-card-body" style="padding:0;">
    <table class="table mp-static-table">
      <thead><tr><th>#</th><th><?= $this->lang->line('column_name'); ?></th><th><?= $this->lang->line('value'); ?></th><th><?= $this->lang->line('details'); ?></th></tr></thead>
      <tbody>
        <?php $i=1; foreach($import_fields as $fkey=>$flabel): $is_req = ($fkey==='customer_name'); ?>
        <tr><td><?= $i++; ?></td><td><?= htmlspecialchars(rtrim($flabel,' *')); ?></td>
          <td><span class="mp-pill <?= $is_req?'success':'muted'; ?>"><?= $is_req?$this->lang->line('required'):$this->lang->line('optional'); ?></span></td>
          <td><?= $fkey==='credit_limit' ? '[ <b>-1</b> for No Limit]' : ''; ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
.mp-map-row{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #eef2f7;}
.mp-map-row label{flex:0 0 220px;font-weight:600;margin:0;}
.mp-map-row select{flex:1;}
.mp-map-sample{flex:0 0 220px;color:#64748b;font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
</style>

<script type="text/javascript">
(function(){
  var base_url = $('#base_url').val();
  var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
  var batchId = 0, headers = [], fields = {};

  function csrf(){ var o={}; o[csrfName]=$('#csrf_field').val(); return o; }
  function refreshCsrf(h){ if(h){ $('#csrf_field').val(h); } }
  function toastErr(m){ toastr['error'](m); if(typeof failed!=='undefined'){failed.currentTime=0;failed.play();} }

  // STEP 1 — upload
  $('#btn-upload').on('click',function(){
    if($('#import_file').val()==''){ toastr['warning']('Please select file to Import!'); return; }
    var fd = new FormData($('#import-form')[0]);
    $(this).prop('disabled',true);
    $('.box').append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.ajax({type:'POST',url:base_url+'import/import_customers_upload',data:fd,cache:false,contentType:false,processData:false,dataType:'json'})
    .done(function(res){
      refreshCsrf(res.csrf_hash);
      if(res.status!=='success'){ toastErr(res.message||'Upload failed'); return; }
      batchId = res.batch_id; headers = res.headers; fields = res.fields;
      buildMapping(res.suggested, res.samples);
      $('#step-map').show(); $('#step-preview,#step-run').hide();
      $('html,body').animate({scrollTop:$('#step-map').offset().top-80},300);
    })
    .fail(function(){ toastErr('Upload failed — check the file and try again.'); })
    .always(function(){ $('#btn-upload').prop('disabled',false); $('.overlay').remove(); });
  });

  function buildMapping(suggested, samples){
    var g = $('#map-grid').empty();
    var sample = samples && samples.length ? samples[0] : [];
    $.each(fields,function(key,label){
      var sel = $('<select class="mp-form-control"></select>').attr('data-field',key)
        .append('<option value="-1">— not in file —</option>');
      $.each(headers,function(i,h){
        var o=$('<option></option>').attr('value',i).text((h||('Column '+(i+1))).substring(0,40));
        if(suggested && suggested[key]===i){ o.prop('selected',true); }
        sel.append(o);
      });
      var sv = (suggested && suggested[key]!==undefined && sample[suggested[key]]!==undefined) ? sample[suggested[key]] : '';
      g.append($('<div class="mp-map-row"></div>')
        .append('<label>'+label+'</label>').append(sel)
        .append('<div class="mp-map-sample">e.g. '+String(sv).substring(0,40)+'</div>'));
    });
  }

  // STEP 2 — preview
  $('#btn-preview').on('click',function(){
    var data = csrf();
    data.batch_id = batchId;
    data.has_header = $('#has_header').val();
    data.dup_policy = $('#dup_policy').val();
    $('#map-grid select').each(function(){ var v=parseInt($(this).val(),10); if(v>=0){ data['map['+$(this).attr('data-field')+']']=v; } });
    $(this).prop('disabled',true);
    $.post(base_url+'import/import_customers_preview',data,null,'json')
    .done(function(res){
      refreshCsrf(res.csrf_hash);
      if(res.status!=='success'){ toastErr(res.message||'Preview failed'); return; }
      $('#pv-total').text(res.total); $('#pv-valid').text(res.valid);
      $('#pv-dup').text(res.duplicates); $('#pv-err').text(res.errors);
      var html='';
      $.each(res.sample_errors,function(i,e){ html+='<div class="mp-map-row"><span class="mp-pill danger">'+e.status+'</span>&nbsp;Row '+e.row_number+': '+$('<div>').text(e.error_message).html()+'</div>'; });
      $('#pv-errors-list').html(html);
      $('#step-preview').show();
      $('html,body').animate({scrollTop:$('#step-preview').offset().top-80},300);
    })
    .fail(function(){ toastErr('Preview failed — please try again.'); })
    .always(function(){ $('#btn-preview').prop('disabled',false); });
  });
  $('#btn-remap').on('click',function(){ $('#step-preview').hide(); });

  // STEP 3/4 — chunked run
  $('#btn-run').on('click',function(){ $('#step-run').show(); runChunk(); });
  $('#btn-resume').on('click',function(){ runChunk(); });
  $('.resume-batch').on('click',function(){ batchId=$(this).data('batch'); $('#step-run').show(); runChunk(); });

  function runChunk(){
    $('#btn-resume').hide();
    var data = csrf(); data.batch_id = batchId;
    $.post(base_url+'import/import_customers_run',data,null,'json')
    .done(function(res){
      refreshCsrf(res.csrf_hash);
      if(res.status==='error'){ toastErr(res.message||'Import error'); $('#btn-resume').show(); updateRunUI(res); return; }
      var pct = res.total>0 ? Math.round(res.processed/res.total*100) : 100;
      $('#run-bar').css('width',pct+'%').text(pct+'%');
      updateRunUI(res);
      if(!res.done){ runChunk(); }
      else{ $('#run-done-link').show(); }
    })
    .fail(function(){ toastErr('Connection lost — the import can be resumed safely.'); $('#btn-resume').show(); });
  }
  function updateRunUI(res){
    $('#run-summary').html('Imported: <b>'+(res.ok||0)+'</b> &nbsp; Updated: <b>'+(res.updated||0)+'</b> &nbsp; Skipped: <b>'+(res.skipped||0)+'</b> &nbsp; Errors: <b>'+(res.errors||0)+'</b>');
    if((res.errors||0)>0){ $('#run-errors-link').attr('href',base_url+'import/import_customers_errors/'+batchId).show(); }
  }
})();
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.import_customers-active-li').addClass('active');
  $('.import_customers-active-li').closest('.mp-nav-group').addClass('open');
</script>
