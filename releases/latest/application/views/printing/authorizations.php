<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-section">
    <div class="mp-page-head">
        <h2>Print Authorization</h2>
        <div class="mp-page-sub">Internal authorization to proceed to production. Version-fingerprinted — changing artwork invalidates a standing approval.</div>
    </div>
    <div class="table-wrap" style="overflow-x:auto;">
        <table class="table table-striped" style="min-width:800px;">
            <thead>
                <tr><th>Job</th><th>Title</th><th>Artwork v</th><th>Status</th><th>Reason</th><th>Decided By</th><th>Decided At</th></tr>
            </thead>
            <tbody>
                <?php if (empty($auths)): ?>
                <tr><td colspan="7" class="text-muted text-center">No authorization requests yet.</td></tr>
                <?php else: foreach ($auths as $a): ?>
                <tr>
                    <td><span class="label label-default"><?= htmlspecialchars($a->job_code) ?></span></td>
                    <td><?= htmlspecialchars($a->title ?: '-') ?></td>
                    <td>v<?= $a->artwork_version ?></td>
                    <td><span class="label label-<?= $a->status === 'authorized' ? 'success' : ($a->status === 'rejected' ? 'danger' : ($a->status === 'invalidated' ? 'warning' : 'default')) ?>"><?= $a->status ?></span></td>
                    <td><?= htmlspecialchars($a->reason ?: '-') ?><?= $a->urgent_override ? ' <span class="label label-warning">urgent</span>' : '' ?></td>
                    <td><?= htmlspecialchars($a->decided_by ?: '—') ?></td>
                    <td><?= $a->decided_at ?: '—' ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
