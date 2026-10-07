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
		if(!function_exists('physio_enabled')) $this->load->helper('physio');
		$physio = physio_enabled();
		$data = array_merge($this->data, [
			'page_title' => 'Leads',
			'leads' => $this->leads->getLeads($storeId, $status, $search),
			'stats' => $this->leads->getLeadStats($storeId),
			'status_filter' => $status,
			'search' => $search,
			'can_edit' => $this->_can_edit(),
			'physio_active' => $physio,
			'can_convert_patient' => $physio && physio_can('patients_add'),
			'can_book_appt' => $physio && physio_can('appointments_add'),
			'staff' => $physio ? $this->db->select('id, username')->where('store_id', $storeId)->where('status', 1)->get('db_users')->result() : array(),
		]);
		$data['content'] = $this->load->view('leads/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	// ============== SAVE (add / edit) ==============

        /**
         * Integrator — the embed snippet and store key for this store.
         *
         * GET  /leads/integrator         view the snippet + key
         * POST /leads/integrator         rotate the key (owner only)
         *
         * Per fleet: the key belongs to db_store, so every store on the install
         * gets its own, and a snippet copied from one store can only ever write
         * leads to that store — Intake::_storeByKey() resolves the store FROM
         * the key, and never trusts a store id sent by the page.
         */
        public function integrator(){
                if(!$this->_can_view()){ $this->show_access_denied_page(); return; }
                $storeId = get_current_store_id();

                // Key always shown masked; the owner is the only role allowed to
                // reveal or rotate it, because anyone holding it can write leads.
                $isOwner = ($this->session->userdata('role_id') == 1 || is_store_admin());

                if(strtoupper($this->input->method()) === 'POST'){
                    if(!$isOwner){
                        echo json_encode(array('status' => 'error', 'message' => 'Only the store owner can rotate the store key.'));
                        return;
                    }
                    if(!$this->db->field_exists('intake_key', 'db_store')){
                        echo json_encode(array('status' => 'error', 'message' => 'Run the latest migration first (db_store.intake_key is missing).'));
                        return;
                    }
                    $key = bin2hex(random_bytes(24)); // 48 hex chars, within the 16-64 the intake endpoint accepts
                    $this->db->where('id', $storeId)->update('db_store', array('intake_key' => $key));
                    if(function_exists('mp_audit_log')){
                        mp_audit_log('leads', 'update', 'store', 'Rotated the store intake key');
                    }
                    echo json_encode(array('status' => 'success', 'message' => 'New store key generated. Any page still using the old key will stop working.', 'key' => $key));
                    return;
                }

                // Only select columns that actually exist: db_store has no
                // subdomain/domain column on this schema, and naming a missing
                // column makes the whole statement fail.
                $cols = array('id', 'store_name');
                if($this->db->field_exists('intake_key', 'db_store')){ $cols[] = 'intake_key'; }
                $row = $this->db->select(implode(',', $cols))
                        ->where('id', $storeId)->get('db_store')->row();
                $key = (string)($row->intake_key ?? '');

                if(!function_exists('mp_feature_enabled_for_store')){ $this->load->helper('business_profile'); }

                $data = array_merge($this->data, array(
                        'page_title'    => 'Booking integrator',
                        'store_id'      => $storeId,
                        'store_name'    => $row->store_name ?? '',
                        'intake_key'    => $key,
                        'key_masked'    => $key === '' ? '' : (substr($key, 0, 6) . str_repeat('•', 18) . substr($key, -4)),
                        'key_set'       => $key !== '',
                        'leads_enabled' => mp_feature_enabled_for_store('leads', $storeId),
                        'is_owner'      => $isOwner,
                        'intake_url'    => base_url('intake/lead'),
                        'store_slug'    => '',
                        'book_url'      => base_url('appointments/save'),
                        'has_key_column'=> $this->db->field_exists('intake_key', 'db_store'),
                ));
                $data['content'] = $this->load->view('leads/integrator', $data, TRUE);
                $this->load->view('mp_layout', $data);
        }
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

	// ============== ASSIGN / FOLLOW-UP (stage 2) ==============

	public function assign($id = 0){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		$userId = (int)$this->input->post('assigned_to');
		$follow = trim($this->input->post('next_followup_at', TRUE) ?: '');
		$follow = preg_match('/^\d{4}-\d{2}-\d{2}/', $follow) ? $follow : null;
		if($this->leads->assign((int)$id, $userId ?: null, $follow, get_current_store_id())){
			echo json_encode(['status' => 'success', 'message' => 'Lead assigned']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Could not update lead']);
		}
	}

	public function log_activity($id = 0){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		$type = trim($this->input->post('activity_type', TRUE) ?: 'note');
		$note = trim($this->input->post('note', TRUE) ?: '');
		if($note === ''){ echo json_encode(['status' => 'error', 'message' => 'Note is required']); return; }
		$allowed = ['note','call','whatsapp','email','visit','followup'];
		if(!in_array($type, $allowed)) $type = 'note';
		$this->leads->addActivity((int)$id, $type, $note, null, get_current_store_id());
		echo json_encode(['status' => 'success', 'message' => 'Activity logged']);
	}

	public function activities($id = 0){
		if(!$this->_can_view()){ echo json_encode(['status' => 'error', 'message' => 'Access denied']); return; }
		$rows = $this->leads->getActivities((int)$id, get_current_store_id());
		echo json_encode(['status' => 'success', 'activities' => $rows]);
	}

	// ============== CONVERT TO PATIENT (physio only) ==============

	public function convert_to_patient($id = 0){
		if(!function_exists('physio_enabled')) $this->load->helper('physio');
		if(!physio_enabled() || !physio_can('patients_add')){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		$result = $this->leads->convertToPatient((int)$id, get_current_store_id());
		if(isset($result['error'])){
			echo json_encode(['status' => 'error', 'message' => $result['error']]);
		} else {
			echo json_encode(['status' => 'success',
				'message' => !empty($result['already']) ? 'Lead already linked to this patient' : 'Lead converted to patient',
				'patient_id' => $result['patient_id']]);
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
