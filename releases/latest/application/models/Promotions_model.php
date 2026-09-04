<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promotions_model extends CI_Model {

	var $table = 'db_promotions as a';
	var $column_order = array('a.id','a.promotion_name','a.promotion_code','a.discount_type','a.discount_value','a.start_date','a.end_date','a.status');
	var $column_search = array('a.promotion_name','a.promotion_code','a.description');
	var $order = array('a.id' => 'desc');

	public function __construct(){
		parent::__construct();
	}

	private function _get_datatables_query(){
		$this->db->select('a.*');
		$this->db->from($this->table);
		$this->db->where('a.store_id', get_current_store_id());

		$i = 0;
		foreach($this->column_search as $item){
			if(isset($_POST['search']['value']) && !empty($_POST['search']['value'])){
				if($i===0){
					$this->db->group_start();
					$this->db->like($item, $_POST['search']['value']);
				} else {
					$this->db->or_like($item, $_POST['search']['value']);
				}
				if(count($this->column_search) - 1 == $i)
					$this->db->group_end();
			}
			$i++;
		}
		if(isset($_POST['order']) && isset($_POST['order']['0']['column']) && isset($_POST['order']['0']['dir'])){
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} else if(isset($this->order)){
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables(){
		$this->_get_datatables_query();
		if(isset($_POST['length']) && $_POST['length'] != -1)
			$this->db->limit($_POST['length'], $_POST['start']);
		return $this->db->get()->result();
	}

	function count_filtered(){
		$this->_get_datatables_query();
		return $this->db->get()->num_rows();
	}

	public function count_all(){
		$this->db->where('store_id', get_current_store_id());
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}

	public function xss_html_filter($input){
		return $this->security->xss_clean(html_escape($input));
	}

	public function verify_save_and_update(){
		$command          = $this->input->post('command', TRUE);
		$promotion_id     = $this->input->post('promotion_id', TRUE);
		$promotion_name   = $this->input->post('promotion_name', TRUE);
		$promotion_code   = $this->input->post('promotion_code', TRUE);
		$description      = $this->input->post('description', TRUE);
		$discount_type    = $this->input->post('discount_type', TRUE);
		$discount_value   = (float)$this->input->post('discount_value', TRUE);
		$min_price_rule   = $this->input->post('min_price_rule', TRUE);
		$min_margin_pct   = $this->input->post('min_margin_pct', TRUE);
		$applies_to       = $this->input->post('applies_to', TRUE);
		$category_id      = (int)$this->input->post('category_id', TRUE);
		$brand_id         = (int)$this->input->post('brand_id', TRUE);
		$start_date       = $this->input->post('start_date', TRUE);
		$end_date         = $this->input->post('end_date', TRUE);
		$item_ids         = $this->input->post('item_ids', TRUE);
		$mode             = $this->input->post('mode', TRUE) === 'advanced' ? 'advanced' : 'simple';
		$min_spend        = $this->input->post('min_spend', TRUE);
		$usage_limit_per_customer = $this->input->post('usage_limit_per_customer', TRUE);
		$usage_limit_total        = $this->input->post('usage_limit_total', TRUE);

		$store_id = get_current_store_id();
		$start_db = system_fromatted_date($start_date);
		$end_db   = system_fromatted_date($end_date);

		// Validate date range
		if(strtotime($end_db) < strtotime($start_db)){
			return "End Date cannot be before Start Date.";
		}

		// Validate percentage range
		if($discount_type == 'Percentage' && $discount_value > 100){
			return "Percentage discount cannot exceed 100%.";
		}
		if($discount_value <= 0){
			return "Discount Value must be greater than 0.";
		}

		$min_price_val = ($min_price_rule !== '' && $min_price_rule !== null) ? (float)$min_price_rule : null;
		$min_margin_val = ($min_margin_pct !== '' && $min_margin_pct !== null) ? (float)$min_margin_pct : null;
		$min_spend_val = ($min_spend !== '' && $min_spend !== null) ? (float)$min_spend : null;
		$usage_per_cust_val = ($usage_limit_per_customer !== '' && $usage_limit_per_customer !== null) ? (int)$usage_limit_per_customer : null;
		$usage_total_val = ($usage_limit_total !== '' && $usage_limit_total !== null) ? (int)$usage_limit_total : null;

		$entry = array(
			'store_id'        => $store_id,
			'promotion_name'  => $promotion_name,
			'promotion_code'  => $promotion_code,
			'description'     => $description,
			'discount_type'   => $discount_type,
			'discount_value'  => $discount_value,
			'min_price_rule'  => $min_price_val,
			'min_margin_pct'  => $min_margin_val,
			'applies_to'      => $applies_to,
			'category_id'     => $category_id ?: null,
			'brand_id'        => $brand_id ?: null,
			'start_date'      => $start_db,
			'end_date'        => $end_db,
			'status'          => 1,
			'mode'            => $mode,
			'min_spend'       => $min_spend_val,
			'usage_limit_per_customer' => $usage_per_cust_val,
			'usage_limit_total'        => $usage_total_val,
		);

	$this->db->trans_begin();

		try {
		if($command == 'save'){
			$entry['created_date'] = date('Y-m-d');
			$entry['created_time'] = date('H:i:s');
			$entry['created_by']   = $this->session->userdata('inv_username');
			$this->db->insert('db_promotions', $entry);
			$promotion_id = $this->db->insert_id();
		} else if($command == 'update'){
			$this->db->where('id', $promotion_id)->where('store_id', $store_id)->update('db_promotions', $entry);
			$this->db->where('promotion_id', $promotion_id)->delete('db_promotion_items');
		}

		// Link specific items if applies_to == 'items'
		if($applies_to == 'items' && !empty($item_ids)){
			$ids = is_array($item_ids) ? $item_ids : explode(',', $item_ids);
			$batch = array();
			foreach($ids as $iid){
				$iid = (int)$iid;
				if($iid > 0){
					$batch[] = array('promotion_id'=>$promotion_id, 'item_id'=>$iid, 'store_id'=>$store_id);
				}
			}
			if(!empty($batch)){
				$this->db->insert_batch('db_promotion_items', $batch);
			}
		}

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return "Failed to save promotion. Please try again.";
		}

		$this->db->trans_commit();
		$this->session->set_flashdata('success', 'Success! Promotion saved successfully.');
		return "success<<<###>>>".$promotion_id;
		} catch (Exception $e) {
		$this->db->trans_rollback();
		return "Failed to save promotion: " . $e->getMessage();
		}
	}

	public function get_details($id, $data){
		$q = $this->db->where('id', $id)->where('store_id', get_current_store_id())->get('db_promotions');
		if($q->num_rows() == 0){ show_404(); exit; }
		$row = $q->row();
		$data['promotion_id']      = $row->id;
		$data['promotion_name']    = $row->promotion_name;
		$data['promotion_code']    = $row->promotion_code;
		$data['description']       = $row->description;
		$data['discount_type']     = $row->discount_type;
		$data['discount_value']    = $row->discount_value;
		$data['min_price_rule']    = $row->min_price_rule;
		$data['min_margin_pct']    = $row->min_margin_pct;
		$data['applies_to']        = $row->applies_to;
		$data['category_id']       = $row->category_id;
		$data['brand_id']          = $row->brand_id;
		$data['start_date']        = show_date($row->start_date);
		$data['end_date']          = show_date($row->end_date);
		$data['mode']              = $row->mode ?? 'simple';
		$data['min_spend']         = $row->min_spend ?? '';
		$data['usage_limit_per_customer'] = $row->usage_limit_per_customer ?? '';
		$data['usage_limit_total']        = $row->usage_limit_total ?? '';
		// Fetch linked item ids
		$data['linked_item_ids'] = array();
		if($row->applies_to == 'items'){
			$items = $this->db->select('item_id')->where('promotion_id', $id)->get('db_promotion_items')->result();
			foreach($items as $it){ $data['linked_item_ids'][] = $it->item_id; }
		}
		return $data;
	}

	public function delete_promotion($id){
		$this->db->trans_begin();
		$this->db->where('promotion_id', $id)->delete('db_promotion_items');
		$this->db->where('id', $id)->where('store_id', get_current_store_id())->delete('db_promotions');
		$this->db->trans_commit();
		return "success";
	}

	public function update_status($id, $status){
		$ok = $this->db->where('id', $id)->where('store_id', get_current_store_id())
			->update('db_promotions', array('status'=>(int)$status));
		return $ok ? "success" : "failed";
	}

	/**
	 * Compute the effective promotional price for an item at checkout.
	 * Respects minimum-price and minimum-margin rules (margin protection).
	 *
	 * @param int $item_id
	 * @param float $original_price
	 * @return array
	 */
	public function compute_effective_price($item_id, $original_price){
		$store_id = get_current_store_id();
		$today = date('Y-m-d');
		$result = array(
			'has_promo' => false,
			'price' => (float)$original_price,
			'promo_name' => null,
			'discount_amount' => 0,
			'blocked_by_rule' => null,
		);

		if(!$this->db->table_exists('db_promotions')){
			return $result;
		}

		$item = $this->db->select('category_id, brand_id, purchase_price')->where('id', $item_id)->get('db_items')->row();
		if(!$item){ return $result; }

		// Find active promotions that apply to this item
		$this->db->where('store_id', $store_id);
		$this->db->where('status', 1);
		$this->db->where('start_date <=', $today);
		$this->db->where('end_date >=', $today);
		$this->db->group_start();
		$this->db->where('applies_to', 'all');
		if(!empty($item->category_id)){ $this->db->or_group_start()->where('applies_to','category')->where('category_id',$item->category_id)->group_end(); }
		if(!empty($item->brand_id)){ $this->db->or_group_start()->where('applies_to','brand')->where('brand_id',$item->brand_id)->group_end(); }
		$this->db->or_group_start()->where('applies_to','items')->where("id IN (SELECT promotion_id FROM db_promotion_items WHERE item_id = ".(int)$item_id.")", null, false)->group_end();
		$this->db->group_end();
		$this->db->order_by('discount_value','desc');
		$promos = $this->db->get('db_promotions')->result();

		if(empty($promos)){ return $result; }

		// Pick the promotion that gives the best actual discount for this item's price
		$best = null;
		$best_discount = 0;
		foreach($promos as $p){
			$d = 0;
			if($p->discount_type == 'Percentage'){
				$d = $original_price * ($p->discount_value / 100);
			} else {
				$d = (float)$p->discount_value;
			}
			if($d > $best_discount){
				$best_discount = $d;
				$best = $p;
			}
		}
		$promo = $best;
		if(!$promo){ return $result; }

		// Calculate discounted price
		$discounted = $original_price;
		if($promo->discount_type == 'Percentage'){
			$discounted = $original_price - ($original_price * ($promo->discount_value / 100));
		} else {
			$discounted = $original_price - (float)$promo->discount_value;
		}

		// Margin protection: minimum price rule
		if(!empty($promo->min_price_rule) && $discounted < (float)$promo->min_price_rule){
			$discounted = (float)$promo->min_price_rule;
			$result['blocked_by_rule'] = 'min_price';
		}

		// Margin protection: minimum margin %
		if(!empty($promo->min_margin_pct) && !empty($item->purchase_price)){
			$min_price_for_margin = $item->purchase_price + ($item->purchase_price * ($promo->min_margin_pct / 100));
			if($discounted < $min_price_for_margin){
				$discounted = $min_price_for_margin;
				$result['blocked_by_rule'] = 'min_margin';
			}
		}

		// Never go above original price
		if($discounted > $original_price){ $discounted = $original_price; }
		// Never go below zero
		if($discounted < 0){ $discounted = 0; }

		$result['has_promo'] = ($discounted < $original_price);
		$result['price'] = round($discounted, 4);
		$result['promo_name'] = $promo->promotion_name;
		$result['discount_amount'] = round($original_price - $discounted, 4);
		return $result;
	}

	/**
	 * Check if a promotion code can be used by a customer given their cart total.
	 * Returns array with 'ok' (bool) and 'message' (string).
	 */
	public function check_promotion_eligibility($promo, $customer_id, $cart_subtotal = 0){
		$result = array('ok' => true, 'message' => '');

		// Min spend check
		if(!empty($promo->min_spend) && $cart_subtotal < (float)$promo->min_spend){
			$result['ok'] = false;
			$result['message'] = 'Minimum spend of ' . store_number_format($promo->min_spend) . ' required. Cart total is ' . store_number_format($cart_subtotal) . '.';
			return $result;
		}

		// Usage limit per customer
		if(!empty($promo->usage_limit_per_customer) && $promo->usage_limit_per_customer > 0){
			$used = $this->db->where('promotion_id', $promo->id)
				->where('customer_id', $customer_id)
				->count_all_results('db_promotion_usage');
			if($used >= (int)$promo->usage_limit_per_customer){
				$result['ok'] = false;
				$result['message'] = 'You have already used this promotion ' . $used . ' time(s). Limit is ' . $promo->usage_limit_per_customer . '.';
				return $result;
			}
		}

		// Total usage limit
		if(!empty($promo->usage_limit_total) && $promo->usage_limit_total > 0){
			$total_used = $this->db->where('promotion_id', $promo->id)
				->count_all_results('db_promotion_usage');
			if($total_used >= (int)$promo->usage_limit_total){
				$result['ok'] = false;
				$result['message'] = 'This promotion has reached its total usage limit of ' . $promo->usage_limit_total . '.';
				return $result;
			}
		}

		return $result;
	}

	/**
	 * Record that a promotion was used in a sale.
	 */
	public function record_usage($promotion_id, $customer_id, $sales_id, $store_id = null){
		if(empty($promotion_id) || empty($sales_id)) return;
		$store_id = $store_id ?: get_current_store_id();
		$this->db->insert('db_promotion_usage', array(
			'promotion_id' => (int)$promotion_id,
			'customer_id'  => (int)$customer_id,
			'sales_id'     => (int)$sales_id,
			'store_id'     => (int)$store_id,
			'used_date'    => date('Y-m-d'),
			'used_time'    => date('H:i:s'),
		));
	}
}
