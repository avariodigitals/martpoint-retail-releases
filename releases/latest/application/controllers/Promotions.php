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
		$this->load->view('promotions/promotions_list', $data);
	}

	public function add(){
		$this->permission_check('promotions_manage');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('promotion_add');
		$this->load->view('promotions/promotion_form', $data);
	}

	public function edit($id){
		$this->permission_check('promotions_manage');
		$this->belong_to('db_promotions', $id);
		$data = $this->data;
		$result = $this->promotions_m->get_details($id, $data);
		$data = array_merge($data, $result);
		$data['page_title'] = $this->lang->line('promotion_edit');
		$this->load->view('promotions/promotion_form', $data);
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
			$row[] = htmlspecialchars($p->promotion_name);
			$row[] = htmlspecialchars($p->promotion_code ?: '-');
			$row[] = ($p->discount_type == 'Percentage') ? $p->discount_value.'%' : store_number_format($p->discount_value);
			$row[] = !empty($p->min_price_rule) ? store_number_format($p->min_price_rule) : '<span class="text-muted">-</span>';
			$row[] = !empty($p->min_margin_pct) ? $p->min_margin_pct.'%' : '<span class="text-muted">-</span>';
			$row[] = show_date($p->start_date);
			$row[] = show_date($p->end_date);

			$today = date('Y-m-d');
			$is_active = ($p->status == 1 && $p->start_date <= $today && $p->end_date >= $today);
			$badge = $is_active ? '<span class="label label-success">Running</span>'
				: ($p->status == 0 ? '<span class="label label-default">Inactive</span>'
				: ($p->end_date < $today ? '<span class="label label-danger">Expired</span>'
				: '<span class="label label-info">Scheduled</span>'));
			$row[] = $badge;

			$str = '<div class="btn-group"><a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">Action <span class="caret"></span></a><ul role="menu" class="dropdown-menu dropdown-light pull-right">';
			$str .= '<li><a href="'.base_url('promotions/edit/'.$p->id).'"><i class="fa fa-fw fa-edit text-blue"></i>Edit</a></li>';
			$str .= '<li><a style="cursor:pointer" onclick="delete_promotion('.$p->id.')"><i class="fa fa-fw fa-trash text-red"></i>Delete</a></li>';
			$str .= '</ul></div>';
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
		$this->load->model('promotions_model');
		$item_id = (int)$item_id;
		if(empty($original_price)){
			$item = $this->db->select('sales_price, purchase_price')->where('id',$item_id)->get('db_items')->row();
			$original_price = $item ? $item->sales_price : 0;
		}
		$result = $this->promotions_model->compute_effective_price($item_id, $original_price);
		echo json_encode($result);
	}
}
