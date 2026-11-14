<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_parfum_themes extends CI_Migration {

    public function up() {
        if ($this->db->table_exists('db_storefront_themes')) {
            $themes = [
                ['theme_key' => 'noir_parfum', 'theme_name' => 'Noir Parfum', 'industry' => 'perfumery', 'description' => 'Midnight luxury flagship theme — deep black, champagne gold and italic serif typography for a dramatic haute-parfumerie storefront.', 'default_primary_color' => '#C9A961', 'default_secondary_color' => '#0B0A08', 'default_font_family' => 'Cormorant Garamond', 'sort_order' => 32, 'status' => 1],
                ['theme_key' => 'maison_blanche', 'theme_name' => 'Maison Blanche', 'industry' => 'perfumery', 'description' => 'Ivory Parisian maison theme — cream canvas, black ink and old-gold hairlines for a refined French fragrance boutique.', 'default_primary_color' => '#A98954', 'default_secondary_color' => '#1C1917', 'default_font_family' => 'Playfair Display', 'sort_order' => 33, 'status' => 1],
                ['theme_key' => 'oud_royale', 'theme_name' => 'Oud Royale', 'industry' => 'perfumery', 'description' => 'Arabian opulence theme — espresso darkness, royal gold and arched gallery for oud, attar and musk houses.', 'default_primary_color' => '#D4A24E', 'default_secondary_color' => '#150E07', 'default_font_family' => 'Marcellus', 'sort_order' => 34, 'status' => 1],
                ['theme_key' => 'atelier_essence', 'theme_name' => 'Atelier Essence', 'industry' => 'perfumery', 'description' => 'Niche-lab minimalism — bone white, mono ink and stark grid for artisan perfumeries and custom formulation labs.', 'default_primary_color' => '#161513', 'default_secondary_color' => '#9C4A2F', 'default_font_family' => 'Inter', 'sort_order' => 35, 'status' => 1],
            ];
            foreach ($themes as $t) {
                $sql = $this->db->insert_string('db_storefront_themes', $t);
                $sql = preg_replace('/^INSERT INTO/i', 'INSERT IGNORE INTO', $sql);
                $this->db->query($sql);
            }
        }

        // Repoint perfume-shop businesses to the dedicated perfumery theme group.
        foreach (['db_store_business_profile', 'db_store_industry_settings'] as $table) {
            if ($this->db->table_exists($table)) {
                $this->db->where('industry_type', 'perfume_shop')
                    ->group_start()
                        ->where('storefront_theme_key', null)
                        ->or_where('storefront_theme_key', '')
                        ->or_where('storefront_theme_key', 'beauty_luxe')
                    ->group_end()
                    ->update($table, ['storefront_theme_key' => 'noir_parfum']);
            }
        }
    }

    public function down() {
        if ($this->db->table_exists('db_storefront_themes')) {
            $this->db->where_in('theme_key', ['noir_parfum','maison_blanche','oud_royale','atelier_essence'])->delete('db_storefront_themes');
        }
    }
}
