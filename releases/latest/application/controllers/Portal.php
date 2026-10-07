<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Patient portal — public, self-authenticated surface (Stage 6).
 *
 * Deliberately does NOT call load_global()/the staff dashboard gate:
 * portal principals authenticate via portal_* session keys validated by
 * Portal_model::currentPrincipal() on every request. Patient ownership is
 * enforced inside the model for every data access — controllers never
 * trust a route id.
 */
class Portal extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->database();
		$this->load->helper('physio');
		$this->load->model('portal_model', 'portal');
	}

	private function _principal($need = null){
		$p = $this->portal->currentPrincipal();
		if(!$p){ $this->session->sess_destroy(); redirect('portal'); exit; }
		if($need && !$this->portal->proxyCan($need, $p)){
			$this->load->view('portal/denied', array('title'=>'Not permitted'));
			return null;
		}
		if((int)$this->portal->policy('portal_enabled', 1, $p['store_id']) !== 1){
			$this->session->sess_destroy(); redirect('portal'); exit;
		}
		return $p;
	}

	private function _render($view, $data, $p){
		$data['p'] = $p;
		$data['store_name'] = $this->db->select('store_name')->where('id',$p['store_id'])->get('db_store')->row()->store_name ?? 'Portal';
		$data['patient_code'] = $p['patient']->patient_code ?? '';
		$this->load->view('portal/'.$view, $data);
	}

	// ------------------------------------------------------------------
	// Authentication
	// ------------------------------------------------------------------
	public function index(){
		if($this->portal->currentPrincipal()){ redirect('portal/home'); return; }
		$this->load->view('portal/login', array('error'=>null));
	}
	public function login(){
		if($this->input->method() !== 'post'){ redirect('portal'); return; }
		$r = $this->portal->login($this->input->post('identity', TRUE), $this->input->post('password'));
		if(!$r['ok']){ $this->load->view('portal/login', array('error'=>$r['error'])); return; }
		$this->portal->beginSession($r['kind'], $r['row']);
		redirect('portal/home');
	}
	public function logout(){
		$this->portal->endSession();
		redirect('portal');
	}

	/** Invitation landing — verify token, set password. */
	public function invite($token = ''){
		$inv = $this->portal->findInvite($token);
		if(!$inv){ $this->load->view('portal/login', array('error'=>'This invitation link is invalid or already used.')); return; }
		if($inv['row']->invite_expires_at && strtotime($inv['row']->invite_expires_at) < time()){
			$this->load->view('portal/login', array('error'=>'This invitation has expired — ask the clinic to send a new one.')); return;
		}
		if($this->input->method() === 'post'){
			$r = $this->portal->acceptInvite($token, (string)$this->input->post('password'));
			if($r['ok']){ $this->load->view('portal/login', array('error'=>null,'ok'=>'Password set — sign in below.')); return; }
			$this->load->view('portal/invite', array('kind'=>$inv['kind'],'error'=>$r['error'] === 'expired' ? 'This invitation has expired' : $r['error'],'token'=>$token));
			return;
		}
		$this->load->view('portal/invite', array('kind'=>$inv['kind'],'error'=>null,'token'=>$token));
	}

	// ------------------------------------------------------------------
	// Authenticated pages
	// ------------------------------------------------------------------
	public function home(){
		$p = $this->_principal(); if(!$p) return;
		$this->_render('home', array(
			'patient' => $p['patient'],
			'funds'   => $this->portal->proxyCan('funds',$p) ? $this->portal->funds($p['patient_id'],$p['store_id']) : null,
		), $p);
	}
	public function appointments(){
		$p = $this->_principal('appointments'); if(!$p) return;
		$this->_render('appointments', array('items'=>$this->portal->appointments($p['patient_id'],$p['store_id'])), $p);
	}
	public function progress(){
		$p = $this->_principal('progress'); if(!$p) return;
		$this->_render('progress', $this->portal->progress($p['patient_id'],$p['store_id']), $p);
	}
	public function bills(){
		$p = $this->_principal('bills'); if(!$p) return;
		$this->_render('bills', array('items'=>$this->portal->bills($p['patient_id'],$p['store_id'])), $p);
	}
	public function bill($id = 0){
		$p = $this->_principal('bills'); if(!$p) return;
		$bill = $this->portal->bill($id, $p['patient_id'], $p['store_id']);
		if(!$bill){ $this->load->view('portal/denied', array('title'=>'Not found')); return; }
		$this->_render('bill', $bill, $p);
	}
	/** Payment evidence — same replay/conflict semantics as the staff path. */
	public function submit_evidence($salesId = 0){
		$p = $this->_principal('bills'); if(!$p) return;
		$r = $this->portal->submitEvidence($salesId, $p['patient_id'], $p['store_id'],
			(float)$this->input->post('amount'), trim((string)$this->input->post('payment_ref')),
			trim((string)$this->input->post('channel')) ?: 'transfer');
		$this->session->set_flashdata('portal_msg', !empty($r['ok'])
			? (!empty($r['replayed']) ? 'This reference was already submitted — no duplicate was created.'
			   : (!empty($r['conflict']) ? 'That reference was already used for different details — it was not accepted.'
			   : 'Evidence submitted for review.'))
			: ($r['error'] ?? 'Submission failed'));
		redirect('portal/bill/'.$salesId);
	}
	public function funds(){
		$p = $this->_principal('funds'); if(!$p) return;
		$this->_render('funds', $this->portal->funds($p['patient_id'],$p['store_id']), $p);
	}
	public function statement(){
		$p = $this->_principal('funds'); if(!$p) return;
		$this->_render('statement', $this->portal->statement($p['patient_id'],$p['store_id']), $p);
	}
	public function documents(){
		$p = $this->_principal('documents'); if(!$p) return;
		$this->_render('documents', array('items'=>$this->portal->documents($p['patient_id'],$p['store_id'])), $p);
	}
	public function document($id = 0){
		$p = $this->_principal('documents'); if(!$p) return;
		$doc = $this->portal->portalDoc($id, $p['patient_id'], $p['store_id']);
		if(!$doc || !$doc->current_version_id){ $this->load->view('portal/denied', array('title'=>'Not found')); return; }
		$v = $this->db->where('id',(int)$doc->current_version_id)->get('db_document_versions')->row();
		$dir = function_exists('physio_docs_dir') ? physio_docs_dir() : null;
		if(!$v || !$dir || !is_file($dir.'/'.$v->file_path)){ $this->load->view('portal/denied', array('title'=>'File unavailable')); return; }
		$this->db->insert('db_document_access_log', array(
			'store_id'=>$p['store_id'],'document_id'=>$doc->id,'version_id'=>$v->id,
			'action'=>'portal_download','user_id'=>null,
			'username'=>'portal:'.$p['kind'].':'.$p['account']->id,
			'ip'=>$this->input->ip_address(),
			'created_at'=>date('Y-m-d H:i:s'),
		));
		header('Content-Type: '.($v->mime ?: 'application/octet-stream'));
		header('Content-Disposition: inline; filename="'.preg_replace('/[^a-zA-Z0-9._-]/','_',$doc->title ?: 'document').'"');
		header('X-Content-Type-Options: nosniff');
		readfile($dir.'/'.$v->file_path);
	}
	public function feedback(){
		$p = $this->_principal('feedback'); if(!$p) return;
		if($this->input->method() === 'post'){
			$r = $this->portal->submitFeedback($p['patient_id'], $p['store_id'], $this->input->post(NULL, TRUE));
			$this->session->set_flashdata('portal_msg', !empty($r['ok']) ? 'Thank you — your feedback was recorded privately.' : ($r['error'] ?? 'Could not save feedback'));
			redirect('portal/feedback'); return;
		}
		$this->_render('feedback', array(), $p);
	}
	public function testimonials(){
		$p = $this->_principal(); if(!$p) return;
		if($this->input->method() === 'post'){
			$r = $this->portal->submitTestimonial($p['patient_id'], $p['store_id'], $this->input->post(NULL, TRUE));
			$this->session->set_flashdata('portal_msg', !empty($r['ok']) ? 'Thank you — your testimonial was sent for moderation.' : ($r['error'] ?? 'Could not save'));
			redirect('portal/testimonials'); return;
		}
		$this->_render('testimonials', array('mine'=>$this->portal->myTestimonials($p['patient_id'],$p['store_id'])), $p);
	}
	public function withdraw_testimonial($id = 0){
		$p = $this->_principal(); if(!$p) return;
		if($this->input->method() !== 'post'){ redirect('portal/testimonials'); return; }
		$this->portal->withdrawTestimonial($id, $p['patient_id'], $p['store_id']);
		$this->session->set_flashdata('portal_msg','Testimonial withdrawn — it will no longer display.');
		redirect('portal/testimonials');
	}
}
