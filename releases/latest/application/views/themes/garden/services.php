<section class="mp-section"><form method="get" action="<?= base_url('store/'.$settings->store_slug.'/services'); ?>" class="garden-store-search"><input type="search" name="search" aria-label="Search services" placeholder="Search services" value="<?= htmlspecialchars($search ?? ''); ?>"><button>Search</button></form></section>
<?php $this->load->view('themes/garden/service_cards',['services'=>$services ?? []]); ?>
<?php if (empty($services)): ?><p class="mp-section">No matching services are available.</p><?php endif; ?>
<?php if (($total_pages ?? 0)>1): ?><nav class="mp-section" aria-label="Service pages">
<?php if (($page ?? 1)>1): ?><a href="<?= base_url('store/'.$settings->store_slug.'/services').'?'.http_build_query(['page'=>$page-1,'search'=>$search ?? '','category'=>$category_id ?? 0]); ?>">Previous</a><?php endif; ?>
<span>Page <?= (int)$page; ?> of <?= (int)$total_pages; ?></span>
<?php if ($page<$total_pages): ?><a href="<?= base_url('store/'.$settings->store_slug.'/services').'?'.http_build_query(['page'=>$page+1,'search'=>$search ?? '','category'=>$category_id ?? 0]); ?>">Next</a><?php endif; ?></nav><?php endif; ?>
