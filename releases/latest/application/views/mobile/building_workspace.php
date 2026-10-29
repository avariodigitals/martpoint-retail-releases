<!doctype html><html lang="en"><head>
<meta charset="utf-8">
<?php $this->load->view('comman/app_icons'); ?>
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
<link rel="stylesheet" href="<?= base_url('theme/css/font-awesome-4.7.0/css/font-awesome.min.css'); ?>">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap">
<?php foreach(['building_materials_workspace','building_projects','building_operations'] as $sheet): ?><link rel="stylesheet" href="<?= base_url('theme/dist/css/'.$sheet.'.css?v='.filemtime(FCPATH.'theme/dist/css/'.$sheet.'.css')); ?>"><?php endforeach; ?>
</head><body class="bm-workspace-shell bm-mobile">
<header class="bo-mobile-head"><div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div><h1><?= htmlspecialchars($page_title); ?></h1></header>
<?php $bmMobileCI =& get_instance(); ?>
<nav class="bo-mobile-nav" aria-label="Building Materials workspace"><?php if($bmMobileCI->permissions('dashboard_view')): ?><a href="<?= base_url('building_workspace/actions'); ?>">Actions</a><?php endif; ?><?php if($bmMobileCI->permissions('service_jobs_view')): ?><a href="<?= base_url('building_projects'); ?>">Projects</a><a href="<?= base_url('building_workspace/surveys'); ?>">Surveys</a><a href="<?= base_url('building_workspace/field'); ?>">Field teams</a><?php endif; ?><a href="<?= base_url('building_workspace/more'); ?>">More</a></nav>
<main><?= $content; ?></main>
<?php $this->load->view('mobile/bottom_nav',['active'=>$page_title === 'My attendance' ? 'attendance' : 'more']); ?>
<?php $this->load->view('building_materials/scripts',['building_mobile'=>true]); ?>
<?php $this->load->view('mobile/chat'); ?>
</body></html>
