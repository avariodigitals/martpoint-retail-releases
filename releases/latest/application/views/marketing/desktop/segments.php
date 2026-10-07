<?php $this->load->view('marketing/desktop/_styles'); ?>
<style>
.mp-seg-grid{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(240px,1fr))!important;gap:14px!important;margin-bottom:20px!important}
.mp-seg-card{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:16px!important}
.mp-seg-title{font-weight:700!important;font-size:14px!important;color:var(--mp-text)!important}
.mp-seg-desc{font-size:12px!important;color:var(--mp-muted)!important;margin:4px 0 10px!important;min-height:30px!important}
.mp-seg-count{font-size:20px!important;font-weight:700!important;color:var(--mp-primary)!important}
.mp-seg-actions{margin-top:10px!important;display:flex!important;gap:8px!important}
.mp-seg-preview{margin-top:10px!important;font-size:12px!important;color:var(--mp-muted)!important;max-height:160px!important;overflow-y:auto!important;display:none!important}
.mp-seg-preview table{width:100%!important;border-collapse:collapse!important}
.mp-seg-preview td{padding:3px 6px!important;border-bottom:1px solid var(--mp-border)!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Customer Segments'); ?></h2>
    <div class="mp-page-sub">Audience groups resolved live from your customer and sales data</div>
  </div>
</div>

<div class="mp-seg-grid">
  <?php foreach($segments as $seg): $key = $seg['key']; ?>
    <div class="mp-seg-card">
      <div class="mp-seg-title"><?= htmlspecialchars($seg['name']); ?></div>
      <div class="mp-seg-desc"><?= htmlspecialchars($seg['desc']); ?></div>
      <div class="mp-seg-count" id="seg-count-<?= $key; ?>"><?= (int)$seg['count']; ?></div>
      <div class="mp-seg-actions">
        <button class="mp-btn-secondary mp-seg-preview-btn" data-key="<?= $key; ?>"><i class="fa fa-eye"></i> Preview</button>
        <button class="mp-btn-secondary mp-seg-save-btn" data-key="<?= $key; ?>" data-name="<?= htmlspecialchars($seg['name']); ?>"><i class="fa fa-bookmark"></i> Save</button>
      </div>
      <div class="mp-seg-preview" id="seg-preview-<?= $key; ?>"></div>
    </div>
  <?php endforeach; ?>
</div>

<?php if(!empty($saved)): ?>
<div class="mp-card-form">
  <div class="mp-card-head"><h3>Saved Segments</h3></div>
  <div class="mp-card-body">
    <div class="mp-table-wrap">
      <table class="mp-static-table">
        <thead><tr><th>Name</th><th>Source segment</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($saved as $s): ?>
          <tr>
            <td class="row-name"><?= htmlspecialchars($s->name); ?></td>
            <td><?= htmlspecialchars($s->segment_key); ?></td>
            <td><?= htmlspecialchars($s->created_at); ?></td>
            <td>
              <button class="mp-btn-secondary mp-saved-preview-btn" data-id="<?= (int)$s->id; ?>"><i class="fa fa-eye"></i> Preview</button>
              <button class="mp-delete mp-saved-del-btn" data-id="<?= (int)$s->id; ?>"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
var CSRF = {name: '<?= $this->security->get_csrf_token_name(); ?>', hash: '<?= $this->security->get_csrf_hash(); ?>'};
function mpPost(url, data, cb){
  data[CSRF.name] = CSRF.hash;
  $.post(url, data, function(r){
    if(r && r.csrf_hash) CSRF.hash = r.csrf_hash;
    cb(r);
  }, 'json');
}
$('.mp-seg-preview-btn').on('click', function(){
  var key = $(this).data('key'), box = $('#seg-preview-' + key);
  mpPost('<?= base_url('marketing/segment_preview'); ?>', {segment_key: key}, function(r){
    if(r.status !== 'success'){ box.html('<em>' + (r.message || 'Failed') + '</em>').show(); return; }
    $('#seg-count-' + key).text(r.count);
    var h = '<table>';
    r.sample.forEach(function(m){ h += '<tr><td>' + $('<div>').text(m.name).html() + '</td><td>' + $('<div>').text(m.email || m.phone || '—').html() + '</td></tr>'; });
    box.html(h + '</table>').show();
  });
});
$('.mp-seg-save-btn').on('click', function(){
  mpPost('<?= base_url('marketing/save_segment'); ?>', {segment_key: $(this).data('key'), name: $(this).data('name')}, function(r){
    alert(r.message || (r.status === 'success' ? 'Saved' : 'Failed'));
    if(r.status === 'success') location.reload();
  });
});
$('.mp-saved-preview-btn').on('click', function(){
  mpPost('<?= base_url('marketing/segment_preview'); ?>', {segment_id: $(this).data('id')}, function(r){
    alert(r.status === 'success' ? ('Members: ' + r.count + '\n' + r.sample.map(function(m){ return m.name + ' — ' + (m.email || m.phone); }).join('\n').substr(0, 800)) : (r.message || 'Failed'));
  });
});
$('.mp-saved-del-btn').on('click', function(){
  if(!confirm('Delete this saved segment?')) return;
  mpPost('<?= base_url('marketing/delete_segment'); ?>', {id: $(this).data('id')}, function(r){
    if(r.status === 'success') location.reload(); else alert('Delete failed');
  });
});
$(".marketing-segments-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>
