<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_transfer extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('stock_transfer_model','stock_transfer');
	}

	public function view()
	{
		$this->permission_check('stock_transfer_view');
		$data=$this->data;
		$data['page_title']=$this->lang->line('stock_transfer_list') ?: 'Stock Transfer List';
		$data['content']=$this->load->view('inventory/desktop/stock_transfer_list',$data,TRUE);
		$this->load->view('mp_layout',$data);
	}
	public function add()
	{	
		$this->permission_check('stock_transfer_add');
		$data=$this->data;
		$data['page_title']=$this->lang->line('stock_transfer') ?: 'Stock Transfer';
		$data['content']=$this->load->view('inventory/desktop/stock_transfer_form',$data,TRUE);
		$this->load->view('mp_layout',$data);
	}
	

	public function stock_save_and_update(){
		$this->form_validation->set_rules('transfer_date', 'Stock Date', 'trim|required');
		$this->form_validation->set_rules('warehouse_from', 'From Branch', 'trim|required');
		$this->form_validation->set_rules('warehouse_to', 'To Branch', 'trim|required');
		
		if ($this->form_validation->run() == TRUE) {
	    	$result = $this->stock_transfer->verify_save_and_update();
	    	echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}
	
	
	public function update($id){
		$this->belong_to('db_stocktransfer',$id);
		$this->permission_check('stock_transfer_edit');
		$data=$this->data;
		$data=array_merge($data,array('stocktransfer_id'=>$id));
		$data['page_title']=$this->lang->line('stock_transfer') ?: 'Stock Transfer';
		$data['content']=$this->load->view('inventory/desktop/stock_transfer_form',$data,TRUE);
		$this->load->view('mp_layout',$data);
	}
	

	public function ajax_list()
	{
		$list = $this->stock_transfer->get_datatables();
		
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $stock_transfer) {
			
			$no++;
			$row = array();
			$row[] = '<input type="checkbox" name="checkbox[]" value='.$stock_transfer->id.' class="checkbox column_checkbox" >';
			
			$row[] = show_date($stock_transfer->transfer_date);
			$row[] = get_warehouse_name($stock_transfer->warehouse_from);
			$row[] = get_warehouse_name($stock_transfer->warehouse_to);
					
					$str='<i>';
					$str.="Items: ".get_total_stocktranfer_items($stock_transfer->id);
					$str.="<br>Quantity: ".format_qty(get_total_stocktranfer_items_qty($stock_transfer->id));
					$str.='</i>';

			$row[] = $str;
			$row[] = $this->_arrival_cell($stock_transfer);
			$row[] = $stock_transfer->note;
			
			$row[] = ($stock_transfer->created_by);

					$str2 = '<div class="mp-actions">';
					if($this->permissions('stock_transfer_view'))
					$str2.='<a class="mp-edit" title="View" href="'.base_url('stock_transfer/info/'.$stock_transfer->id).'"><i class="fa fa-eye"></i></a>';

					if($this->permissions('stock_transfer_edit'))
					$str2.='<a class="mp-edit" title="Edit" href="'.base_url('stock_transfer/update/'.$stock_transfer->id).'"><i class="fa fa-pencil"></i></a>';

					if($this->permissions('stock_transfer_delete'))
					$str2.='<button type="button" class="mp-delete" title="Delete" onclick="delete_stock(\''.$stock_transfer->id.'\')"><i class="fa fa-trash"></i></button>';

					$str2 .= '</div>';

			$row[] = $str2;

			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->stock_transfer->count_all(),
						"recordsFiltered" => $this->stock_transfer->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}

	/**
	 * Arrival cell for the transfer list.
	 * Loads are only counted in where the store switched arrival receipts on;
	 * older installs keep posting straight away and show nothing here.
	 */
	private function _arrival_cell($transfer){
		$status = isset($transfer->receipt_status) ? (string)$transfer->receipt_status : 'Posted';
		if($status === '' || $status === 'Posted'){
			return '<span class="text-muted">&mdash;</span>';
		}

		$out = '';
		if($status === 'In Transit'){
			$out .= '<span class="label label-warning">In transit</span>';
			if($this->permissions('stock_transfer_edit') && $this->stock_transfer->may_receive($transfer)){
				$out .= ' <a href="'.base_url('stock_transfer/receive/'.$transfer->id).'" class="mp-edit" title="Count this load in"><i class="fa fa-check-circle"></i> Count in</a>';
			}
			return $out;
		}
		if($status === 'Awaiting Approval'){
			$out .= '<span class="label label-danger">Short &mdash; needs approval</span>';
			$out .= ' <a href="'.base_url('stock_transfer/receive/'.$transfer->id).'" class="mp-edit" title="Review this load"><i class="fa fa-exclamation-triangle"></i> Review</a>';
			return $out;
		}
		if($status === 'Received'){
			$who = trim((string)$transfer->received_by);
			$who = $who !== '' ? htmlspecialchars(explode(' ', $who)[0]) : '';
			$out .= '<span class="label label-success">Arrived</span>';
			if(!empty($transfer->received_date)) $out .= ' <small>'.show_date($transfer->received_date).'</small>';
			if($who !== '') $out .= ' <small>by '.$who.'</small>';
			return $out;
		}
		return '<span class="text-muted">&mdash;</span>';
	}

	/** Count-in screen: what actually arrived at the receiving location. */
	public function receive($id){
		$this->permission_check('stock_transfer_view');
		$this->belong_to('db_stocktransfer',$id);
		$transfer = $this->stock_transfer->get_receipt_header($id);
		if(!$transfer){
			$this->show_access_denied_page();
			return;
		}
		$status = (string)$transfer->receipt_status;
		if($status === '' || $status === 'Posted'){
			redirect('stock_transfer/view');
			return;
		}

		$data = $this->data;
		$data['stocktransfer_id'] = (int)$id;
		$data['transfer'] = $transfer;
		$data['lines'] = $this->stock_transfer->get_receipt_lines($id);
		$data['tolerance'] = function_exists('mp_transfer_loss_tolerance_percent')
			? mp_transfer_loss_tolerance_percent($transfer->store_id) : 0;
		$data['can_receive'] = $this->permissions('stock_transfer_edit') && $this->stock_transfer->may_receive($transfer);
		$data['can_decide'] = $this->_may_decide_variance();
		$data['site_label'] = function_exists('mp_label') ? mp_label('warehouse') : 'Warehouse';
		$sent_total = 0;
		foreach ($data['lines'] as $line) { $sent_total += (float)$line->transfer_qty; }
		$data['sent_total'] = $sent_total;
		$data['from_name'] = get_warehouse_name($transfer->warehouse_from);
		$data['to_name'] = get_warehouse_name($transfer->warehouse_to);
		$data['transfer_note'] = (string)$transfer->note;
		$data['tolerance_text'] = rtrim(rtrim(number_format((float)$data['tolerance'], 2), '0'), '.');
		$counter = trim((string)$transfer->received_by);
		$data['received_first'] = $counter !== '' ? explode(' ', $counter)[0] : '';
		$data['page_title'] = 'Count In Arriving Load';

		if(is_mobile()){
			$this->load->view('mobile/stock_transfer_receive',$data);
			return;
		}
		$data['content'] = $this->load->view('inventory/desktop/stock_transfer_receive',$data,TRUE);
		$this->load->view('mp_layout',$data);
	}

	public function save_receive(){
		$this->permission_check_with_msg('stock_transfer_edit');
		$id = (int)$this->input->post('stocktransfer_id',TRUE);
		$this->belong_to('db_stocktransfer',$id);
		$lines = $this->input->post('received',TRUE);
		$reason = (string)$this->input->post('variance_reason',TRUE);
		$result = $this->stock_transfer->save_receipt($id, is_array($lines) ? $lines : array(), $reason);
		if(stripos($result,'success') !== false){
			$this->session->set_flashdata('success','Counted in. The stock now shows at the receiving location.');
		}elseif(stripos($result,'Recorded.') === 0){
			$this->session->set_flashdata('success',$result);
		}else{
			$this->session->set_flashdata('error', strip_tags($result));
		}
		redirect('stock_transfer/receive/'.$id);
	}

	/** Manager decision on a short load. */
	public function decide_variance(){
		if(!$this->_may_decide_variance()){
			$this->session->set_flashdata('error','Only a manager can decide on a short load.');
			redirect('stock_transfer/view');
			return;
		}
		$id = (int)$this->input->post('stocktransfer_id',TRUE);
		$this->belong_to('db_stocktransfer',$id);
		$note = (string)$this->input->post('decision_note',TRUE);
		$decision = $this->input->post('decision',TRUE);
		if($note === ''){
			$this->session->set_flashdata('error','Please say why before deciding on this load.');
			redirect('stock_transfer/receive/'.$id);
			return;
		}
		if($decision === 'approve'){
			$result = $this->stock_transfer->approve_receipt_variance($id,$note);
			$ok = (stripos($result,'success') !== false);
			$msg = $ok ? 'Approved. The counted quantity has been added to the receiving location.' : strip_tags($result);
		}elseif($decision === 'reject'){
			$result = $this->stock_transfer->reject_receipt_variance($id,$note);
			$ok = (stripos($result,'success') !== false);
			$msg = $ok ? 'Rejected. The load is back in transit and must be counted in again.' : strip_tags($result);
		}else{
			$ok = false;
			$msg = 'Unknown decision.';
		}
		$this->session->set_flashdata($ok ? 'success' : 'error', $msg);
		redirect('stock_transfer/receive/'.$id);
	}

	/** Head-office rule: how much of a load may go missing in transit before a manager must approve. */
	public function tolerance(){
		if(!function_exists('mp_feature_enabled') || !mp_feature_enabled('transfer_receipt')){
			redirect('stock_transfer/view');
			return;
		}
		if(!is_store_admin() && !is_admin()){
			$this->session->set_flashdata('error','Only the store owner can change the transit loss limit.');
			redirect('stock_transfer/view');
			return;
		}
		$data = $this->data;
		$store_id = get_current_store_id();
		$data['tolerance'] = function_exists('mp_transfer_loss_tolerance_percent') ? mp_transfer_loss_tolerance_percent($store_id) : 0;
		$data['page_title'] = 'Transit Loss Limit';
		$data['content'] = $this->load->view('inventory/desktop/stock_transfer_tolerance',$data,TRUE);
		$this->load->view('mp_layout',$data);
	}

	public function save_tolerance(){
		$this->permission_check_with_msg('stock_transfer_edit');
		if(!is_store_admin() && !is_admin()){
			$this->session->set_flashdata('error','Only the store owner can change the transit loss limit.');
			redirect('stock_transfer/tolerance');
			return;
		}
		$value = $this->input->post('tolerance',TRUE);
		if(!is_numeric($value) || (float)$value < 0 || (float)$value > 100){
			$this->session->set_flashdata('error','Enter a limit between 0 and 100 percent.');
			redirect('stock_transfer/tolerance');
			return;
		}
		if(function_exists('mp_set_store_setting')){
			mp_set_store_setting(get_current_store_id(),'oil_gas','transfer_loss_tolerance_percent',round((float)$value,3),'float');
			$this->session->set_flashdata('success','Saved. Loads counted short by more than '.rtrim(rtrim(number_format((float)$value,2),'0'),'.').'% now wait for a manager.');
		}else{
			$this->session->set_flashdata('error','The limit could not be saved.');
		}
		redirect('stock_transfer/tolerance');
	}

	private function _may_decide_variance(){
		return is_store_admin() || is_admin() || $this->permissions('approval_logs_view');
	}

	public function info($id)
	{	
		$this->belong_to('db_stocktransfer',$id);
		if(!$this->permissions('stock_transfer_view') && !$this->permissions('stock_transfer_add') && !$this->permissions('stock_transfer_edit')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data=array_merge($data,array('stocktransfer_id'=>$id));
		$data['page_title']=$this->lang->line('stock_transfer_details') ?: 'Stock Transfer Details';
		$data['content']=$this->load->view('inventory/desktop/stock_transfer_info',$data,TRUE);
		$this->load->view('mp_layout',$data);
	}

	public function delete_stock(){
		$this->permission_check_with_msg('stock_transfer_delete');
		$id=$this->input->post('q_id');
		echo $this->stock_transfer->delete_stock($id);
	}
	public function multi_delete(){
		$this->permission_check_with_msg('stock_transfer_delete');
		$ids=implode (",",$_POST['checkbox']);
		echo $this->stock_transfer->delete_stock($ids);
	}


	//Table ajax code
	public function search_item(){
		$q=$this->input->get('q');
		$result=$this->stock_transfer->search_item($q);
		echo $result;
	}
	public function find_item_details(){
		$id=$this->input->post('id');
		
		$result=$this->stock_transfer->find_item_details($id);
		echo $result;
	}
	
	public function return_row_with_data($rowcount,$item_id){
		echo $this->stock_transfer->get_items_info($rowcount,$item_id);
	}
	public function return_stock_list($stock_id){
		echo $this->stock_transfer->return_stock_list($stock_id);
	}
	
	public function print_invoice($transfer_id)
	{
		$this->belong_to('db_stocktransfer',$transfer_id);
		if(!$this->permissions('stock_transfer_view') && !$this->permissions('stock_transfer_add') && !$this->permissions('stock_transfer_edit')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data=array_merge($data,array('stocktransfer_id'=>$transfer_id));
		$data['page_title']=$this->lang->line('stock_transfer');
		
		$this->load->view('warehouse/print-stock-transfer-invoice',$data);
	
	}

	public function pdf($stocktransfer_id){
		$this->belong_to('db_stocktransfer',$stocktransfer_id);
		if(!$this->permissions('stock_transfer_view') && !$this->permissions('stock_transfer_add') && !$this->permissions('stock_transfer_edit')){
			$this->show_access_denied_page();
		}
		
		$data=$this->data;
		$data['page_title']=$this->lang->line('stock_transfer') ?: 'Stock Transfer';
        $data=array_merge($data,array('stocktransfer_id'=>$stocktransfer_id));
        $this->load->view('warehouse/print-stock-transfer-invoice',$data);
       

        // Get output html
        $html = $this->output->get_output();
        // Load pdf library
        $this->load->library('pdf');
        
        // Load HTML content
        $this->dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation
        $this->dompdf->setPaper('A4', 'portrait');/*landscape or portrait*/
        
        // Render the HTML as PDF
        $this->dompdf->render();
        
        // Output the generated PDF (1 = download and 0 = preview)
        $this->dompdf->stream("stock_transfer_$stocktransfer_id", array("Attachment"=>0));
	}
}
