<?php if ($this->session->userdata('logged_in') && !defined('MP_USAGE_CHECKIN_LOADED')): ?>
<?php define('MP_USAGE_CHECKIN_LOADED', true); ?>
<script src="<?= htmlspecialchars(base_url('theme/js/usage-checkin.js'), ENT_QUOTES, 'UTF-8'); ?>"
        data-endpoint="<?= htmlspecialchars(site_url('usage/ping'), ENT_QUOTES, 'UTF-8'); ?>"
        data-csrf-name="<?= htmlspecialchars($this->security->get_csrf_token_name(), ENT_QUOTES, 'UTF-8'); ?>"
        data-csrf-value="<?= htmlspecialchars($this->security->get_csrf_hash(), ENT_QUOTES, 'UTF-8'); ?>" defer></script>
<?php endif; ?>
