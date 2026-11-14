<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_auto_themes extends CI_Migration {

    public function up() {
        if ($this->db->table_exists('db_storefront_themes')) {
            $themes = [
                ['theme_key' => 'auto_modern', 'theme_name' => 'Auto Modern', 'industry' => 'automotive', 'description' => 'Clean, world-class car dealership theme with a blue and white hero, fast minified images, mobile-first grids and WhatsApp leads.', 'default_primary_color' => '#2563EB', 'default_secondary_color' => '#0B1220', 'default_font_family' => 'Inter', 'sort_order' => 26, 'status' => 1],
                ['theme_key' => 'auto_luxe', 'theme_name' => 'Auto Luxe', 'industry' => 'automotive', 'description' => 'Dark, premium luxury vehicle theme with gold accents, dramatic hero, and a premium buying experience.', 'default_primary_color' => '#C9A961', 'default_secondary_color' => '#0B0F1A', 'default_font_family' => 'Inter', 'sort_order' => 27, 'status' => 1],
                ['theme_key' => 'auto_garage', 'theme_name' => 'Auto Garage', 'industry' => 'automotive', 'description' => 'Rugged, high-energy auto theme for trucks, SUVs and performance vehicles with bold red and charcoal styling.', 'default_primary_color' => '#DC2626', 'default_secondary_color' => '#1F2937', 'default_font_family' => 'Inter', 'sort_order' => 28, 'status' => 1],
            ];
            foreach ($themes as $t) {
                $sql = $this->db->insert_string('db_storefront_themes', $t);
                $sql = preg_replace('/^INSERT INTO/i', 'INSERT IGNORE INTO', $sql);
                $this->db->query($sql);
            }
        }
    }

    public function down() {
        if ($this->db->table_exists('db_storefront_themes')) {
            $this->db->where_in('theme_key', ['auto_modern','auto_luxe','auto_garage'])->delete('db_storefront_themes');
        }
    }
}
