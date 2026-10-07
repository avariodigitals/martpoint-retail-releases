<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Appointments — physiotherapy bookings.
 * All actions use physio_can() explicit grants; branch scope is enforced
 * server-side via physio_branch_ids()/physio_can_branch().
 */
class Appointments extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !mp_feature_enabled('appointments')){
			$this->show_feature_not_activated('appointments');
			return;
		}
		if(!physio_can('appointments_view')){
			$this->show_access_denied_page();
			return;
		}
		$this->load->model('appointments_model', 'appts');
		$this->load->model('patients_model', 'patients');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	// ============== LIST ==============

	public function index(){
		$storeId = get_current_store_id();
		$date   = trim($this->input->get('date', TRUE) ?: date('Y-m-d'));
		$status = trim($this->input->get('status', TRUE) ?: '');
		$filters = array('date' => $date, 'status' => $status);
		if(!physio_can('clinical_cross_branch')) $filters['branch_ids'] = physio_branch_ids();
		$branches = physio_branch_ids();
		$data = array_merge($this->data, array(
			'page_title' => 'Appointments',
			'appointments' => $this->appts->getAppointments($storeId, $filters),
			'filter_date' => $date,
			'status_filter' => $status,
			'patients'   => $this->patients->getPatients($storeId, 'active', '', 500),
			'services'   => $this->db->where('store_id', $storeId)->where('status', 1)->get('db_services')->result(),
			'clinicians' => $this->db->where('store_id', $storeId)->where('status', 1)->get('db_users')->result(),
			'branches'   => empty($branches) ? array()
				: $this->db->where('store_id', $storeId)->where_in('id', $branches)->get('db_warehouse')->result(),
			'can_book'   => physio_can('appointments_add'),
			'can_edit'   => physio_can('appointments_edit'),
			'can_cancel' => physio_can('appointments_cancel'),
			'can_checkin'=> physio_can('care_checkin'),
		));
		$data['content'] = $this->load->view('appointments/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	// ============== BOOK ==============

	public function save(){
		if(!physio_can('appointments_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$warehouseId = (int)$this->input->post('warehouse_id');
		if($warehouseId && !physio_can_branch($warehouseId)){
			$this->_json(array('status' => 'error', 'message' => 'Branch not in your scope')); return;
		}
		$result = $this->appts->book(array(
			'patient_id'    => (int)$this->input->post('patient_id'),
			'lead_id'       => (int)$this->input->post('lead_id') ?: null,
			'warehouse_id'  => $warehouseId ?: null,
			'service_id'    => (int)$this->input->post('service_id') ?: null,
			'staff_user_id' => (int)$this->input->post('staff_user_id') ?: null,
			'scheduled_at'  => trim($this->input->post('scheduled_at', TRUE) ?: ''),
			'duration_min'  => (int)$this->input->post('duration_min') ?: null,
			'notes'         => trim($this->input->post('notes', TRUE) ?: '') ?: null,
			'source'        => 'manual',
		));
		if(is_array($result)){
			$this->_json(array('status' => 'error', 'message' => $result['error']));
		} else {
			// Queue the confirmation — never sent synchronously.
			physio_notify('appt.requested.' . $result, array(
				'channel' => 'email', 'template_key' => 'appt_requested',
				'payload' => array('appointment_id' => $result),
			));
			$this->_json(array('status' => 'success', 'message' => 'Appointment booked', 'appointment_id' => $result));
		}
	}

	// ============== TRANSITIONS ==============

	public function transition($id = 0){
		$to = trim($this->input->post('to', TRUE) ?: '');
		$permMap = array('proposed' => 'appointments_edit', 'confirmed' => 'appointments_edit',
			'cancelled' => 'appointments_cancel', 'no_show' => 'appointments_edit', 'completed' => 'encounters_finalize');
		if(!isset($permMap[$to]) || !physio_can($permMap[$to])){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$appt = $this->appts->getAppointment((int)$id);
		if($appt && $appt->warehouse_id && !physio_can_branch($appt->warehouse_id)){
			$this->_json(array('status' => 'error', 'message' => 'Branch not in your scope')); return;
		}
		$note = trim($this->input->post('note', TRUE) ?: '') ?: null;
		$res = $this->appts->transition((int)$id, $to, $note);
		if($res === true && $to === 'confirmed'){
			physio_notify('appt.confirmed.' . (int)$id, array(
				'channel' => 'email', 'template_key' => 'appt_confirmed',
				'payload' => array('appointment_id' => (int)$id, 'patient_id' => (int)$appt->patient_id),
			));
		}
		if($res === true && $to === 'completed'){
			// Optional private-feedback invitation — frequency-limited by policy.
			$this->load->model('portal_model', 'portal');
			$this->portal->maybeRequestFeedback((int)$appt->patient_id, 'appointment', (int)$id, (int)$appt->store_id);
		}
		$this->_json($res === true
			? array('status' => 'success', 'message' => 'Appointment ' . $to)
			: $res);
	}

	public function reschedule($id = 0){
		if(!physio_can('appointments_edit')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$when = trim($this->input->post('scheduled_at', TRUE) ?: '');
		$reason = trim($this->input->post('reason', TRUE) ?: '') ?: null;
		if(!$when || !strtotime($when)){ $this->_json(array('status' => 'error', 'message' => 'New date/time required')); return; }
		$appt = $this->appts->getAppointment((int)$id);
		if($appt && $appt->warehouse_id && !physio_can_branch($appt->warehouse_id)){
			$this->_json(array('status' => 'error', 'message' => 'Branch not in your scope')); return;
		}
		$res = $this->appts->reschedule((int)$id, $when, $reason);
		$this->_json($res === true ? array('status' => 'success', 'message' => 'Rescheduled') : $res);
	}

	public function events($id = 0){
		$appt = $this->appts->getAppointment((int)$id);
		if(!$appt || ($appt->warehouse_id && !physio_can_branch($appt->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$this->_json(array('status' => 'success', 'events' => $this->appts->getEvents((int)$id)));
	}
}
