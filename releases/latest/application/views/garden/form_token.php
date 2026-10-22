<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
<input type="hidden" name="screen" value="<?= $this->input->get('screen') === 'mobile' ? 'mobile' : 'desktop'; ?>">
