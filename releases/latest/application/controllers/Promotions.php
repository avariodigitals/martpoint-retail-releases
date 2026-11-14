<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Promotions extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('promotions_model','promotions_m');
	}

	public function index(){
		$this->permission_check('promotions_manage');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('promotion_list');
		$data['content'] = $this->load->view('promotions/promotions_list', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function add(){
		$this->permission_check('promotions_manage');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('promotion_add');
		$data['content'] = $this->load->view('promotions/promotion_form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function edit($id){
		$this->permission_check('promotions_manage');
		$this->belong_to('db_promotions', $id);
		$data = $this->data;
		$result = $this->promotions_m->get_details($id, $data);
		$data = array_merge($data, $result);
		$data['page_title'] = $this->lang->line('promotion_edit');
		$data['content'] = $this->load->view('promotions/promotion_form', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save(){
		$this->form_validation->set_rules('promotion_name', 'Promotion Name', 'trim|required');
		$this->form_validation->set_rules('discount_type', 'Discount Type', 'trim|required');
		$this->form_validation->set_rules('discount_value', 'Discount Value', 'trim|required|numeric');
		$this->form_validation->set_rules('start_date', 'Start Date', 'trim|required');
		$this->form_validation->set_rules('end_date', 'End Date', 'trim|required');

		if($this->form_validation->run() == TRUE){
			echo $this->promotions_m->verify_save_and_update();
		} else {
			echo "Please fill all required fields correctly.";
		}
	}

	public function delete(){
		$this->permission_check_with_msg('promotions_manage');
		$id = $this->input->post('q_id');
		echo $this->promotions_m->delete_promotion($id);
	}

	public function update_status(){
		$this->permission_check_with_msg('promotions_manage');
		$id = $this->input->post('id');
		$status = $this->input->post('status');
		echo $this->promotions_m->update_status($id, $status);
	}

	public function ajax_list(){
		$list = $this->promotions_m->get_datatables();
		$data = array();
		$no = $_POST['start'];
		foreach($list as $p){
			$no++;
			$row = array();
			$row[] = '<input type="checkbox" name="checkbox[]" value="'.$p->id.'" class="checkbox column_checkbox">';
			$row[] = '<div class="promo-name">'.htmlspecialchars($p->promotion_name).'</div>'
				. ($p->promotion_code ? '<div class="promo-code">'.htmlspecialchars($p->promotion_code).'</div>' : '');
			$row[] = ($p->discount_type == 'Percentage') ? '<span class="discount-val">'.$p->discount_value.'%</span>' : '<span class="discount-val">'.store_number_format($p->discount_value).'</span>';
			$applies_label = 'All Items';
			if($p->applies_to == 'category'){
				$cat = $this->db->select('category_name')->where('id', $p->category_id)->get('db_category')->row();
				$applies_label = 'Category: ' . ($cat ? htmlspecialchars($cat->category_name) : 'Unknown');
			} elseif($p->applies_to == 'brand'){
				$brand = $this->db->select('brand_name')->where('id', $p->brand_id)->get('db_brands')->row();
				$applies_label = 'Brand: ' . ($brand ? htmlspecialchars($brand->brand_name) : 'Unknown');
			} elseif($p->applies_to == 'items'){
				$count = $this->db->where('promotion_id', $p->id)->from('db_promotion_items')->count_all_results();
				$applies_label = $count . ' Item' . ($count != 1 ? 's' : '');
			}
			$row[] = '<span class="text-muted">' . $applies_label . '</span>';
			// Build rules column: mode badge + advanced rules
			$rules_parts = array();
			$mode = $p->mode ?? 'simple';
			$rules_parts[] = '<span class="label ' . ($mode == 'advanced' ? 'label-info' : 'label-default') . '">' . ucfirst($mode) . '</span>';
			if(!empty($p->min_spend)){
				$rules_parts[] = 'Min Spend: ' . store_number_format($p->min_spend);
			}
			if(!empty($p->usage_limit_per_customer)){
				$rules_parts[] = '1 cust × ' . $p->usage_limit_per_customer;
			}
			if(!empty($p->usage_limit_total)){
				$rules_parts[] = 'Total × ' . $p->usage_limit_total;
			}
			if(!empty($p->min_price_rule)){
				$rules_parts[] = 'Min Price: ' . store_number_format($p->min_price_rule);
			}
			if(!empty($p->min_margin_pct)){
				$rules_parts[] = 'Min Margin: ' . $p->min_margin_pct . '%';
			}
			$row[] = '<div style="font-size:12px;line-height:1.6;">' . implode('<br>', $rules_parts) . '</div>';
			$row[] = show_date($p->start_date);
			$row[] = show_date($p->end_date);

			$today = date('Y-m-d');
			$is_active = ($p->status == 1 && $p->start_date <= $today && $p->end_date >= $today);
			$badge = $is_active ? '<span class="label label-success">Running</span>'
				: ($p->status == 0 ? '<span class="label label-default">Inactive</span>'
				: ($p->end_date < $today ? '<span class="label label-danger">Expired</span>'
				: '<span class="label label-info">Scheduled</span>'));
			$row[] = $badge;

			$str = '<div class="pr-actions">'
				. '<a href="'.base_url('promotions/edit/'.$p->id).'" class="pr-edit" title="Edit"><i class="fa fa-pencil"></i></a>'
				. '<button type="button" class="pr-delete" onclick="delete_promotion('.$p->id.')" title="Delete"><i class="fa fa-trash-o"></i></button>'
				. '</div>';
			$row[] = $str;
			$data[] = $row;
		}
		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->promotions_m->count_all(),
			"recordsFiltered" => $this->promotions_m->count_filtered(),
			"data" => $data,
		);
		echo json_encode($output);
	}

	/**
	 * Returns the effective promotional price for an item, respecting
	 * minimum-price and minimum-margin rules. Called by POS at checkout.
	 * @param int $item_id
	 * @param float $original_price
	 * @return array ['has_promo'=>bool, 'price'=>float, 'promo_name'=>string, 'blocked_by_rule'=>string|null]
	 */
	public function get_effective_price($item_id, $original_price = null){
		$item_id = (int)$item_id;
		if(empty($original_price)){
			$item = $this->db->select('sales_price, purchase_price')->where('id',$item_id)->get('db_items')->row();
			$original_price = $item ? $item->sales_price : 0;
		}
		$result = $this->promotions_m->compute_effective_price($item_id, $original_price);
		echo json_encode($result);
	}
}
