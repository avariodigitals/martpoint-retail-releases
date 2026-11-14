<?php $this->load->view('creator/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2>Students</h2>
    <div class="mp-page-sub"><?= count($rows); ?> enrolments across your courses</div>
  </div>
  <a href="<?= base_url('creator/products/course'); ?>" class="mp-btn"><i class="fa fa-play-circle"></i> Manage Courses</a>
</div>

<?php if(empty($rows)): ?>
  <div class="cr-empty">
    <div class="icon"><i class="fa fa-graduation-cap"></i></div>
    <h3>No students yet</h3>
    <p>When a customer buys a course they are enrolled automatically and appear here with their progress.</p>
    <a href="<?= base_url('creator/products/course'); ?>" class="mp-btn mp-btn-primary">View Courses</a>
  </div>
<?php else: ?>
<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Enrolments</h3></div>
  <div class="mp-dt-scroll">
    <table class="mp-dt-table">
      <thead>
        <tr><th>Student</th><th>Course</th><th>Progress</th><th>Status</th><th>Enrolled</th><th style="width:120px;">Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
        <tr>
          <td>
            <div class="row-name"><?= htmlspecialchars($r->customer_name ?: 'Customer #'.$r->customer_id); ?></div>
            <div class="row-meta"><?= htmlspecialchars($r->email ?: $r->mobile ?: ''); ?></div>
          </td>
          <td><?= htmlspecialchars($r->course_title ?: '—'); ?></td>
          <td>
            <span class="cr-progress"><span style="width:<?= (int)$r->progress; ?>%"></span></span>
            <span style="font-size:12px;font-weight:600;margin-left:8px;"><?= (int)$r->progress; ?>%</span>
          </td>
          <td><span class="cr-pill <?= htmlspecialchars($r->status); ?>"><?= htmlspecialchars($r->status); ?></span></td>
          <td class="row-meta"><?= show_date($r->created_at); ?></td>
          <td>
            <div class="cr-table-actions">
              <?php if(!empty($r->customer_id)): ?><a href="<?= base_url('customers/update/' . $r->customer_id); ?>">Profile</a><?php endif; ?>
              <a href="<?= base_url('courses/edit/' . $r->course_id); ?>" class="primary">Course</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
