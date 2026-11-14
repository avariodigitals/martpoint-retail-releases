<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Customer rules are transaction prices; they never rewrite the catalogue. */
class Customer_pricing_model extends CI_Model {
    private $policies = [];

    public function installed() {
        return $this->db->table_exists('db_customer_pricing_groups')
            && $this->db->field_exists('pricing_group_id', 'db_customers');
    }

    public function groups($store) {
        return $this->installed() ? $this->db->where('store_id', (int)$store)
            ->order_by('name')->get('db_customer_pricing_groups')->result() : [];
    }

    public static function valid_percent($direction, $value) {
        return in_array($direction, ['Increase', 'Decrease'], true) && is_scalar($value)
            && is_numeric($value) && is_finite((float)$value) && (float)$value >= 0
            && (float)$value <= ($direction === 'Decrease' ? 100 : 10000);
    }

    public static function adjust($base, $direction, $percent, $decimals = 2) {
        if (!is_numeric($base) || !is_finite((float)$base) || (float)$base < 0
            || !self::valid_percent($direction, $percent)) throw new RuntimeException('Invalid customer price rule.');
        return round((float)$base * (1 + ($direction === 'Decrease' ? -1 : 1) * (float)$percent / 100), $decimals);
    }

    public function policy($store, $customer) {
        $key = (int)$store . ':' . (int)$customer;
        if (isset($this->policies[$key])) return $this->policies[$key];
        $p = ['source'=>'normal', 'group_id'=>null, 'name'=>'', 'direction'=>'Increase', 'percent'=>0,
            'decimals'=>max(0, min(4, (int)mp_get_store_receipt_setting($store, 'decimals', 2)))];
        $c = $customer ? $this->db->where('store_id', (int)$store)->where('id', (int)$customer)
            ->where('status', 1)->get('db_customers')->row() : null;
        if ($c) {
            // NULL preserves legacy nonzero overrides. An explicit "custom" supports 0%.
            $mode = $c->pricing_rule_mode ?? null;
            if ($mode === 'custom' || ($mode === null && (float)($c->price_level ?? 0) != 0)) {
                $p['source'] = 'customer';
                $p['name'] = 'Customer adjustment';
                $p['direction'] = $c->price_level_type === 'Decrease' ? 'Decrease' : 'Increase';
                $p['percent'] = (float)$c->price_level;
            } elseif ($this->installed() && !empty($c->pricing_group_id)) {
                $g = $this->db->where('id', (int)$c->pricing_group_id)->where('store_id', (int)$store)
                    ->where('active', 1)->get('db_customer_pricing_groups')->row();
                if ($g) {
                    $p['source'] = 'group'; $p['group_id'] = (int)$g->id; $p['name'] = $g->name;
                    $p['direction'] = $g->direction; $p['percent'] = (float)$g->percent;
                }
            }
        }
        $p['version'] = hash('sha256', json_encode($p));
        return $this->policies[$key] = $p;
    }

    public function assignment($store, $input) {
        if (!$this->installed()) {
            if (!empty($input['pricing_group_id'])) throw new RuntimeException('Apply the customer pricing update before assigning a group.');
            return [];
        }
        if (!array_key_exists('pricing_group_id', $input) && !array_key_exists('pricing_rule_mode', $input)) return [];
        $group = (int)($input['pricing_group_id'] ?? 0);
        $mode = $input['pricing_rule_mode'] ?? 'inherit';
        if (!in_array($mode, ['inherit', 'custom'], true)) throw new RuntimeException('Choose a valid customer pricing mode.');
        if ($group && !$this->db->where('id', $group)->where('store_id', (int)$store)
                ->get('db_customer_pricing_groups')->row()) throw new RuntimeException('Pricing group does not belong to this store.');
        if ($mode === 'custom' && !self::valid_percent($input['price_level_type'] ?? '', $input['price_level'] ?? '')) {
            throw new RuntimeException('Enter a valid customer percentage (decrease up to 100%).');
        }
        return ['pricing_group_id'=>$group ?: null, 'pricing_rule_mode'=>$mode];
    }

    /** Resolve the actual unit/batch Wholesale base. Missing bases fail explicitly. */
    public function quote($store, $customer, $item, $tier = 'retail', $unit = 0, $barcode = 0) {
        $p = $this->policy($store, $customer);
        $row = $this->db->where('id', (int)$item)->where('store_id', (int)$store)->get('db_items')->row();
        if (!$row) throw new RuntimeException('Pricing item does not belong to this store.');
        // Fixed bundles have a separate component/fixed-price engine.
        if (!empty($row->is_bundle) && $p['source'] === 'group') $p = array_merge($p, ['source'=>'normal', 'name'=>'Bundle price', 'group_id'=>null]);
        $wholesale = $row->sales_price; $retail = $row->mrp;
        if ($unit) {
            $u = $this->db->where('item_id', (int)$item)->where('store_id', (int)$store)
                ->where('unit_id', (int)$unit)->where('status', 1)->get('db_item_selling_units')->row();
            if (!$u) throw new RuntimeException('The selected selling unit is unavailable.');
            $wholesale = $u->wholesale_price; $retail = $u->selling_price;
        } elseif ($barcode) {
            $b = $this->db->where('id', (int)$barcode)->where('item_id', (int)$item)->get('db_item_barcodes')->row();
            if (!$b) throw new RuntimeException('The selected batch does not belong to this item.');
            $wholesale = $b->sales_price; $retail = $b->mrp;
        }
        $base = $p['source'] === 'group' || $tier === 'wholesale' ? $wholesale : ((float)$retail > 0 ? $retail : $wholesale);
        if ($p['source'] === 'group' && ($base === null || $base === '' || !is_numeric($base) || (float)$base <= 0)) {
            throw new RuntimeException('Set a Wholesale price for this item/unit before using its customer group price.');
        }
        $price = $p['source'] === 'normal' ? (float)$base : self::adjust($base, $p['direction'], $p['percent'], $p['decimals']);
        return array_merge($p, ['base'=>(float)$base, 'price'=>$price,
            'requested_tier'=>$tier === 'wholesale' ? 'wholesale' : 'retail',
            'effective_tier'=>$p['source'] === 'group' ? 'wholesale' : ($tier === 'wholesale' ? 'wholesale' : 'retail')]);
    }

