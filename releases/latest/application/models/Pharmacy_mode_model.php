<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pharmacy_mode_model extends CI_Model {
    public function set_mode($storeId, $mode) {
        if (!in_array($mode, ['standard', 'advanced'], true)) throw new InvalidArgumentException('Choose Standard Pharmacy or Advanced Pharmacy.');
        $storeId = (int)$storeId;
        if ($storeId < 1 || !mp_pharmacy_industry(mp_get_store_profile($storeId)['industry_type'] ?? '')) throw new InvalidArgumentException('This setting is available only to pharmacies.');
        if (!$this->db->field_exists('pharmacy_mode', 'db_store')) throw new RuntimeException('Apply the pharmacy mode database update before changing this setting.');
        // Deliberately update only the mode: no seeders, stock movement or profile reset.
        $ok = $this->db->where('id', $storeId)->update('db_store', ['pharmacy_mode' => $mode]);
        if ($ok) mp_pharmacy_stored_mode($storeId, true);
        return $ok;
    }
}
