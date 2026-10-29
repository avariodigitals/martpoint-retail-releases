<?php
$workspace_key='sms';require __DIR__.'/_context.php';
$workspace_name='SMS Studio';$workspace_symbol='S';
$workspace_menu=array('dashboard'=>'Overview','campaigns'=>'Campaigns','report'=>'Results','compose'=>'New SMS campaign','templates'=>'SMS templates','audience'=>'Phone book','sender'=>'Sender ID','history'=>'Credit usage & logs','recharge'=>'Recharge','subscription'=>'Subscription','requests'=>'Payment requests');
$sections=array(
 'dashboard'=>array('Reach customers with SMS','Create campaigns, manage phone numbers and track every SMS credit.'),
 'campaigns'=>array('SMS campaigns','Your drafts, queued campaigns and sending results.'),
 'report'=>array('Campaign results','Who received each text, what the network answered and what is still queued.'),
 'compose'=>array('Create an SMS campaign','Choose your audience, write your message and preview it before saving.'),
 'templates'=>array('SMS templates','Pick a starting point, personalize it and choose when to send.'),
 'sender'=>array('SMS Sender ID','View your shared sender or request a business Sender ID when your Administrator enables it.'),
 'audience'=>array('Phone book','Import and manage the audience available to your SMS campaigns.'),
 'history'=>array('SMS usage & logs','See how SMS credits were reserved, used or returned.'),
 'recharge'=>array('Recharge SMS credits','Choose a recharge amount or enter a custom amount.'),
 'subscription'=>array('SMS subscription','Manage access to SMS independently from your message credits.'),
 'requests'=>array('SMS payment requests','Track subscription approvals, bank requests and card payments.'));
if(!isset($sections[$workspace_section]))$workspace_section='dashboard';
list($section_title,$section_description)=$sections[$workspace_section];$section_action=in_array($workspace_section,array('dashboard','campaigns'),true)?array('compose','+ New SMS campaign'):null;
$this->load->view('communications/_frame_start',get_defined_vars());
?>
<?php if($workspace_section==='dashboard'): ?>
<div class="comm-grid four"><section class="comm-card"><span class="muted">SMS credits</span><div class="metric"><?= $wallet?number_format($wallet['balance_units']):'—'; ?></div><a href="<?= $h($workspace_url('recharge')); ?>">Recharge →</a></section><section class="comm-card"><span class="muted">Campaigns</span><div class="metric"><?= count($campaigns); ?></div><span class="muted"><?= (int)$campaign_totals['drafts']; ?> drafts</span></section><section class="comm-card"><span class="muted">Accepted recipients</span><div class="metric"><?= number_format($campaign_totals['accepted']); ?></div><span class="muted">Provider acceptance</span></section><section class="comm-card"><span class="muted">Customer audience</span><div class="metric"><?= number_format($contact_count??0); ?></div><a href="<?= $h($workspace_url('audience')); ?>">Import numbers →</a></section></div>
<section class="comm-hero"><div><h2>A direct line to your customers</h2><p>Send announcements, offers and customer updates. Import your phone list or use a customer segment, then confirm the credit estimate before sending.</p><div class="row"><a class="btn" href="<?= $h($workspace_url('compose')); ?>">Write an SMS</a><a class="btn secondary" href="<?= $h($workspace_url('audience')); ?>">Import phone numbers</a></div></div><div class="comm-hero-mark" aria-hidden="true">SMS</div></section>
<?php $campaign_table_title='Recent SMS campaigns';$this->load->view('communications/_campaign_table',get_defined_vars()); ?>
<?php elseif($workspace_section==='campaigns'): ?>
<div class="comm-toolbar" aria-label="Filter SMS campaigns"><button type="button" class="active" data-campaign-filter="all">All campaigns</button><button type="button" data-campaign-filter="draft">Drafts</button><button type="button" data-campaign-filter="queued">Queued / partial</button><button type="button" data-campaign-filter="sent">Sent</button></div>
<?php $campaign_table_title='All SMS campaigns';$this->load->view('communications/_campaign_table',get_defined_vars()); ?>
<?php elseif($workspace_section==='compose'): ?>
<?php if(!$local_ready): ?><div class="notice">This workspace is waiting for an application update. Contact your administrator.</div><?php else:$this->load->view('communications/_sms_compose',get_defined_vars());endif; ?>
<?php elseif($workspace_section==='templates'):$this->load->view('communications/_sms_templates',get_defined_vars()); ?>
<?php elseif($workspace_section==='sender'):$this->load->view('communications/_sender',get_defined_vars()); ?>
<?php elseif($workspace_section==='audience'): ?>
<?php $this->load->view('communications/_audience',get_defined_vars()); ?>
<?php elseif($workspace_section==='report'):$this->load->view('communications/_report',get_defined_vars()); ?>
<?php elseif($workspace_section==='history'):$this->load->view('communications/_history',get_defined_vars()); ?>
<?php elseif($workspace_section==='recharge'):$this->load->view('communications/_recharge',get_defined_vars()); ?>
<?php elseif($workspace_section==='subscription'):$this->load->view('communications/_subscription',get_defined_vars()); ?>
<?php elseif($workspace_section==='requests'):$this->load->view('communications/_requests',get_defined_vars());endif; ?>
<?php $this->load->view('communications/_frame_end'); ?>
