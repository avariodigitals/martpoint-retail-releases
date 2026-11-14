<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_food_express_theme extends CI_Migration {

	public function up() {
		// Update Food Express theme default palette to match makan design
		$this->db->where('theme_key', 'food_express')
			->update('db_storefront_themes', [
				'default_primary_color'   => '#1E6B40',  // deep pine green
				'default_secondary_color' => '#F7C948',  // mustard gold
				'default_font_family'     => 'Inter',
				'description'             => 'Modern food ordering theme with bold hero search, circular category tiles, spice-level picker, and real-time delivery tracking.',
				'theme_version'           => '2.0.0',
			]);

		// If a store is already using food_express and hasn't customized colours,
		// reset their stored colours so the new theme palette takes effect.
		// We only touch stores where the stored values equal the OLD defaults.
		$oldPrimary   = '#D32F2F';
		$oldSecondary = '#FBC02D';
		$this->db->where('theme_id', (int)$this->db->select('id')->where('theme_key','food_express')->get('db_storefront_themes')->row()->id ?? 0)
			->where('(primary_color = ? OR primary_color IS NULL)', $oldPrimary, FALSE)
			->where('(secondary_color = ? OR secondary_color IS NULL)', $oldSecondary, FALSE)
			->update('db_storefront_settings', [
				'primary_color'   => NULL,
				'secondary_color' => NULL,
			]);
	}

	public function down() {
		// Revert to original colours
		$this->db->where('theme_key', 'food_express')
			->update('db_storefront_themes', [
				'default_primary_color'   => '#D32F2F',
				'default_secondary_color' => '#FBC02D',
				'default_font_family'     => 'Inter',
				'theme_version'           => '1.0.0',
			]);
	}
}