<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Feedback — private patient ratings + testimonial moderation.
 *
 * Private feedback is never published automatically. Testimonials require a
 * separate publication consent, explicit moderation and can be withdrawn by
 * the patient at any time (takes effect immediately).
 */
class Feedback extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled()){ $this->show_feature_not_activated('physiotherapy'); return; }
		if(!physio_can_any(['patient_feedback_view','patient_feedback_manage','testimonial_publish'])){
			$this->show_access_denied_page(); return;
		}
		$this->load->model('portal_model', 'portal');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	public function index(){
		if(!physio_can('patient_feedback_view')){ $this->show_access_denied_page(); return; }
		$data = array_merge($this->data, array(
			'page_title' => 'Patient Feedback',
			'rows'       => $this->portal->feedbackList(get_current_store_id()),
			'can_manage' => physio_can('patient_feedback_manage'),
		));
		$data['content'] = $this->load->view('feedback/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function follow_up($id = 0){
		if(!physio_can('patient_feedback_manage')){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$status = trim((string)$this->input->post('status', TRUE)) ?: 'acknowledged';
		$note   = trim((string)$this->input->post('note', TRUE));
		if($note === ''){ $this->_json(array('status'=>'error','message'=>'A follow-up note is required')); return; }
		$res = $this->portal->feedbackFollowUp((int)$id, $status, $note);
		$this->_json(!empty($res['ok'])
			? array('status'=>'success','message'=>'Follow-up recorded')
			: array('status'=>'error','message'=>$res['error'] ?? 'Not found'));
	}

	public function testimonials(){
		if(!physio_can_any(['patient_feedback_view','testimonial_publish'])){ $this->show_access_denied_page(); return; }
		$data = array_merge($this->data, array(
			'page_title' => 'Testimonials',
			'rows'       => $this->portal->testimonials(get_current_store_id()),
			'can_publish'=> physio_can('testimonial_publish'),
		));
		$data['content'] = $this->load->view('feedback/testimonials', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function moderate($id = 0){
		if(!physio_can('testimonial_publish')){ $this->_json(array('status'=>'error','message'=>'Access denied')); return; }
		$decision = trim((string)$this->input->post('decision', TRUE));
		$note     = trim((string)$this->input->post('note', TRUE));
		$res = $this->portal->moderateTestimonial((int)$id, $decision, $note);
		$this->_json(!empty($res['ok'])
			? array('status'=>'success','message'=>$decision === 'approved'
				? 'Approved — now eligible to display on the storefront' : 'Rejected — will not display')
			: array('status'=>'error','message'=>$res['error'] ?? 'Failed'));
	}
}
