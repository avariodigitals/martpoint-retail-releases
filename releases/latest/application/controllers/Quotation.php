<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;

class Quotation extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('quotation_model','quotation');
		$this->load->helper('sms_template_helper');
	}

	public function is_sms_enabled(){
		return is_sms_enabled();
	}

	/** Send a saved customer quotation through the store's configured email provider. */
	public function send_email($id = 0){
		$this->permission_check_with_msg('send_email');
		$id = (int)$id;
		$store_id = get_current_store_id();
		$quote = $this->db->select('q.*, c.customer_name, c.email as customer_email, s.store_name')
			->from('db_quotation q')->join('db_customers c','c.id=q.customer_id','left')
			->join('db_store s','s.id=q.store_id','left')->where('q.id',$id)->where('q.store_id',$store_id)->get()->row();
		if(!$quote || empty($quote->customer_email) || !filter_var($quote->customer_email, FILTER_VALIDATE_EMAIL)){
			header('Content-Type: application/json');
			echo json_encode(['success'=>false,'message'=>'This customer has no valid email address. Add one to the customer record first.','csrf_hash'=>$this->security->get_csrf_hash()]);
			return;
		}
		$sectionField = $this->db->field_exists('section_name', 'db_quotationitems') ? 'qi.section_name' : "'' AS section_name";
		$items = $this->db->select('COALESCE(NULLIF(qi.manual_item_name,\'\'), i.item_name) AS item_name, COALESCE(NULLIF(qi.part_code,\'\'), i.item_code) AS item_code, COALESCE(NULLIF(qi.unit_label,\'\'), u.unit_name) AS unit_name, qi.description, '.$sectionField.', qi.quotation_qty, qi.price_per_unit, qi.tax_amt, qi.unit_total_cost, qi.total_cost', false)
			->from('db_quotationitems qi')->join('db_items i','i.id=qi.item_id','left')->join('db_units u','u.id=i.unit_id','left')
			->where('qi.quotation_id',$id)->where('qi.store_id',$store_id)->get()->result();
		$esc = function($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
		$money = function($v){ return store_number_format((float)$v, 2); };
		$rows = '';
		$textRows = '';
		$lastSection = null;
		$sectionAmount = 0;
		$taxTotal = 0;
		foreach($items as $item){
			$name = $item->item_name ?: 'Item';
			$desc = trim((string)$item->description);
			$section = trim((string)($item->section_name ?? ''));
			$partCode = trim((string)($item->item_code ?? ''));
			$sectionHeading = '';
			if($section !== $lastSection){
				if($lastSection !== null && $lastSection !== ''){ $rows .= '<tr><td colspan="4" style="padding:8px 10px;text-align:right;color:#475569;font-weight:bold">Section subtotal</td><td style="padding:8px 10px;text-align:right;font-weight:bold">'.$esc($money($sectionAmount)).'</td></tr>'; $textRows .= 'Section subtotal: '.$money($sectionAmount)."\n"; }
				$lastSection = $section;
				$sectionAmount = 0;
				if($section !== ''){ $sectionHeading = '<tr><td colspan="5" style="padding:9px 10px;background:#f1f5f9;color:#14532d;font-weight:bold">'.$esc($section).'</td></tr>'; }
			}
			$rows .= $sectionHeading.'<tr><td style="padding:10px;border-bottom:1px solid #e2e8f0">'.($partCode !== '' ? '<small style="color:#64748b;font-weight:bold">'.$esc($partCode).' · </small>' : '').$esc($name).($desc !== '' ? '<br><small>'.$esc($desc).'</small>' : '').'</td><td style="padding:10px;border-bottom:1px solid #e2e8f0;text-align:right">'.$esc(format_qty($item->quotation_qty)).'</td><td style="padding:10px;border-bottom:1px solid #e2e8f0">'.$esc($item->unit_name ?? '').'</td><td style="padding:10px;border-bottom:1px solid #e2e8f0;text-align:right">'.$esc($money($item->price_per_unit)).'</td><td style="padding:10px;border-bottom:1px solid #e2e8f0;text-align:right">'.$esc($money($item->total_cost)).'</td></tr>';
			$textRows .= ($sectionHeading !== '' ? "\n".$section."\n" : '').($partCode !== '' ? $partCode.' · ' : '').$name.' | '.format_qty($item->quotation_qty).' '.($item->unit_name ?? '').' | '.$money($item->price_per_unit).' | '.$money($item->total_cost)."\n";
			$sectionAmount += (float)$item->total_cost;
			$taxTotal += (float)$item->tax_amt;
		}
		if($lastSection !== null && $lastSection !== ''){ $rows .= '<tr><td colspan="4" style="padding:8px 10px;text-align:right;color:#475569;font-weight:bold">Section subtotal</td><td style="padding:8px 10px;text-align:right;font-weight:bold">'.$esc($money($sectionAmount)).'</td></tr>'; $textRows .= 'Section subtotal: '.$money($sectionAmount)."\n"; }
		$storeName = $quote->store_name ?: 'MartPoint';
		$subject = $storeName.' — Quotation '.$quote->quotation_code;
		$html = '<div style="font-family:Arial,sans-serif;color:#172033;max-width:760px;margin:auto"><h2 style="color:#14532d">'. $esc($storeName) .'</h2><p>Dear '. $esc($quote->customer_name ?: 'Customer') .',</p><p>Please find our quotation <strong>'. $esc($quote->quotation_code) .'</strong> below.</p><p>Quotation date: '. $esc($quote->quotation_date) .(!empty($quote->expire_date) ? ' · Valid until: '.$esc($quote->expire_date) : '').'</p><table style="width:100%;border-collapse:collapse"><thead><tr style="background:#f1f5f9"><th style="padding:10px;text-align:left">Description</th><th style="padding:10px;text-align:right">Qty</th><th style="padding:10px;text-align:left">Unit</th><th style="padding:10px;text-align:right">Unit price</th><th style="padding:10px;text-align:right">Amount</th></tr></thead><tbody>'. $rows .'</tbody></table>'.($taxTotal > 0 ? '<p style="text-align:right">Subtotal: '.$esc($money($quote->subtotal)).'<br>Tax: '.$esc($money($taxTotal)).'</p>' : '').'<p style="text-align:right;font-size:18px"><strong>Total: '. $esc($money($quote->grand_total)) .'</strong></p>'.(!empty($quote->quotation_note) ? '<p>'.nl2br($esc($quote->quotation_note)).'</p>' : '').'<p>Please reply to this email if you have questions or would like to proceed.</p><p>'. $esc($storeName) .'</p></div>';
		$text = 'Dear '.($quote->customer_name ?: 'Customer').",\n\nQuotation ".$quote->quotation_code."\nDate: ".$quote->quotation_date."\n\nItem | Qty | Unit Price | Amount\n".$textRows.($taxTotal > 0 ? "\nSubtotal: ".$money($quote->subtotal)."\nTax: ".$money($taxTotal) : '')."\nTotal: ".$money($quote->grand_total)."\n\n".strip_tags((string)$quote->quotation_note)."\n\nReply to this email with any questions.\n".$storeName;
		$this->load->model('email_service');
		$result = $this->email_service->sendRaw($quote->customer_email, $subject, $html, $text, [
			'template_key'=>'customer_quotation','related_module'=>'quotation','related_record_id'=>$id,
			'subject'=>$subject
		]);
		header('Content-Type: application/json');
		echo json_encode(['success'=>!empty($result['success']),'message'=>$result['message'] ?? 'Email could not be sent.','csrf_hash'=>$this->security->get_csrf_hash()]);
	}

	public function index()
	{
		$this->permission_check('quotation_view');
		$data=$this->data;
		$data['page_title']=$this->lang->line('quotation_list');
		$data['content']=$this->load->view('quotation/quotation_list',$data,TRUE);
		$this->load->view('mp_layout',$data);
	}
	public function add()
	{	
		$this->permission_check('quotation_add');
		$data=$this->data;
		$is_plant = function_exists('mp_get_store_profile') && (mp_get_store_profile()['industry_type'] ?? '') === 'oil_processing_plant';
		$data['page_title']=$is_plant ? 'New Quotation' : $this->lang->line('quotation');
		$data['content']=$this->load->view($is_plant ? 'quotation/plant_quotation' : 'quotation/quotation',$data,TRUE);
		$this->load->view('mp_layout',$data);
	}

	public function plant_save(){
		$is_plant = function_exists('mp_get_store_profile') && (mp_get_store_profile()['industry_type'] ?? '') === 'oil_processing_plant';
		if(!$is_plant){ show_404(); return; }
		$this->permission_check($this->input->post('quotation_id', TRUE) ? 'quotation_edit' : 'quotation_add');
		$this->form_validation->set_rules('quotation_date', 'Quotation Date', 'trim|required');
		$this->form_validation->set_rules('customer_id', 'Customer', 'trim|required');
		header('Content-Type: application/json');
		if(!$this->form_validation->run()){
			echo json_encode(array('success'=>false,'message'=>strip_tags(validation_errors()),'csrf_hash'=>$this->security->get_csrf_hash()));
			return;
		}
		$result = $this->quotation->save_plant_quotation();
		$parts = explode('<<<###>>>', $result);
		echo json_encode(array('success'=>$parts[0] === 'success','message'=>$parts[0] === 'success' ? 'Quotation saved.' : $result,'id'=>isset($parts[1]) ? (int)$parts[1] : null,'csrf_hash'=>$this->security->get_csrf_hash()));
	}

	public function plant_item_search(){
		$is_plant = function_exists('mp_get_store_profile') && (mp_get_store_profile()['industry_type'] ?? '') === 'oil_processing_plant';
		if(!$is_plant){ show_404(); return; }
		$this->permission_check('quotation_add');
		$term = trim((string)$this->input->get('q', TRUE));
		$items = array();
		if(mb_strlen($term) >= 2){
			$items = $this->db->select('i.id, i.item_name, i.item_code, i.description, i.sales_price, u.unit_name')
				->from('db_items i')->join('db_units u', 'u.id=i.unit_id', 'left')
				->where('i.store_id', get_current_store_id())->where('i.status', 1)
				->group_start()->like('i.item_name', $term)->or_like('i.item_code', $term)->group_end()
				->order_by('i.item_name')->limit(12)->get()->result_array();
		}
		header('Content-Type: application/json');
		echo json_encode($items);
	}

	/** Workbook example is exposed only inside the processing plant quote builder. */
	public function plant_sample(){
		$is_plant = function_exists('mp_get_store_profile') && (mp_get_store_profile()['industry_type'] ?? '') === 'oil_processing_plant';
		if(!$is_plant){ show_404(); return; }
		$this->permission_check('quotation_add');
		header('Content-Type: application/json');
		echo file_get_contents(APPPATH.'data/plant_quote_hykeng_011026.json');
	}
	

	public function quotation_save_and_update(){
		$this->form_validation->set_rules('quotation_date', 'Quotation Date', 'trim|required');
		$this->form_validation->set_rules('customer_id', 'Customer Name', 'trim|required');
		
		if ($this->form_validation->run() == TRUE) {
	    	$result = $this->quotation->verify_save_and_update();
	    	echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}
	
	
	public function update($id){
		if(empty($id)){ redirect('quotation'); }
		$this->belong_to('db_quotation',$id);
		$this->permission_check('quotation_edit');
		$data=$this->data;
		$data=array_merge($data,array('quotation_id'=>$id));
		$is_plant = function_exists('mp_get_store_profile') && (mp_get_store_profile()['industry_type'] ?? '') === 'oil_processing_plant';
		$is_plant_format = $is_plant && $this->db->field_exists('manual_item_name', 'db_quotationitems')
			&& $this->db->where('quotation_id', (int)$id)->where('manual_item_name !=', '')->count_all_results('db_quotationitems') > 0;
		$data['page_title']=$is_plant_format ? 'Edit Quotation' : $this->lang->line('quotation');
		$data['content']=$this->load->view($is_plant_format ? 'quotation/plant_quotation' : 'quotation/quotation',$data,TRUE);
		$this->load->view('mp_layout',$data);
	}
	

	public function ajax_list()
	{
		$list = $this->quotation->get_datatables();
		
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $quotation) {
			$has_manual_lines = $this->db->field_exists('manual_item_name', 'db_quotationitems')
				&& $this->db->where('quotation_id', (int)$quotation->id)->where('manual_item_name !=', '')->count_all_results('db_quotationitems') > 0;
			
			$no++;
			$row = array();
			$row[] = '<input type="checkbox" name="checkbox[]" value='.$quotation->id.' class="checkbox column_checkbox" >';
			
			$str='';
			        if($quotation->sales_status!='')
			          $str="<span title='Converted to Sales Invoice' class='label label-success' style='cursor:pointer'> Converted </span>";
			$row[] = show_date($quotation->quotation_date)."<br>".$str;
			$row[] = (!empty($quotation->expire_date)) ? show_date($quotation->expire_date) : '';

			$row[] = $quotation->quotation_code;
			
			$row[] = $quotation->reference_no;
			$row[] = $quotation->customer_name;
			
			$row[] = store_number_format($quotation->grand_total);
			$row[] = ($quotation->created_by);

					 $str1=base_url().'quotation/update/';

					$str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light pull-right">';

											if($this->permissions('sales_add') && $quotation->sales_status=='' && !$has_manual_lines)
											$str2.='<li>
												<a title="Convert to Invoice" href="'.base_url().'sales/quotation/'.$quotation->id.'" >
													<i class="fa fa-fw fa-exchange text-blue"></i>Convert to Invoice
												</a>
											</li>';

											if($quotation->sales_status=='Converted')
											$str2.='<li>
												<a title="View to Invoice" href="'.base_url().'sales/invoice/'.get_sales_id_of_quotation($quotation->id).'" >
													<i class="fa fa-fw fa-eye text-blue"></i>View Sales Invoice
												</a>
											</li>';

											if($this->permissions('quotation_view'))
											$str2.='<li>
												<a title="View Invoice" href="'.base_url().'quotation/invoice/'.$quotation->id.'" >
													<i class="fa fa-fw fa-eye text-blue"></i>View Quotation
												</a>
											</li>';

							if($this->permissions('send_email'))
							$str2.='<li>
								<a style="cursor:pointer" onclick="sendQuotationEmail('.(int)$quotation->id.')">
									<i class="fa fa-fw fa-envelope text-blue"></i>Email to Customer
								</a>
							</li>';

											if($this->permissions('quotation_edit'))
											$str2.='<li>
												<a title="Update Record ?" href="'.$str1.$quotation->id.'">
													<i class="fa fa-fw fa-edit text-blue"></i>Edit
												</a>
											</li>';

											if($this->permissions('quotation_add') || $this->permissions('quotation_edit'))
											$str2.='<li>
												<a title="Take Print" target="_blank" href="'.base_url().'quotation/print_invoice/'.$quotation->id.'">
													<i class="fa fa-fw fa-print text-blue"></i>Print
												</a>
											</li>

											<li>
												<a title="Download PDF" target="_blank" href="'.base_url().'quotation/pdf/'.$quotation->id.'">
													<i class="fa fa-fw fa-file-pdf-o text-blue"></i>PDF
												</a>
											</li>';

											if($this->permissions('quotation_delete'))
											$str2.='<li>
												<a style="cursor:pointer" title="Delete Record ?" onclick="delete_quotation(\''.$quotation->id.'\')">
													<i class="fa fa-fw fa-trash text-red"></i>Delete
												</a>
											</li>
											
										</ul>
									</div>';			

			$row[] = $str2;

			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->quotation->count_all(),
						"recordsFiltered" => $this->quotation->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}
	public function update_status(){
		$this->permission_check('quotation_edit');
		$id=$this->input->post('id');
		$status=$this->input->post('status');

		
		$result=$this->quotation->update_status($id,$status);
		return $result;
	}
	public function delete_quotation(){
		$this->permission_check_with_msg('quotation_delete');
		$id=$this->input->post('q_id');
		echo $this->quotation->delete_quotation($id);
	}
	public function multi_delete(){
		$this->permission_check_with_msg('quotation_delete');
		$ids=implode (",",$_POST['checkbox']);
		echo $this->quotation->delete_quotation($ids);
	}


	//Table ajax code
	public function search_item(){
		$q=$this->input->get('q');
		$result=$this->quotation->search_item($q);
		echo $result;
	}
	public function find_item_details(){
		$id=$this->input->post('id');
		
		$result=$this->quotation->find_item_details($id);
		echo $result;
	}

	//quotation invoice form
	public function invoice($id)
	{	
		if(empty($id)){ redirect('quotation'); }
		$this->belong_to('db_quotation',$id);
		if(!$this->permissions('quotation_view')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data=array_merge($data,array('quotation_id'=>$id));
		$data['page_title']=$this->lang->line('quotation_invoice');
		$data['content']=$this->load->view('quotation/quotation-invoice',$data,TRUE);
		$this->load->view('mp_layout',$data);
	}
	
	//Print quotation invoice 
	public function print_invoice($quotation_id)
	{
		if(empty($quotation_id)){ redirect('quotation'); }
		$this->belong_to('db_quotation',$quotation_id);
		if(!$this->permissions('quotation_add') && !$this->permissions('quotation_edit')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data=array_merge($data,array('quotation_id'=>$quotation_id));
		$data['page_title']=$this->lang->line('quotation_invoice');
		$data['auto_print']=true;
		
			$this->load->view('quotation/print-quotation-invoice-3',$data);
		
	}


	public function pdf($quotation_id){
		if(empty($quotation_id)){ redirect('quotation'); }
		$this->belong_to('db_quotation',$quotation_id);
		if(!$this->permissions('quotation_add') && !$this->permissions('quotation_edit')){
			$this->show_access_denied_page();
		}
		
		$data=$this->data;
		$data['page_title']=$this->lang->line('quotation_invoice');
        $data=array_merge($data,array('quotation_id'=>$quotation_id));
      
			$this->load->view('quotation/print-quotation-invoice-3',$data);
		
       

        // Get output html
        $html = $this->output->get_output();

        require_once(APPPATH . 'libraries/dompdf/autoload.inc.php');
        mb_internal_encoding('UTF-8');

        $options = new Options();
		$options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // Load HTML content
        $dompdf->loadHtml($html,'UTF-8');
        
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');/*landscape or portrait*/
        
        // Render the HTML as PDF
        $dompdf->render();
        
        // Output the generated PDF (1 = download and 0 = preview)
        $dompdf->stream("Quotation_$quotation_id-".date('M')."_".date('d')."_".date('Y'), array("Attachment"=>0));
		exit;
	}
	
	

	
	/*v1.1*/
	public function return_row_with_data($rowcount,$item_id){
		echo $this->quotation->get_items_info($rowcount,$item_id);
	}
	public function return_quotation_list($quotation_id){
		echo $this->quotation->return_quotation_list($quotation_id);
	}
	
	public function show_pay_now_modal(){
		$this->permission_check_with_msg('quotation_view');
		$quotation_id=$this->input->post('quotation_id');
		echo $this->quotation->show_pay_now_modal($quotation_id);
	}
	public function save_payment(){
		$this->permission_check_with_msg('quotation_add');
		echo $this->quotation->save_payment();
	}
	public function view_payments_modal(){
		$this->permission_check_with_msg('quotation_view');
		$quotation_id=$this->input->post('quotation_id');
		echo $this->quotation->view_payments_modal($quotation_id);
	}
	public function get_customers_select_list(){
		echo get_customers_select_list(null,$_POST['store_id']);
	}
	public function get_items_select_list(){
		echo get_items_select_list(null,$_POST['store_id']);
	}
	public function get_tax_select_list(){
		echo get_tax_select_list(null,$_POST['store_id']);
	}
	/*Get warehouse select list*/
	public function get_warehouse_select_list(){
		echo get_warehouse_select_list(null,$_POST['store_id']);
	}
	//Print quotation Payment Receipt
	public function print_show_receipt($payment_id){
		if(!$this->permissions('quotation_add') && !$this->permissions('quotation_edit')){
			$this->show_access_denied_page();
		}
		$data=$this->data;
		$data['page_title']=$this->lang->line('payment_receipt');
		$data=array_merge($data,array('payment_id'=>$payment_id));
		$this->load->view('print-cust-payment-receipt',$data);
	}
	
	public function get_users_select_list(){
		echo get_users_select_list($this->session->userdata("role_id"),$_POST['store_id']);
	}
}
