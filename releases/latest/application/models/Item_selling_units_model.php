<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Item_selling_units_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	/**
	 * Get all selling units for an item.
	 * is_template=1 are parent product templates; is_template=0 are sellable units.
	 */
	public function get_units($item_id, $store_id = null, $is_template = 0){
		if(empty($store_id)) $store_id = get_current_store_id();
		$this->db->select('su.*, u.unit_name, u.shortcode');
		$this->db->from('db_item_selling_units su');
		$this->db->join('db_units u', 'u.id = su.unit_id', 'left');
		$this->db->where('su.item_id', $item_id);
		$this->db->where('su.store_id', $store_id);
		$this->db->where('su.status', 1);
		if($this->db->field_exists('is_template', 'db_item_selling_units')){
			$this->db->where('su.is_template', $is_template);
		}
		$this->db->order_by('su.is_default', 'desc');
		$this->db->order_by('su.id', 'asc');
		return $this->db->get()->result();
	}

	/**
	 * Save selling units from POST arrays for an item.
	 * Expected POST keys:
	 *   selling_unit_id[], selling_unit_unit_id[], selling_unit_conversion[],
	 *   selling_unit_selling_price[], selling_unit_purchase_price[],
	 *   selling_unit_sku[], selling_unit_barcode[], selling_unit_default[]
	 */
	public function save_units($item_id, $store_id = null, $is_template = 0){
		if(empty($store_id)) $store_id = get_current_store_id();
		if(!$item_id) return false;

		$unit_ids      = $this->input->post('selling_unit_unit_id', TRUE) ?: [];
		if(!is_array($unit_ids) || empty($unit_ids)){
			// No units provided, soft-delete existing for this item/template
			$this->db->where('item_id', $item_id)->where('store_id', $store_id);
			if($this->db->field_exists('is_template', 'db_item_selling_units')){
				$this->db->where('is_template', $is_template);
			}
			$this->db->update('db_item_selling_units', ['status' => 0]);
			return true;
		}

		$conversions    = $this->input->post('selling_unit_conversion', TRUE) ?: [];
		$selling_prices = $this->input->post('selling_unit_selling_price', TRUE) ?: [];
		$wholesale_prices=$this->input->post('selling_unit_wholesale_price', TRUE) ?: [];
		$purchase_prices= $this->input->post('selling_unit_purchase_price', TRUE) ?: [];
		$skus           = $this->input->post('selling_unit_sku', TRUE) ?: [];
		$barcodes       = $this->input->post('selling_unit_barcode', TRUE) ?: [];
		$default_index  = (int)($this->input->post('selling_unit_default', TRUE) ?: 0);

		// Soft-delete existing units, then re-save active rows
		$this->db->where('item_id', $item_id)->where('store_id', $store_id);
		if($this->db->field_exists('is_template', 'db_item_selling_units')){
			$this->db->where('is_template', $is_template);
		}
		$this->db->update('db_item_selling_units', ['status' => 0]);

		foreach($unit_ids as $i => $unit_id){
			$unit_id = (int)$unit_id;
			if($unit_id <= 0) continue;

			$conversion = !empty($conversions[$i]) ? (float)$conversions[$i] : 1;
			if($conversion <= 0) $conversion = 1;

			$selling_price = !empty($selling_prices[$i]) ? str_replace(',','',$selling_prices[$i]) : 0;
			$wholesale_price = !empty($wholesale_prices[$i]) ? str_replace(',','',$wholesale_prices[$i]) : null;
			$purchase_price = !empty($purchase_prices[$i]) ? str_replace(',','',$purchase_prices[$i]) : null;
			$sku = trim($skus[$i] ?? '');
			$barcode = trim($barcodes[$i] ?? '');
			$is_default = ($i === $default_index) ? 1 : 0;

			$unit = $this->db->select('unit_name, shortcode')->where('id', $unit_id)->get('db_units')->row();
			$unit_name = $unit ? $unit->unit_name : '';
			$shortcode = $unit && !empty($unit->shortcode) ? $unit->shortcode : $unit_name;

			$data = [
				'store_id'          => $store_id,
				'item_id'           => $item_id,
				'unit_id'           => $unit_id,
				'unit_shortcode'    => $shortcode,
				'conversion_factor' => $conversion,
				'selling_price'     => $selling_price,
				'wholesale_price'   => ($wholesale_price === null || $wholesale_price === '') ? null : $wholesale_price,
				'purchase_price'    => $purchase_price,
				'sku'               => $sku,
				'barcode'           => $barcode,
				'is_default'        => $is_default,
				'status'            => 1,
			];
			if($this->db->field_exists('is_template', 'db_item_selling_units')){
				$data['is_template'] = $is_template;
			}

			$existing = $this->db->where('item_id', $item_id)
								 ->where('unit_id', $unit_id)
								 ->where('store_id', $store_id);
			if($this->db->field_exists('is_template', 'db_item_selling_units')){
				$existing = $existing->where('is_template', $is_template);
			}
			$existing = $existing->get('db_item_selling_units')->row();
			if($existing){
				$this->db->where('id', $existing->id)->update('db_item_selling_units', $data);
			} else {
				$this->db->insert('db_item_selling_units', $data);
			}
		}

		return true;
	}

	/**
	 * Clone a parent's template selling units to a child variant.
	 * Child can have its own price; if not provided, parent template price is used.
	 */
	public function clone_template_to_child($parent_id, $child_id, $store_id = null){
		if(empty($store_id)) $store_id = get_current_store_id();
		if(empty($parent_id) || empty($child_id)) return false;
		if(!$this->db->field_exists('is_template', 'db_item_selling_units')) return true;

		$templates = $this->get_units($parent_id, $store_id, 1);
		if(empty($templates)) return true;

		// Soft-delete any existing child selling units before cloning
		$this->db->where('item_id', $child_id)->where('store_id', $store_id)->where('is_template', 0);
		$this->db->update('db_item_selling_units', ['status' => 0]);

		foreach($templates as $t){
			// Barcodes and SKUs are variant-specific; leave them blank so the user sets per child
			$clone = [
				'store_id'          => $store_id,
				'item_id'           => $child_id,
				'unit_id'           => $t->unit_id,
				'unit_shortcode'    => $t->unit_shortcode,
				'conversion_factor' => $t->conversion_factor,
				'selling_price'     => $t->selling_price,
				'wholesale_price'   => $t->wholesale_price,
				'purchase_price'    => $t->purchase_price,
				'sku'               => '',
				'barcode'           => '',
				'is_default'        => $t->is_default,
				'is_template'       => 0,
				'status'            => 1,
			];
			$this->db->insert('db_item_selling_units', $clone);
		}

		return true;
	}

	/**
	 * Find a selling unit by barcode for an item or any item in the store.
	 * Only returns real sellable units (not parent templates).
	 */
	public function find_by_barcode($barcode, $store_id = null, $item_id = null){
		if(empty($store_id)) $store_id = get_current_store_id();
		if(empty($barcode)) return null;
		$this->db->select('su.*, u.unit_name, u.shortcode');
		$this->db->from('db_item_selling_units su');
		$this->db->join('db_units u', 'u.id = su.unit_id', 'left');
		$this->db->where('su.barcode', $barcode);
		$this->db->where('su.store_id', $store_id);
		$this->db->where('su.status', 1);
		if($this->db->field_exists('is_template', 'db_item_selling_units')){
			$this->db->where('su.is_template', 0);
		}
		if(!empty($item_id)) $this->db->where('su.item_id', $item_id);
		return $this->db->get()->row();
	}

	/**
	 * Get the default selling unit for an item.
	 */
	public function get_default_unit($item_id, $store_id = null){
		$units = $this->get_units($item_id, $store_id, 0);
		return $units ? $units[0] : null;
	}

	/**
	 * Convert a selling quantity into base quantity.
	 */
	public static function to_base_qty($qty, $conversion_factor){
		return (float)$qty * (float)$conversion_factor;
	}
}
