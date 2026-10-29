<?php
$workspace_key='email';require __DIR__.'/_context.php';
$workspace_name='Email Studio';$workspace_symbol='@';
$workspace_menu=array('dashboard'=>'Dashboard','campaigns'=>'Email campaigns','report'=>'Results','templates'=>'Email designs','compose'=>'Create email','audience'=>'Audience','sender'=>'Sender identity','history'=>'Sending & credit logs','recharge'=>'Buy email credits','subscription'=>'Subscription','requests'=>'Payment requests');
$sections=array(
 'dashboard'=>array('Your email dashboard','Design campaigns and keep customer emails moving from one email workspace.'),
 'campaigns'=>array('Email campaigns','Manage drafts and review campaign results.'),
 'report'=>array('Campaign results','See who received your email, who read it and which links they opened.'),
 'templates'=>array('Your email designs','Browse your designs and the MartPoint library, preview how each one sends, and make any of them your own.'),
 'build'=>array('Design builder','Build a reusable email layout from blocks — headings, highlights, buttons and fine print.'),
 'compose'=>array('Create an email','Build your message with a subject, design, personalization and optional button.'),
 'audience'=>array('Your email audience','Import email contacts or reach customers using a saved customer segment.'),
 'sender'=>array('Your sender details','Choose the name customers see in their inbox and where their replies reach you.'),
 'history'=>array('Sending & credit logs','Campaigns and transactional emails use the same email credit balance.'),
 'recharge'=>array('Buy email credits','Recharge the balance used by campaigns, invoices and customer updates.'),
 'subscription'=>array('Email subscription','Manage your email plan, expiry and module preference.'),
 'requests'=>array('Email payment requests','Follow the status of email subscription and credit payments.'));
if(!isset($sections[$workspace_section]))$workspace_section='dashboard';list($section_title,$section_description)=$sections[$workspace_section];
$section_action=null;
if(in_array($workspace_section,array('dashboard','campaigns'),true))$section_action=array('templates','+ Browse designs');
elseif($workspace_section==='templates')$section_action=array('build','+ New design');
$this->load->view('communications/_frame_start',get_defined_vars());
?>
<?php if($workspace_section==='dashboard'): ?>
<div class="comm-grid four"><section class="comm-card"><span class="muted">Email credits</span><div class="metric"><?= $wallet?number_format($wallet['balance_units']):'—'; ?></div><a href="<?= $h($workspace_url('recharge')); ?>">Buy credits →</a></section><section class="comm-card"><span class="muted">Accepted recipients</span><div class="metric"><?= number_format($campaign_totals['accepted']); ?></div><span class="muted">Across email campaigns</span></section><section class="comm-card"><span class="muted">Campaign drafts</span><div class="metric"><?= (int)$campaign_totals['drafts']; ?></div><a href="<?= $h($workspace_url('campaigns')); ?>">Continue your work →</a></section><section class="comm-card"><span class="muted">Customer audience</span><div class="metric"><?= number_format($contact_count??0); ?></div><a href="<?= $h($workspace_url('audience')); ?>">Manage audience →</a></section></div>
<section class="comm-hero"><div><h2>Good emails start with a clear message</h2><p>Choose a ready-made design, add your content and see how it will look. Email campaigns and invoices share your credits, so you can track everything in one place.</p><div class="row"><a class="btn" href="<?= $h($workspace_url('templates')); ?>">Browse email templates</a><a class="btn secondary" href="<?= $h($workspace_url('compose')); ?>">Start a personal letter</a></div></div><div class="comm-hero-mark" aria-hidden="true">@</div></section>
<?php $campaign_table_title='Recent email campaigns';$this->load->view('communications/_campaign_table',get_defined_vars()); ?>
<?php elseif($workspace_section==='templates'):$this->load->view('communications/_email_templates',get_defined_vars()); ?>
<?php elseif($workspace_section==='build'):if(empty($designs_saved)): ?><div class="notice">Your email designs library needs an application update. Contact your administrator.</div><?php else:$this->load->view('communications/_email_builder',get_defined_vars());endif; ?>
<?php elseif($workspace_section==='compose'):if(!$local_ready): ?><div class="notice">This workspace is waiting for an application update. Contact your administrator.</div><?php else:$this->load->view('communications/_email_compose',get_defined_vars());endif; ?>
<?php elseif($workspace_section==='campaigns'): ?>
<div class="comm-toolbar" aria-label="Filter email campaigns"><button type="button" class="active" data-campaign-filter="all">All emails</button><button type="button" data-campaign-filter="draft">Drafts</button><button type="button" data-campaign-filter="queued">Queued / partial</button><button type="button" data-campaign-filter="sent">Sent</button></div>
<?php $campaign_table_title='All email campaigns';$this->load->view('communications/_campaign_table',get_defined_vars()); ?>
<?php elseif($workspace_section==='audience'):$this->load->view('communications/_audience',get_defined_vars()); ?>
<?php elseif($workspace_section==='report'):$this->load->view('communications/_report',get_defined_vars()); ?>
<?php elseif($workspace_section==='sender'):$this->load->view('communications/_email_sender',get_defined_vars()); ?>
<?php elseif($workspace_section==='history'):$this->load->view('communications/_history',get_defined_vars()); ?>
<?php elseif($workspace_section==='recharge'):$this->load->view('communications/_recharge',get_defined_vars()); ?>
<?php elseif($workspace_section==='subscription'):$this->load->view('communications/_subscription',get_defined_vars()); ?>
<?php elseif($workspace_section==='requests'):$this->load->view('communications/_requests',get_defined_vars());endif; ?>
<?php $this->load->view('communications/_frame_end'); ?>
