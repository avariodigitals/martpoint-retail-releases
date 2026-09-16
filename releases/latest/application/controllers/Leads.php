<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Leads / CRM Controller
 * Capture, qualify and convert leads into customers.
 * Gated by the 'leads' feature flag (enabled by default for service-type businesses).
 */
class Leads extends MY_Controller {

	private function _can_view(){
		return $this->permissions('leads_view') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
	}

	private function _can_edit(){
		return $this->permissions('leads_edit') || $this->permissions('leads_add') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
	}

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!mp_feature_enabled('leads')){
			$this->show_feature_not_activated('leads');
			return;
		}
		if(!$this->_can_view() && !$this->_can_edit()){
			$this->show_access_denied_page();
			return;
		}
		$this->load->model('leads_model', 'leads');
	}

	// ============== LIST ==============

	public function index(){
		if(!$this->_can_view()){ $this->show_access_denied_page(); return; }
		$storeId = get_current_store_id();
		$status = trim($this->input->get('status', TRUE) ?: '');
		$search = trim($this->input->get('search', TRUE) ?: '');
		$data = array_merge($this->data, [
			'page_title' => 'Leads',
			'leads' => $this->leads->getLeads($storeId, $status, $search),
			'stats' => $this->leads->getLeadStats($storeId),
			'status_filter' => $status,
			'search' => $search,
			'can_edit' => $this->_can_edit(),
		]);
		$data['content'] = $this->load->view('leads/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	// ============== SAVE (add / edit) ==============

	public function save(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		try{
			$storeId = get_current_store_id();
			$id = (int)$this->input->post('lead_id');
			$name = trim($this->input->post('name', TRUE) ?: '');
			if($name === ''){
				echo json_encode(['status' => 'error', 'message' => 'Lead name is required']); return;
			}
			$source = trim($this->input->post('source', TRUE) ?: 'manual');
			$status = trim($this->input->post('status', TRUE) ?: 'new');
			$lData = [
				'store_id' => $storeId,
				'name' => $name,
				'phone' => trim($this->input->post('phone', TRUE) ?: ''),
				'email' => trim($this->input->post('email', TRUE) ?: ''),
				'source' => in_array($source, Leads_model::SOURCES) ? $source : 'manual',
				'status' => in_array($status, Leads_model::STATUSES) ? $status : 'new',
				'interest' => trim($this->input->post('interest', TRUE) ?: ''),
				'notes' => trim($this->input->post('notes', TRUE) ?: ''),
				'updated_at' => date('Y-m-d H:i:s'),
			];
			if(!$id){
				$lData['created_at'] = date('Y-m-d H:i:s');
				if(!$this->permissions('leads_add') && !is_admin() && !is_store_admin() && $this->session->userdata('role_id') != 1){
					echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
				}
			}
			$result = $this->leads->saveLead($lData, $id ?: null);
			if($result === false){
				$err = $this->db->error();
				echo json_encode(['status' => 'error', 'message' => 'Failed to save lead. ' . ($err['message'] ?? '')]);
			} else {
				echo json_encode(['status' => 'success', 'message' => 'Lead saved']);
			}
		} catch(Exception $e){
			echo json_encode(['status' => 'error', 'message' => 'Error: '.$e->getMessage()]);
		}
	}

	// ============== STATUS ==============

	public function update_status($id = 0){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		$status = trim($this->input->post('status', TRUE) ?: '');
		if($this->leads->updateStatus((int)$id, $status, get_current_store_id())){
			echo json_encode(['status' => 'success', 'message' => 'Status updated']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Could not update status']);
		}
	}

	// ============== CONVERT TO CUSTOMER ==============

	public function convert($id = 0){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		$result = $this->leads->convertToCustomer((int)$id, get_current_store_id());
		if(isset($result['error'])){
			echo json_encode(['status' => 'error', 'message' => $result['error']]);
		} else {
			echo json_encode(['status' => 'success', 'message' => 'Lead converted to customer', 'customer_id' => $result['customer_id']]);
		}
	}

	// ============== DELETE ==============

	public function delete($id = 0){
		if(!($this->permissions('leads_delete') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1)){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		try{
			$this->leads->deleteLead((int)$id, get_current_store_id());
			echo json_encode(['status' => 'success', 'message' => 'Lead deleted']);
		} catch(Exception $e){
			echo json_encode(['status' => 'error', 'message' => 'Error: '.$e->getMessage()]);
		}
	}

	// ============== EXPORT CSV ==============

	public function export(){
		if(!$this->_can_view()){ $this->show_access_denied_page(); return; }
		$storeId = get_current_store_id();
		$rows = $this->leads->getLeads($storeId, '', '', 10000);
		$store = get_store_details($storeId);
		$filename = 'leads-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($store->store_name ?? 'store')) . '-' . date('Ymd') . '.csv';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		$out = fopen('php://output', 'w');
		fputcsv($out, ['Name', 'Phone', 'Email', 'Source', 'Status', 'Interest', 'Notes', 'Created At', 'Converted Customer ID']);
		foreach($rows as $r){
			fputcsv($out, [$r->name, $r->phone, $r->email, $r->source, $r->status, $r->interest, $r->notes, $r->created_at, $r->converted_customer_id]);
		}
		fclose($out);
		exit;
	}
}
