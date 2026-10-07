<?php
$_p = isset($p) ? $p : null;
$_store = htmlspecialchars($store_name ?? 'Portal');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $_store ?> — Patient Portal<?= isset($title) ? ' — '.htmlspecialchars($title) : ''; ?></title>
<style>
  :root { --mp-primary:#2456d6; --mp-bg:#f4f6fb; --mp-ink:#1c2333; --mp-muted:#6b7280; --mp-line:#e5e7eb; --mp-good:#0e8a5f; --mp-warn:#b45309; --mp-bad:#b91c1c; }
  * { box-sizing:border-box; }
  body { margin:0; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; background:var(--mp-bg); color:var(--mp-ink); }
  .wrap { max-width:720px; margin:0 auto; padding:18px 16px 60px; }
  .topbar { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:12px 0 16px; border-bottom:1px solid var(--mp-line); margin-bottom:16px; }
  .store-name { font-size:11px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; color:var(--mp-muted); }
  .topbar h1 { font-size:20px; margin:2px 0 0; }
  .card { background:#fff; border:1px solid var(--mp-line); border-radius:14px; padding:16px; margin-bottom:14px; }
  .card h3 { margin:0 0 10px; font-size:13px; text-transform:uppercase; letter-spacing:.5px; color:var(--mp-muted); }
  table { width:100%; border-collapse:collapse; font-size:13px; }
  th { text-align:left; font-size:11px; text-transform:uppercase; color:var(--mp-muted); padding:6px 4px; border-bottom:1px solid var(--mp-line); }
  td { padding:9px 4px; border-bottom:1px solid var(--mp-line); }
  .btn { display:inline-block; background:var(--mp-primary); color:#fff; border:0; border-radius:10px; padding:10px 16px; font-size:14px; font-weight:600; text-decoration:none; cursor:pointer; }
  .btn.ghost { background:#eef1f8; color:var(--mp-ink); }
  .btn.danger { background:var(--mp-bad); }
  input,select,textarea { width:100%; padding:10px 12px; border:1px solid var(--mp-line); border-radius:10px; font-size:14px; font-family:inherit; }
  label { font-size:12px; font-weight:600; color:var(--mp-muted); display:block; margin:10px 0 4px; }
  .nav { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px; }
  .nav a { font-size:12px; font-weight:600; color:var(--mp-primary); text-decoration:none; background:#fff; border:1px solid var(--mp-line); padding:7px 12px; border-radius:999px; }
  .badge { font-size:11px; font-weight:700; padding:2px 10px; border-radius:999px; }
  .b-ok { background:#e6f7f0; color:var(--mp-good); } .b-warn { background:#fdf3e4; color:var(--mp-warn); } .b-bad { background:#fdeaea; color:var(--mp-bad); } .b-mut { background:#eef1f8; color:var(--mp-muted); }
  .muted { color:var(--mp-muted); font-size:13px; }
  .flash { background:#e8f0ff; border:1px solid #c5d7ff; color:#1e3a8a; border-radius:10px; padding:10px 14px; font-size:13px; margin-bottom:14px; }
  .err { background:#fdeaea; border:1px solid #f5c5c5; color:var(--mp-bad); border-radius:10px; padding:10px 14px; font-size:13px; margin-bottom:14px; }
  .bal { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .bal .cell { background:var(--mp-bg); border-radius:10px; padding:10px 12px; }
  .bal .n { font-size:18px; font-weight:700; }
  .bal .l { font-size:11px; color:var(--mp-muted); text-transform:uppercase; letter-spacing:.4px; }
  .foot { text-align:center; color:var(--mp-muted); font-size:11px; padding:24px 0 0; }
</style>
</head>
<body>
<div class="wrap">
  <div class="topbar">
    <div>
      <div class="store-name"><?= $_store ?></div>
      <h1><?= isset($title) ? htmlspecialchars($title) : 'Patient Portal' ?></h1>
    </div>
    <?php if($_p): ?><a class="btn ghost" href="<?= site_url('portal/logout') ?>">Sign out</a><?php endif; ?>
  </div>
  <?php if($_p): ?>
  <div class="nav">
    <a href="<?= site_url('portal/home') ?>">Home</a>
    <?php if($this->portal->proxyCan('appointments',$p)): ?><a href="<?= site_url('portal/appointments') ?>">Appointments</a><?php endif; ?>
    <?php if($this->portal->proxyCan('progress',$p)): ?><a href="<?= site_url('portal/progress') ?>">Progress</a><?php endif; ?>
    <?php if($this->portal->proxyCan('bills',$p)): ?><a href="<?= site_url('portal/bills') ?>">Bills</a><?php endif; ?>
    <?php if($this->portal->proxyCan('funds',$p)): ?><a href="<?= site_url('portal/funds') ?>">Funds</a><?php endif; ?>
    <?php if($this->portal->proxyCan('documents',$p)): ?><a href="<?= site_url('portal/documents') ?>">Documents</a><?php endif; ?>
    <?php if($this->portal->proxyCan('feedback',$p)): ?><a href="<?= site_url('portal/feedback') ?>">Feedback</a><?php endif; ?>
    <a href="<?= site_url('portal/testimonials') ?>">Testimonials</a>
  </div>
  <?php endif; ?>
  <?php if($this->session->flashdata('portal_msg')): ?>
    <div class="flash"><?= htmlspecialchars($this->session->flashdata('portal_msg')) ?></div>
  <?php endif; ?>
