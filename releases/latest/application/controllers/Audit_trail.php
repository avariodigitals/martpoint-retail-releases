<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Audit Trail
 *
 * Operational log of important user actions (logins, record changes,
 * deletions, settings updates) stored in db_audit_trail.
 *
 * Access: permission key `audit_trail_view` — wired into the roles matrix
 * but not assigned to any default role (Store Admin / Super Admin always
 * pass permissions()).
 */
class Audit_trail extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	public function index(){
		$this->permission_check('audit_trail_view');

		if(!$this->db->table_exists('db_audit_trail')){
			$data = $this->data;
			$data['page_title'] = 'Audit Trail';
			$data['entries'] = [];
			$data['modules'] = [];
			$data['actions'] = [];
			$data['filters'] = [];
			$data['table_missing'] = true;
			$data['content'] = $this->load->view('audit_trail', $data, TRUE);
			$this->load->view('mp_layout', $data);
			return;
		}

		$store_id = get_current_store_id();

		$filters = array(
			'from'    => $this->input->get('from', TRUE),
			'to'      => $this->input->get('to', TRUE),
			'module'  => $this->input->get('module', TRUE),
			'action'  => $this->input->get('action', TRUE),
			'q'       => $this->input->get('q', TRUE),
		);

		$perPage = 50;
		$page    = max(1, (int)$this->input->get('page'));

		$this->_applyAuditFilters($store_id, $filters);
		$total = (int)$this->db->count_all_results('db_audit_trail');

		$this->_applyAuditFilters($store_id, $filters);
		$this->db->order_by('id', 'DESC')->limit($perPage, ($page - 1) * $perPage);
		$entries = $this->db->get()->result();

		$modules = $this->db->select('module')->distinct()
			->where('store_id', $store_id)
			->order_by('module')->get('db_audit_trail')->result();

		$actions = $this->db->select('action')->distinct()
			->where('store_id', $store_id)
			->order_by('action')->get('db_audit_trail')->result();

		$data = $this->data;
		$data['page_title'] = 'Audit Trail';
		$data['entries'] = $entries;
		$data['modules'] = $modules;
		$data['actions'] = $actions;
		$data['filters'] = $filters;
		$data['page'] = $page;
		$data['per_page'] = $perPage;
		$data['total'] = $total;
		$data['table_missing'] = false;
		$data['content'] = $this->load->view('audit_trail', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** Shared filter chain for the count query and the page query. */
	private function _applyAuditFilters($store_id, array $filters){
		$this->db->from('db_audit_trail')->where('store_id', $store_id);
		if(!empty($filters['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['from'])){
			$this->db->where('created_date >=', $filters['from']);
		}
		if(!empty($filters['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['to'])){
			$this->db->where('created_date <=', $filters['to']);
		}
		if(!empty($filters['module'])){
			$this->db->where('module', $filters['module']);
		}
		if(!empty($filters['action'])){
			$this->db->where('action', $filters['action']);
		}
		if(!empty($filters['q'])){
			$q = $filters['q'];
			$this->db->group_start()
				->like('username', $q)
				->or_like('description', $q)
				->or_like('ref_id', $q)
			->group_end();
		}
	}
}
