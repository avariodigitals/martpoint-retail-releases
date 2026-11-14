<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seed the branded storefront order/appointment email templates for every
 * store. Templates the merchant has customized are left untouched.
 */
class Migration_Branded_order_appointment_emails extends CI_Migration {

	public function up() {
		if (!$this->db->table_exists('db_email_templates') || !$this->db->table_exists('db_store')) return;

		$this->load->model('email_template_model');
		$stores = $this->db->select('id')->get('db_store')->result();
		foreach ($stores as $store) {
			$this->email_template_model->seedDefaults((int)$store->id);
		}
	}

	public function down() {
	}
}
