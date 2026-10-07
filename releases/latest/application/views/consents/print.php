<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Consent — <?= htmlspecialchars($patient->customer_name ?? ''); ?></title>
<style>
body{font-family:Georgia,serif;max-width:700px;margin:40px auto;color:#111;}
h1{font-size:20px;border-bottom:2px solid #111;padding-bottom:8px;}
.meta{font-size:13px;color:#444;margin-bottom:20px;}
.body{white-space:pre-line;font-size:14px;line-height:1.7;}
.sig{margin-top:50px;}
@media print{ .noprint{display:none;} }
</style>
</head>
<body>
<button class="noprint" onclick="window.print()">Print</button>
<div class="body"><?= htmlspecialchars($row->body_text); ?></div>
<p style="font-size:11px;color:#666;margin-top:30px;">Consent record #<?= (int)$row->id; ?> · <?= htmlspecialchars(mp_code_label($row->consent_type)); ?> · generated <?= $row->created_date; ?></p>
</body>
</html>
