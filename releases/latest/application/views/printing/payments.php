<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-section">
    <div class="mp-page-head">
        <h2>Print Payments</h2>
        <div class="mp-page-sub">Deposits, collections, refunds and credits with Finance receive-verification. Unverified transfers never clear the deposit gate.</div>
    </div>
    <div class="table-wrap" style="overflow-x:auto;">
        <table class="table table-striped" style="min-width:800px;">
            <thead>
                <tr><th>Job</th><th>Kind</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th><th>Verified By</th><th>Created</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                <tr><td colspan="9" class="text-muted text-center">No payments recorded yet.</td></tr>
                <?php else: foreach ($payments as $p): ?>
                <tr>
                    <td><span class="label label-default"><?= htmlspecialchars($p->job_code) ?></span></td>
                    <td><span class="label label-default"><?= $p->payment_kind ?></span></td>
                    <td><?= $CI->currency() . number_format((float)$p->amount, 2) ?></td>
                    <td><?= htmlspecialchars($p->method) ?></td>
                    <td><?= htmlspecialchars($p->reference ?: '-') ?></td>
                    <td><span class="label label-<?= $p->status === 'verified' ? 'success' : ($p->status === 'reversed' ? 'danger' : 'warning') ?>"><?= $p->status ?></span></td>
                    <td><?= htmlspecialchars($p->verified_by ?: '—') ?></td>
                    <td><?= $p->created_at ?: '—' ?></td>
                    <td>
                        <?php if (!empty($can_verify) && $p->status === 'received'): ?>
                        <button class="btn btn-xs btn-success" onclick="printVerifyPayment(<?= $p->id ?>)"><i class="fa fa-check"></i> Verify</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php if (!empty($can_verify)): ?>
<script>
function printVerifyPayment(id){
    if(!confirm('Confirm this receipt as verified?')) return;
    var fd = new FormData();
    fd.append('payment_id', id);
    // CodeIgniter rejects a POST with no token; send it with the request.
    fd.append('<?= $CI->security->get_csrf_token_name() ?>', '<?= $CI->security->get_csrf_hash() ?>');
    fetch('<?= base_url('printing/payment_verify') ?>', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){return r.json();}).then(function(d){ alert(d.message || 'Updated'); if(d.success) location.reload(); })
        .catch(function(){ alert('Request failed'); });
}
</script>
<?php endif; ?>