    public function save_group($store, array $input) {
        if (!$this->installed()) throw new RuntimeException('Apply migration 4.0.9.239 before creating groups.');
        $id = (int)($input['id'] ?? 0); $name = trim((string)($input['name'] ?? ''));
        if ($name === '' || mb_strlen($name) > 100) throw new RuntimeException('Enter a group name of 1–100 characters.');
        if (!self::valid_percent($input['direction'] ?? '', $input['percent'] ?? '')) throw new RuntimeException('Enter a valid percentage (decrease up to 100%).');
        if ($id && !$this->db->where('store_id', (int)$store)->where('id', $id)->get('db_customer_pricing_groups')->row()) throw new RuntimeException('Group not found.');
        $duplicate = $this->db->where('store_id', (int)$store)->where('name', $name);
        if ($id) $duplicate->where('id !=', $id);
        if ($duplicate->get('db_customer_pricing_groups')->row()) throw new RuntimeException('A group with that name already exists.');
        $data = ['name'=>$name, 'direction'=>$input['direction'], 'percent'=>(float)$input['percent'],
            'active'=>!empty($input['active']) ? 1 : 0, 'updated_at'=>date('Y-m-d H:i:s')];
        $ok = $id ? $this->db->where('store_id', (int)$store)->where('id', $id)->update('db_customer_pricing_groups', $data)
            : $this->db->insert('db_customer_pricing_groups', $data + ['store_id'=>(int)$store]);
        $this->policies = [];
        if (!$ok) throw new RuntimeException('Could not save the group.');
    }

    /** Validate a new line; an old saved snapshot is preserved by the caller on edits. */
    public function validate_line($store, $customer, $item, $tier, $unit, $barcode, $basePrice, $submitted = null) {
        $p = $this->policy($store, $customer);
        $old = is_array($submitted) ? $submitted : json_decode((string)$submitted, true);
        // The till echoes the customer's whole policy on every line, so 'normal'
        // arrives even when no rule exists. That answer claims no price rule: it
        // must not be enforced as a quote, or every ordinary sale is refused.
        if (is_array($old) && ($old['source'] ?? 'normal') === 'normal') $old = null;
        if (!in_array($p['source'], ['group', 'customer'], true) && empty($old['group_id']) && empty($old['source'])) return null;
        $q = $this->quote($store, $customer, $item, $tier, $unit, $barcode);
        if ($q['source'] === 'normal' && empty($old['group_id']) && empty($old['source'])) return null;
        if (!in_array($q['source'], ['group', 'customer'], true)
            || (!empty($old['version']) && $old['version'] !== $q['version'])
            || abs((float)$basePrice - $q['price']) > max(0.00001, pow(10, -$q['decimals']) / 2)) {
            throw new RuntimeException('Customer pricing changed or the price does not match. Refresh this cart and review the total before payment.');
        }
        return json_encode($q);
    }

    public function history($table, $document, $id, $store, $customer) {
        if (!$id) return [];
        $header = $this->db->where('id', (int)$id)->where('store_id', (int)$store)
            ->where('customer_id', (int)$customer)->get($document)->row();
        if (!$header) return [];
        $foreign = $document === 'db_sales' ? 'sales_id' : 'quotation_id';
        return $this->db->where($foreign, (int)$id)->get($table)->result();
    }

    public function line_snapshot($store, $customer, $item, $tier, $unit, $barcode, $price, $submitted, array &$history) {
        foreach ($history as $key=>$old) {
            if ((int)$old->item_id !== (int)$item || (int)($old->unit_id ?? 0) !== (int)$unit
                || (int)($old->barcode_id ?? 0) !== (int)$barcode) continue;
            $snapshot = json_decode((string)($old->pricing_snapshot ?? ''), true);
            $original = $snapshot['price'] ?? $old->price_per_unit;
            if (abs((float)$price - (float)$original) > 0.00001) continue;
            unset($history[$key]);
            return $old->pricing_snapshot ?? null;
        }
        return $this->validate_line($store, $customer, $item, $tier, $unit, $barcode, $price, $submitted);
    }

    /** Reject a stale quote instead of silently altering a paid/offline sale. */
    public function validate_cart($store, $customer, array $cart, $coupon = '') {
        if ($coupon !== '' && $this->policy($store, $customer)['source'] === 'group') {
            throw new RuntimeException('Automatic coupons do not stack with customer group prices. Remove the coupon before continuing.');
        }
        foreach ($cart as &$line) {
            $snapshot = $this->validate_line($store, $customer, $line['id'] ?? 0, $line['price_type'] ?? 'retail',
                $line['unit_id'] ?? 0, $line['barcode_id'] ?? 0, $line['base_price'] ?? $line['price'] ?? 0, $line['pricing_snapshot'] ?? null);
            if ($snapshot) { $line['pricing_snapshot'] = $snapshot; $line['price_type'] = 'wholesale'; }
        }
        unset($line);
        return $cart;
    }
}
