<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Oil & Gas workspace.
 *
 * The control tower for a company that runs more than one site: filling
 * stations, LPG outlets, bulk depots, a lubricants yard. It composes the
 * existing purchasing, stock, transfer and sales modules around the way a
 * multi-location fuel business works, and it is deliberately additive — a
 * station owner who only wants the till still has it.
 */
class Oil_gas extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('oil_gas_model', 'oil_gas');
	}

	private function _check_feature(){
		if(!function_exists('mp_is_oil_gas_workspace') || !mp_is_oil_gas_workspace()){
			$this->show_feature_not_activated(
				'oil_gas_workspace',
				'Select Filling Station, LPG / Cooking Gas, Bulk Fuel Depot, Lubricants or Oil & Gas Supplies in Business Profile to open this workspace.'
			);
		}
	}

	public function index(){
		$this->_check_feature();
		$this->permission_check('dashboard_view');

		$store_id = (int)get_current_store_id();
		$forecourt = $this->_forecourt_model();
		$lpg = $this->_lpg_model();
		$data = $this->data ?? array();
		$data['page_title'] = 'Oil & Gas Workspace';
		$data['summary'] = $this->oil_gas->company_summary($store_id);
		$data['forecourt_sites'] = $forecourt->forecourt_sites($store_id);
		$data['forecourt'] = $forecourt->forecourt_counts($store_id);
		$data['forecourt_by_site'] = $forecourt->site_forecourt_summary($store_id);
		$data['forecourt_dip'] = mp_forecourt_tank_variance_percent($store_id);
		$data['lpg_by_site'] = $lpg->site_lpg_summary($store_id);
		$data['lpg'] = $lpg->lpg_counts($store_id);
		$data['deposit_flag'] = mp_feature_enabled('cylinder_deposits');
		$data['jobs_flag'] = function_exists('mp_oil_gas_jobs_enabled') && mp_oil_gas_jobs_enabled();
		$data['can_see_jobs'] = ($data['jobs_flag'] && $this->permissions('service_jobs_view'));
		$data['locations'] = $this->oil_gas->locations($store_id);
		$ids = array();
		foreach($data['locations'] as $location) $ids[] = (int)$location->id;
		$data['rollup'] = $this->oil_gas->location_rollup($store_id, $ids);
		$data['open_loads'] = $this->oil_gas->open_loads($store_id);
		$data['arrival_mode'] = mp_feature_enabled('transfer_receipt');
		$data['site_label'] = mp_label('warehouse');
		$data['transfer_label'] = mp_label('stock_transfer');
		$data['can_edit_locations'] = (is_store_admin() || $this->permissions('warehouse_edit'));

		if(is_mobile()){
			$this->load->view('mobile/oil_gas', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/dashboard', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** Per-location capability switches: a site can only narrow what head office allows. */
	public function locations(){
		$this->_check_feature();
		$this->permission_check('warehouse_view');

		$store_id = (int)get_current_store_id();
		$data = $this->data ?? array();
		$data['page_title'] = 'Location Operations';
		$data['locations'] = $this->oil_gas->locations($store_id);
		$ids = array();
		foreach($data['locations'] as $location) $ids[] = (int)$location->id;
		$data['rollup'] = $this->oil_gas->location_rollup($store_id, $ids);
		$data['switches'] = function_exists('mp_get_location_scoped_flags') ? mp_get_location_scoped_flags() : array();
		$data['site_label'] = mp_label('warehouse');
		$data['can_edit'] = (is_store_admin() || $this->permissions('warehouse_edit'));

		if(is_mobile()){
			$this->load->view('mobile/oil_gas_locations', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/locations', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_locations(){
		$this->_check_feature();
		$this->permission_check_with_msg('warehouse_edit');
		if(!is_store_admin() && !is_admin() && !$this->permissions('warehouse_edit')){
			$this->session->set_flashdata('error', 'Only the store owner can change what a location does.');
			redirect('oil_gas/locations');
			return;
		}

		$store_id = (int)get_current_store_id();
		$per_location = $this->input->post('location', TRUE);
		if(!is_array($per_location) || empty($per_location)){
			$this->session->set_flashdata('error', 'Nothing was changed.');
			redirect('oil_gas/locations');
			return;
		}

		$saved = 0;
		foreach($per_location as $warehouse_id => $flags){
			$result = $this->oil_gas->save_location_flags($store_id, (int)$warehouse_id, is_array($flags) ? $flags : array());
			if($result === 'success') $saved++;
		}
		$this->session->set_flashdata($saved ? 'success' : 'error',
			$saved ? 'Saved. ' . $saved . ' location' . ($saved === 1 ? '' : 's') . ' updated.' : 'The locations could not be saved.');
		redirect('oil_gas/locations');
	}

	// ---------------------------------------------------------------- forecourt

	/**
	 * The forecourt is a layer on top of the workspace, not a replacement for it:
	 * a depot or an LPG outlet that has not switched on pump metering never sees
	 * a pump screen, and the company still has to run oil & gas at all.
	 */
	private function _check_forecourt($flag = 'pump_metering'){
		$this->_check_feature();
		if(!function_exists('mp_feature_enabled') || !mp_feature_enabled($flag)){
			$this->show_feature_not_activated(
				$flag,
				'Turn on this capability under Business Profile &rarr; Capabilities, then switch it on for the location that has pumps and tanks.'
			);
			return false;
		}
		return true;
	}

	private function _forecourt_model(){
		$this->load->model('fuel_forecourt_model', 'forecourt');
		return $this->forecourt;
	}

	/**
	 * The site the forecourt screens are working on. A staff member only ever
	 * gets a site they are assigned to; head office may move between all of them.
	 */
	private function _chosen_site($sites, $requested = 0){
		$ids = array();
		foreach($sites as $site) $ids[] = (int)$site->id;
		if(empty($ids)) return 0;
		$requested = (int)$requested;
		if($requested > 0 && in_array($requested, $ids, true)) return $requested;
		return $ids[0];
	}

	/** Forecourt overview for one site: tanks, what they hold, duties waiting. */
	public function forecourt(){
		if(!$this->_check_forecourt()) return;
		$this->permission_check('warehouse_view');

		$store_id = (int)get_current_store_id();
		$forecourt = $this->_forecourt_model();
		$sites = $forecourt->forecourt_sites($store_id);
		$site_id = $this->_chosen_site($sites, (int)$this->input->get('site', TRUE));

		$data = $this->data ?? array();
		$data['page_title'] = 'Forecourt';
		$data['sites'] = $sites;
		$data['site_id'] = $site_id;
		$data['summary'] = $forecourt->forecourt_counts($store_id);
		$data['tanks'] = $site_id ? $forecourt->tanks($store_id, array($site_id)) : array();
		$tank_ids = array();
		foreach($data['tanks'] as $tank) $tank_ids[] = (int)$tank->id;
		$data['positions'] = $forecourt->tank_positions($store_id, $tank_ids);
		$data['duty'] = $site_id ? $forecourt->current_shift($store_id, $site_id) : null;
		$data['duty_done'] = $site_id ? $forecourt->shifts($store_id, null, null, 5, $site_id) : array();
		$data['duties_pending'] = $forecourt->pending_shifts($store_id);
		$data['dip_gaps'] = $forecourt->open_dip_gaps($store_id);
		$data['tolerance_dip'] = mp_forecourt_tank_variance_percent($store_id);
		$data['tolerance_duty'] = mp_forecourt_shift_variance_percent($store_id);
		$data['can_edit'] = (is_store_admin() || $this->permissions('warehouse_edit'));
		$data['can_decide'] = $this->_may_decide_forecourt();
		$data['site_label'] = mp_label('warehouse');
		$data['pumps'] = $site_id ? $forecourt->pumps($store_id, $site_id) : array();

		if(is_mobile()){
			$this->load->view('mobile/oil_gas_forecourt', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/forecourt', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** Register and adjust the tanks at a site. */
	public function tanks(){
		if(!$this->_check_forecourt()) return;
		$this->permission_check('warehouse_view');

		$store_id = (int)get_current_store_id();
		$forecourt = $this->_forecourt_model();
		$sites = $forecourt->forecourt_sites($store_id);
		$site_id = $this->_chosen_site($sites, (int)$this->input->get('site', TRUE));

		$data = $this->data ?? array();
		$data['page_title'] = 'Storage Tanks';
		$data['sites'] = $sites;
		$data['site_id'] = $site_id;
		$data['tanks'] = $site_id ? $forecourt->tanks($store_id, array($site_id)) : array();
		$tank_ids = array();
		foreach($data['tanks'] as $tank) $tank_ids[] = (int)$tank->id;
		$data['positions'] = $forecourt->tank_positions($store_id, $tank_ids);
		$data['edit'] = $this->input->get('edit') ? $forecourt->tank($store_id, (int)$this->input->get('edit', TRUE)) : null;
		$data['items'] = $site_id ? $forecourt->items_for_site($store_id, $site_id) : array();
		$data['can_edit'] = (is_store_admin() || $this->permissions('warehouse_edit'));
		$data['site_label'] = mp_label('warehouse');

		if(is_mobile()){
			$this->load->view('mobile/oil_gas_tanks', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/tanks', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_tank(){
		if(!$this->_check_forecourt()) return;
		$this->permission_check_with_msg('warehouse_edit');
		$store_id = (int)get_current_store_id();
		$forecourt = $this->_forecourt_model();
		$result = $forecourt->save_tank($store_id, $this->input->post(NULL, TRUE));
		$site = (int)$this->input->post('site_id', TRUE);
		$this->session->set_flashdata($result === 'success' ? 'success' : 'error',
			$result === 'success' ? 'Saved. The tank is on the forecourt board now.' : strip_tags($result));
		redirect('oil_gas/tanks'.($site ? '?site='.$site : ''));
	}

	/**
	 * Fill a tank from what physically arrived. Optional transfer id pre-selects
	 * the load, so counting a tanker in and booking it into the ground tank
	 * happens at the same moment.
	 */
	public function fill(){
		if(!$this->_check_forecourt()) return;
		$this->permission_check('warehouse_view');

		$store_id = (int)get_current_store_id();
		$forecourt = $this->_forecourt_model();
		$tank_id = (int)$this->input->get('tank', TRUE);
		$tank = $tank_id ? $forecourt->tank($store_id, $tank_id) : null;
		if($tank_id && !$tank){
			$this->session->set_flashdata('error', 'That tank is not one of yours.');
			redirect('oil_gas/forecourt');
			return;
		}
		$transfer_id = (int)$this->input->get('transfer', TRUE);
		$transfer = null;
		$transfer_lines = array();
		if($transfer_id > 0){
			$this->load->model('stock_transfer_model');
			$transfer = $this->stock_transfer_model->get_receipt_header($transfer_id);
			if(!$transfer || (int)$transfer->store_id !== $store_id){
				$this->session->set_flashdata('error', 'That load is not one of yours.');
				redirect('oil_gas/forecourt');
				return;
			}
			$transfer_lines = $this->stock_transfer_model->get_receipt_lines($transfer_id);
			if(!$tank && (int)$transfer->warehouse_to > 0){
				$candidates = $forecourt->fill_options($store_id, (int)$transfer->warehouse_to, 0);
				if(count($candidates) === 1) $tank = $forecourt->tank($store_id, (int)$candidates[0]->id);
			}
		}

		$data = $this->data ?? array();
		$data['page_title'] = 'Fill a Tank';
		$data['tank'] = $tank;
		$data['transfer'] = $transfer;
		$data['transfer_lines'] = $transfer_lines;
		$data['transfer_id'] = $transfer_id;
		$data['tanks'] = $tank ? array() : $forecourt->tanks($store_id);
		$tank_ids = array();
		foreach($data['tanks'] as $option) $tank_ids[] = (int)$option->id;
		$data['positions'] = $forecourt->tank_positions($store_id, $tank_ids);
		$data['options'] = $tank ? $forecourt->fill_options($store_id, (int)$tank->warehouse_id, (int)$tank->item_id) : array();
		$data['book'] = $tank ? $forecourt->tank_book_litres($tank->id) : 0;
		$data['already_filled'] = $transfer_id ? $forecourt->fill_for_transfer($store_id, $transfer_id) : null;
		$data['already_filled_tank'] = $data['already_filled'] ? $forecourt->tank($store_id, (int)$data['already_filled']->tank_id) : null;
		$data['site_label'] = mp_label('warehouse');
		$data['can_edit'] = (is_store_admin() || $this->permissions('warehouse_edit'));

		if(is_mobile()){
			$this->load->view('mobile/oil_gas_fill', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/fill', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_fill(){
		if(!$this->_check_forecourt()) return;
		$this->permission_check_with_msg('warehouse_edit');
		$store_id = (int)get_current_store_id();
		$forecourt = $this->_forecourt_model();
		$tank_id = (int)$this->input->post('tank_id', TRUE);
		$transfer_id = (int)$this->input->post('transfer_id', TRUE);
		$result = $forecourt->fill_tank($store_id, $tank_id,
			$this->input->post('litres', TRUE), $transfer_id,
			(string)$this->input->post('reason', TRUE));
		$this->session->set_flashdata($result === 'success' ? 'success' : 'error',
			$result === 'success' ? 'Booked. The tank now shows the litres that arrived.' : strip_tags($result));
		redirect('oil_gas/forecourt?site='.(int)$this->input->post('site_id', TRUE));
	}

	/** A physical dip reading. Never changes the book on its own. */
	public function save_dip(){
		if(!$this->_check_forecourt()) return;
		$this->permission_check_with_msg('warehouse_edit');
		$store_id = (int)get_current_store_id();
		$forecourt = $this->_forecourt_model();
		$tank_id = (int)$this->input->post('tank_id', TRUE);
		$result = $forecourt->record_dip($store_id, $tank_id,
			$this->input->post('level_litres', TRUE), (string)$this->input->post('note', TRUE));
		$ok = (stripos($result, 'Recorded.') === 0 || $result === 'success');
		$this->session->set_flashdata($ok ? 'success' : 'error', strip_tags($result));
		redirect('oil_gas/forecourt?site='.(int)$this->input->post('site_id', TRUE));
	}

	/** Register the pumps so a duty knows what to read. */
	public function pumps(){
		if(!$this->_check_forecourt()) return;
		$this->permission_check('warehouse_view');

		$store_id = (int)get_current_store_id();
		$forecourt = $this->_forecourt_model();
		$sites = $forecourt->forecourt_sites($store_id);
		$site_id = $this->_chosen_site($sites, (int)$this->input->get('site', TRUE));

		$data = $this->data ?? array();
		$data['page_title'] = 'Pumps & Nozzles';
		$data['sites'] = $sites;
		$data['site_id'] = $site_id;
		$data['pumps'] = $site_id ? $forecourt->pumps($store_id, $site_id) : array();
		$data['meters'] = $site_id ? $forecourt->pump_current_meters($store_id, $site_id) : array();
		$data['tanks'] = $site_id ? $forecourt->tanks($store_id, array($site_id)) : array();
		$tank_ids = array();
		foreach($data['tanks'] as $tank) $tank_ids[] = (int)$tank->id;
		$data['positions'] = $forecourt->tank_positions($store_id, $tank_ids);
		$data['edit'] = $this->input->get('edit') ? $forecourt->pump($store_id, (int)$this->input->get('edit', TRUE)) : null;
		$data['items'] = $site_id ? $forecourt->items_for_site($store_id, $site_id) : array();
		$data['can_edit'] = (is_store_admin() || $this->permissions('warehouse_edit'));
		$data['site_label'] = mp_label('warehouse');

		if(is_mobile()){
			$this->load->view('mobile/oil_gas_pumps', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/pumps', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_pump(){
		if(!$this->_check_forecourt()) return;
		$this->permission_check_with_msg('warehouse_edit');
		$store_id = (int)get_current_store_id();
		$forecourt = $this->_forecourt_model();
		$result = $forecourt->save_pump($store_id, $this->input->post(NULL, TRUE));
		$site = (int)$this->input->post('site_id', TRUE);
		$this->session->set_flashdata($result === 'success' ? 'success' : 'error',
			$result === 'success' ? 'Saved. The pump is on the duty sheet now.' : strip_tags($result));
		redirect('oil_gas/pumps'.($site ? '?site='.$site : ''));
	}

	/**
	 * The duty: open it by reading every meter, close it by reading them again.
	 * Kept apart from the till shift on purpose — one counts litres, the other
	 * counts money, and the station needs both to prove a day.
	 */
	public function duty(){
		if(!$this->_check_forecourt('fuel_shift_reconciliation')) return;
		$this->permission_check('warehouse_view');

		$store_id = (int)get_current_store_id();
		$forecourt = $this->_forecourt_model();
		$sites = $forecourt->forecourt_sites($store_id, 'fuel_shift_reconciliation');
		if(empty($sites)){
			$this->session->set_flashdata('error', 'None of your locations closes duties yet. Switch on duty reconciliation for the site under Locations.');
			redirect('oil_gas/forecourt');
			return;
		}
		$site_id = $this->_chosen_site($sites, (int)$this->input->get('site', TRUE));
		$duty = $site_id ? $forecourt->current_shift($store_id, $site_id) : null;

		$data = $this->data ?? array();
		$data['page_title'] = 'Duty Readings';
		$data['sites'] = $sites;
		$data['site_id'] = $site_id;
		$data['duty'] = $duty;
		$data['lines'] = $duty ? $forecourt->shift_meters($store_id, (int)$duty->id) : array();
		$data['pumps'] = $site_id ? $forecourt->pumps($store_id, $site_id) : array();
		$data['meters'] = $site_id ? $forecourt->pump_current_meters($store_id, $site_id) : array();
		$till_items = array();
		foreach($data['lines'] as $line) if((int)$line->item_id > 0) $till_items[(int)$line->item_id] = true;
		$data['till_so_far'] = $duty ? $forecourt->till_litres($store_id, $site_id, array_keys($till_items), $duty->opened_at, date('Y-m-d H:i:s')) : 0;
		$data['history'] = $site_id ? $forecourt->shifts($store_id, null, null, 12) : array();
		$data['tolerance'] = mp_forecourt_shift_variance_percent($store_id);
		$data['can_work'] = $this->_may_work_forecourt();
		$data['can_decide'] = $this->_may_decide_forecourt();
		$data['site_label'] = mp_label('warehouse');

		if(is_mobile()){
			$this->load->view('mobile/oil_gas_duty', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/duty', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_duty(){
		if(!$this->_check_forecourt('fuel_shift_reconciliation')) return;
		$this->permission_check_with_msg('warehouse_view');
		if(!$this->_may_work_forecourt()){
			$this->session->set_flashdata('error', 'You are not allowed to take duty readings here.');
			redirect('oil_gas/duty');
			return;
		}
		$store_id = (int)get_current_store_id();
		$forecourt = $this->_forecourt_model();
		$action = $this->input->post('action', TRUE);
		$site_id = (int)$this->input->post('site_id', TRUE);

		if($action === 'open'){
			$result = $forecourt->open_shift($store_id, $site_id);
			$ok = is_int($result) && $result > 0;
			$msg = $ok ? 'Duty opened. Every meter on this site is captured — read them again when you hand over.' : (is_int($result) ? 'The duty could not be opened.' : strip_tags($result));
		}elseif($action === 'close'){
			$shift_id = (int)$this->input->post('shift_id', TRUE);
			$readings = $this->input->post('meter', TRUE);
			$result = $forecourt->close_shift($store_id, $shift_id, is_array($readings) ? $readings : array(),
				(string)$this->input->post('close_note', TRUE));
			$ok = ($result === 'success');
			$msg = $ok ? 'Duty closed and reconciled.' : strip_tags($result);
		}else{
			$ok = false;
			$msg = 'Unknown duty action.';
		}
		$this->session->set_flashdata($ok ? 'success' : 'error', $msg);
		redirect('oil_gas/duty?site='.$site_id);
	}

	/** A manager's answer to a duty or dip that is bigger than the company allows. */
	public function decide(){
		if(!$this->_check_forecourt()) return;
		$this->permission_check_with_msg('warehouse_view');
		if(!$this->_may_decide_forecourt()){
			$this->session->set_flashdata('error', 'Only a manager can decide on a forecourt difference.');
			redirect('oil_gas/forecourt');
			return;
		}
		$store_id = (int)get_current_store_id();
		$forecourt = $this->_forecourt_model();
		$kind = $this->input->post('kind', TRUE);
		$id = (int)$this->input->post('target_id', TRUE);
		$decision = $this->input->post('decision', TRUE);
		$note = (string)$this->input->post('decision_note', TRUE);
		$site_id = (int)$this->input->post('site_id', TRUE);

		if($note === ''){
			$this->session->set_flashdata('error', 'Please say why before deciding. This goes in the approval history.');
			redirect('oil_gas/forecourt?site='.$site_id);
			return;
		}
		if($kind === 'duty'){
			$result = $forecourt->decide_shift($store_id, $id, $decision, $note);
			$msg = $result === 'success'
				? ($decision === 'approve' ? 'Accepted. The duty is settled and the litres stay out of the tank.'
					: 'Rejected. The duty is open again and the tank book has been put back.')
				: strip_tags($result);
		}elseif($kind === 'dip'){
			$result = $forecourt->decide_dip($store_id, $id, $decision, $note);
			$msg = $result === 'success'
				? ($decision === 'accept' ? 'Accepted. The tank book now matches the dip.'
					: 'Declined. Nothing was written off; check the reading and dip again.')
				: strip_tags($result);
		}else{
			$result = 'unknown';
			$msg = 'Unknown decision.';
		}
		$this->session->set_flashdata($result === 'success' ? 'success' : 'error', $msg);
		redirect($kind === 'duty' ? 'oil_gas/duty?site='.$site_id : 'oil_gas/forecourt?site='.$site_id);
	}

	/** Head-office tolerance limits for the forecourt. */
	public function forecourt_settings(){
		if(!$this->_check_forecourt()) return;
		if(!is_store_admin() && !is_admin()){
			$this->session->set_flashdata('error', 'Only the store owner can change the forecourt limits.');
			redirect('oil_gas/forecourt');
			return;
		}
		$store_id = (int)get_current_store_id();
		$data = $this->data ?? array();
		$data['page_title'] = 'Forecourt Limits';
		$data['duty_tolerance'] = mp_forecourt_shift_variance_percent($store_id);
		$data['tank_tolerance'] = mp_forecourt_tank_variance_percent($store_id);
		if(is_mobile()){
			$this->load->view('mobile/oil_gas_settings', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/settings', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_forecourt_settings(){
		if(!$this->_check_forecourt()) return;
		$this->permission_check_with_msg('warehouse_edit');
		if(!is_store_admin() && !is_admin()){
			$this->session->set_flashdata('error', 'Only the store owner can change the forecourt limits.');
			redirect('oil_gas/forecourt_settings');
			return;
		}
		$store_id = (int)get_current_store_id();
		$saved = 0;
		foreach(array('duty_variance' => 'shift_variance_tolerance_percent', 'tank_variance' => 'tank_variance_tolerance_percent') as $field => $key){
			$value = $this->input->post($field, TRUE);
			if(!is_numeric($value) || (float)$value < 0 || (float)$value > 100){
				$this->session->set_flashdata('error', 'Enter a limit between 0 and 100 percent for both checks.');
				redirect('oil_gas/forecourt_settings');
				return;
			}
			if(function_exists('mp_set_store_setting')){
				mp_set_store_setting($store_id, 'oil_gas', $key, round((float)$value, 3), 'float');
				$saved++;
			}
		}
		$this->session->set_flashdata($saved ? 'success' : 'error',
			$saved ? 'Saved. The forecourt now allows the limits you set.' : 'The limits could not be saved.');
		redirect('oil_gas/forecourt_settings');
	}

	// ---------------------------------------------------------------------- LPG

	/**
	 * LPG sits on the same workspace as the forecourt but is not part of it: an
	 * outlet that only lends and refills cylinders never sees a pump screen, and a
	 * station that only sells litres never sees a cylinder register.
	 */
	private function _check_lpg($flag = 'lpg_refills'){
		$this->_check_feature();
		if(!function_exists('mp_feature_enabled') || !mp_feature_enabled($flag)){
			$this->show_feature_not_activated(
				$flag,
				'Turn on this capability under Business Profile &rarr; Capabilities, then switch it on for the location that fills or lends cylinders.'
			);
			return false;
		}
		return true;
	}

	/** The board opens as soon as either half of LPG work is switched on. */
	private function _check_lpg_board(){
		$this->_check_feature();
		$lpg = $this->_lpg_model();
		if(!$lpg->has_lpg((int)get_current_store_id())){
			$this->show_feature_not_activated(
				'lpg_refills',
				'Switch on cylinder refills or the cylinder register under Business Profile &rarr; Capabilities, then mark the location that does this work under Locations.'
			);
			return false;
		}
		return true;
	}

	private function _lpg_model(){
		$this->load->model('fuel_lpg_model', 'lpg');
		return $this->lpg;
	}

	/**
	 * The LPG board: what the scale said against what the till rang up, the shells
	 * that are out with customers, and the money held against them.
	 */
	public function lpg(){
		if(!$this->_check_lpg_board()) return;
		$this->permission_check('warehouse_view');

		$store_id = (int)get_current_store_id();
		$lpg = $this->_lpg_model();
		$sites = $lpg->worksites($store_id);
		$site_id = $this->_chosen_site($sites, (int)$this->input->get('site', TRUE));

		$today = date('Y-m-d');
		$site_ids = $site_id ? array($site_id) : null;
		$data = $this->data ?? array();
		$data['page_title'] = 'LPG Board';
		$data['sites'] = $sites;
		$data['site_id'] = $site_id;
		$data['counts'] = $lpg->lpg_counts($store_id);
		$data['refilled_today'] = $site_id ? $lpg->refilled_kg($store_id, $today, $today, $site_ids) : 0;
		$data['sold_today'] = $site_id
			? $lpg->sold_kg($store_id, $site_ids, $lpg->cylinder_item_ids($store_id), $today.' 00:00:00', $today.' 23:59:59')
			: 0;
		$data['shells'] = $site_id ? array_slice($lpg->cylinders($store_id, null, $site_ids), 0, 8) : array();
		$data['refills'] = $site_id ? $lpg->refills($store_id, $today, $today, 10, $site_id) : array();
		$data['waiting'] = $lpg->unreconciled_refills($store_id, 7, 10);
		$data['deposits'] = $lpg->open_deposits($store_id, 10);
		$data['can_work'] = $this->permissions('warehouse_view');
		$data['can_edit'] = (is_store_admin() || $this->permissions('warehouse_edit'));
		$data['site_label'] = mp_label('warehouse');
		$data['refill_flag'] = mp_feature_enabled('lpg_refills');
		$data['tracking_flag'] = mp_feature_enabled('cylinder_tracking');
		$data['deposit_flag'] = mp_feature_enabled('cylinder_deposits');

		if(is_mobile()){
			$this->load->view('mobile/oil_gas_lpg', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/lpg', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** The cylinder register: every shell the shop owns and who is holding it. */
	public function cylinders(){
		if(!$this->_check_lpg('cylinder_tracking')) return;
		$this->permission_check('warehouse_view');

		$store_id = (int)get_current_store_id();
		$lpg = $this->_lpg_model();
		$sites = $lpg->lpg_sites($store_id, 'cylinder_tracking');
		$site_id = $this->_chosen_site($sites, (int)$this->input->get('site', TRUE));

		$data = $this->data ?? array();
		$data['page_title'] = 'Cylinder Register';
		$data['sites'] = $sites;
		$data['site_id'] = $site_id;
		$data['cylinders'] = $site_id ? $lpg->cylinders($store_id, null, array($site_id)) : array();
		$data['counts'] = $lpg->cylinder_counts($store_id);
		$data['edit'] = $this->input->get('edit') ? $lpg->cylinder($store_id, (int)$this->input->get('edit', TRUE)) : null;
		$data['history'] = $this->input->get('shell')
			? $lpg->movement_history($store_id, (int)$this->input->get('shell', TRUE)) : array();
		$data['shell'] = $this->input->get('shell') ? $lpg->cylinder($store_id, (int)$this->input->get('shell', TRUE)) : null;
		$data['items'] = $site_id ? $lpg->items_for_site($store_id, $site_id) : array();
		$data['customers'] = $lpg->customers($store_id);
		$data['can_work'] = $this->permissions('warehouse_view');
		$data['can_edit'] = (is_store_admin() || $this->permissions('warehouse_edit'));
		$data['site_label'] = mp_label('warehouse');
		$data['refill_flag'] = mp_feature_enabled('lpg_refills');
		$data['deposit_flag'] = mp_feature_enabled('cylinder_deposits');

		if(is_mobile()){
			$this->load->view('mobile/oil_gas_cylinders', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/cylinders', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_cylinder(){
		if(!$this->_check_lpg('cylinder_tracking')) return;
		$this->permission_check_with_msg('warehouse_edit');
		$store_id = (int)get_current_store_id();
		$lpg = $this->_lpg_model();
		$result = $lpg->save_cylinder($store_id, $this->input->post(NULL, TRUE));
		$site = (int)$this->input->post('site_id', TRUE);
		$this->session->set_flashdata($result === 'success' || is_int($result) ? 'success' : 'error',
			$result === 'success' || is_int($result)
				? 'Saved. The shell is on the register at that location now.'
				: strip_tags($result));
		redirect('oil_gas/cylinders'.($site ? '?site='.$site : ''));
	}

	/** Weigh a fill. The tare comes from the register, never from the scale hand. */
	public function refill(){
		if(!$this->_check_lpg('lpg_refills')) return;
		$this->permission_check('warehouse_view');

		$store_id = (int)get_current_store_id();
		$lpg = $this->_lpg_model();
		$sites = $lpg->lpg_sites($store_id);
		$site_id = $this->_chosen_site($sites, (int)$this->input->get('site', TRUE));
		$today = date('Y-m-d');

		$data = $this->data ?? array();
		$data['page_title'] = 'Refill a Cylinder';
		$data['sites'] = $sites;
		$data['site_id'] = $site_id;
		$data['empty_shells'] = $site_id
			? array_values(array_filter($lpg->cylinders($store_id, array('in_shop'), array($site_id)),
				function($c){ return (int)$c->is_full !== 1; }))
			: array();
		$data['full_shells'] = $site_id
			? array_values(array_filter($lpg->cylinders($store_id, array('in_shop'), array($site_id)),
				function($c){ return (int)$c->is_full === 1; }))
			: array();
		$data['refills'] = $site_id ? $lpg->refills($store_id, $today, $today, 12, $site_id) : array();
		$data['refilled_today'] = $site_id ? $lpg->refilled_kg($store_id, $today, $today, array($site_id)) : 0;
		$data['waiting'] = $lpg->unreconciled_refills($store_id, 7, 10);
		$data['can_work'] = $this->permissions('warehouse_view');
		$data['tracking_flag'] = mp_feature_enabled('cylinder_tracking');
		$data['site_label'] = mp_label('warehouse');

		if(is_mobile()){
			$this->load->view('mobile/oil_gas_refill', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/refill', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_refill(){
		if(!$this->_check_lpg('lpg_refills')) return;
		$this->permission_check_with_msg('warehouse_view');
		$store_id = (int)get_current_store_id();
		$lpg = $this->_lpg_model();
		$site = (int)$this->input->post('site_id', TRUE);
		$result = $lpg->refill($store_id, $this->input->post(NULL, TRUE));
		$this->session->set_flashdata(is_int($result) && $result > 0 ? 'success' : 'error',
			is_int($result) && $result > 0
				? 'Weighed in. The kilograms went out through the till, so stock has already counted them.'
				: strip_tags($result));
		redirect('oil_gas/refill'.($site ? '?site='.$site : ''));
	}

	/** Hand a shell out, take it back, or write it off. */
	public function save_custody(){
		if(!$this->_check_lpg('cylinder_tracking')) return;
		$this->permission_check_with_msg('warehouse_view');
		$store_id = (int)get_current_store_id();
		$lpg = $this->_lpg_model();
		$action = $this->input->post('action', TRUE);
		$id = (int)$this->input->post('cylinder_id', TRUE);
		$site = (int)$this->input->post('site_id', TRUE);
		$note = (string)$this->input->post('note', TRUE);

		if($action === 'issue'){
			$result = $lpg->issue($store_id, $id, (int)$this->input->post('customer_id', TRUE), $note);
			$msg = $result === 'success' ? 'Away it goes. Say who is holding it, and take a deposit if you hold one.' : strip_tags($result);
		}elseif($action === 'return'){
			$result = $lpg->take_back($store_id, $id, $note);
			$msg = $result === 'success' ? 'Back in the shop. Refill it before it goes out again.' : strip_tags($result);
		}elseif($action === 'lost' || $action === 'scrapped'){
			$result = $lpg->write_off_cylinder($store_id, $id, $action, $note);
			$msg = $result === 'success'
				? ($action === 'lost' ? 'Written off as lost. Any deposit held against it stays owed.' : 'Taken off the road.')
				: strip_tags($result);
		}else{
			$result = 'unknown';
			$msg = 'Unknown cylinder action.';
		}
		$this->session->set_flashdata($result === 'success' ? 'success' : 'error', $msg);
		redirect('oil_gas/cylinders'.($site ? '?site='.$site : ''));
	}

	/** One shell out, one shell back, to the same customer — in one action. */
	public function exchange(){
		if(!$this->_check_lpg('cylinder_tracking')) return;
		$this->permission_check('warehouse_view');

		$store_id = (int)get_current_store_id();
		$lpg = $this->_lpg_model();
		$sites = $lpg->lpg_sites($store_id, 'cylinder_tracking');
		$site_id = $this->_chosen_site($sites, (int)$this->input->get('site', TRUE));

		$data = $this->data ?? array();
		$data['page_title'] = 'Cylinder Exchange';
		$data['sites'] = $sites;
		$data['site_id'] = $site_id;
		$data['full_shells'] = $site_id
			? array_values(array_filter($lpg->cylinders($store_id, array('in_shop'), array($site_id)),
				function($c){ return (int)$c->is_full === 1; }))
			: array();
		$data['out_shells'] = $lpg->cylinders($store_id, array('out'));
		$data['customers'] = $lpg->customers($store_id);
		$data['counts'] = $lpg->cylinder_counts($store_id);
		$data['can_work'] = $this->permissions('warehouse_view');
		$data['deposit_flag'] = mp_feature_enabled('cylinder_deposits');
		$data['site_label'] = mp_label('warehouse');

		if(is_mobile()){
			$this->load->view('mobile/oil_gas_exchange', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/exchange', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_exchange(){
		if(!$this->_check_lpg('cylinder_tracking')) return;
		$this->permission_check_with_msg('warehouse_view');
		$store_id = (int)get_current_store_id();
		$lpg = $this->_lpg_model();
		$site = (int)$this->input->post('site_id', TRUE);
		$result = $lpg->exchange($store_id,
			(int)$this->input->post('given_id', TRUE),
			(int)$this->input->post('taken_id', TRUE),
			(int)$this->input->post('customer_id', TRUE),
			(string)$this->input->post('note', TRUE));
		$this->session->set_flashdata($result === 'success' ? 'success' : 'error',
			$result === 'success'
				? 'Exchanged. The register still counts the same number of shells — one out, one back.'
				: strip_tags($result));
		redirect('oil_gas/exchange'.($site ? '?site='.$site : ''));
	}

	/** The money held against shells. Deposits are never stock and never a sale. */
	public function deposits(){
		if(!$this->_check_lpg('cylinder_deposits')) return;
		$this->permission_check('warehouse_view');

		$store_id = (int)get_current_store_id();
		$lpg = $this->_lpg_model();
		$sites = $lpg->worksites($store_id);
		$site_id = $this->_chosen_site($sites, (int)$this->input->get('site', TRUE));

		$data = $this->data ?? array();
		$data['page_title'] = 'Cylinder Deposits';
		$data['sites'] = $sites;
		$data['site_id'] = $site_id;
		$data['cylinders'] = $site_id ? $lpg->cylinders($store_id, null, array($site_id)) : array();
		$data['open'] = $lpg->open_deposits($store_id, 50);
		$data['owed'] = $lpg->deposits_owed($store_id);
		$data['ledger'] = $lpg->deposits($store_id, 30);
		$data['customers'] = $lpg->customers($store_id);
		$data['can_work'] = $this->permissions('warehouse_view');
		$data['site_label'] = mp_label('warehouse');

		if(is_mobile()){
			$this->load->view('mobile/oil_gas_deposits', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/deposits', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_deposit(){
		if(!$this->_check_lpg('cylinder_deposits')) return;
		$this->permission_check_with_msg('warehouse_view');
		$store_id = (int)get_current_store_id();
		$lpg = $this->_lpg_model();
		$site = (int)$this->input->post('site_id', TRUE);
		$result = $lpg->record_deposit($store_id, $this->input->post(NULL, TRUE));
		$this->session->set_flashdata($result === 'success' ? 'success' : 'error',
			$result === 'success'
				? 'Booked. A deposit is money we hold, not gas we sold, so nothing moved in stock.'
				: strip_tags($result));
		redirect('oil_gas/deposits'.($site ? '?site='.$site : ''));
	}

	/** Anyone who can see the site can take its readings. */
	private function _may_work_forecourt(){
		return $this->permissions('warehouse_view');
	}

	/** Same rule the short-load decision uses, so one role set covers both. */
	private function _may_decide_forecourt(){
		return is_store_admin() || is_admin() || $this->permissions('approval_logs_view');
	}

	// ------------------------------------------------------------- service work

	/**
	 * Service work is the fuel company's own surface. The equipment trade's
	 * `service_jobs` switch brings that trade's screens with it, so either switch
	 * opens this rail and neither one forces the other.
	 */
	private function _check_jobs(){
		$this->_check_feature();
		if(!function_exists('mp_oil_gas_jobs_enabled') || !mp_oil_gas_jobs_enabled()){
			$this->show_feature_not_activated(
				'oil_gas_service_jobs',
				'Switch on Equipment Service Work under Business Profile &rarr; Capabilities to book installation and maintenance visits at your locations.'
			);
			return false;
		}
		return true;
	}

	/** The locations this person works across, as id => name. */
	private function _site_map($locations){
		$choices = array();
		foreach($locations as $site){
			$choices[(int)$site->id] = ($site->warehouse_name !== '') ? $site->warehouse_name : 'Unnamed location';
		}
		return $choices;
	}

	/** Who may be sent out, named the way a counter would say it. */
	private function _staff_choices($store_id){
		// Both exclusion helpers run their own query, so resolve them first.
		$excluded_roles = function_exists('get_excluded_staff_roles') ? get_excluded_staff_roles($store_id) : array();
		$excluded_users = function_exists('get_excluded_user_ids') ? get_excluded_user_ids($store_id) : array();
		$rows = $this->db->select('id, username, first_name, last_name')
			->where('store_id', (int)$store_id)->where('status', 1)
			->where_not_in('role_id', $excluded_roles)
			->where_not_in('id', $excluded_users)
			->order_by('username')->limit(80)->get('db_users')->result();
		foreach($rows as $row){
			$named = trim($row->first_name.' '.$row->last_name);
			$row->name = $named !== '' ? $named : $row->username;
		}
		return $rows;
	}

	/** Who the work is for. Bounded, and searchable once the list grows. */
	private function _customer_choices($store_id, $search = ''){
		$this->db->select('id, customer_name')->where('store_id', (int)$store_id)->where('status', 1);
		if($search !== '') $this->db->like('customer_name', substr($search, 0, 60));
		return $this->db->order_by('customer_name')->limit(200)->get('db_customers')->result();
	}

	/**
	 * Service work across the company: which site the visit is at, who is going,
	 * what it is worth. Head office sees every site; a site's staff see their own.
	 */
	public function jobs(){
		if(!$this->_check_jobs()) return;
		$this->permission_check('service_jobs_view');
		$this->load->model('equipment_model', 'equipment');

		$store_id = (int)get_current_store_id();
		$locations = $this->oil_gas->locations($store_id);
		$sites = $this->_site_map($locations);
		$site = (int)$this->input->get('site', TRUE);
		$status = (string)$this->input->get('status', TRUE);
		$search = trim((string)$this->input->get('q', TRUE));

		$assigned = function_exists('mp_location_assigned_ids') ? mp_location_assigned_ids($store_id) : null;
		$filters = array('status' => $status, 'open_only' => ($status === 'open_list'));
		if(is_array($assigned)) $filters['scope_location_ids'] = $assigned;
		if($site > 0 && isset($sites[$site])) $filters['warehouse_id'] = $site;

		$jobs = $this->equipment->get_job_list($filters);
		$open = 0;
		foreach($jobs as $job){
			if(!in_array($job->status, array('completed','cancelled'), true)) $open++;
			// A screen should never offer a move the job book refuses to make.
			$job->next = $this->equipment->next_statuses($job->status);
		}

		// Only a job this person may reach can be reopened in the form.
		$editing = null;
		$edit_id = (int)$this->input->get('edit', TRUE);
		$may_edit = (is_store_admin() || is_admin() || $this->permissions('service_jobs_edit'));
		if($edit_id && $may_edit){
			$candidate = $this->equipment->get_job($edit_id);
			$at = $candidate ? (int)($candidate->warehouse_id ?? 0) : 0;
			if($candidate && (!is_array($assigned) || in_array($at, $assigned, true))) $editing = $candidate;
		}

		$data = $this->data ?? array();
		$data['page_title'] = 'Service Work';
		$data['locations'] = $locations;
		$data['jobs'] = array_slice($jobs, 0, 60);
		$data['shown'] = count($data['jobs']);
		$data['total'] = count($jobs);
		$data['open_count'] = $open;
		$data['sites'] = $sites;
		$data['editing'] = $editing;
		$data['site'] = $site;
		$data['status'] = $status;
		$data['search'] = $search;
		$data['customers'] = $this->_customer_choices($store_id, $search);
		$data['staff'] = $this->_staff_choices($store_id);
		$data['job_types'] = $this->equipment->job_types();
		$data['can_add'] = (is_store_admin() || is_admin() || $this->permissions('service_jobs_add'));
		$data['can_edit'] = (is_store_admin() || is_admin() || $this->permissions('service_jobs_edit'));
		$data['site_label'] = mp_label('warehouse');
		$data['customer_label'] = mp_label('customer');
		$data['multi_site'] = (count($sites) > 1);
		$data['scoped'] = is_array($assigned);

		if(is_mobile()){
			$this->load->view('mobile/oil_gas_jobs', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/jobs', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_job(){
		if(!$this->_check_jobs()) return;
		$job_id = (int)$this->input->post('job_id');
		$this->permission_check_with_msg($job_id ? 'service_jobs_edit' : 'service_jobs_add');
		$this->load->model('equipment_model', 'equipment');
		$result = (string)$this->equipment->save_job();
		$site = (int)$this->input->post('warehouse_id');
		if(strpos($result, 'success') === 0){
			$this->session->set_flashdata('success', $job_id
				? 'Saved. The visit is still booked at the same location.'
				: 'Booked. The work now sits with that location and its engineer.');
		} else {
			$this->session->set_flashdata('error', strip_tags($result));
		}
		redirect('oil_gas/jobs'.($site ? '?site='.$site : ''));
	}

	/** Move a job along its own path: booked, on site, waiting for a part, done. */
	public function set_job_status(){
		if(!$this->_check_jobs()) return;
		$this->permission_check_with_msg('service_jobs_edit');
		$this->load->model('equipment_model', 'equipment');
		$result = (string)$this->equipment->update_job_status();
		$site = (int)$this->input->post('warehouse_id');
		$this->session->set_flashdata(strpos($result, 'success') === 0 ? 'success' : 'error',
			strpos($result, 'success') === 0
				? 'Noted. Everyone looking at this location sees the new status.'
				: strip_tags($result));
		redirect('oil_gas/jobs'.($site ? '?site='.$site : ''));
	}

	// ---------------------------------------------------------------- report

	/**
	 * Head office's consolidated view: every location on one table, and a company
	 * line that is the sum of the rows printed above it.
	 */
	public function report(){
		$this->_check_feature();
		$this->permission_check('dashboard_view');

		$store_id = (int)get_current_store_id();
		$report = $this->oil_gas->group_report($store_id,
			(string)$this->input->get('from', TRUE), (string)$this->input->get('to', TRUE));

		$data = $this->data ?? array();
		$data['page_title'] = 'Company Report';
		$data['report'] = $report;
		$data['from'] = $report['from'];
		$data['to'] = $report['to'];
		$data['sites'] = $this->_site_map($this->oil_gas->locations($store_id));
		$data['site_label'] = mp_label('warehouse');
		$data['transfer_label'] = mp_label('stock_transfer');

		if(is_mobile()){
			$this->load->view('mobile/oil_gas_report', $data);
			return;
		}
		$data['content'] = $this->load->view('oil_gas/report', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
}
